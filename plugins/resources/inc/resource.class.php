<?php
/*
 * @version $Id: resource.class.php 480 2012-11-09 tynet $
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

class PluginResourcesResource extends CommonDBTM {

   static $types = array('Computer','Monitor','NetworkEquipment','Peripheral',
         'Phone', 'Printer', 'Software', 'ConsumableItem','User');

	public $dohistory=true;

   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['plugin_resources']['title'][1];
      }
      return $LANG['plugin_resources'][1];
   }

   function canCreate() {
      return plugin_resources_haveRight('resources', 'w');
   }

   function canView() {
      return plugin_resources_haveRight('resources', 'r');
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

   /**
    * Type than could be linked to a Resource
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

	function cleanDBonPurge() {

		$temp = new PluginResourcesResource_Item();
		$temp->deleteByCriteria(array('plugin_resources_resources_id' => $this->fields['id']));

      $temp = new PluginResourcesChoice();
      $temp->deleteByCriteria(array('plugin_resources_resources_id' => $this->fields['id']));

      $temp = new PluginResourcesTask();
      $temp->deleteByCriteria(array('plugin_resources_resources_id' => $this->fields['id']),1);

      $temp = new PluginResourcesEmployee();
      $temp->deleteByCriteria(array('plugin_resources_resources_id' => $this->fields['id']));

      $temp = new PluginResourcesReportConfig();
      $temp->deleteByCriteria(array('plugin_resources_resources_id' => $this->fields['id']));

      $temp = new PluginResourcesChecklist();
      $temp->deleteByCriteria(array('plugin_resources_resources_id' => $this->fields['id']));

      $temp = new PluginResourcesResourceResting();
      $temp->deleteByCriteria(array('plugin_resources_resources_id' => $this->fields['id']));

      $temp = new PluginResourcesResourceHoliday();
      $temp->deleteByCriteria(array('plugin_resources_resources_id' => $this->fields['id']));
   }

	/**
    * Hook called After an item is purge
    */
   static function cleanForItem(CommonDBTM $item) {

      $type = get_class($item);
      $temp = new PluginResourcesResource_Item();
      $temp->deleteByCriteria(array('itemtype' => $type,
                                       'items_id' => $item->getField('id')));

      $task = new PluginResourcesTask_Item();
      $task->deleteByCriteria(array('itemtype' => $type,
                                       'items_id' => $item->getField('id')));
   }

   function getSearchOptions() {
      global $LANG;

      $tab = array();

      $tab['common'] = $LANG['plugin_resources']['title'][1];

      $tab[1]['table']=$this->getTable();
      $tab[1]['field']='name';
      $tab[1]['name']=$LANG['plugin_resources'][8];
      $tab[1]['datatype']='itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      $tab[1]['massiveaction'] = false;
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[1]['searchtype'] = 'contains';

      $tab[2]['table']=$this->getTable();
      $tab[2]['field']='firstname';
      $tab[2]['name']=$LANG['plugin_resources'][18];
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[2]['searchtype'] = 'contains';

      $tab[3]['table']='glpi_plugin_resources_contracttypes';
      $tab[3]['field']='name';
      $tab[3]['name']=$LANG['plugin_resources'][20];

      $tab[4]['table']='glpi_users';
      $tab[4]['field']='name';
      $tab[4]['name']=$LANG['plugin_resources'][47];
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[4]['searchtype'] = 'contains';

      $tab[5]['table']=$this->getTable();
      $tab[5]['field']='date_begin';
      $tab[5]['name']=$LANG['plugin_resources'][11];
      $tab[5]['datatype']='date';

      $tab[6]['table']=$this->getTable();
      $tab[6]['field']='date_end';
      $tab[6]['name']=$LANG['plugin_resources'][13];
      $tab[6]['datatype']='date';

      $tab[7]['table']=$this->getTable();
      $tab[7]['field']='comment';
      $tab[7]['name']=$LANG['plugin_resources'][12];
      $tab[7]['datatype']='text';

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $tab[8]['table']='glpi_plugin_resources_resources_items';
         $tab[8]['field']='items_id';
         $tab[8]['name']=$LANG['plugin_resources'][7];
         $tab[8]['forcegroupby']=true;
         $tab[8]['massiveaction'] = false;
         $tab[8]['nosearch']=true;
      }

      $tab[9]['table']=$this->getTable();
      $tab[9]['field']='date_declaration';
      $tab[9]['name']=$LANG['plugin_resources'][49];
      $tab[9]['datatype']='date';
      $tab[9]['massiveaction'] = false;

      $tab[10]['table']='glpi_users';
      $tab[10]['field']='name';
      $tab[10]['linkfield']='users_id_recipient';
      $tab[10]['name']=$LANG['plugin_resources'][50];
      $tab[10]['massiveaction'] = false;
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[10]['searchtype'] = 'contains';

      $tab[11]['table']='glpi_plugin_resources_departments';
      $tab[11]['field']='name';
      $tab[11]['name']=$LANG['plugin_resources'][14];

      $tab[12]['table']='glpi_locations';
      $tab[12]['field']='completename';
      $tab[12]['name']=$LANG['plugin_resources'][5];

      $tab[13]['table']=$this->getTable();
      $tab[13]['field']='is_leaving';
      $tab[13]['name']=$LANG['plugin_resources'][58];
      $tab[13]['datatype']='bool';

      $tab[14]['table']='glpi_users';
      $tab[14]['field']='name';
      $tab[14]['linkfield']='users_id_recipient_leaving';
      $tab[14]['name']=$LANG['plugin_resources'][73];
      $tab[14]['massiveaction'] = false;
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $tab[14]['searchtype'] = 'contains';

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $tab[15]['table']=$this->getTable();
         $tab[15]['field']='is_helpdesk_visible';
         $tab[15]['name']=$LANG['software'][46];
         $tab[15]['datatype']='bool';
      }

      $tab[16]['table']=$this->getTable();
      $tab[16]['field']='date_mod';
      $tab[16]['name']=$LANG['common'][26];
      $tab[16]['datatype']='datetime';
      $tab[16]['massiveaction'] = false;

      $tab[17]['table']='glpi_plugin_resources_resourcestates';
      $tab[17]['field']='name';
      $tab[17]['name']=$LANG['plugin_resources'][24];

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $tab[18]['table']=$this->getTable();
         $tab[18]['field']='picture';
         $tab[18]['name']=$LANG['plugin_resources'][32];
         $tab[18]['massiveaction'] = false;

         $tab[19]['table']=$this->getTable();
         $tab[19]['field']='is_recursive';
         $tab[19]['name']=$LANG['entity'][9];
         $tab[19]['datatype']='bool';
      }

      $tab[20]['table']=$this->getTable();
      $tab[20]['field']='quota';
      $tab[20]['name']=$LANG['plugin_resources'][4];
      $tab[20]['datatype'] = 'decimal';

      if (plugin_resources_haveRight('dropdown_public', 'r')){

         $tab[21]['table']='glpi_plugin_resources_resourcesituations';
         $tab[21]['field']='name';
         $tab[21]['name']=$LANG['plugin_resources'][75];
         $tab[21]['massiveaction'] = false;

         $tab[22]['table']='glpi_plugin_resources_contractnatures';
         $tab[22]['field']='name';
         $tab[22]['name']=$LANG['plugin_resources'][76];
         $tab[22]['massiveaction'] = false;

         $tab[23]['table']='glpi_plugin_resources_ranks';
         $tab[23]['field']='name';
         $tab[23]['name']=$LANG['plugin_resources'][77];
         $tab[23]['massiveaction'] = false;

         $tab[24]['table']='glpi_plugin_resources_resourcespecialities';
         $tab[24]['field']='name';
         $tab[24]['name']=$LANG['plugin_resources'][78];
         $tab[24]['massiveaction'] = false;
      }

      $tab[25]['table']='glpi_plugin_resources_leavingreasons';
      $tab[25]['field']='name';
      $tab[25]['name']=$LANG['plugin_resources'][79];

      $tab[31]['table']=$this->getTable();
      $tab[31]['field']='id';
      $tab[31]['name']=$LANG['common'][2];
      $tab[31]['massiveaction'] = false;

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         $tab[80]['table']='glpi_entities';
         $tab[80]['field']='completename';
         $tab[80]['name']=$LANG['entity'][0];
      }
      return $tab;
   }

   function defineTabs($options=array()) {

		$ong = array();

      $this->addStandardTab('PluginResourcesResource_Item', $ong,$options);
      $this->addStandardTab('PluginResourcesChoice', $ong,$options);
      $this->addStandardTab('PluginResourcesEmployment', $ong, $options);
      $this->addStandardTab('PluginResourcesEmployee',$ong,$options);
      $this->addStandardTab('PluginResourcesChecklist',$ong,$options);
      $this->addStandardTab('PluginResourcesTask',$ong,$options);

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         
         $this->addStandardTab('PluginResourcesReportConfig',$ong,$options);
         $this->addStandardTab('Document',$ong,$options);
      
         if (!isset($options['withtemplate']) || empty($options['withtemplate'])) {
            $this->addStandardTab('Ticket',$ong,$options);
            $this->addStandardTab('Item_Problem', $ong, $options);
         }
      
         $this->addStandardTab('Note',$ong,$options);
         $this->addStandardTab('Log',$ong,$options);
      }
      return $ong;
	}

   function checkRequiredFields($input) {

      $need=array();
      $rulecollection = new PluginResourcesRuleContracttypeCollection($input['entities_id']);

      $fields=array();
      $fields=$rulecollection->processAllRules($input,$fields,array());

      $rank = new PluginResourcesRank();

      $field=array();
      foreach ($fields as $key=>$val) {
            $required=explode("requiredfields_", $key);
            if (isset($required[1]))
               $field[]=$required[1];
      }

      if (count($field) > 0)
         foreach ($field as $key=>$val) {
            if (!isset($input[$val]) || empty($input[$val]) || is_null($input[$val]) || $input[$val] == "NULL"){
               if (!$rank->canCreate() && in_array($val,
                  array('plugin_resources_ranks_id', 'plugin_resources_resourcesituations_id'))){
               } else {
                  $need[]=$val;
               }
            }
         }

      return $need;
	}

	function prepareInputForAdd($input) {
      global $LANG;
		// dump status

		if (!isset ($input["is_template"])) {

         $required = $this->checkRequiredFields($input);

         if(count($required) > 0) {
            Session::addMessageAfterRedirect($LANG['plugin_resources']['helpdesk'][6], false, ERROR);
            return array ();
         }
      }

		if (isset($input['date_end'])&&empty($input['date_end'])) $input['date_end']='NULL';
		if (!isset($input['plugin_resources_resourcestates_id'])
		|| empty($input['plugin_resources_resourcestates_id'])) $input['plugin_resources_resourcestates_id']='0';
		//Add picture of the resource
		$input['picture']="NULL";
		if (isset($_FILES) && isset($_FILES['picture']) && $_FILES['picture']['size']>0) {

         if ($_FILES['picture']['type']=="image/jpeg" || $_FILES['picture']['type']=="image/pjpeg") {
            $max_size=Toolbox::return_bytes_from_ini_vars(ini_get("upload_max_filesize"));
            if ($_FILES['picture']['size'] <= $max_size) {

               if (is_writable (GLPI_PLUGIN_DOC_DIR."/resources/")) {
                  $input['picture']= $this->addPhoto($this);
               }
            } else {
               Session::addMessageAfterRedirect($LANG['document'][46],false,ERROR);
            }
         } else {
            Session::addMessageAfterRedirect($LANG['document'][44]. " : ".$_FILES['picture']['type'],false,ERROR);
         }
      }

		if (isset($input["id"]) && $input["id"]>0) {
         $input["_oldID"]=$input["id"];
      }
      unset($input['id']);

		return $input;
	}

	function post_addItem() {
		global $CFG_GLPI;

		// Manage add from template
		if (isset($this->input["_oldID"])) {

         // ADD choices
         $PluginResourcesChoice= new PluginResourcesChoice();
         $restrict = "`plugin_resources_resources_id` = '".$this->input["_oldID"]."'";
         $choices = getAllDatasFromTable("glpi_plugin_resources_choices",$restrict);
         if (!empty($choices)) {
            foreach ($choices as $choice) {
               $values['plugin_resources_resources_id'] = $this->fields['id'];
               $values['plugin_resources_choiceitems_id'] = $choice["plugin_resources_choiceitems_id"];
               $PluginResourcesChoice->addHelpdeskItem($values);
            }
			}
			// ADD items
			$PluginResourcesResource_Item= new PluginResourcesResource_Item();
			$restrict = "`plugin_resources_resources_id` = '".$this->input["_oldID"]."'";
         $items = getAllDatasFromTable("glpi_plugin_resources_resources_items",$restrict);
         if (!empty($items)) {
            foreach ($items as $item) {
               $PluginResourcesResource_Item->add(array('plugin_resources_resources_id' => $this->fields['id'],
                        'itemtype' => $item["itemtype"],
                        'items_id' => $item["items_id"]));
            }
			}
			// ADD reports
			$PluginResourcesReportConfig= new PluginResourcesReportConfig();
			$restrict = "`plugin_resources_resources_id` = '".$this->input["_oldID"]."'";
         $reports = getAllDatasFromTable("glpi_plugin_resources_reportconfigs",$restrict);
         if (!empty($reports)) {
            foreach ($reports as $report) {
               $PluginResourcesReportConfig->add(array('plugin_resources_resources_id' => $this->fields['id'],
                        'entities_id' => $this->fields['entities_id'],
                        'comment' => addslashes($report["comment"]),
                        'information' => addslashes($report["information"])));
            }
			}
			//manage template from helpdesk (no employee to add : resource.form.php)
			if (!isset($this->input["add_from_helpdesk"])) {
            $PluginResourcesEmployee= new PluginResourcesEmployee();
            $restrict = "`plugin_resources_resources_id` = '".$this->input["_oldID"]."'";
            $employees = getAllDatasFromTable("glpi_plugin_resources_employees",$restrict);
            if (!empty($employees)) {
               foreach ($employees as $employee) {
                  $PluginResourcesEmployee->add(array('plugin_resources_resources_id' => $this->fields['id'],
                        'plugin_resources_employers_id' => $employee["plugin_resources_employers_id"],
                        'plugin_resources_clients_id' => $employee["plugin_resources_clients_id"]));
               }
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
			// ADD tasks
			$PluginResourcesTask = new PluginResourcesTask();
			$PluginResourcesTask_Item = new PluginResourcesTask_Item();
			$restrict = "`plugin_resources_resources_id` = '".$this->input["_oldID"]."'
                     AND is_deleted != 1";
         $tasks = getAllDatasFromTable("glpi_plugin_resources_tasks",$restrict);
         if (!empty($tasks)) {
            foreach ($tasks as $task) {
               $values=$task;
               $taskid = $values["id"];
               unset($values["id"]);
               $values["plugin_resources_resources_id"]=$this->fields['id'];
               $values["name"] = addslashes($task["name"]);
					$values["comment"] = addslashes($task["comment"]);

               $newid = $PluginResourcesTask->add($values);

               $restrictitems = "`plugin_resources_tasks_id` = '".$taskid."'";
               $tasksitems = getAllDatasFromTable("glpi_plugin_resources_tasks_items",$restrictitems);
               if (!empty($tasksitems)) {
                  foreach ($tasksitems as $tasksitem) {
                     $PluginResourcesTask_Item->add(array('plugin_resources_tasks_id' => $newid,
                        'itemtype' => $tasksitem["itemtype"],
                        'items_id' => $tasksitem["items_id"]));
                  }
               }
            }
         }
		}

		//Launch notification

		if (isset($this->input['withtemplate'])
          && $this->input["withtemplate"]!=1
          && isset($this->input['send_notification'])
          && $this->input['send_notification']==1) {
			if ($CFG_GLPI["use_mailing"]) {
            NotificationEvent::raiseEvent("new",$this);
         }
      }

      //ADD Checklists from rules
      $PluginResourcesChecklistconfig= new PluginResourcesChecklistconfig();
      $PluginResourcesChecklistconfig->addChecklistsFromRules($this,PluginResourcesChecklist::RESOURCES_CHECKLIST_IN);
      $PluginResourcesChecklistconfig->addChecklistsFromRules($this,PluginResourcesChecklist::RESOURCES_CHECKLIST_OUT);
	}

	function replace_accents($str, $charset='utf-8')
   {
      $str = htmlentities($str, ENT_NOQUOTES, $charset);

      $str = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml)\;#', '\1', $str);
      $str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str); // pour les ligatures e.g. '&oelig;'
      $str = preg_replace('#\&[^;]+\;#', '', $str); // supprime les autres caractères

      return $str;
   }

   function addPhoto($class)
   {
      $uploadedfile= $_FILES['picture']['tmp_name'];
      $src = imagecreatefromjpeg($uploadedfile);

      list($width,$height)=getimagesize($uploadedfile);

      $newwidth=75;
      $newheight=($height/$width)*$newwidth;
      $tmp=imagecreatetruecolor($newwidth,$newheight);

      imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);
      $ext = strtolower(substr(strrchr($_FILES['picture']['name'], '.'), 1));
      $resources_name = str_replace(" ","", strtolower($class->fields["name"]));
      $resources_firstname = str_replace(" ","", strtolower($class->fields["firstname"]));
      $name = $resources_name."_".$resources_firstname.".".$ext;

      $name = $this->replace_accents($name);

      $tmpfile = GLPI_DOC_DIR."/_uploads/". $name;
      $filename = GLPI_PLUGIN_DOC_DIR."/resources/". $name;

      imagejpeg($tmp,$tmpfile,100);

      rename($tmpfile,$filename);
      //Document::renameForce($tmpfile, $filename);

      imagedestroy($src);
      imagedestroy($tmp);

      return $name;
   }

	function prepareInputForUpdate($input) {
		global $LANG;

		if (isset($input['date_begin'])&&empty($input['date_begin'])) $input['date_begin']='NULL';
		if (isset($input['date_end'])&&empty($input['date_end'])) $input['date_end']='NULL';

		//unset($input['picture']);
		$this->getFromDB($input["id"]);

		if (isset($_FILES) && isset($_FILES['picture']) && $_FILES['picture']['size']>0) {

         if ($_FILES['picture']['type']=="image/jpeg" || $_FILES['picture']['type']=="image/pjpeg") {
            $max_size=Toolbox::return_bytes_from_ini_vars(ini_get("upload_max_filesize"));
            if ($_FILES['picture']['size'] <= $max_size) {

               $input['picture']= $this->addPhoto($this);

            } else {
               Session::addMessageAfterRedirect($LANG['document'][46],false,ERROR);
            }
         } else {
            Session::addMessageAfterRedirect($LANG['document'][44]. " : ".$_FILES['picture']['type'],false,ERROR);
         }
      }

		$input["_old_name"]=$this->fields["name"];
		$input["_old_firstname"]=$this->fields["firstname"];
		$input["_old_plugin_resources_contracttypes_id"]=$this->fields["plugin_resources_contracttypes_id"];
		$input["_old_users_id"]=$this->fields["users_id"];
		$input["_old_users_id_recipient"]=$this->fields["users_id_recipient"];
		$input["_old_date_declaration"]=$this->fields["date_declaration"];
		$input["_old_date_begin"]=$this->fields["date_begin"];
		$input["_old_date_end"]=$this->fields["date_end"];
      $input["_old_quota"]=$this->fields["quota"];
      $input["_old_plugin_resources_departments_id"]=$this->fields["plugin_resources_departments_id"];
		$input["_old_plugin_resources_resourcestates_id"]=$this->fields["plugin_resources_resourcestates_id"];
      $input["_old_plugin_resources_resourcesituations_id"]=$this->fields["plugin_resources_resourcesituations_id"];
      $input["_old_plugin_resources_contractnatures_id"]=$this->fields["plugin_resources_contractnatures_id"];
      $input["_old_plugin_resources_ranks_id"]=$this->fields["plugin_resources_ranks_id"];
      $input["_old_plugin_resources_resourcespecialities_id"]=$this->fields["plugin_resources_resourcespecialities_id"];
      $input["_old_locations_id"]=$this->fields["locations_id"];
		$input["_old_is_leaving"]=$this->fields["is_leaving"];
      $input["_old_plugin_resources_leavingreasons_id"]=$this->fields["plugin_resources_leavingreasons_id"];
      $input["_old_comment"]=$this->fields["comment"];

		return $input;
	}

	function pre_updateInDB() {
      global $LANG;

      $PluginResourcesResource_Item= new PluginResourcesResource_Item();
      $PluginResourcesChecklist= new PluginResourcesChecklist();
      //if leaving field is updated  && isset($this->input["withtemplate"]) && $this->input["withtemplate"]!=1

      $this->input["checkbadge"]=0;

		if (isset($this->input["is_leaving"]) && $this->input["is_leaving"]==1 && in_array("is_leaving", $this->updates)) {

			if ((!(isset($this->input["date_end"]))
               || $this->input["date_end"]=='NULL')
                  || (!(isset($this->fields["date_end"]))
                  || $this->fields["date_end"]=='NULL')) {

            Session::addMessageAfterRedirect($LANG['plugin_resources'][3],false,ERROR);
            Html::back();

         } else {
				$this->fields["users_id_recipient_leaving"]=Session::getLoginUserID();
				$this->updates[]="users_id_recipient_leaving";

				$resources_checklist=PluginResourcesChecklist::checkIfChecklistExist($this->fields["id"]);
				if (!$resources_checklist) {
               $PluginResourcesChecklistconfig= new PluginResourcesChecklistconfig();
               $PluginResourcesChecklistconfig->addChecklistsFromRules($this,PluginResourcesChecklist::RESOURCES_CHECKLIST_OUT);
				}
         }
		}

		//if location field is updated
		if (isset ($this->fields["locations_id"])
         && isset ($this->input["_old_locations_id"])
         && !isset ($this->input["_UpdateFromUser_"])
         && $this->fields["locations_id"]!=$this->input["_old_locations_id"]) {

			$PluginResourcesResource_Item->updateLocation($this->fields,"PluginResourcesResource");
		}

      $this->input["addchecklist"]=0;
		if (isset ($this->fields["plugin_resources_contracttypes_id"])
         && isset ($this->input["_old_plugin_resources_contracttypes_id"])
         && $this->fields["plugin_resources_contracttypes_id"]!=$this->input["_old_plugin_resources_contracttypes_id"])
			$this->input["addchecklist"]=1;

   }

	function post_updateItem($history=1) {
		global $CFG_GLPI;

      $PluginResourcesChecklist= new PluginResourcesChecklist();
		if (isset ($this->input["addchecklist"])
         && $this->input["addchecklist"]==1) {

         $PluginResourcesChecklist->deleteByCriteria(array('plugin_resources_resources_id' => $this->fields["id"]));

         $PluginResourcesChecklistconfig= new PluginResourcesChecklistconfig();
         $PluginResourcesChecklistconfig->addChecklistsFromRules($this,
                                                   PluginResourcesChecklist::RESOURCES_CHECKLIST_IN);
         $PluginResourcesChecklistconfig->addChecklistsFromRules($this,
                                                   PluginResourcesChecklist::RESOURCES_CHECKLIST_OUT);
		}
		$status = "update";
		if (isset($this->fields["is_leaving"]) && !empty($this->fields["is_leaving"])) {
         $status = "LeavingResource";
         $PluginResourcesResource_Item= new PluginResourcesResource_Item();
         $badge = $PluginResourcesResource_Item->searchAssociatedBadge($this->fields["id"]);
         if ($badge)
            $this->input["checkbadge"]=1;

         //when a resource is leaving, current employment get default state
         $PluginResourcesEmployment= new PluginResourcesEmployment();
         $default = PluginResourcesEmploymentState::getDefault();
         // only current employment
         $restrict = "`plugin_resources_resources_id` = '".$this->input["id"]."'
                     AND ((`begin_date` < '".$this->input['date_end']."'
                           OR `begin_date` IS NULL)
                           AND (`end_date` > '".$this->input['date_end']."'
                                 OR `end_date` IS NULL)) ";
         $employments = getAllDatasFromTable("glpi_plugin_resources_employments",$restrict);
         if (!empty($employments)) {
            foreach ($employments as $employment) {
               $values = array('plugin_resources_employmentstates_id'=> $default,
                  //TODO voir avec XACA si ok
//                               'end_date' => $this->input['date_end'],
                               'id'=> $employment['id']
               );
               $PluginResourcesEmployment->update($values);
            }
         }
      }

      $picture = array(0 => "picture",1 => "date_mod");
		if (count($this->updates)
         && array_diff($this->updates,$picture)
         && isset($this->input["withtemplate"])
         && $this->input["withtemplate"]!=1) {

         if ($CFG_GLPI["use_mailing"]
            && isset($this->input['send_notification'])
            && $this->input['send_notification']==1) {
            NotificationEvent::raiseEvent($status,$this);
         }
      }
	}

	function pre_deleteItem() {
      global $CFG_GLPI;


      if (isset($this->input['picture'])) {
         $filename = GLPI_PLUGIN_DOC_DIR."/resources/". $this->input['picture'];
         unlink($filename);
      }
      if ($CFG_GLPI["use_mailing"]
         && $this->fields["is_template"]!=1
         && isset($this->input['delete'])
         && isset($this->input['send_notification'])
         && $this->input['send_notification']==1) {
         NotificationEvent::raiseEvent("delete",$this);
      }

      return true;
   }

   function dropdownTemplate($name, $value = 0) {
      global $LANG;

      $restrict = "`is_template` = '1'";
      $restrict.=getEntitiesRestrictRequest(" AND ",$this->getTable(),'','',$this->maybeRecursive());
      $restrict.=" GROUP BY `template_name`
                  ORDER BY `template_name`";
      $templates = getAllDatasFromTable($this->getTable(),$restrict);

      $option[-1] = $LANG["plugin_resources"]["wizard"][5];

      if (!empty($templates)) {
         foreach ($templates as $template) {
            $id_display ="";
            if ($_SESSION["glpiis_ids_visible"]||empty($template["template_name"]))
               $id_display = " (".$template["id"].")";
            $option[$template["id"]] = $template["template_name"].$id_display;
         }
      }
      return Dropdown::showFromArray($name, $option, array('value'  => $value));
   }

   /*
    * Return the SQL command to retrieve linked object
    *
    * @return a SQL command which return a set of (itemtype, items_id)
    */
   function getSelectLinkedItem () {
      return "SELECT `itemtype`, `items_id`
              FROM `glpi_plugin_resources_resources_items`
              WHERE `plugin_resources_resources_id`='" . $this->fields['id']."'";
   }

	function showForm($ID, $options=array("")) {
      global $LANG,$CFG_GLPI;

      if (!$this->canView()) return false;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }

      $rank = new PluginResourcesRank();

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

      //if ($_SESSION['glpiactiveprofile']['interface'] == 'central')
         $this->showTabs($options);
      $options['formoptions'] = " enctype='multipart/form-data'";
      $this->showFormHeader($options);

      $required = array();
      if (isset($this->fields["entities_id"])) {
         $input['entities_id'] = $this->fields["entities_id"];
      } else {
         $input['entities_id'] = $_SESSION['glpiactive_entity'];
      }
      $input['plugin_resources_contracttypes_id'] = $this->fields["plugin_resources_contracttypes_id"];
      $required = $this->checkRequiredFields($input);
      $alert = " class='red' ";

      echo "<tr class='tab_bg_1'>";
      echo "<td";
      if(in_array("name", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][8]."</td>";
      echo "<td>";
      $option = array('option' => "onChange=\"javascript:this.value=this.value.toUpperCase();\"");
      Html::autocompletionTextField($this,"name",$option);
      echo "</td>";

      echo "<td rowspan='5' colspan='2' align='center'>";
      if (isset($this->fields["picture"]) && !empty($this->fields["picture"])) {
         $path = GLPI_PLUGIN_DOC_DIR."/resources/".$this->fields["picture"];
         if (file_exists($path)) {
            echo "<object data='".$CFG_GLPI['root_doc']."/plugins/resources/front/picture.send.php?file=".$this->fields["picture"]."'>
             <param name='src' value='".$CFG_GLPI['root_doc'].
               "/plugins/resources/front/picture.send.php?file=".$this->fields["picture"]."'>
            </object> ";
            echo "<input type='hidden' name='picture' value='".$this->fields["picture"]."'>";
         } else {
            echo "<img src='../pics/nobody.png'>";
         }
      } else {
         echo "<img src='../pics/nobody.png'>";
      }

      echo "<br>".$LANG["plugin_resources"]["wizard"][10]."<br>";
      echo "<input type='file' name='picture' value=\"".
         $this->fields["picture"]."\" size='25'>&nbsp;";
      echo "(".Document::getMaxUploadSize().")&nbsp;";
      if (isset($this->fields["picture"]) && !empty($this->fields["picture"])) {
         Html::showSimpleForm($CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php",
            'delete_picture',
            $LANG['buttons'][6],
            array('id' => $ID,
               'picture' => $this->fields["picture"]),
            "../pics/puce-delete2.png");
      }
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td";
      if(in_array("firstname", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][18]."</td>";
      echo "<td>";
      $option = array('option' => "onChange='First2UpperCase(this.value);' style='text-transform:capitalize;'");
      Html::autocompletionTextField($this,"firstname",$option);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_resources'][24]."</td>";
      echo "<td>";
      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         Dropdown::show('PluginResourcesResourceState',
            array('value'  => $this->fields["plugin_resources_resourcestates_id"],
                  'entity' => $this->fields["entities_id"]));
      } else {
         echo Dropdown::getDropdownName("glpi_plugin_resources_resourcestates",$this->fields["plugin_resources_resourcestates_id"]);
      }
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_resources'][20]."</td>";
      echo "<td>";
      Dropdown::show('PluginResourcesContractType',
         array('value'  => $this->fields["plugin_resources_contracttypes_id"],
               'entity' => $this->fields["entities_id"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td";
      if(in_array("quota", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][4]."</td>";
      echo "<td>";
      echo "<input type='text' name='quota' value='".Html::formatNumber($this->fields["quota"], true, 4).
         "' size='14'>";
      echo "</td>";
      echo "</tr>";

      echo "</table><table class='tab_cadre_fixe'>";

      if ($rank->canView()) {
         echo "<tr class='tab_bg_1'>";
         echo "<td";
         if(in_array("plugin_resources_resourcesituations_id", $required))
            echo $alert;
         echo ">";
         echo $LANG['plugin_resources'][75]."</td>";
         echo "<td>";

         $params = array('name' => 'plugin_resources_resourcesituations_id',
            'value' => $this->fields['plugin_resources_resourcesituations_id'],
            'entity' => $this->fields["entities_id"],
            'action' => $CFG_GLPI["root_doc"]."/plugins/resources/ajax/dropdownContractnature.php",
            'span' => 'span_contractnature'
         );
         self::showGenericDropdown('PluginResourcesResourceSituation',$params);
         echo "<td";
         if(in_array("plugin_resources_contractnatures_id", $required))
            echo $alert;
         echo ">";
         echo $LANG['plugin_resources'][76]."</td>";
         echo "<td>";
         echo "<span id='span_contractnature' name='span_contractnature'>";
         if ($this->fields["plugin_resources_contractnatures_id"]>0) {
            echo Dropdown::getDropdownName('glpi_plugin_resources_contractnatures',
               $this->fields["plugin_resources_contractnatures_id"]);
         } else {
            echo $LANG['job'][32];
         }
         echo "</span>";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td";
         if(in_array("plugin_resources_ranks_id", $required))
            echo $alert;
         echo ">";
         echo $LANG['plugin_resources'][77]."</td>";
         echo "<td>";

         $params = array('name' => 'plugin_resources_ranks_id',
            'value' => $this->fields['plugin_resources_ranks_id'],
            'entity' => $this->fields["entities_id"],
            'action' => $CFG_GLPI["root_doc"]."/plugins/resources/ajax/dropdownSpeciality.php",
            'span' => 'span_speciality'
         );
         self::showGenericDropdown('PluginResourcesRank',$params);
         echo "</td>";
         echo "<td";
         if(in_array("plugin_resources_resourcespecialities_id", $required))
            echo $alert;
         echo ">";
         echo $LANG['plugin_resources'][78]."</td>";
         echo "<td>";
         echo "<span id='span_speciality' name='span_speciality'>";
         if ($this->fields["plugin_resources_resourcespecialities_id"]>0) {
            echo Dropdown::getDropdownName('glpi_plugin_resources_resourcespecialities',
               $this->fields["plugin_resources_resourcespecialities_id"]);
         } else {
            echo $LANG['job'][32];
         }
         echo "</span>";
         echo "</td>";
         echo "</tr>";
         echo "</table><table class='tab_cadre_fixe'>";

      }

      echo "<tr class='tab_bg_1'>";
      echo "<td";
      if(in_array("locations_id", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][5]."</td>";
      echo "<td>";
      Dropdown::show('Location',
         array('value'  => $this->fields["locations_id"],
               'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td";
      if(in_array("plugin_resources_departments_id", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][14]."</td>";
      echo "<td>";
      Dropdown::show('PluginResourcesDepartment',
         array('value'  => $this->fields["plugin_resources_departments_id"],
               'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td";
      if(in_array("users_id", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][47]."</td>";
      echo "<td>";
      User::dropdown(array('value'  => $this->fields["users_id"],
         'name'  => "users_id",
         'entity' => $this->fields["entities_id"],
         'right'  => 'all'));
      echo "</td>";
      echo "<td";
      if(in_array("date_begin", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][11]."</td>";
      echo "<td>";
      Html::showDateFormItem("date_begin",$this->fields["date_begin"],true,true);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td colspan='4'>".$LANG['plugin_resources'][12]."</td></tr>";

      echo "<tr class='tab_bg_1'><td colspan='4'>";
      echo "<textarea cols='130' rows='4' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "<input type='hidden' name='withtemplate' value='".$options['withtemplate']."'>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2'>";
      if($ID && $options['withtemplate'] < 2) {
         echo $LANG['joblist'][11]." : ";
         echo Html::convDateTime($this->fields["date_declaration"]);
         echo " ".$LANG['plugin_resources']['setup'][1]." ";
         $users_id_recipient=new User();
         $users_id_recipient->getFromDB($this->fields["users_id_recipient"]);
         if ($this->canCreate() && $_SESSION['glpiactiveprofile']['interface'] == 'central') {

            User::dropdown(array('value'  => $this->fields["users_id_recipient"],
               'name'  => "users_id_recipient",
               'entity' => $this->fields["entities_id"],
               'right'  => 'all'));
         } else {
            echo $users_id_recipient->getName();
         }
      } else {
         echo "<input type='hidden' name='users_id_recipient' value=\"".Session::getLoginUserID()."\" >";
         echo "<input type='hidden' name='date_declaration' value=\"".$_SESSION["glpi_currenttime"]."\" >";
      }
      echo "</td>";

      echo "<td>" . $LANG['software'][46] . "</td><td>";

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      } else {
         echo Dropdown::getDropdownName($this->getTable(),$this->fields["is_helpdesk_visible"]);
      }
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td></td><td></td><td>".$LANG['plugin_resources']['mailing'][42]."</td><td>";
      echo "<input type='checkbox' name='send_notification' checked = true";
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         echo " disabled='true' ";
      echo " value='1'>";
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         echo "<input type='hidden' name='send_notification' value=\"1\">";
      echo "</td>";
      echo "</tr>";

      echo "</table><table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['plugin_resources'][58]."</td><td>";
      Dropdown::showYesNo("is_leaving",$this->fields["is_leaving"]);

      if($ID!=-1 && $options['withtemplate'] != 1 && $this->fields["is_leaving"]==1
         && isset($this->fields["users_id_recipient_leaving"])) {
         echo "&nbsp;".$LANG['plugin_resources']['setup'][1]." ";
         $users_id_recipient_leaving=new User();
         if ($users_id_recipient_leaving->getFromDB($this->fields["users_id_recipient_leaving"]))
            echo $users_id_recipient_leaving->getName();
      }

      echo "</td>";
      echo "<td";
      if(in_array("plugin_resources_leavingreasons_id", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][79]."</td>";
      echo "<td>";
      Dropdown::show('PluginResourcesLeavingReason',
         array('value'  => $this->fields["plugin_resources_leavingreasons_id"],
               'entity' => $this->fields["entities_id"]));
      echo "</td>";

      echo "<td";
      if(in_array("date_end", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][13]."&nbsp;";
      if(!in_array("date_end", $required))
         Html::showToolTip(nl2br($LANG['plugin_resources'][17]));
      echo "</td>";
      echo "<td>";
      Html::showDateFormItem("date_end",$this->fields["date_end"],true,true);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='6'>";
      $datestring = $LANG['common'][26].": ";
      $date = Html::convDateTime($this->fields["date_mod"]);
      echo $datestring.$date."</td>";
      echo "</tr>";

      echo "</table><table class='tab_cadre_fixe'>";

      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         $options['candel'] = false;
      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }

	function sendReport($options) {
      global $CFG_GLPI;

      if (!$this->getFromDB($options["id"])) return false;

      if ($CFG_GLPI["use_mailing"]) {
         $status = "report";
         NotificationEvent::raiseEvent($status,$this,array('reports_id'=>$options["reports_id"]));
      }
   }

   function reSendResourceCreation($options) {
      global $CFG_GLPI;

      if (!$this->getFromDB($options["id"])) return false;

      if ($CFG_GLPI["use_mailing"]) {
         $status = "new";
         NotificationEvent::raiseEvent($status,$this);
      }
   }

   static function showReportForm($options) {
      global $LANG;

      echo "<div align='center'>";

      echo "<form action='".$options['target']."' method='post'>";

      $reportconfig = new PluginResourcesReportConfig();
      $reportconfig->getFromDBByResource($options['id']);
      echo "<table class='tab_cadre' width='50%'>";
      echo "<tr>";
      echo "<th colspan='2'>";
      echo $LANG['plugin_resources']['mailing'][15];
      echo "</th></tr>";
      echo "<tr class='tab_bg_2 center'>";
      echo "<td colspan='2'>";
      echo "<input type='submit' name='report' value='".$LANG['help'][14]."' class='submit' />";
      echo "<input type='hidden' name='id' value='".$options['id']."'>";
      echo "<input type='hidden' name='reports_id' value='".$reportconfig->fields["id"]."'>";
      echo "</td></tr></table>";
      Html::closeForm();
      echo "</div>";
   }

   /**
    * Display menu
    */
   function showMenu() {
      global $LANG,$CFG_GLPI;

      echo "<div align='center'><table class='tab_cadre' width='30%' cellpadding='5'>";
		echo "<tr><th colspan='4'>".$LANG['plugin_resources']['helpdesk'][0]."</th></tr>";

		$canresting = plugin_resources_haveRight('resting', 'w');
      $canholiday = plugin_resources_haveRight('holiday', 'w');
      $canemployment = plugin_resources_haveRight('employment', 'w');
      $canseeemployment = plugin_resources_haveRight('employment', 'r');
      $canseebudget = plugin_resources_haveRight('budget', 'r');
      $colspan = "1";
      $colspan2 = "1";
      if (!$this->canCreate())
         $colspan+= 3;
      if (!$canresting || !$canholiday)
         $colspan2+= 1;
		echo "<tr><th colspan='4'>".$LANG['plugin_resources']['menu'][1]."</th></tr>";

		echo "<tr class='tab_bg_1'>";

      if($this->canCreate()) {
			//Add a resource
			echo "<td class='center'>";
			echo "<a href=\"./wizard.form.php\">";
			echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/newresource.png' alt='".$LANG['plugin_resources']['helpdesk'][1]."'>";
			echo "<br>".$LANG['plugin_resources']['helpdesk'][1]."</a>";
			echo "</td>";
			//See resources
         echo "<td class='center'>";
         echo "<a href=\"./resource.php\">";
         echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/resourcelist.png' alt='".$LANG['plugin_resources']['helpdesk'][2]."'>";
         echo "<br>".$LANG['plugin_resources']['helpdesk'][2]."</a>";
         echo "</td>";
         //Remove resources
			echo "<td class='center'>";
			echo "<a href=\"./resource.remove.php\">";
			echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/removeresource.png' alt='".$LANG['plugin_resources']['helpdesk'][4]."'>";
			echo "<br>".$LANG['plugin_resources']['helpdesk'][4]."</a>";
			echo "</td>";
		}
		echo "<td colspan='$colspan' class='center'>";
		echo "<a href=\"./directory.php\">";
		echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/directory.png' alt='".$LANG['plugin_resources'][66]."'>";
		echo "<br>".$LANG['plugin_resources'][66]."</a>";
		echo "</td>";

		echo "</tr>";

		if ($canresting || $canholiday) {
         echo "<tr><th colspan='4'>".$LANG['plugin_resources']['menu'][2]."</th></tr>";

         echo "<tr class='tab_bg_1'>";
         if ($canresting) {
            //Add resting resource
            echo "<td colspan='$colspan2' class='center'>";
            echo "<a href=\"./resourceresting.form.php\">";
            echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/newresting.png' alt='".$LANG['plugin_resources']['helpdesk'][7]."'>";
            echo "<br>".$LANG['plugin_resources']['helpdesk'][7]."</a>";
            echo "</td>";
            //List resting resource
            echo "<td colspan='$colspan2' class='center'>";
            echo "<a href=\"./resourceresting.php\">";
            echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/restinglist.png' alt='".$LANG['plugin_resources']['helpdesk'][8]."'>";
            echo "<br>".$LANG['plugin_resources']['helpdesk'][8]."</a>";
            echo "</td>";
         }
         if ($canholiday) {
            echo "<td colspan='$colspan2' class='center'>";
            echo "<a href=\"./resourceholiday.form.php\">";
            echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/holidayresource.png' alt='".$LANG['plugin_resources']['helpdesk'][18]."'>";
            echo "<br>".$LANG['plugin_resources']['helpdesk'][18]."</a>";
            echo "</td>";
            echo "<td colspan='$colspan2' class='center'>";
            echo "<a href=\"./resourceholiday.php\">";
            echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/holidaylist.png' alt='".$LANG['plugin_resources']['helpdesk'][23]."'>";
            echo "<br>".$LANG['plugin_resources']['helpdesk'][23]."</a>";
            echo "</td>";
         }
         echo "</tr>";
      }

      if ($canseeemployment || $canseebudget) {

         echo "<tr><th colspan='4'>".$LANG['plugin_resources']['menu'][3]."</th></tr>";

         echo "<tr class='tab_bg_1'>";

         if($canseeemployment) {
            if($canemployment) {
               //Add an employment
               echo "<td class='center'>";
               echo "<a href=\"./employment.form.php\">";
               echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/employment.png' alt='".$LANG['plugin_resources']['helpdesk'][26]."'>";
               echo "<br>".$LANG['plugin_resources']['helpdesk'][26]."</a>";
               echo "</td>";
            }
            //See managment employments
            echo "<td class='center'>";
            echo "<a href=\"./employment.php\">";
            echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/employmentlist.png' alt='".$LANG['plugin_resources']['helpdesk'][27]."'>";
            echo "<br>".$LANG['plugin_resources']['helpdesk'][27]."</a>";
            echo "</td>";
         }
         if ($canseebudget){
            //See managment budgets
            echo "<td class='center'>";
            echo "<a href=\"./budget.php\">";
            echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/budgetlist.png' alt='".$LANG['plugin_resources']['helpdesk'][28]."'>";
            echo "<br>".$LANG['plugin_resources']['helpdesk'][28]."</a>";
            echo "</td>";
         }

         if($canseeemployment) {
            //See recap ressource / employment
            echo "<td class='center'>";
            echo "<a href=\"./recap.php\">";
            echo "<img src='".$CFG_GLPI["root_doc"]."/plugins/resources/pics/recap.png' alt='".$LANG['plugin_resources']['helpdesk'][29]."'>";
            echo "<br>".$LANG['plugin_resources']['helpdesk'][29]."</a>";
            echo "</td>";
         }

         echo "</tr>";
      }
		echo " </table></div>";
   }


	function wizardFirstForm () {
      global $LANG;

      echo "<div align='center'>";

      echo "<form action='./wizard.form.php' method='post'>";
      echo "<table class='plugin_resources_wizard'>";
      echo "<tr>";
      echo "<td class='plugin_resources_wizard_left_area' valign='top'>";
      echo "</td>";

      echo "<td class='plugin_resources_wizard_right_area' valign='top'>";

      echo "<div class='plugin_resources_wizard_title'><p>";
      echo "<img class='plugin_resource_wizard_img' src='../pics/newresource.png' alt='newresource' />&nbsp;";
      echo $LANG['plugin_resources']['wizard'][1];
      echo "</p></div>";

      echo "<div class='plugin_resources_presentation_text'>";
      echo $LANG['plugin_resources']['wizard'][2];
      echo "<br /><br /><br />".$LANG['plugin_resources']['wizard'][3];
      echo "</div>";

      echo "<br /><br /><div class='center'>";

      $this->dropdownTemplate("template");

      echo "</div></td>";
      echo "</tr>";

      echo "<tr><td class='plugin_resources_wizard_button' colspan='2'>";
      echo "<div class='next'>";
      echo "<input type='hidden' name='withtemplate' value='2' >";
      echo "<input type='submit' name='first_step' value='".$LANG['plugin_resources']['button'][1]."' class='submit' />";
      echo "</div>";
      echo "</td></tr></table>";
      Html::closeForm();

      echo "</div>";
	}

	function wizardSecondForm ($ID, $options=array()) {
      global $LANG, $CFG_GLPI;

      $empty = 0;
      if ($ID > 0) {
			$this->check($ID,'r');
		} else {
			// Create item
			$this->check(-1,'w');
			$this->getEmpty();
			$empty = 1;
		}

      $rank = new PluginResourcesRank();

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

      if (!isset($options["requiredfields"]))
         $options["requiredfields"] = 0;
      if (($options['withtemplate'] == 2 || $options["new"]!=1) && $options["requiredfields"]!=1) {

         $options["name"] = $this->fields["name"];
         $options["firstname"] = $this->fields["firstname"];
         $options["locations_id"] = $this->fields["locations_id"];
         $options["users_id"] = $this->fields["users_id"];
         $options["plugin_resources_departments_id"] = $this->fields["plugin_resources_departments_id"];
         $options["date_begin"] = $this->fields["date_begin"];
         $options["date_end"] = $this->fields["date_end"];
         $options["comment"] = $this->fields["comment"];
         $options["quota"] = $this->fields["quota"];
         $options["plugin_resources_resourcesituations_id"] = $this->fields["plugin_resources_resourcesituations_id"];
         $options["plugin_resources_contractnatures_id"] = $this->fields["plugin_resources_contractnatures_id"];
         $options["plugin_resources_ranks_id"] = $this->fields["plugin_resources_ranks_id"];
         $options["plugin_resources_resourcespecialities_id"] = $this->fields["plugin_resources_resourcespecialities_id"];
         $options["plugin_resources_leavingreasons_id"] = $this->fields["plugin_resources_leavingreasons_id"];

      }

      echo "<div align='center'>";

      echo "<form action='".$options['target']."' method='post'>";
      echo "<table class='plugin_resources_wizard'>";
      echo "<tr>";

      echo "<td class='plugin_resources_wizard_right_area' valign='top'>";

      echo "<div class='plugin_resources_wizard_title'><p>";
      echo "<img class='plugin_resource_wizard_img' src='../pics/newresource.png' alt='newresource'/>&nbsp;";
      echo $LANG['plugin_resources']['wizard'][6];
      echo "</p></div>";

      echo "<div class='center'>";

      if (!$this->canView()) return false;
      echo "<table class='plugin_resources_wizard_table'>";
      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td width='30%'>".$LANG['plugin_resources'][20]."</td><td width='70%'>";
      if ($this->fields["plugin_resources_contracttypes_id"])
         echo Dropdown::getDropdownName("glpi_plugin_resources_contracttypes",
            $this->fields["plugin_resources_contracttypes_id"]);
      else
         echo $LANG["plugin_resources"]["wizard"][5];
      echo "</td>";
      echo "</tr></table>";

      echo "<br>";

      echo "<table class='plugin_resources_wizard_table'>";
      echo "<tr class='plugin_resources_wizard_explain'>";

      $required = array();
      $input = array();
      if (isset($this->fields["entities_id"]) || $empty==1) {
         if ($empty==1) {
            $input['plugin_resources_contracttypes_id'] = 0;
            $input['entities_id'] = $_SESSION['glpiactive_entity'];
            echo "<input type='hidden' name='entities_id' value='".$_SESSION['glpiactive_entity']."'>";
         } else {
            $input['plugin_resources_contracttypes_id'] = $this->fields["plugin_resources_contracttypes_id"];
            if (isset($options['withtemplate']) && $options['withtemplate'] == 2) {
               $input['entities_id'] = $_SESSION['glpiactive_entity'];
               echo "<input type='hidden' name='entities_id' value='".$_SESSION['glpiactive_entity']."'>";
            } else {
               $input['entities_id'] = $this->fields["entities_id"];
               echo "<input type='hidden' name='entities_id' value='".$this->fields["entities_id"]."'>";
            }
         }
      }
      $required = $this->checkRequiredFields($input);
      $alert = " class='red' ";

      if (Session::isMultiEntitiesMode()) {
         echo "<tr class='plugin_resources_wizard_explain'>";
         echo "<td width='30%'>";
         echo $LANG['entity'][0];
         echo "</td>";
         echo "<td width='50%'>";
         echo Dropdown::getDropdownName("glpi_entities",$input['entities_id']);
         echo "</td>";
         echo "</tr>";
      }

      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td";
      if(in_array("name", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][8];
      echo "</td>";
      echo "<td>";
      $option = array('value' => $options["name"],'option' => "onchange=\"javascript:this.value=this.value.toUpperCase();\"");
      Html::autocompletionTextField($this,"name",$option);
      echo "</td>";

      echo "<td rowspan='2' class='plugin_resources_wizard_comment red'>";
      echo $LANG["plugin_resources"]["wizard"][14];
      echo "</td>";

      echo "</tr>";

      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td";
      if(in_array("firstname", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][18];
      echo "</td>";
      echo "<td>";
      $option = array('value' => $options["firstname"],
                  'option' => "onChange='First2UpperCase(this.value);' style='text-transform:capitalize;'");
      Html::autocompletionTextField($this,"firstname",$option);
      echo "</td>";
      echo "</tr>";

      if ($this->fields["plugin_resources_resourcestates_id"]) {
         echo "<tr class='plugin_resources_wizard_explain'>";
         echo "<td>".$LANG['plugin_resources'][24]."</td><td>";
         echo Dropdown::getDropdownName("glpi_plugin_resources_resourcestates",
                                          $this->fields["plugin_resources_resourcestates_id"]);
         echo "</td>";
         echo "</tr>";
      }

      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td";
      if(in_array("locations_id", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][5];
      echo "</td>";
      echo "<td>";
      Dropdown::show('Location', array('name' => "locations_id",'value' => $options["locations_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td";
      if(in_array("quota", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][4];
      echo "</td>";
      echo "<td>";
      echo "<input type='text' name='quota' value='".Html::formatNumber($this->fields["quota"], true,4).
         "' size='14'>";
      echo "</td>";
      echo "</tr></table>";

      echo "<br>";

      if ($rank->canView()) {
         echo "<table class='plugin_resources_wizard_table'>";
         echo "<tr class='plugin_resources_wizard_explain'>";
         echo "<td width='30%' ";
         if(in_array("plugin_resources_resourcesituations_id", $required))
            echo $alert;
         echo ">";
         echo $LANG['plugin_resources'][75];
         echo "</td>";
         echo "<td width='70%'>";
         $params = array('name' => 'plugin_resources_resourcesituations_id',
            'value' => $this->fields['plugin_resources_resourcesituations_id'],
            'entity' => $this->fields["entities_id"],
            'action' => $CFG_GLPI["root_doc"]."/plugins/resources/ajax/dropdownContractnature.php",
            'span' => 'span_contractnature'
         );
         self::showGenericDropdown('PluginResourcesResourceSituation',$params);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='plugin_resources_wizard_explain'>";
         echo "<td";
         if(in_array("plugin_resources_contractnatures_id", $required))
            echo $alert;
         echo ">";
         echo $LANG['plugin_resources'][76];
         echo "</td><td>";
         echo "<span id='span_contractnature' name='span_contractnature'>";
         if ($this->fields["plugin_resources_contractnatures_id"]>0) {
            echo Dropdown::getDropdownName('glpi_plugin_resources_contractnatures',
               $this->fields["plugin_resources_contractnatures_id"]);
         } else {
            echo $LANG['job'][32];
         }
         echo "</span>";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='plugin_resources_wizard_explain'>";
         echo "<td";
         if(in_array("plugin_resources_ranks_id", $required))
            echo $alert;
         echo ">";
         echo $LANG['plugin_resources'][77];
         echo "</td>";
         echo "<td>";
         $params = array('name' => 'plugin_resources_ranks_id',
            'value' => $this->fields['plugin_resources_ranks_id'],
            'entity' => $this->fields["entities_id"],
            'action' => $CFG_GLPI["root_doc"]."/plugins/resources/ajax/dropdownSpeciality.php",
            'span' => 'span_speciality'
         );
         self::showGenericDropdown('PluginResourcesRank',$params);

         echo "</td>";
         echo "</tr>";

         echo "<tr class='plugin_resources_wizard_explain'>";
         echo "<td";
         if(in_array("plugin_resources_resourcespecialities_id", $required))
            echo $alert;
         echo ">";
         echo $LANG['plugin_resources'][78];
         echo "</td><td>";
         echo "<span id='span_speciality' name='span_speciality'>";
         if ($this->fields["plugin_resources_resourcespecialities_id"]>0) {
            echo Dropdown::getDropdownName('glpi_plugin_resources_resourcespecialities',
               $this->fields["plugin_resources_resourcespecialities_id"]);
         } else {
            echo $LANG['job'][32];
         }
         echo "</span>";
         echo "</td>";
         echo "</tr></table>";

         echo "<br>";
      }

      echo "<table class='plugin_resources_wizard_table'>";
      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td width='30%' ";
      if(in_array("users_id", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][47]."</td>";
      echo "<td width='70%'>";
      User::dropdown(array('value'  => $options["users_id"],
                           'name'  => "users_id",
                           'entity' => $input['entities_id'],
                           'right'  => 'all'));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td";
      if(in_array("plugin_resources_departments_id", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][14]."</td><td>";
      Dropdown::show('PluginResourcesDepartment',
         array('name' => "plugin_resources_departments_id",
               'value' => $options["plugin_resources_departments_id"],
               'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td";
      if(in_array("date_begin", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][11];
      echo "</td>";
      echo "<td>";
      Html::showDateFormItem("date_begin",$options["date_begin"],true,true);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td";
      if(in_array("date_end", $required))
         echo $alert;
      echo ">";
      echo $LANG['plugin_resources'][13]."&nbsp;";
      if(!in_array("date_end", $required))
         Html::showToolTip(nl2br($LANG['plugin_resources'][17]));
      echo "</td>";
      echo "<td>";
      Html::showDateFormItem("date_end",$options["date_end"],true,true);
      echo "</td>";
      echo "</tr></table>";

      echo "<br>";

      echo "<table class='plugin_resources_wizard_table'>";
      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td colspan='4'>".$LANG['plugin_resources'][12]."</td>";
      echo "</tr>";

      echo "<tr><td colspan='4'>";
      echo "<textarea cols='95' rows='6' name='comment' >".$options["comment"]."</textarea>";

      echo "</td></tr>";

      echo "<tr class='plugin_resources_wizard_explain'>";
      echo "<td colspan='2'>".$LANG['plugin_resources']['mailing'][42]."&nbsp;";
      echo "<input type='checkbox' name='send_notification' checked = true";
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         echo " disabled='true' ";
      echo " value='1'>";
      if ($_SESSION['glpiactiveprofile']['interface'] != 'central')
         echo "<input type='hidden' name='send_notification' value=\"1\">";
      echo "</td></tr>";

      echo "<tr><td colspan='4'>&nbsp;";
      echo "</td></tr>";

      if (!empty($required)){
         echo "<tr>";
         echo "<td class='right plugin_resources_wizard_explain red' colspan='4'>";
         echo $LANG['plugin_resources'][59];
         echo "</td>";
         echo "</tr>";
      }

      echo "</table>";
      echo "</div></td>";
      echo "</tr>";
      $contract = $this->fields["plugin_resources_contracttypes_id"];
      if ($empty==1)
        $contract = $input['plugin_resources_contracttypes_id'];
      echo "<input type='hidden' name='plugin_resources_contracttypes_id' value=\"".$contract."\">";
      echo "<input type='hidden' name='plugin_resources_resourcestates_id' value=\"".$this->fields["plugin_resources_resourcestates_id"]."\">";
      echo "<input type='hidden' name='withtemplate' value=\"".$options['withtemplate']."\" >";
      echo "<input type='hidden' name='date_declaration' value=\"".$_SESSION["glpi_currenttime"]."\">";
      echo "<input type='hidden' name='users_id_recipient' value=\"".Session::getLoginUserID()."\">";
      echo "<input type='hidden' name='id' value=\"".$ID."\">";

      if($this->canCreate() && (empty($ID)||$options['withtemplate']==2)) {
         echo "<tr><td class='plugin_resources_wizard_button' colspan='2'>";
         echo "<div class='preview'>";
         echo "<input type='submit' name='undo_first_step' value='".$LANG['plugin_resources']['button'][2]."' class='submit' />";
         echo "</div>";
         echo "<div class='next'>";
         echo "<input type='submit' name='second_step' value='".$LANG['plugin_resources']['button'][1]."' class='submit' />";
         echo "</div>";
         echo "</td></tr>";
      } else if ($this->canCreate() && !empty($ID) && $options["new"]!=1) {

         echo "<tr><td class='plugin_resources_wizard_button' colspan='2'>";
         echo "<div class='preview'>";
         echo "<input type='submit' name='undo_first_step' value='".$LANG['plugin_resources']['button'][2]."' class='submit' />";
         echo "</div>";
         echo "<div class='next'>";
         echo "<input type='submit' name='second_step_update' value='".$LANG['plugin_resources']['button'][1]."' class='submit' />";
         echo "</div>";
         echo "</td></tr>";
      }
      echo "</table>";
      Html::closeForm();

      echo "</div>";
	}

	function wizardFiveForm ($ID, $options=array()) {
      global $LANG,$CFG_GLPI;

      if ($ID > 0) {
			$this->check($ID,'r');
		}

      echo "<div align='center'>";

      echo "<form action='".$options['target']."' enctype='multipart/form-data' method='post'>";
      echo "<table class='plugin_resources_wizard' >";
      echo "<tr>";
      echo "<td class='plugin_resources_wizard_left_area' valign='top'>";
      echo "</td>";

      echo "<td class='plugin_resources_wizard_right_area' valign='top'>";

      echo "<div class='plugin_resources_wizard_title'><p>";
      echo "<img class='plugin_resource_wizard_img' src='../pics/newresource.png' alt='newresource'/>&nbsp;";
      echo $LANG["plugin_resources"]["wizard"][9];
      echo "</p></div>";

      echo "<div class='center'>";

      if (!$this->canView()) return false;
      echo "<table class='plugin_resources_wizard_table'>";

      echo "<tr><td colspan='2' align='left'>";
      if (isset($this->fields["picture"])) {
         $path = GLPI_PLUGIN_DOC_DIR."/resources/".$this->fields["picture"];
         if (file_exists($path)) {
            echo "<object data='".$CFG_GLPI['root_doc']."/plugins/resources/front/picture.send.php?file=".$this->fields["picture"]."'>
             <param name='src' value='".$CFG_GLPI['root_doc'].
              "/plugins/resources/front/picture.send.php?file=".$this->fields["picture"]."'>
            </object> ";
         } else {
            echo "<img src='../pics/nobody.png'>";
         }
      } else {
         echo "<img src='../pics/nobody.png'>";
      }
      echo "</td><td colspan='2' align='left'>".$LANG["plugin_resources"]["wizard"][10]."<br>";
      echo "<input type='file' name='picture' value=\"".
                 $this->fields["picture"]."\" size='25'>&nbsp;";
      echo "(".Document::getMaxUploadSize().")&nbsp;";
      echo "</td></tr>";
      echo "<tr><td colspan='2'>&nbsp;";
      echo "</td><td colspan='2'>";
      echo "<input type='submit' name='upload_five_step' value='".$LANG['buttons'][8]."' class='submit' />";
      echo "</td></tr>";
      echo "</table>";
      echo "</div></td>";
      echo "</tr>";

      echo "<input type='hidden' name='plugin_resources_resources_id' value=\"".$ID."\">";

      if($this->canCreate() && (!empty($ID))) {
         echo "<tr><td class='plugin_resources_wizard_button' colspan='2'>";
         echo "<div class='next'>";
         echo "<input type='submit' name='five_step' value='".$LANG['plugin_resources']['button'][1]."' class='submit' />";
         echo "</div>";
         echo "</td></tr>";
      }
      echo "</table>";
      Html::closeForm();

      echo "</div>";
	}

   static function getResourceName($ID, $link=0) {
      global $DB, $CFG_GLPI, $LANG;

      $user = "";
      if ($link==2) {
         $user = array("name"    => "",
                       "link"    => "",
                       "comment" => "");
      }

      if ($ID) {
         $query = "SELECT `glpi_plugin_resources_resources`.*,
                          `glpi_users`.`registration_number`,
                          `glpi_users`.`name` AS username
                   FROM `glpi_plugin_resources_resources`
                      LEFT JOIN `glpi_plugin_resources_resources_items`
                        ON (`glpi_plugin_resources_resources_items`.`plugin_resources_resources_id`
                            = `glpi_plugin_resources_resources`.`id`)
                      LEFT JOIN `glpi_users`
                        ON (`glpi_users`.`id` = `glpi_plugin_resources_resources_items`.`items_id`
                            AND `glpi_plugin_resources_resources_items`.`itemtype` = 'User')
                   WHERE `glpi_plugin_resources_resources`.`id` = '$ID'";
         $result = $DB->query($query);

         if ($link==2) {
            $user = array("name"    => "",
                          "comment" => "",
                          "link"    => "");
         }

         if ($DB->numrows($result)==1) {
            $data     = $DB->fetch_assoc($result);
            $username = formatUserName($data["id"], $data["username"], $data["name"],
                                       $data["firstname"], $link);

            if ($link==2) {
               $user["name"]    = $username;
               $user["link"]    = $CFG_GLPI["root_doc"]."/plugins/resources/front/resource.form.php?id=".$ID;
               $user["comment"] = "";

               if (isset($data["picture"]) && !empty($data["picture"])) {
                  $path = GLPI_PLUGIN_DOC_DIR."/resources/".$data["picture"];
                  if (file_exists($path)) {
                     $user["comment"] .="<object data='".$CFG_GLPI['root_doc']."/plugins/resources/front/picture.send.php?file=".$data["picture"]."'>
                      <param name='src' value='".$CFG_GLPI['root_doc'].
                        "/plugins/resources/front/picture.send.php?file=".$data["picture"]."'>
                     </object><br> ";

                  } else {
                     $user["comment"] .="<img src='".$CFG_GLPI['root_doc']."/plugins/resources/pics/nobody.png'><br>";
                  }
               } else {
                  $user["comment"] .="<img src='".$CFG_GLPI['root_doc']."/plugins/resources/pics/nobody.png'><br>";
               }

               $user["comment"] .= $LANG['common'][16]."&nbsp;: ".$username."<br>";

               if ($data["plugin_resources_ranks_id"]>0) {
                  $user["comment"] .= $LANG['plugin_resources'][77]."&nbsp;: ".
                                      Dropdown::getDropdownName("glpi_plugin_resources_ranks",
                                                                $data["plugin_resources_ranks_id"])."<br>";
               }

               if ($data["locations_id"]>0) {
                  $user["comment"] .= $LANG['common'][15]."&nbsp;: ".
                                      Dropdown::getDropdownName("glpi_locations",
                                                                $data["locations_id"])."<br>";
               }

               if($data["registration_number"]>0) {
                  $user["comment"] .= $LANG['users'][17]."&nbsp;: ".
                                      $data["registration_number"]."<br>";
               }

            } else {
               $user = $username;
            }
         }
      }
      return $user;
   }

   /**
    * Permet l'affichage dynamique des ressources avec info bulle
    *
    * @static
    * @param array ($myname,$value,$entity_restrict)
    */

   static function dropdown($options=array()) {
      global $DB, $CFG_GLPI, $LANG;

      // Default values
      $p['name']           = 'plugin_resources_resources_id';
      $p['value']          = '';
      $p['all']            = 0;
      $p['on_change']      = '';
      $p['comments']       = 1;
      $p['entity']         = -1;
      $p['entity_sons']    = false;
      $p['used']           = array();
      $p['toupdate']       = '';
      $p['rand']           = mt_rand();
      $p['plugin_resources_contracttypes_id'] = 0;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      if (!($p['entity']<0) && $p['entity_sons']) {
         if (is_array($p['entity'])) {
            echo "entity_sons options is not available with array of entity";
         } else {
            $p['entity'] = getSonsOf('glpi_entities',$p['entity']);
         }
      }

      // Make a select box with all glpi users
      $use_ajax = false;

      if ($CFG_GLPI["use_ajax"]) {
         $res = self::getSqlSearchResult (true, $p['entity'], $p['value'], $p['used'],'',$p['plugin_resources_contracttypes_id']);
         $nb = ($res ? $DB->result($res,0,"cpt") : 0);
         if ($nb > $CFG_GLPI["ajax_limit_count"]) {
            $use_ajax = true;
         }
      }
      $user = self::getResourceName($p['value'],2);

      $default_display  = "<select id='dropdown_".$p['name'].$p['rand']."' name='".$p['name']."'>";
      $default_display .= "<option value='".$p['value']."'>";
      $default_display .= Toolbox::substr($user["name"], 0, $_SESSION["glpidropdown_chars_limit"]);
      $default_display .= "</option></select>";

      //$view_users = (Session::haveRight("user", "r"));

      $params = array('searchText'       => '__VALUE__',
                      'value'            => $p['value'],
                      'myname'           => $p['name'],
                      'all'              => $p['all'],
                      'comment'          => $p['comments'],
                      'rand'             => $p['rand'],
                      'on_change'        => $p['on_change'],
                      'entity_restrict'  => $p['entity'],
                      'used'             => $p['used'],
                      'update_item'      => $p['toupdate'],
                      'plugin_resources_contracttypes_id' => $p['plugin_resources_contracttypes_id']);

      $default = "";
      if (!empty($p['value']) && $p['value']>0) {
         $default = $default_display;

      } else {
         $default = "<select name='".$p['name']."' id='dropdown_".$p['name'].$p['rand']."'>";
         if ($p['all']) {
            $default.= "<option value='0'>[ ".$LANG['common'][66]." ]</option></select>";
         } else {
            $default.= "<option value='0'>".Dropdown::EMPTY_VALUE."</option></select>\n";
         }
      }
      Ajax::dropdown($use_ajax, "/plugins/resources/ajax/dropdownResources.php", $params, $default, $p['rand']);
      
      if (class_exists('PluginPositionsPosition')) {
         PluginPositionsPosition::showGeolocLink('PluginResourcesResource',$params["value"]);
      }
      // Display comment
      if ($p['comments']) {
         if (empty($user["link"])) {
            $user["link"] = $CFG_GLPI['root_doc']."/plugins/resources/front/resource.php";
         }
         Html::showToolTip($user["comment"],
                           array('contentid' => "comment_".$p['name'].$p['rand'],
                                 'link'      => $user["link"],
                                 'linkid'    => "comment_link_".$p["name"].$p['rand']));
      }

      return $p['rand'];
   }

   static function getSqlSearchResult ($count=true, $entity_restrict=-1, $value=0,
                                       $used=array(), $search='') {
      global $DB, $CFG_GLPI;

      // No entity define : use active ones
      if ($entity_restrict < 0) {
         $entity_restrict = $_SESSION["glpiactiveentities"];
      }

      $joinprofile = false;

      $where = " `glpi_plugin_resources_resources`.`is_deleted` = '0'
                  AND `glpi_plugin_resources_resources`.`is_leaving` = '0'
                  AND `glpi_plugin_resources_resources`.`is_template` = '0' ";


      $where.= getEntitiesRestrictRequest('AND','glpi_plugin_resources_resources','',$entity_restrict,true);
      if ((is_numeric($value) && $value)
      || count($used)) {

         $where .= " AND `glpi_plugin_resources_resources`.`id` NOT IN (0";
         if (is_numeric($value)) {
            $first = false;
            $where .= $value;
         } else {
            $first = true;
         }
         if(is_array($used)){
            foreach ($used as $val) {
               if ($first) {
                  $first = false;
               } else {
                  $where .= ",";
               }
               $where .= $val;
            }
         }
         $where .= ")";
      }

      if ($count) {
         $query = "SELECT COUNT(DISTINCT `glpi_plugin_resources_resources`.`id` ) AS cpt
                   FROM `glpi_plugin_resources_resources` ";
      } else {
         $query = "SELECT DISTINCT `glpi_plugin_resources_resources`.*,
                          `glpi_users`.`registration_number`,
                          `glpi_users`.`name` AS username
                   FROM `glpi_plugin_resources_resources`
                   LEFT JOIN `glpi_plugin_resources_resources_items`
                      ON (`glpi_plugin_resources_resources_items`.`plugin_resources_resources_id`
                          = `glpi_plugin_resources_resources`.`id`)
                    LEFT JOIN `glpi_users`
                      ON (`glpi_users`.`id` = `glpi_plugin_resources_resources_items`.`items_id`
                            AND `glpi_plugin_resources_resources_items`.`itemtype` = 'User') ";
      }

      if ($count) {
         $query .= " WHERE $where ";
      } else {
         if (strlen($search)>0 && $search!=$CFG_GLPI["ajax_wildcard"]) {
            $where .= " AND (`glpi_plugin_resources_resources`.`name` ".Search::makeTextSearch($search)."
                             OR `glpi_plugin_resources_resources`.`firstname` ".Search::makeTextSearch($search)."
                             OR `glpi_users`.`registration_number` ".Search::makeTextSearch($search)."
                             OR `glpi_users`.`name` ".Search::makeTextSearch($search)."
                             OR CONCAT(`glpi_plugin_resources_resources`.`name`,' ',`glpi_plugin_resources_resources`.`firstname`,' ',`glpi_users`.`registration_number`,' ',`glpi_users`.`name`) ".
                                       Search::makeTextSearch($search).")";
         }
         $query .= " WHERE $where ";

         if ($_SESSION["glpinames_format"] == FIRSTNAME_BEFORE) {
            $query.=" ORDER BY `glpi_plugin_resources_resources`.`firstname`,
                               `glpi_plugin_resources_resources`.`name` ";
         } else {
            $query.=" ORDER BY `glpi_plugin_resources_resources`.`firstname`,
                               `glpi_plugin_resources_resources`.`name` ";
         }

         if ($search != $CFG_GLPI["ajax_wildcard"]) {
            $query .= " LIMIT 0,".$CFG_GLPI["dropdown_max"];
         }
      }
      return $DB->query($query);
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
         echo "<tr><th colspan='".(2+$colsup)."'>".$LANG['common'][7]." - ".$LANG['plugin_resources']['title'][1].":</th>";
      } else {
         echo "<tr><th colspan='".(2+$colsup)."'>".$LANG['common'][14]." - ".$LANG['plugin_resources']['title'][1]." :</th>";
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

   //Show form from heelpdesk to remove a resource
   function showResourcesToRemove() {
      global $CFG_GLPI,$LANG;

      $first=false;

      if (countElementsInTable($this->getTable())>0) {

         echo "<div align='center'>";

         echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/resources/front/resource.remove.php\">";

         echo "<table class='plugin_resources_wizard'>";
         echo "<tr>";
         echo "<td class='plugin_resources_wizard_left_area' valign='top'>";
         echo "</td>";

         echo "<td class='plugin_resources_wizard_right_area' valign='top'>";

         echo "<div class='plugin_resources_wizard_title'><p>";
         echo "<img class='plugin_resource_wizard_img' src='../pics/removeresource.png' alt='removeresource' />";
         echo $LANG['plugin_resources']['helpdesk'][4];
         echo "</p></div>";

         echo "<table align='center'>";

         echo "<tr class='plugin_resources_wizard_explain'><td>";
         echo $LANG['plugin_resources']['helpdesk'][9]."</td>";
         echo "<td class='left'>";
         PluginResourcesResource::dropdown(array('name'   => 'plugin_resources_resources_id',
            'entity' => $_SESSION['glpiactiveentities']));

         echo "</td></tr>";
         echo "<tr class='plugin_resources_wizard_explain'><td>";
         echo $LANG['plugin_resources'][13]."</td>";
         echo "<td class='center'>";
         Html::showDateFormItem("date_end",$_POST["date_end"],true,true);
         echo "</td></tr>";

         echo "<tr class='plugin_resources_wizard_explain'><td>";
         echo $LANG['plugin_resources'][79]."</td>";
         echo "<td>";
         Dropdown::show('PluginResourcesLeavingReason',
            array('entity' => $_SESSION['glpiactiveentities']));
         echo "</td></tr>";

         echo "</table>";
         echo "</div></td>";
         echo "</tr>";

         echo "<tr><td class='plugin_resources_wizard_button' colspan='2'>";
         echo "<div class='next'>";
         echo "<input type='submit' name='removeresources' value=\"".$LANG['plugin_resources']['helpdesk'][4]."\" class='submit'>";
         echo "</div>";
         echo "</td></tr></table>";
         Html::closeForm();

         echo "</div>";
      } else {
         echo "<div align='center'>".$LANG['plugin_resources'][33]."</div>";
      }
   }

   /**
    * Show for PDF an resources
    *
    * @param $pdf object for the output
    * @param $ID of the resources
    */
   function show_PDF ($pdf) {
      global $LANG;

      $pdf->setColumnsSize(50,50);
      $col1 = '<b>'.$LANG["common"][2].' '.$this->fields['id'].'</b>';
      if (isset($this->fields["date_declaration"])) {
         $users_id_recipient=new User();
         $users_id_recipient->getFromDB($this->fields["users_id_recipient"]);
         $col2 = $LANG['joblist'][11].' : '.Html::convDateTime($this->fields["date_declaration"]).' '.$LANG['plugin_resources']['setup'][1].' '.$users_id_recipient->getName();
      } else {
         $col2 = '';
      }
      $pdf->displayTitle($col1, $col2);

      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_resources'][8].' :</i></b> '.$this->fields['name'],
         '<b><i>'.$LANG['plugin_resources'][18].' :</i></b> '.$this->fields['firstname']);
      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_resources'][5].' :</i></b> '.Html::clean(Dropdown::getDropdownName('glpi_locations',$this->fields['locations_id'])),
         '<b><i>'.$LANG['plugin_resources'][20].' :</i></b> '.Html::clean(Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',$this->fields['plugin_resources_contracttypes_id'])));

      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_resources'][47].' :</i></b> '.Html::clean(getusername($this->fields["users_id"])),
         '<b><i>'.$LANG['plugin_resources'][14].' :</i></b> '.Html::clean(Dropdown::getDropdownName('glpi_plugin_resources_departments',$this->fields["plugin_resources_departments_id"])));

      $pdf->displayLine(
         '<b><i>'.$LANG['plugin_resources'][11].' :</i></b> '.Html::convDate($this->fields["date_begin"]),
         '<b><i>'.$LANG['plugin_resources'][13].' :</i></b> '.Html::convDate($this->fields["date_end"]));

      $pdf->setColumnsSize(100);

      $pdf->displayText('<b><i>'.$LANG['plugin_resources'][12].' :</i></b>', $this->fields['comment']);

      $pdf->displaySpace();
   }

   // Cron action
   static function cronInfo($name) {
      global $LANG;

      switch ($name) {
         case 'Resources':
            return array (
               'description' => $LANG['plugin_resources']['mailing'][24]);   // Optional
            break;
      }
      return array();
   }

   function queryAlert() {

      $first=false;
      $date=date("Y-m-d H:i:s");
      $query = "SELECT *
            FROM `".$this->getTable()."`
            WHERE `date_end` IS NOT NULL
            AND `date_end` <= '".$date."'
            AND `is_leaving` != '1'";

      // Add Restrict templates
      if ($this->maybeTemplate()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`".$this->getTable()."`.`is_template` = '0' ";
      }
      // Add is_deleted if item have it
      if ($this->maybeDeleted()) {
         $LINK= " AND " ;
         if ($first) {$LINK=" ";$first=false;}
         $query.= $LINK."`".$this->getTable()."`.`is_deleted` = '0' ";
      }

      return $query;

   }

   /**
    * Cron action on tasks : LeavingResources
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronResources($task=NULL) {
      global $DB,$CFG_GLPI,$LANG;

      if (!$CFG_GLPI["use_mailing"]) {
         return 0;
      }

      $message=array();
      $cron_status = 0;

      $resource = new self();
      $query_expired = $resource->queryAlert();

      $querys = array(Alert::END=>$query_expired);

      $task_infos = array();
      $task_messages = array();

      foreach ($querys as $type => $query) {
         $task_infos[$type] = array();
         foreach ($DB->request($query) as $data) {
            $entity = $data['entities_id'];
            $message = $data["name"]." ".$data["firstname"]." : ".
               Html::convDate($data["date_end"])."<br>\n";
            $task_infos[$type][$entity][] = $data;

            if (!isset($tasks_infos[$type][$entity])) {
               $task_messages[$type][$entity] = $LANG['plugin_resources']['mailing'][14]."<br />";
            }
            $task_messages[$type][$entity] .= $message;
         }
      }

      foreach ($querys as $type => $query) {

         foreach ($task_infos[$type] as $entity => $resources) {
            Plugin::loadLang('resources');

            if (NotificationEvent::raiseEvent("AlertLeavingResources",
               new PluginResourcesResource(),
               array('entities_id'=>$entity,
                  'resources'=>$resources))) {
               $message = $task_messages[$type][$entity];
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
                     ":  Send leaving resources alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities",$entity).
                     ":  Send leaving resources alert failed",false,ERROR);
               }
            }
         }
      }

      return $cron_status;
   }

   /**
    * Display entities of the loaded profile
    *
   * @param $myname select name
    * @param $target target for entity change action
    */
   static function showSelector($target) {
      global $CFG_GLPI,$LANG;

      $rand=mt_rand();
      Plugin::loadLang('resources');
      echo "<div class='center' ><span class='b'>".$LANG['plugin_resources']['setup'][5]."</span><br>";
      echo "<a style='font-size:14px;' href='".$target."?reset=reset' title=\"".
             $LANG['buttons'][40]."\">".str_replace(" ","&nbsp;",$LANG['buttons'][40])."</a></div>";

      echo "<div class='left' style='width:100%'>";

      echo "<script type='javascript'>";
      echo "var Tree_Category_Loader$rand = new Ext.tree.TreeLoader({
         dataUrl:'".$CFG_GLPI["root_doc"]."/plugins/resources/ajax/resourcetreetypes.php'
      });";

      echo "var Tree_Category$rand = new Ext.tree.TreePanel({
         collapsible      : false,
         animCollapse     : false,
         border           : false,
         id               : 'tree_projectcategory$rand',
         el               : 'tree_projectcategory$rand',
         autoScroll       : true,
         animate          : false,
         enableDD         : true,
         containerScroll  : true,
         height           : 320,
         width            : 770,
         loader           : Tree_Category_Loader$rand,
         rootVisible     : false
      });";

      // SET the root node.
      echo "var Tree_Category_Root$rand = new Ext.tree.AsyncTreeNode({
         text     : '',
         draggable   : false,
         id    : '-1'                  // this IS the id of the startnode
      });
      Tree_Category$rand.setRootNode(Tree_Category_Root$rand);";

      // Render the tree.
      echo "Tree_Category$rand.render();
            Tree_Category_Root$rand.expand();";

      echo "</script>";

      echo "<div id='tree_projectcategory$rand' ></div>";
      echo "</div>";
   }

   function sendEmail ($data) {
      global $LANG;

      $users = array();
      foreach ($data["item"] as $key => $val) {
         if ($val==1) {
            $restrict = "`itemtype` = 'User'
                     AND `plugin_resources_resources_id` = '".$key."'";
            $resources = getAllDatasFromTable("glpi_plugin_resources_resources_items",$restrict);

            if (!empty($resources)) {
               foreach ($resources as $resource) {
                 $users[] = $resource["items_id"];
               }
            }
         }
      }
      $User = new User();
      $mail = "";
      $first=true;
      foreach ($users as $key => $val) {
         if($User->getFromDB($val)) {
            $email = $User->getDefaultEmail();
            if (!empty($email)) {
               if (!$first) $mail.=";";
               else $first=false;
               $mail.= $email;
            }
         }
      }

      $send = "<a href='mailto:$mail'>".$LANG['plugin_resources']['mailing'][43]."</a>";
      Session::addMessageAfterRedirect($send);

   }

   /**
	 * Send a file (not a document) to the navigator
	 * See Document->send();
	 *
	 * @param $file string: storage filename
	 * @param $filename string: file title
	 *
	 * @return nothing
	**/
	static function sendFile($file, $filename) {

		// Test securite : document in DOC_DIR
		$tmpfile = str_replace(GLPI_PLUGIN_DOC_DIR."/resources/", "", $file);

		if (strstr($tmpfile,"../") || strstr($tmpfile,"..\\")) {
			Event::log($file, "sendFile", 1, "security",
						  $_SESSION["glpiname"]." try to get a non standard file.");
			die("Security attack !!!");
		}

		if (!file_exists($file)) {
			die("Error file $file does not exist");
		}

		$splitter = explode("/", $file);
		$mime     = "application/octet-stream";

		if (preg_match('/\.(....?)$/', $file, $regs)) {
			switch ($regs[1]) {
				case "jpeg" :
					$mime = "image/jpeg";
					break;

				case "jpg" :
					$mime = "image/jpeg";
					break;
			}
		}
		//print_r($file);

		// Now send the file with header() magic
		header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
		header('Pragma: private'); /// IE BUG + SSL
		header('Cache-control: private, must-revalidate'); /// IE BUG + SSL
		header("Content-disposition: filename=\"$filename\"");
		header("Content-type: ".$mime);

		readfile($file) or die ("Error opening file $file");
	}

   
   /**
    * Permet l'affichage dynamique d'une liste déroulante imbriquee
    *
    * @static
    * @param array ($itemtype,$myname,$value,$entity_restrict,$action,$span)
    */
   static function showGenericDropdown($itemtype, $options=array()) {
      global $DB,$CFG_GLPI,$LANG;

      $item = getItemForItemtype($itemtype);
      if ($itemtype && !($item = getItemForItemtype($itemtype))) {
         return false;
      }

      $table = $item->getTable();

      /*$options["table"] = $table;
      $options["rand"] = $rand;
      
      $params['name']        = $item->getForeignKeyField();
      $params['value']       = ($itemtype=='Entity' ? $_SESSION['glpiactive_entity'] : '');
      
      $params['entity']      = -1;
      $params['entity_sons'] = false;
      $params['toupdate']    = '';
      $params['used']        = array();
      $params['toadd']       = array();
      $params['on_change']   = '';
      $params['condition']   = '';
      $params['rand']        = mt_rand();
      $params['displaywith'] = array();
      //Parameters about choice 0
      //Empty choice's label
      $params['emptylabel'] = self::EMPTY_VALUE;*/

      $params['comments']    = true;
      $params['condition']   = '';
      $params['entity']      = -1;
      $params['entity_sons'] = false;
      $params['rand']        = mt_rand();
      $params['used']        = array();
      $params['table']       = $table;
      $params['emptylabel']  = Dropdown::EMPTY_VALUE;
      //Display emptychoice ?
      $params['display_emptychoice'] = true;
      //In case of Entity dropdown, display root entity ?
      $params['display_rootentity']  = false;

      //specific
      
      $params['action'] = "";
      $params['span']   = "";
      $params['sort']    = false;
                      
      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $name         = $params['emptylabel'];
      $comment      = "";
      $limit_length = $_SESSION["glpidropdown_chars_limit"];
      
      if (strlen($params['value'])==0 || !is_numeric($params['value'])) {
         $params['value'] = 0;
      }

      if ($params['value'] > 0) {
         $tmpname = Dropdown::getDropdownName($table, $params['value'], 1);

         if ($tmpname["name"] != "&nbsp;") {
            $name    = $tmpname["name"];
            $comment = $tmpname["comment"];

            if (Toolbox::strlen($name) > $_SESSION["glpidropdown_chars_limit"]) {
               if ($item instanceof CommonTreeDropdown) {
                  $pos          = strrpos($name, ">");
                  $limit_length = max(Toolbox::strlen($name) - $pos,
                                      $_SESSION["glpidropdown_chars_limit"]);

                  if (Toolbox::strlen($name)>$limit_length) {
                     $name = "&hellip;".Toolbox::substr($name, -$limit_length);
                  }

               } else {
                  $limit_length = Toolbox::strlen($name);
               }

            } else {
               $limit_length = $_SESSION["glpidropdown_chars_limit"];
            }
         }
      }
      
      // Manage entity_sons
      if (!($params['entity']<0) && $params['entity_sons']) {
         if (is_array($params['entity'])) {
            echo "entity_sons options is not available with array of entity";
         } else {
            $params['entity'] = getSonsOf('glpi_entities',$params['entity']);
         }
      }
      
      
      $use_ajax = false;
      if ($CFG_GLPI["use_ajax"]) {
         $nb = 0;

         if ($item->isEntityAssign()) {
            if (!($params['entity']<0)) {
               $nb = countElementsInTableForEntity($table, $params['entity'], $params['condition']);
            } else {
               $nb = countElementsInTableForMyEntities($table, $params['condition']);
            }

         } else {
            $nb = countElementsInTable($table, $params['condition']);
         }

         $nb -= count($params['used']);

         if ($nb>$CFG_GLPI["ajax_limit_count"]) {
            $use_ajax = true;
         }
      }
      
      $param = array('searchText'           => '__VALUE__',
                      'value'               => $params['value'],
                      'itemtype'            => $itemtype,
                      'myname'              => $params['name'],
                      'limit'               => $limit_length,
                      'comment'             => $params['comments'],
                      'rand'                => $params['rand'],
                      'entity_restrict'     => $params['entity'],
                      'used'                => $params['used'],
                      'condition'           => $params['condition'],
                      'emptylabel'          => $params['emptylabel'],
                      'display_emptychoice' => $params['display_emptychoice'],
                      'display_rootentity'  => $params['display_rootentity'],

                     //specific
                      'action'           => $params['action'],
                      'span'           => $params['span'],
                      'sort'           => $params['sort']);
                      
      $default  = "<select name='".$params['name']."' id='dropdown_".$params['name'].
                    $params['rand']."'>";
      $default .= "<option value='".$params['value']."'>$name</option></select>";

      Ajax::dropdown($use_ajax, "/plugins/resources/ajax/dropdownValue.php", $param, $default,  $params['rand']);

      // Display comment
      if ($params['comments']) {
         $options_tooltip = array('contentid' => "comment_".$param['myname'].$params['rand']);

         if ($params['value'] && $item->getFromDB($params['value'])
            ) {

            $options_tooltip['link']       = $item->getLinkURL();
            $options_tooltip['linktarget'] = '_blank';
         }

         Html::showToolTip($comment,$options_tooltip);

         if (($item instanceof CommonDropdown)
              && $item->canCreate()
              && !isset($_GET['popup'])) {

               echo "<img alt='' title=\"".$LANG['buttons'][8]."\" src='".$CFG_GLPI["root_doc"].
                     "/pics/add_dropdown.png' style='cursor:pointer; margin-left:2px;'
                     onClick=\"var w = window.open('".$item->getFormURL()."?popup=1&amp;rand=".
                     $params['rand']."' ,'glpipopup', 'height=400, ".
                     "width=1000, top=100, left=100, scrollbars=yes' );w.focus();\">";
         }
      }

      return $params['rand'];
   }
}

?>