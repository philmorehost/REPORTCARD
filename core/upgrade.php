<?php
/**
 * upgrade.php - A simple database schema updater.
 *
 * This script checks for missing columns and adds them. It's a rudimentary
 * migration system to ensure the database schema matches the application code.
 * This script is included from init.php after a successful DB connection.
 */

try {
    /**
     * Checks if a table exists in the current database.
     */
    function table_exists($pdo, $table) {
        try {
            $stmt = $pdo->prepare("SELECT 1 FROM `information_schema`.`tables` WHERE `table_schema` = DATABASE() AND `table_name` = :table");
            $stmt->execute(['table' => $table]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            error_log("Could not check for table existence: {$e->getMessage()}");
            return false; // Fail safely
        }
    }

    /**
     * Checks if a column exists in a given table.
     */
    function column_exists($pdo, $table, $column) {
        try {
            $stmt = $pdo->prepare(
                "SELECT 1 FROM `information_schema`.`columns`
                 WHERE `table_schema` = DATABASE()
                 AND `table_name` = :table
                 AND `column_name` = :column"
            );
            $stmt->execute(['table' => $table, 'column' => $column]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            error_log("Could not check for column existence: {$e->getMessage()}");
            return false; // Fail safely
        }
    }

    // --- Schema migration for report_comments table ---
    if (!table_exists($pdo, 'report_comments')) {
        $pdo->exec("
            CREATE TABLE `report_comments` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `school_id` int(11) NOT NULL,
              `user_id` int(11) DEFAULT NULL,
              `comment_type` enum('principal','teacher_general') NOT NULL,
              `comment_text` text NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `school_id` (`school_id`),
              KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
        // Add constraints separately to avoid issues on some MySQL versions
        $pdo->exec("ALTER TABLE `report_comments` ADD CONSTRAINT `report_comments_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;");
        $pdo->exec("ALTER TABLE `report_comments` ADD CONSTRAINT `report_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;");
    } else {
        // If table exists, check for individual columns to ensure backward compatibility
        if (!column_exists($pdo, 'report_comments', 'comment_type')) {
            $pdo->exec("ALTER TABLE `report_comments` ADD `comment_type` ENUM('principal','teacher_general') NOT NULL AFTER `user_id`;");
        }
    }

    // --- Schema migration for report_card_signatures table ---
    if (!table_exists($pdo, 'report_card_signatures')) {
        $pdo->exec("
            CREATE TABLE `report_card_signatures` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `signature_type` enum('text','image') NOT NULL DEFAULT 'text',
              `signature_data` text NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
        $pdo->exec("ALTER TABLE `report_card_signatures` ADD CONSTRAINT `report_card_signatures_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;");
    } else {
        if (!column_exists($pdo, 'report_card_signatures', 'signature_type')) {
            $pdo->exec("ALTER TABLE `report_card_signatures` ADD `signature_type` ENUM('text','image') NOT NULL DEFAULT 'text' AFTER `user_id`;");
        }
        if (!column_exists($pdo, 'report_card_signatures', 'signature_data')) {
            $pdo->exec("ALTER TABLE `report_card_signatures` ADD `signature_data` TEXT NOT NULL AFTER `signature_type`;");
        }
        if (!column_exists($pdo, 'report_card_signatures', 'updated_at')) {
            $pdo->exec("ALTER TABLE `report_card_signatures` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;");
        }
    }

    // --- Full Rebranding from WhatsApp to SMS ---

    // 1. Rename whatsapp_transactions to sms_transactions
    if (table_exists($pdo, 'whatsapp_transactions')) {
        $pdo->exec("RENAME TABLE `whatsapp_transactions` TO `sms_transactions`;");
    } else if (!table_exists($pdo, 'sms_transactions')) {
        $pdo->exec("
            CREATE TABLE `sms_transactions` (
              `id` int(11) NOT NULL AUTO_INCREMENT, `school_id` int(11) NOT NULL, `credits_purchased` int(11) NOT NULL,
              `amount` decimal(10,2) NOT NULL, `payment_method` varchar(50) NOT NULL, `status` varchar(50) NOT NULL,
              `reference` varchar(255) DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`), KEY `school_id` (`school_id`), UNIQUE KEY `reference` (`reference`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
        $pdo->exec("ALTER TABLE `sms_transactions` ADD CONSTRAINT `sms_transactions_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;");
    }

    // Fix cost column to amount
    if (column_exists($pdo, 'sms_transactions', 'cost')) {
        $pdo->exec("ALTER TABLE `sms_transactions` CHANGE `cost` `amount` DECIMAL(10,2) NOT NULL;");
    }
    if (!column_exists($pdo, 'sms_transactions', 'amount')) {
        $pdo->exec("ALTER TABLE `sms_transactions` ADD `amount` DECIMAL(10,2) NOT NULL AFTER `credits_purchased`;");
    }

    // Add payment_transaction_id to link to the main transactions table
    if (!column_exists($pdo, 'sms_transactions', 'payment_transaction_id')) {
        $pdo->exec("ALTER TABLE `sms_transactions` ADD COLUMN `payment_transaction_id` INT(11) NULL AFTER `id`;");
        try {
            // Add the foreign key constraint. We wrap this in a try-catch block
            // because the constraint might already exist with a different name,
            // which would cause a fatal error.
            $pdo->exec("ALTER TABLE `sms_transactions` ADD CONSTRAINT `fk_sms_payment_id` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;");
        } catch (PDOException $e) {
            error_log("Could not add foreign key for sms_transactions.payment_transaction_id: {$e->getMessage()}");
        }
    }

    // 2. Rename whatsapp_log to sms_log
    if (table_exists($pdo, 'whatsapp_log')) {
        $pdo->exec("RENAME TABLE `whatsapp_log` TO `sms_log`;");
    } else if (!table_exists($pdo, 'sms_log')) {
        $pdo->exec("
            CREATE TABLE `sms_log` (
              `id` int(11) NOT NULL AUTO_INCREMENT, `school_id` int(11) NOT NULL, `student_id` int(11) DEFAULT NULL,
              `phone_number` varchar(20) NOT NULL, `message` text NOT NULL, `status` enum('sent','failed','pending') NOT NULL DEFAULT 'pending',
              `cost` decimal(10,4) NOT NULL DEFAULT '0.0000', `api_response` text, `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`), KEY `school_id` (`school_id`), KEY `student_id` (`student_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
    } else {
        // If the table exists, check for the legacy column name and rename it.
        if (column_exists($pdo, 'sms_log', 'whatsapp_number')) {
            $pdo->exec("ALTER TABLE `sms_log` CHANGE `whatsapp_number` `phone_number` VARCHAR(20) NOT NULL;");
        }
        // Also ensure the phone_number column exists if the legacy one didn't
        if (!column_exists($pdo, 'sms_log', 'phone_number')) {
            $pdo->exec("ALTER TABLE `sms_log` ADD `phone_number` VARCHAR(20) NOT NULL AFTER `student_id`;");
        }
        // Ensure the 'cost' column exists
        if (!column_exists($pdo, 'sms_log', 'cost')) {
            $pdo->exec("ALTER TABLE `sms_log` ADD `cost` DECIMAL(10,4) NOT NULL DEFAULT '0.0000' AFTER `status`;");
        }
        // Ensure the 'api_response' column exists
        if (!column_exists($pdo, 'sms_log', 'api_response')) {
            $pdo->exec("ALTER TABLE `sms_log` ADD `api_response` TEXT NULL DEFAULT NULL AFTER `cost`;");
        }
    }

    // --- Add/Modify columns for existing legacy tables ---
    // 3. Rename schools.whatsapp_credits to sms_credits
    if (column_exists($pdo, 'schools', 'whatsapp_credits')) {
        $pdo->exec("ALTER TABLE `schools` CHANGE `whatsapp_credits` `sms_credits` INT(11) NOT NULL DEFAULT 0;");
    } else if (!column_exists($pdo, 'schools', 'sms_credits')) {
        $pdo->exec("ALTER TABLE `schools` ADD COLUMN `sms_credits` INT(11) NOT NULL DEFAULT 0 AFTER `student_slots`;");
    }

    // Add sender_id to schools table
    if (!column_exists($pdo, 'schools', 'sender_id')) {
        $pdo->exec("ALTER TABLE `schools` ADD COLUMN `sender_id` VARCHAR(11) NULL DEFAULT NULL AFTER `sms_credits`;");
    }

    // 4. Rename students.parent_whatsapp_number to parent_phone_number
    if (column_exists($pdo, 'students', 'parent_whatsapp_number')) {
        $pdo->exec("ALTER TABLE `students` CHANGE `parent_whatsapp_number` `parent_phone_number` VARCHAR(255) NULL DEFAULT NULL;");
    } else if (!column_exists($pdo, 'students', 'parent_phone_number')) {
        $pdo->exec("ALTER TABLE `students` ADD COLUMN `parent_phone_number` VARCHAR(255) NULL DEFAULT NULL AFTER `parent_email`;");
    }

    if (!column_exists($pdo, 'schools', 'subscription_expires_at')) {
        $pdo->exec("ALTER TABLE `schools` ADD COLUMN `subscription_expires_at` TIMESTAMP NULL AFTER `status`;");
    }
    if (!column_exists($pdo, 'schools', 'language')) {
        $pdo->exec("ALTER TABLE `schools` ADD COLUMN `language` VARCHAR(10) NOT NULL DEFAULT 'en' AFTER `subscription_expires_at`;");
    }
    $pdo->exec("ALTER TABLE `schools` MODIFY COLUMN `status` ENUM('active', 'suspended', 'pending_payment') NOT NULL DEFAULT 'active';");

    if (!column_exists($pdo, 'schools', 'school_level')) {
        $pdo->exec("ALTER TABLE `schools` ADD COLUMN `school_level` ENUM('k-12', 'tertiary') NOT NULL DEFAULT 'k-12' AFTER `name`;");
    }

    // --- Modify schools status column to include closed ---
    $pdo->exec("ALTER TABLE `schools` MODIFY COLUMN `status` ENUM('active', 'suspended', 'pending_payment', 'closed') NOT NULL DEFAULT 'active';");

    if (!column_exists($pdo, 'students', 'parent_name')) {
        $pdo->exec("ALTER TABLE `students` ADD COLUMN `parent_name` VARCHAR(255) NULL AFTER `class`;");
    }
    if (!column_exists($pdo, 'students', 'parent_email')) {
        $pdo->exec("ALTER TABLE `students` ADD COLUMN `parent_email` VARCHAR(255) NULL AFTER `parent_name`;");
    }

    if (!column_exists($pdo, 'schools', 'package_id')) {
        $pdo->exec("ALTER TABLE `schools` ADD COLUMN `package_id` INT(11) NULL AFTER `school_level`;");
        try {
            $pdo->exec("ALTER TABLE `schools` ADD CONSTRAINT `fk_package_id` FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;");
        } catch (PDOException $e) {
            error_log("Could not add foreign key for package_id: {$e->getMessage()}");
        }
    }

    // --- Add optional custom_domain to schools table ---
    if (!column_exists($pdo, 'schools', 'custom_domain')) {
        $pdo->exec("ALTER TABLE `schools` ADD COLUMN `custom_domain` VARCHAR(255) NULL DEFAULT NULL AFTER `language`;");
    }

    // --- Add hero_image_url to system_settings if it doesn't exist ---
    $stmt_check_hero = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = 'hero_image_url'");
    $stmt_check_hero->execute();
    if ($stmt_check_hero->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO system_settings (setting_key, setting_value) VALUES ('hero_image_url', 'https://via.placeholder.com/1920x1080/8f8f8f/ffffff?text=School+Background')");
    }

    // 5. Update system settings for SMS
    // 5a. Rename whatsapp_credit_cost to sms_credit_cost
    $stmt_cost = $pdo->prepare("SELECT * FROM system_settings WHERE setting_key = 'whatsapp_credit_cost'");
    $stmt_cost->execute();
    if ($stmt_cost->fetch()) {
        $pdo->exec("UPDATE system_settings SET setting_key = 'sms_credit_cost' WHERE setting_key = 'whatsapp_credit_cost'");
    }

    // 5b. Remove old WhatsApp/KudiSMS settings
    $pdo->exec("DELETE FROM system_settings WHERE setting_key IN ('kudisms_phone_number_id', 'kudisms_template_code', 'whatsapp_api_provider', 'whatsapp_api_key', 'whatsapp_api_url')");

    // 5c. Add new PhilmoreSMS settings
    $sms_settings = [
        'sms_provider' => 'philmoresms',
        'sms_api_key' => '',
        'sms_sender_id' => ''
    ];
    foreach ($sms_settings as $key => $value) {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = :key");
        $stmt_check->execute(['key' => $key]);
        if ($stmt_check->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO system_settings (setting_key, setting_value) VALUES ('$key', '$value')");
        }
    }

    // --- Add registration_enabled setting if it doesn't exist ---
    $stmt_check_reg = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = 'registration_enabled'");
    $stmt_check_reg->execute();
    if ($stmt_check_reg->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO system_settings (setting_key, setting_value) VALUES ('registration_enabled', '1')");
    }

    // --- Add payment gateway fee settings ---
    $fee_settings = [
        'sms_payment_fee_percentage' => '0',
        'billing_payment_fee_percentage' => '0'
    ];
    foreach ($fee_settings as $key => $value) {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = :key");
        $stmt_check->execute(['key' => $key]);
        if ($stmt_check->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO system_settings (setting_key, setting_value) VALUES ('$key', '$value')");
        }
    }

    // --- Add email verification columns to users table ---
    if (!column_exists($pdo, 'users', 'email_verification_token')) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `email_verification_token` VARCHAR(255) NULL DEFAULT NULL AFTER `full_name`;");
    }
    if (!column_exists($pdo, 'users', 'email_verified_at')) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `email_verified_at` TIMESTAMP NULL DEFAULT NULL AFTER `email_verification_token`;");
    }

    // --- Modify users status column to include unverified ---
    $pdo->exec("ALTER TABLE `users` MODIFY COLUMN `status` ENUM('active', 'inactive', 'unverified') NOT NULL DEFAULT 'unverified';");

    // --- Add password reset columns to users table ---
    if (!column_exists($pdo, 'users', 'password_reset_token')) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `password_reset_token` VARCHAR(255) NULL DEFAULT NULL AFTER `email_verified_at`;");
    }
    if (!column_exists($pdo, 'users', 'password_reset_expires_at')) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `password_reset_expires_at` TIMESTAMP NULL DEFAULT NULL AFTER `password_reset_token`;");
    }

    // --- Create subjects table ---
    if (!table_exists($pdo, 'subjects')) {
        $pdo->exec("
            CREATE TABLE `subjects` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `school_id` INT(11) NOT NULL,
                `subject_name` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
    }

    // --- Create subject_teacher_assignments table ---
    if (!table_exists($pdo, 'subject_teacher_assignments')) {
        $pdo->exec("
            CREATE TABLE `subject_teacher_assignments` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `subject_id` INT(11) NOT NULL,
                `teacher_id` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
    }

    // --- Create support_tickets table ---
    if (!table_exists($pdo, 'support_tickets')) {
        $pdo->exec("
            CREATE TABLE `support_tickets` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `school_id` INT(11) NOT NULL,
                `user_id` INT(11) NOT NULL,
                `subject` VARCHAR(255) NOT NULL,
                `status` ENUM('open', 'in_progress', 'closed') NOT NULL DEFAULT 'open',
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`school_id`) REFERENCES `schools`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
    }

    // --- Create support_ticket_replies table ---
    if (!table_exists($pdo, 'support_ticket_replies')) {
        $pdo->exec("
            CREATE TABLE `support_ticket_replies` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `ticket_id` INT(11) NOT NULL,
                `user_id` INT(11) NOT NULL,
                `message` TEXT NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
    }

} catch (PDOException $e) {
    // If the upgrade fails, it's a critical error.
    die("CRITICAL ERROR: Could not update the database schema. " . $e->getMessage());
}
?>