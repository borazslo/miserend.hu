<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240131233452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ENGINE = InnoDB');

        $this->addSql('ALTER TABLE favorites MODIFY id INT UNSIGNED NOT NULL');
        $this->addSql('DROP INDEX uid_tid_UNIQUE ON favorites');
        $this->addSql('DROP INDEX `primary` ON favorites');
        $this->addSql('ALTER TABLE favorites DROP id, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE favorites CHANGE tid church_id INT NOT NULL');
        $this->addSql('ALTER TABLE favorites CHANGE uid user_id INT NOT NULL');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F552596C31 FOREIGN KEY (church_id) REFERENCES templomok (id)');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5539B0606 FOREIGN KEY (user_id) REFERENCES user (uid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E46960F5C1538FD4 ON favorites (church_id)');
        $this->addSql('CREATE INDEX IDX_E46960F5A76ED395 ON favorites (user_id)');
        $this->addSql('ALTER TABLE favorites ADD PRIMARY KEY (church_id, user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
