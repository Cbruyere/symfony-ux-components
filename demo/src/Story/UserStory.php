<?php

namespace App\Story;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'user')]
final class UserStory extends Story
{
    public function build(): void
    {
        UserFactory::createMany(500);
    }
}
