<?php

namespace ChrisDev\UxComponents\Twig\Components;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'Navbar',
    template: '@ChrisDevUxComponents/components/Navbar.html.twig'
)]
final class Navbar
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security,
    ) {
    }

    /**
     * @return array{label: string, route: string, logo: string}
     */
    public function getBrand(): array
    {
        return [
            'label' => 'Symfony Ux Starter Kit',
            'route' => 'app_home',
            'logo' => 'bi:boxes',
        ];
    }

    /**
     * @return list<array{label: string, route: string, icon: string, authenticated?: bool}>
     */
    public function getItems(): array
    {
        return [
            [
                'label' => 'Accueil',
                'route' => 'app_home',
                'icon' => 'bi:house',
            ],
            [
                'label' => 'Compte',
                'route' => 'app_account',
                'icon' => 'bi:person',
                'authenticated' => true,
            ],
        ];
    }

    /**
     * @return list<array{label: string, route: string, icon: string, authenticated: bool}>
     */
    public function getUserItems(): array
    {
        if ($this->isAuthenticated()) {
            return [
                [
                    'label' => 'Logout',
                    'route' => 'app_logout',
                    'icon' => 'bi:box-arrow-right',
                    'authenticated' => true,
                ],
            ];
        }

        return [
            [
                'label' => 'Login',
                'route' => 'app_login',
                'icon' => 'bi:box-arrow-in-right',
                'authenticated' => false,
            ],
        ];
    }

    /**
     * @param array{authenticated?: bool} $item
     */
    public function isVisible(array $item): bool
    {
        return !($item['authenticated'] ?? false) || $this->isAuthenticated();
    }

    public function isActive(string $route): bool
    {
        return $this->getCurrentRoute() === $route;
    }

    public function getLinkClasses(string $route): string
    {
        if ($this->isActive($route)) {
            return 'inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white';
        }

        return 'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 hover:bg-slate-900 hover:text-white';
    }

    public function getMobileLinkClasses(string $route): string
    {
        if ($this->isActive($route)) {
            return 'flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-2 text-sm font-medium text-white';
        }

        return 'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 hover:bg-slate-900 hover:text-white';
    }

    private function getCurrentRoute(): ?string
    {
        $route = $this->requestStack->getCurrentRequest()?->attributes->get('_route');

        return is_string($route) ? $route : null;
    }

    private function isAuthenticated(): bool
    {
        return $this->security->getUser() !== null;
    }
}
