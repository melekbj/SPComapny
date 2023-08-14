<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230814065040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34D04547F037AB0F ON banques (tel)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_34D045475126AC48 ON banques (mail)');
        $this->addSql('ALTER TABLE pays ADD tel VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_349F3CAEF037AB0F ON pays (tel)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_349F3CAE5126AC48 ON pays (mail)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_34D04547F037AB0F ON banques');
        $this->addSql('DROP INDEX UNIQ_34D045475126AC48 ON banques');
        $this->addSql('DROP INDEX UNIQ_349F3CAEF037AB0F ON pays');
        $this->addSql('DROP INDEX UNIQ_349F3CAE5126AC48 ON pays');
        $this->addSql('ALTER TABLE pays DROP tel');
    }
}
