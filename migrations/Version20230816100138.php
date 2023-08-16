<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230816100138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE devise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tresorie ADD `desc` VARCHAR(255) DEFAULT NULL, DROP entree, DROP sortie, DROP desc_e, DROP desc_s, DROP devise_e, DROP devise_s');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE devise');
        $this->addSql('ALTER TABLE tresorie ADD entree DOUBLE PRECISION DEFAULT NULL, ADD sortie DOUBLE PRECISION DEFAULT NULL, ADD desc_s VARCHAR(255) DEFAULT NULL, ADD devise_e VARCHAR(255) DEFAULT NULL, ADD devise_s VARCHAR(255) DEFAULT NULL, CHANGE `desc` desc_e VARCHAR(255) DEFAULT NULL');
    }
}
