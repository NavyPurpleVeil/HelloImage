<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220713225938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, uid INTEGER NOT NULL, extension VARCHAR(255) NOT NULL, vote_count INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE rating (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, uid VARCHAR(255) NOT NULL, img_id INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, auth_key VARCHAR(255) NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE rating');
        $this->addSql('DROP TABLE user');
    }
}
