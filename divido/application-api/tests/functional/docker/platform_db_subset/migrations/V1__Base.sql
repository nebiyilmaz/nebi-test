# ************************************************************
# Sequel Pro SQL dump
# Version 5446
#
# https://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.27)
# Database: platform
# Generation Time: 2019-10-13 18:47:04 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table application
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application`;

CREATE TABLE `application` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(34) COLLATE utf8_unicode_ci DEFAULT NULL,
  `platform_environment_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `branch_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_submission_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `currency_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_finance_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_finance_option_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_channel_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_api_key_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_user_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `finalised` tinyint(1) DEFAULT NULL,
  `finalisation_required` tinyint(1) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `purchase_price` int(11) DEFAULT NULL,
  `deposit_amount` int(11) DEFAULT NULL,
  `deposit_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lender_fee` int(11) NOT NULL,
  `lender_fee_reported_date` datetime DEFAULT NULL,
  `form_data` longtext COLLATE utf8_unicode_ci,
  `applicants` longtext COLLATE utf8_unicode_ci,
  `product_data` longtext COLLATE utf8_unicode_ci,
  `metadata` longtext COLLATE utf8_unicode_ci,
  `commission` int(11) NOT NULL,
  `partner_commission` int(11) NOT NULL,
  `merchant_reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_response_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_checkout_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_redirect_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `finance_settings` longtext COLLATE utf8_unicode_ci,
  `payment_data` longtext COLLATE utf8_unicode_ci,
  `deposit_reference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `activation_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `next_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_data` longtext COLLATE utf8_unicode_ci,
  `lender_reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_loan_reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `available_finance_options` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_A45BDDC1F92F3E70` (`country_id`),
  KEY `IDX_A45BDDC138248176` (`currency_id`),
  KEY `IDX_A45BDDC182F1BAF4` (`language_id`),
  KEY `IDX_A45BDDC16796D554` (`merchant_id`),
  KEY `IDX_A45BDDC19395C3F3` (`customer_id`),
  KEY `IDX_A45BDDC152E1E0F3` (`platform_environment_id`),
  KEY `merchant_id` (`merchant_id`,`id`),
  KEY `merchant_status` (`merchant_id`,`status`),
  KEY `IDX_A45BDDC1F3E9B5A8` (`application_submission_id`),
  KEY `IDX_A45BDDC1109A667A` (`merchant_channel_id`),
  KEY `IDX_A45BDDC13CE86112` (`merchant_finance_id`),
  KEY `IDX_A45BDDC1E970D51E` (`merchant_finance_option_id`),
  KEY `token_status` (`token`,`status`),
  CONSTRAINT `FK_A45BDDC1109A667A` FOREIGN KEY (`merchant_channel_id`) REFERENCES `merchant_channel` (`id`),
  CONSTRAINT `FK_A45BDDC138248176` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`code`),
  CONSTRAINT `FK_A45BDDC13CE86112` FOREIGN KEY (`merchant_finance_id`) REFERENCES `merchant_finance` (`id`),
  CONSTRAINT `FK_A45BDDC152E1E0F3` FOREIGN KEY (`platform_environment_id`) REFERENCES `platform_environment` (`code`),
  CONSTRAINT `FK_A45BDDC16796D554` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`),
  CONSTRAINT `FK_A45BDDC182F1BAF4` FOREIGN KEY (`language_id`) REFERENCES `language` (`code`),
  CONSTRAINT `FK_A45BDDC19395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  CONSTRAINT `FK_A45BDDC1E970D51E` FOREIGN KEY (`merchant_finance_option_id`) REFERENCES `merchant_finance_option` (`id`),
  CONSTRAINT `FK_A45BDDC1F3E9B5A8` FOREIGN KEY (`application_submission_id`) REFERENCES `application_submission` (`id`),
  CONSTRAINT `FK_A45BDDC1F92F3E70` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_activation
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_activation`;

