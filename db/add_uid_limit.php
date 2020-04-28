<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
//require_once($CFG->libdir.'/clilib.php');      // cli only functions

$CFG->debug=31;
try {
    if($DB->execute("select server from {bigbluebuttonbn} where uidlimit is not NULL"))
	echo "bigbluebuttonbn already upgraded!\n";
} catch (Exception $e) {
#	echo 'Выброшено исключение: ',  $e->getMessage(), "\n";

	if( $DB->execute("ALTER TABLE {bigbluebuttonbn} ADD COLUMN uidlimit int") && 
	    $DB->execute("ALTER TABLE {bigbluebuttonbn} ALTER uidlimit SET DEFAULT 0") &&
	    $DB->execute("UPDATE {bigbluebuttonbn} SET uidlimit=0 WHERE uidlimit is NULL")) {
	    echo "bigbluebuttonbn success upgraded!\n";
	}
}


exit(0); // 0 means success.
