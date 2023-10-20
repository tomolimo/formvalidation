<?php
use Glpi\Application\View\TemplateRenderer;
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

class PluginFormvalidationConfig extends CommonDBTM {

   static private $_instance = null;
   static $rightname = 'config';

   /**
    * Singleton for the unique config record
    * @return PluginFormvalidationConfig singleton
    */
   static function getInstance() {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
         if (!self::$_instance->getFromDB(1)) {
            self::$_instance->getEmpty();
         }
      }
      return self::$_instance;
   }



   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType()=='Config') {
         return "Formvalidation";
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType()=='Config') {
         self::showConfigForm($item);
      }
      return true;
   }

   static function showConfigForm($item) {
      global $DB;
      $config = self::getInstance();
      $config->showFormHeader();

      $extLoaded = extension_loaded('v8js');
      $html = TemplateRenderer::getInstance()->render('@formvalidation/config_form.html.twig', [
        'data' => $config->fields,
        'extLoaded' => $extLoaded,
     ]);

      echo $html;
      $config->showFormButtons(['candel'=>false]);

      return false;
   }
}

