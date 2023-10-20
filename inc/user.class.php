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

/**
 * PluginFormvalidationUser short summary.
 *
 * PluginFormvalidationUser description.
 *
 * @version 1.0
 * @author MoronO
 */
class PluginFormvalidationUser extends CommonDBTM
{

   /**
    * Summary of getTabNameForItem
    * @param CommonGLPI $item
    * @param  $withtemplate
    * @return string[]
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      return [ 'formvalidationformvalidation' => __('Form Validation', 'formvalidation') ];
   }


   /**
    * Summary of displayTabContentForItem
    * @param CommonGLPI $item
    * @param  $tabnum
    * @param  $withtemplate
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if (in_array( $item->getType(), [ 'Preference', 'User' ])) {
         $pref = new self();
         $user_id = ($item->getType()=='Preference'?Session::getLoginUserID():$item->getID());
         $pref->showForm($user_id);
      }
      return true;
   }


   /**
    * Summary of showForm
    * @param  $user_id
    * @param  $options
    */
   function showForm($user_id, $options = []) {

      $target = $this->getFormURL();
      if (isset($options['target'])) {
         $target = $options['target'];
      }

      echo "<form action='".$target."' method='post'>";
      echo "<input type=hidden name=users_id value='$user_id'/>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='2'>".__('Edit mode', 'formvalidation')."</th></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>".__('Edit mode (valid for current session only, will be reset at next login)', 'formvalidation')." :</td><td>";

      Dropdown::showYesNo('editmode', $_SESSION['glpiformvalidationeditmode'] ); //$this->fields["editmode"]);

      echo "</td></tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\"".__('Save')."\">";
      echo "</td></tr>";

      echo "</table>";
      Html::closeForm();
   }

}
