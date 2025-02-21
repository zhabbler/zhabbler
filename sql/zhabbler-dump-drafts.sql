CREATE TABLE `drafts` (
  `draftID` int(11) NOT NULL,
  `draftBy` int(11) NOT NULL,
  `draft` longtext NOT NULL,
  `draftAnsweredTo` varchar(128) NOT NULL,
  `draftRepliedTo` varchar(72) NOT NULL,
  `draftTags` longtext NOT NULL,
  `draftAdded` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `drafts`
  ADD PRIMARY KEY (`draftID`);
ALTER TABLE `drafts`
  MODIFY `draftID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;