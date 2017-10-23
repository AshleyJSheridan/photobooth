CREATE TABLE `photobooth`.`photos`( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `uri` VARCHAR(255) NOT NULL, `taken_at` DATETIME NOT NULL, `display` ENUM('yes','no') NOT NULL DEFAULT 'yes', PRIMARY KEY (`id`) ) ENGINE=INNODB CHARSET=utf8 COLLATE=utf8_general_ci;

