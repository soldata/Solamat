<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Badges plugin for GLPI
 Copyright (C) 2003-2011 by the badges Development Team.

 https://forge.indepnet.net/projects/badges
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of badges.

 Badges is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Badges is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Badges. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_badges_install() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/badges/inc/profile.class.php");
   
   $install=false;
   $update78=false;
   $update80=false;
   
   if (!TableExists("glpi_plugin_badges") && !TableExists("glpi_plugin_badges_badgetypes")) {
      $install=true;
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/empty-1.7.0.sql");

   } else if (TableExists("glpi_plugin_badges_users") && !TableExists("glpi_plugin_badges_default")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/update-1.4.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/update-1.5.0.sql");
      plugin_badges_configure15();
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/update-1.6.0.sql");

   } else if (TableExists("glpi_plugin_badges_profiles") && FieldExists("glpi_plugin_badges_profiles","interface")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/update-1.5.0.sql");
      plugin_badges_configure15();
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/update-1.6.0.sql");

   } else if (TableExists("glpi_plugin_badges") && !FieldExists("glpi_plugin_badges","date_mod")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/update-1.6.0.sql");

   } else if (!TableExists("glpi_plugin_badges_badgetypes")) {
      
      $update78=true;
      $update80=true;
      $DB->runFile(GLPI_ROOT ."/plugins/badges/sql/update-1.6.0.sql");
      
   }
   
   if ($install || $update78) {
      
      //Do One time on 0.78
      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginBadgesBadge' AND `name` = 'Alert Badges'";
      $result = $DB->query($query_id) or die ($DB->error());
      $itemtype = $DB->result($result,0,'id');
      
      $query="INSERT INTO `glpi_notificationtemplatetranslations`
                                 VALUES(NULL, ".$itemtype.", '','##badge.action## : ##badge.entity##',
                        '##lang.badge.entity## :##badge.entity##
   ##FOREACHbadges##
   ##lang.badge.name## : ##badge.name## - ##lang.badge.dateexpiration## : ##badge.dateexpiration####IFbadge.serial## - ##lang.badge.serial## : ##badge.serial####ENDIFbadge.serial####IFbadge.users## - ##lang.badge.users## : ##badge.users####ENDIFbadge.users##
   ##ENDFOREACHbadges##',
                        '&lt;p&gt;##lang.badge.entity## :##badge.entity##&lt;br /&gt; &lt;br /&gt;
                        ##FOREACHbadges##&lt;br /&gt;
                        ##lang.badge.name##  : ##badge.name## - ##lang.badge.dateexpiration## :  ##badge.dateexpiration####IFbadge.serial## - ##lang.badge.serial## :  ##badge.serial####ENDIFbadge.serial####IFbadge.users## - ##lang.badge.users## :  ##badge.users####ENDIFbadge.users##&lt;br /&gt; 
                        ##ENDFOREACHbadges##&lt;/p&gt;');";
      $result=$DB->query($query);
      
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'Alert Expired Badges', 0, 'PluginBadgesBadge', 'ExpiredBadges',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";
      $result=$DB->query($query);
      $query = "INSERT INTO `glpi_notifications`
                                   VALUES (NULL, 'Alert Badges Which Expire', 0, 'PluginBadgesBadge', 'BadgesWhichExpire',
                                          'mail',".$itemtype.",
                                          '', 1, 1, '2010-02-17 22:36:46');";

      $result=$DB->query($query);
   }
   
   if ($update78) {
      $query_="SELECT *
            FROM `glpi_plugin_badges_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_badges_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $query="ALTER TABLE `glpi_plugin_badges_profiles`
               DROP `name` ;";
      $result=$DB->query($query);
   
      Plugin::migrateItemType(
         array(1600=>'PluginBadgesBadge'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"));
	}
	
	CronTask::Register('PluginBadgesBadge', 'BadgesAlert', DAY_TIMESTAMP);
	
   PluginBadgesProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_badges_configure15() {
	global $DB;
	
	// ADD FK_users
	$query_old_items="SELECT `glpi_plugin_badges_users`.`FK_users`,`glpi_plugin_badges`.`ID` 
					FROM `glpi_plugin_badges_users`,`glpi_plugin_badges` WHERE `glpi_plugin_badges_users`.`FK_badges` = `glpi_plugin_badges`.`ID` ";
	$result_old_items=$DB->query($query_old_items);
	if ($DB->numrows($result_old_items)>0) {

		while ($data_old_items=$DB->fetch_array($result_old_items)) {
			if ($data_old_items["ID"]) { 
				$query = "UPDATE `glpi_plugin_badges` SET `FK_users` = '".$data_old_items["FK_users"]."' WHERE `ID` = '".$data_old_items["ID"]."' ";
				$DB->query($query);
			}
		}
	}
	
	$query = "DROP TABLE IF EXISTS `glpi_plugin_badges_users` ";
	$DB->query($query);
}

function plugin_badges_uninstall() {
	global $DB;

   $tables = array("glpi_plugin_badges_badges",
					"glpi_plugin_badges_badgetypes",
					"glpi_plugin_badges_profiles",
					"glpi_plugin_badges_configs",
					"glpi_plugin_badges_notificationstates");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //old versions	
   $tables = array("glpi_plugin_badges",
					"glpi_dropdown_plugin_badges_type",
					"glpi_plugin_badges_users",
					"glpi_plugin_badges_config",
					"glpi_plugin_badges_mailing",
					"glpi_plugin_badges_default");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
	
	$notif = new Notification();
   $options = array('itemtype' => 'PluginBadgesBadge',
                    'event'    => 'ExpiredBadges',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }

   $options = array('itemtype' => 'PluginBadgesBadge',
                    'event'    => 'BadgesWhichExpire',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   
   //templates
   $template = new NotificationTemplate();
   $translation = new NotificationTemplateTranslation();
   $options = array('itemtype' => 'PluginBadgesBadge',
                    'FIELDS'   => 'id');
   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = array('notificationtemplates_id' => $data['id'],
                    'FIELDS'   => 'id');
   
         foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
         }
      $template->delete($data);
   }
   $tables_glpi = array("glpi_displaypreferences",
					"glpi_documents_items",
					"glpi_bookmarks",
					"glpi_logs",
					"glpi_tickets");

	foreach($tables_glpi as $table_glpi)
		$DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginBadgesBadge';");

	if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype'=>'PluginBadgesBadge'));
   }

	return true;
}

function plugin_badges_AssignToTicket($types) {
	global $LANG;

	if (plugin_badges_haveRight("open_ticket","1"))
		$types['PluginBadgesBadge']=$LANG['plugin_badges']['title'][1];

	return $types;
}

// Define dropdown relations
function plugin_badges_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("badges"))
      return array("glpi_plugin_badges_badgetypes"=>array("glpi_plugin_badges_badges"=>"plugin_badges_badgetypes_id"),
      "glpi_entities"=>array("glpi_plugin_badges_badges"=>"entities_id",
                              "glpi_plugin_badges_badgetypes"=>"entities_id"),
      "glpi_locations"=>array("glpi_plugin_badges_badges"=>"locations_id"),
      "glpi_states"=>array("glpi_plugin_badges_badges"=>"states_id",
                           "glpi_plugin_badges_mailingstates"=>"states_id"),
      "glpi_profiles" => array (
				"glpi_plugin_badges_profiles" => "profiles_id"
			),
      "glpi_users"=>array("glpi_plugin_badges_badges"=>"users_id"));
   else
      return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_badges_getDropdown() {
	global $LANG;

	$plugin = new Plugin();
	if ($plugin->isActivated("badges"))
		return array("PluginBadgesBadgeType"=>$LANG['plugin_badges']['setup'][2]);
	else
		return array();
}

function plugin_badges_displayConfigItem($type,$ID,$data,$num) {

	$searchopt=&Search::getOptions($type);
	$table=$searchopt[$ID]["table"];
	$field=$searchopt[$ID]["field"];
	
	switch ($table.'.'.$field) {
      case "glpi_plugin_badges_badges.date_expiration" :
         if ($data["ITEM_$num"] <= date('Y-m-d') && !empty($data["ITEM_$num"]))
            return " class=\"deleted\" ";
         break;
	}
	return "";
}

function plugin_badges_giveItem($type,$ID,$data,$num) {
	global $LANG;

	$searchopt=&Search::getOptions($type);
	$table=$searchopt[$ID]["table"];
	$field=$searchopt[$ID]["field"];

	switch ($table.'.'.$field) {
		case "glpi_plugin_badges_badges.date_expiration" :
			if (empty($data["ITEM_$num"]))
				$out=$LANG['plugin_badges'][30];
			else
				$out= Html::convdate($data["ITEM_$num"]);
         return $out;
         break;
	}
	return "";
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_badges_MassiveActions($type) {
	global $LANG;
	
	switch ($type) {

		case 'PluginBadgesBadge':
			return array(
				// Specific one
				"plugin_badges_transfert"=>$LANG['buttons'][48],
				);
         break;
	}
	return array();
}

function plugin_badges_MassiveActionsDisplay($options=array()) {
	global $LANG;
	
	switch ($options['itemtype']) {

		case 'PluginBadgesBadge':
			switch ($options['action']) {
				// No case for add_document : use GLPI core one
				case "plugin_badges_transfert":
					Dropdown::show('Entity');
               echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
               break;
			}
         break;
	}
	return "";
}

function plugin_badges_MassiveActionsProcess($data) {
   
   
   $badge=new PluginBadgesBadge();
   
	switch ($data['action']) {

      case "plugin_badges_transfert":
         if ($data['itemtype']=='PluginBadgesBadge') {
            foreach ($data["item"] as $key => $val) {
               if ($val==1) {
                  
                  $badge->getFromDB($key);
                  $type = PluginBadgesBadgeType::transfer($badge->fields["plugin_badges_badgetypes_id"],$data['entities_id']);
                  if ($type > 0) {
                     $values["id"] = $key;
                     $values["plugin_badges_badgetypes_id"] = $type;
                     $badge->update($values);
                  }
                  
                  unset($values);
                  $values["id"] = $key;
                  $values["entities_id"] = $data['entities_id'];
                  $badge->update($values);
               }
            }
         }
         break;
	}
}

function plugin_datainjection_populate_badges() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginBadgesBadgeInjection'] = 'badges';
}

?>