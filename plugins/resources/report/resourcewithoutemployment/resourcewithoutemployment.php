<?php
/*
 * @version $Id: resourcewithoutemployment.php 480 2012-11-09 tynet $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2006-2012 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 1;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

// Instantiate Report with Name
$report = new PluginReportsAutoReport();

// Columns title (optional), from $LANG
$report->setColumns( array(new PluginReportsColumnInteger('registration_number', $LANG['users'][17],
                                                   array('sorton' => 'registration_number')),
                           new PluginReportsColumnLink('resource_id', $LANG['common'][48],'PluginResourcesResource',
                                                   array('sorton' => 'resource_name')),
                           new PluginReportsColumn('firstname', $LANG['common'][43],
                                                   array('sorton' => 'firstname')),
                           new PluginReportsColumn('rank', $LANG['plugin_resources'][77],
                                                   array('sorton' => 'rank')),
                           new PluginReportsColumn('situation', $LANG['plugin_resources'][75],
                                                   array('sorton' => 'situation')),
                           new PluginReportsColumn('state', $LANG['plugin_resources'][24],
                                                   array('sorton' => 'state')),
                           new PluginReportsColumnDate('date_begin', $LANG['plugin_resources'][11],
                                                   array('sorton' => 'date_begin')),
                           new PluginReportsColumnDate('date_end', $LANG['plugin_resources'][13],
                                                   array('sorton' => 'date_end')),));

// SQL statement
$condition = getEntitiesRestrictRequest(' AND ',"glpi_plugin_resources_resources",'','',false);
$date=date("Y-m-d");

//display only resource without user linked
$query = "SELECT `glpi_users`.`registration_number`,
                 `glpi_users`.`id` as user_id,
                 `glpi_plugin_resources_resources`.`id` as resource_id,
                 `glpi_plugin_resources_resources`.`name` as resource_name,
                 `glpi_plugin_resources_resources`.`firstname`,
                 `glpi_plugin_resources_ranks`.`name` AS rank,
                 `glpi_plugin_resources_resourcesituations`.`name` AS situation,
                 `glpi_plugin_resources_resourcestates`.`name` AS state,
                 `glpi_plugin_resources_resources`.`date_begin`,
                 `glpi_plugin_resources_resources`.`date_end`
          FROM `glpi_users`
          LEFT JOIN `glpi_plugin_resources_resources_items`
               ON (`glpi_users`.`id` = `glpi_plugin_resources_resources_items`.`items_id`
                  AND `glpi_plugin_resources_resources_items`.`itemtype`= 'User')
          LEFT JOIN `glpi_plugin_resources_resources`
               ON (`glpi_plugin_resources_resources`.`id` = `glpi_plugin_resources_resources_items`.`plugin_resources_resources_id`)
          LEFT JOIN `glpi_plugin_resources_ranks`
               ON (`glpi_plugin_resources_resources`.`plugin_resources_ranks_id` = `glpi_plugin_resources_ranks`.`id`)
          LEFT JOIN `glpi_plugin_resources_resourcesituations`
               ON (`glpi_plugin_resources_resources`.`plugin_resources_resourcesituations_id` = `glpi_plugin_resources_resourcesituations`.`id`)
          LEFT JOIN `glpi_plugin_resources_resourcestates`
               ON (`glpi_plugin_resources_resources`.`plugin_resources_resourcestates_id` = `glpi_plugin_resources_resourcestates`.`id`)
          WHERE (`glpi_plugin_resources_resources`.`is_leaving` = 0
             AND `glpi_users`.`is_active` = 1
             AND `glpi_plugin_resources_resources`.`is_deleted` = '0'
             AND `glpi_plugin_resources_resources`.`is_template` = '0'
             AND `glpi_plugin_resources_resources`.`id` NOT IN
                     (SELECT DISTINCT(`plugin_resources_resources_id`)
                      FROM `glpi_plugin_resources_employments`
                      WHERE ((`glpi_plugin_resources_employments`.`end_date` IS NULL )
                          OR (`glpi_plugin_resources_employments`.`end_date` > '".$date."' ))
                          AND ((`glpi_plugin_resources_employments`.`begin_date` IS NULL)
                          OR ( `glpi_plugin_resources_employments`.`begin_date` < '".$date."')))
             ".$condition.")
                          AND ((`glpi_plugin_resources_resources`.`date_end` IS NULL )
                          OR (`glpi_plugin_resources_resources`.`date_end` > '".$date."' ))
                          AND ((`glpi_plugin_resources_resources`.`date_begin` IS NULL)
                          OR ( `glpi_plugin_resources_resources`.`date_begin` < '".$date."'))".
                     $report->getOrderBy('resource_id');


$report->setSqlRequest($query);
$report->execute();
?>