<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522120845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE leaderboard (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, total_games INT NOT NULL, wins INT NOT NULL, losses INT NOT NULL, draws INT NOT NULL, total_points INT NOT NULL, last_updated DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_182E5253A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE leaderboard ADD CONSTRAINT FK_182E5253A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE leaderboard DROP FOREIGN KEY FK_182E5253A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE leaderboard
        SQL);
    }
}
