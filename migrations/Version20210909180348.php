<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210909180348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE empresa CHANGE nombre nombre VARCHAR(40) NOT NULL, CHANGE telefono telefono VARCHAR(50) NOT NULL, CHANGE email email VARCHAR(200) NOT NULL, CHANGE sector sector INT NOT NULL');
        $this->addSql('ALTER TABLE empresa ADD CONSTRAINT FK_B8D75A504BA3D9E8 FOREIGN KEY (sector) REFERENCES sector (id)');
        $this->addSql('CREATE INDEX IDX_B8D75A504BA3D9E8 ON empresa (sector)');
        $this->addSql('CREATE UNIQUE INDEX empresa_nombre_unique_idx ON empresa (nombre)');
        $this->addSql('CREATE UNIQUE INDEX empresa_email_unique_idx ON empresa (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE empresa DROP FOREIGN KEY FK_B8D75A504BA3D9E8');
        $this->addSql('DROP INDEX IDX_B8D75A504BA3D9E8 ON empresa');
        $this->addSql('DROP INDEX empresa_nombre_unique_idx ON empresa');
        $this->addSql('DROP INDEX empresa_email_unique_idx ON empresa');
        $this->addSql('ALTER TABLE empresa CHANGE sector sector VARCHAR(80) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE nombre nombre VARCHAR(40) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE telefono telefono VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(200) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
