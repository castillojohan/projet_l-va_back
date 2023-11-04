<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231005170625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_folder CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE platform CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE proposal CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE reported CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_folder CHANGE created_at created_at TIME NOT NULL, CHANGE updated_at updated_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message CHANGE created_at created_at TIME NOT NULL');
        $this->addSql('ALTER TABLE platform CHANGE created_at created_at TIME NOT NULL, CHANGE updated_at updated_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE proposal CHANGE created_at created_at TIME NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE created_at created_at TIME NOT NULL, CHANGE updated_at updated_at TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reported CHANGE created_at created_at TIME NOT NULL, CHANGE updated_at updated_at TIME DEFAULT NULL');
    }
}
