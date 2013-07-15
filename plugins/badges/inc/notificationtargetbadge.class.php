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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginBadgesNotificationTargetBadge extends NotificationTarget {

   function getEvents() {
      global $LANG;
      return array ('ExpiredBadges' => $LANG['plugin_badges']['mailing'][0],
                     'BadgesWhichExpire' => $LANG['plugin_badges']['mailing'][2]);
   }

   function getDatasForTemplate($event,$options=array()) {
      global $LANG, $CFG_GLPI;
         
      $this->datas['##badge.entity##'] =
                        Dropdown::getDropdownName('glpi_entities',
                                                  $options['entities_id']);
      $this->datas['##lang.badge.entity##'] =$LANG['entity'][0];
      $this->datas['##badge.action##'] = ($event=="ExpiredBadges"?$LANG['plugin_badges']['mailing'][0]:
                                                         $LANG['plugin_badges']['mailing'][2]);
      
      $this->datas['##lang.badge.name##'] = $LANG['plugin_badges'][8];
      $this->datas['##lang.badge.dateexpiration##'] = $LANG['plugin_badges'][5];
      $this->datas['##lang.badge.serial##'] = $LANG['plugin_badges'][11];
      $this->datas['##lang.badge.users##'] = $LANG['plugin_badges'][33];

      foreach($options['badges'] as $id => $badge) {
         $tmp = array();

         $tmp['##badge.name##'] = $badge['name'];
         $tmp['##badge.serial##'] = $badge['serial'];
         $tmp['##badge.users##'] = Html::clean(getUserName($badge["users_id"]));
         $tmp['##badge.dateexpiration##'] = Html::convDate($badge['date_expiration']);

         $this->datas['badges'][] = $tmp;
      }
   }
   
   function getTags() {
      global $LANG;

      $tags = array('badge.name'            => $LANG['plugin_badges'][8],
                   'badge.serial'            => $LANG['plugin_badges'][11],
                   'badge.dateexpiration'    => $LANG['plugin_badges'][5],
                   'badge.users' => $LANG['plugin_badges'][33]);
      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag,'label'=>$label,
                                   'value'=>true));
      }
      
      $this->addTagToList(array('tag'=>'badges',
                                'label'=>$LANG['plugin_badges']['mailing'][4],
                                'value'=>false,
                                'foreach'=>true,
                                'events'=>array('BadgesWhichExpire','ExpiredBadges')));

      asort($this->tag_descriptions);
   }
}

?>