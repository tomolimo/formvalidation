<?php
/*
 * -------------------------------------------------------------------------
Form Validation plugin
Copyright (C) 2016-2023 by Raynet SAS a company of A.Raymond Network.

http://www.araymond.com
-------------------------------------------------------------------------

LICENSE

This file is part of Form Validation plugin for GLPI.

This file is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GLPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
*/

function formvalidation_update() {
   global $DB, $CFG_GLPI;
   $dbu = new DbUtils();
   $config = PluginFormvalidationConfig::getInstance();
   /*** Update 0.5.3 ****/
   $migration = new Migration(100);
   if (!$DB->fieldExists('glpi_plugin_formvalidation_configs', 'db_version')) {
      $migration->addField(
            'glpi_plugin_formvalidation_configs',
            'db_version',
            'string',
            ['value'=>'1.0.0']
         );
      $migration->executeMigration();
   }

   if (!$DB->fieldExists('glpi_plugin_formvalidation_configs', 'js_path')) {
      $migration->addField(
            'glpi_plugin_formvalidation_configs',
            'js_path',
            'string'
         );
      $migration->executeMigration();
   }
   /***********************/

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
      if ($DB->fieldExists('glpi_plugin_formvalidation_itemtypes', 'itemtype')) {
         $query = "ALTER TABLE `glpi_plugin_formvalidation_itemtypes`
                   DROP COLUMN `itemtype`,
	                DROP INDEX `itemtype`;";
         $DB->query($query) or die("Error deleting itemtypes field and index from glpi_plugin_formvalidation_pages " . $DB->error());
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

   if ($DB->tableExists('glpi_plugin_formvalidation_itemtypes')) {
      if (!$DB->fieldExists('glpi_plugin_formvalidation_itemtypes', 'guid')) {
         $query = "ALTER TABLE `glpi_plugin_formvalidation_itemtypes`
	                ADD COLUMN `guid` VARCHAR(32) NOT NULL;
                  ";
         $DB->query($query) or die("Error inserting guid field into glpi_plugin_formvalidation_itemtypes " . $DB->error());
      }
   }
   if ($DB->tableExists('glpi_plugin_formvalidation_pages')) {
      if (!$DB->fieldExists('glpi_plugin_formvalidation_pages', 'guid')) {
         $query = "ALTER TABLE `glpi_plugin_formvalidation_pages`
	                ADD COLUMN `guid` VARCHAR(32) NOT NULL;
                  ";
         $DB->query($query) or die("Error inserting guid field into glpi_plugin_formvalidation_pages " . $DB->error());
      }
   }
   if ($DB->tableExists('glpi_plugin_formvalidation_forms')) {
      if (!$DB->fieldExists('glpi_plugin_formvalidation_forms', 'guid')) {
         $query = "ALTER TABLE `glpi_plugin_formvalidation_forms`
	                ADD COLUMN `guid` VARCHAR(32) NOT NULL;
                  ";
         $DB->query($query) or die("Error inserting guid field into glpi_plugin_formvalidation_forms " . $DB->error());
      }
   }
   if ($DB->tableExists('glpi_plugin_formvalidation_fields')) {
      if (!$DB->fieldExists('glpi_plugin_formvalidation_fields', 'guid')) {
         $query = "ALTER TABLE `glpi_plugin_formvalidation_fields`
	                ADD COLUMN `guid` VARCHAR(32) NOT NULL;
                  ";
         $DB->query($query) or die("Error inserting guid field into glpi_plugin_formvalidation_fields " . $DB->error());
      }
   }

   /**** UPDATE NEW DATA 0.5.1****/
   $lastIdItemTypes = 0;
   $lastIdPage = 0;
   $lastIdForms = [];
   $obj = ['itemtype','page','form','field'];

   if ($DB->tableExists("glpi_plugin_formvalidation_itemtypes")) {
      $result = $DB->request('glpi_plugin_formvalidation_itemtypes', ['name' => 'PluginFormvalidationItemtype']);
      if (!count($result)) {
         $DB->insertOrDie(
            'glpi_plugin_formvalidation_itemtypes',
            [
               'name'            => 'PluginFormvalidationItemtype',
               'URL_path_part'   => '/plugins/formvalidation/front/itemtype.form.php',
               'guid'            => ''
            ],
            'Error inserting default item types into glpi_plugin_formvalidation_itemtypes'
          );
         $lastIdItemTypes = $DB->insert_id();
         //}
         //}

         if ($DB->tableExists("glpi_plugin_formvalidation_pages") && $lastIdItemTypes != 0) {
            $DB->insertOrDie(
               'glpi_plugin_formvalidation_pages',
               [
                  'name'         => 'Form Validation Itemtype',
                  'entities_id'  => '0',
                  'itemtypes_id' => $lastIdItemTypes,
                  'is_recursive' => '1',
                  'is_active'    => '1',
                  'comment'      => null,
                  'date_mod'     => null,
                  'guid'         => ''
               ],
               'Error inserting default item types into glpi_plugin_formvalidation_pages '
            );
            $lastIdPage = $DB->insert_id();
         }

         if ($DB->tableExists("glpi_plugin_formvalidation_forms") && $lastIdPage != 0) {
            $values = [
               "'form(/plugins/formvalidation/front/itemtype.form.php)',$lastIdPage, 'form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]', 1, 1, 0, NULL, NULL, NULL, ''",
               "'form(/plugins/formvalidation/front/itemtype.form.php)',$lastIdPage, 'form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]', 0, 1, 0, NULL, NULL, NULL, ''"
               ];

            foreach ($values as $value) {
               $query = "INSERT INTO `glpi_plugin_formvalidation_forms` (`name`, `pages_id`, `css_selector`, `is_createitem`, `is_active`, `use_for_massiveaction`, `formula`, `comment`, `date_mod`, `guid`)
                        VALUE ($value);";
               $DB->query($query) or die("Error inserting default item forms into glpi_plugin_formvalidation_forms " . $DB->error());
               $lastIdForms[] = $DB->insert_id();
            }
         }

         if ($DB->tableExists("glpi_plugin_formvalidation_fields") && !empty($lastIdForms)) {
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
   }

   /* update value guid field */
   if ($DB->fieldExists('glpi_plugin_formvalidation_itemtypes', 'guid', false)) {
      $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/".$obj[0]."/".time()."/".rand()."/";
      foreach ($DB->request('glpi_plugin_formvalidation_itemtypes', ['guid' => ""]) as $row) {
         $guid =  $guid."".$row['id'];
         $id = $row['id'];
         $DB->updateOrDie(
            'glpi_plugin_formvalidation_itemtypes',
            ['guid' => md5($guid)],
            ['id' => $id],
            'Error updating value into glpi_plugin_formvalidation_itemtypes'
         );
      }
   }
   /* update value guid field */
   if ($DB->fieldExists('glpi_plugin_formvalidation_pages', 'guid', false)) {
      $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/".$obj[1]."/".time()."/".rand()."/";
      foreach ($DB->request('glpi_plugin_formvalidation_pages', ['guid'=>""]) as $row) {
         $guid =  $guid."".$row['id'];
         $id = $row['id'];
         $DB->updateOrDie(
            'glpi_plugin_formvalidation_pages',
            ['guid' => md5($guid)],
            ['id' => $id],
            'Error updating value into glpi_plugin_formvalidation_pages'
         );
      }
   }
   /* Update value guid field */
   if ($DB->fieldExists('glpi_plugin_formvalidation_forms', 'guid', false)) {
      $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/".$obj[2]."/".time()."/".rand()."/";
      foreach ($DB->request('glpi_plugin_formvalidation_forms', ['guid' => ""]) as $row) {
         $guid =  $guid."".$row['id'];
         $id = $row['id'];
         $DB->updateOrDie(
            'glpi_plugin_formvalidation_forms',
            ['guid' => md5($guid)],
            ['id' => $id],
            'Error updating value into glpi_plugin_formvalidation_forms'
         );
      }
   }
   /* Update value guid field */
   if ($DB->fieldExists('glpi_plugin_formvalidation_fields', 'guid', false)) {
      $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/".$obj[3]."/".time()."/".rand()."/";
      foreach ($DB->request('glpi_plugin_formvalidation_fields', ['guid' => ""]) as $row) {
         $guid =  $guid."".$row['id'];
         $id = $row['id'];
         $DB->updateOrDie(
            'glpi_plugin_formvalidation_fields',
            ['guid' => md5($guid)],
            ['id' => $id],
            'Error updating value into glpi_plugin_formvalidation_fields'
         );
      }
   }
   if ($DB->tableExists("glpi_plugin_formvalidation_fields")) {
      $oldFormula = ['isValidDate', 'isValidInteger', 'countWords', 'isValidIPv4', 'isValidIPv6', 'isValidEmail', 'isValidMacAddress'];
      $newFormula = ['FVH.isValidDate', 'FVH.isValidInteger', 'FVH.countWords', 'FVH.isValidIPv4', 'FVH.isValidIPv6', 'FVH.isValidEmail', 'FVH.isValidMacAddress'];
      $datas = $DB->query("SELECT id, formula FROM glpi_plugin_formvalidation_fields ");
      if ($DB->numrows($datas) > 0) {
         while ($row = $DB->fetchAssoc($datas)) {
            if ($row['formula'] != null && strpos($row['formula'], 'FVH.') < 0) {
               $res = str_replace($oldFormula, $newFormula, $row['formula']);
               $DB->query("UPDATE glpi_plugin_formvalidation_fields SET formula = '".$DB->escape($res)."' WHERE id = ".$row['id']);
            }
         }
      }
   }

   /****************************************************
    * update data for 1.0.0 to 1.0.1
    * *************************************************/
   if ($config->fields['db_version'] == '1.0.0') {
      //* it is neccessary to upgrade the data for the itemtype dropdown
      // get itemtype
      $itemtypes = $dbu->getAllDataFromTable('glpi_plugin_formvalidation_itemtypes', [
         'name' => 'PluginFormvalidationItemtype',
         'URL_path_part' => '/plugins/formvalidation/front/itemtype.form.php'
         ]);
      if (count($itemtypes)) {
         // there should be only one row
         // get page
         $pages = $dbu->getAllDataFromTable('glpi_plugin_formvalidation_pages', [
            'itemtypes_id' => array_column($itemtypes, 'id')
            ]);
         if (count($pages)) {
            // get forms
            $forms = $dbu->getAllDataFromTable('glpi_plugin_formvalidation_forms', [
               'pages_id' => array_column($pages, 'id')
               ]);
            foreach ($forms as $form) {
               $fieldName = array_values($dbu->getAllDataFromTable('glpi_plugin_formvalidation_fields', [
                  'forms_id' => $form['id'],
                  'name'     => 'Name'
                  ]));
               $fieldName = $fieldName[0];

               $fieldItemtype = array_values($dbu->getAllDataFromTable('glpi_plugin_formvalidation_fields', [
                  'id' => $fieldName['id'] + 1
                  ]));
               $fieldItemtype = $fieldItemtype[0];

               $fieldURL = array_values($dbu->getAllDataFromTable('glpi_plugin_formvalidation_fields', [
                  'id' => $fieldName['id'] + 2
                  ]));
               $fieldURL = $fieldURL[0];

               $DB->update('glpi_plugin_formvalidation_fields', ['name' => $fieldItemtype['name']], ['id' => $fieldName['id']]);
               $DB->delete('glpi_plugin_formvalidation_fields', ['id' => $fieldURL['id']]);
               $DB->update('glpi_plugin_formvalidation_fields', [
                  'name'    => $fieldURL['name'],
                  'formula' => $fieldURL['formula'],
                  'css_selector_value' => 'div>table>tbody>tr:eq(2)>td:eq(1) input[name="URL_path_part"]'
                  ], [
                  'id' => $fieldItemtype['id']
                  ]);
            }
         }
      }
      $config->update(['id' => 1, 'db_version' => '1.0.1']);
   }
}