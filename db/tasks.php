<?php

$tasks = array(
    array(
        'classname' => 'mod_bigbluebuttonbn\task\cron_task',
        'blocking' => 1,
	'minute' => '*/2',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);
?>

