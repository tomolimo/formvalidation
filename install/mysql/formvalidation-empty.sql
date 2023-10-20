CREATE TABLE `glpi_plugin_formvalidation_configs` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`css_mandatory` VARCHAR(255) NOT NULL,
   `css_error` VARCHAR(255) NOT NULL,
   `db_version` VARCHAR(255) NOT NULL DEFAULT '2',
   `js_path` VARCHAR(255),
   PRIMARY KEY (`id`)
)
ENGINE=InnoDB;

CREATE TABLE `glpi_plugin_formvalidation_itemtypes` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`URL_path_part` VARCHAR(255) NOT NULL,
   `guid`VARCHAR(32) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `name` (`name`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `glpi_plugin_formvalidation_pages` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(200) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`entities_id` INT(11) NOT NULL DEFAULT '0',
	`plugin_formvalidation_itemtypes_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`is_recursive` TINYINT(1) NOT NULL DEFAULT '0',
	`is_active` TINYINT(1) NOT NULL DEFAULT '1',
	`comment` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`date_mod` TIMESTAMP NULL DEFAULT NULL,
	`guid` VARCHAR(32) NOT NULL COLLATE 'utf8_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `entities_id` (`entities_id`) USING BTREE,
	UNIQUE INDEX `plugin_formvalidation_itemtypes_id` (`plugin_formvalidation_itemtypes_id`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=14;


CREATE TABLE `glpi_plugin_formvalidation_forms` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(200) NULL DEFAULT NULL,
	`plugin_formvalidation_pages_id` INT UNSIGNED NOT NULL,
	`css_selector` VARCHAR(255) NOT NULL,
   `is_createitem` TINYINT(1) NOT NULL DEFAULT '0',
	`is_active` TINYINT(1) NOT NULL DEFAULT '1',
	`use_for_massiveaction` TINYINT(1) NOT NULL DEFAULT '0',
   `formula` TEXT NULL,
	`comment` TEXT NULL,
	`date_mod` TIMESTAMP NULL DEFAULT NULL,
   `guid`VARCHAR(32) NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `plugin_formvalidation_pages_id` (`plugin_formvalidation_pages_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE `glpi_plugin_formvalidation_fields` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(200) NULL DEFAULT NULL,
	`plugin_formvalidation_forms_id` INT UNSIGNED NOT NULL,
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
   `guid` VARCHAR(32) NOT NULL,
	PRIMARY KEY (`id`),
   UNIQUE INDEX `forms_id_css_selector_value` (`plugin_formvalidation_forms_id`, `css_selector_value`),
	INDEX `plugin_formvalidation_forms_id` (`plugin_formvalidation_forms_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;