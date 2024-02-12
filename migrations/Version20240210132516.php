<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240210132516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD password_change_hash VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE login login VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE jelszo jelszo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE jogok jogok VARCHAR(200) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE regdatum regdatum DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE lastlogin lastlogin DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE lastactive lastactive DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE notifications notifications TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE becenev becenev VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE nev nev VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE volunteer volunteer TINYINT(1) NOT NULL');

        $this->addSql('ALTER TABLE user ADD password_change_deadline DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
