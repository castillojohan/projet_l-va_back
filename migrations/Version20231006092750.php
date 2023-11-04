<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231006092750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE screenshots (id INT AUTO_INCREMENT NOT NULL, case_folder_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_1A8D2713380FC9B9 (case_folder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE screenshots ADD CONSTRAINT FK_1A8D2713380FC9B9 FOREIGN KEY (case_folder_id) REFERENCES case_folder (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE screenshots DROP FOREIGN KEY FK_1A8D2713380FC9B9');
        $this->addSql('DROP TABLE screenshots');
    }
}
