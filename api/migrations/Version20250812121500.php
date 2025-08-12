<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour corriger les séquences et commentaires manquants
 */
final class Version20250812121500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correction des séquences et commentaires manquants';
    }

    public function up(Schema $schema): void
    {
        // Vérifier et créer les séquences si elles n'existent pas
        $this->addSql('DO $$ BEGIN CREATE SEQUENCE IF NOT EXISTS achievement_id_seq; EXCEPTION WHEN duplicate_object THEN null; END $$;');
        $this->addSql('DO $$ BEGIN CREATE SEQUENCE IF NOT EXISTS chat_message_id_seq; EXCEPTION WHEN duplicate_object THEN null; END $$;');
        $this->addSql('DO $$ BEGIN CREATE SEQUENCE IF NOT EXISTS exercise_id_seq; EXCEPTION WHEN duplicate_object THEN null; END $$;');
        $this->addSql('DO $$ BEGIN CREATE SEQUENCE IF NOT EXISTS game_session_id_seq; EXCEPTION WHEN duplicate_object THEN null; END $$;');
        $this->addSql('DO $$ BEGIN CREATE SEQUENCE IF NOT EXISTS leaderboard_id_seq; EXCEPTION WHEN duplicate_object THEN null; END $$;');
        $this->addSql('DO $$ BEGIN CREATE SEQUENCE IF NOT EXISTS lesson_id_seq; EXCEPTION WHEN duplicate_object THEN null; END $$;');
        $this->addSql('DO $$ BEGIN CREATE SEQUENCE IF NOT EXISTS progression_id_seq; EXCEPTION WHEN duplicate_object THEN null; END $$;');
        $this->addSql('DO $$ BEGIN CREATE SEQUENCE IF NOT EXISTS user_id_seq; EXCEPTION WHEN duplicate_object THEN null; END $$;');
        
        // Mettre à jour les séquences avec les valeurs maximales existantes
        $this->addSql('SELECT setval(\'achievement_id_seq\', COALESCE((SELECT MAX(id) FROM achievement), 1))');
        $this->addSql('SELECT setval(\'chat_message_id_seq\', COALESCE((SELECT MAX(id) FROM chat_message), 1))');
        $this->addSql('SELECT setval(\'exercise_id_seq\', COALESCE((SELECT MAX(id) FROM exercise), 1))');
        $this->addSql('SELECT setval(\'game_session_id_seq\', COALESCE((SELECT MAX(id) FROM game_session), 1))');
        $this->addSql('SELECT setval(\'leaderboard_id_seq\', COALESCE((SELECT MAX(id) FROM leaderboard), 1))');
        $this->addSql('SELECT setval(\'lesson_id_seq\', COALESCE((SELECT MAX(id) FROM lesson), 1))');
        $this->addSql('SELECT setval(\'progression_id_seq\', COALESCE((SELECT MAX(id) FROM progression), 1))');
        $this->addSql('SELECT setval(\'user_id_seq\', COALESCE((SELECT MAX(id) FROM "user"), 1))');
        
        // Définir les séquences comme valeurs par défaut pour les colonnes id (si ce ne sont pas des colonnes d'identité)
        $this->addSql('DO $$ BEGIN ALTER TABLE achievement ALTER id SET DEFAULT nextval(\'achievement_id_seq\'); EXCEPTION WHEN others THEN null; END $$;');
        $this->addSql('DO $$ BEGIN ALTER TABLE chat_message ALTER id SET DEFAULT nextval(\'chat_message_id_seq\'); EXCEPTION WHEN others THEN null; END $$;');
        $this->addSql('DO $$ BEGIN ALTER TABLE exercise ALTER id SET DEFAULT nextval(\'exercise_id_seq\'); EXCEPTION WHEN others THEN null; END $$;');
        $this->addSql('DO $$ BEGIN ALTER TABLE game_session ALTER id SET DEFAULT nextval(\'game_session_id_seq\'); EXCEPTION WHEN others THEN null; END $$;');
        $this->addSql('DO $$ BEGIN ALTER TABLE leaderboard ALTER id SET DEFAULT nextval(\'leaderboard_id_seq\'); EXCEPTION WHEN others THEN null; END $$;');
        $this->addSql('DO $$ BEGIN ALTER TABLE lesson ALTER id SET DEFAULT nextval(\'lesson_id_seq\'); EXCEPTION WHEN others THEN null; END $$;');
        $this->addSql('DO $$ BEGIN ALTER TABLE progression ALTER id SET DEFAULT nextval(\'progression_id_seq\'); EXCEPTION WHEN others THEN null; END $$;');
        $this->addSql('DO $$ BEGIN ALTER TABLE "user" ALTER id SET DEFAULT nextval(\'user_id_seq\'); EXCEPTION WHEN others THEN null; END $$;');
        
        // Ajouter les commentaires sur les colonnes datetime
        $this->addSql('COMMENT ON COLUMN chat_message.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN game_session.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN game_session.started_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN game_session.ended_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN leaderboard.last_updated IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN lesson.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        
        // Renommer les index si nécessaire
        $this->addSql('DO $$ BEGIN ALTER INDEX user_username_key RENAME TO unique_username; EXCEPTION WHEN undefined_object THEN null; END $$;');
        $this->addSql('DO $$ BEGIN ALTER INDEX user_email_key RENAME TO unique_email; EXCEPTION WHEN undefined_object THEN null; END $$;');
    }

    public function down(Schema $schema): void
    {
        // Supprimer les commentaires
        $this->addSql('COMMENT ON COLUMN chat_message.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN game_session.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN game_session.started_at IS NULL');
        $this->addSql('COMMENT ON COLUMN game_session.ended_at IS NULL');
        $this->addSql('COMMENT ON COLUMN leaderboard.last_updated IS NULL');
        $this->addSql('COMMENT ON COLUMN lesson.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS NULL');
        
        // Supprimer les valeurs par défaut
        $this->addSql('ALTER TABLE achievement ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE chat_message ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE exercise ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE game_session ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE leaderboard ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE lesson ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE progression ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ALTER id DROP DEFAULT');
        
        // Renommer les index en arrière
        $this->addSql('DO $$ BEGIN ALTER INDEX unique_username RENAME TO user_username_key; EXCEPTION WHEN undefined_object THEN null; END $$;');
        $this->addSql('DO $$ BEGIN ALTER INDEX unique_email RENAME TO user_email_key; EXCEPTION WHEN undefined_object THEN null; END $$;');
    }
}
