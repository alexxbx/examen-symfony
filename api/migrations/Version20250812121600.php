<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration finale pour nettoyer les séquences en double
 */
final class Version20250812121600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Nettoyage des séquences en double et finalisation de la synchronisation';
    }

    public function up(Schema $schema): void
    {
        // Supprimer les séquences en double qui causent des conflits
        $this->addSql('DROP SEQUENCE IF EXISTS achievement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS chat_message_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS exercise_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS game_session_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS leaderboard_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS lesson_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS progression_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS user_id_seq CASCADE');
        
        // Les tables utilisent SERIAL, donc les séquences sont automatiquement gérées par PostgreSQL
        // Pas besoin de créer des séquences séparées
    }

    public function down(Schema $schema): void
    {
        // Cette migration ne peut pas être annulée car elle supprime des séquences en double
        // qui causaient des conflits
    }
}
