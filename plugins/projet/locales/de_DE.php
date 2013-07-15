<?php
/*
 * @version $Id: HEADER 15930 2012-03-08 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Projet plugin for GLPI
 Copyright (C) 2003-2012 by the Projet Development Team.

 https://forge.indepnet.net/projects/projet
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of Projet.

 Projet is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Projet is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Projet. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

$title = "Projekt";

$LANG['plugin_projet']['title'][1] = "".$title."";
$LANG['plugin_projet']['title'][2] = "Teilnehmer";
$LANG['plugin_projet']['title'][3] = "Aufgaben";
$LANG['plugin_projet']['title'][4] = "Projects";
$LANG['plugin_projet']['title'][5] = "Gantt";

$LANG['plugin_projet']['profile'][0] = "Rechteverwaltung";
$LANG['plugin_projet']['profile'][1] = "$title";

$LANG['plugin_projet'][0] = "Name";
$LANG['plugin_projet'][1] = "Hierarchy";
$LANG['plugin_projet'][2] = "Kommentare";
$LANG['plugin_projet'][3] = "Unknown project";
$LANG['plugin_projet'][4] = "Unknown project task";
$LANG['plugin_projet'][5] = "Verbundene Teilnehmer";
$LANG['plugin_projet'][6] = "Beendet";
$LANG['plugin_projet'][7] = "Task";
$LANG['plugin_projet'][8]  = "Create a project from this ticket";
$LANG['plugin_projet'][9] = "Responsible(s)";
$LANG['plugin_projet'][10] = "Beschreibung";
$LANG['plugin_projet'][11] = "Aufgabe hinzufügen";
$LANG['plugin_projet'][12] = "Aufgaben in Bearbeitung";
$LANG['plugin_projet'][13] = "Create a new project";
$LANG['plugin_projet'][14] = "Projektbeschreibung";
$LANG['plugin_projet'][15] = "Duplizieren";
$LANG['plugin_projet'][16]  = "Create a project from this problem";
$LANG['plugin_projet'][17] = "Material";
$LANG['plugin_projet'][18] = "Ergebnis";
$LANG['plugin_projet'][19] = "Status";
$LANG['plugin_projet'][20] = "Bearbeitung";
$LANG['plugin_projet'][22] = "Aufgabentitel";
$LANG['plugin_projet'][23] = "Typ";
$LANG['plugin_projet'][24] = "Planung";
$LANG['plugin_projet'][26] = "Geplant";
$LANG['plugin_projet'][29] = "Projektname";
$LANG['plugin_projet'][30] = "Aufgabe";
$LANG['plugin_projet'][39] = "Andere Teilnehmer";
$LANG['plugin_projet'][40] = "Betroffene Personen";
$LANG['plugin_projet'][41] = "Priorität";
$LANG['plugin_projet'][44] = "Übergeordnetes Projekt";
$LANG['plugin_projet'][45] = "Untergeordnetes Projekt";
$LANG['plugin_projet'][46] = "Child task(s)";
$LANG['plugin_projet'][47] = "Fortschritt";
$LANG['plugin_projet'][48] = "%";
$LANG['plugin_projet'][49] = "Aufgegeben";
$LANG['plugin_projet'][50] = "Verbundenes Projekt";
$LANG['plugin_projet'][52] = "Projekte, in denen er ein FK_users ist";
$LANG['plugin_projet'][53] = "in Bereitschaft";
$LANG['plugin_projet'][55] = "Abhängig";
$LANG['plugin_projet'][56] = "Abhängig von Unteraufgaben";
$LANG['plugin_projet'][57] = "Finished";
$LANG['plugin_projet'][58] = "Übergeordnete Aufgabe";
$LANG['plugin_projet'][64] = "Im Gantt anzeigen";
$LANG['plugin_projet'][66] = "Finished task";
$LANG['plugin_projet'][67] = "Used for planning";
$LANG['plugin_projet'][68] = "Aufgabenliste";
$LANG['plugin_projet'][69] = "Verbundene Gegenstände";
$LANG['plugin_projet'][70] = "Im globlen Gantt anzeigen";
$LANG['plugin_projet'][71] = "Associated color";
$LANG['plugin_projet'][72] = "Effektive dauer";
$LANG['plugin_projet'][74] = "Alle";
$LANG['plugin_projet'][75] = "Geschätzte Dauer";
$LANG['plugin_projet'][76] = "Total of effective duration of project tasks";
$LANG['plugin_projet'][77] = "Total of duration of linked tickets for project";

$LANG['plugin_projet']['setup'][1] = "Aufgabentyp";
$LANG['plugin_projet']['setup'][17] = "Verbinden";
$LANG['plugin_projet']['setup'][18] = "Trennen";
$LANG['plugin_projet']['setup'][22] = "Projektstatus";
$LANG['plugin_projet']['setup'][23] = "Aufgabenstatus";
$LANG['plugin_projet']['setup'][25] = "Mit Projekt verbinden";

$LANG['plugin_projet']['mailing'][0] = "Mit Erstellung, Veränderung oder Löschung des Projekts";
$LANG['plugin_projet']['mailing'][1] = "Mit Erstellung, Veränderung, Löschung oder Ablauf des Projekts";
$LANG['plugin_projet']['mailing'][2] = "Verantwortlicher Benutzer des Projekts";
$LANG['plugin_projet']['mailing'][3] = "Verantwortlicher Benutzer der Aufgabe";
$LANG['plugin_projet']['mailing'][4] = "Ein Projekt wurde hinzugefügt";
$LANG['plugin_projet']['mailing'][5] = "Ein Projekt wurde verändert oder geöffnet";
$LANG['plugin_projet']['mailing'][6] = "Ein Projekt wurde gelöscht";
$LANG['plugin_projet']['mailing'][7] = "Eine Aufgabe wurde hinzugefügt";
$LANG['plugin_projet']['mailing'][8] = "Eine Aufgabe wurde verändert oder geöffnet";
$LANG['plugin_projet']['mailing'][9] = "Eine Aufgabe wurde gelöscht";
$LANG['plugin_projet']['mailing'][10] = "Eine Aufgabe ist veraltet";
$LANG['plugin_projet']['mailing'][11] = "Verantwortliche Gruppe der Aufgabe";
$LANG['plugin_projet']['mailing'][12] = "Verantwortlicher Lieferant der Aufgabe";
$LANG['plugin_projet']['mailing'][13] = "Verantwortliche Gruppe des Projekts";
$LANG['plugin_projet']['mailing'][14] = "Änderungen";
$LANG['plugin_projet']['mailing'][15] = "Outdated tasks";
$LANG['plugin_projet']['mailing'][16] = "Associated tasks";
$LANG['plugin_projet']['mailing'][17] = "Send email";

?>