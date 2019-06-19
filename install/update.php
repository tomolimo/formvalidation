<?php

function formvalidation_update() {
   global $DB;
   $dbu = new DbUtils();
   $lastIdItemTypes;
   $lastIdPage;

   /*** UPDATE ***/
   if ($DB->tableExists("glpi_plugin_formvalidation_pages")) {
      if (!$DB->fieldExists('glpi_plugin_formvalidation_pages', 'itemtypes_id')) {
         $query = "ALTER TABLE `glpi_plugin_formvalidation_pages`
	                  ADD COLUMN `itemtypes_id` INT(11) NOT NULL DEFAULT '0' AFTER `itemtype`,
                     ADD INDEX `itemtypes_id` (`itemtypes_id`);
                  ";
         $DB->query($query) or die("Error inserting itemtypes_id field into glpi_plugin_formvalidation_pages " . $DB->error());
      }

      // check if migration is neccessary
      $pages = $dbu->getAllDataFromTable('glpi_plugin_formvalidation_pages', ['itemtypes_id' => '0']);
      if (count($pages)) {
         // migration of itemtype into itemtypes_id
         $query = "UPDATE glpi_plugin_formvalidation_pages AS gpfp, glpi_plugin_formvalidation_itemtypes AS gpfi
                   SET gpfp.itemtypes_id = gpfi.id
                   WHERE gpfi.itemtype = gpfp.itemtype;";
         $DB->query($query) or die("Error migrating itemtype into itemtypes_id field in glpi_plugin_formvalidation_pages " . $DB->error());

         // check if all pages have been migrated
         $pages = $dbu->getAllDataFromTable('glpi_plugin_formvalidation_pages', ['itemtypes_id' => '0']);
         if (count($pages)) {
            die("Error some itemtype can't be migrated into itemtypes_id field from glpi_plugin_formvalidation_pages, </br>
                 please check the list of itemtype in glpi_plugin_formvalidation_pages and in glpi_plugin_formvalidation_itemtypes,</br>
                 fix the issue and restart install of the plugin.");
         }
      }

      if ($DB->fieldExists('glpi_plugin_formvalidation_pages', 'itemtype') && !count($pages)) {
         // delete itemtype field after migration is done
         $query = "ALTER TABLE `glpi_plugin_formvalidation_pages`
                   DROP COLUMN `itemtype`,
	                DROP INDEX `itemtype`;";
         $DB->query($query) or die("Error deleting itemtypes field and index from glpi_plugin_formvalidation_pages " . $DB->error());

         // delete the itemtype field from glpi_displaypreferences
         $query = "UPDATE `glpi_displaypreferences`
                   SET num = 803
                   WHERE itemtype = 'PluginFormvalidationPage' AND num = 3;";
         $DB->query($query) or die("Error updating num in glpi_displaypreferences " . $DB->error());
      }
   }
   if ($DB->tableExists("glpi_plugin_formvalidation_forms")) {
      if (!$DB->fieldExists('glpi_plugin_formvalidation_forms', 'use_for_massiveaction')) {
         $query = "ALTER TABLE `glpi_plugin_formvalidation_forms`
	                  ADD COLUMN `use_for_massiveaction` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_active`;
                  ";
         $DB->query($query) or die("Error inserting use_for_massiveaction field into glpi_plugin_formvalidation_forms " . $DB->error());
      }
   }

   /**** UPDATE NEW DATA****/
   if ($DB->tableExists("glpi_plugin_formvalidation_itemtypes")) {
      $query = "SELECT * FROM glpi_plugin_formvalidation_itemtypes WHERE name ='PluginFormvalidationItemtype'";
      $result = $DB->query($query);

      if ($DB->numrows($result) > 0) {
         return true;
      }
      $query = "INSERT INTO `glpi_plugin_formvalidation_itemtypes` (`name`, `itemtype`, `URL_path_part`)
                  VALUE ('PluginFormvalidationItemtype','pluginFormvalidationItemtype', '/plugins/formvalidation/front/itemtype.form.php' );";
      $DB->query($query) or die("Error inserting default item types into glpi_plugin_formvalidation_itemtypes " . $DB->error());
      $lastIdItemTypes = $DB->insert_id();
   }

   if ($DB->tableExists("glpi_plugin_formvalidation_pages")) {
      $query = "INSERT INTO `glpi_plugin_formvalidation_pages` (`name`, `entities_id`, `itemtypes_id`, `is_recursive`, `is_active`, `comment`, `date_mod`)
                  VALUE ('Form Validation Itemtype',0, $lastIdItemTypes, 1, 1, NULL, NULL);";
      $DB->query($query) or die("Error inserting default item types into glpi_plugin_formvalidation_itemtypes " . $DB->error());
      $lastIdPage = $DB->insert_id();
   }

   if ($DB->tableExists("glpi_plugin_formvalidation_forms")) {
      $values = [
         "'form(/plugins/formvalidation/front/itemtype.form.php)',$lastIdPage, 'form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]', 1, 1, 0, NULL, NULL, NULL",
         "'form(/plugins/formvalidation/front/itemtype.form.php)',$lastIdPage, 'form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]', 0, 1, 0, NULL, NULL, NULL"
         ];

      foreach ($values as $value) {
         $query = "INSERT INTO `glpi_plugin_formvalidation_forms` (`name`, `pages_id`, `css_selector`, `is_createitem`, `is_active`, `use_for_massiveaction`, `formula`, `comment`, `date_mod`)
                  VALUE ($value);";
         $DB->query($query) or die("Error inserting default item forms into glpi_plugin_formvalidation_forms " . $DB->error());
         $lastIdForms[] = $DB->insert_id();
      }
   }

   if ($DB->tableExists("glpi_plugin_formvalidation_fields")) {
      $values = [
         "'Name',". $lastIdForms[0].", 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL",
         "'Item type (example: Project)', ". $lastIdForms[0].", 'div>table>tbody>tr:eq(2)>td:eq(1) input[name=\\\"itemtype\\\"]', NULL, 'div>table>tbody>tr:eq(2)>td:eq(1)','div>table>tbody>tr:eq(2)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL",
         "'URL path part (example: /front/project.form.php)', ". $lastIdForms[0].", 'div>table>tbody>tr:eq(3)>td:eq(1) input[name=\\\"URL_path_part\\\"]', NULL, 'div>table>tbody>tr:eq(3)>td:eq(1)', 'div>table>tbody>tr:eq(3)>td:eq(0)', 1, 1, NULL, '(#.length > 0) && (function(){var res;$.ajax({async: false, url: \"/plugins/formvalidation/ajax/existingFile.php\",type: \"GET\",data: { filename: # },success: function (response) {if(response==1){res = 1;}else{res=0;}}});return res;})();', NULL, NULL",
         "'Name', ". $lastIdForms[1].", 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]', NULL, 'div>table>tbody>tr:eq(1)>td:eq(1)', 'div>table>tbody>tr:eq(1)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL",
         "'Item type (example: Project)', ". $lastIdForms[1].", 'div>table>tbody>tr:eq(2)>td:eq(1) input[name=\\\"itemtype\\\"]', NULL, 'div>table>tbody>tr:eq(2)>td:eq(1)','div>table>tbody>tr:eq(2)>td:eq(0)', 1, 1, NULL, NULL, NULL, NULL",
         "'URL path part (example: /front/project.form.php)', ". $lastIdForms[1].", 'div>table>tbody>tr:eq(3)>td:eq(1) input[name=\\\"URL_path_part\\\"]', NULL, 'div>table>tbody>tr:eq(3)>td:eq(1)', 'div>table>tbody>tr:eq(3)>td:eq(0)', 1, 1, NULL, '(#.length > 0) && (function(){var res;$.ajax({async: false, url: \"/plugins/formvalidation/ajax/existingFile.php\",type: \"GET\",data: { filename: # },success: function (response) {if(response==1){res = 1;}else{res=0;}}});return res;})();', NULL, NULL"
      ];
      foreach ($values as $value) {
         $query = "INSERT INTO `glpi_plugin_formvalidation_fields` (`name`, `forms_id`, `css_selector_value`, `css_selector_altvalue`, `css_selector_errorsign`, `css_selector_mandatorysign`, `is_active`, `show_mandatory`, `show_mandatory_if`, `formula`, `comment`, `date_mod`)
                   VALUE ($value)";
         $DB->query($query) or die("Error inserting default item types into glpi_plugin_formvalidation_itemtypes " . $DB->error());
      }
   }
}