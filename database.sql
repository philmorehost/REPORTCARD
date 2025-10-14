--
-- Database: `ars`
--

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--
CREATE TABLE IF NOT EXISTS `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `student_limit` int(11) NOT NULL,
  `features` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--
CREATE TABLE IF NOT EXISTS `schools` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `school_level` enum('k-12','tertiary') NOT NULL DEFAULT 'k-12',
  `package_id` int(11) DEFAULT NULL,
  `address` text,
  `student_slots` int(11) NOT NULL DEFAULT '0',
  `sms_credits` int(11) NOT NULL DEFAULT '0',
  `sender_id` varchar(11) DEFAULT NULL,
  `status` enum('active','suspended','pending_payment') NOT NULL DEFAULT 'active',
  `subscription_expires_at` timestamp NULL DEFAULT NULL,
  `language` varchar(10) NOT NULL DEFAULT 'en',
  `logo_url` varchar(255) DEFAULT NULL,
  `brand_color` varchar(7) DEFAULT '#0d6efd',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `school_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('super_admin','school_admin','teacher') NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `student_id_number` varchar(255) DEFAULT NULL,
  `class` varchar(255) NOT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `parent_email` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','graduated') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `report_templates`
--
CREATE TABLE IF NOT EXISTS `report_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `file_path` varchar(255) NOT NULL,
  `thumbnail_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `report_card_settings`
--
CREATE TABLE IF NOT EXISTS `report_card_settings` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `columns` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `published_reports`
--
CREATE TABLE IF NOT EXISTS `published_reports` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_period` varchar(255) NOT NULL,
  `unique_hash` varchar(64) NOT NULL,
  `data_snapshot` json NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--
CREATE TABLE IF NOT EXISTS `classes` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `academic_period` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--
CREATE TABLE IF NOT EXISTS `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--
CREATE TABLE IF NOT EXISTS `grades` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `grades` json NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--
CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `proof_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--
CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cms_content`
--
CREATE TABLE IF NOT EXISTS `cms_content` (
  `content_key` varchar(255) NOT NULL,
  `content_value` text,
  PRIMARY KEY (`content_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `school_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `audience` enum('all_schools','all_teachers') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--
CREATE TABLE IF NOT EXISTS `teacher_assignments` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `schools`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `school_id` (`school_id`);

ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_student_id` (`school_id`,`student_id_number`);

ALTER TABLE `report_templates`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `report_card_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_id` (`school_id`),
  ADD KEY `template_id` (`template_id`);

ALTER TABLE `published_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hash` (`unique_hash`),
  ADD KEY `student_period` (`student_id`,`academic_period`);

ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`);

ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollment_id` (`enrollment_id`);

ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD UNIQUE KEY `reference` (`reference`);

ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `school_id` (`school_id`);

ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_id` (`class_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `report_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `report_card_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `published_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `teacher_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

ALTER TABLE `schools`
  ADD CONSTRAINT `fk_package_id` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

ALTER TABLE `report_card_settings`
  ADD CONSTRAINT `rc_settings_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rc_settings_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `report_templates` (`id`);

ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE;

ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

ALTER TABLE `email_templates`
  ADD CONSTRAINT `email_templates_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_2` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;

ALTER TABLE `teacher_assignments`
  ADD CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `report_comments`
--
CREATE TABLE IF NOT EXISTS `report_comments` (
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

-- --------------------------------------------------------

--
-- Table structure for table `report_card_signatures`
--
CREATE TABLE IF NOT EXISTS `report_card_signatures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `signature_type` enum('text','image') NOT NULL DEFAULT 'text',
  `signature_data` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Constraints for new tables
--
ALTER TABLE `report_comments`
  ADD CONSTRAINT `report_comments_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `report_card_signatures`
  ADD CONSTRAINT `report_card_signatures_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `sms_log`
--
CREATE TABLE IF NOT EXISTS `sms_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `phone_number` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `status` enum('sent','failed','pending') NOT NULL DEFAULT 'pending',
  `cost` decimal(10,4) NOT NULL DEFAULT '0.0000',
  `api_response` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Seeding data
--
INSERT IGNORE INTO `packages` (`id`, `name`, `price`, `student_limit`, `features`) VALUES
(1, 'Freemium', 0.00, 50, '["A single template","cannot print","cannot copy","cannot save","cannot share report card link","cannot download","can add logo report card template","can view report card","report card should be watermarked"]'),
(2, 'Premium', 10.00, 0, '["Multiple templates and Access to new templates","can print","can copy","can save","can share report card link","can download","can add logo report card template","can view report card","watermark is removed"]');

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('currency_symbol', '$'),
('currency_code', 'USD'),
('paystack_enabled', '1'),
('flutterwave_enabled', '1'),
('hero_image_url', 'https://via.placeholder.com/1920x1080/8f8f8f/ffffff?text=School+Background'),
('sms_provider', 'philmoresms'),
('sms_api_key', ''),
('sms_sender_id', '');

INSERT IGNORE INTO `cms_content` (`content_key`, `content_value`) VALUES
('hero_title', 'Automated Report Card System'),
('hero_subtitle', 'Simplify your school\'s reporting process with our intuitive and powerful platform.'),
('feature1_title', 'Easy to Use'),
('feature1_text', 'Our user-friendly interface makes it easy for teachers and administrators to manage student data and generate report cards.'),
('feature2_title', 'Customizable Templates'),
('feature2_text', 'Choose from a variety of professionally designed templates to create beautiful and informative report cards.'),
('feature3_title', 'Secure and Reliable'),
('feature3_text', 'We use the latest security measures to protect your data and ensure our platform is always available when you need it.'),
('testimonial1_text', 'This system has saved us countless hours of work. It\'s a game-changer!'),
('testimonial1_author', 'John Doe, Principal'),
('testimonial2_text', 'My teachers love how easy it is to use. It has made our reporting process so much more efficient.'),
('testimonial2_author', 'Jane Smith, Head of School'),
('testimonial3_text', 'The support team is fantastic. They are always available to help with any questions we have.'),
('testimonial3_author', 'Peter Jones, IT Director'),
('contact_email', 'contact@ars.com'),
('contact_phone', '+1-234-567-890');

-- --------------------------------------------------------

--
-- Table structure for table `sms_transactions`
--
CREATE TABLE IF NOT EXISTS `sms_transactions` (
  `id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `credits_purchased` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `sms_transactions`
--
ALTER TABLE `sms_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`),
  ADD UNIQUE KEY `reference` (`reference`);

--
-- AUTO_INCREMENT for table `sms_transactions`
--
ALTER TABLE `sms_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `sms_transactions`
--
ALTER TABLE `sms_transactions`
  ADD CONSTRAINT `sms_transactions_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE;