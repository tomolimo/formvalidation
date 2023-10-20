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


   if (!$DB->fieldExists('glpi_plugin_formvalidation_configs', 'db_version')) {
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
            $lastIdItemTypes = $DB->insertId();
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
               $lastIdPage = $DB->insertId();
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
                  $lastIdForms[] = $DB->insertId();
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
   }
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
            'plugin_formvalidation_itemtypes_id' => array_column($itemtypes, 'id')
            ]);
         if (count($pages)) {
            // get forms
            $forms = $dbu->getAllDataFromTable('glpi_plugin_formvalidation_forms', [
               'plugin_formvalidation_pages_id' => array_column($pages, 'id')
               ]);
            foreach ($forms as $form) {
               $fieldName = array_values($dbu->getAllDataFromTable('glpi_plugin_formvalidation_fields', [
                  'plugin_formvalidation_forms_id' => $form['id'],
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

   function my_sqlRegexp($str) {
      return str_replace(
         ['[', '\\\\"', '.', '(', '^', ')'],
         ['\\\\[', '\\\\\\\{0,1}"', '\\\\.', '\\\\(', '\\\\^', '\\\\)'],
         $str);
   }

   function prepareUpdateQuery($args) {
      return "UPDATE glpi_plugin_formvalidation_fields AS gpf_fields
           LEFT JOIN glpi_plugin_formvalidation_forms AS gpf_forms ON gpf_forms.id = gpf_fields.plugin_formvalidation_forms_id
           LEFT JOIN glpi_plugin_formvalidation_pages AS gpf_pages ON gpf_pages.id = gpf_forms.plugin_formvalidation_pages_id
           LEFT JOIN glpi_plugin_formvalidation_itemtypes AS gpf_itemtypes ON gpf_itemtypes.id = gpf_pages.plugin_formvalidation_itemtypes_id
           SET `css_selector_value`='". $args['css_selector_value'] ."',
               `css_selector_errorsign`='". $args['css_selector_errorsign'] ."',
               `css_selector_mandatorysign`='". $args['css_selector_mandatorysign'] ."'
           WHERE gpf_itemtypes.URL_path_part LIKE '%". $args['URL_path_part'] ."%'
           AND gpf_forms.is_createitem = ". $args['is_createitem'] ."
           AND gpf_forms.css_selector REGEXP '". my_sqlRegexp($args['where_css_selector']) ."'
           AND gpf_fields.css_selector_value REGEXP '". my_sqlRegexp($args['where_css_selector_value']) . "'";
   }




// upgrade version 1.0.1 to 2
   $config = PluginFormvalidationConfig::getInstance();
   if ($config->fields['db_version'] == '1.0.1') {
      $DB->beginTransaction();

      $query = "UPDATE  `glpi_plugin_formvalidation_itemtypes`
         SET `URL_path_part`='/front/ticket.form.php'
         WHERE URL_path_part = '/front/ticket.form.php,'";
      $DB->queryOrDie($query, 'Error while update itemtype ticket');
     // Update itemtypes_id
      $query = "ALTER TABLE `glpi_plugin_formvalidation_pages`
         CHANGE COLUMN `itemtypes_id` `plugin_formvalidation_itemtypes_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `entities_id`,
         DROP INDEX `itemtypes_id`,
         ADD UNIQUE INDEX `plugin_formvalidation_itemtypes_id` (`plugin_formvalidation_itemtypes_id`) USING BTREE";
      $DB->queryOrDie($query, 'Error change column itemtypes_id into plugin_formvalidation_itemtypes_id in glpi_plugin_formvalidation_pages table');

      $query = "ALTER TABLE `glpi_plugin_formvalidation_forms`
         CHANGE COLUMN `pages_id` `plugin_formvalidation_pages_id` INT UNSIGNED NOT NULL AFTER `name`,
         DROP INDEX `pages_id`,
         ADD INDEX `pages_id` (`plugin_formvalidation_pages_id`) USING BTREE;";
      $DB->queryOrDie($query, 'Error change column pages_id into plugin_formvalidation_pages_id in glpi_plugin_formvalidation_forms table');

      $query = "ALTER TABLE `glpi_plugin_formvalidation_fields`
         CHANGE COLUMN `forms_id` `plugin_formvalidation_forms_id` INT UNSIGNED NOT NULL AFTER `name`,
         DROP INDEX `forms_id_css_selector_value`,
         ADD UNIQUE INDEX `forms_id_css_selector_value` (`plugin_formvalidation_forms_id`, `css_selector_value`) USING BTREE,
         DROP INDEX `forms_id`,
         ADD INDEX `forms_id` (`plugin_formvalidation_forms_id`) USING BTREE";
      $DB->queryOrDie($query, 'Error change column forms_id into plugin_formvalidation_forms_id in glpi_plugin_formvalidation_fields table');

      $query = "UPDATE `glpi_plugin_formvalidation_forms` SET
         `css_selector`='form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/page.form.php\"]'
          WHERE `css_selector`='form[name=\"form\"][action=\"/plugins/formvalidation/front/page.form.php\"]'";
      $DB->queryOrDie($query, 'Error while update form with css_selector: form[name=\"form\"][action=\"/plugins/formvalidation/front/page.form.php\"]');

      $query = "UPDATE `glpi_plugin_formvalidation_forms` SET
         `css_selector`='form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/form.form.php\"]'
         WHERE `css_selector`='form[name=\"form\"][action=\"/plugins/formvalidation/front/form.form.php\"]'";
      $DB->queryOrDie($query, 'Error while update form with css_selector: form[name=\"form\"][action=\"/plugins/formvalidation/front/form.form.php\"]');


      $query = "UPDATE `glpi_plugin_formvalidation_forms` SET
         `css_selector`='form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/field.form.php\"]'
         WHERE `css_selector`='form[name=\"form\"][action=\"/plugins/formvalidation/front/field.form.php\"]'";
      $DB->queryOrDie($query, 'Error while update form with css_selector: form[name=\"form\"][action=\"/plugins/formvalidation/front/field.form.php\"]');

       $query = "UPDATE `glpi_plugin_formvalidation_forms` SET
         `css_selector`='form[id=\"itil-form\"][action=\"/front/tracking.injector.php\"]'
         WHERE `css_selector`='form[name=\"helpdeskform\"][action=\"/front/tracking.injector.php\"]'";
      $DB->queryOrDie($query, 'Error while update form with css_selector: form[name=\"helpdeskform\"][action=\"/front/tracking.injector.php\"]');

      $query = "UPDATE `glpi_plugin_formvalidation_forms` SET
         `css_selector`='form[name=\"asset_form\"][action=\"/front/itilfollowup.form.php\"]'
          WHERE `css_selector`='form[name=\"form\"][action=\"/front/itilfollowup.form.php\"]'";
      $DB->queryOrDie($query, 'Error while update form with css_selector: form[name=\"form\"][action=\"/front/ticketfollowup.form.php\"]');

      $query = "UPDATE `glpi_plugin_formvalidation_forms` SET
         `css_selector`='form[id=\"itil-form\"][action^=\"/front/ticket.form.php\"]'
          WHERE `css_selector`='form[name=\"form_ticket\"][action=\"/front/ticket.form.php\"]'";
      $DB->queryOrDie($query, 'Error while update form with css_selector: form[name=\"form_ticket\"][action=\"/front/ticket.form.php\"]');

      $query = "UPDATE `glpi_plugin_formvalidation_forms` SET
        `css_selector`='form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/page.form.php\"]'
         WHERE `css_selector`='form[name=\"form\"][action=\"/plugins/formvalidation/front/page.form.php\"]'";
      $DB->queryOrDie($query, 'Error while update form with css_selector: form[name=\"form\"][action=\"/plugins/formvalidation/front/page.form.php\"]');

      $query = "UPDATE `glpi_plugin_formvalidation_forms` SET
         `css_selector`='form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]'
          WHERE `css_selector`='form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]'";
      $DB->queryOrDie($query, 'Error while update form with css_selector: form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]');

      $query = "UPDATE `glpi_plugin_formvalidation_forms` SET
         `css_selector`='form[name=\"asset_form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]'
          WHERE `css_selector`='form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]'";
      $DB->queryOrDie($query, 'Error while update form with css_selector: form[name=\"form\"][action=\"/plugins/formvalidation/front/itemtype.form.php\"]');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\\"name\\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(0)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(0)>label',
         'URL_path_part' => '/plugins/formvalidation/front/page.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/page.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\\"name\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Name\' in page : Form Validation Page');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\"name\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(0)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(0)>label',
         'URL_path_part' => '/plugins/formvalidation/front/form.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/form.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\"name\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Name\' in page : Form Validation Form');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(4)>div input[name=\\\"css_selector\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(0)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(0)>label',
         'URL_path_part' => '/plugins/formvalidation/front/form.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/form.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(4)>td:eq(1) input[name=\\\\"css_selector\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'css_selector\' in page : Form Validation Form');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\"name\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(0)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(0)>label',
         'URL_path_part' => '/plugins/formvalidation/front/field.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/field.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\\"name\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Name\' in page : Form Validation Field');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(3)>div input[name=\\\"css_selector_value\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(3)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(3)>label',
         'URL_path_part' => '/plugins/formvalidation/front/field.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/field.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(3)>td:eq(1) input[name=\\\\"css_selector_value\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Value CSS selector\' in page : Form Validation Field');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(6)>div input[name=\\\"show_mandatory\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(6)>div>input:eq(1)',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(6)>label',
         'URL_path_part' => '/plugins/formvalidation/front/field.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/field.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(6)>td:eq(1)>span input[name=\\\\"show_mandatory\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Force mandatory sign\' in page : Form Validation Field');


       $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(7)>div input[name=\\\"show_mandatory_if\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(7)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(7)>label',
         'URL_path_part' => '/plugins/formvalidation/front/field.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/field.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(7)>td:eq(1) input[name=\\\\"show_mandatory_if\\\\"]'
         ]);
       $DB->queryOrDie($query, 'Error while update field \'Mandatory sign formula\' in page : Form Validation Field');

        $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(8)>div input[name=\\\"css_selector_mandatorysign\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(8)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(8)>label',
         'URL_path_part' => '/plugins/formvalidation/front/field.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/field.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(8)>td:eq(1) input[name=\\\\"css_selector_mandatorysign\\\\"]'
         ]);
        $DB->queryOrDie($query, 'Error while update field \'Mandatory sign CSS selector\' in page : Form Validation Field');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>div:eq(2)>div>div:eq(6)>div input[name=\\\"name\\\"]',
         'css_selector_errorsign' => 'div>div:eq(2)>div>div:eq(6)>div>input',
         'css_selector_mandatorysign' => 'div>div:eq(2)>div>div:eq(6)>label',
         'URL_path_part' => '/front/ticket.form.php',
         'is_createitem' => 1,
         'where_css_selector' => 'form[id=\\\\"itil-form\\\\"][action=\\\\"/front/tracking.injector.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(8)>td:eq(1) input[name=\\\\"name\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Title\' in page : Ticket Validations');


      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>div:eq(2)>div>div:eq(7)>div textarea[name=\"content\"]',
         'css_selector_errorsign' => 'div>div:eq(2)>div>div:eq(7)>div>div:eq(0)',
         'css_selector_mandatorysign' => 'div>div:eq(2)>div>div:eq(7)>label',
         'URL_path_part' => '/front/ticket.form.php',
         'is_createitem' => 1,
         'where_css_selector' => 'form[id=\\\\"itil-form\\\\"][action=\\\\"/front/tracking.injector.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(9)>td:eq(1)>div textarea[name=\\\\"content\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Description\' in page : Ticket Validations');


      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>div:eq(2)>div>div:eq(0)>div select[name=\\\"type\\\"]',
         'css_selector_errorsign' => 'div>div:eq(2)>div>div:eq(0)>div>span',
         'css_selector_mandatorysign' => 'div>div:eq(2)>div>div:eq(0)>label',
         'URL_path_part' => '/front/ticket.form.php',
         'is_createitem' => 1,
         'where_css_selector' => 'form[id=\\\\"itil-form\\\\"][action=\\\\"/front/tracking.injector.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(1)>td:eq(1) select[name=\\\\"type\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Type\' in page : Ticket Validations');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div:eq(0)>div:eq(0)>div textarea[name=\\\"content\\\"]',
         'css_selector_errorsign' => 'div:eq(0)>div:eq(0)>div>div:eq(0):not(\\\".fileupload\\\")',
         'css_selector_mandatorysign' => 'div:eq(0)>div:eq(0)>div>div:eq(0):not(\\\".fileupload\\\")>div:eq(0)>div:eq(0)',
         'URL_path_part' => '/front/ticket.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/front/itilfollowup.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(1)>td:eq(0)>div:eq(0)>div>div:eq(1) iframe[id^=\\\\"content\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Description\' in page : Ticket Validations');


      $lastIdForm = 0;
      $mainDescriptionForm = false;
      $query = "SELECT * FROM glpi_plugin_formvalidation_forms WHERE css_selector = 'form[name=\"main_description\"][action=\"/front/ticket.form.php\"]'";
      foreach ($DB->request($query) as $row) {
         $mainDescriptionForm = $row['id'];
      }
      if ($mainDescriptionForm == false) {
         $query = "SELECT glpi_plugin_formvalidation_pages.id as 'id'
          FROM glpi_plugin_formvalidation_pages
          RIGHT JOIN glpi_plugin_formvalidation_itemtypes
          ON glpi_plugin_formvalidation_pages.plugin_formvalidation_itemtypes_id=glpi_plugin_formvalidation_itemtypes.id
          WHERE glpi_plugin_formvalidation_itemtypes.name = 'Ticket'";
         $ticketPageId = 0;
         foreach ($DB->request($query) as $row) {
            $ticketPageId = $row['id'];
         }
         if ($ticketPageId) {
            $query = "INSERT INTO `glpi_plugin_formvalidation_forms`
           (`name`, `plugin_formvalidation_pages_id`, `css_selector`, `is_createitem`, `is_active`, `use_for_massiveaction`, `formula`, `comment`, `date_mod`, `guid`)
           VALUES
           ('main_description(/front/ticket.form.php)',". $ticketPageId .", 'form[name=\"main_description\"][action=\"/front/ticket.form.php\"]', 0, 1, 0, NULL, NULL, '2022-07-12 15:53:46', '68d7a4f8490174e5eba50ec2c5fc93a1');";
            $DB->queryOrDie($query, 'Error while adding \'main_description form\' in page : Ticket validation');

            $lastIdForm = $DB->insertId();
         }
      }
      if ($lastIdForm > 0 && !$mainDescriptionForm) {
         $mainDescriptionFormId = $lastIdForm;
      } else {
         $mainDescriptionFormId = $mainDescriptionForm;
      }

      $query = "UPDATE glpi_plugin_formvalidation_fields AS gpf_fields
           LEFT JOIN glpi_plugin_formvalidation_forms AS gpf_forms ON gpf_forms.id = gpf_fields.plugin_formvalidation_forms_id
           LEFT JOIN glpi_plugin_formvalidation_pages AS gpf_pages ON gpf_pages.id = gpf_forms.plugin_formvalidation_pages_id
           LEFT JOIN glpi_plugin_formvalidation_itemtypes AS gpf_itemtypes ON gpf_itemtypes.id = gpf_pages.plugin_formvalidation_itemtypes_id
           SET `plugin_formvalidation_forms_id`=". $mainDescriptionFormId .",
               `css_selector_value`='div:eq(0)>div:eq(1)>div input[name=\\\"name\\\"]',
               `css_selector_errorsign`='div:eq(0)>div:eq(1)>div>input',
               `css_selector_mandatorysign`='div:eq(0)>div:eq(1)>label'
           WHERE gpf_itemtypes.URL_path_part LIKE '%/front/ticket.form.php%'
           AND gpf_forms.is_createitem = 0
           AND gpf_forms.css_selector REGEXP'". my_sqlRegexp('form[id=\\\\"itil-form\\\\"][action^=\\\\"/front/ticket.form.php\\\\"]') ."'
           AND gpf_fields.css_selector_value REGEXP '". my_sqlRegexp('div>table:eq(2)>tbody>tr:eq(0)>td input[name=\\\\"name\\\\"]') ."'";
      $DB->queryOrDie($query, 'Error while update field \'Title\' in page : Ticket Validations');

      $query = "UPDATE glpi_plugin_formvalidation_fields AS gpf_fields
           LEFT JOIN glpi_plugin_formvalidation_forms AS gpf_forms ON gpf_forms.id = gpf_fields.plugin_formvalidation_forms_id
           LEFT JOIN glpi_plugin_formvalidation_pages AS gpf_pages ON gpf_pages.id = gpf_forms.plugin_formvalidation_pages_id
           LEFT JOIN glpi_plugin_formvalidation_itemtypes AS gpf_itemtypes ON gpf_itemtypes.id = gpf_pages.plugin_formvalidation_itemtypes_id
           SET `plugin_formvalidation_forms_id`=". $mainDescriptionFormId .",
               `css_selector_value`='div:eq(0)>div:eq(2)>div:eq(0) textarea[name=\"content\"]',
               `css_selector_errorsign`='div:eq(0)>div:eq(2)>div:eq(0)>div',
               `css_selector_mandatorysign`='div:eq(0)>div:eq(2)>label'
           WHERE gpf_itemtypes.URL_path_part LIKE '%/front/ticket.form.php%'
           AND gpf_forms.is_createitem = 0
           AND gpf_forms.css_selector REGEXP'". my_sqlRegexp('form[id=\\\\"itil-form\\\\"][action^=\\\\"/front/ticket.form.php\\\\"]') ."'
           AND gpf_fields.css_selector_value REGEXP '". my_sqlRegexp('div>table:eq(2)>tbody>tr:eq(1)>td>div>div>div>div:eq(1) iframe[id^=\\\\"content\\\\"]') ."'";
      $DB->queryOrDie($query, 'Error while update field \'Description\' in page : Ticket Validations');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div:eq(0)>div>div>div>div:eq(2)>div select[name=\\\"type\\\"]',
         'css_selector_errorsign' => 'div:eq(0)>div>div>div>div:eq(2)>div>span',
         'css_selector_mandatorysign' => 'div:eq(0)>div>div>div>div:eq(2)>label',
         'URL_path_part' => '/front/ticket.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[id=\\\\"itil-form\\\\"][action^=\\\\"/front/ticket.form.php\\\\"]',
         'where_css_selector_value' => 'div>table:eq(1)>tbody>tr:eq(0)>td:eq(0) select[name=\\\\"type\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Type\' in page : Ticket Validations');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(0)>div input[name=\\\"name\\\"]',
         'css_selector_errorsign' => 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(0)>div>input',
         'css_selector_mandatorysign' => 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(0)>label',
         'URL_path_part' => '/front/ticket.form.php',
         'is_createitem' => 1,
         'where_css_selector' => 'form[id=\\\\"itil-form\\\\"][action^=\\\\"/front/ticket.form.php\\\\"]',
         'where_css_selector_value' => 'div>table:eq(2)>tbody>tr:eq(0)>td input[name=\\\\"name\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Title\' in page : Ticket Validations');

       $query = prepareUpdateQuery([
         'css_selector_value' => 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(1)>div:eq(0) textarea[name=\"content\"]',
         'css_selector_errorsign' => 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(1)>div:eq(0)>div',
         'css_selector_mandatorysign' => 'div:eq(0)>div:eq(0)>div>div>div>div:eq(1)>span>div>div:eq(1)>div:eq(1)>label',
         'URL_path_part' => '/front/ticket.form.php',
         'is_createitem' => 1,
         'where_css_selector' => 'form[id=\\\\"itil-form\\\\"][action^=\\\\"/front/ticket.form.php\\\\"]',
         'where_css_selector_value' => 'div>table:eq(2)>tbody>tr:eq(1)>td>div>div>div>div:eq(1) iframe[id^=\\\\"content\\\\"]'
         ]);
       $DB->queryOrDie($query, 'Error while update field \'Description\' in page : Ticket Validations');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\"name\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(0)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(0)>label',
         'URL_path_part' => '/plugins/formvalidation/front/page.form.php',
         'is_createitem' => 1,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/page.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\\"name\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Name\' in page : Form Validation Page');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\"name\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(0)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(0)>label',
         'URL_path_part' => '/plugins/formvalidation/front/itemtype.form.php',
         'is_createitem' => 1,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/itemtype.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\\"name\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Item type\' in page: Form Validation Itemtype');

       $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(1)>div input[name=\\\"URL_path_part\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(1)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(1)>label',
         'URL_path_part' => '/plugins/formvalidation/front/itemtype.form.php',
         'is_createitem' => 1,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/itemtype.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(2)>td:eq(1) input[name=\\\\"URL_path_part\\\\"]'
         ]);
       $DB->queryOrDie($query, 'Error while update field \'URL path part\' in page : Form Validation Itemtype');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(0)>div input[name=\\\"name\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(0)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(0)>label',
         'URL_path_part' => '/plugins/formvalidation/front/itemtype.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/itemtype.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(1)>td:eq(1) input[name=\\\\"name\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'Item type\' in page : Form Validation Itemtype');

      $query = prepareUpdateQuery([
         'css_selector_value' => 'div>table>tbody>tr>td>div>div:eq(1)>div input[name=\\\"URL_path_part\\\"]',
         'css_selector_errorsign' => 'div>table>tbody>tr>td>div>div:eq(1)>div>input',
         'css_selector_mandatorysign' => 'div>table>tbody>tr>td>div>div:eq(1)>label',
         'URL_path_part' => '/plugins/formvalidation/front/itemtype.form.php',
         'is_createitem' => 0,
         'where_css_selector' => 'form[name=\\\\"asset_form\\\\"][action=\\\\"/plugins/formvalidation/front/itemtype.form.php\\\\"]',
         'where_css_selector_value' => 'div>table>tbody>tr:eq(2)>td:eq(1) input[name=\\\\"URL_path_part\\\\"]'
         ]);
      $DB->queryOrDie($query, 'Error while update field \'URL path part\' in page : Form Validation Itemtype');


      $DB->update( "glpi_plugin_formvalidation_configs", ['db_version' => '2'], ['id' => '1']);

      $DB->commit();
   }
}

