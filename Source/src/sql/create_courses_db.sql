CREATE TABLE IF NOT EXISTS `requirements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `num_required` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `requirements_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `requirements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1952 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_area` varchar(255) NOT NULL,
  `number` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `weight` decimal(4,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `department` varchar(1023) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `prerequisites` varchar(255) DEFAULT NULL,
  `requirements_id` int(11) DEFAULT NULL,
  `credits_required` decimal(4,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_code` (`subject_area`,`number`),
  KEY `requirements_id` (`requirements_id`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`requirements_id`) REFERENCES `requirements` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1914 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `equates` (
  `course_id` int(11) NOT NULL,
  `equated_id` int(11) NOT NULL,
  KEY `course_id` (`course_id`),
  KEY `equated_id` (`equated_id`),
  CONSTRAINT `equates_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equates_ibfk_2` FOREIGN KEY (`equated_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `offerings` (
  `course_id` int(11) NOT NULL,
  `semester` char(1) NOT NULL,
  KEY `course_id` (`course_id`),
  CONSTRAINT `offerings_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `course_requirements` (
  `course_id` int(11) NOT NULL,
  `requirement_id` int(11) NOT NULL,
  KEY `course_id` (`course_id`),
  KEY `requirement_id` (`requirement_id`),
  CONSTRAINT `course_requirements_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_requirements_ibfk_2` FOREIGN KEY (`requirement_id`) REFERENCES `requirements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

DROP FUNCTION generateUUID;
DELIMITER //

CREATE FUNCTION generateUUID()
RETURNS CHAR(32)
BEGIN
    DECLARE _id CHAR(32);
    DECLARE num_rows INT;

    SET num_rows = 1;

    WHILE num_rows > 0 DO
        SET _id = REPLACE(UUID(), '-', '');
        SELECT COUNT(id) INTO num_rows FROM plans WHERE id = UNHEX(_id);
    END WHILE;

    RETURN _id;
END //

DELIMITER ;

CREATE TABLE IF NOT EXISTS `plans` (
  `id` BINARY(16) PRIMARY KEY DEFAULT UNHEX(REPLACE(UUID(),'-','')),
  `last_edit` datetime NOT NULL DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `course_plan` (
  `course_id` integer NOT NULL,
  `plan_id` BINARY(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE UNIQUE INDEX IF NOT EXISTS `course_code` ON `courses` (`subject_area`, `number`);

ALTER TABLE `offerings` ADD FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

ALTER TABLE `courses` ADD FOREIGN KEY (`requirements_id`) REFERENCES `requirements` (`id`) ON DELETE SET NULL;

ALTER TABLE `equates` ADD FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

ALTER TABLE `equates` ADD FOREIGN KEY (`equated_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

ALTER TABLE `requirements` ADD FOREIGN KEY (`parent_id`) REFERENCES `requirements` (`id`) ON DELETE CASCADE;

ALTER TABLE `course_requirements` ADD FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

ALTER TABLE `course_requirements` ADD FOREIGN KEY (`requirement_id`) REFERENCES `requirements` (`id`) ON DELETE CASCADE;

ALTER TABLE `course_plan` ADD FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

ALTER TABLE `course_plan` ADD FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE;
