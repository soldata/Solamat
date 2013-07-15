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

$title = "Проэкт";

$LANG['plugin_projet']['title'][1] = "".$title."";
$LANG['plugin_projet']['title'][2] = "Участники";
$LANG['plugin_projet']['title'][3] = "Задачи";
$LANG['plugin_projet']['title'][4] = "Projects";
$LANG['plugin_projet']['title'][5] = "Gantt";

$LANG['plugin_projet']['profile'][0] = "Права управления";
$LANG['plugin_projet']['profile'][1] = "$title";

$LANG['plugin_projet'][0] = "Имя";
$LANG['plugin_projet'][1] = "Hierarchy";
$LANG['plugin_projet'][2] = "Комментарии";
$LANG['plugin_projet'][3] = "Unknown project";
$LANG['plugin_projet'][4] = "Unknown project task";
$LANG['plugin_projet'][5] = "Ассоциированные участник(и)";
$LANG['plugin_projet'][6] = "Готово";
$LANG['plugin_projet'][7] = "Task";
$LANG['plugin_projet'][8]  = "Create a project from this ticket";
$LANG['plugin_projet'][9] = "Responsible(s)";
$LANG['plugin_projet'][10] = "Описание";
$LANG['plugin_projet'][11] = "Добавить задачу";
$LANG['plugin_projet'][12] = "Задачи в процессе";
$LANG['plugin_projet'][13] = "Create a new project";
$LANG['plugin_projet'][14] = "Описание проэкта";
$LANG['plugin_projet'][15] = "Копировать";
$LANG['plugin_projet'][16]  = "Create a project from this problem";
$LANG['plugin_projet'][17] = "Материалы";
$LANG['plugin_projet'][18] = "Результат";
$LANG['plugin_projet'][19] = "Статус";
$LANG['plugin_projet'][20] = "Выполнение";
$LANG['plugin_projet'][22] = "Название задачи";
$LANG['plugin_projet'][23] = "Тип";
$LANG['plugin_projet'][24] = "Планификация";
$LANG['plugin_projet'][26] = "Запланировано";
$LANG['plugin_projet'][29] = "Название проэкта";
$LANG['plugin_projet'][30] = "Задача";
$LANG['plugin_projet'][39] = "Другие участники";
$LANG['plugin_projet'][40] = "Affected people";
$LANG['plugin_projet'][41] = "Приоритет";
$LANG['plugin_projet'][44] = "Родительский проэкт";
$LANG['plugin_projet'][45] = "дочерний проэкт(ы)";
$LANG['plugin_projet'][46] = "Child task(s)";
$LANG['plugin_projet'][47] = "Прогресс";
$LANG['plugin_projet'][48] = "%";
$LANG['plugin_projet'][49] = "Прерванный";
$LANG['plugin_projet'][50] = "Ассоциированные проэкты";
$LANG['plugin_projet'][52] = "Project(s) where he is the FK_users";
$LANG['plugin_projet'][53] = "В ожидании";
$LANG['plugin_projet'][55] = "Зависимый";
$LANG['plugin_projet'][56] = "Зависит от заданий в дочернем проэкте";
$LANG['plugin_projet'][57] = "Finished";
$LANG['plugin_projet'][58] = "Родительское задание";
$LANG['plugin_projet'][64] = "Вывести Gantt";
$LANG['plugin_projet'][66] = "Finished task";
$LANG['plugin_projet'][67] = "Used for planning";
$LANG['plugin_projet'][68] = "Список заданий";
$LANG['plugin_projet'][69] = "Связанные материалы";
$LANG['plugin_projet'][70] = "Показать общий Gantt";
$LANG['plugin_projet'][71] = "Associated color";
$LANG['plugin_projet'][72] = "Эффективный срок";
$LANG['plugin_projet'][74] = "Все";
$LANG['plugin_projet'][75] = "Примерная продолжительность";
$LANG['plugin_projet'][76] = "Total of effective duration of project tasks";
$LANG['plugin_projet'][77] = "Total of duration of linked tickets for project";

$LANG['plugin_projet']['setup'][1] = "Тип задания";
$LANG['plugin_projet']['setup'][17] = "Ассоциированные";
$LANG['plugin_projet']['setup'][18] = "Отделенные";
$LANG['plugin_projet']['setup'][22] = "Состояние проекта";
$LANG['plugin_projet']['setup'][23] = "Состояние задания";
$LANG['plugin_projet']['setup'][25] = "Ассоциированные с проэктом";

$LANG['plugin_projet']['mailing'][0] = "С создание, изменением, удалением проэкта";
$LANG['plugin_projet']['mailing'][1] = "С создание, изменением, удалением, изменение срока задания";
$LANG['plugin_projet']['mailing'][2] = "Пользователь несет ответственность за проект";
$LANG['plugin_projet']['mailing'][3] = "Пользователь несет ответственность за задания";
$LANG['plugin_projet']['mailing'][4] = "Проэкт был добавлен";
$LANG['plugin_projet']['mailing'][5] = "Проект был изменен или открыт";
$LANG['plugin_projet']['mailing'][6] = "Проект был удален";
$LANG['plugin_projet']['mailing'][7] = "Задание было добавлено";
$LANG['plugin_projet']['mailing'][8] = "Задание было изменено или открыто";
$LANG['plugin_projet']['mailing'][9] = "Задание было удалено";
$LANG['plugin_projet']['mailing'][10] = "Задание устарело";
$LANG['plugin_projet']['mailing'][11] = "Группа ответствечающая за задание";
$LANG['plugin_projet']['mailing'][12] = "Поставщики ответственные за задание";
$LANG['plugin_projet']['mailing'][13] = "Групповая ответственность за проэкт";
$LANG['plugin_projet']['mailing'][14] = "Изменения";
$LANG['plugin_projet']['mailing'][15] = "Outdated tasks";
$LANG['plugin_projet']['mailing'][16] = "Associated tasks";
$LANG['plugin_projet']['mailing'][17] = "Send email";

?>