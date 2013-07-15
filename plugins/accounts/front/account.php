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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

$plugin=new plugin;

if ($_SESSION['glpiactiveprofile']['interface'] == 'central'){
   if ($plugin->isActivated("environment"))
      Html::header($LANG['plugin_accounts']['title'][1],'',"plugins","environment","accounts");
   else
      Html::header($LANG['plugin_accounts']['title'][1],'',"plugins","accounts");
} else {
   Html::helpHeader($LANG['plugin_accounts']['title'][1]);
}

$account=new PluginAccountsAccount();
$account->checkGlobal("r");

if ($account->canView()) {

	if (plugin_accounts_haveRight("all_users","r")) {

      echo "<div align='center'><script type='text/javascript'>";
      echo "cleanhide('modal_account_content');";
      echo "var account_window=new Ext.Window({
         layout:'fit',
         width:800,
         height:400,
         closeAction:'hide',
         modal: true,
         autoScroll: true,
         title: \"".$LANG['plugin_accounts']['setup'][3]."\",
         autoLoad: '".$CFG_GLPI['root_doc']."/plugins/accounts/ajax/accounttree.php'
      });";
      echo "</script>";

      echo "<a onclick='account_window.show();' href='#modal_account_content' title='".
             $LANG['plugin_accounts']['setup'][3]."'>".
             $LANG['plugin_accounts']['setup'][3]."</a>";
      echo "</div>";

	}
	
	Search::show("PluginAccountsAccount");

} else {
	Html::displayRightError();
}

Html::footer();

?>