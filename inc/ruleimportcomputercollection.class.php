<?php
/*
 * @version $Id: ruleimportcomputercollection.class.php 20130 2013-02-04 16:55:15Z moyo $
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
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// OCS Rules collection class
class RuleImportComputerCollection extends RuleCollection {

   // From RuleCollection
   public $stop_on_first_match = true;
   public $right               = 'rule_ocs';
   public $menu_option         = 'linkcomputer';

   // Specific ones
   ///Store the id of the ocs server
   var $ocsservers_id;


   /**
    * Constructor
   **/
   function __construct() {
   }


   function getTitle() {
      global $LANG;

      return $LANG['rulesengine'][57];
   }


   function prepareInputDataForProcess($input,$params) {
      global $DBocs;

      if (!isset($input['ocsid']) && isset($input['id'])) {
         $input['ocsid'] = $input['id'];
      }
      //Get informations about network ports
      $query = "SELECT *
                FROM `networks`
                WHERE `HARDWARE_ID` = '".$input['ocsid']."'";
      $result = $DBocs->query($query);

      $ipbacklist  = array('', '127.0.0.1', '0.0.0.0');
      $macbacklist = array('');

      foreach ($DBocs->request($query) as $data) {
         if (isset($data['IPSUBNET'])) {
            $input['IPSUBNET'][] = $data['IPSUBNET'];
         }
         if (isset($data['MACADDR']) && !in_array($data['MACADDR'], $macbacklist)) {
            $input['MACADDRESS'][] = $data['MACADDR'];
         }
         if (isset($data['IPADDRESS']) && !in_array($data['IPADDRESS'], $ipbacklist)) {
            $input['IPADDRESS'][] = $data['IPADDRESS'];
         }
      }
      return array_merge($input,$params);
   }


   function preProcessPreviewResults($output) {
      return OcsServer::previewRuleImportProcess($output);
   }

}

?>
