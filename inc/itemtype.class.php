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

use Glpi\Application\View\TemplateRenderer;

/**
 * itemtype short summary.
 *
 * itemtype description.
 *
 * @version 1.0
 * @author MoronO
 */
class PluginFormvalidationItemtype extends CommonDropdown {
   public function maybeTranslated() {
      return false;
   }

   static function getTypeName($nb = 0) {
      return _n('Item Type', 'Item Types', $nb, 'formvalidation');
   }

   function showForm($id = null, $options = ['candel'=>false]) {
      global $DB;
      if (!$id) {
         $id = -1;
      }
      $this->initForm($id);
      $this->showFormHeader(['formtitle'=>'Item type','colspan' => 4]);

      

      $html = TemplateRenderer::getInstance()->render('@formvalidation/item_type.html.twig', [
        'data' => $this->fields
     ]);

      echo $html;


      $this->showFormButtons();


   }

   //function post_addItem() {
   //   global $DB,$CFG_GLPI;
   //   $id = $this->fields['id'];
   //   $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/itemtype/".time()."/".rand()."/".$id;
   //   $DB->updateOrDie(
   //      'glpi_plugin_formvalidation_itemtypes',
   //      [
   //         'guid' => md5($guid)
   //      ],
   //      [
   //         'id'  => $id
   //      ]
   //   );
   //}

   function prepareInputForAdd($input) {
       global $CFG_GLPI;
       if (!isset($input['guid'])) {
           // default value
           $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/itemtype/".time()."/".rand()."/".rand();
           $input['guid'] = md5($guid);
       }
       return $input;
   }
}
