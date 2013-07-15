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
	die("Sorry. You can't access directly to this file");
}

class PluginFinancialreportsParameter extends CommonDBTM {
   
   function showForm() {
      global $LANG;
      
      $this->getFromDB('1');
      echo "<div align='center'><form method='post' action=\"./config.form.php\">";
      echo "<table class='tab_cadre' cellpadding='5'>";
      echo "<tr>";
      echo "<th colspan='2'>".$LANG['plugin_financialreports']['setup'][8]."</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_financialreports']['type'][1]."</td><td><input type='text' name='computers_otherserial' value='".$this->fields["computers_otherserial"]."'></td></tr>";
      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_financialreports']['type'][2]."</td><td><input type='text' name='notebooks_otherserial' value='".$this->fields["notebooks_otherserial"]."'></td></tr>";
      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_financialreports']['type'][3]."</td><td><input type='text' name='servers_otherserial' value='".$this->fields["servers_otherserial"]."'></td></tr>";
      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_financialreports']['type'][8]."</td><td><input type='text' name='monitors_otherserial' value='".$this->fields["monitors_otherserial"]."'></td></tr>";
      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_financialreports']['type'][4]."</td><td><input type='text' name='printers_otherserial' value='".$this->fields["printers_otherserial"]."'></td></tr>";
      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_financialreports']['type'][7]."</td><td><input type='text' name='peripherals_otherserial' value='".$this->fields["peripherals_otherserial"]."'></td></tr>";
      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_financialreports']['type'][5]."</td><td><input type='text' name='networkequipments_otherserial' value='".$this->fields["networkequipments_otherserial"]."'></td></tr>";
      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_financialreports']['type'][9]."</td><td><input type='text' name='phones_otherserial' value='".$this->fields["phones_otherserial"]."'></td></tr>";
      echo "<tr class='tab_bg_1'><td colspan='2' class='center'><input type='hidden' name='id' value='".$this->fields["id"]."'><input type='submit' name='update_parameters' value=\"".$LANG['buttons'][7]."\" class='submit' ></td></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }
}

?>