CREATE TABLE `application_activation` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `product_data` longtext COLLATE utf8_unicode_ci,
  `delivery_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tracking_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_6BEB519B3E030ACD` (`application_id`),
  CONSTRAINT `FK_6BEB519B3E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_activation_item
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_activation_item`;

CREATE TABLE `application_activation_item` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_activation_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_item_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B4E7A6EE37FC5AF` (`application_activation_id`),
  KEY `IDX_B4E7A6EEF9D1576D` (`application_item_id`),
  CONSTRAINT `FK_B4E7A6EE37FC5AF` FOREIGN KEY (`application_activation_id`) REFERENCES `application_activation` (`id`),
  CONSTRAINT `FK_B4E7A6EEF9D1576D` FOREIGN KEY (`application_item_id`) REFERENCES `application_item` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_agreement
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_agreement`;

CREATE TABLE `application_agreement` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_submission_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `document_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `section` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_raw` longtext COLLATE utf8_unicode_ci NOT NULL,
  `checksum` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_AA6420253E030ACD` (`application_id`),
  KEY `IDX_AA642025F3E9B5A8` (`application_submission_id`),
  KEY `IDX_AA642025855D3E3D` (`lender_id`),
  KEY `IDX_AA642025C33F7837` (`document_id`),
  CONSTRAINT `FK_AA6420253E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_AA642025855D3E3D` FOREIGN KEY (`lender_id`) REFERENCES `lender` (`id`),
  CONSTRAINT `FK_AA642025C33F7837` FOREIGN KEY (`document_id`) REFERENCES `document` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_AA642025F3E9B5A8` FOREIGN KEY (`application_submission_id`) REFERENCES `application_submission` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_agreement_signature
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_agreement_signature`;

CREATE TABLE `application_agreement_signature` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_agreement_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_signatory_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `signed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_7FEBC80A879CC072` (`application_agreement_id`),
  KEY `IDX_7FEBC80AF6F61AB9` (`application_signatory_id`),
  CONSTRAINT `FK_7FEBC80A879CC072` FOREIGN KEY (`application_agreement_id`) REFERENCES `application_agreement` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7FEBC80AF6F61AB9` FOREIGN KEY (`application_signatory_id`) REFERENCES `application_signatory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_alternative_offer
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_alternative_offer`;

CREATE TABLE `application_alternative_offer` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_8773B50855D3E3D` (`lender_id`),
  KEY `IDX_8773B503E030ACD` (`application_id`),
  CONSTRAINT `FK_8773B503E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`),
  CONSTRAINT `FK_8773B50855D3E3D` FOREIGN KEY (`lender_id`) REFERENCES `lender` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_cancellation
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_cancellation`;

CREATE TABLE `application_cancellation` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` int(11) DEFAULT NULL,
  `product_data` longtext COLLATE utf8_unicode_ci,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_58DB96553E030ACD` (`application_id`),
  CONSTRAINT `FK_58DB96553E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_cancellation_item
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_cancellation_item`;

CREATE TABLE `application_cancellation_item` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_cancellation_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_item_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D958A64B3A2C0FF3` (`application_cancellation_id`),
  KEY `IDX_D958A64BF9D1576D` (`application_item_id`),
  CONSTRAINT `FK_D958A64B3A2C0FF3` FOREIGN KEY (`application_cancellation_id`) REFERENCES `application_cancellation` (`id`),
  CONSTRAINT `FK_D958A64BF9D1576D` FOREIGN KEY (`application_item_id`) REFERENCES `application_item` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_customer
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_customer`;

CREATE TABLE `application_customer` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `customer_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `applicant_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_3E02511A9395C3F3` (`customer_id`),
  KEY `IDX_3E02511A3E030ACD` (`application_id`),
  CONSTRAINT `FK_3E02511A3E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_3E02511A9395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_deposit
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_deposit`;

CREATE TABLE `application_deposit` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `merchant_comment` longtext COLLATE utf8_unicode_ci,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_raw` longtext COLLATE utf8_unicode_ci NOT NULL,
  `product_data` longtext COLLATE utf8_unicode_ci,
  `merchant_reference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_7E65980A3E030ACD` (`application_id`),
  CONSTRAINT `FK_7E65980A3E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_document
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_document`;

