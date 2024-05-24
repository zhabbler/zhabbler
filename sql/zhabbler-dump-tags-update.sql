DROP TABLE IF EXISTS `followed_tags`;
CREATE TABLE `followed_tags` (
  `followedTagID` int(11) NOT NULL AUTO_INCREMENT,
  `followedTag` varchar(32) NOT NULL,
  `followedTagBy` int(11) NOT NULL,
  PRIMARY KEY (`followedTagID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


LOCK TABLES `followed_tags` WRITE;
UNLOCK TABLES;

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `tagID` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(24) NOT NULL,
  PRIMARY KEY (`tagID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `tags` WRITE;
UNLOCK TABLES;

ALTER TABLE `zhabs` ADD `zhabTags` LONGTEXT;
