<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240404224919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review ADD COLUMN title VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__review AS SELECT id, organism_id, author_id, content, note FROM review');
        $this->addSql('DROP TABLE review');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, organism_id INTEGER NOT NULL, author_id INTEGER NOT NULL, content CLOB DEFAULT NULL, note VARCHAR(255) NOT NULL, CONSTRAINT FK_794381C664180A36 FOREIGN KEY (organism_id) REFERENCES organism (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C6F675F31B FOREIGN KEY (author_id) REFERENCES student (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO review (id, organism_id, author_id, content, note) SELECT id, organism_id, author_id, content, note FROM __temp__review');
        $this->addSql('DROP TABLE __temp__review');
        $this->addSql('CREATE INDEX IDX_794381C664180A36 ON review (organism_id)');
        $this->addSql('CREATE INDEX IDX_794381C6F675F31B ON review (author_id)');
    }
}