CREATE TABLE `application_document` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `document_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category` longtext COLLATE utf8_unicode_ci,
  `sub_category` longtext COLLATE utf8_unicode_ci,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_67525565C33F7837` (`document_id`),
  KEY `IDX_675255653E030ACD` (`application_id`),
  CONSTRAINT `FK_675255653E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_67525565C33F7837` FOREIGN KEY (`document_id`) REFERENCES `document` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_history
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_history`;

CREATE TABLE `application_history` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci NOT NULL,
  `internal` tinyint(1) NOT NULL,
  `date` datetime DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_CC0475783E030ACD` (`application_id`),
  CONSTRAINT `FK_CC0475783E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_item
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_item`;

CREATE TABLE `application_item` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `product_data` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_E8F74D96A45BDDC1` (`application`),
  CONSTRAINT `FK_E8F74D96A45BDDC1` FOREIGN KEY (`application`) REFERENCES `application` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_refund
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_refund`;

CREATE TABLE `application_refund` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` int(11) DEFAULT NULL,
  `product_data` longtext COLLATE utf8_unicode_ci,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_EB1F189D3E030ACD` (`application_id`),
  CONSTRAINT `FK_EB1F189D3E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_refund_item
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_refund_item`;

CREATE TABLE `application_refund_item` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_refund_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_item_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_54F507BE9C997BD4` (`application_refund_id`),
  KEY `IDX_54F507BEF9D1576D` (`application_item_id`),
  CONSTRAINT `FK_54F507BE9C997BD4` FOREIGN KEY (`application_refund_id`) REFERENCES `application_refund` (`id`),
  CONSTRAINT `FK_54F507BEF9D1576D` FOREIGN KEY (`application_item_id`) REFERENCES `application_item` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_signatory
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_signatory`;

CREATE TABLE `application_signatory` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `lender_reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hosted_signing` tinyint(1) DEFAULT NULL,
  `data_raw` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_2F3901A93E030ACD` (`application_id`),
  CONSTRAINT `FK_2F3901A93E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_signatory_identification
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_signatory_identification`;

CREATE TABLE `application_signatory_identification` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_signatory_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `identification_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_raw` longtext COLLATE utf8_unicode_ci,
  `expire_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_52E8B4F0F6F61AB9` (`application_signatory_id`),
  CONSTRAINT `FK_52E8B4F0F6F61AB9` FOREIGN KEY (`application_signatory_id`) REFERENCES `application_signatory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_signer_collection
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_signer_collection`;

CREATE TABLE `application_signer_collection` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_submission_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `collection_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_id` (`application_id`),
  KEY `application_submission_id` (`application_submission_id`),
  CONSTRAINT `application_signer_collection_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_signer_collection_ibfk_2` FOREIGN KEY (`application_submission_id`) REFERENCES `application_submission` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_submission
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_submission`;

CREATE TABLE `application_submission` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `application_alternative_offer_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_finance_plan_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lender_reference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lender_loan_reference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lender_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lender_data` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `order` int(11) DEFAULT NULL,
  `decline_referred` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_AC866B1FF82EE02F` (`application_alternative_offer_id`),
  KEY `IDX_AC866B1F3E030ACD` (`application_id`),
  KEY `IDX_AC866B1F855D3E3D` (`lender_id`),
  CONSTRAINT `FK_AC866B1F3E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_AC866B1F855D3E3D` FOREIGN KEY (`lender_id`) REFERENCES `lender` (`id`),
  CONSTRAINT `FK_AC866B1FF82EE02F` FOREIGN KEY (`application_alternative_offer_id`) REFERENCES `application_alternative_offer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table application_term
# ------------------------------------------------------------

DROP TABLE IF EXISTS `application_term`;

CREATE TABLE `application_term` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `application_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `terms` longtext COLLATE utf8_unicode_ci,
  `invalidated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_52E38F053E030ACD` (`application_id`),
  CONSTRAINT `FK_52E38F053E030ACD` FOREIGN KEY (`application_id`) REFERENCES `application` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table branch
