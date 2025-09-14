use pnjserver_r2;


SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE mem_user MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_reward MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_wallet MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_referral MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_driver MODIFY id INT AUTO_INCREMENT;
ALTER TABLE mem_vehicle MODIFY id INT AUTO_INCREMENT;
ALTER TABLE mem_user MODIFY reward_id int(10) unsigned NULL;
ALTER TABLE mem_user MODIFY wallet_id int(10) unsigned NULL;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE mem_user
ADD COLUMN `is_google_auth` boolean DEFAULT FALSE AFTER `user_name`,
ADD COLUMN `is_meta_auth` boolean DEFAULT FALSE AFTER `user_name`,
ADD COLUMN `is_apple_auth` boolean DEFAULT FALSE AFTER `user_name`,
ADD COLUMN `remember_token` VARCHAR(255) NULL AFTER `user_name`,
ADD COLUMN `email_verified_at` VARCHAR(255) NULL AFTER `user_name`;


SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE mem_reservation_vehicle MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_reservation_driver MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_reservation MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_pricing MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_payment MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_reservation_pending_sync MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE anon_vehicle MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE anon_driver MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE anon_reservation MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE anon_pricing MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE anon_payment MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE anon_reservation_pending_sync MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE stripe_customer MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_wallet_txn MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_wallet_prepaid MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_wallet_prepaid_txn MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_wallet_prepaid_txn_pricing MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_prepaid_package_payment MODIFY id INT UNSIGNED AUTO_INCREMENT;
SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE mem_driver MODIFY owner_id INT(11) UNSIGNED,
    MODIFY createdBy_id INT(11) UNSIGNED,
    MODIFY lastModifiedBy_id INT(11) UNSIGNED;
SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE mem_vehicle MODIFY owner_id INT(11) UNSIGNED,
    MODIFY createdBy_id INT(11) UNSIGNED,
    MODIFY lastModifiedBy_id INT(11) UNSIGNED;
SET FOREIGN_KEY_CHECKS = 1;

-- ALTER TABLE `pnjserver_r2`.`mem_pricing`
-- DROP FOREIGN KEY `FK_84wrif3ogq9qookqg5i2fm8h3`;

-- ALTER TABLE `pnjserver_r2`.`mem_reservation`
-- DROP FOREIGN KEY `FK_lbuuid3ka3d67r50r8cnaanyv`;

-- ALTER TABLE `pnjserver_r2`.`mem_reservation`
-- DROP FOREIGN KEY `FK_q21ns2wbm836iul0jx20t4o26`;

-- ALTER TABLE `pnjserver_r2`.`mem_pricing`
-- DROP FOREIGN KEY `FK_q08kubgtwfq1bso2k1wxdc5en`;

-- ALTER TABLE `pnjserver_r2`.`mem_payment`
-- DROP FOREIGN KEY `FK_87lqy74hs9w1b6rjccrdxwadq`;

-- ALTER TABLE `pnjserver_r2`.`anon_reservation`
-- DROP FOREIGN KEY `FK_qxb84h0l7x5tko1d69a4drmmy`;

-- ALTER TABLE `pnjserver_r2`.`anon_reservation`
-- DROP FOREIGN KEY `FK_f7twpb33igt65knxjglbr1s25`;

-- ALTER TABLE `pnjserver_r2`.`anon_reservation`
-- DROP FOREIGN KEY `FK_11rp59uax7oo0xfduh2nqij8k`;

-- ALTER TABLE `pnjserver_r2`.`anon_pricing`
-- DROP FOREIGN KEY `FK_em4mc5ro8kor25cpiuu6ke1wk`;

-- ALTER TABLE `pnjserver_r2`.`anon_reservation_pending_sync`
-- DROP FOREIGN KEY `FK_8h3bwseviarpdkhvdi1m25n34`;

-- ALTER TABLE `pnjserver_r2`.`anon_payment`
-- DROP FOREIGN KEY `FK_rixofuqhkn0cnorgayjjn33up`;

CREATE TABLE email_change_history (
    id int(11) NOT NULL AUTO_INCREMENT,
    previous_email text DEFAULT NULL,
    new_email text DEFAULT NULL,
    created timestamp NOT NULL DEFAULT current_timestamp(),
    updated timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=987 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE admin_default_pricing ADD extendFee DECIMAL(4, 2);

UPDATE admin_default_pricing SET extendFee = 2;
ALTER TABLE mem_pricing ADD extendFee DECIMAL(19, 2);
ALTER TABLE anon_pricing ADD extendFee DECIMAL(19, 2);
ALTER TABLE mem_reservation ADD reservation_id int UNSIGNED;
ALTER TABLE anon_reservation ADD reservation_id int UNSIGNED;
ALTER TABLE admin_coupon ADD createdDate timestamp NULL DEFAULT NULL;
ALTER TABLE admin_coupon ADD lastModifiedDate timestamp NULL DEFAULT NULL;
ALTER TABLE mem_reservation ADD created_at timestamp NULL DEFAULT NULL;

ALTER TABLE stripe_customer ADD COLUMN `lotType` VARCHAR(255) NULL AFTER `email`;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE mem_lot_payment MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE anon_lot_payment MODIFY id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE mem_reward_txn MODIFY id INT UNSIGNED AUTO_INCREMENT;
SET FOREIGN_KEY_CHECKS = 1;