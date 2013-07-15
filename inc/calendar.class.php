<?php
/*
 * @version $Id: calendar.class.php 20130 2013-02-04 16:55:15Z moyo $
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
// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Class Calendar
class Calendar extends CommonDropdown {

   // From CommonDBTM
   var $dohistory = true;
   protected $forward_entity_to = array('CalendarSegment');

   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['Menu'][42];
      }
      return $LANG['buttons'][15];
   }


   function canCreate() {
      return Session::haveRight('calendar', 'w');
   }


   function canView() {
      return Session::haveRight('calendar', 'r');
   }


   function defineTabs($options=array()) {
      global $LANG;

      $ong = array();
      $this->addStandardTab('CalendarSegment', $ong, $options);
      $this->addStandardTab('Calendar_Holiday', $ong, $options);

      return $ong;
   }


   /** Clone a calendar to another entity : name is updated
    * @param $options array of new values to set
   **/
   function duplicate ($options=array()) {

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            if (isset($this->fields[$key])) {
               $this->fields[$key] = $val;
            }
         }
      }

      $input = $this->fields;
      $oldID = $input['id'];
      unset($input['id']);
      if ($newID = $this->add($input)) {
         $calhol = new Calendar_Holiday();
         $calhol->cloneCalendar($oldID, $newID);
         $calseg = new CalendarSegment();
         $calseg->cloneCalendar($oldID, $newID);
   
         $this->updateDurationCache($newID);
         return true;
      }
      return false;

   }


   function cleanDBonPurge() {
      global $DB;

      $query2 = "DELETE
                 FROM `glpi_calendars_holidays`
                 WHERE `calendars_id` = '".$this->fields['id']."'";
      $DB->query($query2);

      $query2 = "DELETE
                 FROM `glpi_calendarsegments`
                 WHERE `calendars_id` = '".$this->fields['id']."'";
      $DB->query($query2);
   }

   // TODO : override it : only check link item (SLA / entity) but not calendars_holidays and calendarsegments
