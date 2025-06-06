<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522123203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE game_session ADD winner_id INT DEFAULT NULL, DROP winner
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_session ADD CONSTRAINT FK_4586AAFB5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4586AAFB5DFCD4B8 ON game_session (winner_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE game_session DROP FOREIGN KEY FK_4586AAFB5DFCD4B8
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4586AAFB5DFCD4B8 ON game_session
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE game_session ADD winner VARCHAR(50) DEFAULT NULL, DROP winner_id
        SQL);
    }
}
