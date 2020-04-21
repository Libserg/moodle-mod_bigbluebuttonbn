<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
//require_once($CFG->libdir.'/clilib.php');      // cli only functions

$CFG->debug=31;
try {
    if($DB->execute("select server from {bigbluebuttonbn} where server is not NULL"))
	echo "bigbluebuttonbn already upgraded!\n";
} catch (Exception $e) {
#	echo 'Выброшено исключение: ',  $e->getMessage(), "\n";

	if( $DB->execute("ALTER TABLE {bigbluebuttonbn} ADD COLUMN server bigint") && 
	    $DB->execute("ALTER TABLE {bigbluebuttonbn} ALTER server SET DEFAULT 0") &&
	    $DB->execute("UPDATE {bigbluebuttonbn} SET server=0 WHERE server is NULL")) {
	    echo "bigbluebuttonbn success upgraded!\n";
	}
}
try {
    if($DB->execute("select server from {bigbluebuttonbn_logs} where server is not NULL"))
	echo "bigbluebuttonbn_logs already upgraded!\n";
} catch (Exception $e) {
#	echo 'Выброшено исключение: ',  $e->getMessage(), "\n";

	if( $DB->execute("ALTER TABLE {bigbluebuttonbn_logs} ADD COLUMN server bigint") && 
	    $DB->execute("ALTER TABLE {bigbluebuttonbn_logs} ALTER server SET DEFAULT 0") &&
	    $DB->execute("UPDATE {bigbluebuttonbn_logs} SET server=0 WHERE server is NULL")) {
	    echo "bigbluebuttonbn_logs success upgraded!\n";
	}
}



exit(0); // 0 means success.
