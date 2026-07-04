<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260609192000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add generic profile fields to app_user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user ADD COLUMN IF NOT EXISTS nom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD COLUMN IF NOT EXISTS prenom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD COLUMN IF NOT EXISTS biography TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD COLUMN IF NOT EXISTS localisation JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS nom');
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS prenom');
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS avatar');
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS biography');
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS localisation');
    }
}
