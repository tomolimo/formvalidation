<?php

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
class PluginFormvalidationPage extends CommonDBTM {

   static $rightname = 'config';

   static function canPurge(){
      return Config::canUpdate();
   }

    /**
     * Summary of rawSearchOptions
     * @return mixed
     */
   function rawSearchOptions() {
      // global $LANG;

      $tab = [];     

      $tab[] = [
            'id'                 => 'common',
            'name'               => __('Page', 'formvalidation')
         ];
      $tab[] = [
        'id'                 => '1',
        'table'              => $this->getTable(),
        'field'              => 'name',
        'name'               => __('Name'),
        'datatype'           => 'itemlink',
        'searchtype'         => 'contains',
        'massiveaction'      => false,
        'itemlink_type'      => 'PluginFormvalidationPage'
     ];

      $tab[] = [
         'id'                 => '803',
         'table'              => 'glpi_plugin_formvalidation_itemtypes',
         'field'              => 'name',
         'linkfield'          => 'itemtypes_id',
         'name'               => __('Associated item type'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown'
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
         'id'                 => '80',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __('Entity'),
         'massiveaction'      => false,
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '86',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __('Child entities'),
         'datatype'           => 'bool'
      ];

      return $tab;
   }


   static function getTypeName($nb = 0) {
      global $LANG;

      if ($nb>1) {
         return __('Pages', 'formvalidation');
      }
      return __('Page', 'formvalidation');
   }

   function defineTabs($options = []) {

      //        $ong = array('empty' => $this->getTypeName(1));
      $ong = [];
      $this->addDefaultFormTab($ong);
      //$this->addStandardTab(__CLASS__, $ong, $options);

      $this->addStandardTab('PluginFormvalidationForm', $ong, $options);
      //$this->addStandardTab('PluginProcessmakerProcess_Profile', $ong, $options);

      return $ong;
   }

   function showForm ($ID, $options = ['candel'=>false]) {
      //global $DB;

      if ($ID > 0) {
         $this->check($ID, READ);
      }

      $canedit = $this->can($ID, UPDATE);
      $options['canedit'] = $canedit;

      $this->initForm($ID, $options);

      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Name")."&nbsp;:</td><td>";
      //Html::autocompletionTextField($this, "name");
      echo "<input type='text' size='50' maxlength=250 name='name' ".
                " value=\"".htmlentities($this->fields["name"], ENT_QUOTES)."\">";
      echo "</td>";
      echo "<td rowspan='3' class='middle right'>".__("Comments")."&nbsp;:</td>";
      echo "<td class='center middle' rowspan='3'><textarea cols='45' rows='5' name='comment' >".
      htmlentities($this->fields["comment"], ENT_QUOTES)."</textarea></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td >".__("Active")."&nbsp;:</td><td>";
      Html::showCheckbox(['name'           => 'is_active',
                                  'checked'        => $this->fields["is_active"]
                                  ]);
      echo "</td></tr>";

      if (version_compare(GLPI_VERSION, '9.1', 'lt')) {
         echo "<tr class='tab_bg_1'>";
         echo "<td >".__("Child entities")."&nbsp;:</td><td>";
         Html::showCheckbox(['name'           => 'is_recursive',
                                      'checked'        => $this->fields["is_recursive"]
                                      ]);
         echo "</td></tr>";
      }

      echo "<tr>";
      echo "<td>".__("Associated item type")." : </td>";
      echo "<td>";
      if ($ID > 0) {
         echo Dropdown::getDropdownName('glpi_plugin_formvalidation_itemtypes', $this->fields["itemtypes_id"]);
      } else {
         Dropdown::show('PluginFormvalidationItemtype', [ 'name' => 'itemtypes_id' ]); //, array( 'name' => 'name')
      }
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options );
      //$this->addDivForTabs();

   }

    /**
     * Actions done after the PURGE of the item in the database
     *
     * @return nothing
     **/
   function post_purgeItem() {
      global $DB;
      // as it is purged, then need to purge the associated forms
      // get list of form to purge them
      $frm = new PluginFormvalidationForm;
      $query = "SELECT * FROM ".$frm->getTable()." WHERE pages_id=".$this->getID();
      foreach ($DB->request($query) as $frmkey => $row) {
         $frm->delete($row, 1);
      }

   }


}

