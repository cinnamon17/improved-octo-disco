<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012083000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creditos prepago con Telegram Stars: saldo en user, star_transaction y ai_usage_log';
    }

    public function up(Schema $schema): void
    {
        // unsigned a proposito: el motor, no solo la logica, impide saldo negativo
        $this->addSql('ALTER TABLE user ADD credits INT UNSIGNED DEFAULT 0 NOT NULL');

        $this->addSql('CREATE TABLE star_transaction (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            telegram_charge_id VARCHAR(64) NOT NULL,
            invoice_payload VARCHAR(128) NOT NULL,
            provider_charge_id VARCHAR(255) DEFAULT NULL,
            currency VARCHAR(3) NOT NULL,
            total_amount INT NOT NULL,
            credits INT NOT NULL,
            status VARCHAR(20) NOT NULL,
            raw_payload JSON NOT NULL,
            created_at DATETIME DEFAULT NULL,
            updated_at DATETIME DEFAULT NULL,
            UNIQUE INDEX UNIQ_ST_CHARGE_ID (telegram_charge_id),
            UNIQUE INDEX UNIQ_ST_PAYLOAD (invoice_payload),
            INDEX IDX_ST_USER_DATE (user_id, created_at),
            PRIMARY KEY(id),
            CONSTRAINT FK_A1025A1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE ai_usage_log (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            telegram_message_id VARCHAR(64) NOT NULL,
            mode VARCHAR(64) DEFAULT NULL,
            model VARCHAR(64) NOT NULL,
            total_tokens INT DEFAULT NULL,
            credits_reserved INT NOT NULL,
            credits_charged INT NOT NULL,
            status VARCHAR(20) NOT NULL,
            error_message VARCHAR(255) DEFAULT NULL,
            created_at DATETIME DEFAULT NULL,
            updated_at DATETIME DEFAULT NULL,
            INDEX IDX_AUL_USER_DATE (user_id, created_at),
            INDEX IDX_AUL_STATUS (status),
            PRIMARY KEY(id),
            CONSTRAINT FK_B9BDAD34A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_usage_log');
        $this->addSql('DROP TABLE star_transaction');
        $this->addSql('ALTER TABLE user DROP credits');
    }
}