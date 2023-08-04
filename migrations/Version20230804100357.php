<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230804100357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tresorie_history ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tresorie_history ADD CONSTRAINT FK_B72893BDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B72893BDA76ED395 ON tresorie_history (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tresorie_history DROP FOREIGN KEY FK_B72893BDA76ED395');
        $this->addSql('DROP INDEX IDX_B72893BDA76ED395 ON tresorie_history');
        $this->addSql('ALTER TABLE tresorie_history DROP user_id');
    }
}
