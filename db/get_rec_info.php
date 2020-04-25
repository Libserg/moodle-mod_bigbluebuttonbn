#!/usr/bin/env php72
<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * View all BigBlueButton instances in this course.
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2010 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @author    Fred Dixon  (ffdixon [at] blindsidenetworks [dt] com)
 */

define('CLI_SCRIPT', true);
require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once(__DIR__.'/../locallib.php');

use mod_bigbluebuttonbn\plugin;

# help() {{{{
function help($a='') {
    if($a) $a="Error: $a";

    $help =
"Cohorts util
$a
Options:
-l, --list            Show cohorts by name/id/dscr
--batch               Create/Modify/Delete cohorts via .json file
-D, --delete          Delete cohorts by name/id/dscr
--name                Match by name (regexp) (default)
--id                  Match by id
--idn                 Match by idnumber (regexp)
--dscr                Match by description (regexp)
-v, --verbose         Verbose
-n, --dry-run         Do not real changes
-f, --force           Ignore errors
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php admin/cli/cohort_delete.php [-n|-f] [--delete [--id|--name|--dscr] cohortname ...] [--batch cohort.json]
";
    echo $help;
    exit(0);
}
# }}}}

# get_med_info(meetingid) {{{{
$strpar = array(
	"id"=>"meetinguid",
	"starttime"=>"starttime",
	"total_time"=>"total_len",
	"users"=>"users",
	"voice_time"=>"voice_len",
	"rtc_time"=>"rtc_len",
	"tcdesk_time"=>"tcdesk_len",
	"pr_cnt"=>"pr_cnt",
	"pr_pages"=>"pr_pages",
	"audio"=>"audio_size",
	"video"=>"video_size",
	"deskshare"=>"deskshare_size");

function get_mid_info($mid) {
global $strpar;	
$ret = array();

$ch = curl_init();
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, "");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
curl_setopt($ch, CURLOPT_VERBOSE, false);

curl_setopt($ch, CURLOPT_URL, 'https://bbb2.guap.ru/meeting_id/'.urlencode($mid));
$result = curl_exec($ch);
curl_close($ch);

#print_r($result);

foreach(explode("\n",$result) as $l) {
	if(!strchr($l,":")) continue;
	list($field, $data) = preg_split('/\s*:\s*/', $l);
	if(!array_key_exists($field,$strpar)) continue;
#	echo "! '$field' {$strpar[$field]} : $data\n";
	$ret[$strpar[$field]] = $data;
}
if(count($ret)) {
	foreach($strpar as $k=>$v) {
	  if(!isset($ret[$v])) $ret[$v] = 0;
	}
	$ret['server'] = 1;
	$ret['meetingid'] = $eid;
}
return $ret;
}
# }}}}

# {{{{
#$recs = $DB->get_records_sql("select bl.meetingid as eid,timecreated from {bigbluebuttonbn_logs} as bl
#	left join {bigbluebuttonbn_info} as bi on bl.meetingid = bi.meetingid
#	where bl.meetingid like '%-%' and bi.total_len is null");
## print_r($recs);
#$ctm = time();
#foreach(array_keys($recs) as $eid ) {
#	if(isset($argv[1]) && $eid != $argv[1]) continue;
#	$info = get_eid_info($eid);
##	print_r($info);
#	if(count($info)) {
#		echo "ADD {$info['meetingid']}\n";
#		$DB->insert_record('bigbluebuttonbn_info',(object)$info,false,false);
##		exit(0);
#	} else {
#		if($recs[$eid]->timecreated < $ctm - 24*3600) {
#			$info = array();
#			foreach($strpar as $k=>$v) { $info[$v] = 0; }
#			$info['server'] = 1;
#			$info['starttime'] = $recs[$eid]->timecreated;
#			$info['meetingid'] = $eid;
#			echo "BAD $eid\n";
#			$DB->insert_record('bigbluebuttonbn_info',(object)$info,false,false);
#		} else {
#			echo "WAIT $eid\n";
#		}
#	}
#}
#
#exit(0); // 0 means success.
# }}}}

list($options, $unrecognized) = cli_get_params(
        array('help' => false,
                'verbose'=>false,
                'id'=>false, 'idn'=>false,
                'list'=>false),
        array('h' => 'help', 'v'=>'verbose',
                'l'=>'list')
);

if ($options['help']) {
    help();
}
$USER = get_admin();
foreach ($options as $k=>$v) {
    if(!isset($v)) $options[$k] = false;
}

if($options['list']) {
    echo "List\n";
    exit(0);
}

foreach($unrecognized as $rec) {
    echo $rec,"\n";
    add_rec_info($rec);
}

exit(0);

function add_rec_info($rec) {
    global $DB;
#    $info = bigbluebuttonbn_get_recordings_array($rec,[],1);
#    if(!$info) throw new \Exception("No info for $rec");
#    print_r($info);
    $blog_rec = $DB->get_records('bigbluebuttonbn_logs',array('meetingid'=>$rec,'log'=>'Create'),'','*');
#            "select * from {bigbluebuttonbn_logs} where log = ?",
#            array('Create'));
    print_r(array($rec,$blog_rec));
}


# vim: set tabstop=4 shiftwidth=4 expandtab:
