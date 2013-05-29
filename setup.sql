CREATE DATABASE badges;
USE badges;

GRANT ALL PRIVILEGES ON badges.*
    TO 'badger'@'localhost'
    WITH GRANT OPTION;

CREATE TABLE `Config` (
    `id` INT UNSIGNED AUTO_INCREMENT,    -- P Row ID
    `key` VARCHAR(32)          NOT NULL, -- U Config Key
    `value`   TINYINT UNSIGNED NOT NULL, -- - Value
    
    PRIMARY KEY (`id`),
    UNIQUE KEY  (`key`)
);
-- Default configuration
INSERT INTO Config VALUES (null, 'default_privacy', 2);
INSERT INTO Config VALUES (null, 'min_privacy',     1);
INSERT INTO Config VALUES (null, 'require_email',   1);
INSERT INTO Config VALUES (null, 'new_restrict',    2);

CREATE TABLE `Event` (
    `id` INT UNSIGNED AUTO_INCREMENT,             -- P Event ID
    `time`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- - Time
    `ip`   VARCHAR(15)          NOT NULL,         -- - IP
    `event` VARCHAR(8)          NOT NULL,         -- - Event type
    `data` VARCHAR(63)          NOT NULL,         -- - Event data
    `user_id`      INT UNSIGNED,                  -- - User ID
    
    PRIMARY KEY (`id`)
);

CREATE TABLE `User` (
    `id` INT UNSIGNED AUTO_INCREMENT,     -- P User ID
    `username`     VARCHAR(24) NOT NULL,  -- U Username
    `password`        CHAR(60) NOT NULL,  -- - Password hash
    `email`       VARCHAR(128) NOT NULL,  -- U Email address
    `first`        VARCHAR(32) NOT NULL,  -- - First name
    `last`         VARCHAR(32) NOT NULL,  -- - Last name
    `role`    TINYINT UNSIGNED NOT NULL,  -- - User role
    `privacy` TINYINT UNSIGNED NOT NULL,  -- - Privacy setting
    `alive`   TINYINT UNSIGNED DEFAULT 1, -- - 0 for deleted, 1 for alive
    
    PRIMARY KEY (`id`),
    UNIQUE KEY  (`username`),
    UNIQUE KEY  (`email`)
);

-- Function to get User ID from username
DELIMITER #
CREATE FUNCTION  `badges`.`getUserID`(strUsername VARCHAR(24))
    RETURNS INT
    BEGIN
        return (SELECT User.id FROM User WHERE User.username = strUsername);
    END;
#
DELIMITER ;

-- Login table, tracks user logins
CREATE TABLE `Login` (
    `id` INT UNSIGNED AUTO_INCREMENT,           -- P Login ID
    `time`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- - Time
    `ip`  VARCHAR(15)          NOT NULL,         -- - IP
    `user_id`     INT UNSIGNED NOT NULL,         -- F User ID
    `success` TINYINT UNSIGNED NOT NULL,         -- - Login success
    
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES User(`id`)
);

CREATE TABLE `Badge` (
    `id` INT UNSIGNED AUTO_INCREMENT, -- P Badge ID
    `name` VARCHAR(64) NOT NULL,      -- U Name
    `alive`    TINYINT DEFAULT 1,     -- - 0 for deleted, 1 for alive
    
    PRIMARY KEY (`id`),
    UNIQUE KEY  (`name`)
);

CREATE TABLE `Icon` (
    `id` INT UNSIGNED AUTO_INCREMENT, -- P Icon ID
    `path` VARCHAR(64) NOT NULL,      -- - Image path
    
    PRIMARY KEY (`id`)
);

CREATE TABLE `BadgeLevel` (
    `id` INT UNSIGNED AUTO_INCREMENT,  -- P- Row ID
    `badge_id`  INT UNSIGNED NOT NULL, -- UF Badge ID
    `level` TINYINT UNSIGNED NOT NULL, -- U- Level
    `desc`     TEXT          NOT NULL, -- -- Badge description
    `criteria` TEXT          NOT NULL, -- -- Badge criteria
    `self`  TINYINT UNSIGNED NOT NULL, -- -- Self approvable (boolean)
    `locked`    INT UNSIGNED,          -- -F Locked Icon ID
    `unlocked`  INT UNSIGNED,          -- -F Unlicked Icon ID
    
    PRIMARY KEY (`id`),
    FOREIGN KEY (`badge_id`) REFERENCES Badge(`id`),
    FOREIGN KEY (`locked`)   REFERENCES Icon(`id`),
    FOREIGN KEY (`unlocked`) REFERENCES Icon(`id`),
    UNIQUE KEY  (`badge_id`, `level`)
);

CREATE TABLE `Progress` (
    `id` INT UNSIGNED AUTO_INCREMENT,    -- P Row ID
    `user_id`     INT UNSIGNED NOT NULL, -- F User ID
    `badge_id`    INT UNSIGNED NOT NULL, -- F Badge ID
    `badge_level` INT UNSIGNED NOT NULL, -- F Badge Level
    `link`       TEXT          NOT NULL, -- - Evidence link
    `comment`    TEXT          NOT NULL, -- - User Comment
    `alive`   TINYINT UNSIGNED NOT NULL, -- - 0 for deleted, 1 for alive
    
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`)  REFERENCES User(`id`),
    FOREIGN KEY (`badge_id`) REFERENCES Badge(`id`),
    UNIQUE KEY  (`user_id`, `badge_id`, `badge_level`)
);