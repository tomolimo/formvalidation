<?php
/*
 * -------------------------------------------------------------------------
Form Validation plugin
Copyright (C) 2016 by Raynet SAS a company of A.Raymond Network.

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

define ("PLUGIN_FORMVALIDATION_VERSION", "0.5.1");

/**
 * Summary of plugin_init_formvalidation
 * @return mixed
 */
function plugin_init_formvalidation() {
   global $PLUGIN_HOOKS,$LANG,$CFG_GLPI;

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

   //$PLUGIN_HOOKS['pre_item_add']['formvalidation'] = array(
   //           'Ticket' => array('PluginFormvalidationHook', 'plugin_pre_item_add_formvalidation')
   //       );

   if (extension_loaded('v8js')) {
      // used only for validation of massiveactions
      // can only be done with v8js module
      $PLUGIN_HOOKS['pre_item_update']['formvalidation'] = [
                 'Ticket' => ['PluginFormvalidationHook', 'plugin_pre_item_update_formvalidation'],
                 'Computer' => ['PluginFormvalidationHook', 'plugin_pre_item_update_formvalidation']
             ];
   }

   $PLUGIN_HOOKS['pre_show_item']['formvalidation'] = ['PluginFormvalidationHook', 'plugin_pre_show_tab_formvalidation'];

   $PLUGIN_HOOKS['post_item_form']['formvalidation'] = ['PluginFormvalidationHook', 'plugin_post_item_form_formvalidation'];

   // Add specific files to add to the header : javascript or css
   $plug = new Plugin;
   if ($plug->isActivated('formvalidation')) {
      $PLUGIN_HOOKS['add_javascript']['formvalidation'] = ['js/formvalidation.js'];
      $PLUGIN_HOOKS['add_css']['formvalidation'] = ['css/formvalidation.css'];
   }
}


/**
 * Get the name and the version of the plugin
 * @return mixed
 */
function plugin_version_formvalidation() {

   return ['name'           => 'Form Validation',
                 'version'        => PLUGIN_FORMVALIDATION_VERSION,
                 'author'         => 'Olivier Moron',
                 'license'        => 'GPLv2+',
                 'homepage'       => 'https://github.com/tomolimo/formvalidation',
                 'minGlpiVersion' => '9.3',
                  'requirements'   => ['glpi' => ['min' => '9.3',
                                           'max' => '9.4']]
                                           ];
}


/**
 * Summary of plugin_formvalidation_check_prerequisites
 * Optional : check prerequisites before install : may print errors or add to message after redirect
 * @return boolean
 */
function plugin_formvalidation_check_prerequisites() {

   if (version_compare(GLPI_VERSION, '9.3', 'lt')) {
      echo "This plugin requires GLPI >= 9.3";
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

