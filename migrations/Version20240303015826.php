<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240303015826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, street VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, zip_code VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE admin (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_880E0D76E7927C74 ON admin (email)');
        $this->addSql('CREATE TABLE need (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E6F46C445E237E06 ON need (name)');
        $this->addSql('CREATE TABLE organism (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, review_id INTEGER DEFAULT NULL, certificate VARCHAR(255) NOT NULL, enable BOOLEAN NOT NULL, CONSTRAINT FK_D538A2CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D538A2C3E2E969B FOREIGN KEY (review_id) REFERENCES review (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D538A2CA76ED395 ON organism (user_id)');
        $this->addSql('CREATE INDEX IDX_D538A2C3E2E969B ON organism (review_id)');
        $this->addSql('CREATE TABLE organism_admin (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, address_id INTEGER DEFAULT NULL, profile_id INTEGER DEFAULT NULL, logo VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(500) DEFAULT NULL, organism_email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, create_at VARCHAR(255) NOT NULL, CONSTRAINT FK_586EBF8BF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_586EBF8BCCFA12B8 FOREIGN KEY (profile_id) REFERENCES organism (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_586EBF8BF5B7AF75 ON organism_admin (address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_586EBF8BCCFA12B8 ON organism_admin (profile_id)');
        $this->addSql('CREATE TABLE organism_admin_need (organism_admin_id INTEGER NOT NULL, need_id INTEGER NOT NULL, PRIMARY KEY(organism_admin_id, need_id), CONSTRAINT FK_8DDF2664276CF21 FOREIGN KEY (organism_admin_id) REFERENCES organism_admin (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8DDF266624AF264 FOREIGN KEY (need_id) REFERENCES need (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_8DDF2664276CF21 ON organism_admin_need (organism_admin_id)');
        $this->addSql('CREATE INDEX IDX_8DDF266624AF264 ON organism_admin_need (need_id)');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, text CLOB DEFAULT NULL)');
        $this->addSql('CREATE TABLE student (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, address_id INTEGER DEFAULT NULL, review_id INTEGER DEFAULT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, university VARCHAR(255) NOT NULL, enable BOOLEAN NOT NULL, create_at DATETIME NOT NULL, CONSTRAINT FK_B723AF33F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B723AF333E2E969B FOREIGN KEY (review_id) REFERENCES review (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B723AF33F5B7AF75 ON student (address_id)');
        $this->addSql('CREATE INDEX IDX_B723AF333E2E969B ON student (review_id)');
        $this->addSql('CREATE TABLE student_need (student_id INTEGER NOT NULL, need_id INTEGER NOT NULL, PRIMARY KEY(student_id, need_id), CONSTRAINT FK_D9D7179CCB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D9D7179C624AF264 FOREIGN KEY (need_id) REFERENCES need (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D9D7179CCB944F1A ON student_need (student_id)');
        $this->addSql('CREATE INDEX IDX_D9D7179C624AF264 ON student_need (need_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, student_id INTEGER DEFAULT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, verified BOOLEAN NOT NULL, CONSTRAINT FK_8D93D649CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649CB944F1A ON user (student_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE need');
        $this->addSql('DROP TABLE organism');
        $this->addSql('DROP TABLE organism_admin');
        $this->addSql('DROP TABLE organism_admin_need');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE student_need');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
