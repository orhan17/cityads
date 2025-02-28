<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227223222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE geo (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(32) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_775EE79C77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, external_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, currency_name VARCHAR(32) NOT NULL, approval_time INT NOT NULL, payment_time INT NOT NULL, site_url VARCHAR(255) DEFAULT NULL, logo VARCHAR(500) DEFAULT NULL, rating DOUBLE PRECISION DEFAULT NULL, UNIQUE INDEX UNIQ_29D6873E9F75D7B0 (external_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offer_geo (offer_id INT NOT NULL, geo_id INT NOT NULL, INDEX IDX_5B6B860453C674EE (offer_id), INDEX IDX_5B6B8604FA49D0B (geo_id), PRIMARY KEY(offer_id, geo_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sync_state (id INT AUTO_INCREMENT NOT NULL, sync_in_progress TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE offer_geo ADD CONSTRAINT FK_5B6B860453C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE offer_geo ADD CONSTRAINT FK_5B6B8604FA49D0B FOREIGN KEY (geo_id) REFERENCES geo (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer_geo DROP FOREIGN KEY FK_5B6B860453C674EE');
        $this->addSql('ALTER TABLE offer_geo DROP FOREIGN KEY FK_5B6B8604FA49D0B');
        $this->addSql('DROP TABLE geo');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE offer_geo');
        $this->addSql('DROP TABLE sync_state');
    }
}
