<?php

/*
 * @version $Id: ticketvalidation.class.php 311 2011-12-05 13:58:19Z remi $
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

class PluginPdfTicketValidation extends PluginPdfCommon {


   function __construct(CommonGLPI $obj=NULL) {

      $this->obj = ($obj ? $obj : new TicketValidation());
   }

   static function pdfForTicket(PluginPdfSimplePDF $pdf, Ticket $ticket) {
      global $LANG, $CFG_GLPI, $DB;

      $pdf->setColumnsSize(100);
      $pdf->displayTitle("<b>".$LANG['validation'][7]."</b>");

      if (!Session::haveRight('validate_ticket',1) && !Session::haveRight('create_validation ',1)) {
         return false;
      }
      $ID = $ticket->getField('id');

      $query = "SELECT *
                FROM `glpi_ticketvalidations`
                WHERE `tickets_id` = '".$ticket->getField('id')."'
                ORDER BY submission_date DESC";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number) {
         $pdf->setColumnsSize(20,19,21,19,21);
         $pdf->displayTitle($LANG['validation'][2],
                            $LANG['validation'][3],
                            $LANG['validation'][18],
                            $LANG['validation'][4],
                            $LANG['validation'][21]);

         while ($row = $DB->fetch_assoc($result)) {
            $pdf->setColumnsSize(20,19,21,19,21);
            $pdf->displayLine(TicketValidation::getStatus($row['status']),
                              Html::convDateTime($row["submission_date"]),
                              getUserName($row["users_id"]),
                              Html::convDateTime($row["validation_date"]),
                              getUserName($row["users_id_validate"]));
            $tmp = trim($row["comment_submission"]);
            $pdf->displayText("<b><i>".$LANG['validation'][5]."</i></b> : ",
               (empty($tmp) ? $LANG['common'][49] : $tmp), 1);

            if ($row["validation_date"]) {
               $tmp = trim($row["comment_validation"]);
               $pdf->displayText("<b><i>".$LANG['validation'][6]."</i></b> : ",
                  (empty($tmp) ? $LANG['common'][49] : $tmp), 1);
            }
         }
      } else {
         $pdf->displayLine($LANG['search'][15]);
      }
      $pdf->displaySpace();
   }
}