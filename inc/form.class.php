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
class PluginFormvalidationForm extends CommonDBTM {

   static $rightname = 'config';

   static function canPurge() {
      return Config::canUpdate();
   }

    /**
     * Summary of rawSearchOptions
     * @return mixed
     */
   function rawSearchOptions() {

      $tab = [];
      $tab[] = [
              'id'                 => 'common',
              'name'               =>  __('Form', 'formvalidation')
           ];
       $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Name'),
         'datatype'           => 'itemlink',
         'searchtype'         => 'contains',
         'massiveaction'      => false,
         'itemlink_type'      => 'PluginFormvalidationForm'
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
         'id'                 => '800',
         'table'              => 'glpi_plugin_formvalidation_pages',
         'field'              => 'name',
         'linkfield'          => 'plugin_formvalidation_pages_id',
         'name'               => __('Page', 'formvalidation'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown'
       ];

       return $tab;
   }



   static function getTypeName($nb = 0) {

      if ($nb>1) {
         return __('Forms', 'formvalidation');
      }
      return __('Form', 'formvalidation');
   }


    /**
     * @since version 0.85
     *
     * @see CommonGLPI::getTabNameForItem()
     **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (static::canView()) {
         $nb = 0;
         $dbu = new DbUtils();
         switch ($item->getType()) {
            case 'PluginFormvalidationPage' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = $dbu->countElementsInTable('glpi_plugin_formvalidation_forms',
                                             ['plugin_formvalidation_pages_id' => $item->getID()]);
               }
               return self::createTabEntry(PluginFormvalidationForm::getTypeName(Session::getPluralNumber()), $nb);

            case 'PluginFormvalidationForm' :
               return PluginFormvalidationForm::getTypeName(Session::getPluralNumber());
         }
      }
      return '';
   }


    /**
     * @since version 0.85
     **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      self::showForPage($item);

      return true;
   }


    /**
     * Summary of getDataForPage
     * @param PluginFormvalidationPage $page
     * @param mixed                    $members
     * @param mixed                    $ids
     * @param mixed                    $crit
     * @param mixed                    $tree
     * @return
     */
   static function getDataForPage(PluginFormvalidationPage $page, &$members, &$ids, $crit = '', $tree = 0) {
      global $DB;

      $res = $DB->request([
                     'SELECT' => [
                        'glpi_plugin_formvalidation_forms.id AS id',
                        'glpi_plugin_formvalidation_forms.id AS linkID',
                        'glpi_plugin_formvalidation_forms.name',
                        'glpi_plugin_formvalidation_forms.plugin_formvalidation_pages_id',
                        'glpi_plugin_formvalidation_forms.css_selector',
                        'glpi_plugin_formvalidation_forms.is_active',
                        'glpi_plugin_formvalidation_forms.is_createitem',
                        'glpi_plugin_formvalidation_forms.use_for_massiveaction',
                        'glpi_plugin_formvalidation_forms.name'
                     ],
                     'DISTINCT' => true,
                     'FROM'            => 'glpi_plugin_formvalidation_forms',
                     'WHERE'           => [
                        'glpi_plugin_formvalidation_forms.plugin_formvalidation_pages_id' => $page->getID()
                     ],
                     'ORDER'           => 'glpi_plugin_formvalidation_forms.id'
         ]);

      foreach ($res as $data) {
         // Add to display list, according to criterion
         if (empty($crit) || $data[$crit]) {
            $members[] = $data;
         }
         // Add to member list (member of sub-group are not member)
         if ($data['plugin_formvalidation_pages_id'] == $page->getID()) {
            $ids[]  = $data['id'];
         }
      }

   }

