<?php
/*
 * @version $Id: notificationtargetresource.class.php 480 2012-11-09 tynet $
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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginResourcesNotificationTargetResource extends NotificationTarget {

   const RESOURCE_MANAGER = 4300;
   const RESOURCE_AUTHOR = 4301;
   const RESOURCE_AUTHOR_LEAVING = 4302;
   const RESOURCE_TASK_TECHNICIAN = 4303;
   const RESOURCE_TASK_GROUP = 4304;
   
   function getEvents() {
      global $LANG;
      return array ('new' => $LANG['plugin_resources']['mailing'][4],
                     'update' => $LANG['plugin_resources']['mailing'][5],
                     'delete' => $LANG['plugin_resources']['mailing'][6],
                     'newtask' => $LANG['plugin_resources']['mailing'][7],
                     'updatetask' => $LANG['plugin_resources']['mailing'][8],
                     'deletetask' => $LANG['plugin_resources']['mailing'][9],
                     'AlertExpiredTasks' => $LANG['plugin_resources']['mailing'][25],
                     'AlertLeavingResources' => $LANG['plugin_resources']['mailing'][14],
                     'AlertArrivalChecklists' => $LANG['plugin_resources']['mailing'][21],
                     'AlertLeavingChecklists' => $LANG['plugin_resources']['mailing'][22],
                     'LeavingResource' => $LANG['plugin_resources']['mailing'][12],
                     'report' => $LANG['plugin_resources'][29],
                     'newresting' => $LANG['plugin_resources']['mailing'][35],
                     'updateresting' => $LANG['plugin_resources']['mailing'][36],
                     'deleteresting' => $LANG['plugin_resources']['mailing'][37],
                     'newholiday' => $LANG['plugin_resources']['mailing'][39],
                     'updateholiday' => $LANG['plugin_resources']['mailing'][40],
                     'deleteholiday' => $LANG['plugin_resources']['mailing'][41]
                     );
   }

   /**
    * Get additionnals targets for Tickets
    */
   function getAdditionalTargets($event='') {
      global $LANG;
      
      if ($event != 'AlertExpiredTasks'
            && $event != 'AlertLeavingResources'
               && $event != 'AlertLeavingChecklists'
                  && $event != 'AlertLeavingChecklists') {
         $this->addTarget(PluginResourcesNotificationTargetResource::RESOURCE_MANAGER,$LANG['plugin_resources'][47]);
         $this->addTarget(PluginResourcesNotificationTargetResource::RESOURCE_AUTHOR,$LANG['plugin_resources'][2]);
         $this->addTarget(PluginResourcesNotificationTargetResource::RESOURCE_AUTHOR_LEAVING,$LANG['plugin_resources'][73]);
         if ($event == 'newtask'
               || $event == 'updatetask'
                  || $event == 'deletetask') {
            $this->addTarget(PluginResourcesNotificationTargetResource::RESOURCE_TASK_TECHNICIAN,$LANG['plugin_resources']['mailing'][3]);
            $this->addTarget(PluginResourcesNotificationTargetResource::RESOURCE_TASK_GROUP,$LANG['plugin_resources']['mailing'][11]);
         }
      }
   }

   function getSpecificTargets($data,$options) {

      //Look for all targets whose type is Notification::ITEM_USER
      switch ($data['items_id']) {

         case PluginResourcesNotificationTargetResource::RESOURCE_MANAGER :
            $this->getManagerAddress();
            break;
         case PluginResourcesNotificationTargetResource::RESOURCE_AUTHOR :
            $this->getAuthorAddress();
            break;
         case PluginResourcesNotificationTargetResource::RESOURCE_AUTHOR_LEAVING :
            $this->getAuthorLeavingAddress();
            break;
         case PluginResourcesNotificationTargetResource::RESOURCE_TASK_TECHNICIAN :
            $this->getTaskTechAddress($options);
            break;
         case PluginResourcesNotificationTargetResource::RESOURCE_TASK_GROUP :
            $this->getTaskGroupAddress($options);
            break;
      }
   }

   //Get recipient
   function getManagerAddress() {
      return $this->getUserByField ("users_id");
   }
   
   function getAuthorAddress() {
      return $this->getUserByField ("users_id_recipient");
   }
   
   function getAuthorLeavingAddress() {
      return $this->getUserByField ("users_id_recipient_leaving");
   }
   
   function getTaskTechAddress($options=array()) {
      global $DB;

      if (isset($options['tasks_id'])) {
         $query = "SELECT DISTINCT `glpi_users`.`id` AS id,
                          `glpi_users`.`language` AS language
                   FROM `glpi_plugin_resources_tasks`
                   LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_plugin_resources_tasks`.`users_id`)
                   WHERE `glpi_plugin_resources_tasks`.`id` = '".$options['tasks_id']."'";

         foreach ($DB->request($query) as $data) {
            $data['email'] = UserEmail::getDefaultForUser($data['id']);
            $this->addToAddressesList($data);
         }
      }
   }
   
   function getTaskGroupAddress ($options=array()) {
      global $DB;

      if (isset($options['groups_id'])
                && $options['groups_id']>0
                && isset($options['tasks_id'])) {

         $query = $this->getDistinctUserSql().
                   " FROM `glpi_users`
                    LEFT JOIN `glpi_groups_users` ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`) 
                    LEFT JOIN `glpi_plugin_resources_tasks` ON (`glpi_plugin_resources_tasks`.`groups_id` = `glpi_groups_users`.`groups_id`)
                    WHERE `glpi_plugin_resources_tasks`.`id` = '".$options['tasks_id']."'";
         
         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }

   function getDatasForTemplate($event,$options=array()) {
      global $LANG, $CFG_GLPI, $DB;
      
      if ($event == 'AlertExpiredTasks') {
         
         $this->datas['##resource.entity##'] =
                           Dropdown::getDropdownName('glpi_entities',
                                                     $options['entities_id']);
         $this->datas['##lang.resource.entity##'] =$LANG['entity'][0];
         $this->datas['##resource.action##'] = $LANG['plugin_resources']['mailing'][25];

         $this->datas['##lang.task.name##'] = $LANG['plugin_resources'][8];
         $this->datas['##lang.task.type##'] = $LANG['plugin_resources'][28];
         $this->datas['##lang.task.users##'] = $LANG['plugin_resources'][23];
         $this->datas['##lang.task.groups##'] = $LANG['common'][35];
         $this->datas['##lang.task.datebegin##'] = $LANG['plugin_resources'][34];
         $this->datas['##lang.task.dateend##'] = $LANG['plugin_resources'][35];
         $this->datas['##lang.task.planned##'] = $LANG['plugin_resources'][37];
         $this->datas['##lang.task.realtime##'] = $LANG['plugin_resources'][36];
         $this->datas['##lang.task.finished##'] = $LANG['plugin_resources'][26];
         $this->datas['##lang.task.comment##'] = $LANG['plugin_resources'][12];
         $this->datas['##lang.task.resource##'] = $LANG['plugin_resources'][9];
         
         foreach($options['tasks'] as $id => $task) {
            $tmp = array();

            $tmp['##task.name##'] = $task['name'];
            $tmp['##task.type##'] = Dropdown::getDropdownName('glpi_plugin_resources_tasktypes',
                                                       $task['plugin_resources_tasktypes_id']);
            $tmp['##task.users##'] = Html::clean(getUserName($task['users_id']));
            $tmp['##task.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                       $task['groups_id']);
            $restrict = " `plugin_resources_tasks_id` = '".$task['id']."' ";
            $plans = getAllDatasFromTable("glpi_plugin_resources_taskplannings",$restrict);
            
            if (!empty($plans)) {
               foreach ($plans as $plan) {
                  $tmp['##task.datebegin##'] = Html::convDateTime($plan["begin"]);
                  $tmp['##task.dateend##'] = Html::convDateTime($plan["end"]);
               }
            } else {
               $tmp['##task.datebegin##'] = '';
               $tmp['##task.dateend##'] = '';
            }
            
            $tmp['##task.planned##'] = '';
            $tmp['##task.finished##'] = Dropdown::getYesNo($task['is_finished']);
            $tmp['##task.realtime##'] = Ticket::getActionTime($task["actiontime"]);
            $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['comment']));
            $tmp['##task.comment##'] = Html::clean($comment);
            $tmp['##task.resource##'] = Dropdown::getDropdownName('glpi_plugin_resources_resources',
                                                       $task['plugin_resources_resources_id']);
                                                       
            $this->datas['tasks'][] = $tmp;
         }
      } else if ($event == 'AlertLeavingResources') {
         
         $this->datas['##resource.entity##'] =
                           Dropdown::getDropdownName('glpi_entities',
                                                     $options['entities_id']);
         $this->datas['##lang.resource.entity##'] =$LANG['entity'][0];
         $this->datas['##resource.action##'] = $LANG['plugin_resources']['mailing'][14];

         $this->datas['##lang.resource.id##'] = "ID";
         $this->datas['##lang.resource.name##'] = $LANG['plugin_resources'][8];
         $this->datas['##lang.resource.firstname##'] = $LANG['plugin_resources'][18];
         $this->datas['##lang.resource.type##'] = $LANG['plugin_resources'][20];
         $this->datas['##lang.resource.users##'] = $LANG['plugin_resources'][47];
         $this->datas['##lang.resource.usersrecipient##'] = $LANG['plugin_resources'][2];
         $this->datas['##lang.resource.datedeclaration##'] = $LANG['plugin_resources'][74];
         $this->datas['##lang.resource.datebegin##'] = $LANG['plugin_resources'][11];
         $this->datas['##lang.resource.dateend##'] = $LANG['plugin_resources'][13];
         $this->datas['##lang.resource.department##'] = $LANG['plugin_resources'][14];
         $this->datas['##lang.resource.status##'] = $LANG['plugin_resources'][24];
         $this->datas['##lang.resource.location##'] = $LANG['plugin_resources'][5];
         $this->datas['##lang.resource.comment##'] = $LANG['plugin_resources'][12];
         $this->datas['##lang.resource.usersleaving##'] = $LANG['plugin_resources'][73];
         $this->datas['##lang.resource.leaving##'] = $LANG['plugin_resources'][58];
         $this->datas['##lang.resource.leavingreason##'] = $LANG['plugin_resources'][79];
         $this->datas['##lang.resource.helpdesk##'] = $LANG['software'][46];
         $this->datas['##lang.resource.url##'] = "URL";
         
         foreach($options['resources'] as $id => $resource) {
            $tmp = array();

            $tmp['##resource.name##'] = $resource['name'];
            $tmp['##resource.firstname##'] = $resource['firstname'];
            $tmp['##resource.type##'] = Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                       $resource['plugin_resources_contracttypes_id']);
            $tmp['##resource.users##'] = Html::clean(getUserName($resource['users_id']));
            $tmp['##resource.usersrecipient##'] = Html::clean(getUserName($resource['users_id_recipient']));
            $tmp['##resource.datedeclaration##'] = Html::convDateTime($resource['date_declaration']);
            $tmp['##resource.datebegin##'] = Html::convDateTime($resource['date_begin']);
            $tmp['##resource.dateend##'] = Html::convDateTime($resource['date_end']);
            $tmp['##resource.department##'] = Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                       $resource['plugin_resources_departments_id']);
            $tmp['##resource.status##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                       $resource['plugin_resources_resourcestates_id']);
            $tmp['##resource.location##'] = Dropdown::getDropdownName('glpi_locations',
                                                       $resource['locations_id']);
            $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $resource['comment']));
            $tmp['##resource.comment##'] = Html::clean($comment);
            $tmp['##resource.usersleaving##'] = Html::clean(getUserName($resource['users_id_recipient_leaving']));
            $tmp['##resource.leaving##'] = Dropdown::getYesNo($resource['is_leaving']);
            $tmp['##resource.leavingreason##'] = Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
                                                      $resource['plugin_resources_leavingreasons_id']);
            $tmp['##resource.helpdesk##'] = Dropdown::getYesNo($resource['is_helpdesk_visible']);
            $tmp['##resource.url##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=plugin_resources_".
                                    $resource['id']);
            
            $this->datas['resources'][] = $tmp;
         }
      } else if ($event == 'AlertArrivalChecklists' || $event == 'AlertLeavingChecklists') {
         
         $this->datas['##checklist.entity##'] =
                           Dropdown::getDropdownName('glpi_entities',
                                                     $options['entities_id']);
         $this->datas['##lang.checklist.entity##'] =$LANG['entity'][0];
         
         if ($event == 'AlertArrivalChecklists') {
            $checklist_type = PluginResourcesChecklist::RESOURCES_CHECKLIST_IN;
            $this->datas['##checklist.action##'] = $LANG['plugin_resources']['mailing'][21];
            $this->datas['##lang.checklist.title##'] = $LANG['plugin_resources']['mailing'][19];
         } else {
            $checklist_type = PluginResourcesChecklist::RESOURCES_CHECKLIST_OUT;
            $this->datas['##checklist.action##'] = $LANG['plugin_resources']['mailing'][22];
            $this->datas['##lang.checklist.title##'] = $LANG['plugin_resources']['mailing'][20];
         }
         $this->datas['##lang.checklist.title2##'] = $LANG['plugin_resources']['checklists'][6];
         
         $this->datas['##lang.checklist.id##'] = "ID";
         $this->datas['##lang.checklist.name##'] = $LANG['plugin_resources'][8];
         $this->datas['##lang.checklist.firstname##'] = $LANG['plugin_resources'][18];
         $this->datas['##lang.checklist.type##'] = $LANG['plugin_resources'][20];
         $this->datas['##lang.checklist.users##'] = $LANG['plugin_resources'][47];
         $this->datas['##lang.checklist.usersrecipient##'] = $LANG['plugin_resources'][2];
         $this->datas['##lang.checklist.datedeclaration##'] = $LANG['plugin_resources'][74];
         $this->datas['##lang.checklist.datebegin##'] = $LANG['plugin_resources'][11];
         $this->datas['##lang.checklist.dateend##'] = $LANG['plugin_resources'][13];
         $this->datas['##lang.checklist.department##'] = $LANG['plugin_resources'][14];
         $this->datas['##lang.checklist.status##'] = $LANG['plugin_resources'][24];
         $this->datas['##lang.checklist.location##'] = $LANG['plugin_resources'][5];
         $this->datas['##lang.checklist.comment##'] = $LANG['plugin_resources'][12];
         $this->datas['##lang.checklist.usersleaving##'] = $LANG['plugin_resources'][73];
         $this->datas['##lang.checklist.leaving##'] = $LANG['plugin_resources'][58];
//         $this->datas['##lang.checklist.leavingreason##'] = $LANG['plugin_resources'][79];
         $this->datas['##lang.checklist.helpdesk##'] = $LANG['software'][46];
         $this->datas['##lang.checklist.url##'] = "URL";
         
         foreach($options['checklists'] as $id => $checklist) {
            $tmp = array();
            
            $tmp['##checklist.id##'] = $checklist['plugin_resources_resources_id'];
            $tmp['##checklist.name##'] = $checklist['resource_name'];
            $tmp['##checklist.firstname##'] = $checklist['resource_firstname'];
            $tmp['##checklist.type##'] = Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                       $checklist['plugin_resources_contracttypes_id']);
            $tmp['##checklist.users##'] = Html::clean(getUserName($checklist['users_id']));
            $tmp['##checklist.usersrecipient##'] = Html::clean(getUserName($checklist['users_id_recipient']));
            $tmp['##checklist.datedeclaration##'] = Html::convDateTime($checklist['date_declaration']);
            $tmp['##checklist.datebegin##'] = Html::convDateTime($checklist['date_begin']);
            $tmp['##checklist.dateend##'] = Html::convDateTime($checklist['date_end']);
            $tmp['##checklist.department##'] = Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                       $checklist['plugin_resources_departments_id']);
            $tmp['##checklist.status##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                       $checklist['plugin_resources_resourcestates_id']);
            $tmp['##checklist.location##'] = Dropdown::getDropdownName('glpi_locations',
                                                       $checklist['locations_id']);
            $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $checklist['comment']));
            $tmp['##checklist.comment##'] = Html::clean($comment);
            $tmp['##checklist.usersleaving##'] = Html::clean(getUserName($checklist['users_id_recipient_leaving']));
            $tmp['##checklist.leaving##'] = Dropdown::getYesNo($checklist['is_leaving']);
