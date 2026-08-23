<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260517002741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aprobaciones (id INT AUTO_INCREMENT NOT NULL, estatus VARCHAR(255) NOT NULL, fecha_respuesta DATETIME NOT NULL, comentarios VARCHAR(255) NOT NULL, solicitud_id INT DEFAULT NULL, aprobador_id INT DEFAULT NULL, INDEX IDX_12A1996F1CB9D6E4 (solicitud_id), UNIQUE INDEX UNIQ_12A1996F28589659 (aprobador_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE solicitudes_dcr (id INT AUTO_INCREMENT NOT NULL, nombre_documento VARCHAR(255) NOT NULL, numero_revision VARCHAR(255) NOT NULL, link_sharepoint VARCHAR(255) NOT NULL, fecha_creacion DATETIME NOT NULL, fecha_limite DATE NOT NULL, estatus VARCHAR(255) NOT NULL, originador_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_23EB57033E75FAF4 (originador_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE aprobaciones ADD CONSTRAINT FK_12A1996F1CB9D6E4 FOREIGN KEY (solicitud_id) REFERENCES solicitudes_dcr (id)');
        $this->addSql('ALTER TABLE aprobaciones ADD CONSTRAINT FK_12A1996F28589659 FOREIGN KEY (aprobador_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE solicitudes_dcr ADD CONSTRAINT FK_23EB57033E75FAF4 FOREIGN KEY (originador_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE usuario ADD id_user_fk_id INT NOT NULL, DROP correo_electronico, DROP rol, DROP password');
        $this->addSql('ALTER TABLE usuario ADD CONSTRAINT FK_2265B05DE23F625F FOREIGN KEY (id_user_fk_id) REFERENCES `user` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2265B05DE23F625F ON usuario (id_user_fk_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aprobaciones DROP FOREIGN KEY FK_12A1996F1CB9D6E4');
        $this->addSql('ALTER TABLE aprobaciones DROP FOREIGN KEY FK_12A1996F28589659');
        $this->addSql('ALTER TABLE solicitudes_dcr DROP FOREIGN KEY FK_23EB57033E75FAF4');
        $this->addSql('DROP TABLE aprobaciones');
        $this->addSql('DROP TABLE solicitudes_dcr');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('ALTER TABLE usuario DROP FOREIGN KEY FK_2265B05DE23F625F');
        $this->addSql('DROP INDEX UNIQ_2265B05DE23F625F ON usuario');
        $this->addSql('ALTER TABLE usuario ADD correo_electronico VARCHAR(255) NOT NULL, ADD rol JSON NOT NULL, ADD password VARCHAR(255) NOT NULL, DROP id_user_fk_id');
    }
}
