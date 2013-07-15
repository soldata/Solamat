<?php
/*
 * @version $Id: resource.php 480 2012-11-09 tsmr $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

//central or helpdesk access
if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
	Html::header($LANG['plugin_resources']['title'][1],'',"plugins","resources");
} else {
	Html::helpHeader($LANG['plugin_resources']['title'][1]);
}

$resource=new PluginResourcesResource();
if ($resource->canView() || Session::haveRight("config","w")) {
	
		if (plugin_resources_haveRight("all","w")) {

			//Have right to see all resources
			//Have not right to see all resources
         echo "<div align='center'><script type='text/javascript'>";
         echo "cleanhide('modal_resource_content');";
         echo "var account_window=new Ext.Window({
            layout:'fit',
            width:800,
            height:400,
            closeAction:'hide',
            modal: true,
            autoScroll: true,
            title: \"".$LANG['plugin_resources']['setup'][4]."\",
            autoLoad: '".$CFG_GLPI['root_doc']."/plugins/resources/ajax/resourcetree.php'
         });";
         echo "</script>";

         echo "<a onclick='account_window.show();' href='#modal_resource_content' title='".
                $LANG['plugin_resources']['setup'][4]."'>".
                $LANG['plugin_resources']['setup'][4]."</a>";
         echo "</div>";

		}
		
		Search::show("PluginResourcesResource",$_GET);

} else {
	Html::displayRightError();
}

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
	Html::footer();
} else {
	Html::helpFooter();
}

?>