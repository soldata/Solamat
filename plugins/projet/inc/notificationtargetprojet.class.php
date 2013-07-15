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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginProjetNotificationTargetProjet extends NotificationTarget {

   const PROJET_MANAGER = 2300;
   const PROJET_GROUP = 2301;
   const PROJET_TASK_TECHNICIAN = 2302;
   const PROJET_TASK_GROUP = 2303;
   const PROJET_TASK_CONTACT = 2304;
   
   function getEvents() {
      global $LANG;
      return array ('new' => $LANG['plugin_projet']['mailing'][4],
                     'update' => $LANG['plugin_projet']['mailing'][5],
                     'delete' => $LANG['plugin_projet']['mailing'][6],
                     'newtask' => $LANG['plugin_projet']['mailing'][7],
                     'updatetask' => $LANG['plugin_projet']['mailing'][8],
                     'deletetask' => $LANG['plugin_projet']['mailing'][9],
                     'AlertExpiredTasks' => $LANG['plugin_projet']['mailing'][15],
                     );
   }

   /**
    * Get additionnals targets for Tickets
    */
   function getAdditionalTargets($event='') {
      global $LANG;
      
      if ($event != 'AlertExpiredTasks') {
         $this->addTarget(PluginProjetNotificationTargetProjet::PROJET_MANAGER,
                           $LANG['plugin_projet']['mailing'][2]);
         $this->addTarget(PluginProjetNotificationTargetProjet::PROJET_GROUP,
                           $LANG['plugin_projet']['mailing'][13]);
         if ($event == 'newtask' || $event == 'updatetask' || $event == 'deletetask') {
            $this->addTarget(PluginProjetNotificationTargetProjet::PROJET_TASK_TECHNICIAN,
                              $LANG['plugin_projet']['mailing'][3]);
            $this->addTarget(PluginProjetNotificationTargetProjet::PROJET_TASK_GROUP,
                              $LANG['plugin_projet']['mailing'][11]);
            $this->addTarget(PluginProjetNotificationTargetProjet::PROJET_TASK_CONTACT,
                              $LANG['plugin_projet']['mailing'][12]);
         }
      }
   }

   function getSpecificTargets($data,$options) {

      //Look for all targets whose type is Notification::ITEM_USER
      switch ($data['items_id']) {

         case PluginProjetNotificationTargetProjet::PROJET_MANAGER :
            $this->getManagerAddress();
            break;
         case PluginProjetNotificationTargetProjet::PROJET_GROUP :
            $this->getGroupAddress();
            break;
         case PluginProjetNotificationTargetProjet::PROJET_TASK_TECHNICIAN :
            $this->getTaskTechAddress($options);
            break;
         case PluginProjetNotificationTargetProjet::PROJET_TASK_GROUP :
            $this->getTaskGroupAddress($options);
            break;
         case PluginProjetNotificationTargetProjet::PROJET_TASK_CONTACT :
            $this->getTaskContactAddress($options);
            break;
      }
   }

   //Get recipient
   function getManagerAddress() {
      return $this->getUserByField ("users_id");
   }
   
   function getGroupAddress () {
      global $DB;

      $group_field = "groups_id";

      if (isset($this->obj->fields[$group_field])
                && $this->obj->fields[$group_field]>0) {

         $query = $this->getDistinctUserSql().
                   " FROM `glpi_users`
                    LEFT JOIN `glpi_groups_users` 
                    ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`)
                    WHERE `glpi_groups_users`.`groups_id` = '".$this->obj->fields[$group_field]."'";

         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }
   
   function getTaskTechAddress($options=array()) {
      global $DB;

      if (isset($options['tasks_id'])) {
         $query = "SELECT DISTINCT `glpi_users`.`id` AS id,
                          `glpi_users`.`language` AS language
                   FROM `glpi_plugin_projet_tasks`
                   LEFT JOIN `glpi_users` ON (`glpi_users`.`id` = `glpi_plugin_projet_tasks`.`users_id`)
                   WHERE `glpi_plugin_projet_tasks`.`id` = '".$options['tasks_id']."'";

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
                    LEFT JOIN `glpi_groups_users` 
                    ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`) 
                    LEFT JOIN `glpi_plugin_projet_tasks` 
                    ON (`glpi_plugin_projet_tasks`.`groups_id` = `glpi_groups_users`.`groups_id`)
                    WHERE `glpi_plugin_projet_tasks`.`id` = '".$options['tasks_id']."'";
         
         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }
   
   function getTaskContactAddress() {
      global $DB;

      if (isset($this->obj->fields["contacts_id"])
          && $this->obj->fields["contacts_id"]>0
                && isset($options['tasks_id'])) {

         $query = "SELECT DISTINCT `glpi_contacts`.`email` AS email
                   FROM `glpi_contacts`
                   WHERE `glpi_contacts`.`id` = '".$this->obj->fields["contacts_id"]."'";

         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }

   function getDatasForTemplate($event,$options=array()) {
      global $LANG, $CFG_GLPI, $DB;
      
      if ($event == 'AlertExpiredTasks') {
         
         $this->datas['##projet.entity##'] =
                           Dropdown::getDropdownName('glpi_entities',
                                                     $options['entities_id']);
         $this->datas['##lang.projet.entity##'] =$LANG['entity'][0];
         $this->datas['##projet.action##'] = $LANG['plugin_projet']['mailing'][15];

         $this->datas['##lang.task.name##'] = $LANG['plugin_projet'][22];
         $this->datas['##lang.task.type##'] = $LANG['plugin_projet'][23];
         $this->datas['##lang.task.users##'] = $LANG['common'][34];
         $this->datas['##lang.task.groups##'] = $LANG['common'][35];
         $this->datas['##lang.task.datebegin##'] = $LANG['search'][8];
         $this->datas['##lang.task.dateend##'] = $LANG['search'][9];
         $this->datas['##lang.task.planned##'] = $LANG['plugin_projet'][67];
         $this->datas['##lang.task.realtime##'] = $LANG['plugin_projet'][72];
         $this->datas['##lang.task.comment##'] = $LANG['joblist'][6];
         $this->datas['##lang.task.projet##'] = $LANG['plugin_projet']['title'][1];
         
         foreach($options['tasks'] as $id => $task) {
            $tmp = array();

            $tmp['##task.name##'] = $task['name'];
            $tmp['##task.type##'] = Dropdown::getDropdownName('glpi_plugin_projet_tasktypes',
                                                       $task['plugin_projet_tasktypes_id']);
            $tmp['##task.users##'] = Html::clean(getUserName($task['users_id']));
            $tmp['##task.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                       $task['groups_id']);
            $restrict = " `plugin_projet_tasks_id` = '".$task['id']."' ";
            $plans = getAllDatasFromTable("glpi_plugin_projet_taskplannings",$restrict);
            
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
            $tmp['##task.realtime##'] = Ticket::getActionTime($task["actiontime"]);
            $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['comment']));
            $tmp['##task.comment##'] = Html::clean($comment);
            $tmp['##task.projet##'] = Dropdown::getDropdownName('glpi_plugin_projet_projets',
                                                       $task['plugin_projet_projets_id']);
                                                       
            $this->datas['tasks'][] = $tmp;
         }
      } else {
      
         $events = $this->getAllEvents();

         $this->datas['##lang.projet.title##'] = $events[$event];

         $this->datas['##lang.projet.entity##'] = $LANG['entity'][0];
         $this->datas['##projet.entity##'] =
                              Dropdown::getDropdownName('glpi_entities',
                                                        $this->obj->getField('entities_id'));
         $this->datas['##projet.id##'] = $this->obj->getField("id");

         $this->datas['##lang.projet.name##'] = $LANG['plugin_projet'][0];
         $this->datas['##projet.name##'] = $this->obj->getField("name");
         
         $this->datas['##lang.projet.datebegin##'] = $LANG['search'][8];
         $this->datas['##projet.datebegin##'] = Html::convDate($this->obj->getField('date_begin'));
         
         $this->datas['##lang.projet.dateend##'] = $LANG['search'][9];
         $this->datas['##projet.dateend##'] = Html::convDate($this->obj->getField('date_end'));
         
         $this->datas['##lang.projet.users##'] = $LANG['common'][34];
         $this->datas['##projet.users##'] =  Html::clean(getUserName($this->obj->getField("users_id")));
         
         $this->datas['##lang.projet.groups##'] = $LANG['common'][35];
         $this->datas['##projet.groups##'] =  Dropdown::getDropdownName('glpi_groups',
                                                       $this->obj->getField('groups_id'));
         
         $this->datas['##lang.projet.status##'] = $LANG['plugin_projet'][19];
         $this->datas['##projet.status##'] =  Dropdown::getDropdownName('glpi_plugin_projet_projetstates',
                                                       $this->obj->getField('plugin_projet_projetstates_id'));
         
         $this->datas['##lang.projet.parent##'] = $LANG['plugin_projet'][44];
         $this->datas['##projet.parent##'] =  PluginProjetProjet_Projet::displayLinkedProjetsTo($this->obj->getField('id'), true);
         
         $this->datas['##lang.projet.advance##'] = $LANG['plugin_projet'][47];
         $this->datas['##projet.advance##'] =  PluginProjetProjet::displayProgressBar('100',$this->obj->getField('advance'),array('simple'=>true));
         
         $this->datas['##lang.projet.gantt##'] = $LANG['plugin_projet'][70];
         $this->datas['##projet.gantt##'] =  Dropdown::getYesNo($this->obj->getField('show_gantt'));
         
         $this->datas['##lang.projet.comment##'] = $LANG['plugin_projet'][2];
         $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->obj->getField("comment")));
         $this->datas['##projet.comment##'] = Html::clean($comment);
         
         $this->datas['##lang.projet.description##'] = $LANG['plugin_projet'][10];
         $description = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->obj->getField("description")));
         $this->datas['##projet.description##'] = Html::clean($description);
         
         $this->datas['##lang.projet.helpdesk##'] = $LANG['software'][46];
         $this->datas['##projet.helpdesk##'] =  Dropdown::getYesNo($this->obj->getField('is_helpdesk_visible'));

         $this->datas['##lang.projet.url##'] = "URL";
         $this->datas['##projet.url##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=plugin_projet_".
                                    $this->obj->getField("id"));

          
         //old values infos
         if (isset($this->target_object->oldvalues) && !empty($this->target_object->oldvalues) && $event=='update') {
            
            $this->datas['##lang.update.title##'] = $LANG['plugin_projet']['mailing'][14];
            
            $tmp = array();
               
            if (isset($this->target_object->oldvalues['name'])) {
               if (empty($this->target_object->oldvalues['name']))
                  $tmp['##update.name##'] = "---";
               else  
                  $tmp['##update.name##'] = $this->target_object->oldvalues['name'];
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
            
            if (isset($this->target_object->oldvalues['users_id'])) {
               if (empty($this->target_object->oldvalues['users_id']))
                  $tmp['##update.users##'] = "---";
               else
                  $tmp['##update.users##'] = Html::clean(getUserName($this->target_object->oldvalues['users_id']));
            }
            
            if (isset($this->target_object->oldvalues['groups_id'])) {
               if (empty($this->target_object->oldvalues['groups_id']))
                  $tmp['##update.groups##'] = "---";
               else
                  $tmp['##update.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                       $this->target_object->oldvalues['groups_id']);
            }        
            
            if (isset($this->target_object->oldvalues['plugin_projet_projetstates_id'])) {
               if (empty($this->target_object->oldvalues['plugin_projet_projetstates_id']))
                  $tmp['##update.status##'] = "---";
               else
                  $tmp['##update.status##'] = Dropdown::getDropdownName('glpi_plugin_projet_projetstates',
                                                       $this->target_object->oldvalues['plugin_projet_projetstates_id']);
            }
            
            if (isset($this->target_object->oldvalues['plugin_projet_projets_id'])) {
               if (empty($this->target_object->oldvalues['plugin_projet_projets_id']))
                  $tmp['##update.plugin_projet_projets_id##'] = "---";
               else
                  $tmp['##update.plugin_projet_projets_id##'] = Dropdown::getDropdownName('glpi_plugin_projet_projets',
                                                       $this->target_object->oldvalues['plugin_projet_projets_id']);
            }
            
            if (isset($this->target_object->oldvalues['advance'])) {
               if (empty($this->target_object->oldvalues['advance']))
                  $tmp['##update.advance##'] = "---";
               else
                  $tmp['##update.advance##'] = PluginProjetProjet::displayProgressBar('100',$this->target_object->oldvalues['advance'],array('simple'=>true));
            }
            
            if (isset($this->target_object->oldvalues['comment'])) {
               if (empty($this->target_object->oldvalues['comment'])) {
                  $tmp['##update.comment##'] = "---";
               } else {
                  $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->target_object->oldvalues['comment']));
                  $tmp['##update.comment##'] = Html::clean($comment);
               }
            }
            
            if (isset($this->target_object->oldvalues['description'])) {
               if (empty($this->target_object->oldvalues['description'])) {
                  $tmp['##update.description##'] = "---";
               } else {
                  $description = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->target_object->oldvalues['description']));
                  $tmp['##update.description##'] = Html::clean($description);
               }
            }
            
            if (isset($this->target_object->oldvalues['show_gantt'])) {
               if (empty($this->target_object->oldvalues['show_gantt']))
                  $tmp['##update.gantt##'] = "---";
               else
                  $tmp['##update.gantt##'] = Dropdown::getYesNo($this->target_object->oldvalues['show_gantt']);
            }
            
            if (isset($this->target_object->oldvalues['is_helpdesk_visible'])) {
               if (empty($this->target_object->oldvalues['is_helpdesk_visible']))
                  $tmp['##update.helpdesk##'] = "---";
               else
                  $tmp['##update.helpdesk##'] = Dropdown::getYesNo($this->target_object->oldvalues['is_helpdesk_visible']);
            }

            $this->datas['updates'][] = $tmp;
         }

         //task infos
         $restrict = "`plugin_projet_projets_id`='".$this->obj->getField('id')."'";
         
         if (isset($options['tasks_id']) && $options['tasks_id']) {
            $restrict .= " AND `glpi_plugin_projet_tasks`.`id` = '".$options['tasks_id']."'";
         }
         $restrict .= " ORDER BY `name` DESC";
         $tasks = getAllDatasFromTable('glpi_plugin_projet_tasks',$restrict);
         
         $this->datas['##lang.task.title##'] = $LANG['plugin_projet']['mailing'][16];

         $this->datas['##lang.task.name##'] = $LANG['plugin_projet'][22];
         $this->datas['##lang.task.users##'] = $LANG['common'][34];
         $this->datas['##lang.task.groups##'] = $LANG['common'][35];
         $this->datas['##lang.task.contacts##'] = $LANG['financial'][26];
         $this->datas['##lang.task.type##'] = $LANG['plugin_projet'][23];
         $this->datas['##lang.task.status##'] = $LANG['plugin_projet'][19];
         $this->datas['##lang.task.advance##'] = $LANG['plugin_projet'][47];
         $this->datas['##lang.task.priority##'] = $LANG['plugin_projet'][41];
         $this->datas['##lang.task.comment##'] = $LANG['joblist'][6];
         $this->datas['##lang.task.sub##'] = $LANG['plugin_projet'][18];
         $this->datas['##lang.task.others##'] = $LANG['plugin_projet'][39];
         $this->datas['##lang.task.affect##'] = $LANG['plugin_projet'][40];
         $this->datas['##lang.task.parenttask##'] = $LANG['plugin_projet'][58];
         $this->datas['##lang.task.gantt##'] = $LANG['plugin_projet'][64];
         $this->datas['##lang.task.depends##'] = $LANG['plugin_projet'][55];
         $this->datas['##lang.task.realtime##'] = $LANG['plugin_projet'][72];
         $this->datas['##lang.task.location##'] = $LANG['common'][15];
         $this->datas['##lang.task.projet##'] = $LANG['plugin_projet']['title'][1];
         
         if (!empty($tasks)) {
               $this->datas['##task.title##'] = $LANG['plugin_projet']['mailing'][16];
            foreach ($tasks as $task) {
               $tmp = array();

               $tmp['##task.name##'] = $task['name'];
               $tmp['##task.users##'] = Html::clean(getUserName($task['users_id']));
               $tmp['##task.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                          $task['groups_id']);
               $tmp['##task.contacts##'] = Dropdown::getDropdownName('glpi_contacts',
                                                          $task['contacts_id']);
               $tmp['##task.type##'] = Dropdown::getDropdownName('glpi_plugin_projet_tasktypes',
                                                          $task['plugin_projet_tasktypes_id']);
               $tmp['##task.status##'] = Dropdown::getDropdownName('glpi_plugin_projet_taskstates',
                                                       $task['plugin_projet_taskstates_id']);
               $tmp['##task.advance##'] = PluginProjetProjet::displayProgressBar('100',$task['advance'],array('simple'=>true));
               $tmp['##task.priority##'] = Ticket::getPriorityName($task['priority']);
               $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['comment']));
               $tmp['##task.comment##'] = Html::clean($comment);
               $sub = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['sub']));
               $tmp['##task.sub##'] = Html::clean($sub);
               $others = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['others']));
               $tmp['##task.others##'] = Html::clean($others);
               $affect = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $task['affect']));
               $tmp['##task.affect##'] = Html::clean($affect);
               $tmp['##task.parenttask##'] = PluginProjetTask_Task::displayLinkedProjetTasksTo($task['id'],true);
               $tmp['##task.gantt##'] = Dropdown::getYesNo($task['show_gantt']);
               $tmp['##task.depends##'] = Dropdown::getYesNo($task['depends']);
               $tmp['##task.realtime##'] = Ticket::getActionTime($task["actiontime"]);
               $tmp['##task.location##'] = Dropdown::getDropdownName('glpi_locations',
                                                          $task['locations_id']);
               $tmp['##task.projet##'] = Dropdown::getDropdownName('glpi_plugin_projet_projets',
                                                          $task['plugin_projet_projets_id']);
                                                          
               $this->datas['tasks'][] = $tmp;
            }
         }
      }
   }
   
   function getTags() {
      global $LANG;

      $tags = array('projet.id'           => 'ID',
                     'projet.name'        => $LANG['plugin_projet'][0],
                     'projet.users'       => $LANG['common'][34],
                     'projet.groups'      => $LANG['common'][35],
                     'projet.datebegin'   => $LANG['search'][8],
                     'projet.dateend'     => $LANG['search'][9],
                     'projet.status'      => $LANG['plugin_projet'][19],
                     'projet.parent'      => $LANG['plugin_projet'][44],
                     'projet.advance'     => $LANG['plugin_projet'][47],
                     'projet.gantt'       => $LANG['plugin_projet'][70],
                     'projet.comment'     => $LANG['plugin_projet'][2],
                     'projet.description' => $LANG['plugin_projet'][10],
                     'projet.helpdesk'    => $LANG['software'][46],
                     'update.name'        => $LANG['plugin_projet'][0],
                     'update.users'       => $LANG['common'][34],
                     'update.groups'      => $LANG['common'][35],
                     'update.datebegin'   => $LANG['search'][8],
                     'update.dateend'     => $LANG['search'][9],
                     'update.status'      => $LANG['plugin_projet'][19],
                     'update.parent'      => $LANG['plugin_projet'][44],
                     'update.advance'     => $LANG['plugin_projet'][47],
                     'update.gantt'       => $LANG['plugin_projet'][70],
                     'update.comment'     => $LANG['plugin_projet'][2],
                     'update.description' => $LANG['plugin_projet'][10],
                     'update.helpdesk'    => $LANG['software'][46],
                     'task.name'          => $LANG['plugin_projet'][22],
                     'task.type'          => $LANG['plugin_projet'][23],
                     'task.status'        => $LANG['plugin_projet'][19],
                     'task.users'         => $LANG['common'][34],
                     'task.groups'        => $LANG['common'][35],
                     'task.contacts'      => $LANG['financial'][26],
                     'task.advance'       => $LANG['plugin_projet'][47],
                     'task.priority'      => $LANG['plugin_projet'][41],
                     'task.parenttask'    => $LANG['plugin_projet'][58],
                     'task.gantt'         => $LANG['plugin_projet'][64],
                     'task.realtime'      => $LANG['plugin_projet'][72],
                     'task.depends'       => $LANG['plugin_projet'][55],
                     'task.comment'       => $LANG['joblist'][6],
                     'task.sub'           => $LANG['plugin_projet'][18],
                     'task.others'        => $LANG['plugin_projet'][39],
                     'task.affect'        => $LANG['plugin_projet'][40]);
      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag,'label'=>$label,
                                   'value'=>true));
      }
      
      $this->addTagToList(array('tag'=>'projet',
                                'label'=>$LANG['plugin_projet']['mailing'][0],
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('new','update','delete')));
      $this->addTagToList(array('tag'=>'updates',
                                'label'=>$LANG['plugin_projet']['mailing'][14],
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('update')));
      $this->addTagToList(array('tag'=>'tasks',
                                'label'=>$LANG['plugin_projet']['mailing'][1],
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('newtask','updatetask','deletetask')));

      asort($this->tag_descriptions);
   }
}

?>