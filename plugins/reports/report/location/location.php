<?php
/*
 * @version $Id: location.php 204 2011-11-08 12:28:40Z remi $
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2011 by the reports Development Team.

 https://forge.indepnet.net/projects/reports
 -------------------------------------------------------------------------

 LICENSE

 This file is part of reports.

 reports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with reports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/*
 * ----------------------------------------------------------------------
 * Original Author of file: Remi Collet
 *
 * Purpose of file:
 * 		Generate location report
 * 		Illustrate use of simpleReport
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 0; // not really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

// Instantiate Report with Name
$report = new PluginReportsAutoReport();

// Columns title (optional), from $LANG
$report->setColumns(array(new PluginReportsColumn('entity', $LANG["entity"][0],
                                                  array('sorton' => 'entity,location')),
                          new PluginReportsColumn('location', $LANG["common"][15],
                                                  array('sorton' => 'location')),
                          new PluginReportsColumnLink('link', $LANG['title'][34],'Location',
                                                  array('sorton' => '`glpi_locations`.`name`'))));

// SQL statement
$query = "SELECT `glpi_entities`.`completename` AS entity,
                 `glpi_locations`.`completename` AS location,
                 `glpi_locations`.`id` AS link
          FROM `glpi_locations`
          LEFT JOIN `glpi_entities` ON (`glpi_locations`.`entities_id` = `glpi_entities`.`id`)" .
          getEntitiesRestrictRequest(" WHERE ", "glpi_locations") .
          $report->getOrderBy('entity');

$report->setGroupBy('entity');
$report->setSqlRequest($query);
$report->execute();
?>