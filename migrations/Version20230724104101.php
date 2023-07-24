<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230724104101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tresorie ADD pays_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tresorie ADD CONSTRAINT FK_6D539BA5A6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id)');
        $this->addSql('CREATE INDEX IDX_6D539BA5A6E44244 ON tresorie (pays_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tresorie DROP FOREIGN KEY FK_6D539BA5A6E44244');
        $this->addSql('DROP INDEX IDX_6D539BA5A6E44244 ON tresorie');
        $this->addSql('ALTER TABLE tresorie DROP pays_id');
    }
}
