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
     * Summary of getSearchOptions
     * @return mixed
     */
   function getSearchOptions() {
      // global $LANG;

      $tab = [];

      $tab['common'] = __('Page', 'formvalidation');

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Title');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['searchtype']      = 'contains';
      $tab[1]['massiveaction']   = false;
      $tab[1]['itemlink_type']   = $this->getType();

      $tab[803]['table']         = 'glpi_plugin_formvalidation_itemtypes';
      $tab[803]['field']         = 'name';
      $tab[803]['linkfield']     = 'itemtypes_id';
      $tab[803]['name']          = __('Associated item type');
      $tab[803]['massiveaction'] = false;
      $tab[803]['datatype']      = 'dropdown';

      $tab[8]['table']           = $this->getTable();
      $tab[8]['field']           = 'is_active';
      $tab[8]['name']            = __('Active');
      $tab[8]['massiveaction']   = true;
      $tab[8]['datatype']        = 'bool';

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'comment';
      $tab[4]['name']            = __('Comments');
      $tab[4]['massiveaction']   = true;
      $tab[4]['datatype']        = 'text';

      $tab[19]['table']          = $this->getTable();
      $tab[19]['field']          = 'date_mod';
      $tab[19]['name']           = __('Last update');
      $tab[19]['datatype']       = 'datetime';
      $tab[19]['massiveaction']  = false;

      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['massiveaction']  = false;
      $tab[80]['datatype']       = 'dropdown';

      $tab[86]['table']          = $this->getTable();
      $tab[86]['field']          = 'is_recursive';
      $tab[86]['name']           = __('Child entities');
      $tab[86]['datatype']       = 'bool';

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

