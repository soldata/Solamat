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

class PluginProjetProjet extends CommonDBTM {
   
   static $types = array('Computer','Monitor','NetworkEquipment','Peripheral',
         'Phone', 'Printer', 'Software', 'Group','User','Supplier', 'Ticket', 'Problem');
         
   public $dohistory=true;
   
   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['plugin_projet']['title'][4];
      }
      return $LANG['plugin_projet']['title'][1];
   }
   
   function canCreate() {
      return plugin_projet_haveRight('projet', 'w');
   }

   function canView() {
      return plugin_projet_haveRight('projet', 'r');
   }
   
   /**
    * For other plugins, add a type to the linkable types
    *
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }
   //TODO Appliances

   /**
    * Type than could be linked to a Store
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
	
	//clean if projet are deleted
   function cleanDBonPurge() {

		$temp = new PluginProjetProjet_Item();
		$temp->deleteByCriteria(array('plugin_projet_projets_id' => $this->fields['id']));
      
      $temp = new PluginProjetTask();
      $temp->deleteByCriteria(array('plugin_projet_projets_id' => $this->fields['id']),1);
      
      $temp = new PluginProjetProjet_Projet();
		$temp->deleteByCriteria(array('plugin_projet_projets_id_1' => $this->fields['id'],
                                    'plugin_projet_projets_id_2' => $this->fields['id']));
		
      
	}
	
	/**
    * Hook called After an item is purge
    */
   static function cleanForItem(CommonDBTM $item) {

      $type = get_class($item);
      $temp = new PluginProjetProjet_Item();
      $temp->deleteByCriteria(array('itemtype' => $type,
                                       'items_id' => $item->getField('id')));
      
      $task = new PluginProjetTask_Item();
      $task->deleteByCriteria(array('itemtype' => $type,
                                       'items_id' => $item->getField('id')));
   }
	
	/*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   function getSelectLinkedItem () {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_projet_projets_items`
              WHERE `plugin_projet_projets_id`='" . $this->fields['id']."'";
   }
	
	function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_projet']['title'][1];

      $tab[1]['table']=$this->getTable();
      $tab[1]['field']='name';
      $tab[1]['name']=$LANG['plugin_projet'][0];
      $tab[1]['datatype']='itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      
      $tab[2]['table']=$this->getTable();
      $tab[2]['field']='date_begin';
      $tab[2]['name']=$LANG['search'][8];
      $tab[2]['datatype']='date';
      
      $tab[3]['table']=$this->getTable();
      $tab[3]['field']='date_end';
      $tab[3]['name']=$LANG['search'][9];
      $tab[3]['datatype']='date';
      
      $tab[4]['table']='glpi_users';
      $tab[4]['field']='name';
      $tab[4]['name']=$LANG['common'][34];
      
      $tab[5]['table']='glpi_groups';
      $tab[5]['field']='name';
      $tab[5]['name']=$LANG['common'][35];
      
      $tab[6]['table']='glpi_plugin_projet_projetstates';
      $tab[6]['field']='name';
      $tab[6]['name']=$LANG['plugin_projet'][19];
      
      $tab[7]['table']         = 'glpi_plugin_projet_projets_projets';
      $tab[7]['field']         = 'plugin_projet_projets_id_1';
      $tab[7]['name']          = $LANG['plugin_projet'][44];
      $tab[7]['massiveaction'] = false;
      $tab[7]['searchtype']    = 'equals';
      $tab[7]['joinparams']    = array('jointype'  => 'item_item');
      $tab[7]['forcegroupby']  =  true;
                                                     
      $tab[8]['table']=$this->getTable();
      $tab[8]['field']='advance';
      $tab[8]['name']=$LANG['plugin_projet'][47];
      $tab[8]['datatype']='integer';
      
      $tab[9]['table']=$this->getTable();
      $tab[9]['field']='show_gantt';
      $tab[9]['name']=$LANG['plugin_projet'][70];
      $tab[9]['datatype']='bool';
      
      $tab[11]['table']=$this->getTable();
      $tab[11]['field']='comment';
      $tab[11]['name']=$LANG['plugin_projet'][2];
      $tab[11]['datatype']='text';
      
      $tab[12]['table']=$this->getTable();
      $tab[12]['field']='description';
      $tab[12]['name']=$LANG['plugin_projet'][10];
      $tab[12]['datatype']='text';
      
      $tab[13]['table']=$this->getTable();
      $tab[13]['field']='is_recursive';
      $tab[13]['name']=$LANG['entity'][9];
      $tab[13]['datatype']='bool';
      $tab[13]['massiveaction'] = false;
      
      $tab[14]['table']=$this->getTable();
      $tab[14]['field']='date_mod';
      $tab[14]['name']=$LANG['common'][26];
      $tab[14]['datatype']='datetime';
      $tab[14]['massiveaction'] = false;
      
      $tab[15]['table']=$this->getTable();
      $tab[15]['field']='is_helpdesk_visible';
      $tab[15]['name']=$LANG['software'][46];
      $tab[15]['datatype']='bool';
      
      $tab[16]['table']='glpi_plugin_projet_projets_items';
      $tab[16]['field']='items_id';
      $tab[16]['name']=$LANG['plugin_projet'][69];
      $tab[16]['massiveaction'] = false;
      $tab[16]['forcegroupby']  =  true;
      $tab[16]['joinparams']    = array('jointype' => 'child');
      
      $tab[31]['table']=$this->getTable();
      $tab[31]['field']='id';
      $tab[31]['name']=$LANG['common'][2];
      $tab[31]['massiveaction'] = false;
      
      $tab[80]['table']='glpi_entities';
      $tab[80]['field']='completename';
      $tab[80]['name']=$LANG['entity'][0];
      
      return $tab;
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType()==__CLASS__) {
         
         if (!isset($withtemplate) || empty($withtemplate)) {
            $ong[1] = $LANG['plugin_projet'][1];
				$ong[2]=$LANG['plugin_projet']['title'][5];
         }
         if ($_SESSION['glpishow_count_on_tabs']) {
            $ong[3]= self::createTabEntry($LANG['plugin_projet']['title'][2], PluginProjetProjet_Item::countForProjet($item));
         } else {
            $ong[3] = $LANG['plugin_projet']['title'][2];
         }
         return $ong;
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 :
               PluginProjetProjet_Projet::showHierarchy($item->getID(),1);
               PluginProjetProjet_Projet::showHierarchy($item->getID());
               break;

            case 2 :
               self::showProjetTreeGantt(array('plugin_projet_projets_id'=>$item->getID(),
                                                'prefixp'=>'','parent'=>0));
               PluginProjetTask::taskLegend();
               break;
               
            case 3 :
               $PluginProjetProjet_Item = new PluginProjetProjet_Item();
               $PluginProjetProjet_Item->showItemFromPlugin($item->getID(),$withtemplate);
               break;

         }
      }
      return true;
   }
   
   function defineTabs($options=array()) {
		global $LANG;
		
		$ong = array();

		$this->addStandardTab('PluginProjetProjet', $ong,$options);
      $this->addStandardTab('PluginProjetProjet_Item', $ong,$options);
      $this->addStandardTab('Document',$ong,$options);
      $this->addStandardTab('Contract_Item',$ong,$options);
      $this->addStandardTab('PluginProjetTask', $ong,$options);
      if (!isset($options['withtemplate']) || empty($options['withtemplate'])) {
         $this->addStandardTab('Ticket',$ong,$options);
         $this->addStandardTab('Item_Problem', $ong, $options);
      }
      
      $this->addStandardTab('Note',$ong,$options);
      $this->addStandardTab('Log',$ong,$options);

      return $ong;
	}
	
	function post_getEmpty() {

      $this->fields['show_gantt'] = 1;
      $this->fields['is_helpdesk_visible'] = 1;
   }
   
	function prepareInputForAdd($input) {

		if (isset($input['date_begin']) 
            && empty($input['date_begin'])) $input['date_begin']='NULL';
		if (isset($input['date_end']) 
            && empty($input['date_end'])) $input['date_end']='NULL';
		
		if (isset($input["id"]) && $input["id"]>0) {
         $input["_oldID"]=$input["id"];
      }
      
      if (isset($input['plugin_projet_projetstates_id']) 
            && !empty($input['plugin_projet_projetstates_id'])) {
         
         $archived = " `type` = '1' ";
         $states = getAllDatasFromTable("glpi_plugin_projet_projetstates",$archived);
         $tab = array();
         if (!empty($states)) {
            foreach ($states as $state) {
               $tab[]= $state['id'];
            }
         }

         if (!empty($tab) && in_array($input['plugin_projet_projetstates_id'],$tab)) {
           
            $input['advance']='100';
         }  
      }

      unset($input['id']);
      //unset($input['withtemplate']);

		return $input;
	}

	function post_addItem() {
		global $CFG_GLPI,$LANG;
		
		
		$projet_projet = new PluginProjetProjet_Projet();

      // From interface
      if (isset($this->input['_link'])) {
         $this->input['_link']['plugin_projet_projets_id_1'] = $this->fields['id'];
         // message if projet doesn't exist
         if (!empty($this->input['_link']['plugin_projet_projets_id_2'])) {
            if ($projet_projet->can(-1, 'w', $this->input['_link'])) {
               $projet_projet->add($this->input['_link']);
            } else {
               Session::addMessageAfterRedirect($LANG['plugin_projet'][3], false, ERROR);
            }
         }
      }
      
		// Manage add from template
		if (isset($this->input["_oldID"])) {
         
         //add parent
         // ADD Documents
			$projet_projet = new PluginProjetProjet_Projet();
			$restrict = "`plugin_projet_projets_id_1` = '".$this->input["_oldID"]."'";
         $parents = getAllDatasFromTable("glpi_plugin_projet_projets_projets",$restrict);
         if (!empty($parents)) {
            foreach ($parents as $parent) {
               $projet_projet->add(array(
                        'plugin_projet_projets_id_2' => $parent["plugin_projet_projets_id_2"],
                        'plugin_projet_projets_id_1' => $this->fields['id'],
                        'link' => PluginProjetProjet_Projet::LINK_TO));
            }
			}
      
			// ADD Documents
			$docitem=new Document_Item();
			$restrict = "`items_id` = '".$this->input["_oldID"]."' AND `itemtype` = '".$this->getType()."'";
         $docs = getAllDatasFromTable("glpi_documents_items",$restrict);
         if (!empty($docs)) {
            foreach ($docs as $doc) {
               $docitem->add(array('documents_id' => $doc["documents_id"],
                        'itemtype' => $this->getType(),
                        'items_id' => $this->fields['id']));
            }
			}
			
			// ADD Contracts
			$contractitem=new Contract_Item();
			$restrict = "`items_id` = '".$this->input["_oldID"]."' AND `itemtype` = '".$this->getType()."'";
         $contracts = getAllDatasFromTable("glpi_contracts_items",$restrict);
         if (!empty($contracts)) {
            foreach ($contracts as $contract) {
               $contractitem->add(array('contracts_id' => $contract["contracts_id"],
                        'itemtype' => $this->getType(),
                        'items_id' => $this->fields['id']));
            }
			}
			
			// ADD items
			$PluginProjetProjet_Item= new PluginProjetProjet_Item();
			$restrict = "`plugin_projet_projets_id` = '".$this->input["_oldID"]."'";
         $items = getAllDatasFromTable("glpi_plugin_projet_projets_items",$restrict);
         if (!empty($items)) {
            foreach ($items as $item) {
               $PluginProjetProjet_Item->add(array('plugin_projet_projets_id' => $this->fields['id'],
                        'itemtype' => $item["itemtype"],
                        'items_id' => $item["items_id"]));
            }
			}

			// ADD tasks
			$PluginProjetTask = new PluginProjetTask();
			$PluginProjetTask_Item = new PluginProjetTask_Item();
			
			$restrict = "`plugin_projet_projets_id` = '".$this->input["_oldID"]."'
                     AND is_deleted != 1";
         $tasks = getAllDatasFromTable("glpi_plugin_projet_tasks",$restrict);
         if (!empty($tasks)) {
            foreach ($tasks as $task) {
               $values=$task;
               $taskid = $values["id"];
               unset($values["id"]);
               $values["plugin_projet_projets_id"]=$this->fields['id'];
               $values["name"] = addslashes($task["name"]);
					$values["comment"] = addslashes($task["comment"]);
					$values["sub"] = addslashes($task["sub"]);
					$values["others"] = addslashes($task["others"]);
					$values["affect"] = addslashes($task["affect"]);
               
               $newid = $PluginProjetTask->add($values);
               
               $restrictitems = "`plugin_projet_tasks_id` = '".$taskid."'";
               $tasksitems = getAllDatasFromTable("glpi_plugin_projet_tasks_items",$restrictitems);
               if (!empty($tasksitems)) {
                  foreach ($tasksitems as $tasksitem) {
                     $PluginProjetTask_Item->add(array('plugin_projet_tasks_id' => $newid,
                        'itemtype' => $tasksitem["itemtype"],
                        'items_id' => $tasksitem["items_id"]));
                  }
               }
            }
         }
		}

		if (isset($this->input['withtemplate']) 
          && $this->input["withtemplate"]!=1
          && isset($this->input['send_notification']) 
          && $this->input['send_notification']==1) {
			if ($CFG_GLPI["use_mailing"]) {
            NotificationEvent::raiseEvent("new",$this);
         }
      }
	}
	
	function prepareInputForUpdate($input) {
		global $LANG,$CFG_GLPI;
      
      if (isset($input['date_begin']) 
            && empty($input['date_begin'])) $input['date_begin']='NULL';
		if (isset($input['date_end']) 
            && empty($input['date_end'])) $input['date_end']='NULL';
		
		if (isset($input['plugin_projet_projetstates_id']) 
            && !empty($input['plugin_projet_projetstates_id'])) {
         
         $archived = " `type` = '1' ";
         $states = getAllDatasFromTable("glpi_plugin_projet_projetstates",$archived);
         $tab = array();
         if (!empty($states)) {
            foreach ($states as $state) {
               $tab[]= $state['id'];
            }
         }
         if (!empty($tab) && in_array($input['plugin_projet_projetstates_id'],$tab)) { 
            $input['advance']='100';
         }  
      }
            
		if (isset($input['_link'])) {
         $projet_projet = new PluginProjetProjet_Projet();
         if (!empty($input['_link']['plugin_projet_projets_id_2'])) {
            if ($projet_projet->can(-1, 'w', $input['_link'])) {
               $projet_projet->add($input['_link']);
            } else {
               Session::addMessageAfterRedirect($LANG['plugin_projet'][3], false, ERROR);
            }
         }
      }
      
		$this->getFromDB($input["id"]);
		
		$input["_old_name"]=$this->fields["name"];
		$input["_old_date_begin"]=$this->fields["date_begin"];
		$input["_old_date_end"]=$this->fields["date_end"];
		$input["_old_users_id"]=$this->fields["users_id"];
		$input["_old_groups_id"]=$this->fields["groups_id"];
		$input["_old_plugin_projet_projetstates_id"]=$this->fields["plugin_projet_projetstates_id"];
		$input["_old_advance"]=$this->fields["advance"];
		$input["_old_show_gantt"]=$this->fields["show_gantt"];
		$input["_old_comment"]=$this->fields["comment"];
		$input["_old_description"]=$this->fields["description"];

		return $input;
	}
	
	function post_updateItem($history=1) {
		global $CFG_GLPI,$LANG;
      
      if (count($this->updates) 
         && isset($this->input["withtemplate"]) 
         && $this->input["withtemplate"]!=1) {

         if ($CFG_GLPI["use_mailing"] 
            && isset($this->input['send_notification']) 
            && $this->input['send_notification']==1) {
            NotificationEvent::raiseEvent("update",$this);
         }
      }
	}
	
	function pre_deleteItem() {
      global $CFG_GLPI;
      
      if ($CFG_GLPI["use_mailing"] 
         && $this->fields["is_template"]!=1 
         && isset($this->input['delete'])  
         && isset($this->input['send_notification']) 
         && $this->input['send_notification']==1) {
         NotificationEvent::raiseEvent("delete",$this);
      }
      
      return true;
   }
	
	function showForm($ID, $options=array()) {
		global $CFG_GLPI, $LANG;
		
		if (!plugin_projet_haveRight("projet","r")) return false;
		
		if (!$this->canView()) return false;
    
      if ($ID > 0) {
			$this->check($ID,'r');
		} else {
			// Create item 
			$this->check(-1,'w');
			$this->getEmpty();
		}
		
		if (isset($options['withtemplate']) && $options['withtemplate'] == 2) {
         $template = "newcomp";
         $datestring = $LANG['computers'][14]." : ";
         $date = Html::convDateTime($_SESSION["glpi_currenttime"]);
      } else if (isset($options['withtemplate']) && $options['withtemplate'] == 1) {
         $template = "newtemplate";
         $datestring = $LANG['computers'][14]." : ";
         $date = Html::convDateTime($_SESSION["glpi_currenttime"]);
      } else {
         $datestring = $LANG['common'][26].": ";
         $date = Html::convDateTime($this->fields["date_mod"]);
         $template = false;
      }
    
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_projet'][0].": </td>";
      echo "<td>";
      $opt = array();
      if (isset($options['helpdesk_id']) && !empty($options['helpdesk_id'])) {
         $help = new $options['helpdesk_itemtype']();
         if ($help->getFromDB($options['helpdesk_id'])) {
            $opt['value'] = $help->fields["name"];
            echo "<input type='hidden' name='helpdesk_id' value='".$options['helpdesk_id']."'>";
            echo "<input type='hidden' name='helpdesk_itemtype' value='".$options['helpdesk_itemtype']."'>";
         }
      }
      Html::autocompletionTextField($this,"name",$opt);	
      echo "</td>";

      //Projet parent
      echo "<td>".$LANG['plugin_projet'][44].": </td>";
      echo "<td>";
      
      PluginProjetProjet_Projet::displayLinkedProjetsTo($ID);
      
      if ($this->canCreate()) {
         
         $rand_linked_projet = mt_rand();
         
			echo "&nbsp;";
			if (!PluginProjetProjet_Projet::getParentProjetsTo($ID)) {
				echo "<img onClick=\"Ext.get('linkedprojet$rand_linked_projet').setDisplayed('block')\"
                       title=\"".$LANG['buttons'][8]."\" alt=\"".$LANG['buttons'][8]."\"
                       class='pointer' src='".$CFG_GLPI["root_doc"]."/pics/add_dropdown.png'>";
         }
			echo "<div style='display:none' id='linkedprojet$rand_linked_projet'>";
         PluginProjetProjet_Projet::dropdownLinks('_link[link]',
                                      (isset($values["_link"])?$values["_link"]['link']:''));
         echo "&nbsp;";
         PluginProjetProjet_Projet::dropdownParent("_link[plugin_projet_projets_id_2]", 
                           (isset($values["_link"])?$values["_link"]['plugin_projet_projets_id_2']:''),
                           array('id' => $this->fields["id"],
                                 'entities_id' => $this->fields["entities_id"]));
         echo "<input type='hidden' name='_link[plugin_projet_projets_id_1]' value='$ID'>\n";
         
         echo "&nbsp;";
         echo "</div>";

         if (isset($values["_link"]) && !empty($values["_link"]['plugin_projet_projets_id_2'])) {
            echo "<script language='javascript'>Ext.get('linkedprojet$rand_linked_projet').
                   setDisplayed('block');</script>";
         }
      }
      
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_3'><td colspan='2'>".$LANG['plugin_projet'][9]."</td>";
      echo "<td colspan='2'>".$LANG['plugin_projet'][24]."</td></tr>";
      
      echo "<tr class='tab_bg_1'><td>".$LANG['common'][34].": </td><td>";
      User::dropdown(array('value' => $this->fields["users_id"],
                           'entity' => $this->fields["entities_id"],
                           'right' => 'all'));
      echo "</td>";
      echo "<td>".$LANG['search'][8].": </td><td>";
      Html::showDateFormItem("date_begin",$this->fields["date_begin"],true,true);
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1'><td>".$LANG['common'][35].": </td><td>";
      Dropdown::show('Group', array('value' => $this->fields["groups_id"],
                                    'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>".$LANG['search'][9].": </td><td>";
      Html::showDateFormItem("date_end",$this->fields["date_end"],true,true);
      echo "</td></tr>";
      
      //// START PROVECTIO
      echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_projet'][75].":&nbsp;";
      Html::showToolTip(nl2br($LANG['plugin_projet'][76]));
      echo "</td>";
      echo "<td>" . self::getProjectForecast($ID) . "</td>";
      echo "<td>" . $LANG['plugin_projet'][72].":&nbsp;";
      Html::showToolTip(nl2br($LANG['plugin_projet'][77]));
      echo "</td>";
      echo "<td>". self::getProjectDuration($ID) . "</td></tr>";
      //// END PROVECTIO 

      //status
      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_projet'][19].":</td><td>";
      Dropdown::show('PluginProjetProjetState',
                  array('value'  => $this->fields["plugin_projet_projetstates_id"]));
      echo "</td>";
      echo "<td>".$LANG['plugin_projet'][70].": </td><td>";
      Dropdown::showYesNo("show_gantt",$this->fields["show_gantt"]);
      echo "</td></tr>";
      
      //advance
      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_projet'][47].":</td><td>";
      $advance=floor($this->fields["advance"]);
      echo "<select name='advance'>";
      for ($i=0;$i<101;$i+=5) {
         echo "<option value='$i' ";
         if ($advance==$i) echo "selected";
            echo " >$i</option>";
      }
      echo "</select> ".$LANG['plugin_projet'][48];	
      echo "<td>".$LANG['software'][46].": </td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1'><td colspan='4'>".$LANG['plugin_projet'][2].": </td></tr>";

      echo "<tr class='tab_bg_1'><td colspan='4'>";
      $value = $this->fields["comment"];
      if (isset($options['helpdesk_id']) && !empty($options['helpdesk_id'])) {
         $help = new $options['helpdesk_itemtype']();
         if ($help->getFromDB($options['helpdesk_id'])) {
            $value = $help->fields["content"];
         }
      }
      echo "<textarea cols='130' rows='4' name='comment' >".$value."</textarea>";

      echo "<input type='hidden' name='withtemplate' value='".$options['withtemplate']."'>";
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1'><td colspan='4'>".$LANG['plugin_projet'][14].": </td></tr>";

      echo "<tr class='tab_bg_1'><td colspan='4'>";
      echo "<textarea cols='130' rows='4' name='description' >".$this->fields["description"]."</textarea>";
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1 center'>";
      echo "<td>".$LANG['plugin_projet']['mailing'][17]."</td><td>";
      echo "<input type='checkbox' name='send_notification' checked = true";
      echo " value='1'>";
      echo "</td>";
      
      echo "<td colspan='2'>";
      $datestring = $LANG['common'][26].": ";
      $date = Html::convDateTime($this->fields["date_mod"]);
      echo $datestring.$date."</td>";

      echo "</tr>";
      
      $this->showFormButtons($options);
      $this->addDivForTabs();
      return true;
		
	}
	
	function listOfTemplates($target,$add=0) {
      global $LANG;
      
      $restrict = "`is_template` = '1'";
      $restrict.=getEntitiesRestrictRequest(" AND ",$this->getTable(),'','',$this->maybeRecursive());
      $restrict.=" ORDER BY `name`";
      $templates = getAllDatasFromTable($this->getTable(),$restrict);
      
      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }
         
      echo "<div align='center'><table class='tab_cadre' width='50%'>";
      if ($add) {
         echo "<tr><th colspan='".(2+$colsup)."'>".$LANG['common'][7]." - ".$LANG['plugin_projet']['title'][1].":</th>";
      } else {
         echo "<tr><th colspan='".(2+$colsup)."'>".$LANG['common'][14]." - ".$LANG['plugin_projet']['title'][1]." :</th>";
      }
      
      echo "</tr>";
      if ($add) {

         echo "<tr>";
         echo "<td colspan='".(2+$colsup)."' class='center tab_bg_1'>";
         echo "<a href=\"$target?id=-1&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;" . $LANG['common'][31] . "&nbsp;&nbsp;&nbsp;</a></td>";
         echo "</tr>";
      }
      
      foreach ($templates as $template) {

         $templname = $template["template_name"];
         if ($_SESSION["glpiis_ids_visible"]||empty($template["template_name"]))
         $templname.= "(".$template["id"].")";

         echo "<tr>";
         echo "<td class='center tab_bg_1'>";
         if (!$add) {
            echo "<a href=\"$target?id=".$template["id"]."&amp;withtemplate=1\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";
            
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center tab_bg_2'>";
               echo Dropdown::getDropdownName("glpi_entities",$template['entities_id']);
               echo "</td>";
            }
            echo "<td class='center tab_bg_2'>";
            Html::showSimpleForm($target,
                                    'purge',
                                    $LANG['buttons'][6],
                                    array('id' => $template["id"],'withtemplate'=>1));

            echo "</td>";
            
         } else {
            echo "<a href=\"$target?id=".$template["id"]."&amp;withtemplate=2\">&nbsp;&nbsp;&nbsp;$templname&nbsp;&nbsp;&nbsp;</a></td>";
            
            if (Session::isMultiEntitiesMode()) {
               echo "<td class='center tab_bg_2'>";
               echo Dropdown::getDropdownName("glpi_entities",$template['entities_id']);
               echo "</td>";
            }
         }
         echo "</tr>";
      }
      if (!$add) {
         echo "<tr>";
         echo "<td colspan='".(2+$colsup)."' class='tab_bg_2 center'>";
         echo "<b><a href=\"$target?withtemplate=1\">".$LANG['common'][9]."</a></b>";
         echo "</td>";
         echo "</tr>";
      }
      echo "</table></div>";
   }
   
   /**
    * Display a simple progress bar
    * @param $width Width of the progress bar
    * @param $percent Percent of the progress bar
    * @param $options array options :
    *            - title : string title to display (default Progesssion)
    *            - simple : display a simple progress bar (no title / only percent)
    *            - forcepadding : boolean force str_pad to force refresh (default true)
    * @return nothing
    *
    *
    **/
   static function displayProgressBar($width,$percent,$options=array()) {
      global  $CFG_GLPI,$LANG;
      
      $param['simple']=false;
      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $param[$key]=$val;
         }
      }
   
      $percentwidth=floor($percent*$width/100);
      
      if ($param['simple']) {
         $output=$percent."%";
      } else {
         $output="<div class='center'><table class='tab_cadre' width='".($width+20)."px'>";

         $output.="<tr><td>
                <table><tr><td class='center' style='background:url(".$CFG_GLPI["root_doc"].
                "/pics/loader.png) repeat-x; padding: 0px;font-size: 10px;' width='".$percentwidth."px' height='12px'>";

         $output.=$percent."%";

         $output.="</td></tr></table></td>";
         $output.="</tr></table>";
         $output.="</div>";
      }
      return $output;
   }
   
   function dropdownProjet($name,$entity_restrict=-1,$used=array()) {
      global $DB,$CFG_GLPI, $LANG;

      $where=" WHERE `".$this->gettable()."`.`is_deleted` = '0' 
            AND `".$this->gettable()."`.`is_template` = '0'";
      
      if (isset($entity_restrict)&&$entity_restrict>=0) {
         $where.=getEntitiesRestrictRequest("AND",$this->gettable(),'',$entity_restrict,true);
      } else {
         $where.=getEntitiesRestrictRequest("AND",$this->gettable(),'','',true);
      }
      
      if (isset($used)) {
      $where .=" AND `".$this->gettable()."`.`id` NOT IN (0";

      foreach($used as $val)
         $where .= ",$val";
      $where .= ") ";
      }

      $query = "SELECT * 
            FROM `".$this->gettable()."` 
            $where 
            ORDER BY `entities_id`,`name`";
      $result=$DB->query($query);
      $number = $DB->numrows($result);
      $i = 0;
      
      echo "<select name=\"".$name."\">";

      echo "<option value=\"0\">".Dropdown::EMPTY_VALUE."</option>";
      
      if ($DB->numrows($result)) {
         $prev=-1;
         while ($data=$DB->fetch_array($result)) {
            if ($data["entities_id"]!=$prev) {
               if ($prev>=0) {
                  echo "</optgroup>";
               }
               $prev=$data["entities_id"];
               echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
            }
            $output = $data["name"];
            echo "<option value=\"".$data["id"]."\" title=\"$output\">".substr($output,0,$_SESSION["glpidropdown_chars_limit"])."</option>";
         }
         if ($prev>=0) {
            echo "</optgroup>";
         }
      }
      echo "</select>";
      
   }
	
	function showUsers($itemtype,$ID) {
      global $DB,$CFG_GLPI, $LANG;
      
      $item = new $itemtype();
      $canread = $item->can($ID,'r');
      $canedit = $item->can($ID,'w');
      
      $query = "SELECT `".$this->gettable()."`.* FROM `".$this->gettable()."` "
      ." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$this->gettable()."`.`entities_id`) ";
      if ($itemtype=='User')
         $query.= " WHERE `".$this->gettable()."`.`users_id` = '".$ID."' ";
      else
         $query.= " WHERE `".$this->gettable()."`.`groups_id` = '".$ID."' ";
      $query.= "AND `".$this->gettable()."`.`is_template` = 0 "
      . getEntitiesRestrictRequest(" AND ",$this->gettable(),'','',$this->maybeRecursive());
      
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      if (Session::isMultiEntitiesMode()) {
         $colsup=1;
      } else {
         $colsup=0;
      }
      
      if ($number>0){
         echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php\">";
         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='".(4+$colsup)."'>".$LANG['plugin_projet'][52].":</th></tr>";
         echo "<tr><th>".$LANG['plugin_projet'][0]."</th>";
         if (Session::isMultiEntitiesMode()) 
            echo "<th>".$LANG['entity'][0]."</th>";
         echo "<th>".$LANG['plugin_projet'][10]."</th>";
         echo "<th>".$LANG['plugin_projet'][47]."</th>";
         echo "</tr>";

         while ($data=$DB->fetch_array($result)) {

            echo "<tr class='tab_bg_1".($data["is_deleted"]=='1'?"_2":"")."'>";

            if ($canread && (in_array($data['entities_id'],$_SESSION['glpiactiveentities']) || $data["recursive"])) {
               echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/projet/front/projet.form.php?id=".$data["id"]."'>".$data["name"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
               echo "</a></td>";
            } else {
               echo "<td class='center'>".$data["name"];
               if ($_SESSION["glpiis_ids_visible"]) echo " (".$data["id"].")";
               echo "</td>";
            }
            if (Session::isMultiEntitiesMode()) 
               echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",$data['entities_id'])."</td>";
            echo "<td align='center'>".$data["description"]."</td>";
            echo "<td align='center'>".$data["advance"]." ".$LANG['plugin_projet'][48]."</td>";
            echo "</tr>";
         }
         echo "</table></div>";
         Html::closeForm();
         echo "<br>";
      } else {
         echo "<div align='center'><table class='tab_cadre_fixe' style='text-align:center'>";
         echo "<tr><th><strong>".$LANG['plugin_projet'][52]."</strong>";
         echo "</th></tr></table></div><br>";
      }
   }
   
   //// START PROVECTIO
	/**
	* Compute project real duration
	*
	*@param $ID ID of the project
	*@return text duration of project
	**/
	static function getProjectDuration($ID) {
		global $DB;
		
		$query = "SELECT SUM(`actiontime`) 
				FROM `glpi_tickets` 
				WHERE `items_id`  = '$ID' 
					AND `itemtype` = 'PluginProjetProjet';";
		
		if ($result = $DB->query($query)) {
			$sum=$DB->result($result,0,0);
			if (is_null($sum)) return '--';
			
			return Ticket::getActionTime($sum);
		} else {
			return '--';
		}
	}
	
	/**
   * Compute forecast duration
   *
   *@param $ID ID of the project
   *@return text duration of project
   */
   static function getProjectForecast ($ID) {
		global $DB;
		
		$query = "SELECT SUM(`actiontime`) 
				FROM `glpi_plugin_projet_tasks` 
				WHERE `plugin_projet_projets_id` = '$ID' ";
		
		if ($result = $DB->query($query)) {
			$sum=$DB->result($result,0,0);
		if (is_null($sum)) return '--';
			return Ticket::getActionTime($sum);
		} else {
			return '--';
		}
   }
   

	static function showGlobalGantt() {
      global $LANG,$CFG_GLPI,$gtitle,$gdata,$nbgdata,$gconst,$gdate_begin,$gdate_end;    
      
      //not show archived projects
      $archived = " `type` = '1' ";
      $states = getAllDatasFromTable("glpi_plugin_projet_projetstates",$archived);
      
      $restrict= " `is_deleted` = '0'";
      
      $tab = array();
      if (!empty($states)) {
         foreach ($states as $state) {
            $tab[]= $state['id'];
         }
      }
      if (!empty($tab)) {
         $restrict.= "AND `plugin_projet_projetstates_id` NOT IN (".implode(',',$tab).")";
      }
      
      $restrict.= " AND `is_template` = '0'";
      $restrict.= " AND `show_gantt` = '1'";
      $restrict.=getEntitiesRestrictRequest(" AND ","glpi_plugin_projet_projets",'','',true);
      $restrict.= " ORDER BY `date_begin` ASC";
      
      $projets = getAllDatasFromTable("glpi_plugin_projet_projets",$restrict);
      
      if (!empty($projets)) {
         echo "<div align='center'><table border='0' class='tab_cadre'>";
         echo "<tr><th align='center' >".$LANG['plugin_projet']['title'][5];
         echo "</th></tr>";
         
         $gdata=array();
         foreach ($projets as $projet) {
            
            if (Session::isMultiEntitiesMode())
               $gantt_p_name=Dropdown::getDropdownName("glpi_entities",$projet['entities_id'])." - ".$projet["name"];
            else
               $gantt_p_name=$projet["name"];
               
            $int = hexdec(PluginProjetProjetState::getStatusColor($projet["plugin_projet_projetstates_id"]));
            $gantt_p_bgcolor = array(0xFF & ($int >> 0x10), 0xFF & ($int >> 0x8), 0xFF & $int);
            
            $gantt_p_date_begin=date("Y-m-d");
            $gantt_p_date_end=date("Y-m-d");
            if (!empty($projet["date_begin"])) {
               $gantt_p_date_begin=$projet["date_begin"];
            }
            if (!empty($projet["date_end"])) {
               $gantt_p_date_end=$projet["date_end"];
            }
            
            $dateDepartTimestamp = strtotime($gantt_p_date_end);
            $gantt_p_date_end = date("Y-m-d", strtotime("+ 1 day", $dateDepartTimestamp));
            
            $gdata[]=array("type"=>'group',
                           "projet"=>$projet["id"],
                           "name"=>$gantt_p_name,
                           "date_begin"=>$gantt_p_date_begin,
                           "date_end"=>$gantt_p_date_end,
                           "advance"=>$projet["advance"],
                           "bg_color"=>$gantt_p_bgcolor);
         }

         echo "<tr><td width='100%'>";
         echo "<div align='center'>";
         if (!empty($gdate_begin) && !empty($gdate_end)) {
            $gtitle=$gtitle."<DateBeg> / <DateEnd>";
            $gdate_begin=date("Y",$gdate_begin)."-".date("m",$gdate_begin)."-".date("d",$gdate_begin);
            $gdate_end=date("Y",$gdate_end)."-".date("m",$gdate_end)."-".date("d",$gdate_end);
         }
         $ImgName=self::writeGantt($gtitle,$gdata,$gconst,$gdate_begin,$gdate_end);
         echo "<img src='".$CFG_GLPI["root_doc"]."/front/pluginimage.send.php?plugin=projet&amp;name=".$ImgName."&amp;clean=1' alt='Gantt'/>";//afficher graphique
         echo "</div>";
         echo "</td></tr></table></div>";
      }
   }
   
   static function showProjetTreeGantt($options=array()) {
      global $LANG,$CFG_GLPI,$gtitle,$gdata,$gconst,$gdate_begin,$gdate_end;
      
      self::showProjetGantt($options);
      
      echo "<div align='center'><table border='0' class='tab_cadre'>";
      echo "<tr><th align='center' >".$LANG['plugin_projet']['title'][5];
      echo "</th></tr>";
      echo "<tr><td width='100%'>";
      echo"<div align='center'>";
      if (!empty($gdate_begin) && !empty($gdate_end)) {
         $gtitle=$gtitle."<DateBeg> / <DateEnd>";
         $gdate_begin=date("Y",$gdate_begin)."-".date("m",$gdate_begin)."-".date("d",$gdate_begin);
         $gdate_end=date("Y",$gdate_end)."-".date("m",$gdate_end)."-".date("d",$gdate_end);
      }
      $ImgName=self::writeGantt($gtitle,$gdata,$gconst,$gdate_begin,$gdate_end);
      echo "<img src='".$CFG_GLPI["root_doc"]."/front/pluginimage.send.php?plugin=projet&amp;name=".$ImgName."&amp;clean=1' alt='Gantt' />";//afficher graphique

      echo"</div>";
      echo "</td></tr></table></div>";
            
   }
   
   static function showProjetGantt($options=array()) {
      global $gdata;
      
      $restrict = " `id` = '".$options["plugin_projet_projets_id"]."' ";
      $restrict.= " AND `is_deleted` = '0'";
      $restrict.= " AND `is_template` = '0'";
      
      $projets = getAllDatasFromTable("glpi_plugin_projet_projets",$restrict);
      
      $prefixp = $options["prefixp"];
      
      if (!empty($projets)) {
         
         foreach ($projets as $projet) {
            
            if ($options["parent"] > 0)
               $prefixp.= "-";
            //nom
            if ($options["parent"] > 0)
               $gantt_p_name= $prefixp." ".$projet["name"];
            else
               $gantt_p_name= $projet["name"];

            
            $int = hexdec(PluginProjetProjetState::getStatusColor($projet["plugin_projet_projetstates_id"]));
            $gantt_p_bgcolor = array(0xFF & ($int >> 0x10), 0xFF & ($int >> 0x8), 0xFF & $int);
            
            $gantt_p_date_begin=date("Y-m-d");
            $gantt_p_date_end=date("Y-m-d");
            if (!empty($projet["date_begin"])) {
               $gantt_p_date_begin=$projet["date_begin"];
            }
            if (!empty($projet["date_end"])) {
               $gantt_p_date_end=$projet["date_end"];
            }
            
            $dateDepartTimestamp = strtotime($gantt_p_date_end);
            $gantt_p_date_end = date("Y-m-d", strtotime("+ 1 day", $dateDepartTimestamp));
            $gdata[]=array("type"=>'group',
                           "projet"=>$options["plugin_projet_projets_id"],
                           "name"=>$gantt_p_name,
                           "date_begin"=>$gantt_p_date_begin,
                           "date_end"=>$gantt_p_date_end,
                           "advance"=>$projet["advance"],
                           "bg_color"=>$gantt_p_bgcolor);

            PluginProjetTask::showTaskTreeGantt(array('plugin_projet_projets_id'=>$projet["id"]));
            
            $condition = " `plugin_projet_projets_id_2` = '".$projet["id"]."' ";
            $projets_projets = getAllDatasFromTable("glpi_plugin_projet_projets_projets",$condition);
            
            $restrictchild= " `is_deleted` = '0'";
            $restrictchild.= " AND `is_template` = '0'";
            $tab = array();
            if (!empty($projets_projets)) {
               foreach ($projets_projets as $projets_projet) {
                  $tab[]= $projets_projet['plugin_projet_projets_id_1'];
               }
            }
            if (!empty($tab)) {
               $restrictchild.= " AND `id` IN (".implode(',',$tab).")";
            }

            $restrictchild.= " ORDER BY `plugin_projet_projetstates_id`,`date_begin` DESC";

            $childs = getAllDatasFromTable("glpi_plugin_projet_projets",$restrictchild);

            if (!empty($childs) && !empty($tab)) {
               foreach ($childs as $child) {
                  $params=array('plugin_projet_projets_id'=>$child["id"],
                                 'parent'=>1,
                                 'prefixp'=>$prefixp);
                  self::showProjetGantt($params);
                  
               }
            }
         }
      }        
   }
   
   static function dropAccent($chaine) {

      $chaine=utf8_decode($chaine);
      $chaine=strtr( $chaine, 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ', 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn' );
      return $chaine;

   }

   static function writeGantt($title,$gdata,$gconst,$gantt_date_begin,$gantt_date_end) {
      global $CFG_GLPI;
      
      include_once (GLPI_ROOT."/plugins/projet/inc/gantt.class.php");
      
      if (isset($gantt_date_begin)) $definitions['limit']['start'] = mktime(0,0,0,substr($gantt_date_begin, 5, 2),substr($gantt_date_begin, 8, 2),substr($gantt_date_begin, 0, 4));

      if (isset($gantt_date_end))   $definitions['limit']['end']   = mktime(0,0,0,substr($gantt_date_end, 5, 2),substr($gantt_date_end, 8, 2),substr($gantt_date_end, 0, 4));

      $definitions['locale'] = substr($CFG_GLPI["language"],0,2);
      $definitions['today']['data']= time();        
      $definitions['title_string'] = self::dropAccent((strlen($title)>60) ? substr($title,0,58)."..." : $title);

      for ($i=0 ; $i<count($gdata) ; $i++) {            

         if ($gdata[$i]["type"]=='group') { // Groupe 
            $definitions['groups']['group'][$gdata[$i]["projet"]]['name'] = self::dropAccent((strlen($gdata[$i]["name"])>60) ? substr($gdata[$i]["name"],0,58)."..." : $gdata[$i]["name"]);
            $definitions['groups']['group'][$gdata[$i]["projet"]]['bg_color'] = $gdata[$i]["bg_color"];
            $definitions['groups']['group'][$gdata[$i]["projet"]]['start'] = mktime(0,0,0,substr($gdata[$i]["date_begin"], 5, 2),substr($gdata[$i]["date_begin"], 8, 2),substr($gdata[$i]["date_begin"], 0, 4));
            $definitions['groups']['group'][$gdata[$i]["projet"]]['end'] = mktime(0,0,0,substr($gdata[$i]["date_end"], 5, 2),substr($gdata[$i]["date_end"], 8, 2),substr($gdata[$i]["date_end"], 0, 4));
            if (isset($gdata[$i]["advance"])) 
               $definitions['groups']['group'][$gdata[$i]["projet"]]['progress'] = $gdata[$i]["advance"];
            
         } else if ($gdata[$i]["type"]=='phase') { // Tache
            $definitions['groups']['group'][$gdata[$i]["projet"]]['phase'][$gdata[$i]["task"]] = $gdata[$i]["task"];
            $definitions['planned']['phase'][$gdata[$i]["task"]]['name'] = self::dropAccent((strlen($gdata[$i]["name"])>60) ? substr($gdata[$i]["name"],0,58)."..." : $gdata[$i]["name"]);

            $definitions['planned']['phase'][$gdata[$i]["task"]]['start'] = mktime(0,0,0,substr($gdata[$i]["begin"], 5, 2),substr($gdata[$i]["begin"], 8, 2),substr($gdata[$i]["begin"], 0, 4));
            $definitions['planned']['phase'][$gdata[$i]["task"]]['end'] = mktime(0,0,0,substr($gdata[$i]["end"], 5, 2),substr($gdata[$i]["end"], 8, 2),substr($gdata[$i]["end"], 0, 4));
            $definitions['planned']['phase'][$gdata[$i]["task"]]['bg_color']=$gdata[$i]["bg_color"];
            /*if ($gdata[$i]["planned"]!='1') {
               $definitions['planned_adjusted']['phase'][$gdata[$i]["task"]]['start'] = mktime(0,0,0,substr($gdata[$i]["date_begin"], 5, 2),substr($gdata[$i]["date_begin"], 8, 2),substr($gdata[$i]["date_begin"], 0, 4));
               $definitions['planned_adjusted']['phase'][$gdata[$i]["task"]]['end'] = mktime(0,0,0,substr($gdata[$i]["date_end"], 5, 2),substr($gdata[$i]["date_end"], 8, 2),substr($gdata[$i]["date_end"], 0, 4));
               $definitions['planned_adjusted']['phase'][$gdata[$i]["task"]]['color']=$gdata[$i]["bg_color"];
            }*/
            //if (isset($gdata[$i]["realstart"])) $definitions['real']['phase'][$gdata[$i]["projet"]]['start'] = mktime(0,0,0,substr($gdata[$i][9], 5, 2),substr($gdata[$i][9], 8, 2),substr($gdata[$i][9], 0, 4));
            //if (isset($gdata[$i]["realend"])) $definitions['real']['phase'][$gdata[$i]["projet"]]['end'] = mktime(0,0,0,substr($gdata[$i][10], 5, 2),substr($gdata[$i][10], 8, 2),substr($gdata[$i][10], 0, 4));
            if (isset($gdata[$i]["advance"])) 
                  $definitions['progress']['phase'][$gdata[$i]["task"]]['progress']=$gdata[$i]["advance"];
                  
         } else if ($gdata[$i]["type"]=='milestone') { // Point Important
            $definitions['groups']['group'][$gdata[$i]["projet"]]['phase'][$gdata[$i]["task"]]=$gdata[$i]["task"];
            $definitions['milestone']['phase'][$gdata[$i]["task"]]['title']=self::dropAccent((strlen($gdata[$i]["name"])>27) ? substr($gdata[$i]["name"],0,24)."..." : $gdata[$i]["name"]);
            $definitions['milestone']['phase'][$gdata[$i]["task"]]['data']= mktime(0,0,0,substr($gdata[$i]["date_begin"], 5, 2),substr($gdata[$i]["date_begin"], 8, 2),substr($gdata[$i]["date_begin"], 0, 4));
         } else if ($gdata[$i]["type"]=='dependency') { // Dependance
            $definitions['dependency'][$gdata[$i]["projet"]]['type']= 1;
            $definitions['dependency'][$gdata[$i]["projet"]]['phase_from']=$gdata[$i]["date_begin"];
            $definitions['dependency'][$gdata[$i]["projet"]]['phase_to']=$gdata[$i]["name"];
         }
      }

      $ImgName = sprintf("gantt-%08x.png", rand());

      $definitions['image']['type']= 'png'; 
      $definitions['image']['filename'] = GLPI_PLUGIN_DOC_DIR."/projet/".$ImgName;

      new gantt($definitions);

      return $ImgName;

   }
   
   /**
    * Show for PDF an projet
    * 
    * @param $pdf object for the output
    * @param $ID of the projet
    */
   function show_PDF ($pdf) {
      global $LANG;
      
      $pdf->setColumnsSize(100);
      $col1 = '<b>'.$LANG["common"][2].' '.$this->fields['id'].'</b>';
      $pdf->displayTitle($col1);
      
      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_projet'][0].' :</i></b> '.$this->fields['name']);
      $pdf->setColumnsSize(50,50);
      $pdf->displayLine(
         '<b><i>'.$LANG['common'][34].' :</i></b> '.Html::clean(getUserName($this->fields["users_id"])),
         '<b><i>'.$LANG['common'][35].' :</i></b> '.Html::clean(Dropdown::getDropdownName('glpi_groups',$this->fields["groups_id"])));
      
      $pdf->displayLine(
         '<b><i>'.$LANG['search'][8].' :</i></b> '.Html::convDate($this->fields["date_begin"]),
         '<b><i>'.$LANG['search'][9].' :</i></b> '.Html::convDate($this->fields["date_end"]));
      
      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_projet'][19].' :</i></b> '.Dropdown::getDropdownName("glpi_plugin_projet_projetstates",$this->fields['plugin_projet_projetstates_id']),
         '<b><i>'.$LANG['plugin_projet'][47].' :</i></b> '.PluginProjetProjet::displayProgressBar('100',$this->fields["advance"],array("simple"=>true)));

      $pdf->setColumnsSize(100);

      $pdf->displayText('<b><i>'.$LANG['plugin_projet'][2].' :</i></b>', $this->fields['comment']);
      
      $pdf->setColumnsSize(100);

      $pdf->displayText('<b><i>'.$LANG['plugin_projet'][10].' :</i></b>', $this->fields['description']);
      
      $pdf->displaySpace();
   }
   
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {
      
      if ($item->getType()=='PluginProjetProjet') {
         if ($tab == 1) {
            
            PluginProjetProjet_Projet::pdfHierarchyForProjet($pdf, $item);
            PluginProjetProjet_Projet::pdfHierarchyForProjet($pdf, $item, 1);
            
         } else if ($tab == 2) {
            
            self::pdfGanttForProjet($pdf, $item);
            
         } else if ($tab == 3) {
            
            PluginProjetProjet_Item::pdfForProjet($pdf, $item);
         }
         
      } else {
         return false;
      }
      return true;
   }
   
   /**
    * Show for PDF an projet
    * 
    * @param $pdf object for the output
    * @param $ID of the projet
    */
   static function pdfGanttForProjet(PluginPdfSimplePDF $pdf, PluginProjetProjet $appli) {
       global $LANG,$CFG_GLPI,$gtitle,$gdata,$gconst,$gdate_begin,$gdate_end;
      
      $ID = $appli->fields['id'];

      if (!$appli->can($ID,"r")) {
         return false;
      }
      
      if (!plugin_projet_haveRight("projet","r")) {
         return false;
      }
      
      //nom
      $gantt_p_name= $appli->fields["name"];
      //type de gantt    
      $int = hexdec(PluginProjetProjetState::getStatusColor($appli->fields["plugin_projet_projetstates_id"]));
      $gantt_p_bgcolor = array(0xFF & ($int >> 0x10), 0xFF & ($int >> 0x8), 0xFF & $int);
            
      $gantt_p_date_begin=date("Y-m-d");
      $gantt_p_date_end=date("Y-m-d");
      if (!empty($appli->fields["date_begin"])) {
         $gantt_p_date_begin=$appli->fields["date_begin"];
      }
      if (!empty($appli->fields["date_end"])) {
         $gantt_p_date_end=$appli->fields["date_end"];
      }
      
      $gdata[]=array("type"=>'group',
                     "projet"=>$ID,
                     "name"=>$gantt_p_name,
                     "date_begin"=>$gantt_p_date_begin,
                     "date_end"=>$gantt_p_date_end,
                     "advance"=>$appli->fields["advance"],
                     "bg_color"=>$gantt_p_bgcolor);

      PluginProjetTask::showTaskTreeGantt(array('plugin_projet_projets_id'=>$ID));
      
      if (!empty($gdate_begin) && !empty($gdate_end)) {
         $gtitle=$gtitle."<DateBeg> / <DateEnd>";
         $gdate_begin=date("Y",$gdate_begin)."-".date("m",$gdate_begin)."-".date("d",$gdate_begin);
         $gdate_end=date("Y",$gdate_end)."-".date("m",$gdate_end)."-".date("d",$gdate_end);
      }
      $ImgName=self::writeGantt($gtitle,$gdata,$gconst,$gdate_begin,$gdate_end);

      $image = GLPI_PLUGIN_DOC_DIR."/projet/".$ImgName;

      $pdf->newPage();

      $pdf->setColumnsSize(100);
      $pdf->displayTitle('<b>'.$LANG['plugin_projet']['title'][5].'</b>');
      $size = GetImageSize($image);
      $src_w = $size[0];
      $src_h = $size[1];
      $pdf->addPngFromFile($image,$src_w/2,$src_h/2);
      $pdf->displaySpace();
      unlink($image);

   }
   
   static function commonListHeader($output_type=HTML_OUTPUT, $canedit) {
      global $LANG;

      // New Line for Header Items Line
      echo Search::showNewLine($output_type);
      // $show_sort if
      $header_num = 1;

      $items = array();

      $items[$LANG['plugin_projet'][0]] = "glpi_plugin_projet_projets.name";
      if (Session::isMultiEntitiesMode()) {
         $items[$LANG['entity'][0]] = "glpi_entities.completename";
      }
      $items[$LANG['plugin_projet'][10]] = "glpi_plugin_projet_projets.description";
      $items[$LANG['plugin_projet'][47]]   = "glpi_plugin_projet_projets.advance";
      $items[$LANG['search'][8]]       = "glpi_plugin_projet_projets.date_begin";
      $items[$LANG['search'][9]]   = "glpi_plugin_projet_projets.date_end";
      
      foreach ($items as $key => $val) {
         $issort = 0;
         $link = "";
         echo Search::showHeaderItem($output_type,$key,$header_num,$link);
      }
      if ($canedit) {
         echo "<th>&nbsp;</th>";
      }
      // End Line for column headers
      echo Search::showEndLine($output_type);
   }
}

?>