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

class PluginFormvalidationMenu extends CommonGLPI {
   static $rightname = 'config';

   static function getMenuName() {
      return __("Form Validations", "formvalidation");
   }

   static function getMenuContent() {

      if (!Session::haveRight(self::$rightname, READ)) {
         return;
      }

      $front_page = "/plugins/formvalidation/front";
      $menu = [];
      $menu['title'] = self::getMenuName();
      $menu['page']  = "$front_page/page.php";

      $itemtypes = ['PluginFormvalidationPage' => 'formvalidationpage',
                         'PluginFormvalidationForm' => 'formvalidationform',
                         'PluginFormvalidationField' => 'formvalidationfield'];

      foreach ($itemtypes as $itemtype => $option) {
         $menu['options'][$option]['title']           = $itemtype::getTypeName(Session::getPluralNumber());
         switch ($itemtype) {
            case 'PluginFormvalidationPage':
               $menu['options'][$option]['page']            = $itemtype::getSearchURL(false);
               $menu['options'][$option]['links']['search'] = $itemtype::getSearchURL(false);
               if ($itemtype::canCreate()) {
                  $menu['options'][$option]['links']['add'] = $itemtype::getFormURL(false);
               }
               break;
            case 'PluginFormvalidationForm':
            case 'PluginFormvalidationField':
               $menu['options'][$option]['page']            = $itemtype::getSearchURL(false);
               $menu['options'][$option]['links']['search'] = $itemtype::getSearchURL(false);
               break;
            default :
               $menu['options'][$option]['page']            = PluginFormvalidationPage::getSearchURL(false);
               break;
         }

      }
      return $menu;
   }


}
