<?php
/*
 * @version $Id: setup.php 480 2012-11-09 tynet $
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

// Init the hooks of the plugins -Needed
function plugin_init_resources() {
	global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
	
	$PLUGIN_HOOKS['csrf_compliant']['resources'] = true;
	$PLUGIN_HOOKS['change_profile']['resources'] = array('PluginResourcesProfile','changeProfile');
	$PLUGIN_HOOKS['assign_to_ticket']['resources'] = true;

	if (Session::getLoginUserID()) {
      
      $noupdate = false;
      if (isset ($_SESSION['glpiactiveprofile']['interface']) 
            && $_SESSION['glpiactiveprofile']['interface'] != 'central') {
            $noupdate = true;
      }
            
      Plugin::registerClass('PluginResourcesResource', array(
         'linkuser_types' => true,
         'document_types' => true,	
         'ticket_types'         => true,
         'helpdesk_visible_types' => true,
         'notificationtemplates_types' => true,
         'unicity_types' => true,
         'massiveaction_nodelete_types' => $noupdate,
         'massiveaction_noupdate_types' => $noupdate
      ));
      
      Plugin::registerClass('PluginResourcesDirectory', array(
         'massiveaction_nodelete_types' => true,
         'massiveaction_noupdate_types' => true
      ));

      Plugin::registerClass('PluginResourcesRecap', array(
         'massiveaction_nodelete_types' => true,
         'massiveaction_noupdate_types' => true
      ));
      
      Plugin::registerClass('PluginResourcesTaskPlanning', array(
         'planning_types' => true
      ));
      
      Plugin::registerClass('PluginResourcesRuleChecklistCollection', array(
         'rulecollections_types' => true
         
      ));
      
      Plugin::registerClass('PluginResourcesRuleContracttypeCollection', array(
         'rulecollections_types' => true
         
      ));
      
      Plugin::registerClass('PluginResourcesProfile',
                         array('addtabon' => 'Profile'));

      Plugin::registerClass('PluginResourcesEmployment', array(
         'massiveaction_nodelete_types' => true));
      
		
		if (class_exists('PluginPositionsPosition')) {
         PluginPositionsPosition::registerType('PluginResourcesResource');
      }
   
		if ((plugin_resources_haveRight("resources","r") || plugin_resources_haveRight("employer","w"))) {
			$PLUGIN_HOOKS['menu_entry']['resources'] = 'front/menu.php';
			$PLUGIN_HOOKS['helpdesk_menu_entry']['resources'] = '/front/menu.php';
         $PLUGIN_HOOKS['submenu_entry']['resources']['search'] = 'front/resource.php';
         $PLUGIN_HOOKS['redirect_page']['resources'] = "front/resource.form.php";
         $PLUGIN_HOOKS['submenu_entry']['resources']["<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/directory18.png' title='".$LANG['plugin_resources'][66]."' alt='".$LANG['plugin_resources'][66]."'>"] = 'front/directory.php';
      }
      if (plugin_resources_haveRight("resting","w")) {
         $PLUGIN_HOOKS['submenu_entry']['resources']["<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/restinglist18.png' title='".$LANG['plugin_resources']['helpdesk'][8]."' alt='".$LANG['plugin_resources']['helpdesk'][8]."'>"] = 'front/resourceresting.php';
      }

      if (plugin_resources_haveRight("holiday","w")) {
         $PLUGIN_HOOKS['submenu_entry']['resources']["<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/holidaylist18.png' title='".$LANG['plugin_resources']['helpdesk'][23]."' alt='".$LANG['plugin_resources']['helpdesk'][23]."'>"] = 'front/resourceholiday.php';
      }

      if (plugin_resources_haveRight("employment","r")) {
         $PLUGIN_HOOKS['submenu_entry']['resources']["<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/employmentlist18.png' title='".$LANG['plugin_resources']['helpdesk'][27]."' alt='".$LANG['plugin_resources']['helpdesk'][27]."'>"] = 'front/employment.php';
         $PLUGIN_HOOKS['submenu_entry']['resources']["<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/recap18.png' title='".$LANG['plugin_resources']['helpdesk'][29]."' alt='".$LANG['plugin_resources']['helpdesk'][29]."'>"] = 'front/recap.php';
      }

      if (plugin_resources_haveRight("budget","r")) {
         $PLUGIN_HOOKS['submenu_entry']['resources']["<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/budgetlist18.png' title='".$LANG['plugin_resources']['helpdesk'][28]."' alt='".$LANG['plugin_resources']['helpdesk'][28]."'>"] = 'front/budget.php';
      }

      if (plugin_resources_haveRight("resources","w")) {
         $PLUGIN_HOOKS['submenu_entry']['resources']['add'] = 'front/wizard.form.php';
         $PLUGIN_HOOKS['submenu_entry']['resources']['template'] = 'front/setup.templates.php?add=0';
         if (plugin_resources_haveRight("task","r"))
            $PLUGIN_HOOKS['submenu_entry']['resources']["<img  src='".$CFG_GLPI["root_doc"]."/pics/menu_showall.png' title='".$LANG['plugin_resources'][42]."' alt='".$LANG['plugin_resources'][42]."'>"] = 'front/task.php';
         if (plugin_resources_haveRight("checklist","r"))
            $PLUGIN_HOOKS['submenu_entry']['resources']["<img  src='".$CFG_GLPI["root_doc"]."/pics/reservation-3.png' title='".$LANG['plugin_resources']['title'][3]."' alt='".$LANG['plugin_resources']['title'][3]."'>"] = 'front/checklistconfig.php'; 
         
         if (plugin_resources_haveRight("checklist","r")) {
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['checklist']['title'] = $LANG['plugin_resources']['title'][3];
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['checklist']['page']  = '/plugins/resources/front/checklistconfig.php';
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['checklist']['links']['search'] = '/plugins/resources/front/checklistconfig.php';
         }
         
         if (plugin_resources_haveRight("checklist","w")) {
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['checklist']['links']['add']    = '/plugins/resources/front/checklistconfig.form.php?new=1';
         }

         if (plugin_resources_haveRight("employment","r")) {
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['employment']['title'] = $LANG['plugin_resources']['title'][6];
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['employment']['page']  = '/plugins/resources/front/employment.php';
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['employment']['links']['search'] = '/plugins/resources/front/employment.php';
         }

         if (plugin_resources_haveRight("employment","w")) {
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['employment']['links']['add']    = '/plugins/resources/front/employment.form.php';
         }

         if (plugin_resources_haveRight("budget","r")) {
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['budget']['title'] = $LANG['plugin_resources']['title'][8];
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['budget']['page']  = '/plugins/resources/front/budget.php';
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['budget']['links']['search'] = '/plugins/resources/front/budget.php';
         }

         if (plugin_resources_haveRight("budget","w")) {
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['budget']['links']['add']    = '/plugins/resources/front/budget.form.php';
         }
         if (Session::haveRight("config","w")) {
            $PLUGIN_HOOKS['submenu_entry']['resources']['options']['checklist']['links']['config'] = '/plugins/resources/front/config.form.php';
         }
         
         $PLUGIN_HOOKS['use_massive_action']['resources']=1;
		}
		
		// Add specific files to add to the header : javascript or css
		$PLUGIN_HOOKS['add_javascript']['resources']="resources.js";
		$PLUGIN_HOOKS['add_css']['resources']="resources.css";
		
		$PLUGIN_HOOKS['plugin_positions']['PluginResourcesResource']='plugin_resources_positions_pics';
		
		//TODO : Check
		$PLUGIN_HOOKS['plugin_pdf']['PluginResourcesResource']='PluginResourcesResourcePDF';
		
		//Clean Plugin on Profile delete
      if (class_exists('PluginResourcesResource_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['pre_item_purge']['resources'] = array('Profile'=>array('PluginResourcesProfile', 'purgeProfiles'));
         $PLUGIN_HOOKS['plugin_datainjection_populate']['resources'] = 'plugin_datainjection_populate_resources';
      }
      
      //planning action
      $PLUGIN_HOOKS['planning_populate']['resources']=array('PluginResourcesTaskPlanning','populatePlanning');
      $PLUGIN_HOOKS['display_planning']['resources']=array('PluginResourcesTaskPlanning','displayPlanningItem');
      $PLUGIN_HOOKS['migratetypes']['resources'] = 'plugin_datainjection_migratetypes_resources';
      // Config page
      if (Session::haveRight("config","w")) {
         $PLUGIN_HOOKS['submenu_entry']['resources']['config'] = 'front/config.form.php';
         $PLUGIN_HOOKS['config_page']['resources'] = 'front/config.form.php';
      }
	}
	// End init, when all types are registered
   $PLUGIN_HOOKS['post_init']['resources'] = 'plugin_resources_postinit';
}

// Get the name and the version of the plugin - Needed

function plugin_version_resources() {
	global $LANG;

	return array (
		'name' => $LANG['plugin_resources']['title'][1],
		'version' => '1.9.1',
		'license' => 'GPLv2+',
		'author'=>'Xavier Caillaud, Faustine Crespel',
		'homepage'=>'https://forge.indepnet.net/projects/resources',
		'minGlpiVersion' => '0.83.3',// For compatibility / no install in version < 0.83.3
	);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_resources_check_prerequisites() {
	if (version_compare(GLPI_VERSION,'0.83.3','lt') || version_compare(GLPI_VERSION,'0.84','ge')) {
      echo "This plugin requires GLPI >= 0.83.3";
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_resources_check_config() {
	return true;
}

function plugin_resources_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_resources_profile"][$module]) 
            && in_array($_SESSION["glpi_plugin_resources_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

function plugin_datainjection_migratetypes_resources($types) {
   $types[4300] = 'PluginResourcesResource';
   return $types;
}

?>