# ------------------------------------------------------------

DROP TABLE IF EXISTS `branch`;

CREATE TABLE `branch` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `platform_environment_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `settings` longtext COLLATE utf8_unicode_ci,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table country
# ------------------------------------------------------------

DROP TABLE IF EXISTS `country`;

CREATE TABLE `country` (
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `currency_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`),
  KEY `IDX_5373C96682F1BAF4` (`language_id`),
  KEY `IDX_5373C96638248176` (`currency_id`),
  CONSTRAINT `FK_5373C96638248176` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`code`),
  CONSTRAINT `FK_5373C96682F1BAF4` FOREIGN KEY (`language_id`) REFERENCES `language` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table currency
# ------------------------------------------------------------

DROP TABLE IF EXISTS `currency`;

CREATE TABLE `currency` (
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `foreign_names` longtext COLLATE utf8_unicode_ci,
  `symbol` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `symbol_before_price` tinyint(1) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table customer
# ------------------------------------------------------------

DROP TABLE IF EXISTS `customer`;

CREATE TABLE `customer` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `platform_environment_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `metadata` longtext COLLATE utf8_unicode_ci,
  `phone_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `form_data` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_81398E0952E1E0F3` (`platform_environment_id`),
  CONSTRAINT `FK_81398E0952E1E0F3` FOREIGN KEY (`platform_environment_id`) REFERENCES `platform_environment` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table language
# ------------------------------------------------------------

DROP TABLE IF EXISTS `language`;

CREATE TABLE `language` (
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `locale_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table lender
# ------------------------------------------------------------

DROP TABLE IF EXISTS `lender`;

CREATE TABLE `lender` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `app_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `settings` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant`;

CREATE TABLE `merchant` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `platform_environment_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `theme_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `shared_secret` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `layout_logo` longtext COLLATE utf8_unicode_ci,
  `layout_css` longtext COLLATE utf8_unicode_ci,
  `layout_styling` longtext COLLATE utf8_unicode_ci,
  `layout_html` longtext COLLATE utf8_unicode_ci,
  `settings` longtext COLLATE utf8_unicode_ci,
  `metadata` longtext COLLATE utf8_unicode_ci,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lead_source_id` int(11) DEFAULT NULL,
  `user_csm_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_lead_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_salesman_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ecommerce_platform_id` int(11) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `branch_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_74AB25E158585B87` (`user_lead_id`),
  KEY `IDX_74AB25E13664EB77` (`user_salesman_id`),
  KEY `IDX_74AB25E1B4CE6E89` (`user_csm_id`),
  KEY `IDX_74AB25E19393F8FE` (`partner_id`),
  KEY `IDX_74AB25E1C9F1E59` (`lead_source_id`),
  KEY `IDX_74AB25E1A6A7ADE8` (`ecommerce_platform_id`),
  KEY `env` (`platform_environment_id`),
  KEY `id_env` (`id`,`platform_environment_id`),
  KEY `IDX_74AB25E159027487` (`theme_id`),
  CONSTRAINT `FK_74AB25E13664EB77` FOREIGN KEY (`user_salesman_id`) REFERENCES `backoffice_user` (`id`),
  CONSTRAINT `FK_74AB25E152E1E0F3` FOREIGN KEY (`platform_environment_id`) REFERENCES `platform_environment` (`code`),
  CONSTRAINT `FK_74AB25E158585B87` FOREIGN KEY (`user_lead_id`) REFERENCES `backoffice_user` (`id`),
  CONSTRAINT `FK_74AB25E159027487` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`id`),
  CONSTRAINT `FK_74AB25E19393F8FE` FOREIGN KEY (`partner_id`) REFERENCES `partner` (`id`),
  CONSTRAINT `FK_74AB25E1A6A7ADE8` FOREIGN KEY (`ecommerce_platform_id`) REFERENCES `ecommerce_platform` (`id`),
  CONSTRAINT `FK_74AB25E1B4CE6E89` FOREIGN KEY (`user_csm_id`) REFERENCES `backoffice_user` (`id`),
  CONSTRAINT `FK_74AB25E1C9F1E59` FOREIGN KEY (`lead_source_id`) REFERENCES `lead_source` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_activity
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_activity`;

CREATE TABLE `merchant_activity` (
  `platform_environment_id` varchar(50) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `merchant` varchar(50) DEFAULT NULL,
  `merchant_id` varchar(60) DEFAULT NULL,
  `user` varchar(50) DEFAULT NULL,
  `user_id` varchar(60) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT NULL,
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table merchant_api_key
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_api_key`;

