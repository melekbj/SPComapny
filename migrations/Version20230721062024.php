<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230721062024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE materiels ADD categorie_id INT DEFAULT NULL, ADD quantite INT DEFAULT NULL');
        $this->addSql('ALTER TABLE materiels ADD CONSTRAINT FK_9C1EBE69BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_materiel (id)');
        $this->addSql('CREATE INDEX IDX_9C1EBE69BCF5E72D ON materiels (categorie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE materiels DROP FOREIGN KEY FK_9C1EBE69BCF5E72D');
        $this->addSql('DROP INDEX IDX_9C1EBE69BCF5E72D ON materiels');
        $this->addSql('ALTER TABLE materiels DROP categorie_id, DROP quantite');
    }
}
