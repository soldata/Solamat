<?php
/*
 * @version $Id: ticketassigninformation.php 20130 2013-02-04 16:55:15Z moyo $
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
// Original Author of file: Nelly Mahu-Lasson
// Purpose of file:
// ----------------------------------------------------------------------

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"ticketassigninformation.php")) {
   $AJAX_INCLUDE = 1;
   define('GLPI_ROOT','..');
   include (GLPI_ROOT."/inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkLoginUser();

if (isset($_REQUEST['users_id_assign']) && $_REQUEST['users_id_assign'] > 0) {

   $ticket = new Ticket();

   $options2['field'][0]      = 5; // users_id assign
   $options2['searchtype'][0] = 'equals';
   $options2['contains'][0]   = $_REQUEST['users_id_assign'];
   $options2['link'][0]       = 'AND';

   $options2['field'][1]      = 12; // status
   $options2['searchtype'][1] = 'equals';
   $options2['contains'][1]   = 'notold';
   $options2['link'][1]       = 'AND';

   $options2['reset'] = 'reset';
   
   $url = $ticket->getSearchURL()."?".Toolbox::append_params($options2,'&amp;');

   echo "&nbsp;<a href='$url' title=\"".$LANG['joblist'][21]."\" target='_blank'>(".
         $LANG['joblist'][21]."&nbsp;:&nbsp;".
         $ticket->countActiveObjectsForTech($_REQUEST['users_id_assign']).")</a>";
}
?>