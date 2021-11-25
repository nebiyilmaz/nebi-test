
INSERT INTO `currency` (`code`, `foreign_names`, `symbol`, `symbol_before_price`, `created_at`, `updated_at`)
VALUES
	('GBP','British Pound','£',0,'2019-10-12 21:10:50',NULL),
	('SEK','Swedish Krona','kr',1,'2019-10-12 21:10:11','2019-10-12 21:10:25'),
	('NOK','Norwegian Krona','kr',1,'2019-10-12 21:10:11','2019-10-12 21:10:25'),
	('DKK','Danish Krona','kr',1,'2019-10-12 21:10:11','2019-10-12 21:10:25'),
	('USD','US Dollars','$',0,'2019-10-12 21:10:23',NULL),
	('EUR','Euro','€',1,'2019-10-12 21:10:23',NULL);

INSERT INTO `language` (`code`, `name`, `locale_code`, `created_at`, `updated_at`)
VALUES
	('en','English','en','2019-10-12 21:11:23',NULL),
	('no','Norwegian','no','2019-10-12 21:11:23',NULL),
	('da','Danish','da','2019-10-12 21:11:23',NULL),
	('fi','Finish','fi','2019-10-12 21:11:23',NULL),
	('sv','Swedish','sv','2019-10-12 21:11:27',NULL);