CREATE TABLE `merchant_api_key` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_channel_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `public` tinyint(1) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_102C98066796D554` (`merchant_id`),
  KEY `merchant_apiKey` (`api_key`),
  KEY `IDX_102C9806109A667A` (`merchant_channel_id`),
  CONSTRAINT `FK_102C9806109A667A` FOREIGN KEY (`merchant_channel_id`) REFERENCES `merchant_channel` (`id`),
  CONSTRAINT `FK_102C98066796D554` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_api_key_channel
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_api_key_channel`;

CREATE TABLE `merchant_api_key_channel` (
  `merchant_api_key_id` int(11) NOT NULL,
  `merchant_channel_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`merchant_api_key_id`,`merchant_channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_channel
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_channel`;

CREATE TABLE `merchant_channel` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_7BC7FBDC6796D554` (`merchant_id`),
  KEY `IDX_7BC7FBDCF92F3E70` (`country_id`),
  CONSTRAINT `FK_7BC7FBDC6796D554` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`),
  CONSTRAINT `FK_7BC7FBDCF92F3E70` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_finance
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_finance`;

CREATE TABLE `merchant_finance` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `type` longtext COLLATE utf8_unicode_ci NOT NULL,
  `text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `interest_rate` double NOT NULL,
  `agreement_duration` int(11) NOT NULL,
  `deferral_period` int(11) NOT NULL,
  `min_amount` double NOT NULL,
  `sorting` int(11) DEFAULT NULL,
  `min_deposit` double NOT NULL,
  `max_deposit` double NOT NULL,
  `lender_interest_rate` double NOT NULL,
  `margin_rate_percentage` decimal(6,5) DEFAULT NULL,
  `merchant_minimum_deposit_percentage` decimal(6,5) NOT NULL,
  `merchant_maximum_deposit_percentage` decimal(6,5) NOT NULL,
  `minimum_repayment_percentage` decimal(6,5) NOT NULL,
  `minimum_repayment_amount` int(11) NOT NULL,
  `commission` double NOT NULL,
  `partner_commission` double NOT NULL,
  `lender_fee_percentage` double NOT NULL,
  `lender_fee_min_amount` double NOT NULL,
  `setup_fee` double NOT NULL,
  `instalment_fee` double NOT NULL,
  `index_rate_name` longtext COLLATE utf8_unicode_ci NOT NULL,
  `decline_referred` tinyint(1) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_17169F7B6796D554` (`merchant_id`),
  KEY `IDX_17169F7B855D3E3D` (`lender_id`),
  KEY `IDX_17169F7BF92F3E70` (`country_id`),
  CONSTRAINT `FK_17169F7B6796D554` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`),
  CONSTRAINT `FK_17169F7B855D3E3D` FOREIGN KEY (`lender_id`) REFERENCES `lender` (`id`),
  CONSTRAINT `FK_17169F7BF92F3E70` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_finance_option
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_finance_option`;

CREATE TABLE `merchant_finance_option` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `type` longtext COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `interest_rate_percentage` decimal(6,5) NOT NULL,
  `agreement_duration_months` int(11) NOT NULL,
  `deferral_period_months` int(11) NOT NULL,
  `order` int(11) DEFAULT NULL,
  `minimum_amount` int(11) NOT NULL,
  `maximum_amount` int(11) NOT NULL,
  `minimum_deposit_percentage` decimal(6,5) NOT NULL,
  `maximum_deposit_percentage` decimal(6,5) NOT NULL,
  `margin_rate_percentage` decimal(6,5) DEFAULT NULL,
  `merchant_minimum_deposit_percentage` decimal(6,5) NOT NULL,
  `merchant_maximum_deposit_percentage` decimal(6,5) NOT NULL,
  `minimum_repayment_amount` int(11) NOT NULL,
  `minimum_repayment_percentage` decimal(6,5) NOT NULL,
  `finance_settings` longtext COLLATE utf8_unicode_ci NOT NULL,
  `setup_fee_amount` int(11) NOT NULL,
  `instalment_fee_amount` int(11) NOT NULL,
  `index_rate_name` longtext COLLATE utf8_unicode_ci NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_8384CA706796D554` (`merchant_id`),
  CONSTRAINT `FK_8384CA706796D554` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_finance_option_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_finance_option_group`;

CREATE TABLE `merchant_finance_option_group` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_finance_option_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_waterfall_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rate` decimal(6,5) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_CE37D157E970D51E` (`merchant_finance_option_id`),
  KEY `IDX_CE37D157A6BA0B9E` (`merchant_waterfall_id`),
  CONSTRAINT `FK_CE37D157A6BA0B9E` FOREIGN KEY (`merchant_waterfall_id`) REFERENCES `merchant_waterfall` (`id`),
  CONSTRAINT `FK_CE37D157E970D51E` FOREIGN KEY (`merchant_finance_option_id`) REFERENCES `merchant_finance_option` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_finance_plan
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_finance_plan`;

CREATE TABLE `merchant_finance_plan` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_finance_option_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lender_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `commission_percentage` decimal(6,5) NOT NULL,
  `partner_commission_percentage` decimal(6,5) NOT NULL,
  `lender_fee_percentage` decimal(6,5) NOT NULL,
  `lender_fee_minimum_amount` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_F676501EE970D51E` (`merchant_finance_option_id`),
  CONSTRAINT `FK_F676501EE970D51E` FOREIGN KEY (`merchant_finance_option_id`) REFERENCES `merchant_finance_option` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_lender
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_lender`;

CREATE TABLE `merchant_lender` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lender_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `settings` longtext COLLATE utf8_unicode_ci,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_45AFBD68855D3E3D` (`lender_id`),
  KEY `IDX_45AFBD686796D554` (`merchant_id`),
  CONSTRAINT `FK_45AFBD686796D554` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`),
  CONSTRAINT `FK_45AFBD68855D3E3D` FOREIGN KEY (`lender_id`) REFERENCES `lender` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_logo
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_logo`;

CREATE TABLE `merchant_logo` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `width` int(11) NOT NULL,
  `original` tinyint(1) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_A58D55186796D554` (`merchant_id`),
  CONSTRAINT `FK_A58D55186796D554` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_payment_provider
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_payment_provider`;

CREATE TABLE `merchant_payment_provider` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_provider_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `settings` longtext COLLATE utf8_unicode_ci,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IDX_B31FE10B6796D554` (`merchant_id`),
  KEY `IDX_B31FE10BFCDF7870` (`payment_provider_id`),
  CONSTRAINT `FK_B31FE10B6796D554` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`),
  CONSTRAINT `FK_B31FE10BFCDF7870` FOREIGN KEY (`payment_provider_id`) REFERENCES `payment_provider` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_waterfall
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_waterfall`;

CREATE TABLE `merchant_waterfall` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `strategy` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table merchant_waterfall_stream
# ------------------------------------------------------------

DROP TABLE IF EXISTS `merchant_waterfall_stream`;

CREATE TABLE `merchant_waterfall_stream` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `merchant_waterfall_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `merchant_finance_plan_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `decline_referred` tinyint(1) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_53737625A6BA0B9E` (`merchant_waterfall_id`),
  KEY `IDX_537376257324C2E1` (`merchant_finance_plan_id`),
  CONSTRAINT `FK_537376257324C2E1` FOREIGN KEY (`merchant_finance_plan_id`) REFERENCES `merchant_finance_plan` (`id`),
  CONSTRAINT `FK_53737625A6BA0B9E` FOREIGN KEY (`merchant_waterfall_id`) REFERENCES `merchant_waterfall` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table payment_provider
