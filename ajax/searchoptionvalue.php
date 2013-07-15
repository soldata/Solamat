<?php
/*
 * @version $Id: searchoptionvalue.php 20130 2013-02-04 16:55:15Z moyo $
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

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"searchoptionvalue.php")) {
   define('GLPI_ROOT','..');
   include (GLPI_ROOT."/inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

if (isset($_REQUEST['searchtype'])) {
   $searchopt         = unserialize(stripslashes($_REQUEST['searchopt']));
   
   /// TODO clean it : used for glpi_complete_entites
   if (isset($searchopt['realtable']) && !empty($searchopt['realtable'])) {
      $searchopt['table'] = $searchopt['realtable'];
   }
   
   $_REQUEST['value'] = rawurldecode(stripslashes($_REQUEST['value']));

   $addmeta = "";
   if (isset($_REQUEST['meta']) && $_REQUEST['meta']) {
      $addmeta = '2';
   }

   $inputname = 'contains'.$addmeta.'['.$_REQUEST['num'].']';
   $display   = false;

   switch ($_REQUEST['searchtype']) {
      case "equals" :
      case "notequals" :
      case "morethan" :
      case "lessthan" :
      case "under" :
        // Specific cases with linkfield
//         if (!$display && isset($searchopt['linkfield'])) {
//             // Specific cases
//             switch ($searchopt['table'].".".$searchopt['linkfield']) {
//                case "glpi_users_validation.users_id_validate" :
//                   User::dropdown(array('name'     => $inputname,
//                                        'value'    => $_REQUEST['value'],
//                                        'comments' => false,
//                                        'right'    => 'validate_ticket'));
//                   $display = true;
//                   break;
//
//                case "glpi_users_validation.users_id" :
//                   User::dropdown(array('name'     => $inputname,
//                                        'value'    => $_REQUEST['value'],
//                                        'comments' => false,
//                                        'right'    => 'create_validation'));
//                   $display = true;
//                   break;
//             }
//          }

        // Specific cases with linkfield
//         if (!$display && isset($searchopt['linkfield'])) {
//             switch ($_REQUEST['itemtype'].".".$searchopt['linkfield']) {
//                case "Ticket.users_id_recipient" :
// //                case "Ticket.users_id" :
//                   User::dropdown(array('name'  => $inputname,
//                                        'value' => $_REQUEST['value'],
//                                        'right' => 'all'));
//                   $display = true;
//                   break;
//
// //                case "Ticket.users_id_assign" :
// //                   User::dropdown(array('name'  => $inputname,
// //                                        'value' => $_REQUEST['value'],
// //                                        'right' => 'own_ticket'));
// //                   $display = true;
// //                   break;
//             }
//         }

        if (!$display && isset($searchopt['field'])) {
            // Specific cases
            switch ($searchopt['table'].".".$searchopt['field']) {
               case "glpi_reminders.state" :
                  Planning::dropdownState($inputname, $_REQUEST['value']);
                  $display = true;
                  break;


               case "glpi_changes.status" :
                  Change::dropdownStatus($inputname, $_REQUEST['value'], 1);
                  $display = true;
                  break;

               case "glpi_changes.priority" :
                  Change::dropdownPriority($inputname, $_REQUEST['value'], true, true);
                  $display = true;
                  break;

               case "glpi_changes.impact" :
                  Change::dropdownImpact($inputname, $_REQUEST['value'], true);
                  $display = true;
                  break;

               case "glpi_changes.urgency" :
                  Change::dropdownUrgency($inputname, $_REQUEST['value'], true);
                  $display = true;
                  break;

               case "glpi_problems.status" :
                  Problem::dropdownStatus($inputname, $_REQUEST['value'], 1);
                  $display = true;
                  break;

               case "glpi_problems.priority" :
                  Problem::dropdownPriority($inputname, $_REQUEST['value'], true, true);
                  $display = true;
                  break;

               case "glpi_problems.impact" :
                  Problem::dropdownImpact($inputname, $_REQUEST['value'], true);
                  $display = true;
                  break;

               case "glpi_problems.urgency" :
                  Problem::dropdownUrgency($inputname, $_REQUEST['value'], true);
                  $display = true;
                  break;

               case "glpi_tickets.status" :
                  Ticket::dropdownStatus($inputname, $_REQUEST['value'], 1);
                  $display = true;
                  break;

               case "glpi_tickets.type" :
                  Ticket::dropdownType($inputname, array('value' => $_REQUEST['value']));
                  $display = true;
                  break;

               case "glpi_tickets.priority" :
                  Ticket::dropdownPriority($inputname, $_REQUEST['value'], true, true);
                  $display = true;
                  break;

               case "glpi_tickets.impact" :
                  Ticket::dropdownImpact($inputname, $_REQUEST['value'], true);
                  $display = true;
                  break;

               case "glpi_tickets.urgency" :
                  Ticket::dropdownUrgency($inputname, $_REQUEST['value'], true);
                  $display = true;
                  break;

               case "glpi_tickets.global_validation" :
                  TicketValidation::dropdownStatus($inputname, array('value'  => $_REQUEST['value'],
                                                                     'global' => true,
                                                                     'all'    => 1));
                  $display =true;
                  break;

               case "glpi_users.name" :
                  User::dropdown(array('name'     => $inputname,
                                       'value'    => $_REQUEST['value'],
                                       'comments' => false,
                                       'right'    => isset($searchopt['filter'])?$searchopt['filter']
                                                                                :'all'));
                  $display = true;
                  break;

               case "glpi_ticketvalidations.status" :
                  TicketValidation::dropdownStatus($inputname, array('value' => $_REQUEST['value'],
                                                                     'all'   => 1));
                  $display = true;
                  break;

               case "glpi_ticketsatisfactions.type" :
                  Dropdown::showFromArray($inputname,
                                          array(1 => $LANG['satisfaction'][9],
                                                2 => $LANG['satisfaction'][10]),
                                          array('value' => $_REQUEST['value']));
                  $display = true;
                  break;

               case "glpi_crontasks.state" :
                  CronTask::dropdownState($inputname, $_REQUEST['value']);
                  $display = true;
                  break;
            }

            // Standard datatype usage
            if (!$display && isset($searchopt['datatype'])) {
               switch ($searchopt['datatype']) {
                  case "bool" :
                     Dropdown::showYesNo($inputname, $_REQUEST['value']);
                     $display = true;
                     break;

                  case "right" :
                     // No access not displayed because empty not take into account for search
                     Profile::dropdownNoneReadWrite($inputname, $_REQUEST['value'], 1, 1, 1);
                     $display = true;
                     break;

                  case "itemtypename" :
                     Dropdown::dropdownUsedItemTypes($inputname,
                                                     getItemTypeForTable($searchopt['table']),
                                                     array('value'    => $_REQUEST['value'],
                                                           'comments' => 0));
                     $display = true;
                     break;

                  case "date" :
                  case "date_delay" :
                     Html::showGenericDateTimeSearch($inputname, $_REQUEST['value'],
                                                     array('with_time'   => false,
                                                           'with_future'
                                                               => (isset($searchopt['maybefuture'])
                                                                   && $searchopt['maybefuture'])));
                     $display = true;
                     break;

                  case "datetime" :
                     Html::showGenericDateTimeSearch($inputname, $_REQUEST['value'],
                                                     array('with_time'   => true,
                                                           'with_future'
                                                               => (isset($searchopt['maybefuture'])
                                                                   && $searchopt['maybefuture'])));
                     $display = true;
                     break;
               }
            }

            //Could display be handled by a plugin ?
            if (!$display && $plug = isPluginItemType(getItemTypeForTable($searchopt['table']))) {
               $function = 'plugin_'.$plug['plugin'].'_searchOptionsValues';
               if (function_exists($function)) {
                  $params = array('name'           => $inputname,
                                  'searchtype'     => $_REQUEST['searchtype'],
                                  'searchoption'   => $searchopt,
                                  'value'          => $_REQUEST['value']);
                  $display = $function($params);
               }
            }

            // Standard field usage
            if (!$display) {
               switch ($searchopt['field']) {
                  case "name" :
                  case "completename" :
                     $cond = (isset($searchopt['condition']) ? $searchopt['condition'] : '');
                     Dropdown::show(getItemTypeForTable($searchopt['table']),
                                    array('value'     => $_REQUEST['value'],
                                          'name'      => $inputname,
                                          'comments'  => 0,
                                          'condition' => $cond));
                     $display = true;
                     break;
               }
            }
        }
        break; //case "lessthan" :
   }

   // Default case : text field
   if (!$display) {
        echo "<input type='text' size='13' name='$inputname' value=\"".
               Html::cleanInputText($_REQUEST['value'])."\">";
   }
}

?>