//            $tmp['##checklist.leavingreason##'] = Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
//                                                   $checklist['plugin_resources_leavingreasons_id']);
            $tmp['##checklist.helpdesk##'] = Dropdown::getYesNo($checklist['is_helpdesk_visible']);
            $tmp['##checklist.url##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=plugin_resources_".
                                    $checklist['plugin_resources_resources_id']);

            
            $query = PluginResourcesChecklist::queryListChecklists($checklist['plugin_resources_resources_id'],$checklist_type);
         
            $tmp['##tasklist.name##'] = '';
            foreach ($DB->request($query) as $data) {
         
               $tmp['##tasklist.name##'].=$data["name"];
               if ($_SESSION["glpiis_ids_visible"] == 1) $tmp['##tasklist.name##'].=" (".$data["id"].")";
               $tmp['##tasklist.name##'].="\n";
            }

            $this->datas['checklists'][] = $tmp;
            
         }
      } /*else if ($event == 'ArrivalChecklists' || $event == 'LeavingChecklists') {
         
         $this->datas['##resource.entity##'] =
                           Dropdown::getDropdownName('glpi_entities',
                                                     $options['entities_id']);
         $this->datas['##lang.resource.entity##'] =$LANG['entity'][0];
         $this->datas['##resource.action##'] = $LANG['plugin_resources']['mailing'][19];

         $this->datas['##lang.resource.id##'] = "ID";
         $this->datas['##lang.resource.name##'] = $LANG['plugin_resources'][8];
         $this->datas['##lang.resource.firstname##'] = $LANG['plugin_resources'][18];
         $this->datas['##lang.resource.url##'] = "URL";      
         
         foreach($options['resources'] as $id => $resource) {
            $tmp = array();

            $tmp['##resource.name##'] = $resource['name'];
            $tmp['##resource.firstname##'] = $resource['firstname'];
            $tmp['##resource.url##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=plugin_resources_".
                                    $resource['id']);
            
            
            $this->datas['resources'][] = $tmp;
         }
      } */else if ($event == 'LeavingResource') {
         
         $this->datas['##resource.entity##'] =
                           Dropdown::getDropdownName('glpi_entities',
                                                     $this->obj->getField('entities_id'));
         $this->datas['##lang.resource.entity##'] =$LANG['entity'][0];
         $this->datas['##lang.resource.title##'] = $LANG['plugin_resources']['mailing'][12];
         
         $this->datas['##lang.resource.title2##'] = $LANG['plugin_resources']['mailing'][29];
         
         $this->datas['##lang.resource.id##'] = "ID";
         $this->datas['##resource.id##'] = $this->obj->getField("id");
         $this->datas['##lang.resource.name##'] = $LANG['plugin_resources'][8];
         $this->datas['##resource.name##'] = $this->obj->getField("name");
         
         $this->datas['##lang.resource.firstname##'] = $LANG['plugin_resources'][18];
         $this->datas['##resource.firstname##'] = $this->obj->getField("firstname");
         
         $this->datas['##lang.resource.type##'] = $LANG['plugin_resources'][20];
         $this->datas['##resource.type##'] =  Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                       $this->obj->getField('plugin_resources_contracttypes_id'));

         $this->datas['##lang.resource.situation##'] = $LANG['plugin_resources']['title'][0]." - ".$LANG['plugin_resources'][75];
         $this->datas['##resource.situation##'] =  Dropdown::getDropdownName('glpi_plugin_resources_resourcesituations',
                                                      $this->obj->getField('plugin_resources_resourcesituations_id'));

         $this->datas['##lang.resource.contractnature##'] = $LANG['plugin_resources'][76];
         $this->datas['##resource.contractnature##'] =  Dropdown::getDropdownName('glpi_plugin_resources_contractnatures',
                                                            $this->obj->getField('plugin_resources_contractnatures_id'));

         $this->datas['##lang.resource.quota##'] = $LANG['plugin_resources'][4];
         $this->datas['##resource.quota##'] =  $this->obj->getField('quota');

         $this->datas['##lang.resource.department##'] = $LANG['plugin_resources'][14];
         $this->datas['##resource.department##'] =  Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                       $this->obj->getField('plugin_resources_departments_id'));

         $this->datas['##lang.resource.rank##'] = $LANG['plugin_resources'][77];
         $this->datas['##resource.rank##'] =  Dropdown::getDropdownName('glpi_plugin_resources_ranks',
                                                $this->obj->getField('plugin_resources_ranks_id'));

         $this->datas['##lang.resource.speciality##'] = $LANG['plugin_resources'][78];
         $this->datas['##resource.speciality##'] =  Dropdown::getDropdownName('glpi_plugin_resources_resourcespecialities',
                                                      $this->obj->getField('plugin_resources_resourcespecialities_id'));

         $this->datas['##lang.resource.status##'] = $LANG['plugin_resources'][24];
         $this->datas['##resource.status##'] =  Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                       $this->obj->getField('plugin_resources_resourcestates_id'));
                                                       
         $this->datas['##lang.resource.users##'] = $LANG['plugin_resources'][47];
         $this->datas['##resource.users##'] =  Html::clean(getUserName($this->obj->getField("users_id")));
         
         $this->datas['##lang.resource.usersrecipient##'] = $LANG['plugin_resources'][2];
         $this->datas['##resource.usersrecipient##'] =  Html::clean(getUserName($this->obj->getField("users_id_recipient")));
         
         $this->datas['##lang.resource.datedeclaration##'] = $LANG['plugin_resources'][74];
         $this->datas['##resource.datedeclaration##'] = Html::convDate($this->obj->getField('date_declaration'));
         
         $this->datas['##lang.resource.datebegin##'] = $LANG['plugin_resources'][11];
         $this->datas['##resource.datebegin##'] = Html::convDate($this->obj->getField('date_begin'));
         
         $this->datas['##lang.resource.dateend##'] = $LANG['plugin_resources'][13];
         $this->datas['##resource.dateend##'] = Html::convDate($this->obj->getField('date_end'));
         
         $this->datas['##lang.resource.location##'] = $LANG['plugin_resources'][5];
         $this->datas['##resource.location##'] =  Dropdown::getDropdownName('glpi_locations',
                                                       $this->obj->getField('locations_id'));
         
         $this->datas['##lang.resource.helpdesk##'] = $LANG['software'][46];                                        
         $this->datas['##resource.helpdesk##'] =  Dropdown::getYesNo($this->obj->getField('is_helpdesk_visible'));
         
         $this->datas['##lang.resource.leaving##'] = $LANG['plugin_resources'][58];
         $this->datas['##resource.leaving##'] =  Dropdown::getYesNo($this->obj->getField('is_leaving'));

         $this->datas['##lang.resource.leavingreason##'] = $LANG['plugin_resources'][79];
         $this->datas['##resource.leavingreason##'] =  Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
            $this->obj->getField('plugin_resources_leavingreasons_id'));

         $this->datas['##lang.resource.usersleaving##'] = $LANG['plugin_resources'][73];
         $this->datas['##resource.usersleaving##'] = Html::clean(getUserName($this->obj->getField('users_id_recipient_leaving')));

         $this->datas['##lang.resource.comment##'] = $LANG['plugin_resources'][12];
         $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->obj->getField("comment")));
         $this->datas['##resource.comment##'] = Html::clean($comment);
         
         $this->datas['##lang.resource.url##'] = "URL";
         $this->datas['##resource.url##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=plugin_resources_".
                                    $this->obj->getField("id"));
         
         $this->datas['##lang.resource.badge##']=" ";
         if (isset($this->target_object->input['checkbadge'])) {
               if (!empty($this->target_object->input['checkbadge']))
                  $this->datas['##lang.resource.badge##'] = $LANG['plugin_resources']['mailing'][13];
               else
                  $this->datas['##lang.resource.badge##']=" ";
         }
                  
      } else {
      
         $events = $this->getAllEvents();

         $this->datas['##lang.resource.title##'] = $events[$event];

         $this->datas['##lang.resource.entity##'] = $LANG['entity'][0];
         $this->datas['##resource.entity##'] =
                              Dropdown::getDropdownName('glpi_entities',
                                                        $this->obj->getField('entities_id'));
         $this->datas['##resource.id##'] = $this->obj->getField("id");

         $this->datas['##lang.resource.name##'] = $LANG['plugin_resources'][8];
         $this->datas['##resource.name##'] = $this->obj->getField("name");

         $this->datas['##lang.resource.firstname##'] = $LANG['plugin_resources'][18];
         $this->datas['##resource.firstname##'] = $this->obj->getField("firstname");
         
         $this->datas['##lang.resource.type##'] = $LANG['plugin_resources'][20];
         $this->datas['##resource.type##'] =  Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                       $this->obj->getField('plugin_resources_contracttypes_id'));

         $this->datas['##lang.resource.situation##'] = $LANG['plugin_resources']['title'][0]." - ".$LANG['plugin_resources'][75];
         $this->datas['##resource.situation##'] =  Dropdown::getDropdownName('glpi_plugin_resources_resourcesituations',
            $this->obj->getField('plugin_resources_resourcesituations_id'));

         $this->datas['##lang.resource.contractnature##'] = $LANG['plugin_resources'][76];
         $this->datas['##resource.contractnature##'] =  Dropdown::getDropdownName('glpi_plugin_resources_contractnatures',
            $this->obj->getField('plugin_resources_contractnatures_id'));

         $this->datas['##lang.resource.quota##'] = $LANG['plugin_resources'][4];
         $this->datas['##resource.quota##'] =  $this->obj->getField('quota');

         $this->datas['##lang.resource.users##'] = $LANG['plugin_resources'][47];
         $this->datas['##resource.users##'] =  Html::clean(getUserName($this->obj->getField("users_id")));
         
         $this->datas['##lang.resource.usersrecipient##'] = $LANG['plugin_resources'][2];
         $this->datas['##resource.usersrecipient##'] =  Html::clean(getUserName($this->obj->getField("users_id_recipient")));
         
         $this->datas['##lang.resource.datedeclaration##'] = $LANG['plugin_resources'][74];
         $this->datas['##resource.datedeclaration##'] = Html::convDate($this->obj->getField('date_declaration'));
         
         $this->datas['##lang.resource.datebegin##'] = $LANG['plugin_resources'][11];
         $this->datas['##resource.datebegin##'] = Html::convDate($this->obj->getField('date_begin'));
         
         $this->datas['##lang.resource.dateend##'] = $LANG['plugin_resources'][13];
         $this->datas['##resource.dateend##'] = Html::convDate($this->obj->getField('date_end'));
         
         $this->datas['##lang.resource.department##'] = $LANG['plugin_resources'][14];
         $this->datas['##resource.department##'] =  Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                       $this->obj->getField('plugin_resources_departments_id'));

         $this->datas['##lang.resource.rank##'] = $LANG['plugin_resources'][77];
         $this->datas['##resource.rank##'] =  Dropdown::getDropdownName('glpi_plugin_resources_ranks',
            $this->obj->getField('plugin_resources_ranks_id'));

         $this->datas['##lang.resource.speciality##'] = $LANG['plugin_resources'][78];
         $this->datas['##resource.speciality##'] =  Dropdown::getDropdownName('glpi_plugin_resources_resourcespecialities',
            $this->obj->getField('plugin_resources_resourcespecialities_id'));

         $this->datas['##lang.resource.status##'] = $LANG['plugin_resources'][24];
         $this->datas['##resource.status##'] =  Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                       $this->obj->getField('plugin_resources_resourcestates_id'));
                                                       
         $this->datas['##lang.resource.location##'] = $LANG['plugin_resources'][5];
         $this->datas['##resource.location##'] =  Dropdown::getDropdownName('glpi_locations',
                                                       $this->obj->getField('locations_id'));
                                                       
         $this->datas['##lang.resource.comment##'] = $LANG['plugin_resources'][12];
         $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->obj->getField("comment")));
         $this->datas['##resource.comment##'] = Html::clean($comment);
         
         $this->datas['##lang.resource.usersleaving##'] = $LANG['plugin_resources'][73];
         $this->datas['##resource.usersleaving##'] =  Html::clean(getUserName($this->obj->getField("users_id_recipient_leaving")));
         
         $this->datas['##lang.resource.leaving##'] = $LANG['plugin_resources'][58];
         $this->datas['##resource.leaving##'] =  Dropdown::getYesNo($this->obj->getField('is_leaving'));

         $this->datas['##lang.resource.leavingreason##'] = $LANG['plugin_resources'][79];
         $this->datas['##resource.leavingreason##'] =  Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
            $this->obj->getField('plugin_resources_leavingreasons_id'));

         $this->datas['##lang.resource.helpdesk##'] = $LANG['software'][46];
         $this->datas['##resource.helpdesk##'] =  Dropdown::getYesNo($this->obj->getField('is_helpdesk_visible'));
                                                      
         $this->datas['##lang.resource.url##'] = "URL";
         $this->datas['##resource.url##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=plugin_resources_".
                                    $this->obj->getField("id"));
         
         if ($event == 'report') {
            
            $this->datas['##lang.resource.creationtitle##'] = $LANG['plugin_resources']['mailing'][2];
            
            $this->datas['##resource.login##'] =  "";
            $this->datas['##resource.email##'] =  "";
            
            $restrict = "`itemtype` = 'User' 
                        AND `plugin_resources_resources_id` = '".$this->obj->getField("id")."'";
            $items = getAllDatasFromTable("glpi_plugin_resources_resources_items",$restrict);
            if (!empty($items)) {
               foreach ($items as $item) {
                  $user = new User();
                  $user->getFromDB($item["items_id"]);
                  $this->datas['##resource.login##'] =  $user->fields["name"];
                  $this->datas['##resource.email##'] =  $user->getDefaultEmail();
               }
            }
            
            $this->datas['##lang.resource.login##'] = $LANG['login'][6];
            
            $this->datas['##lang.resource.creation##'] = $LANG['plugin_resources']['mailing'][23];
            $this->datas['##lang.resource.datecreation##'] = $LANG['plugin_resources']['mailing'][10];
            $this->datas['##resource.datecreation##'] = Html::convDate(date("Y-m-d"));
            
            $this->datas['##lang.resource.email##'] = $LANG['setup'][14];
            
            $this->datas['##lang.resource.informationtitle##'] = $LANG['plugin_resources']['mailing'][31];
            
            $PluginResourcesReportConfig = new PluginResourcesReportConfig();
            $PluginResourcesReportConfig->getFromDB($options['reports_id']);
      
            $this->datas['##lang.resource.informations##'] = $LANG['plugin_resources']['mailing'][30];
            $information = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br>", $PluginResourcesReportConfig->fields['information']));
            $this->datas['##resource.informations##'] =  Html::clean(nl2br($information));
            
            $this->datas['##lang.resource.commentaires##'] = $LANG['common'][25];
            $commentaire = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br>", $PluginResourcesReportConfig->fields['comment']));
            $this->datas['##resource.commentaires##'] =  Html::clean(nl2br($commentaire));
         }
         
         if ($event == 'newresting' || $event == 'updateresting' || $event == 'deleteresting') {
            
            $this->datas['##lang.resource.restingtitle##'] = $LANG['plugin_resources']['helpdesk'][15];
            
            $this->datas['##lang.resource.resting##'] = $LANG['plugin_resources']['helpdesk'][12];
            $this->datas['##lang.resource.datecreation##'] = $LANG['plugin_resources']['mailing'][10];
            $this->datas['##resource.datecreation##'] = Html::convDate(date("Y-m-d"));
            
            $PluginResourcesResourceResting = new PluginResourcesResourceResting();
            $PluginResourcesResourceResting->getFromDB($options['resting_id']);
            
            $this->datas['##lang.resource.location##'] = $LANG['plugin_resources']['helpdesk'][10];
            $this->datas['##resource.location##'] =  Dropdown::getDropdownName('glpi_locations',
                                                       $PluginResourcesResourceResting->fields['locations_id']);
                                                       
            $this->datas['##lang.resource.home##'] = $LANG['plugin_resources']['helpdesk'][11];
            $this->datas['##resource.home##'] =  Dropdown::getYesNo($PluginResourcesResourceResting->fields['at_home']);
         
            $this->datas['##lang.resource.datebegin##'] = $LANG['plugin_resources'][34];
            $this->datas['##resource.datebegin##'] = Html::convDate($PluginResourcesResourceResting->fields['date_begin']);
         
            $this->datas['##lang.resource.dateend##'] = $LANG['plugin_resources'][35];
            $this->datas['##resource.dateend##'] = Html::convDate($PluginResourcesResourceResting->fields['date_end']);
            
            $this->datas['##lang.resource.informationtitle##'] = $LANG['plugin_resources']['mailing'][31];
            
            $this->datas['##lang.resource.commentaires##'] = $LANG['common'][25];
            $commentaire = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br>", $PluginResourcesResourceResting->fields['comment']));
            $this->datas['##resource.commentaires##'] =  Html::clean(nl2br($commentaire));
            
            $this->datas['##lang.resource.openby##'] = $LANG['plugin_resources']['helpdesk'][16];
            $this->datas['##resource.openby##'] = Html::clean(getUserName(Session::getLoginUserID()));
            
            if (isset($options['oldvalues']) && !empty($options['oldvalues']))
               $this->target_object->oldvalues = $options['oldvalues'];
         }
         
         if ($event == 'newholiday' || $event == 'updateholiday' || $event == 'deleteholiday') {
            
            $this->datas['##lang.resource.holidaytitle##'] = $LANG['plugin_resources']['helpdesk'][25];
            
            $this->datas['##lang.resource.holiday##'] = $LANG['plugin_resources']['helpdesk'][21];
            $this->datas['##lang.resource.datecreation##'] = $LANG['plugin_resources']['mailing'][10];
            $this->datas['##resource.datecreation##'] = Html::convDate(date("Y-m-d"));
            
            $PluginResourcesResourceHoliday = new PluginResourcesResourceHoliday();
            $PluginResourcesResourceHoliday->getFromDB($options['holiday_id']);
         
            $this->datas['##lang.resource.datebegin##'] = $LANG['plugin_resources'][34];
            $this->datas['##resource.datebegin##'] = Html::convDate($PluginResourcesResourceHoliday->fields['date_begin']);
         
            $this->datas['##lang.resource.dateend##'] = $LANG['plugin_resources'][35];
            $this->datas['##resource.dateend##'] = Html::convDate($PluginResourcesResourceHoliday->fields['date_end']);
            
            $this->datas['##lang.resource.informationtitle##'] = $LANG['plugin_resources']['mailing'][31];
            
            $this->datas['##lang.resource.commentaires##'] = $LANG['common'][25];
            $commentaire = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br>", $PluginResourcesResourceHoliday->fields['comment']));
            $this->datas['##resource.commentaires##'] =  Html::clean(nl2br($commentaire));
            
            $this->datas['##lang.resource.openby##'] = $LANG['plugin_resources']['helpdesk'][16];
            $this->datas['##resource.openby##'] = Html::clean(getUserName(Session::getLoginUserID()));
            
            if (isset($options['oldvalues']) && !empty($options['oldvalues']))
               $this->target_object->oldvalues = $options['oldvalues'];
         }
          
         //old values infos
         if (isset($this->target_object->oldvalues) && !empty($this->target_object->oldvalues) && ($event=='update' || $event=='updateresting' || $event=='updateholiday')) {
            
            $this->datas['##lang.update.title##'] = $LANG['plugin_resources']['mailing'][28];
            
            $tmp = array();
               
            if (isset($this->target_object->oldvalues['name'])) {
               if (empty($this->target_object->oldvalues['name']))
                  $tmp['##update.name##'] = "---";
               else  
                  $tmp['##update.name##'] = $this->target_object->oldvalues['name'];
            }
            if (isset($this->target_object->oldvalues['firstname'])) {
               if (empty($this->target_object->oldvalues['firstname']))
                  $tmp['##update.firstname##'] = "---";
               else
                  $tmp['##update.firstname##'] = $this->target_object->oldvalues['firstname'];
            }
            
            if (isset($this->target_object->oldvalues['plugin_resources_contracttypes_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_contracttypes_id']))
                  $tmp['##update.type##'] = "---";
               else
                  $tmp['##update.type##'] = Dropdown::getDropdownName('glpi_plugin_resources_contracttypes',
                                                       $this->target_object->oldvalues['plugin_resources_contracttypes_id']);
            }
            
            if (isset($this->target_object->oldvalues['users_id'])) {
               if (empty($this->target_object->oldvalues['users_id']))
                  $tmp['##update.users##'] = "---";
               else
                  $tmp['##update.users##'] = Html::clean(getUserName($this->target_object->oldvalues['users_id']));
            }
            
            if (isset($this->target_object->oldvalues['users_id_recipient'])) {
               if (empty($this->target_object->oldvalues['users_id_recipient']))
                  $tmp['##update.usersrecipient##'] = "---";
               else
                  $tmp['##update.usersrecipient##'] = Html::clean(getUserName($this->target_object->oldvalues['users_id_recipient']));
            }
            
            if (isset($this->target_object->oldvalues['date_declaration'])) {
               if (empty($this->target_object->oldvalues['date_declaration']))
                  $tmp['##update.datedeclaration##'] = "---";
               else
                  $tmp['##update.datedeclaration##'] = Html::convDate($this->target_object->oldvalues['date_declaration']);
            }
            
            if (isset($this->target_object->oldvalues['date_begin'])) {
               if (empty($this->target_object->oldvalues['date_begin']))
                  $tmp['##update.datebegin##'] = "---";
               else
                  $tmp['##update.datebegin##'] = Html::convDate($this->target_object->oldvalues['date_begin']);
            }
            
            if (isset($this->target_object->oldvalues['date_end'])) {
               if (empty($this->target_object->oldvalues['date_end']))
                  $tmp['##update.dateend##'] = "---";
               else
                  $tmp['##update.dateend##'] = Html::convDate($this->target_object->oldvalues['date_end']);
            }

            if (isset($this->target_object->oldvalues['quota'])) {
               if (empty($this->target_object->oldvalues['quota']))
                  $tmp['##update.quota##'] = "---";
               else
                  $tmp['##update.quota##'] = $this->target_object->oldvalues['quota'];
            }

            if (isset($this->target_object->oldvalues['plugin_resources_departments_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_departments_id']))
                  $tmp['##update.department##'] = "---";
               else
                  $tmp['##update.department##'] = Dropdown::getDropdownName('glpi_plugin_resources_departments',
                                                       $this->target_object->oldvalues['plugin_resources_departments_id']);
            }
            
            if (isset($this->target_object->oldvalues['plugin_resources_resourcestates_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_resourcestates_id']))
                  $tmp['##update.status##'] = "---";
               else
                  $tmp['##update.status##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcestates',
                                                       $this->target_object->oldvalues['plugin_resources_resourcestates_id']);
            }

            if (isset($this->target_object->oldvalues['plugin_resources_resourcesituations_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_resourcesituations_id']))
                  $tmp['##update.situation##'] = "---";
               else
                  $tmp['##update.situation##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcesituations',
                     $this->target_object->oldvalues['plugin_resources_resourcesituations_id']);
            }

            if (isset($this->target_object->oldvalues['plugin_resources_contractnatures_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_contractnatures_id']))
                  $tmp['##update.contractnature##'] = "---";
               else
                  $tmp['##update.contractnature##'] = Dropdown::getDropdownName('glpi_plugin_resources_contractnatures',
                     $this->target_object->oldvalues['plugin_resources_contractnatures_id']);
            }

            if (isset($this->target_object->oldvalues['plugin_resources_ranks_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_ranks_id']))
                  $tmp['##update.rank##'] = "---";
               else
                  $tmp['##update.rank##'] = Dropdown::getDropdownName('glpi_plugin_resources_ranks',
                     $this->target_object->oldvalues['plugin_resources_ranks_id']);
            }

            if (isset($this->target_object->oldvalues['plugin_resources_resourcespecialities_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_resourcespecialities_id']))
                  $tmp['##update.speciality##'] = "---";
               else
                  $tmp['##update.speciality##'] = Dropdown::getDropdownName('glpi_plugin_resources_resourcespecialities',
                     $this->target_object->oldvalues['plugin_resources_resourcespecialities_id']);
            }

            if (isset($this->target_object->oldvalues['locations_id'])) {
               if (empty($this->target_object->oldvalues['locations_id']))
                  $tmp['##update.location##'] = "---";
               else
                  $tmp['##update.location##'] = Dropdown::getDropdownName('glpi_locations',
                                                       $this->target_object->oldvalues['locations_id']);
            }
            
            if (isset($this->target_object->oldvalues['comment'])) {
               if (empty($this->target_object->oldvalues['comment'])) {
                  $tmp['##update.comment##'] = "---";
               } else {
                  $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->target_object->oldvalues['comment']));
                  $tmp['##update.comment##'] = Html::clean($comment);
               }
            }
            
            if (isset($this->target_object->oldvalues['users_id_recipient_leaving'])) {
               if (empty($this->target_object->oldvalues['users_id_recipient_leaving']))
                  $tmp['##update.usersleaving##'] = "---";
               else
                  $tmp['##update.usersleaving##'] = Html::clean(getUserName($this->target_object->oldvalues['users_id_recipient_leaving']));
            }
            
            if (isset($this->target_object->oldvalues['is_leaving'])) {
               if (empty($this->target_object->oldvalues['is_leaving']))
                  $tmp['##update.leaving##'] = "---";
               else
                  $tmp['##update.leaving##'] = Dropdown::getYesNo($this->target_object->oldvalues['is_leaving']);
            }

            if (isset($this->target_object->oldvalues['plugin_resources_leavingreasons_id'])) {
               if (empty($this->target_object->oldvalues['plugin_resources_leavingreasons_id']))
                  $tmp['##update.leavingreason##'] = "---";
               else
                  $tmp['##update.leavingreason##'] = Dropdown::getDropdownName('glpi_plugin_resources_leavingreasons',
                     $this->target_object->oldvalues['plugin_resources_leavingreasons_id']);
            }

            if (isset($this->target_object->oldvalues['is_helpdesk_visible'])) {
               if (empty($this->target_object->oldvalues['is_helpdesk_visible']))
                  $tmp['##update.helpdesk##'] = "---";
               else
                  $tmp['##update.helpdesk##'] = Dropdown::getYesNo($this->target_object->oldvalues['is_helpdesk_visible']);
            }
            
            if (isset($this->target_object->oldvalues['at_home'])) {
               if (empty($this->target_object->oldvalues['at_home']))
                  $tmp['##update.home##'] = "---";
               else  
                  $tmp['##update.home##'] = Dropdown::getYesNo($this->target_object->oldvalues['at_home']);
            }

            $this->datas['updates'][] = $tmp;
         }

         //task infos
         $restrict = "`plugin_resources_resources_id`='".$this->obj->getField('id')."' AND `is_deleted` = 0";
         
         if (isset($options['tasks_id']) && $options['tasks_id']) {
            $restrict .= " AND `glpi_plugin_resources_tasks`.`id` = '".$options['tasks_id']."'";
         }
         $restrict .= " ORDER BY `name` DESC";
         $tasks = getAllDatasFromTable('glpi_plugin_resources_tasks',$restrict);
         
         $this->datas['##lang.task.title##'] = $LANG['plugin_resources']['mailing'][27];
         
         $this->datas['##lang.task.name##'] = $LANG['plugin_resources'][8];
         $this->datas['##lang.task.type##'] = $LANG['plugin_resources'][28];
         $this->datas['##lang.task.users##'] = $LANG['plugin_resources'][23];
         $this->datas['##lang.task.groups##'] = $LANG['common'][35];
         $this->datas['##lang.task.datebegin##'] = $LANG['plugin_resources'][34];
         $this->datas['##lang.task.dateend##'] = $LANG['plugin_resources'][35];
         $this->datas['##lang.task.planned##'] = $LANG['plugin_resources'][37];
         $this->datas['##lang.task.realtime##'] = $LANG['plugin_resources'][36];
         $this->datas['##lang.task.finished##'] = $LANG['plugin_resources'][26];
         $this->datas['##lang.task.comment##'] = $LANG['plugin_resources'][12];
         
         foreach ($tasks as $task) {
            $tmp = array();
            
            $tmp['##task.name##'] = $task['name'];
            $tmp['##task.type##'] = Dropdown::getDropdownName('glpi_plugin_resources_tasktypes',
                                                       $task['plugin_resources_tasktypes_id']);
            $tmp['##task.users##'] = Html::clean(getUserName($task['users_id']));
            $tmp['##task.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                       $task['groups_id']);
            $restrict = " `plugin_resources_tasks_id` = '".$task['id']."' ";
            $plans = getAllDatasFromTable("glpi_plugin_resources_taskplannings",$restrict);
            
            if (!empty($plans)) {
               foreach ($plans as $plan) {
                  $tmp['##task.datebegin##'] = Html::convDateTime($plan["begin"]);
                  $tmp['##task.dateend##'] = Html::convDateTime($plan["end"]);
               }
            } else {
               $tmp['##task.datebegin##'] = '';
               $tmp['##task.dateend##'] = '';
            }
            $tmp['##task.planned##'] = '';
            $tmp['##task.finished##'] = Dropdown::getYesNo($task['is_finished']);
            $tmp['##task.realtime##'] = Ticket::getActionTime($task["actiontime"]);
            $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['comment']));
            $tmp['##task.comment##'] = Html::clean($comment);
 

            $this->datas['tasks'][] = $tmp;
         }
      }
   }
   
   function getTags() {
      global $LANG;

      $tags = array('resource.id'               => 'ID',
                   'resource.name'              => $LANG['plugin_resources'][8],
                   'resource.firstname'         => $LANG['plugin_resources'][18],
                   'resource.type'              => $LANG['plugin_resources'][20],
                   'resource.quota'             => $LANG['plugin_resources'][4],
                   'resource.situation'         => $LANG['plugin_resources']['title'][0]." - ".$LANG['plugin_resources'][75],
                   'resource.contractnature'    => $LANG['plugin_resources'][76],
                   'resource.rank'              => $LANG['plugin_resources'][77],
                   'resource.speciality'        => $LANG['plugin_resources'][78],
                   'resource.users'             => $LANG['plugin_resources'][47],
                   'resource.usersrecipient'    => $LANG['plugin_resources'][2],
                   'resource.datedeclaration'   => $LANG['plugin_resources'][74],
                   'resource.datebegin'         => $LANG['plugin_resources'][11],
                   'resource.dateend'           => $LANG['plugin_resources'][13],
                   'resource.department'        => $LANG['plugin_resources'][14],
                   'resource.location'          => $LANG['plugin_resources'][5],
                   'resource.comment'           => $LANG['plugin_resources'][12],
                   'resource.usersleaving'      => $LANG['plugin_resources'][73],
                   'resource.leaving'           => $LANG['plugin_resources'][58],
                   'resource.leavingreason'     => $LANG['plugin_resources'][79],
                   'resource.helpdesk'          => $LANG['software'][46],
                   'update.name'                => $LANG['plugin_resources'][8],
                   'update.firstname'           => $LANG['plugin_resources'][18],
                   'update.type'                => $LANG['plugin_resources'][20],
                   'update.quota'               => $LANG['plugin_resources'][4],
                   'update.situation'           => $LANG['plugin_resources']['title'][0]." - ".$LANG['plugin_resources'][75],
                   'update.contractnature'      => $LANG['plugin_resources'][76],
                   'update.rank'                => $LANG['plugin_resources'][77],
                   'update.speciality'          => $LANG['plugin_resources'][78],
                   'update.users'               => $LANG['plugin_resources'][47],
                   'update.usersrecipient'      => $LANG['plugin_resources'][2],
                   'update.datedeclaration'     => $LANG['plugin_resources'][74],
                   'update.datebegin'           => $LANG['plugin_resources'][11],
                   'update.dateend'             => $LANG['plugin_resources'][13],
                   'update.department'          => $LANG['plugin_resources'][14],
                   'update.status'              => $LANG['plugin_resources'][24],
                   'update.location'            => $LANG['plugin_resources'][5],
                   'update.comment'             => $LANG['plugin_resources'][12],
                   'update.usersleaving'        => $LANG['plugin_resources'][73],
                   'update.leaving'             => $LANG['plugin_resources'][58],
                   'update.leavingreason'       => $LANG['plugin_resources'][79],
                   'update.helpdesk'            => $LANG['software'][46],
                   'task.name'                  => $LANG['plugin_resources'][8],
                   'task.type'                  => $LANG['plugin_resources'][28],
                   'task.users'                 => $LANG['plugin_resources'][23],
                   'task.groups'                => $LANG['common'][35],
                   'task.datebegin'             => $LANG['plugin_resources'][34],
                   'task.dateend'               => $LANG['plugin_resources'][35],
                   'task.planned'               => $LANG['plugin_resources'][37],
                   'task.realtime'              => $LANG['plugin_resources'][36],
                   'task.finished'              => $LANG['plugin_resources'][26],
                   'task.comment'               => $LANG['plugin_resources'][12]);
      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag,'label'=>$label,
                                   'value'=>true));
      }
      
      $this->addTagToList(array('tag'=>'resource',
                                'label'=>$LANG['plugin_resources']['mailing'][0],
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('new','update','delete','report','newresting','updateresting','deleteresting','newholiday','updateholiday','deleteholiday')));
      $this->addTagToList(array('tag'=>'updates',
                                'label'=>$LANG['plugin_resources']['mailing'][28],
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('update','updateresting','updateholiday')));
      $this->addTagToList(array('tag'=>'tasks',
                                'label'=>$LANG['plugin_resources']['mailing'][1],
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('newtask','updatetask','deletetask')));

      asort($this->tag_descriptions);
   }
}

?>