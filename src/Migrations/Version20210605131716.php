<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210605131716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, civility INT DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, social_reason VARCHAR(255) DEFAULT NULL, siren VARCHAR(255) DEFAULT NULL, street VARCHAR(255) NOT NULL, complement VARCHAR(255) DEFAULT NULL, city VARCHAR(255) NOT NULL, postal_code VARCHAR(255) NOT NULL, country_code VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type INT NOT NULL, phone_number VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE co_owner (id INT AUTO_INCREMENT NOT NULL, attached_order_id INT NOT NULL, civility INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B222EF7E8AB74D1E (attached_order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document_process (document_id INT NOT NULL, process_id INT NOT NULL, INDEX IDX_2272BE4AC33F7837 (document_id), INDEX IDX_2272BE4A7EC2F574 (process_id), PRIMARY KEY(document_id, process_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, shipping_address_id INT NOT NULL, billing_address_id INT DEFAULT NULL, user_id INT DEFAULT NULL, process_id INT NOT NULL, number VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, total DOUBLE PRECISION NOT NULL, state INT NOT NULL, payment_intent VARCHAR(255) DEFAULT NULL, stripe_session VARCHAR(255) DEFAULT NULL, number_plate VARCHAR(255) DEFAULT NULL, priority TINYINT(1) NOT NULL, registration_document_name VARCHAR(255) DEFAULT NULL, assignment_certificate_name VARCHAR(255) DEFAULT NULL, registration_certificate_name VARCHAR(255) DEFAULT NULL, registration_mandate_name VARCHAR(255) DEFAULT NULL, driver_licence_name VARCHAR(255) DEFAULT NULL, address_proof_name VARCHAR(255) DEFAULT NULL, vehicle_insurance_name VARCHAR(255) DEFAULT NULL, technical_control_name VARCHAR(255) DEFAULT NULL, send_sms TINYINT(1) NOT NULL, registration_document_original_name VARCHAR(255) DEFAULT NULL, assignment_certificate_original_name VARCHAR(255) DEFAULT NULL, registration_certificate_original_name VARCHAR(255) DEFAULT NULL, registration_mandate_original_name VARCHAR(255) DEFAULT NULL, driver_licence_original_name VARCHAR(255) DEFAULT NULL, address_proof_original_name VARCHAR(255) DEFAULT NULL, vehicle_insurance_original_name VARCHAR(255) DEFAULT NULL, technical_control_original_name VARCHAR(255) DEFAULT NULL, INDEX IDX_F52993984D4CFF2B (shipping_address_id), INDEX IDX_F529939879D0C0E4 (billing_address_id), INDEX IDX_F5299398A76ED395 (user_id), UNIQUE INDEX UNIQ_F52993987EC2F574 (process_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_number (id INT AUTO_INCREMENT NOT NULL, number INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_process (id INT AUTO_INCREMENT NOT NULL, process_type VARCHAR(255) NOT NULL, purchase_place VARCHAR(255) DEFAULT NULL, fourth_change_home TINYINT(1) DEFAULT NULL, vehicle_modification VARCHAR(255) DEFAULT NULL, vehicle_type VARCHAR(255) DEFAULT NULL, circulation_date VARCHAR(255) NOT NULL, disability TINYINT(1) DEFAULT NULL, registration_type VARCHAR(255) DEFAULT NULL, demonstration_vehicle TINYINT(1) DEFAULT NULL, administrative_power INT DEFAULT NULL, collection_vehicle TINYINT(1) DEFAULT NULL, energy VARCHAR(255) DEFAULT NULL, co2_rate INT DEFAULT NULL, department INT NOT NULL, y1_tax_before_reduction DOUBLE PRECISION NOT NULL, y1_tax DOUBLE PRECISION NOT NULL, y2_transport_vehicle_surcharge DOUBLE PRECISION NOT NULL, y3_co2_penalty_passengers_cars DOUBLE PRECISION NOT NULL, y4_fixed_tax DOUBLE PRECISION NOT NULL, subtotal DOUBLE PRECISION NOT NULL, y5_routing_fee DOUBLE PRECISION NOT NULL, y6_taxes_payable DOUBLE PRECISION NOT NULL, five_seater_van TINYINT(1) DEFAULT NULL, is_code_carosserie_pick_up_be TINYINT(1) DEFAULT NULL, is_pick_up_affectation_remontees_mec_et_domaines_ski TINYINT(1) DEFAULT NULL, van_pick_up_submitted_ecotax TINYINT(1) DEFAULT NULL, vehicle_n1_carrying_travellers TINYINT(1) DEFAULT NULL, tourism_vehicle TINYINT(1) DEFAULT NULL, administrative_power_e85 INT DEFAULT NULL, total_to_pay DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION DEFAULT NULL, community_reception TINYINT(1) DEFAULT NULL, process_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE process (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, process_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) DEFAULT NULL, username_canonical VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) DEFAULT NULL, expired TINYINT(1) DEFAULT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', credentials_expired TINYINT(1) DEFAULT NULL, credentials_expire_at DATETIME DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, social_reason VARCHAR(255) DEFAULT NULL, siren VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE co_owner ADD CONSTRAINT FK_B222EF7E8AB74D1E FOREIGN KEY (attached_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE document_process ADD CONSTRAINT FK_2272BE4AC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document_process ADD CONSTRAINT FK_2272BE4A7EC2F574 FOREIGN KEY (process_id) REFERENCES process (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993984D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939879D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993987EC2F574 FOREIGN KEY (process_id) REFERENCES order_process (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993984D4CFF2B');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939879D0C0E4');
        $this->addSql('ALTER TABLE document_process DROP FOREIGN KEY FK_2272BE4AC33F7837');
        $this->addSql('ALTER TABLE co_owner DROP FOREIGN KEY FK_B222EF7E8AB74D1E');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993987EC2F574');
        $this->addSql('ALTER TABLE document_process DROP FOREIGN KEY FK_2272BE4A7EC2F574');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE co_owner');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE document_process');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_number');
        $this->addSql('DROP TABLE order_process');
        $this->addSql('DROP TABLE process');
        $this->addSql('DROP TABLE user');
    }
}
