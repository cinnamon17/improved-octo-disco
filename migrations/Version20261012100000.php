<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Amplia star_transaction.telegram_charge_id a VARCHAR(255): los charge id de Stars miden ~110 chars';
    }

    public function up(Schema $schema): void
    {
        // El token real de un pago con Stars (telegram_payment_charge_id)
        // ronda los 110 caracteres. Con VARCHAR(64) el INSERT del
        // successful_payment reventaba con SQLSTATE 22001 y el pago se perdia.
        $this->addSql('ALTER TABLE star_transaction MODIFY telegram_charge_id VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revertir reintroduce el bug: cualquier charge id de Stars real
        // vuelve a no caber y a perder el pago.
        $this->addSql('ALTER TABLE star_transaction MODIFY telegram_charge_id VARCHAR(64) NOT NULL');
    }
}