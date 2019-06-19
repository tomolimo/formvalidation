CREATE TABLE `glpi_plugin_formvalidation_configs` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`css_mandatory` VARCHAR(200) NOT NULL DEFAULT '{\"background-color\":\"lightgrey\", \"font-weight\":\"bold\"}',
   `css_error` VARCHAR(200) NOT NULL DEFAULT '{\"background-color\": \"red\"}',                     
   PRIMARY KEY (`id`)
)
ENGINE=InnoDB;

CREATE TABLE `glpi_plugin_formvalidation_itemtypes` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`itemtype` VARCHAR(100) NOT NULL,
	`URL_path_part` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `itemtype` (`itemtype`),
	UNIQUE INDEX `name` (`name`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `glpi_plugin_formvalidation_pages` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(200) NULL DEFAULT NULL,
	`entities_id` INT(11) NOT NULL DEFAULT '0',
	`itemtypes_id` INT(11) NOT NULL DEFAULT '0',
	`is_recursive` TINYINT(1) NOT NULL DEFAULT '0',
	`is_active` TINYINT(1) NOT NULL DEFAULT '1',
	`comment` TEXT NULL,
	`date_mod` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `itemtypes_id` (`itemtypes_id`),
   INDEX `entities_id` (`entities_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `glpi_plugin_formvalidation_forms` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(200) NULL DEFAULT NULL,
	`pages_id` INT(11) NOT NULL,
	`css_selector` VARCHAR(255) NOT NULL,
   `is_createitem` TINYINT(1) NOT NULL DEFAULT '0',
	`is_active` TINYINT(1) NOT NULL DEFAULT '1',
	`use_for_massiveaction` TINYINT(1) NOT NULL DEFAULT '0',
   `formula` TEXT NULL,
	`comment` TEXT NULL,
	`date_mod` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `pages_id` (`pages_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `glpi_plugin_formvalidation_fields` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(200) NULL DEFAULT NULL,
	`forms_id` INT(11) NOT NULL,
	`css_selector_value` VARCHAR(255) NULL DEFAULT NULL,
	`css_selector_altvalue` VARCHAR(255) NULL DEFAULT NULL,
	`css_selector_errorsign` VARCHAR(255) NULL DEFAULT NULL,
	`css_selector_mandatorysign` VARCHAR(255) NULL DEFAULT NULL,
	`is_active` TINYINT(1) NOT NULL DEFAULT '1',
	`show_mandatory` TINYINT(1) NOT NULL DEFAULT '1',
	`show_mandatory_if` TEXT NULL,
   `formula` TEXT NULL,
	`comment` TEXT NULL,
	`date_mod` TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
   UNIQUE INDEX `forms_id_css_selector_value` (`forms_id`, `css_selector_value`),
	INDEX `forms_id` (`forms_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;