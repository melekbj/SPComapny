<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230816121616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tresorie_history (id INT AUTO_INCREMENT NOT NULL, devise_id INT DEFAULT NULL, banque_id INT DEFAULT NULL, pays_id INT DEFAULT NULL, user_id INT DEFAULT NULL, solde_r DOUBLE PRECISION DEFAULT NULL, montant DOUBLE PRECISION DEFAULT NULL, description VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B72893BDF4445056 (devise_id), INDEX IDX_B72893BD37E080D9 (banque_id), INDEX IDX_B72893BDA6E44244 (pays_id), INDEX IDX_B72893BDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tresorie_history ADD CONSTRAINT FK_B72893BDF4445056 FOREIGN KEY (devise_id) REFERENCES devise (id)');
        $this->addSql('ALTER TABLE tresorie_history ADD CONSTRAINT FK_B72893BD37E080D9 FOREIGN KEY (banque_id) REFERENCES banques (id)');
        $this->addSql('ALTER TABLE tresorie_history ADD CONSTRAINT FK_B72893BDA6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id)');
        $this->addSql('ALTER TABLE tresorie_history ADD CONSTRAINT FK_B72893BDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tresorie_history DROP FOREIGN KEY FK_B72893BDF4445056');
        $this->addSql('ALTER TABLE tresorie_history DROP FOREIGN KEY FK_B72893BD37E080D9');
        $this->addSql('ALTER TABLE tresorie_history DROP FOREIGN KEY FK_B72893BDA6E44244');
        $this->addSql('ALTER TABLE tresorie_history DROP FOREIGN KEY FK_B72893BDA76ED395');
        $this->addSql('DROP TABLE tresorie_history');
    }
}
