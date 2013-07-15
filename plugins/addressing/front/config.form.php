<?php
/*
 * @version $Id: config.form.php 133 2011-11-08 11:29:51Z remi $
 -------------------------------------------------------------------------
 Addressing plugin for GLPI
 Copyright (C) 2003-2011 by the addressing Development Team.

 https://forge.indepnet.net/projects/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 Addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Addressing is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Addressing. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '../../..');
   include (GLPI_ROOT . "/inc/includes.php");
}

$plugin = new Plugin();
if ($plugin->isActivated("addressing")) {
   $PluginAddressingConfig = new PluginAddressingConfig();

   Session::checkRight("config", "w");

   if (isset($_POST["update"])) {
      $PluginAddressingConfig->update($_POST);
      Html::back();

   } else {
      Html::header($LANG['plugin_addressing']['title'][1], '', "plugins", "addressing");
      $PluginAddressingConfig->showForm();
      Html::footer();
   }

} else {
   Html::header($LANG["common"][12], '', "config", "plugins");
   echo "<div class='center'><br><br>".
         "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt='warning'><br><br>";
   echo "<b>Please activate the plugin</b></div>";
   Html::footer();
}
?>