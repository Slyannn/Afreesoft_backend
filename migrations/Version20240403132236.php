<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240403132236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__organism AS SELECT id, user_id, certificate, enable FROM organism');
        $this->addSql('DROP TABLE organism');
        $this->addSql('CREATE TABLE organism (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, certificate VARCHAR(255) NOT NULL, enable BOOLEAN NOT NULL, CONSTRAINT FK_D538A2CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO organism (id, user_id, certificate, enable) SELECT id, user_id, certificate, enable FROM __temp__organism');
        $this->addSql('DROP TABLE __temp__organism');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D538A2CA76ED395 ON organism (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__review AS SELECT id, content, note FROM review');
        $this->addSql('DROP TABLE review');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, organism_id INTEGER NOT NULL, author_id INTEGER NOT NULL, content CLOB DEFAULT NULL, note VARCHAR(255) NOT NULL, CONSTRAINT FK_794381C664180A36 FOREIGN KEY (organism_id) REFERENCES organism (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_794381C6F675F31B FOREIGN KEY (author_id) REFERENCES student (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO review (id, content, note) SELECT id, content, note FROM __temp__review');
        $this->addSql('DROP TABLE __temp__review');
        $this->addSql('CREATE INDEX IDX_794381C664180A36 ON review (organism_id)');
        $this->addSql('CREATE INDEX IDX_794381C6F675F31B ON review (author_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__student AS SELECT id, address_id, firstname, lastname, university, enable, create_at FROM student');
        $this->addSql('DROP TABLE student');
        $this->addSql('CREATE TABLE student (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, address_id INTEGER DEFAULT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, university VARCHAR(255) NOT NULL, enable BOOLEAN NOT NULL, create_at DATETIME NOT NULL, CONSTRAINT FK_B723AF33F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO student (id, address_id, firstname, lastname, university, enable, create_at) SELECT id, address_id, firstname, lastname, university, enable, create_at FROM __temp__student');
        $this->addSql('DROP TABLE __temp__student');
        $this->addSql('CREATE INDEX IDX_B723AF33F5B7AF75 ON student (address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__organism AS SELECT id, user_id, certificate, enable FROM organism');
        $this->addSql('DROP TABLE organism');
        $this->addSql('CREATE TABLE organism (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, review_id INTEGER DEFAULT NULL, certificate VARCHAR(255) NOT NULL, enable BOOLEAN NOT NULL, CONSTRAINT FK_D538A2CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D538A2C3E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO organism (id, user_id, certificate, enable) SELECT id, user_id, certificate, enable FROM __temp__organism');
        $this->addSql('DROP TABLE __temp__organism');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D538A2CA76ED395 ON organism (user_id)');
        $this->addSql('CREATE INDEX IDX_D538A2C3E2E969B ON organism (review_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__review AS SELECT id, content, note FROM review');
        $this->addSql('DROP TABLE review');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB DEFAULT NULL, note VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO review (id, content, note) SELECT id, content, note FROM __temp__review');
        $this->addSql('DROP TABLE __temp__review');
        $this->addSql('CREATE TEMPORARY TABLE __temp__student AS SELECT id, address_id, firstname, lastname, university, enable, create_at FROM student');
        $this->addSql('DROP TABLE student');
        $this->addSql('CREATE TABLE student (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, address_id INTEGER DEFAULT NULL, review_id INTEGER DEFAULT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, university VARCHAR(255) NOT NULL, enable BOOLEAN NOT NULL, create_at DATETIME NOT NULL, CONSTRAINT FK_B723AF33F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B723AF333E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO student (id, address_id, firstname, lastname, university, enable, create_at) SELECT id, address_id, firstname, lastname, university, enable, create_at FROM __temp__student');
        $this->addSql('DROP TABLE __temp__student');
        $this->addSql('CREATE INDEX IDX_B723AF33F5B7AF75 ON student (address_id)');
        $this->addSql('CREATE INDEX IDX_B723AF333E2E969B ON student (review_id)');
    }
}
