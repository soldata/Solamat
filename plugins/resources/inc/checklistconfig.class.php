<?php
/*
 * @version $Id: checklistconfig.class.php 480 2012-11-09 tsmr $
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

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginResourcesChecklistconfig extends CommonDBTM {
	
	static function getTypeName() {
      global $LANG;

      return $LANG['plugin_resources']['title'][3];
   }
   
	function canCreate() {
      return plugin_resources_haveRight('checklist', 'w');
   }

   function canView() {
      return plugin_resources_haveRight('checklist', 'r');
   }
   
   /**
   * Clean object veryfing criteria (when a relation is deleted)
   *
   * @param $crit array of criteria (should be an index)
   */
   public function clean ($crit) {
      global $DB;
      
      foreach ($DB->request($this->getTable(), $crit) as $data) {
         $this->delete($data);
      }
   }
   
	//define header form
	function defineTabs($options=array()) {
		global $LANG;
		//principal
		$ong[1]=$LANG['title'][26];
		return $ong;
	}
	
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_resources']['title'][3];

      $tab[1]['table']=$this->getTable();
      $tab[1]['field']='name';
      $tab[1]['name']=$LANG['plugin_resources'][8];
      $tab[1]['datatype']='itemlink';
      
      $tab[2]['table']=$this->getTable();
      $tab[2]['field']='address';
      $tab[2]['name']=$LANG['plugin_resources']['checklists'][13];

      $tab[3]['table']=$this->getTable();
      $tab[3]['field']='comment';
      $tab[3]['name']=$LANG['plugin_resources'][12];
      $tab[3]['datatype']='text';

      $tab[4]['table']=$this->getTable();
      $tab[4]['field']='tag';
      $tab[4]['name']=$LANG['plugin_resources']['checklists'][1];
      $tab[4]['datatype']='bool';
      
      $tab[30]['table']=$this->getTable();
      $tab[30]['field']='id';
      $tab[30]['name']=$LANG['common'][2];
		
		$tab[80]['table']='glpi_entities';
      $tab[80]['field']='completename';
      $tab[80]['name']=$LANG['entity'][0];
      
      return $tab;
   }
	
	function showForm ($ID, $options=array()) {
		global $LANG;
		
		if (!$this->canView()) return false;
      
		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w',$input);
      }

      $this->showTabs($options);
      $this->showFormHeader($options);
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td >".$LANG['plugin_resources'][8].":	</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name",array('size' => "40"));	
      echo "</td>";
      
      echo "<td>";
      echo $LANG['plugin_resources']['checklists'][1]." : </td><td>";
      Dropdown::showYesNo("tag",$this->fields["tag"]);
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td >".$LANG['plugin_resources']['checklists'][13].":	</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"address",array('size' => "75"));
      echo "</td>";
      
      echo "<td></td>";
      echo "<td></td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td colspan = '4'>";
      echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
      echo $LANG['plugin_resources'][12].": </td></tr>";
      echo "<tr><td class='center'><textarea cols='125' rows='6' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td></tr></table>";
      echo "</td>";
      
      echo "</tr>";

      $options['candel'] = false;
      $this->showFormButtons($options);
      return true;

	}
	
	function addResourceChecklist($resource,$checklists_id,$checklist_type) {
      
      $restrict = "`id` = '".$checklists_id."'";

      $checklists = getAllDatasFromTable("glpi_plugin_resources_checklistconfigs",$restrict);
      
      if (!empty($checklists)) {
         
         foreach ($checklists as $checklist) {
            
            if (isset($resource->fields["plugin_resources_contracttypes_id"])) { 
               unset($checklist["id"]);
               $checklist["plugin_resources_resources_id"] = $resource->fields["id"];
               $checklist["plugin_resources_contracttypes_id"] = $resource->fields["plugin_resources_contracttypes_id"];
               $checklist["checklist_type"] = $checklist_type;
               $checklist["name"] = addslashes($checklist["name"]);
               $checklist["address"] = addslashes($checklist["address"]);
               $checklist["comment"] = addslashes($checklist["comment"]);
               $checklist["entities_id"] = $resource->fields["entities_id"];
               $resource_checklist= new PluginResourcesChecklist();
               $resource_checklist->add($checklist);
            }
         }
      }
   }
   
	function addChecklistsFromRules($resource,$checklist_type) {
      
      $rulecollection = new PluginResourcesRuleChecklistCollection($resource->fields["entities_id"]);
      
      if (isset($resource->fields["plugin_resources_contracttypes_id"]) &&
            $resource->fields["plugin_resources_contracttypes_id"] > 0)
         $contract = $resource->fields["plugin_resources_contracttypes_id"];
        else
         $contract = 0;
         
      $checklists=array();
      $checklists=$rulecollection->processAllRules(array("plugin_resources_contracttypes_id"=>$contract,
                                                "checklist_type"=>$checklist_type),$checklists,array());
      
      if (!empty($checklists)) {
         
         foreach ($checklists as $key => $checklist) {
            $this->addResourceChecklist($resource,$checklist,$checklist_type);
         }
      }
	}
	
	function addRulesFromChecklists($data) {
      
      $rulecollection = new PluginResourcesRuleChecklistCollection();
      $rulecollection->checkGlobal('w');
      
      foreach ($data["item"] as $key => $val) {
         if ($val == 1) {
           
            $this->getFromDB($key);
            $rule = new PluginResourcesRuleChecklist();
            $values["name"] = addslashes($this->fields["name"]);
            $values["match"] = "AND";
            $values["is_active"] = 1;
            $values["is_recursive"] = 1;
            $values["entities_id"] = $this->fields["entities_id"];
            $values["sub_type"] = "PluginResourcesRuleChecklist";
            $newID = $rule->add($values);
            
            if (isset($data["checklist_type"]) && $data["checklist_type"] > 0) {
               $criteria = new RuleCriteria();
               $values["rules_id"] = $newID;
               $values["criteria"] = "checklist_type";
               $values["condition"] = 0;
               $values["pattern"] = $data["checklist_type"];
               $criteria->add($values);
            }
            
            if (isset($data["plugin_resources_contracttypes_id"])) {
               $criteria = new RuleCriteria();
               $values["rules_id"] = $newID;
               $values["criteria"] = "plugin_resources_contracttypes_id";
               $values["condition"] = $data["condition"];
               $values["pattern"] = $data["plugin_resources_contracttypes_id"];
               $criteria->add($values);
            }
             
            $action = new RuleAction();
            $values["rules_id"] = $newID;
            $values["action_type"] = "assign";
            $values["field"] = "checklists_id";
            $values["value"] = $key;
            $action->add($values);
            
         }
      }
	}
}

?>