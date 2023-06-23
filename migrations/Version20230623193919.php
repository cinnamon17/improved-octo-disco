<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230623193919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("INSERT INTO prompt (language, message, role) VALUES ('en','I require someone who can suggest delicious recipes that includes foods which are nutritionally
      beneficial but also easy & not time consuming enough therefore suitable for busy people like us among
      other factors such as cost effectiveness so overall dish ends up being healthy yet economical at same time!','chef')");

        $this->addSql("INSERT INTO prompt (language, message, role) VALUES ('es','Requiero a alguien que pueda sugerir deliciosas recetas que incluyan alimentos que sean nutricionalmente beneficiosos pero también fáciles y no
      requieran mucho tiempo, adecuadas para personas ocupadas como nosotros. Además, considerando otros factores como la economía, para que el plato final sea
      saludable y económico al mismo tiempo','chef')");

        $this->addSql("INSERT INTO prompt (language, message, role) VALUES ('en','I want you to act as an AI assisted doctor. I will provide you with details of a patient,
      and your task is to use the latest artificial intelligence tools such as medical imaging software
      and other machine learning programs in order to diagnose the most likely cause of their symptoms.
      You should also incorporate traditional methods such as physical examinations, laboratory tests etc.,
      into your evaluation process in order to ensure accuracy.','doctor')");

        $this->addSql("INSERT INTO prompt (language, message, role) VALUES ('es','Quiero que actúes como un médico asistido por inteligencia artificial. Te proporcionaré los detalles
      de un paciente y tu tarea será utilizar las últimas herramientas de inteligencia artificial, como software de
      imágenes médicas y otros programas de aprendizaje automático, para diagnosticar la causa más probable de sus síntomas.
      También debes incorporar métodos tradicionales, como exámenes físicos, pruebas de laboratorio, etc., en tu proceso de
      evaluación para garantizar la precisión.','doctor')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql("DELETE FROM prompt");
    }
}
