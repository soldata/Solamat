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

if (strpos($_SERVER['PHP_SELF'],"dropdownTypeAccounts.php")) {
	define('GLPI_ROOT', '../../..');
	$AJAX_INCLUDE=1;
	include (GLPI_ROOT."/inc/includes.php");
	header("Content-Type: text/html; charset=UTF-8");
	Html::header_nocache();
}

Session::checkCentralAccess();

// Make a select box

if (isset($_POST["plugin_accounts_accounttypes_id"])) {

	$rand=$_POST['rand'];

	$use_ajax=false;
	$restrict = "plugin_accounts_accounttypes_id='".$_POST["plugin_accounts_accounttypes_id"]."' 
	".getEntitiesRestrictRequest("AND", "glpi_plugin_accounts_accounts","",$_POST["entity_restrict"],true);
	if ($CFG_GLPI["use_ajax"] &&
		countElementsInTable('glpi_plugin_accounts_accounts',$restrict )>$CFG_GLPI["ajax_limit_count"]) {
		$use_ajax=true;
	}


	$params=array('searchText'=>'__VALUE__',
			'plugin_accounts_accounttypes_id'=>$_POST["plugin_accounts_accounttypes_id"],
			'entity_restrict'=>$_POST["entity_restrict"],
			'rand'=>$_POST['rand'],
			'myname'=>$_POST['myname'],
			'used'=>$_POST['used']
			);

	$default="<select name='".$_POST["myname"]."'><option value='0'>".Dropdown::EMPTY_VALUE."</option></select>";
	Ajax::dropdown($use_ajax,"/plugins/accounts/ajax/dropdownAccounts.php",$params,$default,$rand);

}

?>