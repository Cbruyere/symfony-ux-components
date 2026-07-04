# Déploiement de la démo (production)

## Purpose

Ce document décrit comment l'application `demo/` de ce bundle est construite, publiée et déployée
en production, sur le même modèle que le portfolio (`Cbruyere/portfolio`) : image Docker → registre
privé → GitOps (`Cbruyere/k8s-deploy`) → ArgoCD → cluster Kubernetes.

Il sert de référence pour comprendre le pipeline existant, le reproduire pour une nouvelle app, ou
diagnostiquer un incident de déploiement.

---

## Vue d'ensemble du flux

```
git tag vX.Y.Z (ux-components)
        │
        ▼
GitHub Actions (.github/workflows/deploy.yml)
  1. build l'image Docker (Dockerfile à la racine du repo)
  2. scan Trivy (bloquant sur les vulnérabilités CRITICAL)
  3. push vers registry.chrisdeveloppement.ovh/ux-components-demo:X.Y.Z
  4. checkout du repo k8s-deploy
  5. met à jour values/apps/ux-components-demo/app.yaml (image.tag)
  6. commit + push sur main de k8s-deploy
        │
        ▼
ArgoCD (Application ux-components-demo, syncPolicy.automated + selfHeal)
  détecte le changement sur main → synchronise → met à jour le Deployment K8s
        │
        ▼
Nouveau pod tiré avec la nouvelle image, ancien pod terminé
```

---

## Composants du pipeline

### 1. Image de production (`ux-components/Dockerfile`)

Le bundle expose un `demo/` (mini-CRM Symfony) qui référence le bundle parent via un
**path repository Composer** (`demo/composer.json` → `repositories: [{type: path, url: "../"}]`).

Conséquence directe : **le contexte de build Docker doit être la racine du repo**, pas `demo/`,
sinon `composer install` dans `demo/` ne peut pas résoudre le bundle.

Le `Dockerfile` est multi-stage :

