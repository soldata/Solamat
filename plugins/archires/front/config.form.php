<?php
/*
 * @version $Id: config.form.php 164 2011-11-08 11:30:52Z remi $
 -------------------------------------------------------------------------
 Archires plugin for GLPI
 Copyright (C) 2003-2011 by the archires Development Team.

 https://forge.indepnet.net/projects/archires
 -------------------------------------------------------------------------

 LICENSE

 This file is part of archires.

 Archires is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Archires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Archires. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '../../..');
   include (GLPI_ROOT . "/inc/includes.php");
}

$plugin = new Plugin();
if ($plugin->isActivated("archires")) {
   Session::checkRight("config","w");

   $PluginArchiresImageItem             = new PluginArchiresImageItem();
   $PluginArchiresNetworkInterfaceColor = new PluginArchiresNetworkInterfaceColor();
   $PluginArchiresVlanColor             = new PluginArchiresVlanColor();
   $PluginArchiresStateColor            = new PluginArchiresStateColor();

   if (isset($_POST["add"]) && isset($_POST['type'])) {
      $test = explode(";", $_POST['type']);
      if (isset($test[0])) {
         $_POST['type']     = $test[1];
         $_POST['itemtype'] = $test[0];

         if ($PluginArchiresImageItem->canCreate()) {
            $PluginArchiresImageItem->addItemImage($_POST['type'], $_POST['itemtype'],
                                                   $_POST['img']);
         }
      }
      Html::back();

   } else if (isset($_POST["delete"])) {
      Session::checkRight("config","w");
      $PluginArchiresImageItem->getFromDB($_POST["id"],-1);

      foreach ($_POST["item"] as $key => $val) {
         if ($val == 1) {
            $PluginArchiresImageItem->deleteItemImage($key);
         }
      }
      Html::back();

   } else if (isset($_POST["add_color_networkinterface"])
              && isset($_POST['networkinterfaces_id'])) {

      if ($PluginArchiresNetworkInterfaceColor->canCreate()) {
         $PluginArchiresNetworkInterfaceColor->addNetworkInterfaceColor($_POST['networkinterfaces_id'],
                                                                        $_POST['color']);
      }
      Html::back();

   } else if (isset($_POST["delete_color_networkinterface"])) {
      Session::checkRight("config","w");
      $PluginArchiresNetworkInterfaceColor->getFromDB($_POST["id"],-1);

      foreach ($_POST["item_color"] as $key => $val) {
         if ($val == 1) {
            $PluginArchiresNetworkInterfaceColor->deleteNetworkInterfaceColor($key);
         }
      }
      Html::back();

   } else if (isset($_POST["add_color_state"]) && isset($_POST['states_id'])) {
      if ($PluginArchiresStateColor->canCreate()) {
         $PluginArchiresStateColor->addStateColor($_POST['states_id'],$_POST['color']);
      }
      Html::back();

   } else if (isset($_POST["delete_color_state"])) {
      Session::checkRight("config","w");
      $PluginArchiresStateColor->getFromDB($_POST["id"],-1);

      foreach ($_POST["item_color"] as $key => $val) {
         if ($val == 1) {
            $PluginArchiresStateColor->deleteStateColor($key);
         }
      }
      Html::back();

   } else if (isset($_POST["add_color_vlan"]) && isset($_POST['vlans_id'])) {
      if ($PluginArchiresVlanColor->canCreate()) {
         $PluginArchiresVlanColor->addVlanColor($_POST['vlans_id'],$_POST['color']);
      }
      Html::back();

   } else if (isset($_POST["delete_color_vlan"])) {
      Session::checkRight("config","w");
      $PluginArchiresVlanColor->getFromDB($_POST["id"],-1);

      foreach ($_POST["item_color"] as $key => $val) {
         if ($val==1) {
            $PluginArchiresVlanColor->deleteVlanColor($key);
         }
      }
      Html::back();

   } else {
      Html::header($LANG['plugin_archires']['title'][0], '', "plugins",
                   "archires", "summary");

      $PluginArchiresImageItem->showConfigForm();

      $PluginArchiresNetworkInterfaceColor->showConfigForm(true);

      $PluginArchiresVlanColor->showConfigForm(true);

      $PluginArchiresStateColor->showConfigForm(true);

      Html::footer();
   }

} else {
   Html::header($LANG["common"][12],'',"config","plugins");
   echo "<div class='center'><br><br>";
   echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt='warning'><br><br>";
   echo "<b>Please activate the plugin</b></div>";
   Html::footer();
}
?>