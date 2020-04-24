#!/usr/bin/env php72
<?php

function get_eid_info($eid) {
	
$ret = array();

$ch = curl_init();
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, "");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
curl_setopt($ch, CURLOPT_VERBOSE, false);

curl_setopt($ch, CURLOPT_URL, 'https://bbb2.guap.ru/meeting_eid/'.urlencode($eid));
$result = curl_exec($ch);
curl_close($ch);

#print_r($result);
$strpar = array(
	"id"=>"uid",
	"starttime"=>"starttime",
	"total_time"=>"total_len",
	"voice_time"=>"voice_len",
	"rtc_time"=>"rtc_len",
	"tcdesk_time"=>"tcdesk_len",
	"pr_cnt"=>"pr_cnt",
	"pr_pages"=>"pr_pages",
	"audio"=>"audio_size",
	"video"=>"video_size",
	"deskshare"=>"deskshare_size");

foreach(explode("\n",$result) as $l) {
	if(!strchr($l,":")) continue;
	list($field, $data) = preg_split('/\s*:\s*/', $l);
	if(!isset($strpar[$field])) continue;
#	echo "$field : $data\n";
	$ret[$field] = $data;
}
if(count($ret)) {
	foreach($strpar as $k=>$v) {
	  if(!isset($ret[$k])) $ret[$k] = 0;
	}
	$ret['server'] = 1;
	$ret['eid'] = $eid;
}
return $ret;
}

print_r(get_eid_info($argv[1]));
?>
