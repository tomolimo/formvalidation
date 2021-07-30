<?php
/*
 * -------------------------------------------------------------------------
Form Validation plugin
Copyright (C) 2016-2020 by Raynet SAS a company of A.Raymond Network.

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


// ----------------------------------------------------------------------
// Original Author of file: Olivier Moron
// ----------------------------------------------------------------------

define ("PLUGIN_FORMVALIDATION_VERSION", "1.0.11");


/**
 * Summary of plugin_init_formvalidation
 * @return
 */
function plugin_init_formvalidation() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $DB;

   if ((!isset($_SESSION["glpicronuserrunning"]) || (Session::getLoginUserID() != $_SESSION["glpicronuserrunning"])) && !isset($_SESSION['glpiformvalidationeditmode'])) {
      $_SESSION['glpiformvalidationeditmode'] = 0;
   }
   $PLUGIN_HOOKS['csrf_compliant']['formvalidation'] = true;

   Plugin::registerClass('PluginFormvalidationPage');

   if (Config::canUpdate()) {
      Plugin::registerClass('PluginFormvalidationUser',
                         ['addtabon'                    => ['Preference', 'User']]);

      // Display a menu entry
      $PLUGIN_HOOKS['menu_toadd']['formvalidation'] = ['config' => 'PluginFormvalidationMenu'];
   }
   Plugin::registerClass('PluginFormvalidationConfig', ['addtabon' => 'Config']);
   $PLUGIN_HOOKS['config_page']['formvalidation'] = 'front/config.form.php';

   //$PLUGIN_HOOKS['pre_item_add']['formvalidation'] = array(
   //           'Ticket' => array('PluginFormvalidationHook', 'plugin_pre_item_add_formvalidation')
   //       );

   $res = $DB->request([
                  'SELECT'     => 'glpi_plugin_formvalidation_itemtypes.name',
                  'FROM'       => 'glpi_plugin_formvalidation_itemtypes',
                  'INNER JOIN' => [
                     'glpi_plugin_formvalidation_pages' => [
                        'FKEY' => [
                           'glpi_plugin_formvalidation_pages' => 'itemtypes_id',
                           'glpi_plugin_formvalidation_itemtypes' => 'id'
                        ]
                     ]
                  ],
         ]);
   foreach ($res as $itemtype) {
      $PLUGIN_HOOKS['pre_item_update']['formvalidation'][$itemtype['name']] = ['PluginFormvalidationHook', 'plugin_pre_item_update_formvalidation'];
   }

   $PLUGIN_HOOKS['pre_show_item']['formvalidation'] = ['PluginFormvalidationHook', 'plugin_pre_show_tab_formvalidation'];

   $PLUGIN_HOOKS['post_item_form']['formvalidation'] = ['PluginFormvalidationHook', 'plugin_post_item_form_formvalidation'];

   $PLUGIN_HOOKS['use_massive_action']['formvalidation'] = 1;

   // Add specific files to add to the header : javascript or css
   $plug = new Plugin;
   if ($plug->isActivated('formvalidation')) {
      $PLUGIN_HOOKS['add_javascript']['formvalidation'] = [
         'js/formvalidation.js',
         'lib/moment/moment.js',
         'js/helpers_function.js'
      ];
      $PLUGIN_HOOKS['add_css']['formvalidation'] = ['css/formvalidation.css'];
   }
}


/**
 * Get the name and the version of the plugin
 * @return mixed
 */
function plugin_version_formvalidation() {

   return [
      'name'           => 'Form Validation',
      'version'        => PLUGIN_FORMVALIDATION_VERSION,
      'author'         => 'Olivier Moron',
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/tomolimo/formvalidation',
      'requirements'   => [
         'glpi' => [
            'min' => '9.5',
            'max' => '9.6'
         ]
      ]
   ];
}


/**
 * Summary of plugin_formvalidation_check_prerequisites
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 * @return boolean
 */
function plugin_formvalidation_check_prerequisites() {

   if (version_compare(GLPI_VERSION, '9.5', 'lt') || version_compare(GLPI_VERSION, '9.6', 'ge')) {
      echo "This plugin requires GLPI >= 9.5 and < 9.6";
      return false;
   }
   return true;
}


/**
 * Summary of plugin_formvalidation_check_config
 * Check configuration process for plugin : need to return true if succeeded
 * Can display a message only if failure and $verbose is true
 * @param mixed $verbose not used
 * @return boolean
 */
function plugin_formvalidation_check_config($verbose = false) {

   return true;
}

