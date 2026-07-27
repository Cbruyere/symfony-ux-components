<?php

declare(strict_types=1);

namespace ChrisDev\UxComponents\Tests\Twig\Components;

use ChrisDev\UxComponents\Twig\Components\Navbar;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

final class NavbarTest extends TestCase
{
    public function testIsActiveMatchesCurrentRoute(): void
    {
        $navbar = $this->createNavbar($this->requestStackWithRoute('app_home'));

        self::assertTrue($navbar->isActive('app_home'));
        self::assertFalse($navbar->isActive('app_account'));
    }

    public function testWithoutSecurityUserIsNeverAuthenticated(): void
    {
        $navbar = $this->createNavbar($this->requestStackWithRoute('app_home'), security: null);

        self::assertFalse($navbar->isVisible(['authenticated' => true]));
        self::assertTrue($navbar->isVisible(['authenticated' => false]));
        self::assertSame('Login', $navbar->getUserItems()[0]['label']);
    }

    public function testWithAuthenticatedUser(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->createMock(UserInterface::class));

        $navbar = $this->createNavbar($this->requestStackWithRoute('app_home'), $security);

        self::assertTrue($navbar->isVisible(['authenticated' => true]));
        self::assertSame('Logout', $navbar->getUserItems()[0]['label']);
    }

    public function testMissingLoggedOutUserItemReturnsNoUserItem(): void
    {
        $navbar = new Navbar(
            $this->requestStackWithRoute('app_home'),
            null,
            userItems: ['logged_in' => ['label' => 'Logout', 'route' => 'app_logout', 'icon' => 'bi:box-arrow-right']],
        );

        self::assertSame([], $navbar->getUserItems());
    }

    public function testMissingLoggedInUserItemReturnsNoUserItem(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->createMock(UserInterface::class));

        $navbar = new Navbar(
            $this->requestStackWithRoute('app_home'),
            $security,
            userItems: ['logged_out' => ['label' => 'Login', 'route' => 'app_login', 'icon' => 'bi:box-arrow-in-right']],
        );

        self::assertSame([], $navbar->getUserItems());
    }

    public function testEmptyUserItemsReturnsNoUserItem(): void
    {
        $navbar = new Navbar(
            $this->requestStackWithRoute('app_home'),
            null,
            userItems: [],
        );

        self::assertSame([], $navbar->getUserItems());
    }

    public function testCustomConfigurationIsExposed(): void
    {
        $navbar = new Navbar(
            $this->requestStackWithRoute('app_home'),
            null,
            ['label' => 'My App', 'route' => 'app_home', 'logo' => 'bi:app'],
            [['label' => 'Dashboard', 'route' => 'app_dashboard', 'icon' => 'bi:speedometer']],
        );

        self::assertSame('My App', $navbar->getBrand()['label']);
        self::assertSame('Dashboard', $navbar->getItems()[0]['label']);
    }

    public function testGetLinkClassesReflectsActiveState(): void
    {
        $navbar = $this->createNavbar($this->requestStackWithRoute('app_home'));

        self::assertStringContainsString('bg-slate-800', $navbar->getLinkClasses('app_home'));
        self::assertStringNotContainsString('bg-slate-800', $navbar->getLinkClasses('app_account'));
    }

    private function requestStackWithRoute(string $route): RequestStack
    {
        $request = Request::create('/');
        $request->attributes->set('_route', $route);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        return $requestStack;
    }

    private function createNavbar(RequestStack $requestStack, ?Security $security = null): Navbar
    {
        return new Navbar($requestStack, $security);
    }
}
