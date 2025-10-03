<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250927212745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        // Create a default user for existing tasks
        $this->addSql('INSERT INTO `user` (email, roles, password, first_name, last_name) VALUES (\'admin@todoapp.com\', \'["ROLE_USER"]\', \'$2y$13$dummy.hash.for.existing.tasks\', \'Admin\', \'User\')');
        
        // Add user_id column to task table
        $this->addSql('ALTER TABLE task ADD user_id INT NOT NULL');
        
        // Update existing tasks to use the default user
        $this->addSql('UPDATE task SET user_id = 1 WHERE user_id IS NULL');
        
        // Add foreign key constraint
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        
        // Create index
        $this->addSql('CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_527EDB25A76ED395 ON task');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25A76ED395');
        $this->addSql('ALTER TABLE task DROP user_id');
        $this->addSql('DELETE FROM `user` WHERE email = \'admin@todoapp.com\'');
    }
}
