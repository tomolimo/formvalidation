<?php

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

      echo "<tr class='tab_bg_1'>";
      echo "<td><label for='name'>".__('Item type name (examples: Project, Computer, ...)')."</label></td>";
      echo "<td><input type='text' name='name' value='".$this->fields['name']."'/></td>";
      echo "<tr class='tab_bg_1'>";
      echo "<td><label for='name'>".__('URL path part of the item type form (examples: /front/project.form.php, /front/computer.form.php, ...)', 'formvalidation')."</label></td>";
      echo "<td><input type='text' id='urlpath' name='URL_path_part' value='".$this->fields['URL_path_part']."'/></td>";
      echo "</tr'>";

      $this->showFormButtons();
      //echo '<script>';
      //echo '$(".ui-icon.ui-icon-search").on("click", function (e) {
      //       var filename = $("#urlpath").val();
      //       $.ajax({url: "http://fry09129.ar.ray.group/plugins/formvalidation/ajax/existingFile.php",async: false, type: "GET",data: { filename: filename },success: function (response, statut, error) {if(response == "1") { return true; }else{ return false; }}});
      //   });';
      //echo '</script>';

   }
   
   function post_addItem() {
      global $DB,$CFG_GLPI;
      $id = $this->fields['id'];
      $guid = $CFG_GLPI['url_base']."/plugins/formvalidation/ajax/itemtype/".time()."/".rand()."/".$id;
      $DB->updateOrDie(
         'glpi_plugin_formvalidation_itemtypes',
         [
            'guid' => md5($guid)
         ],
         [
            'id'  => $id
         ]
      );
   }
}
