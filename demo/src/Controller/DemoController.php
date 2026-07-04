<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DemoController extends AbstractController
{
    #[Route('/users/{id}', name: 'user_show', requirements: ['id' => '\d+'])]
    public function showUser(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user instanceof User) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        return $this->render('demo/user_show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {

        $badges = [
            ['label' => 'Actif', 'variant' => 'success'],
            ['label' => 'En attente', 'variant' => 'warning'],
            ['label' => 'Erreur', 'variant' => 'danger'],
            ['label' => 'Information', 'variant' => 'info'],
            ['label' => 'Neutre', 'variant' => 'neutral'],
        ];

        $alerts = [
            [
                'variant' => 'success',
                'title' => 'Succès',
                'message' => 'Utilisateur créé avec succès.',
            ],
            [
                'variant' => 'warning',
                'title' => 'Attention',
                'message' => 'Certaines informations sont incomplètes.',
            ],
            [
                'variant' => 'danger',
                'title' => 'Erreur',
                'message' => 'Une erreur est survenue lors de la sauvegarde.',
            ],
            [
                'variant' => 'info',
                'title' => 'Information',
                'message' => 'Votre profil a été mis à jour.',
            ],
        ];

        $carouselItems = [
            [
                'title' => 'Symfony UX',
                'description' => 'Construire des interfaces modernes avec Symfony UX.',
                'image' => '/images/demo/carousel-1.svg',
                'alt' => 'Symfony UX demo image',
            ],
            [
                'title' => 'Preline UI',
                'description' => 'Utiliser des composants Tailwind prets a l emploi.',
                'image' => '/images/demo/carousel-2.svg',
                'alt' => 'Preline UI demo image',
            ],
            [
                'title' => 'Twig Components',
                'description' => 'Encapsuler les elements reutilisables dans des composants Twig.',
                'image' => '/images/demo/carousel-3.svg',
                'alt' => 'Twig Components demo image',
            ],
        ];

        $userTable = [
            'columns' => [
                ['key' => 'nom', 'label' => 'Nom', 'sortable' => true],
                ['key' => 'prenom', 'label' => 'Prénom', 'sortable' => true],
                ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ],
            'source' => User::class,
            'perPage' => 10,
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'Voir',
                    'route' => 'user_show',
                    'icon' => 'bi:eye',
                    'variant' => 'secondary',
                    'params' => ['id' => 'id'],
                ],
                [
                    'name' => 'edit',
                    'label' => 'Modifier',
                    'route' => 'user_show',
                    'icon' => 'bi:pencil',
                    'variant' => 'primary',
                    'params' => ['id' => 'id'],
                ],
            ],
            'filters' => [
                [
                    'name' => 'nom',
                    'label' => 'Nom',
                    'field' => 'nom',
                    'placeholder' => 'Tous les noms',
                    'autoChoices' => true
                    
                ],            
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'field' => 'email',
                    'placeholder' => 'Tous les email',
                    'autoChoices' => true
                    
                ],
            ],
        ];

        $emptyTable = [
            'columns' => $userTable['columns'],
            'source' => [],
            'rows' => [],
            'perPage' => $userTable['perPage'],
            'actions' => $userTable['actions'],
            'filters' => [],
        ];

        return $this->render('demo/index.html.twig', [
            'badges' => $badges,
            'alerts' => $alerts,
            'carouselItems' => $carouselItems,
            'userTable' => $userTable,
            'emptyTable' => $emptyTable,
        ]);
    }
}
