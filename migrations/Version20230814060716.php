<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230814060716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE banques ADD responsable VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tresorie_history DROP FOREIGN KEY FK_B72893BDA6E44244');
        $this->addSql('DROP INDEX IDX_B72893BDA6E44244 ON tresorie_history');
        $this->addSql('ALTER TABLE tresorie_history DROP pays_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE banques DROP responsable');
        $this->addSql('ALTER TABLE tresorie_history ADD pays_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tresorie_history ADD CONSTRAINT FK_B72893BDA6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id)');
        $this->addSql('CREATE INDEX IDX_B72893BDA6E44244 ON tresorie_history (pays_id)');
    }
}
