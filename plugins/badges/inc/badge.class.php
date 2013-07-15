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

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginBadgesBadge extends CommonDBTM {
   
   public $dohistory=true;
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_badges']['title'][1];
   }
   
   function canCreate() {
      return plugin_badges_haveRight('badges', 'w');
   }

   function canView() {
      return plugin_badges_haveRight('badges', 'r');
   }
  
   function getSearchOptions() {
      global $LANG;

      $tab = array();

      $tab['common'] = $LANG['plugin_badges']['title'][1];

      $tab[1]['table']=$this->getTable();
      $tab[1]['field']='name';
      $tab[1]['name']=$LANG['plugin_badges'][8];
      $tab[1]['datatype']='itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      
      $tab[2]['table']='glpi_plugin_badges_badgetypes';
      $tab[2]['field']='name';
      $tab[2]['name']=$LANG['plugin_badges'][20];
      
      $tab[3]['table']=$this->getTable();
      $tab[3]['field']='serial';
      $tab[3]['name']=$LANG['plugin_badges'][11];
      
      $tab[4]['table']=$this->getTable();
      $tab[4]['field']='date_affectation';
      $tab[4]['name']=$LANG['plugin_badges'][3];
      $tab[4]['datatype']='date';
      
      $tab[5]['table']=$this->getTable();
      $tab[5]['field']='date_expiration';
      $tab[5]['name']=$LANG['plugin_badges'][5];
      $tab[5]['datatype']='date';
      
      $tab[6]['table']='glpi_locations';
      $tab[6]['field']='completename';
      $tab[6]['name']=$LANG['plugin_badges'][2];
      
      $tab[7]['table']='glpi_states';
      $tab[7]['field']='name';
      $tab[7]['name']=$LANG['plugin_badges'][28];
      
      $tab[8]['table']=$this->getTable();
      $tab[8]['field']='comment';
      $tab[8]['name']=$LANG['plugin_badges'][12];
      $tab[8]['datatype']='text';
      
      $tab[9]['table']=$this->getTable();
      $tab[9]['field']='is_helpdesk_visible';
      $tab[9]['name']=$LANG['software'][46];
      $tab[9]['datatype']='bool';
      
      $tab[10]['table']='glpi_users';
      $tab[10]['field']='name';
      $tab[10]['name']=$LANG['common'][34];
      
      $tab[11]['table']=$this->getTable();
      $tab[11]['field']='date_mod';
      $tab[11]['massiveaction']=false;
      $tab[11]['name']=$LANG['common'][26];
      $tab[11]['datatype']='datetime';
      
      $tab[30]['table']=$this->getTable();
      $tab[30]['field']='id';
      $tab[30]['name']=$LANG['common'][2];
      
      $tab[80]['table']='glpi_entities';
      $tab[80]['field']='completename';
      $tab[80]['name']=$LANG['entity'][0];
      
		return $tab;
   }
   
	function defineTabs($options=array()) {
		global $LANG;
		
		$ong = array();
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Item_Problem', $ong, $options);
      $this->addStandardTab('Document', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
         
		return $ong;
	}

	function prepareInputForAdd($input) {

		if (isset($input['date_affectation']) && empty($input['date_affectation'])) 
         $input['date_affectation']='NULL';
		if (isset($input['date_expiration']) && empty($input['date_expiration'])) 
         $input['date_expiration']='NULL';

		return $input;
	}

	function prepareInputForUpdate($input) {

		if (isset($input['date_affectation']) && empty($input['date_affectation'])) 
         $input['date_affectation']='NULL';
		if (isset($input['date_expiration']) && empty($input['date_expiration'])) 
         $input['date_expiration']='NULL';

		return $input;
	}

	function showForm ($ID, $options=array()) {
      global $CFG_GLPI, $LANG;

      if (!$this->canView()) return false;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['plugin_badges'][8].": </td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";
      
      echo "<td>".$LANG['common'][34].": </td><td>";
      User::dropdown(array('value' => $this->fields["users_id"],
                           'entity' => $this->fields["entities_id"],
                           'right' => 'all'));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_badges'][2].": </td><td>";
      Dropdown::show('Location', array('name' => "locations_id",
                                       'value' => $this->fields["locations_id"], 
                                       'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "<td>".$LANG['plugin_badges'][20].": </td><td>";
      Dropdown::show('PluginBadgesBadgeType', array('name' => "plugin_badges_badgetypes_id",
                                                   'value' => $this->fields["plugin_badges_badgetypes_id"], 
                                                   'entity' => $this->fields["entities_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_badges'][11].": </td>";
      echo "<td>";
      Html::autocompletionTextField($this,"serial");
      echo "</td>";
      
      echo "<td>".$LANG['plugin_badges'][28].": </td><td>";
      Dropdown::show('State', array('name' => "states_id",
                                    'value' => $this->fields["states_id"]));
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_badges'][3].": </td>";
      echo "<td>";
      Html::showDateFormItem("date_affectation",$this->fields["date_affectation"],true,true);
      echo "</td>";
      
      echo "<td>" . $LANG['software'][46] . ":</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>".$LANG['plugin_badges'][5].":&nbsp;";
      Html::showToolTip(nl2br($LANG['plugin_badges'][29]));
      echo "</td>";
      echo "<td>";
      Html::showDateFormItem("date_expiration",$this->fields["date_expiration"],true,true);
      echo "</td>";
      
      echo "<td>" . $LANG['common'][26] . ":</td><td>";
      echo Html::convDateTime($this->fields["date_mod"]);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td></td><td></td>";
      
      echo "<td>";
      echo $LANG['plugin_badges'][12].": </td>";
      echo "<td class='center'><textarea cols='45' rows='3' name='comment' >".
               $this->fields["comment"]."</textarea>";

      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
	}
   
   // Cron action
   static function cronInfo($name) {
      global $LANG;
       
      switch ($name) {
         case 'BadgesAlert':
            return array (
               'description' => $LANG['plugin_badges']['mailing'][4]);   // Optional
            break;
      }
      return array();
   }
   
   static function queryExpiredBadges() {
      global $DB;
      
      $config=new PluginBadgesConfig();
      $notif= new PluginBadgesNotificationState();
      
      $config->getFromDB('1');
      $delay=$config->fields["delay_expired"];

      $query = "SELECT * 
         FROM `glpi_plugin_badges_badges`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > $delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) > 0 ";
      $query.= "AND `states_id` NOT IN (999999";
      $query.= $notif->findStates();
      $query.= ") ";

      return $query;
   }
   
   static function queryBadgesWhichExpire() {
      global $DB;
      
      $config=new PluginBadgesConfig();
      $notif= new PluginBadgesNotificationState();
      
      $config->getFromDB('1');
      $delay=$config->fields["delay_whichexpire"];
      
      $query = "SELECT *
         FROM `glpi_plugin_badges_badges`
         WHERE `date_expiration` IS NOT NULL
         AND `is_deleted` = '0'
         AND DATEDIFF(CURDATE(),`date_expiration`) > -$delay 
         AND DATEDIFF(CURDATE(),`date_expiration`) < 0 ";
      $query.= "AND `states_id` NOT IN (999999";
      $query.= $notif->findStates();
      $query.= ") ";

      return $query;
   }
   /**
    * Cron action on badges : ExpiredBadges or BadgesWhichExpire
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronBadgesAlert($task=NULL) {
      global $DB,$CFG_GLPI,$LANG;
      
      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;
      
      $query_expired = self::queryExpiredBadges();
      $query_whichexpire = self::queryBadgesWhichExpire();
      
      $querys = array(Alert::NOTICE=>$query_whichexpire, Alert::END=>$query_expired);
      
      $badge_infos = array();
      $badge_messages = array();

      foreach ($querys as $type => $query) {
         $badge_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = $data["name"].": ".
                        Html::convdate($data["date_expiration"])."<br>\n";
            $badge_infos[$type][$entity][] = $data;

            if (!isset($badges_infos[$type][$entity])) {
               $badge_messages[$type][$entity] = $LANG['plugin_badges']['mailing'][0]."<br />";
            }
            $badge_messages[$type][$entity] .= $message;
         }
      }
      
      foreach ($querys as $type => $query) {
      
         foreach ($badge_infos[$type] as $entity => $badges) {
            Plugin::loadLang('badges');

            if (NotificationEvent::raiseEvent(($type==Alert::NOTICE?"BadgesWhichExpire":"ExpiredBadges"),
                                              new PluginBadgesBadge(),
                                              array('entities_id'=>$entity,
                                                    'badges'=>$badges))) {
               $message = $badge_messages[$type][$entity];
               $cron_status = 1;
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",
                                                       $entity).":  $message\n");
                  $task->addVolume(1);
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",
                                                                    $entity).":  $message");
               }

            } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities",$entity).
                             ":  Send badges alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                                          ":  Send badges alert failed",false,ERROR);
               }
            }
         }
      }
      
      return $cron_status;
   }
   
   static function configCron($target) {

      $notif=new PluginBadgesNotificationState();
      $config=new PluginBadgesConfig();

      $config->showForm($target,1);
      $notif->showForm($target);
      $notif->showAddForm($target);
    
   }
}

?>