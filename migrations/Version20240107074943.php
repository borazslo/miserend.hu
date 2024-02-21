<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240107074943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE modulok');
        $this->addSql('DROP TABLE oldalkeret');
        $this->addSql('DROP TABLE osm_tags');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE igenaptar');
        $this->addSql('DROP TABLE lnaptar');
        $this->addSql('DROP TABLE nevnaptar');
        $this->addSql('DROP TABLE szentek');
        $this->addSql('DROP TABLE unnepnaptar');
        $this->addSql('DROP TABLE updates');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
