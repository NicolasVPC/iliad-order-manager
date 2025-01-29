<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250129114432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE64584665A');
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE68D9F6D38');
        $this->addSql('DROP INDEX IDX_2530ADE68D9F6D38 ON order_product');
        $this->addSql('DROP INDEX IDX_2530ADE64584665A ON order_product');
        $this->addSql('ALTER TABLE order_product ADD id INT AUTO_INCREMENT NOT NULL, ADD order_id_id INT NOT NULL, ADD product_id_id INT NOT NULL, ADD quantity INT NOT NULL, DROP order_id, DROP product_id, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE6FCDAEAAA FOREIGN KEY (order_id_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE6DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_2530ADE6FCDAEAAA ON order_product (order_id_id)');
        $this->addSql('CREATE INDEX IDX_2530ADE6DE18E50B ON order_product (product_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_product MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE6FCDAEAAA');
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE6DE18E50B');
        $this->addSql('DROP INDEX IDX_2530ADE6FCDAEAAA ON order_product');
        $this->addSql('DROP INDEX IDX_2530ADE6DE18E50B ON order_product');
        $this->addSql('DROP INDEX `PRIMARY` ON order_product');
        $this->addSql('ALTER TABLE order_product ADD order_id INT NOT NULL, ADD product_id INT NOT NULL, DROP id, DROP order_id_id, DROP product_id_id, DROP quantity');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE64584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE68D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2530ADE68D9F6D38 ON order_product (order_id)');
        $this->addSql('CREATE INDEX IDX_2530ADE64584665A ON order_product (product_id)');
        $this->addSql('ALTER TABLE order_product ADD PRIMARY KEY (order_id, product_id)');
    }
}
