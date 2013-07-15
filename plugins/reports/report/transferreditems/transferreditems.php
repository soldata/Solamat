<?php
/*
 * @version $Id: transferreditems.php 198 2011-10-27 12:00:52Z remi $
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

// ----------------------------------------------------------------------
// Original Author of file: Stéphane SAVONA
// ----------------------------------------------------------------------
$USEDBREPLICATE       = 1;
$DBCONNECION_REQUIRED = 0;

define('GLPI_ROOT', '../../../..');
include(GLPI_ROOT."/inc/includes.php");

// Instanciation
$report= new PluginReportsAutoReport();

// Search criterias
new PluginReportsDateIntervalCriteria($report, "`glpi_logs`.`date_mod`");

$types = array();
foreach (array('Computer', 'Monitor', 'NetworkEquipment', 'Peripheral', 'Phone', 'Printer',
               'Software','SoftwareLicense') as $type) {
   $label       = call_user_func(array($type, 'getTypeName'));
   $types[$type] = $label;
}

ksort($types);
$typecritera = new PluginReportsItemTypeCriteria($report, "itemtype",$LANG['common'][17], $types);

$report->displayCriteriasForm();

// Declare columns
if($report->criteriasValidated()) {
   $itemtype = $_POST['itemtype'];
   $table = getTableForItemType($itemtype);

   $columns = array(new PluginReportsColumnLink('items_id', $LANG['common'][16],
                                                $itemtype, array('with_comment' => 1)),
                    new PluginReportsColumn('otherserial', $LANG['common'][20]),
                    new PluginReportsColumn('old_value',
                                            $LANG['plugin_reports']['transferreditems'][3]),
                    new PluginReportsColumn('new_value',
                                            $LANG['plugin_reports']['transferreditems'][4]),
                    new PluginReportsColumnDateTime('date_mod',
                                                    $LANG['plugin_reports']['transferreditems'][2]));
   $report->setColumnsNames($columns);

   $query = "SELECT `$table`.`id` as `items_id`,
                    `$table`.`name`,
                    `$table`.`otherserial`,
                    `glpi_logs`.`date_mod` as `date_mod`,
                    `glpi_logs`.`itemtype` as `itemtype`,
                    `glpi_logs`.`old_value`,
                    `glpi_logs`.`new_value`
             FROM `$table`, `glpi_logs` ".
             $report->addSqlCriteriasRestriction("WHERE")."
                   AND `glpi_logs`.`items_id` = `$table`.`id`
                   AND `glpi_logs`.`itemtype` = '$itemtype'
                   AND `glpi_logs`.`id_search_option`='80'
             ORDER BY `date_mod` ASC";

   $report->setSqlRequest($query);
   $report->execute();
}
?>