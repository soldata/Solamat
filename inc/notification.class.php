<?php
/*
 * @version $Id: notification.class.php 20130 2013-02-04 16:55:15Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// Class Notification
class Notification extends CommonDBTM {

// MAILING TYPE
//Notification to a user (sse mailing users type below)
   const USER_TYPE             = 1;
   //Notification to users of a profile
   const PROFILE_TYPE          = 2;
   //Notification to a users of a group
   const GROUP_TYPE            = 3;
   //Notification to the people in charge of the database synchronisation
   const MAILING_TYPE          = 4;
   //Notification to the supervisor of a group
   const SUPERVISOR_GROUP_TYPE = 5;

   // MAILING USERS TYPE

   //Notification to the GLPI global administrator
   const GLOBAL_ADMINISTRATOR       = 1;
   //Notification to the technicial who's assign to a ticket
   const ASSIGN_TECH                = 2;
   //Notification to the owner of the item
   const AUTHOR                     = 3;
   //Notification to the technician previously in charge of the ticket
   const OLD_TECH_IN_CHARGE         = 4;
   //Notification to the technician in charge of the item
   const ITEM_TECH_IN_CHARGE        = 5;
   //Notification to the item's user
   const ITEM_USER                  = 6;
   //Notification to the ticket's recipient
   const RECIPIENT                  = 7;
   //Notificartion to the ticket's assigned supplier
   const SUPPLIER                   = 8;
   //Notification to a group of people
   const ASSIGN_GROUP               = 9;
   //Notification to the supervisor of the ticket's assigned group
   const SUPERVISOR_ASSIGN_GROUP    = 10;
   //Notification to the entity administrator
   const ENTITY_ADMINISTRATOR       = 11;
   //Notification to the supervisor of the ticket's requester group
   const SUPERVISOR_REQUESTER_GROUP = 12;
   //Notification to the ticket's requester group
   const REQUESTER_GROUP            = 13;
   //Notification to the ticket validation approver
   const VALIDATION_APPROVER        = 14;
   //Notification to the ticket validation requester
   const VALIDATION_REQUESTER       = 15;
   //Notification to the task assign user
   const TASK_ASSIGN_TECH           = 16;
   //Notification to the task author
   const TASK_AUTHOR                = 17;
   //Notification to the followup author
   const FOLLOWUP_AUTHOR            = 18;
   //Notification to the user
   const USER                       = 19;
   //Notification to the ticket's observer group
   const OBSERVER_GROUP             = 20;
   //Notification to the ticket's observer user
   const OBSERVER                   = 21;
   //Notification to the supervisor of the ticket's observer group
   const SUPERVISOR_OBSERVER_GROUP  = 22;
   //Notification to the group of technician in charge of the item
   const ITEM_TECH_GROUP_IN_CHARGE  = 23;


   // From CommonDBTM
   public $dohistory = true;

   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['setup'][704];
      }
      return $LANG['setup'][705];
   }


   function defineTabs($options=array()) {
      global $LANG;

      $ong = array();
      $this->addStandardTab('NotificationTarget', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   function showForm($ID, $options=array()) {
      global $LANG,$CFG_GLPI;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'><td>" . $LANG['common'][16] . "&nbsp;:</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";

      echo "<td rowspan='6' class='middle right'>".$LANG['common'][25]."&nbsp;:</td>";
      echo "<td class='center middle' rowspan='6'><textarea cols='45' rows='9' name='comment' >"
            .$this->fields["comment"]."</textarea></td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['common'][60] . "&nbsp;:</td>";
      echo "<td>";
      Dropdown::showYesNo('is_active', $this->fields['is_active']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['common'][17] . "&nbsp;:</td>";
      echo "<td>";
      if (Session::haveRight('config', 'w') && $this->getEntityID() == 0) {
         $rand = Dropdown::dropdownTypes("itemtype", $this->fields['itemtype'],
                                         $CFG_GLPI["notificationtemplates_types"]);
      } else {
         $rand = Dropdown::dropdownTypes("itemtype", $this->fields['itemtype'],
                                         array_diff($CFG_GLPI["notificationtemplates_types"],
                                                    array('Crontask', 'DBConnection', 'User')));

      }

      $params = array('itemtype' => '__VALUE__');
      Ajax::updateItemOnSelectEvent("dropdown_itemtype$rand", "show_events",
                                    $CFG_GLPI["root_doc"]."/ajax/dropdownNotificationEvent.php",
                                    $params);
      Ajax::updateItemOnSelectEvent("dropdown_itemtype$rand", "show_templates",
                                    $CFG_GLPI["root_doc"]."/ajax/dropdownNotificationTemplate.php",
                                    $params);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['mailing'][120] . "&nbsp;:</td>";
      echo "<td>";
      self::dropdownMode($this->fields['mode']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['mailing'][119] . "&nbsp;:</td>";
      echo "<td><span id='show_events'>";
      NotificationEvent::dropdownEvents($this->fields['itemtype'],$this->fields['event']);
      echo "</span></td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['mailing'][112] . "&nbsp;:</td>";
      echo "<td><span id='show_templates'>";
      NotificationTemplate::dropdownTemplates('notificationtemplates_id', $this->fields['itemtype'],
                                              $this->fields['notificationtemplates_id']);
      echo "</span></td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();
      return true;
   }


   function getSearchOptions() {
      global $LANG;

      $tab = array();
      $tab['common'] = $LANG['common'][32];

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = $LANG['common'][16];
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      $tab[1]['massiveaction'] = false;

      $tab[2]['table']         = $this->getTable();
      $tab[2]['field']         = 'event';
      $tab[2]['name']          = $LANG['mailing'][119];
      $tab[2]['massiveaction'] = false;

      $tab[3]['table']         = $this->getTable();
      $tab[3]['field']         = 'mode';
      $tab[3]['name']          = $LANG['mailing'][120];
      $tab[3]['massiveaction'] = false;

      $tab[4]['table']         = 'glpi_notificationtemplates';
      $tab[4]['field']         = 'name';
      $tab[4]['name']          = $LANG['mailing'][113];
      $tab[4]['datatype']      = 'itemlink';
      $tab[4]['itemlink_type'] = 'NotificationTemplate';

      $tab[5]['table']         = $this->getTable();
      $tab[5]['field']         = 'itemtype';
      $tab[5]['name']          = $LANG['common'][17];
      $tab[5]['datatype']      = 'itemtypename';
      $tab[5]['massiveaction'] = false;

      $tab[6]['table']     = $this->getTable();
      $tab[6]['field']     = 'is_active';
      $tab[6]['name']      = $LANG['common'][60];
      $tab[6]['datatype']  = 'bool';

      $tab[16]['table']     = $this->getTable();
      $tab[16]['field']     = 'comment';
      $tab[16]['name']      = $LANG['common'][25];
      $tab[16]['datatype']  = 'text';

      $tab[80]['table']         = 'glpi_entities';
      $tab[80]['field']         = 'completename';
      $tab[80]['name']          = $LANG['entity'][0];
      $tab[80]['massiveaction'] = false;

      $tab[86]['table']     = $this->getTable();
      $tab[86]['field']     = 'is_recursive';
      $tab[86]['name']      = $LANG['entity'][9];
      $tab[86]['datatype']  = 'bool';

      return $tab;
   }


   function canCreate() {
      return Session::haveRight('notification', 'w');
   }


   function canView() {
      return Session::haveRight('notification', 'r');
   }


   function canViewItem() {

      if (($this->fields['itemtype'] == 'Crontask' || $this->fields['itemtype'] == 'DBConnection')
          && !Session::haveRight('config', 'w')) {
          return false;
      }
      return Session::haveAccessToEntity($this->getEntityID(), $this->isRecursive());
   }


   /**
    * Is the current user have right to update the current notification ?
    *
    * @return boolean
   **/
   function canCreateItem() {

      if (($this->fields['itemtype'] == 'Crontask' || $this->fields['itemtype'] == 'DBConnection')
          && !Session::haveRight('config', 'w')) {
          return false;
      }
      return Session::haveAccessToEntity($this->getEntityID());
   }


   /**
    * Display a dropdown with all the available notification modes
    * @param $value the default value for the dropdown
    */
   static function dropdownMode($value) {
      global $LANG;

      $modes['mail'] = $LANG['mailing'][118];
      Dropdown::showFromArray('mode', $modes, array('value' => $value));
   }


   /**
    * Get notification method label (email only for the moment)
    * @param $mode the mode to use
    *
    * @return the mode's label
    */
   static function getMode($mode) {
      global $LANG;

      return $LANG['mailing'][118];
   }


   function cleanDBonPurge() {
      global $DB;

      $query = "DELETE
                FROM `glpi_notificationtargets`
                WHERE `notifications_id` = '".$this->fields['id']."'";
      $DB->query($query);
   }


   static function send($mailing_options) {

      $mail = new NotificationMail();
      $mail->sendNotification($mailing_options);
      $mail->ClearAddresses();
   }


   /**
    * Get the mailing signature for the entity
    */
   static function getMailingSignature($entity) {
      global $DB, $CFG_GLPI;

      foreach ($DB->request('glpi_entitydatas', array('entities_id' => $entity)) as $data) {
         if (!empty($data['mailing_signature'])) {
            return $data['mailing_signature'];
         }
      }
      return $CFG_GLPI['mailing_signature'];

   }


   static function  getNotificationsByEventAndType($event,$itemtype,$entity) {
      global $DB;

      $query = "SELECT `glpi_notifications`.*
                FROM `glpi_notifications`
                LEFT JOIN `glpi_entities`
                  ON (`glpi_entities`.`id` = `glpi_notifications`.`entities_id`)
                WHERE `glpi_notifications`.`itemtype` = '$itemtype'
                      AND `glpi_notifications`.`event` = '$event' ".
                      getEntitiesRestrictRequest("AND", "glpi_notifications", 'entities_id',
                                                 $entity, true) ."
                      AND `glpi_notifications`.`is_active`='1'
                ORDER BY `glpi_entities`.`level` DESC";
// Toolbox::logDebug($query);
      return $DB->request($query);
   }

}
?>
