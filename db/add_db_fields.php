<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
//require_once($CFG->libdir.'/clilib.php');      // cli only functions

$CFG->debug=31;
try {
    if($DB->execute("select server from {bigbluebuttonbn} where server is not NULL"))
	echo "bigbluebuttonbn server already added!\n";
} catch (Exception $e) {
#	echo 'Выброшено исключение: ',  $e->getMessage(), "\n";

	if( $DB->execute("ALTER TABLE {bigbluebuttonbn} ADD COLUMN server bigint") && 
	    $DB->execute("ALTER TABLE {bigbluebuttonbn} ALTER server SET DEFAULT 0") &&
	    $DB->execute("UPDATE {bigbluebuttonbn} SET server=0 WHERE server is NULL")) {
	    echo "bigbluebuttonbn server success added!\n";
	}
}
try {
    if($DB->execute("select uidlimit from {bigbluebuttonbn} where uidlimit is not NULL"))
	echo "bigbluebuttonbn uidlimit already added!\n";
} catch (Exception $e) {
#	echo 'Выброшено исключение: ',  $e->getMessage(), "\n";

	if( $DB->execute("ALTER TABLE {bigbluebuttonbn} ADD COLUMN uidlimit bigint") && 
	    $DB->execute("ALTER TABLE {bigbluebuttonbn} ALTER uidlimit SET DEFAULT 0") &&
	    $DB->execute("UPDATE {bigbluebuttonbn} SET uidlimit=0 WHERE uidlimit is NULL")) {
	    echo "bigbluebuttonbn uidlimit success added!\n";
	}
}
try {
    if($DB->execute("select server from {bigbluebuttonbn_logs} where server is not NULL"))
	echo "bigbluebuttonbn_logs server already added!\n";
} catch (Exception $e) {
#	echo 'Выброшено исключение: ',  $e->getMessage(), "\n";

	if( $DB->execute("ALTER TABLE {bigbluebuttonbn_logs} ADD COLUMN server bigint") && 
	    $DB->execute("ALTER TABLE {bigbluebuttonbn_logs} ALTER server SET DEFAULT 0") &&
	    $DB->execute("UPDATE {bigbluebuttonbn_logs} SET server=0 WHERE server is NULL")) {
	    echo "bigbluebuttonbn_logs server success added!\n";
	}
}

try {
    if($DB->execute("select norecinfo from {bigbluebuttonbn_logs} where norecinfo is not NULL"))
	echo "bigbluebuttonbn_logs norecinfo already added!\n";
} catch (Exception $e) {
#	echo 'Выброшено исключение: ',  $e->getMessage(), "\n";

	if( $DB->execute("ALTER TABLE {bigbluebuttonbn_logs} ADD COLUMN norecinfo int") && 
	    $DB->execute("ALTER TABLE {bigbluebuttonbn_logs} ALTER norecinfo SET DEFAULT 0") &&
	    $DB->execute("UPDATE {bigbluebuttonbn_logs} SET norecinfo=0 WHERE norecinfo is NULL")) {
	    echo "bigbluebuttonbn_logs norecinfo success added!\n";
	}
}
try {
	$sql = "create index {bigbluebuttonbn_logs}_log_hash on {bigbluebuttonbn_logs} USING hash(log)";
        if ($DB->execute($sql))
	    echo "bigbluebuttonbn_logs index log OK!\n";
} catch (Exception $e) {
	if(strstr($e->error,'already exists') !== false)
	    echo "bigbluebuttonbn_logs index already exists!\n";
	  else 
	    echo $e->getMessage(), ':', $e->error , "\n";
}
try {
	$sql = "create index {bigbluebuttonbn_logs}_meetingid_idx on {bigbluebuttonbn_logs} USING btree(meetingid)";
        if ($DB->execute($sql))
	    echo "bigbluebuttonbn_logs index meetingid OK!\n";
} catch (Exception $e) {
	if(strstr($e->error,'already exists') !== false)
	    echo "bigbluebuttonbn_logs index meetingid already exists!\n";
	  else 
	    echo $e->getMessage(), ':', $e->error , "\n";
}

try {
	$sql = "CREATE TABLE IF NOT EXISTS {bigbluebuttonbn_info} (".
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
	
	if ($DB->execute($sql))
	    echo "bigbluebuttonbn_info OK!\n";
} catch (Exception $e) {
	echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
}
try {
    if($DB->execute("select durationlimit from {bigbluebuttonbn} where durationlimit is not NULL"))
	echo "bigbluebuttonbn durationlimit already added!\n";
} catch (Exception $e) {
#	echo 'Выброшено исключение: ',  $e->getMessage(), "\n";

	if( $DB->execute("ALTER TABLE {bigbluebuttonbn} ADD COLUMN durationlimit int") && 
	    $DB->execute("ALTER TABLE {bigbluebuttonbn} ALTER durationlimit SET DEFAULT 0") &&
	    $DB->execute("UPDATE {bigbluebuttonbn} SET durationlimit=0 WHERE durationlimit is NULL")) {
	    echo "bigbluebuttonbn durationlimit success added!\n";
	}
}

exit(0); // 0 means success.
