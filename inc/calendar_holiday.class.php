<?php
/*
 * @version $Id: calendar_holiday.class.php 20130 2013-02-04 16:55:15Z moyo $
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

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class Calendar_Holiday extends CommonDBRelation {

   public $auto_message_on_action = false;

   // From CommonDBRelation
   public $itemtype_1 = 'Calendar';
   public $items_id_1 = 'calendars_id';
   public $itemtype_2 = 'Holiday';
   public $items_id_2 = 'holidays_id';


   function canCreateItem() {

      $calendar = new Calendar();
      return $calendar->can($this->fields['calendars_id'],'w');
   }


   function prepareInputForAdd($input) {

      if (!isset($input['holidays_id'])
          || !isset($input['calendars_id'])
          || $input['calendars_id'] <= 0
          || $input['holidays_id'] <= 0) {
         return false;
      }
      return $input;
   }


   /**
    * Show holidays for a calendar
    *
    * @param $calendar Calendar object
   **/
   static function showForCalendar(Calendar $calendar) {
      global $DB, $CFG_GLPI, $LANG;

      $ID = $calendar->getField('id');
      if (!$calendar->can($ID,'r')) {
         return false;
      }

      $canedit = $calendar->can($ID,'w');

      $rand=mt_rand();
      echo "<form name='calendarholiday_form$rand' id='calendarholiday_form$rand' method='post'
             action='";
      echo Toolbox::getItemTypeFormURL(__CLASS__)."'>";

      echo "<div class='center'><table class='tab_cadre_fixehov'>";
      echo "<tr><th colspan='2'>".$LANG['common'][16]."</th>";
      echo "<th>".$LANG['buttons'][33]."</th>";
      echo "<th>".$LANG['buttons'][32]."</th>";
      echo "<th>".$LANG['calendar'][3]."</th>";
      echo "</tr>";

      $query = "SELECT DISTINCT `glpi_calendars_holidays`.`id` AS linkID,
                                `glpi_holidays`.*
                FROM `glpi_calendars_holidays`
                LEFT JOIN `glpi_holidays`
                     ON (`glpi_calendars_holidays`.`holidays_id` = `glpi_holidays`.`id`)
                WHERE `glpi_calendars_holidays`.`calendars_id` = '$ID'
                ORDER BY `glpi_holidays`.`name`";
      $result = $DB->query($query);

      $used = array();

      if ($DB->numrows($result) >0) {
         Session::initNavigateListItems('Holiday',
                                        $LANG['buttons'][15] ." = ". $calendar->fields["name"]);

         while ($data = $DB->fetch_array($result)) {
            Session::addToNavigateListItems('Holiday', $data["id"]);
            echo "<tr class='tab_bg_1'>";
            echo "<td width='10'>";
            if ($canedit) {
               echo "<input type='checkbox' name='item[".$data["linkID"]."]' value='1'>";
            } else {
               echo "&nbsp;";
            }
            echo "</td>";
            $used[$data['id']] = $data['id'];
            echo "<td><a href='".Toolbox::getItemTypeFormURL('Holiday')."?id=".$data['id']."'>".
                      $data["name"]."</a></td>";
            echo "<td>".Html::convDate($data["begin_date"])."</td>";
            echo "<td>".Html::convDate($data["end_date"])."</td>";
            echo "<td>".Dropdown::getYesNo($data["is_perpetual"])."</td>";
            echo "</tr>";
         }
      }

      if ($canedit) {
         echo "<tr class='tab_bg_2'><td class='right'  colspan='4'>";
         echo "<input type='hidden' name='calendars_id' value='$ID'>";
         Dropdown::show('Holiday', array('used'   => $used,
                                         'entity' => $calendar->fields["entities_id"]));
         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" class='submit'>";
         echo "</td></tr>";
      }

      echo "</table></div>";

      if ($canedit) {
         Html::openArrowMassives("calendarholiday_form$rand",true);
         Html::closeArrowMassives(array('delete' => $LANG['buttons'][6]));
      }
      Html::closeForm();
   }


   /**
    * Duplicate all holidays from a calendar to his clone
   **/
   function cloneCalendar($oldid, $newid) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `calendars_id` = '$oldid'";

      foreach ($DB->request($query) as $data) {
         unset($data['id']);
         $data['calendars_id'] = $newid;
         $data['_no_history']  = true;

         $this->add($data);
      }
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'Calendar' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry($LANG['calendar'][11],
                                              countElementsInTable($this->getTable(),
                                                                   "calendars_id = '".$item->getID()."'"));
               }
               return $LANG['calendar'][11];
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Calendar') {
         self::showForCalendar($item);
      }
      return true;
   }
}

?>