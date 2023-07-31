<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230731091148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tresorie_history (id INT AUTO_INCREMENT NOT NULL, banque_id INT DEFAULT NULL, solde_r DOUBLE PRECISION NOT NULL, entree DOUBLE PRECISION NOT NULL, sortie DOUBLE PRECISION NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B72893BD37E080D9 (banque_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tresorie_history ADD CONSTRAINT FK_B72893BD37E080D9 FOREIGN KEY (banque_id) REFERENCES banques (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tresorie_history DROP FOREIGN KEY FK_B72893BD37E080D9');
        $this->addSql('DROP TABLE tresorie_history');
    }
}
