<?php
/*
 * @version $Id: resourceemploymentwithlapserank.php 480 2012-11-09 tynet $
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

//colname with sort allowed
$columns = array('entity' => array('sorton' => 'entity'),
   'name' => array('sorton' => 'name'),
   'firstname' => array('sorton' => 'firstname'),
   'registration_number' => array('sorton' => 'registration_number'),
   'rank' => array('sorton' => 'rank'),
   'date_begin' => array('sorton' => 'date_begin'),
   'date_end' => array('sorton' => 'date_end'),
   'begin_date' => array('sorton' => 'begin_date'),
   'end_date' => array('sorton' => 'end_date'),);

$output_type = HTML_OUTPUT;

if (isset ($_POST['list_limit'])) {
   $_SESSION['glpilist_limit'] = $_POST['list_limit'];
   unset ($_POST['list_limit']);
}
if (!isset ($_REQUEST['sort'])) {
   $_REQUEST['sort'] = "entity";
   $_REQUEST['order'] = "ASC";
}

$limit = $_SESSION['glpilist_limit'];

if (isset ($_POST["display_type"])) {
   $output_type = $_POST["display_type"];
   if ($output_type < 0) {
      $output_type = - $output_type;
      $limit = 0;
   }
} else {
   $output_type = HTML_OUTPUT;
}

$title = $report->getFullTitle();

//to verify if resources exist
// SQL statement
$condition = getEntitiesRestrictRequest('AND','glpi_plugin_resources_resources');
$date=date("Y-m-d");
$dataAll=array();

//case resource
$query = "SELECT `glpi_plugin_resources_resources`.`entities_id` AS entity,
                  'Resource' AS typeName,
                    `glpi_plugin_resources_resources`.`name` AS name,
                    `glpi_plugin_resources_resources`.`id` AS ID,
                    `glpi_plugin_resources_resources`.`firstname` AS firstname,
                    `glpi_users`.`registration_number` AS registration_number,
                    `glpi_plugin_resources_ranks`.`id` AS rankID,
                    `glpi_plugin_resources_ranks`.`name` AS rank,
                    `glpi_plugin_resources_resources`.`date_begin`,
                    `glpi_plugin_resources_resources`.`date_end`,
                    `glpi_plugin_resources_ranks`.`begin_date`,
                    `glpi_plugin_resources_ranks`.`end_date`
             FROM `glpi_users`
             LEFT JOIN `glpi_plugin_resources_resources_items`
                  ON (`glpi_users`.`id` = `glpi_plugin_resources_resources_items`.`items_id`
                     AND `glpi_plugin_resources_resources_items`.`itemtype`= 'User')
             LEFT JOIN `glpi_plugin_resources_resources`
                  ON (`glpi_plugin_resources_resources`.`id` = `glpi_plugin_resources_resources_items`.`plugin_resources_resources_id`)
             LEFT JOIN `glpi_plugin_resources_ranks`
                  ON (`glpi_plugin_resources_resources`.`plugin_resources_ranks_id` = `glpi_plugin_resources_ranks`.`id`)
             WHERE ((`glpi_plugin_resources_resources`.`date_begin` < '".$date."'
                     AND (`glpi_plugin_resources_resources`.`date_end` IS NULL
                           OR `glpi_plugin_resources_resources`.`date_end` > '".$date."'))
                     AND `glpi_plugin_resources_ranks`.`id` IS NOT NULL
                     AND `glpi_plugin_resources_resources`.`is_leaving` = '0'
                     AND `glpi_plugin_resources_resources`.`is_deleted` = '0'
                     AND `glpi_plugin_resources_resources`.`is_template` = '0')
                     AND ((`glpi_plugin_resources_resources`.`date_begin` > `glpi_plugin_resources_ranks`.`end_date`
                          OR `glpi_plugin_resources_resources`.`date_end` < `glpi_plugin_resources_ranks`.`begin_date`)
                        OR (`glpi_plugin_resources_ranks`.`end_date` < '".$date."'
                            OR `glpi_plugin_resources_ranks`.`begin_date` > '".$date."'))";
//             WHERE ((`glpi_plugin_resources_resources`.`date_begin` > `glpi_plugin_resources_ranks`.`end_date`
//                    OR `glpi_plugin_resources_resources`.`date_end` < `glpi_plugin_resources_ranks`.`begin_date`
//                    OR `glpi_plugin_resources_resources`.`date_begin` < '".$date."'
//                       AND `glpi_plugin_resources_resources`.`date_end` IS NULL
//                       AND (`glpi_plugin_resources_ranks`.`end_date` < '".$date."'
//                            OR `glpi_plugin_resources_ranks`.`begin_date` > '".$date."'))
//                  AND `glpi_plugin_resources_ranks`.`id` IS NOT NULL
//                  AND `glpi_plugin_resources_resources`.`is_leaving` = '0'
//                  AND `glpi_plugin_resources_resources`.`is_deleted` = '0'
//                  AND `glpi_plugin_resources_resources`.`is_template` = '0')";

$conditionAll = getEntitiesRestrictRequest('AND', 'glpi_plugin_resources_resources','','',true);

$query.=$conditionAll." ".getOrderBy('entity', $columns);

$result = $DB->query($query);
for ($row_num = 0 ; $data=$DB->fetch_assoc($result); $row_num++) {
   $dataAll[$row_num]=$data;
}

//case employment
$queryEmploy = "SELECT `glpi_plugin_resources_employments`.`entities_id` AS entity,
                        'Employment' AS typeName,
                       `glpi_plugin_resources_employments`.`name` AS name,
                       `glpi_plugin_resources_employments`.`id` AS ID,
                       NULL AS firstname,
                       NULL AS registration_number,
                        `glpi_plugin_resources_ranks`.`id` AS rankID,
                        `glpi_plugin_resources_ranks`.`name` AS rank,
                    `glpi_plugin_resources_employments`.`begin_date` AS date_begin,
                    `glpi_plugin_resources_employments`.`end_date` AS date_end,
                    `glpi_plugin_resources_ranks`.`begin_date`,
                    `glpi_plugin_resources_ranks`.`end_date`
             FROM `glpi_plugin_resources_employments`
             LEFT JOIN `glpi_plugin_resources_ranks`
                  ON (`glpi_plugin_resources_employments`.`plugin_resources_ranks_id` = `glpi_plugin_resources_ranks`.`id`)
             WHERE ((`glpi_plugin_resources_employments`.`begin_date` < '".$date."'
                     AND (`glpi_plugin_resources_employments`.`end_date` IS NULL
                           OR `glpi_plugin_resources_employments`.`end_date` > '".$date."'))
                     AND `glpi_plugin_resources_ranks`.`id` IS NOT NULL)
                     AND ((`glpi_plugin_resources_employments`.`begin_date` > `glpi_plugin_resources_ranks`.`end_date`
                           OR `glpi_plugin_resources_employments`.`end_date` < `glpi_plugin_resources_ranks`.`begin_date`)
                        OR (`glpi_plugin_resources_ranks`.`end_date` < '".$date."'
                           OR `glpi_plugin_resources_ranks`.`begin_date` > '".$date."'))";
//             WHERE (((`glpi_plugin_resources_employments`.`begin_date` > `glpi_plugin_resources_ranks`.`end_date`
//                    OR `glpi_plugin_resources_employments`.`end_date` < `glpi_plugin_resources_ranks`.`begin_date`
//                    OR `glpi_plugin_resources_employments`.`begin_date` < '".$date."')
//                       AND `glpi_plugin_resources_employments`.`end_date` IS NULL
//                       AND (`glpi_plugin_resources_ranks`.`end_date` < '".$date."'
//                            OR `glpi_plugin_resources_ranks`.`begin_date` > '".$date."'))
//                  AND `glpi_plugin_resources_ranks`.`id` IS NOT NULL)";

$conditionAll = getEntitiesRestrictRequest('AND', 'glpi_plugin_resources_employments','','',true);

$queryEmploy.=$conditionAll." ".getOrderBy('entity', $columns);

foreach($DB->request($queryEmploy) as $dataEmploy) {
   $dataAll[$row_num]=$dataEmploy;
   $row_num++;
}

$nbtot = count($dataAll);
if ($limit) {
   $start = (isset ($_GET["start"]) ? $_GET["start"] : 0);
   if ($start >= $nbtot) {
      $start = 0;
   }
} else {
   $start = 0;
}

if ($nbtot == 0) {
   if (!$HEADER_LOADED) {
      Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
      Report::title();
   }
   echo "<div class='center'><font class='red b'>".$LANG['search'][15]."</font></div>";
   Html::footer();
} else if ($output_type == PDF_OUTPUT_PORTRAIT || $output_type == PDF_OUTPUT_LANDSCAPE) {
   include (GLPI_ROOT . "/lib/ezpdf/class.ezpdf.php");
} else if ($output_type == HTML_OUTPUT) {
   if (!$HEADER_LOADED) {
      Html::header($title, $_SERVER['PHP_SELF'], "utils", "report");
      Report::title();
   }
   echo "<div class='center'><table class='tab_cadre_fixe'>";
   echo "<tr><th>$title</th></tr>\n";
   echo "<tr class='tab_bg_2 center'><td class='center'>";
   echo "<form method='POST' action='" .$_SERVER["PHP_SELF"] . "?start=$start'>\n";

   $param = "";
   foreach ($_POST as $key => $val) {
      if (is_array($val)) {
         foreach ($val as $k => $v) {
            echo "<input type='hidden' name='".$key."[$k]' value='$v' >";
            if (!empty ($param)) {
               $param .= "&";
            }
            $param .= $key."[".$k."]=".urlencode($v);
         }
      } else {
         echo "<input type='hidden' name='$key' value='$val' >";
         if (!empty ($param)) {
            $param .= "&";
         }
         $param .= "$key=" . urlencode($val);
      }
   }
   Dropdown::showOutputFormat();
   Html::closeForm();
   echo "</td></tr>";
   echo "</table></div>";

   Html::printPager($start, $nbtot, $_SERVER['PHP_SELF'], $param);
}

if ($nbtot >0) {
   $nbcols = $DB->num_fields($result);
   $nbrows = $DB->numrows($result);
   $num = 1;
   $link  = $_SERVER['PHP_SELF'];
   $order = 'ASC';
   $issort = false;

   echo Search::showHeader($output_type, $nbrows, $nbcols, true);

   echo Search::showNewLine($output_type);

   showTitle($output_type, $num, $LANG['entity'][0], 'entity', true);
   showTitle($output_type, $num, $LANG['plugin_resources'][28], 'type');
   showTitle($output_type, $num, $LANG['plugin_resources'][8], 'name', true);
   showTitle($output_type, $num, $LANG['common'][43], 'firstname', true);
   showTitle($output_type, $num, $LANG['users'][17], 'registration_number', true);
   showTitle($output_type, $num, $LANG['plugin_resources'][77], 'rank', true);
   showTitle($output_type, $num, $LANG['plugin_resources'][34], 'date_begin', true);
   showTitle($output_type, $num, $LANG['plugin_resources'][35], 'date_end', true);
   showTitle($output_type, $num, $LANG['plugin_resources'][77]." - ".$LANG['plugin_resources'][34], 'begin_date', true);
   showTitle($output_type, $num, $LANG['plugin_resources'][77]." - ".$LANG['plugin_resources'][35], 'end_date', true);

   echo Search::showEndLine($output_type);

   if($limit){
      $dataAll= array_slice($dataAll,$start,$limit);
   }

   foreach($dataAll as $key=>$data) {

      $num = 1;
      echo Search::showNewLine($output_type);
      echo Search::showItem($output_type, Dropdown::getDropdownName('glpi_entities',$data['entity']), $num,$key);
      if($data['typeName'] == 'Resource'){
         $type = PluginResourcesResource::getTypeName(0);
         $link=Toolbox::getItemTypeFormURL("PluginResourcesResource");
      } else if($data['typeName'] == 'Employment'){
         $type = PluginResourcesEmployment::getTypeName(0);
         $link=Toolbox::getItemTypeFormURL("PluginResourcesEmployment");
      }

      echo Search::showItem($output_type, $type, $num,$key);

      $name = "<a href='".$link."?id=".$data["ID"]."'>";
      if ($data["name"] == NULL){
         $name.="(".$data["ID"].")";
      } else {
         $name.=$data["name"];
      }
      $name.="</a>";
      echo Search::showItem($output_type, $name, $num,$key);

      echo Search::showItem($output_type, $data['firstname'], $num,$key);
      echo Search::showItem($output_type, $data['registration_number'], $num,$key);

      $link1=Toolbox::getItemTypeFormURL("PluginResourcesRank");
      $rankName = "<a href='".$link1."?id=".$data["rankID"]."'>".$data["rank"]."</a>";
      echo Search::showItem($output_type, $rankName, $num,$key);

      echo Search::showItem($output_type, Html::convDate($data['date_begin']), $num,$key);
      echo Search::showItem($output_type, Html::convDate($data['date_end']), $num,$key);
      echo Search::showItem($output_type, Html::convDate($data['begin_date']), $num,$key);
      echo Search::showItem($output_type, Html::convDate($data['end_date']), $num,$key);
      echo Search::showEndLine($output_type);

   }

   echo Search::showFooter($output_type, $title);
}

if ($output_type == HTML_OUTPUT) {
   Html::footer();
}

/**
 * Display the column title and allow the sort
 *
 * @param $output_type
 * @param $num
 * @param $title
 * @param $columnname
 * @param bool $sort
 * @return mixed
 */
