<?php

namespace ChrisDev\UxComponents\Twig\Components;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'UxNavbar',
    template: '@ChrisDevUxComponents/components/Navbar.html.twig'
)]
final class Navbar
{
    /**
     * @param array{label: string, route: string, logo: string} $brand
     * @param list<array{label: string, route: string, icon: string, authenticated?: bool}> $items
     * @param array{logged_in: array{label: string, route: string, icon: string}, logged_out: array{label: string, route: string, icon: string}} $userItems
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ?Security $security = null,
        #[Autowire(param: 'chris_dev_ux_components.navbar.brand')]
        private readonly array $brand = ['label' => 'Symfony Ux Starter Kit', 'route' => 'app_home', 'logo' => 'bi:boxes'],
        #[Autowire(param: 'chris_dev_ux_components.navbar.items')]
        private readonly array $items = [],
        #[Autowire(param: 'chris_dev_ux_components.navbar.user_items')]
        private readonly array $userItems = [
            'logged_in' => ['label' => 'Logout', 'route' => 'app_logout', 'icon' => 'bi:box-arrow-right'],
            'logged_out' => ['label' => 'Login', 'route' => 'app_login', 'icon' => 'bi:box-arrow-in-right'],
        ],
    ) {
    }

    /**
     * @return array{label: string, route: string, logo: string}
     */
    public function getBrand(): array
    {
        return $this->brand;
    }

    /**
     * @return list<array{label: string, route: string, icon: string, authenticated?: bool}>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return list<array{label: string, route: string, icon: string, authenticated: bool}>
     */
    public function getUserItems(): array
    {
        $userItem = $this->isAuthenticated() ? $this->userItems['logged_in'] : $this->userItems['logged_out'];

        return [
            [
                'label' => $userItem['label'],
                'route' => $userItem['route'],
                'icon' => $userItem['icon'],
                'authenticated' => $this->isAuthenticated(),
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
        return $this->security?->getUser() !== null;
    }
}
