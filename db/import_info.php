#!/usr/bin/env php72
<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
//require_once($CFG->libdir.'/clilib.php');      // cli only functions

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

function get_eid_info($eid) {
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

curl_setopt($ch, CURLOPT_URL, 'https://bbb2.guap.ru/meeting_eid/'.urlencode($eid).'.txt');
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

$recs = $DB->get_records_sql("select bl.meetingid as eid,timecreated from {bigbluebuttonbn_logs} as bl
	left join {bigbluebuttonbn_info} as bi on bl.meetingid = bi.meetingid
	where bl.meetingid like '%-%' and bi.total_len is null");
# print_r($recs);
$ctm = time();
foreach(array_keys($recs) as $eid ) {
	if(isset($argv[1]) && $eid != $argv[1]) continue;
	$info = get_eid_info($eid);
#	print_r($info);
	if(count($info)) {
		echo "ADD {$info['meetingid']}\n";
		$DB->insert_record('bigbluebuttonbn_info',(object)$info,false,false);
#		exit(0);
	} else {
		if($recs[$eid]->timecreated < $ctm - 24*3600) {
			$info = array();
			foreach($strpar as $k=>$v) { $info[$v] = 0; }
			$info['server'] = 1;
			$info['starttime'] = $recs[$eid]->timecreated;
			$info['meetingid'] = $eid;
			echo "BAD $eid\n";
			$DB->insert_record('bigbluebuttonbn_info',(object)$info,false,false);
		} else {
			echo "WAIT $eid\n";
		}
	}
}

exit(0); // 0 means success.
