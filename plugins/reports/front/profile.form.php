<?php
/*
 * @version $Id: profile.form.php 204 2011-11-08 12:28:40Z remi $
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

// Original Author of file: Balpe Dévi
// Purpose of file:
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../..');
include_once (GLPI_ROOT . "/inc/includes.php");

Session::checkRight('profile', 'w');

//Save profile
if (isset ($_POST['update_user_profile'])) {
   PluginReportsProfile::updateForProfile($_POST);
}

Html::back();
?>