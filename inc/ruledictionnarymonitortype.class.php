<?php
/*
 * @version $Id: ruledictionnarymonitortype.class.php 20130 2013-02-04 16:55:15Z moyo $
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

class RuleDictionnaryMonitorType extends RuleDictionnaryDropdown {


   /**
    * Constructor
   **/
   function __construct() {
      parent::__construct('RuleDictionnaryMonitorType');
   }


   function getCriterias() {
      global $LANG;

      $criterias = array();
      $criterias['name']['field'] = 'name';
      $criterias['name']['name']  = $LANG['common'][17];
      $criterias['name']['table'] = 'glpi_monitortypes';

      return $criterias;
   }


   function getActions() {
      global $LANG;

      $actions = array();
      $actions['name']['name']          = $LANG['common'][17];
      $actions['name']['force_actions'] = array('append_regex_result', 'assign', 'regex_result');

      return $actions;
   }

}

?>