function showTitle($output_type, &$num, $title, $columnname, $sort=false) {

   if ($output_type != HTML_OUTPUT ||$sort==false) {
      echo Search::showHeaderItem($output_type, $title, $num);
      return;
   }
   $order = 'ASC';
   $issort = false;
   if (isset($_REQUEST['sort']) && $_REQUEST['sort']==$columnname) {
      $issort = true;
      if (isset($_REQUEST['order']) && $_REQUEST['order']=='ASC') {
         $order = 'DESC';
      }
   }
   $link  = $_SERVER['PHP_SELF'];
   $first = true;
   foreach ($_REQUEST as $name => $value) {
      if (!in_array($name,array('sort','order','PHPSESSID'))) {
         $link .= ($first ? '?' : '&amp;');
         $link .= $name .'='.urlencode($value);
         $first = false;
      }
   }
   $link .= ($first ? '?' : '&amp;').'sort='.urlencode($columnname);
   $link .= '&amp;order='.$order;
   echo Search::showHeaderItem($output_type, $title, $num,
      $link, $issort, ($order=='ASC'?'DESC':'ASC'));
}

/**
 * Build the ORDER BY clause
 *
 * @param $default string, name of the column used by default
 * @return string
 */
function getOrderBy($default, $columns) {

   if (!isset($_REQUEST['order']) || $_REQUEST['order']!='DESC') {
      $_REQUEST['order'] = 'ASC';
   }
   $order   = $_REQUEST['order'];

   $tab = getOrderByFields($default, $columns);
   if (count($tab)>0) {
      return " ORDER BY ". $tab ." ". $order;
   }
   return '';
}

/**
 * Get the fields used for order
 *
 * @param $default string, name of the column used by default
 *
 * @return array of column names
 */
function getOrderByFields($default, $columns) {

   if (!isset($_REQUEST['sort'])) {
      $_REQUEST['sort'] = $default;
   }
   $colsort = $_REQUEST['sort'];

   foreach ($columns as $colname => $column) {
      if ($colname==$colsort) {
         return $column['sorton'];
      }
   }
   return array();
}


?>