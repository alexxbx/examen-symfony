<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250812144610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_id_seq');
        $this->addSql('SELECT setval(\'user_id_seq\', (SELECT MAX(id) FROM "user"))');
        $this->addSql('ALTER TABLE "user" ALTER id SET DEFAULT nextval(\'user_id_seq\')');
        $this->addSql('CREATE SEQUENCE achievement_id_seq');
        $this->addSql('SELECT setval(\'achievement_id_seq\', (SELECT MAX(id) FROM achievement))');
        $this->addSql('ALTER TABLE achievement ALTER id SET DEFAULT nextval(\'achievement_id_seq\')');
        $this->addSql('CREATE SEQUENCE chat_message_id_seq');
        $this->addSql('SELECT setval(\'chat_message_id_seq\', (SELECT MAX(id) FROM chat_message))');
        $this->addSql('ALTER TABLE chat_message ALTER id SET DEFAULT nextval(\'chat_message_id_seq\')');
        $this->addSql('CREATE SEQUENCE exercise_id_seq');
        $this->addSql('SELECT setval(\'exercise_id_seq\', (SELECT MAX(id) FROM exercise))');
        $this->addSql('ALTER TABLE exercise ALTER id SET DEFAULT nextval(\'exercise_id_seq\')');
        $this->addSql('CREATE SEQUENCE game_session_id_seq');
        $this->addSql('SELECT setval(\'game_session_id_seq\', (SELECT MAX(id) FROM game_session))');
        $this->addSql('ALTER TABLE game_session ALTER id SET DEFAULT nextval(\'game_session_id_seq\')');
        $this->addSql('CREATE SEQUENCE leaderboard_id_seq');
        $this->addSql('SELECT setval(\'leaderboard_id_seq\', (SELECT MAX(id) FROM leaderboard))');
        $this->addSql('ALTER TABLE leaderboard ALTER id SET DEFAULT nextval(\'leaderboard_id_seq\')');
        $this->addSql('CREATE SEQUENCE lesson_id_seq');
        $this->addSql('SELECT setval(\'lesson_id_seq\', (SELECT MAX(id) FROM lesson))');
        $this->addSql('ALTER TABLE lesson ALTER id SET DEFAULT nextval(\'lesson_id_seq\')');
        $this->addSql('CREATE SEQUENCE progression_id_seq');
        $this->addSql('SELECT setval(\'progression_id_seq\', (SELECT MAX(id) FROM progression))');
        $this->addSql('ALTER TABLE progression ALTER id SET DEFAULT nextval(\'progression_id_seq\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE game_session ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE leaderboard ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE exercise ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE achievement ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE lesson ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE progression ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE chat_message ALTER id DROP DEFAULT');
    }
}
