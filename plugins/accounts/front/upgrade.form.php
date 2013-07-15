<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Accounts plugin for GLPI
 Copyright (C) 2003-2011 by the accounts Development Team.

 https://forge.indepnet.net/projects/accounts
 -------------------------------------------------------------------------

 LICENSE

 This file is part of accounts.

 accounts is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 accounts is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with accounts. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	define('GLPI_ROOT', '../../..');
	include (GLPI_ROOT . "/inc/includes.php");
}

if (!isset($_GET["id"])) $_GET["id"] = "";

$plugin = new Plugin();
if ($plugin->isActivated("accounts")) {
   
   Session::checkRight("config","w");
   $PluginAccountsHash=new PluginAccountsHash();
   $PluginAccountsHash->getFromDB(1);
   $hash=$PluginAccountsHash->fields["hash"];

   $update=0;
   if (countElementsInTable("glpi_plugin_accounts_accounts")>0) {
     $update=1;
   }

   if (empty($hash)) {
      
      if ($plugin->isActivated("environment"))
         Html::header($LANG['plugin_accounts']['title'][1],'',"plugins","environment","accounts");
      else
         Html::header($LANG['plugin_accounts']['title'][1],'',"plugins","accounts");
      
      if ($_SESSION['glpiactive_entity'] == 0) {
         if ($update==1) {
            echo "<div class='center b'>".$LANG['plugin_accounts']['upgrade'][0]."</div><br><br>";
            echo "<div class='center b'>".$LANG['plugin_accounts']['upgrade'][1]."</div><br><br>";
            $options = array("update" => true, "upgrade" => 1);
            $PluginAccountsHash->showForm(1,$options);
         }
      } else {
         echo "<div class='center red'>Go to Root Entity</div>";
      }
      Html::footer();

   }
} else {
   Html::header($LANG["common"][12],'',"config","plugins");
   echo "<div align='center'><br><br>";
   echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>Please activate the plugin</b></div>";
   Html::footer();
}

?>