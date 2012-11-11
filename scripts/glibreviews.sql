-- A movie record
DROP TABLE IF EXISTS `movie`;
CREATE TABLE `movie` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(128) DEFAULT NULL,
    `rt_id` BIGINT UNSIGNED DEFAULT NULL,
    `created_time` BIGINT(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- A glib rephrasing of the movie title
DROP TABLE IF EXISTS `glib_name`;
CREATE TABLE `glib_name` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(128) DEFAULT NULL,
    `movie_id` BIGINT UNSIGNED DEFAULT NULL,
    `score` INT NOT NULL DEFAULT 0,
    `created_time` BIGINT(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- words to substitute in the movie title to create 'glib_name'
DROP TABLE IF EXISTS `word`;
CREATE TABLE `word` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `word` VARCHAR(128) DEFAULT NULL,
    `score` INT NOT NULL DEFAULT 0,
    UNIQUE KEY (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- many-to-many table linking glib_name to word
DROP TABLE IF EXISTS `glib_name_word`;
CREATE TABLE `glib_name_word` (
    `glib_name_id` BIGINT UNSIGNED NOT NULL,
    `word_id` BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY (`glib_name_id`, `word_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `movie` ADD COLUMN `release_date` BIGINT(20) DEFAULT NULL;