INSERT INTO `lender` (`id`, `name`, `app_name`, `settings`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	('lender-1','Lender 1','Lender1','{}','2019-10-12 21:11:40',NULL,NULL),
	('lender-2','Lender 2','Lender2','{}','2019-10-12 21:11:40',NULL,NULL),
	('lender-3','Lender 3','Lender3','{}','2019-10-12 21:11:40',NULL,NULL);


INSERT INTO `payment_provider` (`id`, `name`, `app_name`, `settings`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	('payment-provider-1','Payment Provider 1','PaymentProvider1','{}','2019-10-12 21:06:54','2019-10-12 21:07:29',NULL),
	('payment-provider-2','Payment Provider 2','PaymentProvider2','{}','2019-10-12 21:11:40',NULL,NULL),
	('payment-provider-3','Payment Provider 3','PaymentProvider3','{}','2019-10-12 21:11:40',NULL,NULL);

INSERT INTO `country` (`code`, `currency_id`, `language_id`, `name`, `created_at`, `updated_at`)
VALUES
	('GB','GBP','en','United Kingdom','2019-10-12 21:13:21',NULL),
	('SE','SEK','sv','Sweden','2019-10-12 21:13:12',NULL),
	('US','USD','en','United States','2019-10-12 21:13:30',NULL),
	('DK','DKK','da','Denmark','2019-10-12 21:13:30',NULL),
	('NO','NOK','no','Norway','2019-10-12 21:13:30',NULL),
	('FI','EUR','fi','Finland','2019-10-12 21:13:30',NULL);

INSERT INTO `platform_environment` (`code`, `theme_id`, `name`, `settings`, `created_at`, `updated_at`, `deleted_at`)
VALUES
	('divido', NULL, 'Divido', '{\"urls\":{\"application_form\":\"http:\\/\\/localhost:4010\\/#\\/token\\/\",\"merchant_portal\":\"https:\\/\\/merchant.testing.divido.com\",\"platform\":\"https:\\/\\/platform.api.dev.divido.net\"},\"security\":{\"two_factor_authentification_required\":false,\"saml\":{\"url\":\"https:\\/\\/saml-authentification-api-pub.api.dev.divido.net\\/\",\"profile\":\"divido_google\",\"label\":\"Sign in with Google\",\"sso\":{\"method\":\"platform\",\"url\":\"https:\\/\\/platform.api.dev.divido.net\\/admin\\/account\\/sso\\/\",\"encryption_key\":\"UPcKazhjrJpUvRqWsdghs3uWgM54E6hVFQxWg5M7497eub8SvUmxwNET8ZJ9\"}}}}', '2016-12-08 14:03:11', '2019-09-11 11:28:23', NULL),
	('nordea', NULL, 'Nordea', '{\"applicationUrl\":\"https:\\/\\/application.api.dev.divido.net\\/#\\/token\\/\",\"email\":{\"sender\":{\"email\":\"no-reply+sbx@nordea-pwd.com\",\"name\":\"Nordea\"}},\"platformUrl\":\"https:\\/\\/platform.api.dev.divido.net\",\"merchantPortalUrl\":\"https:\\/\\/merchant-portal.api.dev.divido.net\",\"segment\":{\"id\":null},\"stripe\":{\"clientId\":\"ca_Bce3odzhmKl3ZQByrifrDMGY5orFJLqJ\",\"fee\":0,\"publishableKey\":\"pk_test_Qs8hNs4enxi4VhBFRB9QSrcP\",\"secretKey\":\"sk_test_aVa9UJp5Qan8jrG2O4gmdEs1\"},\"tracking\":{\"ga\":null,\"intercom\":null,\"mixpanel\":null,\"segment\":null}}', '2019-01-08 12:36:05', '2019-01-12 21:35:00', NULL);

INSERT INTO `platform_environment_country` (`platform_environment_id`, `country_id`)
VALUES
	('divido', 'GB'),
	('divido', 'US'),
	('nordea', 'NO'),
	('nordea', 'FI'),
	('nordea', 'SE'),
	('nordea', 'DK');

INSERT INTO `platform_environment_lender` (`platform_environment_id`, `lender_id`)
VALUES
	('divido', 'lender-1'),
	('divido', 'lender-2'),
	('divido', 'lender-3'),
	('nordea', 'lender-3');

INSERT INTO `platform_environment_payment_provider` (`platform_environment_id`, `payment_provider_id`)
VALUES
	('nordea', 'payment-provider-1'),
	('divido', 'payment-provider-2');

INSERT INTO `branch` (`id`, `platform_environment_id`, `name`, `settings`, `deleted_at`, `created_at`, `updated_at`)
VALUES
	('branch-1', 'nordea', 'Branch NO', '{}', NULL, '2019-06-24 10:21:32', NULL),
	('branch-2', 'nordea', 'Branch SE', '{}', NULL, '2019-06-24 10:21:32', NULL);


INSERT INTO `merchant` (`id`, `platform_environment_id`, `branch_id`, `theme_id`, `active`, `shared_secret`, `name`, `short_name`, `website_url`, `phone_number`, `email_address`, `layout_logo`, `layout_css`, `layout_styling`, `layout_html`, `settings`, `metadata`, `deleted_at`, `created_at`, `updated_at`)
VALUES
	('merchant-1', 'divido', NULL, NULL, 1, 'secret1', 'Divido - Active', 'Divido', 'www.divido.com', '', 'info@divido.com', '', '', '[]', '', '{\n  \"notifications\": {\n    \"emails\": {\n      \"active\": true,\n      \"customer_email_disabled\": false,\n      \"cc_proposal_creator\": true,\n      \"language\": \"en\",\n      \"email\": [\n        \"hallsten@me.com\"\n      ],\n      \"actions\": [\n      ]\n    },\n    \"webhooks\": {\n      \"active\": false,\n      \"urls\": [\n        \"http://webhook.url\"\n      ]\n    }\n  },\n  \"deposit\": {\n    \"use_3d_secure\": true,\n    \"collect_manually_for_in_store\": false,\n    \"collect_manually_for_online\": false,\n    \"default_value\": 0.15\n  }\n}', '{\n  \"key\": \"value\"\n}', NULL, '2017-06-05 15:22:57', '2019-10-13 23:09:09'),
	('merchant-2', 'divido', NULL, NULL, 0, 'secret2', 'Divido - Inctive', 'Divido', 'www.divido.com', '', 'info@divido.com', '', '', '[]', '', '{\n  \"notifications\": {\n    \"emails\": {\n      \"active\": true,\n      \"customer_email_disabled\": false,\n      \"cc_proposal_creator\": true,\n      \"language\": \"en\",\n      \"email\": [\n        \"hallsten@me.com\"\n      ],\n      \"actions\": [\n      ]\n    },\n    \"webhooks\": {\n      \"active\": false,\n      \"urls\": [\n        \"http://webhook.url\"\n      ]\n    }\n  },\n  \"deposit\": {\n    \"use_3d_secure\": true,\n    \"collect_manually_for_in_store\": false,\n    \"collect_manually_for_online\": false,\n    \"default_value\": 0.15\n  }\n}', '{\n  \"key\": \"value\"\n}', NULL, '2017-06-05 15:22:57', '2019-10-13 23:09:09'),
	('merchant-3', 'nordea', 'branch-1', NULL, 1, 'secret3', 'Nordea - Branch 1', 'Nordea', 'www.divido.com', '', 'info@nordea.poweredbydivido.com', '', '', '[]', '', '{\n  \"notifications\": {\n    \"emails\": {\n      \"active\": true,\n      \"customer_email_disabled\": false,\n      \"cc_proposal_creator\": true,\n      \"language\": \"en\",\n      \"email\": [\n        \"hallsten@me.com\"\n      ],\n      \"actions\": [\n      ]\n    },\n    \"webhooks\": {\n      \"active\": false,\n      \"urls\": [\n        \"http://webhook.url\"\n      ]\n    }\n  },\n  \"deposit\": {\n    \"use_3d_secure\": true,\n    \"collect_manually_for_in_store\": false,\n    \"collect_manually_for_online\": false,\n    \"default_value\": 0.15\n  }\n}', '{\n  \"key\": \"value\"\n}', NULL, '2017-06-05 15:22:57', '2019-10-13 23:09:09'),
	('merchant-4', 'nordea', 'branch-2', NULL, 1, 'secret4', 'Nordea - Branch 2', 'Nordea', 'www.divido.com', '', 'info@nordea.poweredbydivido.com', '', '', '[]', '', '{\n  \"notifications\": {\n    \"emails\": {\n      \"active\": true,\n      \"customer_email_disabled\": false,\n      \"cc_proposal_creator\": true,\n      \"language\": \"en\",\n      \"email\": [\n        \"hallsten@me.com\"\n      ],\n      \"actions\": [\n      ]\n    },\n    \"webhooks\": {\n      \"active\": false,\n      \"urls\": [\n        \"http://webhook.url\"\n      ]\n    }\n  },\n  \"deposit\": {\n    \"use_3d_secure\": true,\n    \"collect_manually_for_in_store\": false,\n    \"collect_manually_for_online\": false,\n    \"default_value\": 0.15\n  }\n}', '{\n  \"key\": \"value\"\n}', NULL, '2017-06-05 15:22:57', '2019-10-13 23:09:09');

##

INSERT INTO `merchant_channel` (`id`, `country_id`, `merchant_id`, `active`, `name`, `type`, `deleted_at`, `created_at`, `updated_at`)
VALUES
	('merchant-1-channel-1', 'GB', 'merchant-1', 1, '[GB] Online', 'online', NULL, '2018-07-24 02:00:56', '2018-07-24 02:00:56'),
	('merchant-1-channel-2', 'GB', 'merchant-1', 1, '[GB] In-store', 'instore', NULL, '2018-07-24 02:00:56', '2018-07-24 02:00:56');

INSERT INTO `merchant_api_key` (`id`, `merchant_id`, `merchant_channel_id`, `api_key`, `public`, `deleted_at`, `created_at`, `updated_at`)
VALUES
	('merchant-1-api-key-1', 'merchant-1', 'merchant-1-channel-1', 'functional.apikey-1', 0, NULL, '2018-07-01 02:48:15', '2018-07-01 02:48:15'),
	('merchant-1-api-key-2', 'merchant-1', 'merchant-1-channel-1', 'functional.apikey-2', 1, NULL, '2018-07-01 02:48:15', '2018-07-01 02:48:15'),
	('merchant-1-api-key-3', 'merchant-1', 'merchant-1-channel-2', 'functional.apikey-3', 1, NULL, '2018-07-01 02:48:15', '2018-07-01 02:48:15'),
	('merchant-1-api-key-4', 'merchant-1', 'merchant-1-channel-1', 'functional.apikey-4', 1, '2018-07-01 02:48:15', '2018-07-01 02:48:15', '2018-07-01 02:48:15');

INSERT INTO `merchant_finance_option` (`id`, `merchant_id`, `country_id`, `active`, `type`, `description`, `interest_rate_percentage`, `agreement_duration_months`, `deferral_period_months`, `order`, `minimum_amount`, `maximum_amount`, `minimum_deposit_percentage`, `maximum_deposit_percentage`, `margin_rate_percentage`, `merchant_minimum_deposit_percentage`, `merchant_maximum_deposit_percentage`, `minimum_repayment_amount`, `minimum_repayment_percentage`, `finance_settings`, `setup_fee_amount`, `instalment_fee_amount`, `index_rate_name`, `deleted_at`, `created_at`, `updated_at`)
VALUES
	('merchant-1-finance-option-1', 'merchant-1', 'GB', 1, 'fixed_term_loan', '6 months interest free', 0.00000, 6, 0, 1, 25000, 0, 0.00000, 0.50000, NULL, 0.00000, 0.00000, 0, 0.00000, '{}', 0, 0, '', NULL, '2019-09-09 15:10:49', '2019-09-09 15:10:49'),
	('merchant-1-finance-option-2', 'merchant-1', 'GB', 1, 'fixed_term_loan', '6 months interest free - deleted', 0.00000, 6, 0, 1, 25000, 0, 0.00000, 0.50000, NULL, 0.00000, 0.00000, 0, 0.00000, '{}', 0, 0, '', '2019-09-09 15:10:49', '2019-09-09 15:10:49', '2019-09-09 15:10:49'),
	('merchant-1-finance-option-3', 'merchant-1', 'GB', 1, 'fixed_term_loan', '6 months interest free - 10% deposit', 0.00000, 6, 0, 1, 25000, 0, 0.10000, 0.10000, NULL, 0.00000, 0.00000, 0, 0.00000, '{}', 0, 0, '', '2019-09-09 15:10:49', '2019-09-09 15:10:49', '2019-09-09 15:10:49');

INSERT INTO `application` (`id`, `token`, `platform_environment_id`, `branch_id`, `application_submission_id`, `country_id`, `currency_id`, `language_id`, `merchant_id`, `customer_id`, `merchant_finance_id`, `merchant_finance_option_id`, `merchant_channel_id`, `merchant_api_key_id`, `merchant_user_id`, `finalised`, `finalisation_required`, `status`, `purchase_price`, `deposit_amount`, `deposit_status`, `lender_fee`, `lender_fee_reported_date`, `form_data`, `applicants`, `product_data`, `metadata`, `commission`, `partner_commission`, `merchant_reference`, `merchant_response_url`, `merchant_checkout_url`, `merchant_redirect_url`, `created_at`, `updated_at`, `finance_settings`, `lender_loan_reference`, `available_finance_options`, `deposit_reference`, `activation_status`)
VALUES
	('-proposal-no-submission-', '0123456789', 'divido', NULL, NULL, 'GB', 'GBP', 'en', 'merchant-1', NULL, NULL, 'merchant-1-finance-option-1', 'merchant-1-channel-1', 'merchant-1-api-key-1', NULL, 0, 0, 'PROPOSAL', 100000, 0, 'NO-DEPOSIT', 0, NULL, '{\n  \"firstName\": \"Ann\",\n  \"lastName\": \"Heselden\",\n  \"gender\": \"female\",\n  \"phoneNumber\": \"652110302\",\n  \"email\": \"hallsten@me.com\"\n}', '{\n  \"value\": [\n    {\n      \"value\": {\n        \"personal_details\": {\n          \"value\": {\n            \"first_name\": {\n              \"value\": \"Ann\"\n            },\n            \"last_name\": {\n              \"value\": \"Heselden\"\n            }\n          }\n        },\n        \"contact_details\": {\n          \"value\": {\n            \"phone_numbers\": {\n              \"value\": [\n                {\n                  \"value\": \"652110302\",\n                  \"metadata\": {\n                    \"uuid\": \"718182eb-fb28-4a05-925c-cd9f70af43ff\"\n                  }\n                }\n              ]\n            },\n            \"email_addresses\": {\n              \"value\": [\n                {\n                  \"value\": \"hallsten@me.com\",\n                  \"metadata\": {\n                    \"uuid\": \"60868fdc-a55f-423c-9d17-8365b6d01815\"\n                  }\n                }\n              ]\n            }\n          }\n        }\n      },\n      \"metadata\": {\n        \"uuid\": \"1dcef1ed-bbda-4475-813c-adf488f7ee02\"\n      }\n    }\n  ]\n}', '[]', '{}', 0, 0, '', NULL, NULL, NULL, '2019-09-11 00:50:37', '2019-10-13 23:42:18', '{\"amount\":100000,\"deposit_amount\":0,\"plan\":{\"agreement_duration_months\":12,\"calculation_family\":\"6b788c1674341d8ba361510fd3f706f6\",\"country_code\":\"ES\",\"credit_amount\":{\"minimum_amount\":0,\"maximum_amount\":0},\"deferral_period_months\":0,\"deposit\":{\"minimum_percentage\":0,\"maximum_percentage\":0},\"description\":\"12 months interest free\",\"fees\":{\"instalment_fee_amount\":0,\"setup_fee_amount\":0},\"id\":\"10cc6be5-840c-4146-b382-50d95ac84585\",\"repayment\":{\"minimum_amount\":0,\"minimum_percentage\":0},\"margin_rate_percentage\":0,\"interest_rate_percentage\":0,\"lender_code\":null,\"index_rate\":{\"percentage\":0,\"registered_at\":null}}}', NULL, NULL, 'DEPOSTIREF', 'ACTIVATIONSTATUS');