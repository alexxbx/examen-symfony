<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration de nettoyage pour Render - supprime les tables en conflit
 */
final class Version20250812121700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Nettoyage des tables en conflit pour déploiement Render';
    }

    public function up(Schema $schema): void
    {
        // Supprimer les tables qui peuvent causer des conflits
        $this->addSql('DROP TABLE IF EXISTS notification CASCADE');
        $this->addSql('DROP TABLE IF EXISTS achievement CASCADE');
        $this->addSql('DROP TABLE IF EXISTS chat_message CASCADE');
        $this->addSql('DROP TABLE IF EXISTS exercise CASCADE');
        $this->addSql('DROP TABLE IF EXISTS game_session CASCADE');
        $this->addSql('DROP TABLE IF EXISTS leaderboard CASCADE');
        $this->addSql('DROP TABLE IF EXISTS lesson CASCADE');
        $this->addSql('DROP TABLE IF EXISTS progression CASCADE');
        $this->addSql('DROP TABLE IF EXISTS "user" CASCADE');
        
        // Supprimer les séquences associées
        $this->addSql('DROP SEQUENCE IF EXISTS notification_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS achievement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS chat_message_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS exercise_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS game_session_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS leaderboard_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS lesson_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS progression_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS user_id_seq CASCADE');
        
        // Vider la table des migrations pour repartir de zéro
        $this->addSql('DELETE FROM doctrine_migration_versions');
    }

    public function down(Schema $schema): void
    {
        // Cette migration ne peut pas être annulée car elle supprime toutes les données
        // pour permettre un déploiement propre
    }
}
