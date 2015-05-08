<?php
	// SQL UPDATES, PER VERSION
	$updates = array();
	
	// buffer existing tables
	$tables = CMS_DB::mysql_query("SHOW TABLES");
	$existing_tables = array();
	while ($table = mysql_fetch_row($tables)) {
		$existing_tables[] = $table[0];
	}
	
	// depending on the current version, the required update queries are accumulated in the $updates array
	// (therefore, break statements are omitted in the following switch/cases!) 

	switch ($CMS_ENV['version_number'])
	{
		case 1:
			$updates[] = "ALTER TABLE `{%PREFIX%}users` ADD `enabled` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `email`";
		case 2:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `xml_access` TINYINT( 1 ) NOT NULL DEFAULT '1'";
		case 3:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` DROP `enable_grouping_view`";
		case 4:
			$updates[] = "DELETE FROM `{%PREFIX%}field_types` WHERE `name` = 'HTML Text (FCK)'";
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` (`id` , `name` , `form_element` , `db_field` , `uses_choices` , `uses_resizes` , `uses_massupload` , `multi_value`  ) VALUES ( 18 , 'HTML Text (FCK)', 'htmltext_fck', 'TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL', '0', '0', '0', '0' )";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `name` = 'HTML Text (Flex)' WHERE `id` = 5";
		case 5:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `introduction` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name`";
		case 6:
			$updates[] = "ALTER TABLE `{%PREFIX%}fields` ADD `render_x` SMALLINT UNSIGNED NOT NULL , ADD `render_y` SMALLINT UNSIGNED NOT NULL , ADD `render_dx` SMALLINT UNSIGNED NOT NULL , ADD `render_dy` SMALLINT UNSIGNED NOT NULL";
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `custom_rendering` TINYINT( 1 ) NOT NULL DEFAULT '0'";
		case 7:
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `db_field` = 'INT(13) NOT NULL' WHERE `id` IN (9,11)";
			$old_date_fields = CMS_DB::mysql_query(str_replace("{%PREFIX%}", $CMS_DB['prefix'], "SELECT `f`.`name`, `f`.`module_id`, `m`.`name` as module_name FROM `{%PREFIX%}fields` f, `{%PREFIX%}modules` m WHERE `f`.`module_id` = `m`.`id` AND `field_type_id` IN (9, 11)"));
			while ($old_date_field = mysql_fetch_assoc($old_date_fields)) {
				$updates[] = "ALTER TABLE `{%PREFIX%}m".$old_date_field['module_id']."_".Tools::alphanumeric($old_date_field['module_name'])."` CHANGE `".Tools::alphanumeric($old_date_field['name'])."` `".Tools::alphanumeric($old_date_field['name'])."` INT( 13 ) NOT NULL";
			}
		case 8:
			$updates[] = "ALTER TABLE `{%PREFIX%}field_types` ADD `description` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `form_element`";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'One line text entry.' WHERE `id` = 1 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A dropdown box with multiple choices of which 1 may be selected (a html SELECT element).' WHERE `id` = 2 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A series of checkboxes of which multiple may be checked. ' WHERE `id` =10 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A text input box for larger amounts of text (the html TEXTAREA element).' WHERE `id` = 4 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A rich (html) text input element (very basic).' WHERE `id` = 5 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'An image upload element.' WHERE `id` = 6 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A multi-image upload element. Allows uploading multiple images at once.' WHERE `id` = 7 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A file upload element.' WHERE `id` = 8 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A timestamp, which is set on entry creation. Can not be edited by users.' WHERE `id` = 9 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A date input (datepicker). ' WHERE `id` = 11 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A simple one-line text input element, which appears as a header to normal users. ' WHERE `id` = 12 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A numeric input field (supports two decimals). ' WHERE `id` = 13 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A large textarea for raw HTML storing. Only required in specific and semi-custom implementations. ' WHERE `id` = 14 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A colorpicker, values are stored as HTML hexcodes (for example: #ff00aa). ' WHERE `id` = 15 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A reference to another entry (usually in another module). Refer to the manual for detailed information. ' WHERE `id` = 16 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'Multiple references to another entry (usually in another module). Refer to the manual for detailed information. ' WHERE `id` = 17 LIMIT 1";
			$updates[] = "UPDATE `{%PREFIX%}field_types` SET `description` = 'A rich (html) text input element, powered by FCKEditor. ' WHERE `id` = 18 LIMIT 1";
		case 9:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `view_own_entries_only` TINYINT( 1 ) NOT NULL DEFAULT '0'";
		case 10:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `searchable` TINYINT( 1 ) NOT NULL DEFAULT '0'";
		case 11:
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` VALUES (19, 'Boolean', 'boolean', 'A simple YES / NO selection.', 'BOOL NOT NULL', 0, 0, 0, 0)";
		case 12:
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` VALUES (20, 'Numeric (integer)', 'numeric', 'A numeric input field (only integers).', 'INT(10) NOT NULL', 0, 0, 0, 0)";
		case 13:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `allow_column_sorting` BOOL NOT NULL DEFAULT '0'";
		case 14:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `simulate_categories_for` MEDIUMINT(8) UNSIGNED NOT NULL";
		case 15:
			$updates[] = "ALTER TABLE `{%PREFIX%}fields` ADD `multilingual` TINYINT( 1 ) NOT NULL DEFAULT '0'";
		case 16:
			$updates[] = "ALTER TABLE `{%PREFIX%}field_options_resizes` ADD `corners` TINYINT( 1 ) NOT NULL DEFAULT '0', ADD `corners_name` TINYTEXT CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL";
		case 17:
			$updates[] = "CREATE TABLE `{%PREFIX%}languages` ( `id` mediumint(8) unsigned NOT NULL, `code` varchar(5) collate latin1_general_ci NOT NULL, `language` tinytext character set utf8 NOT NULL, `local` tinytext character set utf8 NOT NULL, `common` tinyint(1) NOT NULL, `available` tinyint(1) NOT NULL default '0', `default` tinyint(1) NOT NULL default '0', UNIQUE KEY `id` (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
			$updates[] = "CREATE TABLE `{%PREFIX%}user_languages` ( `id` int(10) unsigned NOT NULL auto_increment, `user_id` mediumint(8) unsigned NOT NULL, `language` mediumint(8) unsigned NOT NULL, PRIMARY KEY  (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (1, 'aa', 'Afar', 'Afaraf', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (2, 'ab', 'Abkhazian', 'Ð�Ò§Ñ�ÑƒÐ°', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (3, 'ae', 'Avestan', 'avesta', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (4, 'af', 'Afrikaans', 'Afrikaans', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (5, 'ak', 'Akan', 'Akan', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (6, 'am', 'Amharic', 'áŠ áˆ›áˆ­áŠ›', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (7, 'an', 'Aragonese', 'AragonÃ©s', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (8, 'ar', 'Arabic', 'Ø§Ù„Ø¹Ø±Ø¨ÙŠØ©', 1, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (9, 'as', 'Assamese', 'à¦…à¦¸à¦®à§€à¦¯à¦¼à¦¾', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (10, 'av', 'Avaric', 'Ð°Ð²Ð°Ñ€ Ð¼Ð°Ñ†Ó€; Ð¼Ð°Ð³Ó€Ð°Ñ€ÑƒÐ» Ð¼Ð°Ñ†Ó€', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (11, 'ay', 'Aymara', 'aymar aru', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (12, 'az', 'Azerbaijani', 'azÉ™rbaycan dili', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (13, 'ba', 'Bashkir', 'Ð±Ð°ÑˆÒ¡Ð¾Ñ€Ñ‚ Ñ‚ÐµÐ»Ðµ', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (14, 'be', 'Belarusian', 'Ð‘ÐµÐ»Ð°Ñ€ÑƒÑ�ÐºÐ°Ñ�', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (15, 'bg', 'Bulgarian', 'Ð±ÑŠÐ»Ð³Ð°Ñ€Ñ�ÐºÐ¸ ÐµÐ·Ð¸Ðº', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (16, 'bh', 'Bihari', 'à¤­à¥‹à¤œà¤ªà¥�à¤°à¥€', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (17, 'bi', 'Bislama', 'Bislama', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (18, 'bm', 'Bambara', 'bamanankan', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (19, 'bn', 'Bengali', 'à¦¬à¦¾à¦‚à¦²à¦¾', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (20, 'bo', 'Tibetan', 'à½–à½¼à½‘à¼‹à½¡à½²à½‚', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (21, 'br', 'Breton', 'brezhoneg', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (22, 'bs', 'Bosnian', 'bosanski jezik', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (23, 'ca', 'Catalan', 'CatalÃ ', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (24, 'ce', 'Chechen', 'Ð½Ð¾Ñ…Ñ‡Ð¸Ð¹Ð½ Ð¼Ð¾Ñ‚Ñ‚', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (25, 'ch', 'Chamorro', 'Chamoru', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (26, 'co', 'Corsican', 'corsu; lingua corsa', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (27, 'cr', 'Cree', 'á“€á�¦á�ƒá”­á��á��á�£', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (28, 'cs', 'Czech', 'Ä�esky; Ä�eÅ¡tina', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (29, 'cu', 'Church Slavic', 'Ñ©Ð·Ñ‹ÐºÑŠ Ñ�Ð»Ð¾Ð²Ñ£Ð½ÑŒÑ�ÐºÑŠ', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (30, 'cv', 'Chuvash', 'Ñ‡Ó‘Ð²Ð°Ñˆ Ñ‡Ó—Ð»Ñ…Ð¸', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (31, 'cy', 'Welsh', 'Cymraeg', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (32, 'da', 'Danish', 'dansk', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (33, 'de', 'German', 'Deutsch', 1, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (34, 'dv', 'Divehi', 'Þ‹Þ¨ÞˆÞ¬Þ€Þ¨', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (35, 'dz', 'Dzongkha', 'à½¢à¾«à½¼à½„à¼‹à½�', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (36, 'ee', 'Ewe', 'Æ�Ê‹É›gbÉ›', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (37, 'el', 'Greek', 'Î•Î»Î»Î·Î½Î¹ÎºÎ¬', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (38, 'en', 'English', 'English', 1, 1, 1);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (39, 'eo', 'Esperanto', 'Esperanto', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (40, 'es', 'Spanish', 'EspaÃ±ol; castellano', 1, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (41, 'et', 'Estonian', 'Eesti keel', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (42, 'eu', 'Basque', 'euskara', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (43, 'fa', 'Persian', 'Ù�Ø§Ø±Ø³ÛŒ', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (44, 'ff', 'Fulah', 'Fulfulde', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (45, 'fi', 'Finnish', 'suomen kieli', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (46, 'fj', 'Fijian', 'vosa Vakaviti', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (47, 'fo', 'Faroese', 'FÃ¸royskt', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (48, 'fr', 'French', 'FranÃ§ais; langue franÃ§aise', 1, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (49, 'fy', 'Western Frisian', 'Frysk', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (50, 'ga', 'Irish', 'Gaeilge', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (51, 'gd', 'Scottish Gaelic', 'GÃ idhlig', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (52, 'gl', 'Galician', 'Galego', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (54, 'gu', 'Gujarati', 'àª—à«�àªœàª°àª¾àª¤à«€', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (55, 'gv', 'Manx', 'Gaelg; Gailck', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (56, 'ha', 'Hausa', 'Ù‡ÙŽÙˆÙ�Ø³ÙŽ', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (57, 'he', 'Hebrew', '×¢×‘×¨×™×ª', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (58, 'hi', 'Hindi', 'à¤¹à¤¿à¤¨à¥�à¤¦à¥€; à¤¹à¤¿à¤‚à¤¦à¥€', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (59, 'ho', 'Hiri Motu', 'Hiri Motu', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (60, 'hr', 'Croatian', 'Hrvatski', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (61, 'ht', 'Haitian', 'KreyÃ²l ayisyen', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (62, 'hu', 'Hungarian', 'Magyar', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (63, 'hy', 'Armenian', 'Õ€Õ¡ÕµÕ¥Ö€Õ¥Õ¶', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (64, 'hz', 'Herero', 'Otjiherero', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (65, 'id', 'Indonesian', 'Bahasa Indonesia', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (66, 'ie', 'Interlingue', 'Interlingue', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (67, 'ig', 'Igbo', 'Igbo', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (68, 'ii', 'Sichuan Yi', 'ê†‡ê‰™', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (69, 'ik', 'Inupiaq', 'IÃ±upiaq; IÃ±upiatun', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (70, 'io', 'Ido', 'Ido', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (71, 'is', 'Icelandic', 'Ã�slenska', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (72, 'it', 'Italian', 'Italiano', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (73, 'iu', 'Inuktitut', 'á�ƒá“„á’ƒá‘Žá‘�á‘¦', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (74, 'ja', 'Japanese', 'æ—¥æœ¬èªž (ã�«ã�»ã‚“ã�”ï¼�ã�«ã�£ã�½ã‚“ã�”)', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (75, 'jv', 'Javanese', 'basa Jawa', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (76, 'ka', 'Georgian', 'áƒ¥áƒ�áƒ áƒ—áƒ£áƒšáƒ˜', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (77, 'kg', 'Kongo', 'KiKongo', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (78, 'ki', 'Kikuyu', 'GÄ©kÅ©yÅ©', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (79, 'kj', 'Kwanyama', 'Kuanyama', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (80, 'kk', 'Kazakh', 'ÒšÐ°Ð·Ð°Ò› Ñ‚Ñ–Ð»Ñ–', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (81, 'kl', 'Kalaallisut', 'kalaallisut; kalaallit oqaasii', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (82, 'km', 'Khmer', 'áž—áž¶ážŸáž¶áž�áŸ’áž˜áŸ‚ážš', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (83, 'kn', 'Kannada', 'à²•à²¨à³�à²¨à²¡', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (84, 'ko', 'Korean', 'í•œêµ­ì–´ (éŸ“åœ‹èªž); ì¡°ì„ ë§� (æœ�é®®èªž)', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (85, 'kr', 'Kanuri', 'Kanuri', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (86, 'ks', 'Kashmiri', 'à¤•à¤¶à¥�à¤®à¥€à¤°à¥€; ÙƒØ´Ù…ÙŠØ±ÙŠâ€Ž', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (87, 'ku', 'Kurdish', 'KurdÃ®; ÙƒÙˆØ±Ø¯ÛŒâ€Ž', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (88, 'kv', 'Komi', 'ÐºÐ¾Ð¼Ð¸ ÐºÑ‹Ð²', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (89, 'kw', 'Cornish', 'Kernewek', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (90, 'ky', 'Kirghiz', 'ÐºÑ‹Ñ€Ð³Ñ‹Ð· Ñ‚Ð¸Ð»Ð¸', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (91, 'la', 'Latin', 'latine; lingua latina', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (92, 'lb', 'Luxembourgish', 'LÃ«tzebuergesch', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (93, 'lg', 'Ganda', 'Luganda', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (94, 'li', 'Limburgish', 'Limburgs', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (95, 'ln', 'Lingala', 'LingÃ¡la', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (96, 'lo', 'Lao', 'àºžàº²àºªàº²àº¥àº²àº§', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (97, 'lt', 'Lithuanian', 'lietuviÅ³ kalba', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (98, 'lu', 'Luba-Katanga', '', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (99, 'lv', 'Latvian', 'latvieÅ¡u valoda', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (100, 'mg', 'Malagasy', 'Malagasy fiteny', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (101, 'mh', 'Marshallese', 'Kajin MÌ§ajeÄ¼', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (102, 'mi', 'MÄ�ori', 'te reo MÄ�ori', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (103, 'mk', 'Macedonian', 'Ð¼Ð°ÐºÐµÐ´Ð¾Ð½Ñ�ÐºÐ¸ Ñ˜Ð°Ð·Ð¸Ðº', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (104, 'ml', 'Malayalam', 'à´®à´²à´¯à´¾à´³à´‚', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (105, 'mn', 'Mongolian', 'ÐœÐ¾Ð½Ð³Ð¾Ð»', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (106, 'mo', 'Moldavian', 'Limba moldoveneascÄƒ', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (107, 'mr', 'Marathi', 'à¤®à¤°à¤¾à¤ à¥€', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (108, 'ms', 'Malay', 'bahasa Melayu; Ø¨Ù‡Ø§Ø³ Ù…Ù„Ø§ÙŠÙˆâ€Ž', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (109, 'mt', 'Maltese', 'Malti', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (110, 'my', 'Burmese', 'á€—á€™á€¬á€…á€¬', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (111, 'na', 'Nauru', 'EkakairÅ© Naoero', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (112, 'nb', 'Norwegian BokmÃ¥l', 'Norsk bokmÃ¥l', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (113, 'nd', 'North Ndebele', 'isiNdebele', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (114, 'ne', 'Nepali', 'à¤¨à¥‡à¤ªà¤¾à¤²à¥€', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (115, 'ng', 'Ndonga', 'Owambo', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (116, 'nl', 'Dutch', 'Nederlands', 1, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (117, 'nn', 'Norwegian Nynorsk', 'Norsk nynorsk', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (118, 'no', 'Norwegian', 'Norsk', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (119, 'nr', 'South Ndebele', 'isiNdebele', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (120, 'nv', 'Navajo', 'DinÃ© bizaad; DinÃ©kÊ¼ehÇ°Ã­', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (121, 'ny', 'Chichewa', 'chiCheÅµa; chinyanja', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (122, 'oc', 'Occitan', 'Occitan', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (123, 'oj', 'Ojibwa', 'á�Šá“‚á”‘á“ˆá�¯á’§á�Žá“�', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (124, 'om', 'Oromo', 'Afaan Oromoo', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (125, 'or', 'Oriya', 'à¬“à¬¡à¬¼à¬¿à¬†', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (126, 'os', 'Ossetian', 'Ð˜Ñ€Ð¾Ð½ Ã¦Ð²Ð·Ð°Ð³', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (127, 'pa', 'Panjabi', 'à¨ªà©°à¨œà¨¾à¨¬à©€; Ù¾Ù†Ø¬Ø§Ø¨ÛŒâ€Ž', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (128, 'pi', 'PÄ�li', 'à¤ªà¤¾à¤´à¤¿', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (129, 'pl', 'Polish', 'polski', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (130, 'ps', 'Pashto', 'Ù¾ÚšØªÙˆ', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (131, 'pt', 'Portuguese', 'PortuguÃªs', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (132, 'qu', 'Quechua', 'Runa Simi; Kichwa', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (133, 'rm', 'Raeto-Romance', 'rumantsch grischun', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (134, 'rn', 'Kirundi', 'kiRundi', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (135, 'ro', 'Romanian', 'romÃ¢nÄƒ', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (136, 'ru', 'Russian', 'Ñ€ÑƒÑ�Ñ�ÐºÐ¸Ð¹ Ñ�Ð·Ñ‹Ðº', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (137, 'rw', 'Kinyarwanda', 'Ikinyarwanda', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (138, 'sa', 'Sanskrit', 'à¤¸à¤‚à¤¸à¥�à¤•à¥ƒà¤¤à¤®à¥�', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (139, 'sc', 'Sardinian', 'sardu', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (140, 'sd', 'Sindhi', 'à¤¸à¤¿à¤¨à¥�à¤§à¥€; Ø³Ù†ÚŒÙŠØŒ Ø³Ù†Ø¯Ú¾ÛŒâ€Ž', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (141, 'se', 'Northern Sami', 'DavvisÃ¡megiella', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (142, 'sg', 'Sango', 'yÃ¢ngÃ¢ tÃ® sÃ¤ngÃ¶', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (143, 'sh', 'Serbo-Croatian', 'Srpskohrvatski; Ð¡Ñ€Ð¿Ñ�ÐºÐ¾Ñ…Ñ€Ð²Ð°Ñ‚Ñ�ÐºÐ¸', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (144, 'si', 'Sinhala', 'à·ƒà·’à¶‚à·„à¶½', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (145, 'sk', 'Slovak', 'slovenÄ�ina', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (146, 'sl', 'Slovenian', 'slovenÅ¡Ä�ina', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (148, 'sn', 'Shona', 'chiShona', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (149, 'so', 'Somali', 'Soomaaliga; af Soomaali', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (150, 'sq', 'Albanian', 'Shqip', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (151, 'sr', 'Serbian', 'Ñ�Ñ€Ð¿Ñ�ÐºÐ¸ Ñ˜ÐµÐ·Ð¸Ðº', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (152, 'ss', 'Swati', 'SiSwati', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (153, 'st', 'Southern Sotho', 'Sesotho', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (154, 'su', 'Sundanese', 'Basa Sunda', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (155, 'sv', 'Swedish', 'svenska', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (156, 'sw', 'Swahili', 'Kiswahili', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (157, 'ta', 'Tamil', 'à®¤à®®à®¿à®´à¯�', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (158, 'te', 'Telugu', 'à°¤à±†à°²à±�à°—à±�', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (159, 'tg', 'Tajik', 'Ñ‚Ð¾Ò·Ð¸ÐºÓ£; toÄŸikÄ«; ØªØ§Ø¬ÛŒÚ©ÛŒâ€Ž', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (160, 'th', 'Thai', 'à¹„à¸—à¸¢', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (161, 'ti', 'Tigrinya', 'á‰µáŒ�áˆ­áŠ›', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (162, 'tk', 'Turkmen', 'TÃ¼rkmen; Ð¢Ò¯Ñ€ÐºÐ¼ÐµÐ½', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (163, 'tl', 'Tagalog', 'Tagalog', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (164, 'tn', 'Tswana', 'Setswana', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (165, 'to', 'Tonga', 'faka Tonga', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (166, 'tr', 'Turkish', 'TÃ¼rkÃ§e', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (167, 'ts', 'Tsonga', 'Xitsonga', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (168, 'tt', 'Tatar', 'Ñ‚Ð°Ñ‚Ð°Ñ€Ñ‡Ð°; tatarÃ§a; ØªØ§ØªØ§Ø±Ú†Ø§â€Ž', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (169, 'tw', 'Twi', 'Twi', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (170, 'ty', 'Tahitian', 'Reo MÄ�`ohi', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (171, 'ug', 'Uighur', 'UyÆ£urqÉ™; Ø¦Û‡ÙŠØºÛ‡Ø±Ú†â€Ž', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (172, 'uk', 'Ukrainian', 'Ð£ÐºÑ€Ð°Ñ—Ð½Ñ�ÑŒÐºÐ°', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (173, 'ur', 'Urdu', 'Ø§Ø±Ø¯Ùˆ', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (175, 've', 'Venda', 'Tshivená¸“a', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (176, 'vi', 'Vietnamese', 'Tiáº¿ng Viá»‡t', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (177, 'vo', 'VolapÃ¼k', 'VolapÃ¼k', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (178, 'wa', 'Walloon', 'Walon', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (179, 'wo', 'Wolof', 'Wollof', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (180, 'xh', 'Xhosa', 'isiXhosa', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (181, 'yi', 'Yiddish', '×™×™Ö´×“×™×©', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (182, 'yo', 'Yoruba', 'YorÃ¹bÃ¡', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (183, 'za', 'Zhuang', 'SaÉ¯ cueÅ‹Æ…; Saw cuengh', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (184, 'zh', 'Chinese', 'ä¸­æ–‡, æ±‰è¯­, æ¼¢èªž', 1, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (185, 'zu', 'Zulu', 'isiZulu', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (147, 'sm', 'Samoan', 'gagana fa''a Samoa', 0, 0, 0);";
			$updates[] = "INSERT INTO `{%PREFIX%}languages` VALUES (53, 'gn', 'GuaranÃ­', 'AvaÃ±e''áº½', 0, 0, 0);";
		case 18:
			$updates[] = "ALTER TABLE `{%PREFIX%}field_options_resizes` ADD `no_cropping` TINYINT( 1 ) NOT NULL DEFAULT '0', ADD `background_color` VARCHAR( 6 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL";
		case 19:
			$updates[] = "ALTER TABLE `{%PREFIX%}fields` ADD `tab_id` SMALLINT( 5 ) NOT NULL, ADD `custom_html` TEXT NOT NULL ;";
			$updates[] = "CREATE TABLE `{%PREFIX%}tabs` (`id` smallint(5) NOT NULL auto_increment, `name` varchar(50) collate utf8_unicode_ci NOT NULL, `module_id` mediumint(8) NOT NULL, PRIMARY KEY  (`id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` (`id`, `name`, `form_element`, `description`, `db_field`, `uses_choices`, `uses_resizes`, `uses_massupload`, `multi_value`) VALUES (21, 'Custom Hidden Input', 'custom_text', 'Hidden Input with custom html', 'TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL', 0, 0, 0, 0), (22, 'Custom Input', 'custom_input', 'Input with custom html', 'TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL', 0, 0, 0, 0);";
		case 20:
			$updates[] = "RENAME TABLE `{%PREFIX%}websites` TO `{%PREFIX%}sections` ;";
			$updates[] = "ALTER TABLE `{%PREFIX%}sections` ADD `group_id` MEDIUMINT UNSIGNED NOT NULL AFTER `id` , ADD INDEX ( group_id ) ;";
			$updates[] = "CREATE TABLE `{%PREFIX%}groups` (`id` mediumint(8) unsigned NOT NULL auto_increment,`name` tinytext collate utf8_unicode_ci NOT NULL,`position` smallint(5) unsigned NOT NULL,PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` CHANGE `website_id` `section_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL ;";
			
			if (!in_array(str_replace("{%PREFIX%}", $CMS_DB['prefix'], '{%PREFIX%}groups'), $existing_tables)) {
				$updates[] = "INSERT INTO `{%PREFIX%}groups` ( `id` ,`name` ,`position` ) VALUES ( '1', 'Main', '1' );";
				$updates[] = "UPDATE `{%PREFIX%}sections` SET `group_id` = 1";
			}
			
		case 21:
			$updates[] = "ALTER TABLE `{%PREFIX%}users` ADD `ref_filter_module_id` INT UNSIGNED NULL DEFAULT NULL , ADD `ref_filter_entry_id` INT UNSIGNED NULL DEFAULT NULL ";
		
		case 22:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `hide_from_menu` TINYINT( 1 ) NOT NULL DEFAULT '0'";
		
		case 23:
			$updates[] = "ALTER TABLE `{%PREFIX%}tabs` ADD `position` SMALLINT( 5 ) NOT NULL";
		
		case 24:
			$updates[] = " INSERT INTO `{%PREFIX%}field_types` (`id` ,`name` ,`form_element` ,`description` ,`db_field` ,`uses_choices` ,`uses_resizes` ,`uses_massupload` ,`multi_value`) VALUES ('24', 'Float (10,6) [Lat/Lng]', 'numeric', 'A numeric input field with six decimals. Specifically included for Lat/Lng points.', 'FLOAT(10,6) NOT NULL', '0', '0', '0', '0')";

		case 25:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `csv_export` TINYINT( 1 ) NOT NULL DEFAULT '0'";				
		
		case 26:
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` (`id`, `name`, `form_element`, `description`, `db_field`, `uses_choices`, `uses_resizes`, `uses_massupload`, `multi_value`) VALUES ('25', 'Reference (negative)', 'reference', 'Reference to large or negative index.', 'BIGINT(20) NOT NULL', '0', '0', '0', '0');";			

		case 27:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `related_items_filter` TINYINT( 1 ) NOT NULL DEFAULT '0'";
		
		case 28:
			$updates[] = "ALTER TABLE `{%PREFIX%}groups` ADD `menu_id` MEDIUMINT UNSIGNED NOT NULL AFTER `id`;";
			$updates[] = "CREATE TABLE `{%PREFIX%}menu` (`id` mediumint(8) unsigned NOT NULL auto_increment,`name` tinytext collate utf8_unicode_ci NOT NULL,`icon` tinytext collate utf8_unicode_ci NOT NULL,`position` smallint(5) unsigned NOT NULL,PRIMARY KEY  (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
			
			if (!in_array(str_replace("{%PREFIX%}", $CMS_DB['prefix'], '{%PREFIX%}menu'), $existing_tables)) {
				$updates[] = "INSERT INTO `{%PREFIX%}menu` ( `id` ,`name` ,`icon`,`position` ) VALUES ( '1', 'Home', 'home.gif' , '1' );";
				$updates[] = "UPDATE `{%PREFIX%}groups` SET `menu_id` = 1";
			}
		
		case 29:
			$updates[] = "ALTER TABLE `{%PREFIX%}m_references` ADD `position` TINYINT UNSIGNED NOT NULL DEFAULT '0'";
			
		case 30:
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` (`id`, `name`, `form_element`, `description`, `db_field`, `uses_choices`, `uses_resizes`, `uses_massupload`, `multi_value`) VALUES ('26', 'Reference (1:N Autosort)', 'reference_multi', 'Multiple references to another entry (usually in another module). Refer to the manual for detailed information. Will sort based on referenced module.', '#REF', '0', '0', '0', '1');";			

		case 31:
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` VALUES (27, 'Reference (1:N Checkboxes)', 'reference_multi', 'Multi reference rendered with checkboxes', '#REF', 0, 0, 0, 1)";
			
		case 32:
			$updates[] = "ALTER TABLE `{%PREFIX%}fields` ADD `default` VARCHAR( 255 ) NOT NULL";
			
		case 33:
			$updates[] = "ALTER TABLE `{%PREFIX%}fields` ADD `display_name` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `name`";
			
		case 34:
			$updates[] = "ALTER TABLE  `{%PREFIX%}field_options_resizes` ADD  `trim` BOOL NOT NULL";
		
		case 35:
			$updates[] = "ALTER TABLE `{%PREFIX%}m_images` ADD `language_id` INT UNSIGNED NOT NULL AFTER `field_id`";
		case 36:
			$updates[] = "ALTER TABLE `{%PREFIX%}m_files` ADD `language_id` INT UNSIGNED NOT NULL AFTER `field_id`";
		case 37:
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` VALUES (28, 'Cross Reference', 'reference_multi', 'Automatically creates a reference the other way. So if you create a reference from A to B, the B to A reference will also be made. Only for self references.', '#REF', 0, 0, 0, 1)";
		case 38:
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` (`id`,`name`,`form_element`,`description`,`db_field`,`uses_choices`,`uses_resizes`,`uses_massupload`,`multi_value`) VALUES (29,'Date','date','A date field','INT(13)','0','0','0','0')";
			$updates[] = "ALTER TABLE `{%PREFIX%}fields` ADD `options` TEXT NOT NULL";
			//allow null-values as default
			$updates[] = "ALTER TABLE `{%PREFIX%}fields` MODIFY `default` VARCHAR(255) NULL DEFAULT NULL";
			//update old data-fields
			$options_9 = array(
				'include_time' => false,
				'editable'		=> false,
				'auto_update'	=> 'create',
				'default'		=> 'now'
			);
			$options_11 = array(
				'include_time' => false,
				'editable'		=> true,
				'auto_update'	=> false,
				'default'		=> 'now'
			);
			$updates[] = "UPDATE `{%PREFIX%}fields` SET `field_type_id` = 29, `options` = '".pxl_db_safe(json_encode($options_9))."' WHERE `field_type_id` = 9";
			$updates[] = "UPDATE `{%PREFIX%}fields` SET `field_type_id` = 29, `options` = '".pxl_db_safe(json_encode($options_11))."' WHERE `field_type_id` = 11";
			//remove old data-types
			$updates[] = "DELETE FROM `{%PREFIX%}field_types` WHERE `id` IN (9,11)";
		case 39:
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` (`id`,`name`,`form_element`,`description`,`db_field`,`uses_choices`,`uses_resizes`,`uses_massupload`,`multi_value`) VALUES (30, 'Slider', 'range', 'Select a value between a min and max value', 'INT NOT NULL DEFAULT 0', '0', '0', '0', '0')";
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` (`id`,`name`,`form_element`,`description`,`db_field`,`uses_choices`,`uses_resizes`,`uses_massupload`,`multi_value`) VALUES (31, 'Range', 'range', 'Select a range between a min and max value', 'TEXT NULL DEFAULT NULL', '0', '0', '0', '0');";
		case 40:
			$updates[] = "ALTER TABLE `{%PREFIX%}fields` ADD `indexed` TINYINT(1) NOT NULL DEFAULT '0' AFTER `field_type_id`";
		case 41:
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` (`id`,`name`,`form_element`,`description`,`db_field`,`uses_choices`,`uses_resizes`,`uses_massupload`,`multi_value`) VALUES (32, 'Location', 'location', 'Laat de gebruiker een locatie kiezen via google maps', 'TEXT NULL DEFAULT NULL', '0', '0', '0', '0');";
		case 42:
			$updates[] = "ALTER TABLE `{%PREFIX%}field_options_choices` ADD `position` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'";
		case 43:
			$updates[] = "ALTER TABLE `{%PREFIX%}modules` ADD `search_referenced_identifiers` TINYINT( 1 ) NOT NULL DEFAULT '0'";
		case 44:
			$updates[] = "INSERT INTO `{%PREFIX%}field_types` (`description`, `id` , `name` , `form_element` , `db_field` , `uses_choices` , `uses_resizes` , `uses_massupload` , `multi_value`  ) VALUES ('A rich (html) text input element, powered by Aloha Editor.', 33 , 'HTML Text (Aloha)', 'htmltext_aloha', 'TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL', '0', '0', '0', '0' )";
			
		case 45:
			// NB to developers: please always write your database update/alter such that re-running them again will not create any issues!!!
			// NB to developers: database changes? -> do not forget to change the default layout in "new.sql"
			// NB to developers: don't forget to update version-information in 'includes/read_config.php'
	}
	
	
	// execute updates
	if (count($updates)) {
		foreach ($updates as $update) {
			$update = str_replace("{%PREFIX%}", $CMS_DB['prefix'], $update);
			$s = CMS_DB::mysql_query($update);
			echo $update;
			if ($s) {
				echo " <span style='color: green;'>(success)</span>";
			} else {
				echo " <span style='color: red;'>(error / unnecessary)</span>";
			}
			echo "<br/>";
		}
	} else {
		echo "<i>Database structure already up-2-date!</i><br/>";
	}
?>