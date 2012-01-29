ALTER TABLE  `users` ADD  `user_pass` VARCHAR( 50 ) NOT NULL AFTER  `user_name` ;
INSERT INTO `users` (`user_id`, `user_name`, `user_pass`, `user_openid`, `user_fname`, `user_email`, `user_image`, `user_type`, `user_enabled`, `user_uid`, `user_notes`) VALUES
('', 'admin', ENCRYPT('password'), '', 'Admin User', 'info@example.org', '', 3, 1, MD5(NOW()), '');