# ------------------------------------------------------------

DROP TABLE IF EXISTS `payment_provider`;

CREATE TABLE `payment_provider` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `app_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `settings` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table platform_environment
# ------------------------------------------------------------

DROP TABLE IF EXISTS `platform_environment`;

CREATE TABLE `platform_environment` (
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `theme_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `settings` longtext COLLATE utf8_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`code`),
  KEY `IDX_981D88559027487` (`theme_id`),
  CONSTRAINT `FK_981D88559027487` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table platform_environment_country
# ------------------------------------------------------------

DROP TABLE IF EXISTS `platform_environment_country`;

CREATE TABLE `platform_environment_country` (
  `platform_environment_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`platform_environment_id`,`country_id`),
  KEY `IDX_7C304B8B52E1E0F3` (`platform_environment_id`),
  KEY `IDX_7C304B8BF92F3E70` (`country_id`),
  CONSTRAINT `FK_7C304B8B52E1E0F3` FOREIGN KEY (`platform_environment_id`) REFERENCES `platform_environment` (`code`),
  CONSTRAINT `FK_7C304B8BF92F3E70` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table platform_environment_lender
# ------------------------------------------------------------

DROP TABLE IF EXISTS `platform_environment_lender`;

CREATE TABLE `platform_environment_lender` (
  `platform_environment_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lender_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`platform_environment_id`,`lender_id`),
  KEY `IDX_817E90BD52E1E0F3` (`platform_environment_id`),
  KEY `IDX_817E90BD855D3E3D` (`lender_id`),
  CONSTRAINT `FK_817E90BD52E1E0F3` FOREIGN KEY (`platform_environment_id`) REFERENCES `platform_environment` (`code`),
  CONSTRAINT `FK_817E90BD855D3E3D` FOREIGN KEY (`lender_id`) REFERENCES `lender` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table platform_environment_payment_provider
# ------------------------------------------------------------

DROP TABLE IF EXISTS `platform_environment_payment_provider`;

CREATE TABLE `platform_environment_payment_provider` (
  `platform_environment_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_provider_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`platform_environment_id`,`payment_provider_id`),
  KEY `IDX_8B7C0DE852E1E0F3` (`platform_environment_id`),
  KEY `IDX_8B7C0DE8FCDF7870` (`payment_provider_id`),
  CONSTRAINT `FK_8B7C0DE852E1E0F3` FOREIGN KEY (`platform_environment_id`) REFERENCES `platform_environment` (`code`),
  CONSTRAINT `FK_8B7C0DE8FCDF7870` FOREIGN KEY (`payment_provider_id`) REFERENCES `payment_provider` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table platform_environment_theme
# ------------------------------------------------------------

DROP TABLE IF EXISTS `platform_environment_theme`;

CREATE TABLE `platform_environment_theme` (
  `platform_environment_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `theme_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`platform_environment_id`,`theme_id`),
  KEY `IDX_B08150B652E1E0F3` (`platform_environment_id`),
  KEY `IDX_B08150B659027487` (`theme_id`),
  CONSTRAINT `FK_B08150B652E1E0F3` FOREIGN KEY (`platform_environment_id`) REFERENCES `platform_environment` (`code`),
  CONSTRAINT `FK_B08150B659027487` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
