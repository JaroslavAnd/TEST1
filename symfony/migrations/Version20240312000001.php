<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240312000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create banking credit system tables (clients, bank_accounts, credit_types, credits, payments)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE clients (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(50) NOT NULL, tax_id VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C82E74E7927C74 (email), UNIQUE INDEX UNIQ_C82E74B2A824D8 (tax_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bank_accounts (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, account_number VARCHAR(34) NOT NULL, balance NUMERIC(15, 2) NOT NULL, currency VARCHAR(3) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_FB88842BB1A4D127 (account_number), INDEX IDX_FB88842B19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE credit_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, interest_rate NUMERIC(5, 2) NOT NULL, min_amount NUMERIC(15, 2) NOT NULL, max_amount NUMERIC(15, 2) NOT NULL, term_months INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE credits (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, bank_account_id INT NOT NULL, credit_type_id INT NOT NULL, amount NUMERIC(15, 2) NOT NULL, remaining_amount NUMERIC(15, 2) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_4117D17E19EB6921 (client_id), INDEX IDX_4117D17E12CB990C (bank_account_id), INDEX IDX_4117D17EFFC8EBBC (credit_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payments (id INT AUTO_INCREMENT NOT NULL, credit_id INT NOT NULL, amount NUMERIC(15, 2) NOT NULL, payment_date DATETIME NOT NULL, payment_method VARCHAR(50) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_65D29B32CE062FF9 (credit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bank_accounts ADD CONSTRAINT FK_FB88842B19EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE credits ADD CONSTRAINT FK_4117D17E19EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE credits ADD CONSTRAINT FK_4117D17E12CB990C FOREIGN KEY (bank_account_id) REFERENCES bank_accounts (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE credits ADD CONSTRAINT FK_4117D17EFFC8EBBC FOREIGN KEY (credit_type_id) REFERENCES credit_types (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_65D29B32CE062FF9 FOREIGN KEY (credit_id) REFERENCES credits (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bank_accounts DROP FOREIGN KEY FK_FB88842B19EB6921');
        $this->addSql('ALTER TABLE credits DROP FOREIGN KEY FK_4117D17E19EB6921');
        $this->addSql('ALTER TABLE credits DROP FOREIGN KEY FK_4117D17E12CB990C');
        $this->addSql('ALTER TABLE credits DROP FOREIGN KEY FK_4117D17EFFC8EBBC');
        $this->addSql('ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32CE062FF9');
        $this->addSql('DROP TABLE payments');
        $this->addSql('DROP TABLE credits');
        $this->addSql('DROP TABLE credit_types');
        $this->addSql('DROP TABLE bank_accounts');
        $this->addSql('DROP TABLE clients');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