    /**
     * Show forms of a page
     *
    * @param $page  PluginFormvalidationPage object: the page
     **/
   static function showForPage(PluginFormvalidationPage $page) {
      global $DB, $CFG_GLPI;

      $config = PluginFormvalidationConfig::getInstance();
      $ID = $page->getID();
      if (!PluginFormvalidationForm::canView()
         || !$page->can($ID, READ)) {
         return;
      }

      // Have right to manage members
      $canedit = self::canUpdate();
      $rand    = mt_rand();
      $form    = new PluginFormvalidationForm();
      $crit    = Session::getSavedOption(__CLASS__, 'criterion', '');
      $tree    = Session::getSavedOption(__CLASS__, 'tree', 0);
      $used    = [];
      $ids     = [];

      // Retrieve member list
      self::getDataForPage($page, $used, $ids, $crit, $tree);

      $number = count($used);

        $start = 0;

      if ($number) {
         Session::initNavigateListItems('PluginFormvalidationForm',
                                        sprintf(__('%1$s = %2$s'),
                                        PluginFormvalidationPage::getTypeName(1), $page->getName()));

         if ($canedit) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams = ['num_displayed'    => min($number-$start,
                                                                  $_SESSION['glpilist_limit']),
                                        'container'        => 'mass'.__CLASS__.$rand];
            Html::showMassiveActions($massiveactionparams);
         }

         $massiveactionparams['ontop'] = false;
         //$this->initForm($ID, $options);
         echo TemplateRenderer::getInstance()->render('@formvalidation/show_page.html.twig', [
             'rand' => $rand,
             'action_url' => Toolbox::getItemTypeFormURL(__CLASS__),
             'typeName' => PluginFormvalidationForm::getTypeName(1),
             'start' => $start,
             'number' => $number,
             'used' => $used,
             'form' => $form,
             'glpi_csrf_token' => Session::getNewCSRFToken(),
             'v8js_loaded' => extension_loaded('v8js'),
             'session' => $_SESSION,
             'canedit' => $canedit,
             'allCheckboxth' => Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand),
             '__CLASS__' => __CLASS__,
             'Html' => new Html,
             'Session' => new Session,
             'massiveactionparams' => $massiveactionparams,
             'rand' => $rand
           ]);

         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
      } else {
         echo "<p class='center b'>".__('No item found')."</p>";
      }
   }

   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);

      $this->addStandardTab('PluginFormvalidationField', $ong, $options);

      return $ong;
   }



   function showForm ($ID, $options = ['candel'=>false]) {
      global $DB, $CFG_GLPI;
      $config = PluginFormvalidationConfig::getInstance();
      if ($ID > 0) {
         $this->check($ID, READ);
      }

      $canedit = $this->can($ID, UPDATE);
      $options['canedit'] = $canedit;

      $this->initForm($ID, $options);

      $this->showFormHeader($options);


      echo TemplateRenderer::getInstance()->render('@formvalidation/form.html.twig', [
         'data' => $this->fields,
         'v8js' => extension_loaded('v8js'),
         'node' => file_exists(isset($config->fields['js_path']) ? $config->fields['js_path'] : '')
      ]);
      $this->showFormButtons($options );

   }


    /**
     * Actions done after the PURGE of the item in the database
     *
     * @return
     **/
   function post_purgeItem() {
      global $DB;

      // as it is purged, then need to purge the associated fields
      // get list of fields to purge them
      $fld = new PluginFormvalidationField;
      $res = $DB->request($fld->getTable(), ['plugin_formvalidation_forms_id' => $this->getID()]);
      foreach ($res as $row) {
         $fld->delete($row, true);
      }
   }
   function prepareInputForAdd($input){
      global $CFG_GLPI;
      $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/form/".time()."/".rand()."/";
      $input['guid'] = md5($guid);

      return $input;
   }


   /**
    * Summary of processMassiveActionsForOneItemtype
    * @param MassiveAction $ma
    * @param CommonDBTM $item
    * @param array $ids
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      global $CFG_GLPI;

      $json = [];
      $page = new PluginFormvalidationPage();
      $form = new self();
      $name = '';
      $pages = [];
      switch ($ma->getAction()) {
         case 'exportForm':
            // ids are forms, but we need the page infos for each form
            // must search for all pages in the form ids
            foreach ($ids as $id) {
               $form->getFromDB($id);
               $pages_id = $form->fields['plugin_formvalidation_pages_id'];
               if (!isset($pages[$pages_id])) {
                  $page->getFromDB($pages_id);
                  $pages[$pages_id] = $page->fields;
                  $pages[$pages_id]['itemtype'] = $page->getItemtypes();
               }

               $pages[$pages_id]['form'][$form->fields['guid']] = $form->fields;

               $fields = new PluginFormvalidationField();
               $f = $fields->find(['plugin_formvalidation_forms_id' => $id]);
               $pages[$pages_id]['form'][$form->fields['guid']]['fields'] = $f;
            }

            // build name for file
            foreach ($pages as $page_item) {
               $name .= "-" . $page_item['id'] . "_form";
               foreach ($page_item['form'] as $form_item) {
                  $name .= "-" . $form_item['id'];
               }
               array_push($json, $page_item);
               $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
            }
            $json = json_encode($json);
            $filename = "export_page$name.json";
            $export = GLPI_TMP_DIR."/$filename";
            $fichier = fopen($export, 'w+');
            fwrite($fichier, $json);
            fclose($fichier);
      }
      $ma->setRedirect($CFG_GLPI['root_doc']."/plugins/formvalidation/front/formvalidation.backup.php?action=download&filename=$filename&itemtype=".$item->getType());
   }

}

