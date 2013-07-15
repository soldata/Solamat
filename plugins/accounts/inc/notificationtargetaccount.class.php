<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Accounts plugin for GLPI
 Copyright (C) 2003-2011 by the accounts Development Team.

 https://forge.indepnet.net/projects/accounts
 -------------------------------------------------------------------------

 LICENSE

 This file is part of accounts.

 accounts is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 accounts is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with accounts. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginAccountsNotificationTargetAccount extends NotificationTarget {

   const ACCOUNT_USER = 1900;
   const ACCOUNT_GROUP = 1901;
   const ACCOUNT_TECHUSER = 1902;
   const ACCOUNT_TECHGROUP = 1903;
   
   function getEvents() {
      global $LANG;
      return array ('new' => $LANG['plugin_accounts']['mailing'][4],
                     'ExpiredAccounts' => $LANG['plugin_accounts']['mailing'][0],
                     'AccountsWhichExpire' => $LANG['plugin_accounts']['mailing'][2]);
   }

   /**
    * Get additionnals targets for Tickets
    */
   function getAdditionalTargets($event='') {
      global $LANG;
      $this->addTarget(self::ACCOUNT_USER,$LANG['plugin_accounts'][18]);
      $this->addTarget(self::ACCOUNT_GROUP,$LANG['plugin_accounts'][19]);
      $this->addTarget(self::ACCOUNT_TECHUSER,$LANG['common'][10]);
      $this->addTarget(self::ACCOUNT_TECHGROUP,$LANG['common'][109]);
   }

   function getSpecificTargets($data,$options) {

      //Look for all targets whose type is Notification::ITEM_USER
      switch ($data['items_id']) {

         case self::ACCOUNT_USER :
            $this->getUserAddress();
            break;
         case self::ACCOUNT_GROUP :
            $this->getGroupAddress();
            break;
         case self::ACCOUNT_TECHUSER :
            $this->getUserTechAddress();
            break;
         case self::ACCOUNT_TECHGROUP :
            $this->getGroupTechAddress();
            break;
      }
   }

   //Get receipient
   function getUserAddress() {
      return $this->getUserByField ("users_id");
   }

   function getGroupAddress () {
      global $DB;

      $group_field = "groups_id";

      if (isset($this->obj->fields[$group_field])
                && $this->obj->fields[$group_field]>0) {

         $query = $this->getDistinctUserSql().
                   " FROM `glpi_users`
                    LEFT JOIN `glpi_groups_users` ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`)".
                   $this->getProfileJoinSql()."
                    WHERE `glpi_groups_users`.`groups_id` = '".$this->obj->fields[$group_field]."'";

         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }
   
   //Get receipient
   function getUserTechAddress() {
      return $this->getUserByField ("users_id_tech");
   }

   function getGroupTechAddress () {
      global $DB;

      $group_field = "groups_id_tech";

      if (isset($this->obj->fields[$group_field])
                && $this->obj->fields[$group_field]>0) {

         $query = $this->getDistinctUserSql().
                   " FROM `glpi_users`
                    LEFT JOIN `glpi_groups_users` ON (`glpi_groups_users`.`users_id` = `glpi_users`.`id`)".
                   $this->getProfileJoinSql()."
                    WHERE `glpi_groups_users`.`groups_id` = '".$this->obj->fields[$group_field]."'";

         foreach ($DB->request($query) as $data) {
            $this->addToAddressesList($data);
         }
      }
   }

   function getDatasForTemplate($event,$options=array()) {
      global $LANG, $CFG_GLPI;

      if ($event == 'new') {

         $this->datas['##lang.account.title##'] = $LANG['plugin_accounts']['mailing'][5];

         $this->datas['##lang.account.entity##'] = $LANG['entity'][0];
         $this->datas['##account.entity##'] =
                              Dropdown::getDropdownName('glpi_entities',
                                                        $this->obj->getField('entities_id'));
         $this->datas['##account.id##'] = sprintf("%07d",$this->obj->getField("id"));

         $this->datas['##lang.account.name##'] = $LANG['plugin_accounts'][7];
         $this->datas['##account.name##'] = $this->obj->getField("name");

         $this->datas['##lang.account.type##'] = $LANG['plugin_accounts'][12];
         $this->datas['##account.type##'] =  Dropdown::getDropdownName('glpi_plugin_accounts_accounttypes',
                                                             $this->obj->getField('plugin_accounts_accounttypes_id'));


         $this->datas['##lang.account.state##'] = $LANG['plugin_accounts'][9];
         $this->datas['##account.state##'] =  Dropdown::getDropdownName('glpi_plugin_accounts_accountstates',
                                                             $this->obj->getField('plugin_accounts_accountstates_id'));

         $this->datas['##lang.account.login##'] = $LANG['plugin_accounts'][2];
         $this->datas['##account.login##'] = $this->obj->getField("login");

         $this->datas['##lang.account.users##'] = $LANG['plugin_accounts'][18];
         $this->datas['##account.users##'] =  Html::clean(getUserName($this->obj->getField("users_id")));

         $this->datas['##lang.account.groups##'] = $LANG['plugin_accounts'][19];
         $this->datas['##account.groups##'] =  Dropdown::getDropdownName('glpi_groups',
                                                             $this->obj->getField('groups_id'));
         
         $this->datas['##lang.account.userstech##'] = $LANG['common'][10];
         $this->datas['##account.userstech##'] =  Html::clean(getUserName($this->obj->getField("users_id_tech")));

         $this->datas['##lang.account.groupstech##'] = $LANG['common'][109];
         $this->datas['##account.groupstech##'] =  Dropdown::getDropdownName('glpi_groups',
                                                             $this->obj->getField('groups_id_tech'));
                                                             
         $this->datas['##lang.account.location##'] = $LANG['common'][15];
         $this->datas['##account.location##'] =  Dropdown::getDropdownName('glpi_locations',
                                                             $this->obj->getField('locations_id'));
                                                             
         $this->datas['##lang.account.others##'] = $LANG['plugin_accounts'][16];
         $this->datas['##account.others##'] = $this->obj->getField("others");

         $this->datas['##lang.account.datecreation##'] = $LANG['plugin_accounts'][17];
         $this->datas['##account.datecreation##'] = Html::convDate($this->obj->getField('date_creation'));

         $this->datas['##lang.account.dateexpiration##'] = $LANG['plugin_accounts'][13];
         $this->datas['##account.dateexpiration##'] = Html::convDate($this->obj->getField('date_expiration'));

         $this->datas['##lang.account.comment##'] = $LANG['plugin_accounts'][10];
         $this->datas['##account.comment##'] = $this->obj->getField("comment");

         $this->datas['##lang.account.url##'] = $LANG['plugin_accounts']['mailing'][6];
         $this->datas['##account.url##'] = urldecode($CFG_GLPI["url_base"]."/index.php?redirect=plugin_accounts_".
                                    $this->obj->getField("id"));

      } else {

         $this->datas['##account.entity##'] =
                           Dropdown::getDropdownName('glpi_entities',
                                                     $options['entities_id']);
         $this->datas['##lang.account.entity##'] =$LANG['entity'][0];
         $this->datas['##account.action##'] = ($event=="ExpiredAccounts"?$LANG['plugin_accounts']['mailing'][0]:
                                                            $LANG['plugin_accounts']['mailing'][2]);

         $this->datas['##lang.account.name##'] = $LANG['plugin_accounts'][7];
         $this->datas['##lang.account.dateexpiration##'] = $LANG['plugin_accounts'][13];
         $this->datas['##lang.account.type##'] = $LANG['plugin_accounts'][12];
         $this->datas['##lang.account.state##'] = $LANG['plugin_accounts'][9];
         $this->datas['##lang.account.login##'] = $LANG['plugin_accounts'][2];
         $this->datas['##lang.account.users##'] = $LANG['plugin_accounts'][18];
         $this->datas['##lang.account.groups##'] = $LANG['plugin_accounts'][19];
         $this->datas['##lang.account.userstech##'] = $LANG['common'][10];
         $this->datas['##lang.account.groupstech##'] = $LANG['common'][109];
         $this->datas['##lang.account.location##'] = $LANG['common'][15];
         $this->datas['##lang.account.others##'] = $LANG['plugin_accounts'][16];
         $this->datas['##lang.account.datecreation##'] = $LANG['plugin_accounts'][17];
         $this->datas['##lang.account.dateexpiration##'] = $LANG['plugin_accounts'][13];
         $this->datas['##lang.account.comment##'] = $LANG['plugin_accounts'][10];

         foreach($options['accounts'] as $id => $account) {
            $tmp = array();

            $tmp['##account.name##'] = $account['name'];
            $tmp['##account.type##'] = Dropdown::getDropdownName('glpi_plugin_accounts_accounttypes',
                                                             $account['plugin_accounts_accounttypes_id']);
            $tmp['##account.state##'] = Dropdown::getDropdownName('glpi_plugin_accounts_accountstates',
                                                             $account['plugin_accounts_accountstates_id']);
            $tmp['##account.login##'] = $account['login'];
            $tmp['##account.users##'] = Html::clean(getUserName($account['users_id']));
            $tmp['##account.groups##'] = Dropdown::getDropdownName('glpi_groups',
                                                             $account['groups_id']);
            $tmp['##account.userstech##'] = Html::clean(getUserName($account['users_id_tech']));
            $tmp['##account.groupstech##'] = Dropdown::getDropdownName('glpi_groups',
                                                             $account['groups_id_tech']);
            $tmp['##account.location##'] = Dropdown::getDropdownName('glpi_locations',
                                                             $account['locations_id']);
            $tmp['##account.others##'] = $account['others'];
            $tmp['##account.datecreation##'] = Html::convDate($account['date_creation']);
            $tmp['##account.dateexpiration##'] = Html::convDate($account['date_expiration']);
            $tmp['##account.comment##'] = $account['comment'];

            $this->datas['accounts'][] = $tmp;
         }
      }
   }
   
   function getTags() {
      global $LANG;

      $tags = array('account.name' => $LANG['plugin_accounts'][7],
                   'account.type'   => $LANG['plugin_accounts'][10],
                   'account.state'  => $LANG['plugin_accounts'][9],
                   'account.login' => $LANG['plugin_accounts'][2],
                   'account.users'    => $LANG['plugin_accounts'][18],
                   'account.groups' => $LANG['plugin_accounts'][19],
                   'account.userstech'    => $LANG['common'][10],
                   'account.groupstech' => $LANG['common'][109],
                   'account.location' => $LANG['common'][15],
                   'account.others' => $LANG['plugin_accounts'][16],
                   'account.datecreation' => $LANG['plugin_accounts'][17],
                   'account.dateexpiration' => $LANG['plugin_accounts'][13],
                   'account.comment' => $LANG['plugin_accounts'][10]);
      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag,'label'=>$label,
                                   'value'=>true));
      }
      
      $this->addTagToList(array('tag'=>'accounts',
                                'label'=>$LANG['plugin_accounts']['mailing'][1],
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('AccountsWhichExpire','ExpiredAccounts')));

      asort($this->tag_descriptions);
   }
}

?>