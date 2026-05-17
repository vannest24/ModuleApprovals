<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260517062249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE solicitudes_dcr DROP INDEX UNIQ_23EB57033E75FAF4, ADD INDEX IDX_23EB57033E75FAF4 (originador_id)');
        $this->addSql('ALTER TABLE solicitudes_dcr CHANGE originador_id originador_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE solicitudes_dcr DROP INDEX IDX_23EB57033E75FAF4, ADD UNIQUE INDEX UNIQ_23EB57033E75FAF4 (originador_id)');
        $this->addSql('ALTER TABLE solicitudes_dcr CHANGE originador_id originador_id INT DEFAULT NULL');
    }
}
