<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260927155123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea la tabla ical_calendar para el fusionador de calendarios';
    }

    public function up(Schema $schema): void
    {
        // Solo creamos la tabla propia de esta feature
        $this->addSql('CREATE TABLE ical_calendar (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(64) NOT NULL, sources JSON NOT NULL, sync_interval INT NOT NULL, last_synced_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_253F75425F37A13B (token), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ical_calendar');
    }
}
