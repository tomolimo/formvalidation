<?php

include ("../../../inc/includes.php");
// No autoload when plugin is not activated
//require_once('../inc/config.class.php');

$config = new PluginFormvalidationConfig();
if (isset($_REQUEST["update"])) {
   $config->check($_REQUEST['id'], UPDATE);
   $config->update($_REQUEST);

   Html::back();
}
Html::redirect($CFG_GLPI["root_doc"]."/front/config.form.php?forcetab=".
             urlencode('PluginFormvalidationConfig$1'));
