<?php
/*
 * @version $Id: setup.notification.php 20130 2013-02-04 16:55:15Z moyo $
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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------


define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkSeveralRightsOr(array('notification' => 'r',
                                    'config'       => 'w'));

Html::header($LANG['setup'][704], $_SERVER['PHP_SELF'],"config","mailing",-1);

if (isset($_GET['activate'])) {
   $config = new Config();
   $tmp['id'] = $CFG_GLPI['id'];
   $tmp['use_mailing'] = 1;
   $config->update($tmp);
   Html::back();
}

if (!$CFG_GLPI['use_mailing']) {
   echo "<div align='center'<p>";
   if (Session::haveRight("config","w")) {
      echo "<a href='setup.notification.php?activate=1' class='icon_consol b'>" .
               $LANG['setup'][202] ."</a></p></div>";
   }

} else {
   if (!Session::haveRight("config","r")
       && Session::haveRight("notification","r")
       && $CFG_GLPI['use_mailing']) {
      Html::redirect($CFG_GLPI["root_doc"].'/front/notification.php');

   } else {
      echo "<table class='tab_cadre'>";
      echo "<tr><th>&nbsp;" . $LANG['setup'][704]."&nbsp;</th></tr>";
      if (Session::haveRight("config","r")) {
         echo "<tr class='tab_bg_1'><td class='center'><a href='notificationmailsetting.form.php'>" .
               $LANG['setup'][201] ."</a></td></tr>";
            echo "<tr class='tab_bg_1'><td class='center'><a href='notificationtemplate.php'>" .
                  $LANG['mailing'][113] ."</a></td> </tr>";
      }

      if (Session::haveRight("notification","r") && $CFG_GLPI['use_mailing']) {
         echo "<tr class='tab_bg_1'><td class='center'><a href='notification.php'>" . $LANG['setup'][704] .
               "</a></td></tr>";
      } else {
            echo "<tr class='tab_bg_1'><td class='center'>" . $LANG['setup'][661] ."</td></tr>";
      }
      echo "</table>";
   }
}

Html::footer();
?>