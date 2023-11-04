<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231010135056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP is_active');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` ADD forgot_password_token VARCHAR(255) DEFAULT NULL, ADD forgot_password_token_requested_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD forgot_password_token_must_be_verified_before DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP is_active');
    }
}
