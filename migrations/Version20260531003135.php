<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260531003135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE solicitudes_cdr (id INT AUTO_INCREMENT NOT NULL, ruta_archivo VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE solicitudes_dcr ADD razon_cambio VARCHAR(255) NOT NULL, ADD archivo_adjunto VARCHAR(255) NOT NULL, ADD id_documento_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE solicitudes_dcr ADD CONSTRAINT FK_23EB57036601BA07 FOREIGN KEY (id_documento_id) REFERENCES documento (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_23EB57036601BA07 ON solicitudes_dcr (id_documento_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE solicitudes_cdr');
        $this->addSql('ALTER TABLE solicitudes_dcr DROP FOREIGN KEY FK_23EB57036601BA07');
        $this->addSql('DROP INDEX UNIQ_23EB57036601BA07 ON solicitudes_dcr');
        $this->addSql('ALTER TABLE solicitudes_dcr DROP razon_cambio, DROP archivo_adjunto, DROP id_documento_id');
    }
}
