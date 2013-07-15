<?php
/*
 * @version $Id: problemtask.class.php 21102 2013-06-10 07:02:37Z moyo $
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class ProblemTask extends CommonITILTask {


   function canCreate() {
      return (Session::haveRight('show_my_problem', '1')
              || Session::haveRight('edit_all_problem', '1'));
   }


   function canView() {
      return (Session::haveRight('show_all_problem', 1)
              || Session::haveRight('show_my_problem', 1));
   }


   function canUpdate() {
      return (Session::haveRight('edit_all_problem', 1)
              || Session::haveRight('show_my_problem', 1));
   }


   function canViewPrivates() {
      return true;
   }


   function canEditAll() {
      return Session::haveRight('edit_all_problem', 1);
   }


   /**
    * Is the current user have right to show the current task ?
    *
    * @return boolean
   **/
   function canViewItem() {
      return parent::canReadITILItem();
   }


   /**
    * Is the current user have right to create the current task ?
    *
    * @return boolean
   **/
   function canCreateItem() {

      if (!parent::canReadITILItem()) {
         return false;
      }

      $problem = new Problem();
      if ($problem->getFromDB($this->fields['problems_id'])) {
         return (Session::haveRight("edit_all_problem","1")
                 || (Session::haveRight("show_my_problem","1")
                     && ($problem->isUser(CommonITILObject::ASSIGN, Session::getLoginUserID())
                         || (isset($_SESSION["glpigroups"])
                             && $problem->haveAGroup(CommonITILObject::ASSIGN,
                                                    $_SESSION['glpigroups'])))));
      }

   }


   /**
    * Is the current user have right to update the current task ?
    *
    * @return boolean
   **/
   function canUpdateItem() {

      if (!parent::canReadITILItem()) {
         return false;
      }

      if ($this->fields["users_id"] != Session::getLoginUserID()
          && !Session::haveRight('edit_all_problem',1)) {
         return false;
      }

      return true;
   }


   /**
    * Is the current user have right to delete the current task ?
    *
    * @return boolean
   **/
   function canDeleteItem() {
      return $this->canUpdateItem();
   }


   /**
    * Populate the planning with planned ticket tasks
    *
    * @param $options options array must contains :
    *    - who ID of the user (0 = undefined)
    *    - who_group ID of the group of users (0 = undefined)
    *    - begin Date
    *    - end Date
    *
    * @return array of planning item
   **/
   static function populatePlanning($options=array()) {
      return parent::genericPopulatePlanning('ProblemTask', $options);
   }


   /**
    * Display a Planning Item
    *
    * @param $val Array of the item to display
    *
    * @return Already planned information
   **/
   static function getAlreadyPlannedInformation($val) {
      return parent::genericGetAlreadyPlannedInformation('ProblemTask', $val);
   }


   /**
    * Display a Planning Item
    *
    * @param $val Array of the item to display
    * @param $who ID of the user (0 if all)
    * @param $type position of the item in the time block (in, through, begin or end)
    * @param $complete complete display (more details)
    *
    * @return Nothing (display function)
   **/
   static function displayPlanningItem($val, $who, $type="", $complete=0) {
      return parent::genericDisplayPlanningItem('ProblemTask',$val, $who, $type, $complete);
   }


}
?>