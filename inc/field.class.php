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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * process short summary.
 *
 * process description.
 *
 * @version 1.0
 * @author MoronO
 */
class PluginFormvalidationField extends CommonDBTM {

   /**
    * Summary of $rightname
    * @var string is the right name for this class
    */
   static $rightname = 'config';

   static function canPurge() {
      return Config::canUpdate();
   }

   /**
    * Summary of getTypeName
    * @param mixed $nb plural
    * @return string translation
    */
   static function getTypeName($nb = 0) {

      if ($nb>1) {
         return __('Fields', 'formvalidation');
      }
        return __('Field', 'formvalidation');
   }

   /**
     * Summary of rawSearchOptions
    * @return array of search options
    */
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
             'id'                 => 'common',
             'name'               => __('Field', 'formvalidation')
          ];
      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'searchtype'         => 'contains',
         'massiveaction'      => false,
         'itemlink_type'      => 'PluginFormvalidationField'
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'is_active',
         'name'               => __('Active'),
         'massiveaction'      => true,
         'datatype'           => 'bool'
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __('Comments'),
         'massiveaction'      => true,
         'datatype'           => 'text'
      ];

      $tab[] = [
         'id'                 => '19',
         'table'              => $this->getTable(),
         'field'              => 'date_mod',
         'name'               => __('Last update'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '801',
         'table'              => 'glpi_plugin_formvalidation_forms',
         'field'              => 'name',
         'linkfield'          => 'plugin_formvalidation_forms_id',
         'name'               => _n('Form', 'Forms', 1, 'formvalidation'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '802',
         'table'              => $this->getTable(),
         'field'              => 'css_selector_value',
         'name'               => __('Value CSS selector', 'formvalidation'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown'
      ];

      return $tab;
   }


   /**
    * Summary of getTabNameForItem
    * @param CommonGLPI $item
    * @param mixed      $withtemplate
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (static::canView()) {
         $nb = 0;
         $dbu = new DbUtils();
         switch ($item->getType()) {
            case 'PluginFormvalidationForm' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = $dbu->countElementsInTable('glpi_plugin_formvalidation_fields', ['forms_id' => $item->getID()]);
               }
               return self::createTabEntry(PluginFormvalidationField::getTypeName(Session::getPluralNumber()), $nb);

            case 'PluginFormvalidationField' :
               return PluginFormvalidationField::getTypeName(Session::getPluralNumber());
         }
      }
      return '';
   }

    /**
     * @since version 0.85
     **/
   /**
    * Summary of displayTabContentForItem
    * @param CommonGLPI $item
    * @param mixed      $tabnum
    * @param mixed      $withtemplate
    * @return boolean
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      self::showForForm($item);

      return true;
   }


   /**
    * Summary of getDataForForm
    * @param PluginFormvalidationForm $form    the form to get data
    * @param mixed                    $members not used?
    * @param mixed                    $ids
    * @param mixed                    $crit
    * @param mixed                    $tree
    * @return
    */
   static function getDataForForm(PluginFormvalidationForm $form, &$members, &$ids, $crit = '', $tree = 0) {
      global $DB;

      $res = $DB->request([
                     'SELECT'   => [
                        'glpi_plugin_formvalidation_fields.id',
                        'glpi_plugin_formvalidation_fields.id AS linkID',
                        'glpi_plugin_formvalidation_fields.name',
                        'glpi_plugin_formvalidation_fields.css_selector_value',
                        'glpi_plugin_formvalidation_fields.formula',
                        'glpi_plugin_formvalidation_fields.is_active',
                        'glpi_plugin_formvalidation_fields.show_mandatory',
                        'glpi_plugin_formvalidation_fields.show_mandatory_if',
                        'glpi_plugin_formvalidation_fields.plugin_formvalidation_forms_id'
                     ],
                     'DISTINCT' => true,
                     'FROM'     => 'glpi_plugin_formvalidation_fields',
                     'WHERE'    => [
                        'glpi_plugin_formvalidation_fields.plugin_formvalidation_forms_id' => $form->getID()
                     ],
                     'ORDER'    => 'glpi_plugin_formvalidation_fields.id'
         ]);
      foreach ($res as $data) {
         // Add to display list, according to criterion
         if (empty($crit) || $data[$crit]) {
            $members[] = $data;
         }
         // Add to member list (member of sub-group are not member)
         if ($data['plugin_formvalidation_forms_id'] == $form->getID()) {
            $ids[]  = $data['id'];
         }
      }

   }

   /**
    * Show forms of a page
    *
    * @param $form  PluginFormvalidationForm object: the page
    **/
   /**
    * Summary of showForForm
    * @param PluginFormvalidationForm $form
    * @return
    */
   static function showForForm(PluginFormvalidationForm $form) {
      global $DB, $CFG_GLPI;

      $ID = $form->getID();
      if (!PluginFormvalidationField::canView()
          || !$form->can($ID, READ)) {
         return;
      }

      // Have right to manage members
      $canedit = self::canUpdate();
      $rand    = mt_rand();
      $field    = new PluginFormvalidationField();
      $used    = [];
      $ids     = [];

      // Retrieve member list
      self::getDataForForm($form, $used, $ids);

      $number = count($used);

      $start = 0;

      // Display results
      if ($number) {
         echo TemplateRenderer::getInstance()->render('@formvalidation/fields_form.html.twig', [
              'rand' => $rand,
              'action_url' => Toolbox::getItemTypeFormURL(__CLASS__),
              'typeName' => PluginFormvalidationField::getTypeName(1).__(' / CSS selector', 'formvalidation'),
              'start' => $start,
              'number' => $number,
              'used' => $used,
              'field' => $field,
              'glpi_csrf_token' => Session::getNewCSRFToken()
            ]);

      } else {
         echo "<p class='center b'>".__('No item found')."</p>";
      }
   }

   /**
    * Summary of defineTabs
    * @param mixed $options
    * @return array
    */
   function defineTabs($options = []) {

        $ong = [];
        $this->addDefaultFormTab($ong);

        return $ong;
   }

   /**
    * Summary of showForm
    * @param mixed $ID
    * @param mixed $options
    */
   function showForm ($ID, $options = ['candel'=>false]) {
      global $DB, $CFG_GLPI;

      if ($ID > 0) {
         $this->check($ID, READ);
      }

      $canedit = $this->can($ID, UPDATE);
      $options['canedit'] = $canedit;

      $this->initForm($ID, $options);



      $this->showFormHeader($options);

      echo TemplateRenderer::getInstance()->render('@formvalidation/field.html.twig', [
              'data' => $this->fields
            ]);

      $this->showFormButtons($options );

   }
   /**
    * Summary of prepareInputForUpdate
    * @param mixed $input
    * @return mixed
    */
   function prepareInputForUpdate($input) {
      if (isset( $input['css_selector_value'] )) {
         $input['css_selector_value'] = html_entity_decode( $input['css_selector_value']);
      }
      if (isset( $input['css_selector_altvalue'] )) {
         $input['css_selector_altvalue'] = html_entity_decode( $input['css_selector_altvalue']);
      }
      if (isset( $input['css_selector_errorsign'] )) {
         $input['css_selector_errorsign'] = html_entity_decode( $input['css_selector_errorsign']);
      }
      if (isset( $input['css_selector_mandatorysign'] )) {
         $input['css_selector_mandatorysign'] = html_entity_decode( $input['css_selector_mandatorysign']);
      }
      return $input;
   }

   function prepareInputForAdd($input){
      global $CFG_GLPI;
      $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/fields/".time()."/".rand()."/";
      $input['guid'] = md5($guid);

      return $input;
   }
}

