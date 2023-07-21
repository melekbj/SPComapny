<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230721075933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE UNIQUE INDEX UNIQ_2055F1FBA4D60759 ON categorie_materiel (libelle)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9C1EBE69146F3EA3 ON materiels (ref)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('DROP INDEX UNIQ_2055F1FBA4D60759 ON categorie_materiel');
        $this->addSql('DROP INDEX UNIQ_9C1EBE69146F3EA3 ON materiels');
    }
}
