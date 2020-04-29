<?php
defined('MOODLE_INTERNAL') || die();

$string['view_stop_join'] = 'Доступ временно закрыт {$a}';
$string['config_generalsrv'] = 'Общие настройки';
$string['config_generalsrv_description'] = '<h3>Нет сконфигурированных серверов!</h3>Список BBB-сарверов должен быть описан в файле config.php<br>На каждый сервер нужно указать 3 параметра:<br>'.
	' $CFG->bigbluebuttonbn[\'server_nameX\'] = "Server name";<br>'.
        ' $CFG->bigbluebuttonbn[\'server_urlX\'] = "https://bbbX.example.org/bigbludebutton";<br>'.
        ' $CFG->bigbluebuttonbn[\'shared_secretX\'] = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";<br>'.
	' где X - от 1 до 9';

