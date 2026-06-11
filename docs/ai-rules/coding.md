# Coding Rules

## Core Principles

Le code doit respecter autant que possible :

- SOLID
- Clean Architecture
- séparation des responsabilités
- faible couplage
- forte cohésion
- lisibilité avant optimisation prématurée
- simplicité avant abstraction excessive

---

## SOLID

### Single Responsibility Principle

Une classe doit avoir une seule responsabilité.

Éviter les classes fourre-tout comme :

- Manager
- Helper
- Utility
- CommonService

Préférer des services ciblés :

- UserAvatarUploader
- PdfGenerator
- UserProfileUpdater

---

### Open/Closed Principle

Le code doit être extensible sans modification excessive du code existant.

Préférer :

- interfaces
- services dédiés
- stratégies simples

Éviter les gros blocs conditionnels difficiles à maintenir.

---

### Liskov Substitution Principle

Une classe enfant doit pouvoir remplacer sa classe parente sans casser le comportement attendu.

Ne pas utiliser l’héritage pour partager du code par confort.

Préférer la composition.

---

### Interface Segregation Principle

Les interfaces doivent rester petites et ciblées.

Éviter les interfaces génériques trop larges.

Bon :

```php
interface PdfGeneratorInterface
{
    public function generateFromHtml(string $html): string;
}
```

Mauvais :

```php
interface ApplicationServiceInterface
{
    public function generatePdf(): void;
    public function uploadFile(): void;
    public function sendEmail(): void;
}
```

---

### Dependency Inversion Principle

Les services doivent dépendre d’abstractions lorsque cela apporte une vraie valeur.

Bon usage :

- service externe
- génération PDF
- stockage fichier
- client HTTP
- système d’email

Ne pas créer une interface inutile pour chaque classe.

---

## Clean Architecture

Le code doit séparer clairement :

```text
Interface utilisateur
↓
Application
↓
Domaine
↓
Infrastructure
```

Dans Symfony :

```text
Controller
↓
Service / Use Case
↓
Entity / Value Object
↓
Repository / Infrastructure
```

---

## Controllers

Les contrôleurs doivent rester fins.

Ils peuvent :

- recevoir une requête
- vérifier les accès
- gérer un formulaire
- appeler un service
- retourner une réponse

Ils ne doivent pas contenir :

- logique métier complexe
- requêtes SQL
- transformations lourdes
- accès direct à des services externes

---

## Services

Les services doivent porter les cas d’usage applicatifs.

Bon :

```text
UserProfileUpdater
AvatarUploader
PdfGenerator
```

À éviter :

```text
UserManager
AppHelper
CommonService
UtilityService
```

---

## Entities

Les entités doivent contenir :

- état métier
- règles simples liées à cet état

Elles ne doivent pas contenir :

- logique HTTP
- appels API
- accès fichiers
- appels services
- logique Twig

---

## DTO / Value Objects

Utiliser des DTO ou Value Objects quand cela améliore la lisibilité.

Exemples :

- coordonnées GPS
- période de date
- adresse email
- fichier uploadé
- résultat de génération PDF

Ne pas créer de DTO inutile pour chaque formulaire simple.

---

## Twig Components

Prefer moving presentation logic to the component class.

Good:

```php
public function getVariantClasses(): string
public function getIcon(): string
public function getOptions(): array
```

Bad:

```php{% if variant == 'success' %}
{% elseif variant == 'warning' %}
{% elseif variant == 'danger' %}
```

Twig templates should primarily render data.

Complex conditionals should be implemented in PHP.

## Repositories

Les repositories doivent gérer l’accès aux données.

Ils peuvent contenir :

- find
- search
- save
- remove

Ils ne doivent pas contenir :

- logique métier
- logique de rendu
- logique HTTP

---

## Naming

Les noms doivent décrire l’intention.

Bon :

```text
UpdateUserProfileHandler
GeneratePdfFromHtml
UserAvatarUploader
```

Mauvais :

```text
ProcessData
DoStuff
HandleManager
Utils
```

---

## Abstraction

Ne pas sur-abstraire.

Créer une abstraction seulement si :

- plusieurs implémentations sont prévues ;
- un service externe doit être isolé ;
- les tests en bénéficient réellement ;
- le code devient plus clair.

---

## Minimal Diff

Toujours produire le plus petit changement possible.

Ne pas :

- refactoriser sans demande explicite ;
- renommer sans nécessité ;
- déplacer des fichiers sans raison ;
- reformater des fichiers non concernés.

---

## Dependencies

Ne jamais ajouter de dépendance Composer ou npm sans justification claire.

Avant d’ajouter une dépendance :

1. vérifier si Symfony fournit déjà une solution ;
2. vérifier si Symfony UX fournit déjà une solution ;
3. vérifier si le besoin peut être couvert par du code simple ;
4. documenter la raison.

---

## Error Handling

Les erreurs doivent être explicites.

Préférer :

```php
throw new UnableToGeneratePdfException();
```

à :

```php
throw new \Exception('Error');
```

---

## Tests

Tout comportement important doit être couvert par des tests.

Les tests doivent rester :

- lisibles ;
- indépendants ;
- déterministes ;
- rapides.

---

## Final Rule

Le code doit rester simple, lisible, testable et maintenable.

Ne pas chercher une architecture parfaite au détriment de la compréhension.
