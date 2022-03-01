<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211228143248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, customer_id_id INT NOT NULL, last_name VARCHAR(155) NOT NULL, first_name VARCHAR(155) NOT NULL, street_number VARCHAR(15) DEFAULT NULL, street_name VARCHAR(255) NOT NULL, street_addition VARCHAR(255) DEFAULT NULL, postal_code INT NOT NULL, city VARCHAR(80) NOT NULL, main TINYINT(1) DEFAULT NULL, INDEX IDX_D4E6F81B171EB6C (customer_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(155) NOT NULL, description VARCHAR(500) NOT NULL, active TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discount (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(155) NOT NULL, percentage DOUBLE PRECISION NOT NULL, starting_date DATE NOT NULL, ending_date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ips (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, address VARCHAR(20) NOT NULL, blacklist TINYINT(1) DEFAULT NULL, INDEX IDX_5E7470CD9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, customer_id_id INT NOT NULL, address_id_id INT NOT NULL, purchase_date DATE NOT NULL, status VARCHAR(100) DEFAULT NULL, tracking_number VARCHAR(50) DEFAULT NULL, INDEX IDX_F5299398B171EB6C (customer_id_id), INDEX IDX_F529939848E1E977 (address_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, brand_id_id INT NOT NULL, discount_id INT DEFAULT NULL, name VARCHAR(155) NOT NULL, description VARCHAR(500) NOT NULL, image_path VARCHAR(255) DEFAULT NULL, price DOUBLE PRECISION NOT NULL, stock INT NOT NULL, active TINYINT(1) DEFAULT NULL, INDEX IDX_D34A04AD24BD5740 (brand_id_id), INDEX IDX_D34A04AD4C7C611F (discount_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, last_name VARCHAR(155) NOT NULL, first_name VARCHAR(155) NOT NULL, phone VARCHAR(20) NOT NULL, creation_date DATE NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81B171EB6C FOREIGN KEY (customer_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ips ADD CONSTRAINT FK_5E7470CD9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398B171EB6C FOREIGN KEY (customer_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939848E1E977 FOREIGN KEY (address_id_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD24BD5740 FOREIGN KEY (brand_id_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD4C7C611F FOREIGN KEY (discount_id) REFERENCES discount (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939848E1E977');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD24BD5740');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD4C7C611F');
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81B171EB6C');
        $this->addSql('ALTER TABLE ips DROP FOREIGN KEY FK_5E7470CD9D86650F');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398B171EB6C');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE discount');
        $this->addSql('DROP TABLE ips');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE user');
    }
}
