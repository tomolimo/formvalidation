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

/**
 * Summary of plugin_formvalidation_install
 * @return boolean
 */
function plugin_formvalidation_install() {
   global $DB;
   if (!$DB->tableExists("glpi_plugin_formvalidation_configs")) {
      // new installation
      include_once(GLPI_ROOT."/plugins/formvalidation/install/install.php");
      formvalidation_install();

   } else {
      // upgrade installation
      include_once(GLPI_ROOT."/plugins/formvalidation/install/update.php");
      formvalidation_update();
   }

   return true;
}


/**
 * Summary of plugin_formvalidation_uninstall
 * Uninstall process for plugin : need to return true if succeeded
 * @return boolean
 */
function plugin_formvalidation_uninstall() {
   // will not drop tables
   return true;
}

/**
 * Define Dropdown tables to be manage in GLPI :
 **/
function plugin_formvalidation_getDropdown() {

   return ['PluginFormvalidationItemtype'  => PluginFormvalidationItemtype::getTypeName(2)];
}


function plugin_formvalidation_MassiveActions($type) {
   $actions = [];
   switch ($type) {
      case 'PluginFormvalidationPage' :
         $myclass      = 'PluginFormvalidationPage';
         $action_key   = 'exportPage';
         $action_label = __("Export Page", 'Export');
         $actions[$myclass.MassiveAction::CLASS_ACTION_SEPARATOR.$action_key]
            = $action_label;

         break;
      case 'PluginFormvalidationForm':
         $myclass      = 'PluginFormvalidationForm';
         $action_key   = 'exportForm';
         $action_label = __("Export Form", 'Export');
         $actions[$myclass.MassiveAction::CLASS_ACTION_SEPARATOR.$action_key]
            = $action_label;

         break;
   }
   return $actions;
}


