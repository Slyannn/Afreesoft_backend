<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231213110357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organism_admin ADD COLUMN organism_email VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__organism_admin AS SELECT id, address_id, profile_id, logo, name, email, description FROM organism_admin');
        $this->addSql('DROP TABLE organism_admin');
        $this->addSql('CREATE TABLE organism_admin (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, address_id INTEGER DEFAULT NULL, profile_id INTEGER DEFAULT NULL, logo VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, description VARCHAR(500) DEFAULT NULL, CONSTRAINT FK_586EBF8BF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_586EBF8BCCFA12B8 FOREIGN KEY (profile_id) REFERENCES organism (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO organism_admin (id, address_id, profile_id, logo, name, email, description) SELECT id, address_id, profile_id, logo, name, email, description FROM __temp__organism_admin');
        $this->addSql('DROP TABLE __temp__organism_admin');
        $this->addSql('CREATE INDEX IDX_586EBF8BF5B7AF75 ON organism_admin (address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_586EBF8BCCFA12B8 ON organism_admin (profile_id)');
    }
}
