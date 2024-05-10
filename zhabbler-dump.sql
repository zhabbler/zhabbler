CREATE TABLE `comments` (
  `commentID` int(11) NOT NULL AUTO_INCREMENT,
  `commentBy` int(11) NOT NULL,
  `commentTo` varchar(72) NOT NULL,
  `commentContent` varchar(128) NOT NULL,
  `commentAdded` datetime NOT NULL,
  PRIMARY KEY (`commentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `comments` WRITE;
UNLOCK TABLES;

CREATE TABLE `conversations` (
  `conversationID` int(11) NOT NULL AUTO_INCREMENT,
  `conversationBy` int(11) NOT NULL,
  `conversationTo` int(11) NOT NULL,
  PRIMARY KEY (`conversationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `conversations` WRITE;
UNLOCK TABLES;

CREATE TABLE `emails` (
  `emailID` int(11) NOT NULL AUTO_INCREMENT,
  `emailType` int(11) NOT NULL,
  `emailCode` varchar(72) NOT NULL,
  `emailFor` int(11) NOT NULL,
  PRIMARY KEY (`emailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `emails` WRITE;
UNLOCK TABLES;

CREATE TABLE `follows` (
  `followID` int(11) NOT NULL AUTO_INCREMENT,
  `followBy` int(11) NOT NULL,
  `followTo` int(11) NOT NULL,
  UNIQUE KEY `follows_unique` (`followID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `follows` WRITE;
UNLOCK TABLES;

CREATE TABLE `inbox` (
  `inboxID` int(11) NOT NULL AUTO_INCREMENT,
  `inboxTo` int(11) NOT NULL,
  `inboxMessage` varchar(128) NOT NULL,
  `inboxReaded` int(11) NOT NULL,
  `inboxBy` int(11) NOT NULL,
  `inboxLinked` varchar(128) NOT NULL,
  PRIMARY KEY (`inboxID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `inbox` WRITE;
UNLOCK TABLES;

CREATE TABLE `likes` (
  `likeID` int(11) NOT NULL AUTO_INCREMENT,
  `likeTo` varchar(72) NOT NULL,
  `likeBy` int(11) NOT NULL,
  PRIMARY KEY (`likeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `likes` WRITE;
UNLOCK TABLES;

CREATE TABLE `messages` (
  `messageID` int(11) NOT NULL AUTO_INCREMENT,
  `messageBy` int(11) NOT NULL,
  `messageTo` int(11) NOT NULL,
  `messageContent` longtext NOT NULL,
  `messageAdded` datetime NOT NULL,
  `messageReaded` int(11) NOT NULL,
  `messageImage` longtext NOT NULL,
  PRIMARY KEY (`messageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `messages` WRITE;
UNLOCK TABLES;

CREATE TABLE `notifications` (
  `notificationID` int(11) NOT NULL AUTO_INCREMENT,
  `notificationCausedBy` int(11) NOT NULL,
  `notificationBy` int(11) NOT NULL,
  `notificationTo` int(11) NOT NULL,
  `notificationLink` longtext NOT NULL,
  `notificationAdded` datetime NOT NULL,
  `notificationReaded` int(11) NOT NULL,
  PRIMARY KEY (`notificationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `notifications` WRITE;
UNLOCK TABLES;

CREATE TABLE `personalization` (
  `personalizationID` int(11) NOT NULL AUTO_INCREMENT,
  `personalizationTo` int(11) NOT NULL,
  `personalizationAccent` varchar(7) NOT NULL,
  `personalizationBackgroundColor` varchar(7) NOT NULL,
  `personalizationURL` longtext NOT NULL,
  `personalizationNavbarStyle` int(11) NOT NULL,
  PRIMARY KEY (`personalizationID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `personalization` WRITE;
INSERT INTO `personalization` VALUES (1,1,'#13b552','#00391e','',0);
UNLOCK TABLES;

CREATE TABLE `questions` (
  `questionID` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `questionBy` int(11) NOT NULL,
  `questionTo` int(11) NOT NULL,
  `questionAdded` datetime NOT NULL,
  `questionUniqueID` varchar(128) NOT NULL,
  PRIMARY KEY (`questionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `questions` WRITE;
UNLOCK TABLES;

CREATE TABLE `reports` (
  `reportID` int(11) NOT NULL AUTO_INCREMENT,
  `reportBy` int(11) NOT NULL,
  `reportTo` int(11) NOT NULL,
  PRIMARY KEY (`reportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `reports` WRITE;
UNLOCK TABLES;

CREATE TABLE `sessions` (
  `sessionID` int(11) NOT NULL AUTO_INCREMENT,
  `sessionToken` varchar(255) NOT NULL,
  `sessionIP` varchar(32) NOT NULL,
  `sessionUA` longtext NOT NULL,
  `sessionIdent` varchar(128) NOT NULL,
  PRIMARY KEY (`sessionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `sessions` WRITE;
UNLOCK TABLES;

CREATE TABLE `users` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(20) NOT NULL,
  `name` varchar(48) NOT NULL,
  `email` longtext NOT NULL,
  `password` longtext NOT NULL,
  `profileImage` longtext NOT NULL,
  `profileCover` longtext NOT NULL,
  `biography` varchar(128) NOT NULL,
  `joined` date NOT NULL,
  `token` varchar(255) NOT NULL,
  `activated` int(11) NOT NULL,
  `admin` int(11) NOT NULL,
  `verifed` int(11) NOT NULL,
  `reason` longtext NOT NULL,
  `rateLimitCounter` int(11) NOT NULL,
  `hideLiked` int(11) NOT NULL,
  `hideFollowing` int(11) NOT NULL,
  `askQuestions` int(11) NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES (1,'admin','Administrator','admin@localhost.lh','$2y$10$E12/YDzXf3KxOzx7erc4DO6N.kot3GAi4IbPPoifsKHA1hOHxsN8u','/static/images/no_avatar_1900.png','','','2023-09-12','gcYrLqedKTmBaBDvh1QqSrmDPemTn1HYmH8XhZJyfrYr11SLENYxH5RLru3QGVSwfq2AJaZbAejfBMk5XeRdn2kzQ5tJU0PCH0vr6tXwXdMAc3Y0UjnqrUDK2PiRaySn7DKb69xpqySPhKY63TLxZPgNdj1hawByS32tTwajrJMCDa2ZT3gUhddJkUuzicbMeQm6VYPQpjDk6rFgMhCrKNtH4EiQAx3pA2Njt90r6XuTxUN0rcZeyEx6RNpcbJY',1,1,1,'',0,0,0,0);
UNLOCK TABLES;

CREATE TABLE `zhabs` (
  `zhabID` int(11) NOT NULL AUTO_INCREMENT,
  `zhabURLID` varchar(72) NOT NULL,
  `zhabContent` longtext NOT NULL,
  `zhabBy` int(11) NOT NULL,
  `zhabContains` int(11) NOT NULL,
  `zhabUploaded` date NOT NULL,
  `zhabWhoCanComment` int(11) NOT NULL,
  `zhabWhoCanRepost` int(11) NOT NULL,
  `zhabRepliedTo` varchar(72) NOT NULL,
  `zhabLikes` int(11) NOT NULL,
  `zhabAnsweredTo` varchar(128) NOT NULL,
  UNIQUE KEY `zhabs_unique` (`zhabID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `zhabs` WRITE;
UNLOCK TABLES;