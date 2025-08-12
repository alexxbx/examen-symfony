<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250812112454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE achievement (id SERIAL NOT NULL, user_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, required_lessons INT NOT NULL, icon VARCHAR(255) NOT NULL, unlocked_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_96737FF1A76ED395 ON achievement (user_id)');
        $this->addSql('CREATE TABLE chat_message (id SERIAL NOT NULL, user_id INT NOT NULL, message TEXT NOT NULL, is_from_user BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FAB3FC16A76ED395 ON chat_message (user_id)');
        $this->addSql('COMMENT ON COLUMN chat_message.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE exercise (id SERIAL NOT NULL, lesson_id INT DEFAULT NULL, question VARCHAR(255) NOT NULL, options JSON NOT NULL, answer VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, cours TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AEDAD51CCDF80196 ON exercise (lesson_id)');
        $this->addSql('CREATE TABLE game_session (id SERIAL NOT NULL, host_id INT NOT NULL, guest_id INT DEFAULT NULL, winner_id INT DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ended_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, host_score INT DEFAULT NULL, guest_score INT DEFAULT NULL, game_data JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4586AAFB1FB8D185 ON game_session (host_id)');
        $this->addSql('CREATE INDEX IDX_4586AAFB9A4AA658 ON game_session (guest_id)');
        $this->addSql('CREATE INDEX IDX_4586AAFB5DFCD4B8 ON game_session (winner_id)');
        $this->addSql('COMMENT ON COLUMN game_session.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN game_session.started_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN game_session.ended_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE leaderboard (id SERIAL NOT NULL, user_id INT NOT NULL, total_games INT NOT NULL, wins INT NOT NULL, losses INT NOT NULL, draws INT NOT NULL, total_points INT NOT NULL, last_updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_182E5253A76ED395 ON leaderboard (user_id)');
        $this->addSql('COMMENT ON COLUMN leaderboard.last_updated IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE lesson (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, content TEXT NOT NULL, level VARCHAR(255) DEFAULT NULL, "order" INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN lesson.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE progression (id SERIAL NOT NULL, user_id INT NOT NULL, lesson_id INT NOT NULL, completed BOOLEAN NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, unlocked BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D5B25073A76ED395 ON progression (user_id)');
        $this->addSql('CREATE INDEX IDX_D5B25073CDF80196 ON progression (lesson_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, username VARCHAR(20) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX unique_username ON "user" (username)');
        $this->addSql('CREATE UNIQUE INDEX unique_email ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE achievement ADD CONSTRAINT FK_96737FF1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE exercise ADD CONSTRAINT FK_AEDAD51CCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_session ADD CONSTRAINT FK_4586AAFB1FB8D185 FOREIGN KEY (host_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_session ADD CONSTRAINT FK_4586AAFB9A4AA658 FOREIGN KEY (guest_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_session ADD CONSTRAINT FK_4586AAFB5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE leaderboard ADD CONSTRAINT FK_182E5253A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE progression ADD CONSTRAINT FK_D5B25073A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE progression ADD CONSTRAINT FK_D5B25073CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE achievement DROP CONSTRAINT FK_96737FF1A76ED395');
        $this->addSql('ALTER TABLE chat_message DROP CONSTRAINT FK_FAB3FC16A76ED395');
        $this->addSql('ALTER TABLE exercise DROP CONSTRAINT FK_AEDAD51CCDF80196');
        $this->addSql('ALTER TABLE game_session DROP CONSTRAINT FK_4586AAFB1FB8D185');
        $this->addSql('ALTER TABLE game_session DROP CONSTRAINT FK_4586AAFB9A4AA658');
        $this->addSql('ALTER TABLE game_session DROP CONSTRAINT FK_4586AAFB5DFCD4B8');
        $this->addSql('ALTER TABLE leaderboard DROP CONSTRAINT FK_182E5253A76ED395');
        $this->addSql('ALTER TABLE progression DROP CONSTRAINT FK_D5B25073A76ED395');
        $this->addSql('ALTER TABLE progression DROP CONSTRAINT FK_D5B25073CDF80196');
        $this->addSql('DROP TABLE achievement');
        $this->addSql('DROP TABLE chat_message');
        $this->addSql('DROP TABLE exercise');
        $this->addSql('DROP TABLE game_session');
        $this->addSql('DROP TABLE leaderboard');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE progression');
        $this->addSql('DROP TABLE "user"');
    }
}
