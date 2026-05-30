<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260527041801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE area (id INT AUTO_INCREMENT NOT NULL, nombre_area VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE usuario ADD area_id INT NOT NULL, DROP area, DROP supervisor');
        $this->addSql('ALTER TABLE usuario ADD CONSTRAINT FK_2265B05DBD0F409C FOREIGN KEY (area_id) REFERENCES area (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2265B05DBD0F409C ON usuario (area_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE area');
        $this->addSql('ALTER TABLE usuario DROP FOREIGN KEY FK_2265B05DBD0F409C');
        $this->addSql('DROP INDEX UNIQ_2265B05DBD0F409C ON usuario');
        $this->addSql('ALTER TABLE usuario ADD area VARCHAR(255) NOT NULL, ADD supervisor VARCHAR(255) DEFAULT NULL, DROP area_id');
    }
}
