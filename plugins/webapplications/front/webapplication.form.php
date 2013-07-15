<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2003-2011 by the Webapplications Development Team.

 https://forge.indepnet.net/projects/webapplications
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Webapplications.

 Webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$PluginWebapplications      = new PluginWebapplicationsWebapplication();
$PluginWebapplications_Item = new PluginWebapplicationsWebapplication_Item();

if (isset($_POST["add"])) {
   $PluginWebapplications->check(-1,'w',$_POST);
   $newID = $PluginWebapplications->add($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $PluginWebapplications->check($_POST['id'],'w');
   $PluginWebapplications->delete($_POST);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginWebapplicationsWebapplication'));

} else if (isset($_POST["restore"])) {
   $PluginWebapplications->check($_POST['id'],'w');
   $PluginWebapplications->restore($_POST);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginWebapplicationsWebapplication'));

} else if (isset($_POST["purge"])) {
   $PluginWebapplications->check($_POST['id'],'w');
   $PluginWebapplications->delete($_POST,1);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginWebapplicationsWebapplication'));

} else if (isset($_POST["update"])) {
   $PluginWebapplications->check($_POST['id'],'w');
   $PluginWebapplications->update($_POST);
   Html::back();

} else if (isset($_POST["additem"])) {
   if (!empty($_POST['itemtype'])) {
      $PluginWebapplications_Item->check(-1,'w',$_POST);
      $PluginWebapplications_Item->addItem($_POST["plugin_webapplications_webapplications_id"],
                                           $_POST['items_id'], $_POST['itemtype']);
   }
   Html::back();

} else if (isset($_POST["deleteitem"])) {
   foreach ($_POST["item"] as $key => $val) {
      $input = array('id' => $key);
      if ($val == 1) {
         $PluginWebapplications_Item->check($key,'w');
         $PluginWebapplications_Item->delete($input);
      }
   }
   Html::back();

//unlink webapplications to items of glpi from the items form
} else if (isset($_POST["deletewebapplications"])) {
   $input = array('id' => $_POST["id"]);
   $PluginWebapplications_Item->check($_POST["id"],'w');
   $PluginWebapplications_Item->delete($input);
   Html::back();

} else {
   $PluginWebapplications->checkGlobal("r");

   //check environment meta-plugin installtion for change header
   $plugin = new Plugin();
   if ($plugin->isActivated("environment")) {
      Html::header($LANG['plugin_webapplications'][4],'',"plugins","environment","webapplications");
   } else {
      Html::header($LANG['plugin_webapplications'][4],'',"plugins","webapplications");
   }
   //load webapplications form
   $PluginWebapplications->showForm($_GET["id"]);

   Html::footer();
}
?>