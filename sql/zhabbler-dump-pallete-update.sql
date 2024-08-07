ALTER TABLE `personalization`
	DROP `personalizationAccent`,
	DROP `personalizationBackgroundColor`,
	DROP `personalizationURL`,
	ADD `personalizationPallete` VARCHAR(16) NOT NULL AFTER `personalizationNavbarStyle`;