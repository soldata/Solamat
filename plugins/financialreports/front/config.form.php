<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Financialreports plugin for GLPI
 Copyright (C) 2003-2011 by the Financialreports Development Team.

 https://forge.indepnet.net/projects/financialreports
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Financialreports.

 Financialreports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Financialreports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Financialreports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	define('GLPI_ROOT', '../../..');
	include (GLPI_ROOT . "/inc/includes.php");
}

$plugin = new Plugin();
if ($plugin->isActivated("financialreports")) {

   Session::checkRight("config","w");

   $PluginFinancialreportsParameter=new PluginFinancialreportsParameter();
   $PluginFinancialreportsConfig=new PluginFinancialreportsConfig();

   if (isset($_POST["add_state"])) {

      $PluginFinancialreportsConfig->add($_POST);
      Html::back();

   } else if (isset($_POST["delete_state"])) {

      foreach ($_POST["item"] as $key => $val) {
         if ($val==1) {
            $PluginFinancialreportsConfig->delete($_POST);
         }
      }
      Html::back();

   } else if (isset($_POST["update_parameters"])) {

      Session::checkRight("config","w");
      $PluginFinancialreportsParameter->update($_POST);
      Html::back();

   } else {

      Html::header($LANG['common'][12],'',"config","plugins");
      $PluginFinancialreportsParameter->showForm();

      $PluginFinancialreportsConfig->showForm();

      Html::footer();
   }

} else {
   Html::header($LANG["common"][12],'',"config","plugins");
   echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>Please activate the plugin</b></div>";
   Html::footer();
}

?>