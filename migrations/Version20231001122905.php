<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231001122905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reported ADD platform_id INT NOT NULL');
        $this->addSql('ALTER TABLE reported ADD CONSTRAINT FK_820255A3FFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id)');
        $this->addSql('CREATE INDEX IDX_820255A3FFE6496F ON reported (platform_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reported DROP FOREIGN KEY FK_820255A3FFE6496F');
        $this->addSql('DROP INDEX IDX_820255A3FFE6496F ON reported');
        $this->addSql('ALTER TABLE reported DROP platform_id');
    }
}
