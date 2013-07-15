<?php
/*
 * @version $Id: HEADER 15930 2012-03-08 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Projet plugin for GLPI
 Copyright (C) 2003-2012 by the Projet Development Team.

 https://forge.indepnet.net/projects/projet
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Projet.

 Projet is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Projet is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Projet. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginProjetTaskPlanning extends CommonDBTM {
   
   function canCreate() {
      return (plugin_projet_haveRight('task', 'w') 
                  && Session::haveRight('show_planning', 1));
   }

   function canView() {
      return plugin_projet_haveRight('task', 'r');
   }
   
	function prepareInputForAdd($input) {

      if (!isset($input["begin"]) || !isset($input["end"]) ){
         return false;
      }

      $this->fields["begin"] = $input["begin"];
      $this->fields["end"] = $input["end"];

      if (!$this->test_valid_date()) {
         self::displayError("date");
         return false;
      }

		return $input;
	}
	
	function post_addItem() {
      global $CFG_GLPI;
      
      // Auto update actiontime
      $fup=new PluginProjetTask();
      $fup->getFromDB($this->input["plugin_projet_tasks_id"]);
      if ($fup->fields["actiontime"]==0) {
         $timestart  = strtotime($this->input["begin"]);
         $timeend    = strtotime($this->input["end"]);
         $updates2[] = "actiontime";
         $fup->fields["actiontime"] = $timeend-$timestart;

         $updates2[]="plugin_projet_taskstates_id";
         $fup->fields["plugin_projet_taskstates_id"]=PluginProjetTaskState::getStatusForPlanning();
         $fup->updateInDB($updates2);
         
      }
   }
	
	function prepareInputForUpdate($input) {
		global $LANG,$CFG_GLPI;
      
      $this->getFromDB($input["id"]);
      // Save fields
      $oldfields=$this->fields;
      $this->fields["begin"] = $input["begin"];
      $this->fields["end"] = $input["end"];

      if (!$this->test_valid_date()) {
         $this->displayError("date");
         return false;
      }

      // Restore fields
      $this->fields=$oldfields;
      
		return $input;
	}
	
	function post_updateItem($history=1) {
		global $CFG_GLPI,$LANG;
		
      $fup=new PluginProjetTask();
      $fup->getFromDB($this->input["plugin_projet_tasks_id"]);
      $timestart  = strtotime($this->input["begin"]);
      $timeend    = strtotime($this->input["end"]);
      $updates2[] = "actiontime";
      $fup->fields["actiontime"] = $timeend-$timestart;
      $updates2[]="plugin_projet_taskstates_id";
      $fup->fields["plugin_projet_taskstates_id"]=PluginProjetTaskState::getStatusForPlanning();
      $fup->updateInDB($updates2);
	}
   
   /**
    * Read the planning information associated with a task
    *
    * @param $plugin_projet_tasks_id integer ID of the task
    *
    * @return bool, true if exists
    */
   function getFromDBbyTask($plugin_projet_tasks_id) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `plugin_projet_tasks_id` = '$plugin_projet_tasks_id'";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
      }
      return false;
   }
   
   function showFormForTask($projet, PluginProjetTask $task) {
      global $CFG_GLPI, $LANG;

      $PluginProjetProjet = new PluginProjetProjet();
      $PluginProjetProjet->getFromDB($projet);
      $taskid = $task->getField('id');
      if ($taskid>0 && $this->getFromDBbyTask($taskid)) {
         if ($this->canCreate()) {
            echo "<script type='text/javascript' >\n";
            echo "function showPlan".$taskid."(){\n";
            echo "Ext.get('plan').setDisplayed('none');";
            $params = array (
               'form' => 'followups',
               'id' => $this->fields["id"],
               'begin' => $this->fields["begin"],
               'end' => $this->fields["end"],
               'entity' => $PluginProjetProjet->fields["entities_id"]
            );
            Ajax::updateItemJsCode('viewplan', $CFG_GLPI["root_doc"] . "/plugins/projet/ajax/planning.php", $params);
            echo "}";
            echo "</script>\n";
            echo "<div id='plan' onClick='showPlan".$taskid."()'>\n";
            echo "<span class='showplan'>";
         }
         if ($this->fields["begin"] && $this->fields["end"])
            echo Html::convDateTime($this->fields["begin"]).
              "&nbsp;->&nbsp;".Html::convDateTime($this->fields["end"]);
         else
            echo $LANG['job'][34];
         if ($this->canCreate()) {
            echo "</span>";
            echo "</div>\n";
            echo "<div id='viewplan'></div>\n";
         }
      } else {
         if ($this->canCreate()) {
            echo "<script type='text/javascript' >\n";
            echo "function showPlanUpdate(){\n";
            echo "Ext.get('plan').setDisplayed('none');";
            $params = array('form'     => 'followups',
                            'entity'   => $_SESSION["glpiactive_entity"]);
            Ajax::UpdateItemJsCode('viewplan',$CFG_GLPI["root_doc"]."/plugins/projet/ajax/planning.php",$params);
            echo "};";
            echo "</script>";

            echo "<div id='plan'  onClick='showPlanUpdate()'>\n";
            echo "<span class='showplan'>".$LANG['job'][34]."</span>";
            echo "</div>\n";
            echo "<div id='viewplan'></div>\n";
         } else {
            echo $LANG['job'][32];
         }
      }
   }
   
	// SPECIFIC FUNCTIONS
   
   /**
    * Current dates are valid ? begin before end
    *
    *@return boolean
    **/
   function test_valid_date() {
      return (!empty($this->fields["begin"]) && !empty($this->fields["end"])
              && strtotime($this->fields["begin"]) < strtotime($this->fields["end"]));
   }

   /**
    * Add error message to message after redirect
    * @param $type error type : date / is_res / other
    *@return nothing
    **/
   static function displayError($type) {
      global $LANG;

      switch ($type) {
         case "date" :
            Session::addMessageAfterRedirect($LANG['planning'][1],false,ERROR);
            break;

         default :
            Session::addMessageAfterRedirect($LANG['common'][61],false,ERROR);
            break;
      }
   }
   
   static function getAlreadyPlannedInformation($val) {
      global $CFG_GLPI;
      
      $out="";

      $out .= PluginProjetProjet::getTypeName()." - ".PluginProjetTask::getTypeName().' : '.Html::convDateTime($val["begin"]).' -> '.
              Html::convDateTime($val["end"]).' : ';
      $out .= "<a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php?id=".
               $val["plugin_projet_tasks_id"]."'>";
      $out .= Html::resume_text($val["name"],80).'</a>';

      return $out;
   }
   /**
    * Populate the planning with planned projet tasks
    *
    * @param $who ID of the user (0 = undefined)
    * @param $who_group ID of the group of users (0 = undefined, mine = login user ones)
    * @param $begin Date
    * @param $end Date
    *
    * @return array of planning item
    */
   static function populatePlanning($options = array()) {
      global $DB, $CFG_GLPI;
      
      $parm = $options;
      
      if (!isset($options['begin']) || $options['begin'] == 'NULL'
            || !isset($options['end']) || $options['end'] == 'NULL') {
         return $options;
      }

      $who       = $options['who'];
      $who_group = $options['who_group'];
      $begin     = $options['begin'];
      $end       = $options['end'];
      // Get items to print
      $ASSIGN="";

      if ($who_group==="mine") {
         if (count($_SESSION["glpigroups"])) {
            $groups=implode("','",$_SESSION['glpigroups']);
            $ASSIGN=" `glpi_plugin_projet_tasks`.`users_id` IN (SELECT DISTINCT `users_id`
                                    FROM `glpi_groups_users`
                                    WHERE `groups_id` IN ('$groups'))
                                          AND ";
         } else { // Only personal ones
            $ASSIGN="`glpi_plugin_projet_tasks`.`users_id` = '$who'
                     AND ";
         }
      } else {
         if ($who>0) {
            $ASSIGN="`glpi_plugin_projet_tasks`.`users_id` = '$who'
                     AND ";
         }
         if ($who_group>0) {
            $ASSIGN="`glpi_plugin_projet_tasks`.`users_id` IN (SELECT `users_id`
                                    FROM `glpi_groups_users`
                                    WHERE `groups_id` = '$who_group')
                                          AND ";
         }
      }
      if (empty($ASSIGN)) {
         $ASSIGN="`glpi_plugin_projet_tasks`.`users_id` IN (SELECT DISTINCT `glpi_profiles_users`.`users_id`
                                 FROM `glpi_profiles`
                                 LEFT JOIN `glpi_profiles_users`
                                    ON (`glpi_profiles`.`id` = `glpi_profiles_users`.`profiles_id`)
                                 WHERE `glpi_profiles`.`interface`='central' ";

         $ASSIGN.=getEntitiesRestrictRequest("AND","glpi_profiles_users", '',
                                             $_SESSION["glpiactive_entity"],1);
         $ASSIGN.=") AND ";
      }

      $query = "SELECT *
                FROM `glpi_plugin_projet_tasks`
                LEFT JOIN `glpi_plugin_projet_taskplannings` ON (`glpi_plugin_projet_taskplannings`.`plugin_projet_tasks_id` = `glpi_plugin_projet_tasks`.`id`)
                WHERE $ASSIGN
                      '$begin' < `end` AND '$end' > `begin`
                ORDER BY `begin`";

      $result=$DB->query($query);

      $fup=new PluginProjetTask();
      $job=new PluginProjetProjet();

      if ($DB->numrows($result)>0) {
         for ($i=0 ; $data=$DB->fetch_array($result) ; $i++) {
            if ($fup->getFromDB($data["plugin_projet_tasks_id"])) {
               if ($job->getFromDB($fup->fields["plugin_projet_projets_id"])) {
                     // Do not check entity here because webcal used non authenticated access
//                   if (haveAccessToEntity($job->fields["entities_id"])) {
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["plugin"]="projet";
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["plugin_projet_tasks_id"]=$data["plugin_projet_tasks_id"];
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["plugin_projet_projets_id"]=$fup->fields["plugin_projet_projets_id"];
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["users_id"]=$data["users_id"];
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["id"]=$data["id"];
                     if (strcmp($begin,$data["begin"])>0) {
                        $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["begin"]=$begin;
                     } else {
                        $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["begin"]=$data["begin"];
                     }
                     if (strcmp($end,$data["end"])<0) {
                        $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["end"]=$end;
                     } else {
                        $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["end"]=$data["end"];
                     }
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["name"]=$fup->fields["name"];
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["plugin_projet_tasktypes_id"]=$fup->fields["plugin_projet_tasktypes_id"];
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["locations_id"]=$fup->fields["locations_id"];
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["name_projet"]=$job->fields["name"];
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["content"]=Html::resume_text($fup->fields["comment"],
                                                                           $CFG_GLPI["cut"]);
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["plugin_projet_taskstates_id"]=$data["plugin_projet_taskstates_id"];
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["priority"]=$data["priority"];
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["who"]=$who;
                     $parm["items"][$data["begin"]."$$$"."plugin_projet".$data["id"]]["planningID"]="plugin_projet".$data["id"];
//                   }
                  }
               }
            }
         }
         
         return $parm;
      }
      

   /**
    * Display a Planning Item
    *
    * @param $parm Array of the item to display
    * @return Nothing (display function)
    **/
   static function displayPlanningItem($parm) {
      global $CFG_GLPI, $LANG;
      
      $rand=mt_rand(); 
		
		echo "<a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/task.form.php?id=".$parm["plugin_projet_tasks_id"]."'";

		echo " onmouseout=\"cleanhide('content_task_".$parm["plugin_projet_tasks_id"].$rand."')\" onmouseover=\"cleandisplay('content_task_".$parm["plugin_projet_tasks_id"].$rand."')\"";
		echo ">";
		
		switch ($parm["type"]) {
			case "in":
				echo date("H:i",strtotime($parm["begin"]))." -> ".date("H:i",strtotime($parm["end"])).": ";
				break;
			case "from":
				break;
			case "begin";
				echo $LANG["buttons"][33]." ".date("H:i",strtotime($parm["begin"])).": ";
				break;
			case "end";
				echo $LANG["buttons"][32]." ".date("H:i",strtotime($parm["end"])).": ";
				break;
      }
      
      echo $LANG['plugin_projet'][30]." : <br>".$parm["name"];
		if ($parm["users_id"] && $parm["who"]==0)
         echo " - ".$LANG['job'][2]." ".getUserName($parm["users_id"]);
		echo "</a><br>";

		echo $LANG['plugin_projet']['profile'][1]." : <a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php?id=".$parm["plugin_projet_projets_id"]."'";
		echo ">".$parm["name_projet"]."</a>";

		echo "<div class='over_link' id='content_task_".$parm["plugin_projet_tasks_id"].$rand."'>";
		if ($parm["end"])
         echo "<strong>".$LANG['search'][9]."</strong> : ".Html::convdatetime($parm["end"])."<br>";
      if ($parm["plugin_projet_tasktypes_id"])
         echo "<strong>".$LANG['plugin_projet']['setup'][1]."</strong> : ".Dropdown::getdropdownname("glpi_plugin_projet_tasktypes",$parm["plugin_projet_tasktypes_id"])."<br>";
      if ($parm["plugin_projet_taskstates_id"])
         echo "<strong>".$LANG['plugin_projet'][19]."</strong> : ".Dropdown::getDropdownName("glpi_plugin_projet_taskstates",$parm['plugin_projet_taskstates_id'])."<br>";
		if ($parm["locations_id"])
         echo "<strong>".$LANG['common'][15]."</strong> : ".Dropdown::getdropdownname("glpi_locations",$parm["locations_id"])."<br>";
		if ($parm["priority"])
         echo "<strong>".$LANG['plugin_projet'][41]."</strong> : ".Ticket::getPriorityName($parm["priority"])."<br>";
		if ($parm["content"])
         echo "<strong>".$LANG['plugin_projet'][10]."</strong> : ".$parm["content"];
		echo "</div>";
   }
}

?>