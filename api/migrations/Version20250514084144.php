<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250514084144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE exercise ADD COLUMN cours TEXT DEFAULT NULL');
    }
    
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE exercise DROP COLUMN cours');
    }
    
}