//    function isUsed() {
//    }


   /**
    * is an holiday day ?
    *
    * @param $date date of the day to check
    *
    * @return boolean
   **/
   function isHoliday($date) {
      global $DB;

      $query = "SELECT COUNT(*) AS CPT
                FROM `glpi_calendars_holidays`
                INNER JOIN `glpi_holidays`
                     ON (`glpi_calendars_holidays`.`holidays_id` = `glpi_holidays`.`id`)
                WHERE `glpi_calendars_holidays`.`calendars_id` = '".$this->fields['id']."'
                      AND (('$date' <= `glpi_holidays`.`end_date`
                             AND '$date' >= `glpi_holidays`.`begin_date`)
                           OR (`glpi_holidays`.`is_perpetual` = 1
                               AND MONTH(`end_date`)*100 + DAY(`end_date`)
                                       >= ".date('nd',strtotime($date))."
                               AND MONTH(`begin_date`)*100 + DAY(`begin_date`)
                                       <= ".date('nd',strtotime($date))."
                              )
                          )";
      if ($result=$DB->query($query)) {
         return $DB->result($result, 0, 'CPT');
      }
      return false;
   }


   /**
    * Get active time between to date time for the active calendar
    *
    * @param $start datetime begin
    * @param $end datetime end
    * @param $work_in_days boolean force working in days
    *
    * @return timestamp of delay
   **/
   function getActiveTimeBetween($start, $end, $work_in_days=false) {

      if (!isset($this->fields['id'])) {
         return false;
      }

      if ($end<$start) {
         return 0;
      }

      $timestart  = strtotime($start);
      $timeend    = strtotime($end);
      $datestart  = date('Y-m-d',$timestart);
      $dateend    = date('Y-m-d',$timeend);
      // Need to finish at the closing day : set hour to midnight :  
      /// Before PHP 5.3 need to be 23:59:59 and not 24:00:00
      $timerealend = strtotime($dateend.' 23:59:59');

      $activetime = 0;

      if ($work_in_days) {
         $activetime = $timeend-$timestart;

      } else {
         $cache_duration = $this->getDurationsCache();

         for ($actualtime=$timestart ; $actualtime<=$timerealend ; $actualtime+=DAY_TIMESTAMP) {
            $actualdate = date('Y-m-d',$actualtime);

            if (!$this->isHoliday($actualdate)) {
               $beginhour    = '00:00:00';
               /// Before PHP 5.3 need to be 23:59:59 and not 24:00:00
               $endhour      = '23:59:59';
               $dayofweek    = self::getDayNumberInWeek($actualtime);
               $timeoftheday = 0;

               if ($actualdate==$datestart) { // First day : cannot use cache
                  $beginhour = date('H:i:s',$timestart);
               }

               if ($actualdate==$dateend) { // Last day : cannot use cache
                  $endhour = date('H:i:s',$timeend);
               }

               if (($actualdate==$datestart || $actualdate==$dateend)
                   && $cache_duration[$dayofweek]>0) {
                  $timeoftheday = CalendarSegment::getActiveTimeBetween($this->fields['id'],
                                                                        $dayofweek, $beginhour,
                                                                        $endhour);
               } else {
                  $timeoftheday = $cache_duration[$dayofweek];
               }
//                 echo "time of the day = $timeoftheday ".Html::timestampToString($timeoftheday).'<br>';
               $activetime+=$timeoftheday;
//                 echo "cumulate time = $activetime ".Html::timestampToString($activetime).'<br>';

            } else {
//                 echo "$actualdate is an holiday<br>";
            }
         }
      }
      return $activetime;
   }


   /**
    * Add a delay to a date using the active calendar
    *
    * if delay >= DAY_TIMESTAMP : work in days
    * else work in minutes
    *
    * @param $start datetime begin
    * @param $delay timestamp delay to add
    * @param $work_in_days boolean force working in days
    *
    * @return end date
   **/
   function computeEndDate($start, $delay, $work_in_days=false) {

      if (!isset($this->fields['id'])) {
         return false;
      }

      $actualtime = strtotime($start);
      $timestart  = strtotime($start);
      $datestart  = date('Y-m-d',$timestart);

      if ($work_in_days) { // only based on days
         $cache_duration = $this->getDurationsCache();

         // Compute Real starting time
         // If day is an holiday must start on the begin of next working day
         $actualdate = date('Y-m-d',$actualtime);
         $dayofweek  = self::getDayNumberInWeek($actualtime);
         if ($this->isHoliday($actualdate) || $cache_duration[$dayofweek] == 0) {
            while ($this->isHoliday($actualdate) || $cache_duration[$dayofweek] == 0) {
               $actualtime += DAY_TIMESTAMP;
               $actualdate  = date('Y-m-d',$actualtime);
               $dayofweek   = self::getDayNumberInWeek($actualtime);
            }
            $firstworkhour = CalendarSegment::getFirstWorkingHour($this->fields['id'],
                                                                  $dayofweek);
            $actualtime    = strtotime($actualdate.' '.$firstworkhour);
         }

         while ($delay>0) {
            // Begin next day : do not take into account first day : must finish to a working day
            $actualtime += DAY_TIMESTAMP;
            $actualdate = date('Y-m-d',$actualtime);
            $dayofweek  = self::getDayNumberInWeek($actualtime);

            if (!$this->isHoliday($actualdate) && $cache_duration[$dayofweek]>0 ) {
                  $delay -= DAY_TIMESTAMP;
            }
            if ($delay<0) { // delay done : if < 0 delete hours
               $actualtime += $delay;
            }
         }

         // If > last working hour set last working hour
         $dayofweek   = self::getDayNumberInWeek($actualtime);
         $lastworkinghour = CalendarSegment::getLastWorkingHour($this->fields['id'], $dayofweek);
         if ($lastworkinghour< date('H:i:s', $actualtime)) {
            $actualtime    = strtotime(date('Y-m-d',$actualtime).' '.$lastworkinghour);
         }

         return date('Y-m-d H:i:s', $actualtime);
      }

      // else  // based on working hours
      $cache_duration = $this->getDurationsCache();

      // Only if segments exists
      if (countElementsInTable('glpi_calendarsegments',
                               "`calendars_id` = '".$this->fields['id']."'")) {
          while ($delay>=0) {
            $actualdate = date('Y-m-d',$actualtime);
               if (!$this->isHoliday($actualdate)) {
                  $dayofweek = self::getDayNumberInWeek($actualtime);
                  $beginhour = '00:00:00';
                  /// Before PHP 5.3 need to be 23:59:59 and not 24:00:00
                  $endhour   = '23:59:59';

                  if ($actualdate==$datestart) { // First day cannot use cache
                     $beginhour    = date('H:i:s',$timestart);
                     $timeoftheday = CalendarSegment::getActiveTimeBetween($this->fields['id'],
                                                                           $dayofweek, $beginhour,
                                                                           $endhour);
                  } else {
                     $timeoftheday = $cache_duration[$dayofweek];
                  }

                  // Day do not complete the delay : pass to next day
                  if ($timeoftheday<$delay) {
                     $actualtime += DAY_TIMESTAMP;
                     $delay      -= $timeoftheday;

                  } else { // End of the delay in the day : get hours with this delay
                     $beginhour = '00:00:00';
                     /// Before PHP 5.3 need to be 23:59:59 and not 24:00:00
                     $endhour   = '23:59:59';

                     if ($actualdate==$datestart) {
                        $beginhour = date('H:i:s',$timestart);
                     }

                     $endhour = CalendarSegment::addDelayInDay($this->fields['id'], $dayofweek,
                                                               $beginhour, $delay);
                     return $actualdate.' '.$endhour;
                  }

               } else { // Holiday : pass to next day
                     $actualtime += DAY_TIMESTAMP;
               }

         }
      }
      return false;
   }


   /**
    * Get days durations including all segments of the current calendar
    *
    * @return end date
   **/
   function getDurationsCache() {

      if (!isset($this->fields['id'])) {
         return false;
      }
      $cache_duration = importArrayFromDB($this->fields['cache_duration']);

      // Invalid cache duration : recompute it
      if (!isset($cache_duration[0])) {
         $this->updateDurationCache($this->fields['id']);
         $cache_duration = importArrayFromDB($this->fields['cache_duration']);
      }

      return $cache_duration;
   }


   /**
    * Get days durations including all segments of the current calendar
    *
    * @return end date
   **/
   function getDaysDurations() {

      if (!isset($this->fields['id'])) {
         return false;
      }

      $results = array();
      for ($i=0 ; $i<7 ; $i++) {
         /// Before PHP 5.3 need to be 23:59:59 and not 24:00:00
         $results[$i] = CalendarSegment::getActiveTimeBetween($this->fields['id'], $i, '00:00:00',
                                                              '23:59:59');
      }
      return $results;
   }


   /**
    * Update the calendar cache
   **/
   function updateDurationCache($calendars_id) {

      if ($this->getFromDB($calendars_id)) {
         $input['id']             = $calendars_id;
         $input['cache_duration'] = exportArrayToDB($this->getDaysDurations());
         return $this->update($input);
      }
      return false;
   }


   /**
    * Get day number (in week) for a date
    *
    * @param $date date
   **/
   static function getDayNumberInWeek($date) {
      return date('w', $date);
   }

}
?>
