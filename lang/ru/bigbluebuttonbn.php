<?php
defined('MOODLE_INTERNAL') || die();

$string['view_stop_join'] = 'Доступ временно закрыт {$a}';
$string['config_generalsrv'] = 'Общие настройки';
$string['config_generalsrv_description'] = '<h3>Нет сконфигурированных серверов!</h3>Список BBB-сарверов должен быть описан в файле config.php<br>На каждый сервер нужно указать 3 параметра:<br>'.
	' $CFG->bigbluebuttonbn[X][\'server_name\'] = "Server name";<br>'.
        ' $CFG->bigbluebuttonbn[X][\'server_url\'] = "https://bbbX.example.org/bigbludebutton";<br>'.
        ' $CFG->bigbluebuttonbn[X][\'shared_secret\'] = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";<br>'.
	' где X - от 1 до 9';
$string['uidlimitmsg'] = 'Превышен лимит подключений для одной учетной записи';
$string['connlimitmsg'] = 'Слишком много подключений к серверу';
$string['meeting_duration'] = 'Максимальная продолжительность собрания {$a} минут';
$string['meeting_rec_type_0'] = 'Запись может быть сделана';
$string['meeting_rec_type_1'] = 'Без записи';
$string['meeting_rec_type_2'] = 'Запись включена';
$string['meeting_rec_type_3'] = 'Без записи (принудительно)';
$string['meeting_rec_type_4'] = 'Запись включена (принудительно)';

