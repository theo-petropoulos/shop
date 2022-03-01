<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211228145722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81B171EB6C');
        $this->addSql('DROP INDEX IDX_D4E6F81B171EB6C ON address');
        $this->addSql('ALTER TABLE address CHANGE customer_id_id customer_id INT NOT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F819395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D4E6F819395C3F3 ON address (customer_id)');
        $this->addSql('ALTER TABLE ips DROP FOREIGN KEY FK_5E7470CD9D86650F');
        $this->addSql('DROP INDEX IDX_5E7470CD9D86650F ON ips');
        $this->addSql('ALTER TABLE ips CHANGE user_id_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE ips ADD CONSTRAINT FK_5E7470CDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5E7470CDA76ED395 ON ips (user_id)');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939848E1E977');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398B171EB6C');
        $this->addSql('DROP INDEX IDX_F529939848E1E977 ON `order`');
        $this->addSql('DROP INDEX IDX_F5299398B171EB6C ON `order`');
        $this->addSql('ALTER TABLE `order` ADD customer_id INT NOT NULL, ADD address_id INT NOT NULL, DROP customer_id_id, DROP address_id_id');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('CREATE INDEX IDX_F52993989395C3F3 ON `order` (customer_id)');
        $this->addSql('CREATE INDEX IDX_F5299398F5B7AF75 ON `order` (address_id)');
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F46DE18E50B');
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F46FCDAEAAA');
        $this->addSql('DROP INDEX IDX_ED896F46FCDAEAAA ON order_detail');
        $this->addSql('DROP INDEX UNIQ_ED896F46DE18E50B ON order_detail');
        $this->addSql('ALTER TABLE order_detail ADD order_id INT NOT NULL, ADD product_id INT NOT NULL, DROP order_id_id, DROP product_id_id');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F468D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F464584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_ED896F468D9F6D38 ON order_detail (order_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ED896F464584665A ON order_detail (product_id)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD24BD5740');
        $this->addSql('DROP INDEX IDX_D34A04AD24BD5740 ON product');
        $this->addSql('ALTER TABLE product CHANGE brand_id_id brand_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD44F5D008 ON product (brand_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F819395C3F3');
        $this->addSql('DROP INDEX IDX_D4E6F819395C3F3 ON address');
        $this->addSql('ALTER TABLE address CHANGE customer_id customer_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81B171EB6C FOREIGN KEY (customer_id_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_D4E6F81B171EB6C ON address (customer_id_id)');
        $this->addSql('ALTER TABLE ips DROP FOREIGN KEY FK_5E7470CDA76ED395');
        $this->addSql('DROP INDEX IDX_5E7470CDA76ED395 ON ips');
        $this->addSql('ALTER TABLE ips CHANGE user_id user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE ips ADD CONSTRAINT FK_5E7470CD9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5E7470CD9D86650F ON ips (user_id_id)');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993989395C3F3');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398F5B7AF75');
        $this->addSql('DROP INDEX IDX_F52993989395C3F3 ON `order`');
        $this->addSql('DROP INDEX IDX_F5299398F5B7AF75 ON `order`');
        $this->addSql('ALTER TABLE `order` ADD customer_id_id INT NOT NULL, ADD address_id_id INT NOT NULL, DROP customer_id, DROP address_id');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939848E1E977 FOREIGN KEY (address_id_id) REFERENCES address (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398B171EB6C FOREIGN KEY (customer_id_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F529939848E1E977 ON `order` (address_id_id)');
        $this->addSql('CREATE INDEX IDX_F5299398B171EB6C ON `order` (customer_id_id)');
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F468D9F6D38');
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F464584665A');
        $this->addSql('DROP INDEX IDX_ED896F468D9F6D38 ON order_detail');
        $this->addSql('DROP INDEX UNIQ_ED896F464584665A ON order_detail');
        $this->addSql('ALTER TABLE order_detail ADD order_id_id INT NOT NULL, ADD product_id_id INT NOT NULL, DROP order_id, DROP product_id');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F46DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F46FCDAEAAA FOREIGN KEY (order_id_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_ED896F46FCDAEAAA ON order_detail (order_id_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ED896F46DE18E50B ON order_detail (product_id_id)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD44F5D008');
        $this->addSql('DROP INDEX IDX_D34A04AD44F5D008 ON product');
        $this->addSql('ALTER TABLE product CHANGE brand_id brand_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD24BD5740 FOREIGN KEY (brand_id_id) REFERENCES brand (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_D34A04AD24BD5740 ON product (brand_id_id)');
    }
}
