<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
//require_once($CFG->libdir.'/clilib.php');      // cli only functions

$CFG->debug=31;
try {
    if($DB->execute("select server from {bigbluebuttonbn_info} where server is not NULL"))
	echo "bigbluebuttonbn_info already upgraded!\n";
} catch (Exception $e) {
	#	echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
	$sql = "CREATE TABLE {bigbluebuttonbn_info} (".
		" id bigint PRIMARY KEY,".
		" server smallint default 0,".
		" meetingid varchar(128) not NULL,".
		" meetinguid varchar(128) not NULL,".
		" starttime bigint default 0,".
		" users integer default 0,".
		" total_len integer default 0,".
		" voice_len integer default 0,".
		" rtc_len integer default 0,".
		" tcdesk_len integer default 0,".
		" pr_cnt integer default 0,".
		" pr_pages integer default 0,".
		" audio_size integer default 0,".
		" video_size integer default 0,".
		" deskshare_size integer default 0)";
	if( $DB->execute($sql)) {
	    echo "bigbluebuttonbn_info success upgraded!\n";
	}
}

exit(0); // 0 means success.
