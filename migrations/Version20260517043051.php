<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260517043051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE solicitudes_dcr_user (solicitudes_dcr_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_DAD2A3B9A6258B29 (solicitudes_dcr_id), INDEX IDX_DAD2A3B9A76ED395 (user_id), PRIMARY KEY (solicitudes_dcr_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE solicitudes_dcr_user ADD CONSTRAINT FK_DAD2A3B9A6258B29 FOREIGN KEY (solicitudes_dcr_id) REFERENCES solicitudes_dcr (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE solicitudes_dcr_user ADD CONSTRAINT FK_DAD2A3B9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE solicitudes_dcr_user DROP FOREIGN KEY FK_DAD2A3B9A6258B29');
        $this->addSql('ALTER TABLE solicitudes_dcr_user DROP FOREIGN KEY FK_DAD2A3B9A76ED395');
        $this->addSql('DROP TABLE solicitudes_dcr_user');
    }
}