1. **`composer-builder`** (`composer:2`) : `COPY . .` (toute l'arborescence), puis
   `cd demo && composer install --no-dev --no-scripts ...`.
2. **Stage final** (`php:8.4-apache`) : installe `intl opcache pdo_pgsql zip`, configure le vhost
   Apache sur `demo/public`, copie le code + le vendor du stage précédent, puis :
   - `php bin/console importmap:install` — **indispensable** : `composer install` tourne avec
     `--no-scripts`, ce qui désactive le hook Symfony qui lance normalement `importmap:install`
     automatiquement. Sans cette étape explicite, `assets/vendor/` (gitignored, comme
     `node_modules/`) est vide sur un checkout propre et `asset-map:compile` échoue.
   - `php bin/console asset-map:compile`
   - `cache:clear` / `cache:warmup --env=prod`

`.dockerignore` exclut `vendor/`, `demo/vendor/`, `demo/var/`, `node_modules/`, `.git/`.

**Piège déjà rencontré** : `demo/` était tracké par git comme un **gitlink** (mode `160000`,
référence de sous-module) sans `.gitmodules` associé. En local les fichiers existent sur disque donc
rien ne le révèle, mais un checkout frais (CI) laisse `demo/` complètement vide → `composer.json`
introuvable. Corrigé une fois pour toutes via `git rm --cached demo && git add demo`. Si `demo/`
redevient un gitlink après un mauvais `git add` ailleurs, le symptôme est identique.

### 2. Workflow GitHub Actions (`.github/workflows/deploy.yml`)

Déclenché sur `push: tags: ["v*"]`. Reproduit exactement le pipeline du portfolio :

- Calcule le tag d'image à partir du tag git (`v1.2.3` → `1.2.3`).
- Login au registre (`docker/login-action`) avec les secrets repo `REGISTRY_USERNAME` /
  `REGISTRY_PASSWORD`.
- Build (`docker/build-push-action`, `load: true`) puis scan Trivy (`severity: CRITICAL`,
  `exit-code: 1` — le job échoue si une vuln critique corrigeable est trouvée).
- Push de l'image.
- Checkout de `Cbruyere/k8s-deploy` avec le secret `K8S_DEPLOY_TOKEN` (PAT avec accès en écriture).
- Script Python qui réécrit `image.tag` dans `values/apps/ux-components-demo/app.yaml`, puis commit
  + push sur `main`.

**Secrets requis sur le repo GitHub** (Settings → Secrets and variables → Actions) :

| Secret | Usage | Source |
|---|---|---|
| `REGISTRY_USERNAME` | login registre | même compte que le portfolio (`registry-auth` dans `k8s-deploy/secrets/platform/`) |
| `REGISTRY_PASSWORD` | login registre | idem |
| `K8S_DEPLOY_TOKEN` | push sur `k8s-deploy` | PAT GitHub, même que celui utilisé par ArgoCD pour lire `k8s-deploy` (`secrets/platform/argocd-repo-k8s-deploy.sops.yaml`, champ `password`) |

Ces valeurs sont sensibles : ne jamais les afficher dans un terminal partagé/log/conversation.
Pour les récupérer localement : `sops --decrypt <fichier>.sops.yaml` (nécessite la clé `age` du poste).

### 3. GitOps (`k8s-deploy`)

Convention de nommage (voir `k8s-deploy/docs/NAMING_CONVENTIONS.md`), `app_slug = ux-components-demo` :

| Élément | Valeur |
|---|---|
| Host public | `ux-components-demo.chrisdeveloppement.ovh` |
| Image | `registry.chrisdeveloppement.ovh/ux-components-demo:<tag>` |
| Release Helm app | `ux-components-demo` |
| Release Helm DB | `ux-components-demo-db` |
| Ressource Postgres | `ux-components-demo-postgres` (⚠️ pas `postgres` générique, cf. ci-dessous) |
| Secret app | `ux-components-demo-app-secrets` (`APP_SECRET`, `DATABASE_URL`) |
| Secret Postgres | `ux-components-demo-postgres-auth` (`POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD`) |
| Namespace K8s | `apps` (partagé avec toutes les autres apps) |

Fichiers créés dans `k8s-deploy` :

- `values/apps/ux-components-demo/app.yaml` — values Helm pour le chart `helm/php-app`.
- `values/apps/ux-components-demo/postgres.yaml` — values Helm pour le chart `helm/postgres`.
- `secrets/apps/ux-components-demo-app-secrets.sops.yaml` et
  `secrets/apps/ux-components-demo-postgres-auth.sops.yaml` — secrets chiffrés SOPS.
- `argocd/apps/ux-components-demo.yaml` — `Application` ArgoCD.

**Piège découvert en déployant** : le namespace `apps` est **partagé par toutes les applications**
du cluster. Le chart `helm/postgres` avec `fullnameOverride: postgres` (valeur générique documentée
dans `k8s-deploy/values/templates/symfony/postgres.example.yaml`) crée une ressource **littéralement
nommée `postgres`**. Or `pepememe` a déjà une base déployée sous ce nom exact dans `apps`. Utiliser le
nom générique aurait donc collisionné avec une base de production existante. Solution retenue :
scoper le nom (`fullnameOverride: ux-components-demo-postgres`), comme cela a déjà été fait
silencieusement pour `laravel-demo` (dont le fichier `values/demo/laravel-demo/postgres.yaml` committé
est d'ailleurs obsolète : il affiche encore `fullnameOverride: postgres` alors que le pod réellement
déployé s'appelle `laravel-demo-postgres-...`).

**Avant de créer une base pour une nouvelle app**, toujours vérifier l'état réel du cluster
(`kubectl get pods -n apps`) plutôt que de faire confiance aux fichiers `values/` committés, qui
peuvent être désynchronisés de ce qui tourne réellement.

### 4. ArgoCD

Contrairement à ce qu'on pourrait attendre d'un GitOps « pur », **pousser le manifeste
`argocd/apps/<app>.yaml` sur `main` ne crée pas l'`Application` automatiquement** — il n'y a pas de
pattern *app-of-apps* qui scanne ce dossier dans ce repo (seules `portfolio` et `keycloak` existent
comme `Application` bootstrap). Il faut l'appliquer manuellement une fois :

```bash
kubectl apply -f k8s-deploy/argocd/apps/ux-components-demo.yaml
```

Une fois l'`Application` créée, en revanche, son `syncPolicy.automated.selfHeal: true` fait bien le
travail attendu : tout changement ultérieur sur `values/apps/ux-components-demo/app.yaml` dans
`main` (ex. mise à jour du tag d'image par la CI) est synchronisé automatiquement, sans autre
intervention manuelle.

Le Postgres, lui, **n'est pas géré par ArgoCD** (aucune app existante dans ce repo ne gère sa base via
GitOps) : il est déployé une fois via `helm upgrade --install`, en dehors du cycle continu.

---

## Procédure complète (from scratch)

1. **Secrets Kubernetes** (avant tout déploiement, sinon le pod Postgres ne démarre pas) :

   ```bash
   sops --decrypt secrets/apps/ux-components-demo-postgres-auth.sops.yaml | kubectl apply -f -
   sops --decrypt secrets/apps/ux-components-demo-app-secrets.sops.yaml | kubectl apply -f -
   ```

2. **Postgres** (une fois, hors GitOps) :

   ```bash
   helm upgrade --install ux-components-demo-db helm/postgres \
     -n apps -f values/apps/ux-components-demo/postgres.yaml --wait
   ```

3. **Application ArgoCD** (une fois, bootstrap) :

   ```bash
   kubectl apply -f argocd/apps/ux-components-demo.yaml
   ```

4. **Secrets GitHub Actions** (une fois, dans les settings du repo `ux-components`) :
   `REGISTRY_USERNAME`, `REGISTRY_PASSWORD`, `K8S_DEPLOY_TOKEN`.

5. **Migrations Doctrine** (après le premier déploiement de l'app, la base est vide) :

   ```bash
   kubectl exec -n apps deploy/ux-components-demo -- sh -c \
     "cd demo && php bin/console doctrine:migrations:migrate --no-interaction --env=prod"
   ```

   Les paquets de fixtures (`zenstruck/foundry`, `doctrine-fixtures-bundle`) sont en `require-dev` et
   absents de l'image de prod par design. Pour peupler des données de démo, il n'existe pas
   aujourd'hui de commande console dédiée en prod : on est passé par un script PHP ad hoc exécuté
   dans le pod (bootstrap du kernel + `EntityManager::persist`). Une vraie commande console de seed
   (utilisable avec `--env=prod`, packagée hors `require-dev`) serait une meilleure solution durable
   si ce besoin se répète.

6. **Release applicative** (à chaque changement de code) :

   ```bash
   git tag -a vX.Y.Z -m "vX.Y.Z"
   git push origin vX.Y.Z
   ```

   Le reste (build, scan, push, mise à jour GitOps, sync ArgoCD) est automatique.

---

## Vérification post-déploiement

```bash
kubectl get pods -n apps -l app.kubernetes.io/instance=ux-components-demo
kubectl get application ux-components-demo -n argocd
kubectl logs -n apps deploy/ux-components-demo --tail=50
curl -kI https://ux-components-demo.chrisdeveloppement.ovh
```

---

## Incidents rencontrés lors du premier déploiement (résumé)

| Symptôme | Cause | Correction |
|---|---|---|
| `Username and password required` dans le job de login registre | Secrets GitHub Actions absents | Ajout de `REGISTRY_USERNAME`/`REGISTRY_PASSWORD` dans les settings du repo |
| `No composer.json in current directory` pendant le build | `demo/` tracké comme gitlink cassé (sans `.gitmodules`) | `git rm --cached demo && git add demo` |
| Rien dans ArgoCD après le push sur `k8s-deploy` | Pas d'app-of-apps ; une nouvelle `Application` doit être appliquée manuellement | `kubectl apply -f argocd/apps/ux-components-demo.yaml` |
| `HTTP 500` — `relation "app_user" does not exist` | Migrations jamais jouées sur la base fraîchement créée | `doctrine:migrations:migrate --env=prod` |
| Boutons « Voir »/« Modifier » de la DataTable inactifs | `route: app_home` utilisé comme placeholder pour les deux actions, sans page de destination réelle | Ajout d'une route `user_show` + gabarit dédié, actions liées via `params: {id: 'id'}` |
