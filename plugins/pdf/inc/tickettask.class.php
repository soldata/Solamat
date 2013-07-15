<?php

/*
 * @version $Id: tickettask.class.php 311 2011-12-05 13:58:19Z remi $
 -------------------------------------------------------------------------
 pdf - Export to PDF plugin for GLPI
 Copyright (C) 2003-2011 by the pdf Development Team.

 https://forge.indepnet.net/projects/pdf
 -------------------------------------------------------------------------

 LICENSE

 This file is part of pdf.

 pdf is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 pdf is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with pdf. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

// Original Author of file: Remi Collet
// ----------------------------------------------------------------------

class PluginPdfTicketTask extends PluginPdfCommon {


   function __construct(CommonGLPI $obj=NULL) {

      $this->obj = ($obj ? $obj : new TicketTask());
   }

   static function pdfForTicket(PluginPdfSimplePDF $pdf, Ticket $job, $private) {
      global $LANG, $CFG_GLPI, $DB;

      $ID = $job->getField('id');

      //////////////Tasks///////////
      $pdf->setColumnsSize(100);
      $pdf->displayTitle("<b>".$LANG['plugin_pdf']['ticket'][2]."</b>");

      $RESTRICT = "";
      if (!$private) {
         // Don't show private'
         $RESTRICT=" AND `is_private` = '0' ";
      } else if (!Session::haveRight("show_full_ticket","1")) {
         // No right, only show connected user private one
         $RESTRICT=" AND (`is_private` = '0'
                          OR `users_id` ='".Session::getLoginUserID()."' ) ";
      }

      $query = "SELECT *
                FROM `glpi_tickettasks`
                WHERE `tickets_id` = '$ID'
                $RESTRICT
                ORDER BY `date` DESC";
      $result=$DB->query($query);

      if (!$DB->numrows($result)) {
         $pdf->displayLine($LANG['job'][50]);
      } else {
         while ($data=$DB->fetch_array($result)) {

            $actiontime = Html::timestampToString($data['actiontime'], false);

            if ($data['begin']) {
               $planification = Planning::getState($data["state"])." - ".Html::convDateTime($data["begin"]).
                                " -> ".Html::convDateTime($data["end"])." - ".getUserName($data["users_id_tech"]);
            } else {
               $planification=$LANG['job'][32];
            }

            $pdf->setColumnsSize(40,14,30,16);
            $pdf->displayTitle("<b><i>".$LANG['common'][17]."</i></b>", // Source
                               "<b><i>".$LANG['common'][27]."</i></b>", // Date
                               "<b><i>".$LANG['common'][37]."</i></b>", // Author
                               "<b><i>".$LANG['job'][31]."</i></b>"); // Durée

            if ($data['taskcategories_id']) {
               $lib = Dropdown::getDropdownName('glpi_taskcategories', $data['taskcategories_id']);
            } else {
               $lib = '';
            }
            if ($data['is_private']) {
               $lib .= ' ('.$LANG['common'][77].')';
            }
            $pdf->displayLine(Html::clean($lib),
                              Html::convDateTime($data["date"]),
                              Html::clean(getUserName($data["users_id"])),
                              Html::clean($actiontime));
            $pdf->displayText("<b><i>".$LANG['joblist'][6]."</i></b> : ", Html::clean($data["content"]),1);
            $pdf->displayText("<b><i>".$LANG['job'][35]."</i></b> : ", Html::clean($planification),1);
         }
      }
      $pdf->displaySpace();
   }
}