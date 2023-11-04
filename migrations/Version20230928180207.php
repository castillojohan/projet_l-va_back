<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230928180207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE case_folder (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, reported_id INT NOT NULL, platform_id INT NOT NULL, content LONGTEXT NOT NULL, screenshot VARCHAR(255) NOT NULL, status LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at TIME NOT NULL, updated_at TIME DEFAULT NULL, reference VARCHAR(255) NOT NULL, INDEX IDX_454C4E64A76ED395 (user_id), INDEX IDX_454C4E6494BDEEB6 (reported_id), INDEX IDX_454C4E64FFE6496F (platform_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, recipient_id INT NOT NULL, content LONGTEXT NOT NULL, created_at TIME NOT NULL, updated_at TIME DEFAULT NULL, reading_status TINYINT(1) NOT NULL, INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307FE92F8F78 (recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE platform (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at TIME NOT NULL, updated_at TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proposal (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, content LONGTEXT NOT NULL, created_at TIME NOT NULL, INDEX IDX_BFE59472A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reported (id INT AUTO_INCREMENT NOT NULL, reported_pseudo VARCHAR(255) NOT NULL, reported_number INT NOT NULL, created_at TIME NOT NULL, updated_at TIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(70) NOT NULL, password VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at TIME NOT NULL, updated_at TIME DEFAULT NULL, pseudo VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE case_folder ADD CONSTRAINT FK_454C4E64A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE case_folder ADD CONSTRAINT FK_454C4E6494BDEEB6 FOREIGN KEY (reported_id) REFERENCES reported (id)');
        $this->addSql('ALTER TABLE case_folder ADD CONSTRAINT FK_454C4E64FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FE92F8F78 FOREIGN KEY (recipient_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE case_folder DROP FOREIGN KEY FK_454C4E64A76ED395');
        $this->addSql('ALTER TABLE case_folder DROP FOREIGN KEY FK_454C4E6494BDEEB6');
        $this->addSql('ALTER TABLE case_folder DROP FOREIGN KEY FK_454C4E64FFE6496F');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FE92F8F78');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472A76ED395');
        $this->addSql('DROP TABLE case_folder');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE platform');
        $this->addSql('DROP TABLE proposal');
        $this->addSql('DROP TABLE reported');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
