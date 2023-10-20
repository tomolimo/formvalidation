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
         'linkfield'          => 'pages_id',
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
                                             ['pages_id' => $item->getID()]);
               }
               return self::createTabEntry(PluginFormvalidationForm::getTypeName(Session::getPluralNumber()), $nb);

            case 'PluginFormvalidationForm' :
               //if ($_SESSION['glpishow_count_on_tabs']) {
               //   $nb = countElementsInTable('glpi_plugin_formvalidation_forms',
               //                              "`pages_id` = '".$item->getID()."'");
               //}
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
                        'glpi_plugin_formvalidation_forms.pages_id',
                        'glpi_plugin_formvalidation_forms.css_selector',
                        'glpi_plugin_formvalidation_forms.is_active',
                        'glpi_plugin_formvalidation_forms.is_createitem',
                        'glpi_plugin_formvalidation_forms.use_for_massiveaction',
                        'glpi_plugin_formvalidation_forms.name'
                     ],
                     'DISTINCT' => true,
                     'FROM'            => 'glpi_plugin_formvalidation_forms',
                     'WHERE'           => [
                        'glpi_plugin_formvalidation_forms.pages_id' => $page->getID()
                     ],
                     'ORDER'           => 'glpi_plugin_formvalidation_forms.id'
         ]);

      foreach ($res as $data) {
         // Add to display list, according to criterion
         if (empty($crit) || $data[$crit]) {
            $members[] = $data;
         }
         // Add to member list (member of sub-group are not member)
         if ($data['pages_id'] == $page->getID()) {
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

      //if ($canedit) {
      //   self::showAddUserForm($page, $ids, $entityrestrict, $crit);
      //}

      // Mini Search engine
      //echo "<table class='tab_cadre_fixe'>";
      //echo "<tr class='tab_bg_1'><th colspan='2'>".PluginFormvalidationForm::getTypeName(Session::getPluralNumber())."</th></tr>";
      //echo "<tr class='tab_bg_1'><td class='center'>";
      //echo _n('Criterion', 'Criteria', 1)."&nbsp;";
      //$crits = array('name'      => __('Name'),
      //               'is_active' => __('Active'));
      //Dropdown::showFromArray('crit', $crits,
      //                        array('value'               => $crit,
      //                              'on_change'           => 'reloadTab("start=0&criterion="+this.value)',
      //                              'display_emptychoice' => true));
      ////if ($page->haveChildren()) {
      ////   echo "</td><td class='center'>".__('Child groups');
      ////   Dropdown::showYesNo('tree', $tree, -1,
      ////                       array('on_change' => 'reloadTab("start=0&tree="+this.value)'));
      ////} else {
      //   $tree = 0;
      ////}
      //echo "</td></tr></table>";

      $number = count($used);
      //$start  = (isset($_GET['start']) ? intval($_GET['start']) : 0);
      //if ($start >= $number) {
        $start = 0;
      //}

      // Display results
      if ($number) {
         echo "<div class='spaced'>";
         //Html::printAjaxPager(sprintf(__('%1$s'), PluginFormvalidationForm::getTypeName(Session::getPluralNumber())),
         //                     $start, $number);

         Session::initNavigateListItems('PluginFormvalidationForm',
                              //TRANS : %1$s is the itemtype name,
                              //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                PluginFormvalidationPage::getTypeName(1), $page->getName()));

         if ($canedit) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams = ['num_displayed'    => min($number-$start,
                                                                  $_SESSION['glpilist_limit']),
                                        'container'        => 'mass'.__CLASS__.$rand];
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<table class='tab_cadre_fixehov'>";

         $header_begin  = "<tr>";
         $header_top    = '';
         $header_bottom = '';
         $header_end    = '';

         if ($canedit) {
            $header_begin  .= "<th width='10'>";
            $header_top    .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_bottom .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_end    .= "</th>";
         }
         $header_end .= "<th>".__('ID')."</th>";
         $header_end .= "<th>".PluginFormvalidationForm::getTypeName(1)."</th>";
         //if ($tree) {
         //   $header_end .= "<th>".PluginFormvalidationPage::getTypeName(1)."</th>";
         //}
         $header_end .= "<th>".__('CSS selector', 'formvalidation')."</th>";
         $header_end .= "<th>".__('Active')."</th>";
         $header_end .= "<th>".__('Item creation', 'formvalidation')."</th>";
         //if (extension_loaded('v8js')) {
         if (extension_loaded('v8js') || file_exists($config->fields['js_path'])) {
            $header_end .= "<th>".__('Massive actions', 'formvalidation')."</th>";
         } else {
            $header_end .= "<th>".__('Massive actions (Not available as node.js and v8js are not installed/enabled)', 'formvalidation')."</th>";
         }
         $header_end .= "</tr>";
         echo $header_begin.$header_top.$header_end;

         //$tmpgrp = new PluginFormvalidationPage();

         for ($i=$start, $j=0; ($i < $number) && ($j < $_SESSION['glpilist_limit']); $i++, $j++) {
            $data = $used[$i];
            $form->getFromDB($data["id"]);
            Session::addToNavigateListItems('PluginFormvalidationForm', $data["id"]);

            echo "\n<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["linkID"]);
               echo "</td>";
            }

            echo "<td class='center'>";
            echo $data['id'];

            echo "</td><td>".$form->getLink();

            echo "</td><td >";
            echo $data['css_selector'];
            echo "</td><td class='center'>";
            Html::showCheckbox(['id'        => 'isformactive',
                                'name'           => 'is_active',
                                'checked'        => $data["is_active"],
                                'readonly' => true
                                ]);

            echo "</td><td class='center'>";
            Html::showCheckbox(['id'        => 'isformitemcreation',
                                'name'           => 'is_createitem',
                                'checked'        => $data["is_createitem"],
                                'readonly' => true
                                ]);

            echo "</td><td class='center'>";
            Html::showCheckbox([ 'id'        => 'isformuseformassiveaction',
                                   'name'           => 'use_for_massiveaction',
                                   'checked'        => $data["use_for_massiveaction"],
                                   'readonly' => true

                                   ]);

            echo "</td></tr>";
         }
         echo $header_begin.$header_bottom.$header_end;
         echo "</table>";
         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         //Html::printAjaxPager(sprintf(__('%1$s'), PluginFormvalidationForm::getTypeName(Session::getPluralNumber())),
         //                     $start, $number);

         echo "</div>";
      } else {
         echo "<p class='center b'>".__('No item found')."</p>";
      }
   }

   function defineTabs($options = []) {

      //        $ong = array('empty' => $this->getTypeName(1));
      $ong = [];
      $this->addDefaultFormTab($ong);
      //$this->addStandardTab(__CLASS__, $ong, $options);

      $this->addStandardTab('PluginFormvalidationField', $ong, $options);
      //$this->addStandardTab('PluginProcessmakerProcess_Profile', $ong, $options);

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

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Name")."</td>";
      echo "<td><input type='text' size='50' maxlength=250 name='name' value='".htmlentities($this->fields["name"], ENT_QUOTES)."'></td>";
      echo "<td rowspan='5' class='middle'>".__("Comments")."</td>";
      echo "<td class='center middle' rowspan='5'><textarea cols='40' rows='5' name='comment' >".htmlentities($this->fields["comment"], ENT_QUOTES)."</textarea></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__("Active")."</td>";
      echo "<td>";
      Html::showCheckbox(['name'           => 'is_active',
                               'checked'        => $this->fields["is_active"]
                               ]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__("For item creation")."</td>";
      echo "<td>";
      Html::showCheckbox(['name'           => 'is_createitem',
                               'checked'        => $this->fields["is_createitem"]
                               ]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__("CSS selector", 'formvalidation')."&nbsp;:</td>";
      echo "<td><input type='text' size='50' maxlength=200 name='css_selector' value='".htmlentities($this->fields["css_selector"], ENT_QUOTES)."'></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__("Formula (left empty to default to <b><i>true</i></b>)", 'formvalidation')."</td>";
      echo "<td><input type='text' size='50' maxlength=1000 name='formula' value='".htmlentities($this->fields["formula"], ENT_QUOTES)."'></td>";
      echo "</tr>";

      echo "<tr>";
      $v8js = extension_loaded('v8js');
      $node = file_exists($config->fields['js_path']);
      //if ($v8js) {
      if ($v8js ||$node) {
         echo "<td >".__("Validate massive actions")."</td>";
      } else {
         echo "<td >".__("Massive actions (Will not work if node.js or v8js are not installed/enabled)")."</td>";
      }
      echo "<td>";
      Html::showCheckbox(['name'    => 'use_for_massiveaction',
                         'checked'  => $this->fields["use_for_massiveaction"],
                         'readonly' => false
                         ]);

      echo "</td></tr>";

      echo "<tr>";
      echo "</tr>";

      $this->showFormButtons($options );
      //$this->addDivForTabs();

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
      $res = $DB->request($fld->getTable(), ['forms_id' => $this->getID()]);
      foreach ($res as $row) {
         $fld->delete($row, true);
      }
   }


   /**
    * Summary of post_addItem
    */
   function post_addItem() {
      global $DB, $CFG_GLPI;

      $id = $this->fields['id'];
      $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/form/".time()."/".rand()."/".$id;
      $DB->updateOrDie(
         'glpi_plugin_formvalidation_forms',
         [
            'guid' => md5($guid)
         ],
         [
            'id'  => $id
         ]
      );
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
               $pages_id = $form->fields['pages_id'];
               if (!isset($pages[$pages_id])) {
                  $page->getFromDB($pages_id);
                  $pages[$pages_id] = $page->fields;
                  $pages[$pages_id]['itemtype'] = $page->getItemtypes();
               }

               $pages[$pages_id]['form'][$form->fields['guid']] = $form->fields;

               $fields = new PluginFormvalidationField();
               $f = $fields->find(['forms_id' => $id]);
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

