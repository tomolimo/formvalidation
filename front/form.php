<?php
include ("../../../inc/includes.php");


Html::header(__('Form Validations', 'formvalidation'), $_SERVER['PHP_SELF'], "config", "PluginFormvalidationMenu", "formvalidationform");

if (Session::haveRight('config', READ) || Session::haveRight("config", UPDATE)) {

   Search::show('PluginFormvalidationForm');

} else {
    Html::displayRightError();
}
Html::footer();

