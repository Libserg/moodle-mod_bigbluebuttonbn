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

$dbg = 0;
$NO_REC = array();
$EIDs = array();
$server = '1';

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
    $result = '';

    if(file_exists("rec_info/$mid"))
        $result = file_get_contents("rec_info/$mid");
    if(0) {
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
    }
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
        $ret['starttime'] = intval($ret['starttime']);
    }
    return $ret;
}

function get_empty_info() {
    global $strpar;
    $ret = array();
	foreach($strpar as $k=>$v) $ret[$v] = 0;
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
                'verbose'=>false,'sync'=>false, 'server'=>false,
                'id'=>false, 'idn'=>false, 'check'=>false,
                'list'=>false),
        array('h' => 'help', 'v'=>'verbose', 'S'=>'server',
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
if(isset($options['server'])) {
    $server = intval($options['server']).'';
}

    $ar = file_get_contents("rec_info{$server}/record_all");
    foreach(explode("\n",$ar) as $l) {
        if(!$l) continue;
        list($mid,$eid) = explode(" ",$l);
        if(!$eid || !$mid) { echo "BAD $l\n"; continue;}
        if(!isset($EIDs[$eid])) $EIDs[$eid] = [];
        $EIDs[$eid][$mid] = [];
    }
    foreach(glob("rec_info{$server}/*") as $f1) {
        if($f1 == "rec_info{$server}/record_all") continue;
        $info = explode("\n",file_get_contents($f1));
        $id = false;
        $eid = false;
        $start = false;
        $len = false;
        foreach($info as $l) {
            if(!$l) continue;
            list($k,$v) = explode(":",$l);
            if(!$k) continue;
            $v = trim(rtrim($v));
            if($k == 'id') $id = $v;
            elseif($k == 'eid') $eid = $v;
            elseif($k == 'starttime') $start = intval($v);
            elseif($k == 'total_time') $len = intval($v);
        }
        if($id && $eid) {
            if(!isset($EIDs[$eid])) $EIDs[$eid] = [];
#            if(!isset($EIDs[$eid][$id])) $EIDs[$eid][$id] = [];
            $EIDs[$eid][$id] = [$start,$start+$len,$eid];
            if(!$start || !($start+$len)) {
                print_r($EIDs[$eid][$id]);
                die;
            }
        }
    }
#    print_r($EIDs);
#    exit(0);
if($options['check']) {
    $logs_rec = $DB->get_records_sql(
        "select id,meetingid,timecreated,server from {bigbluebuttonbn_logs} where log = ?",array('Create'));
    foreach($logs_rec as $id => $r) {
        if(!isset($EIDs[$r->meetingid])) {
#            echo "UNK $id $r->meetingid\n";
        } else {
            $info = bigbluebuttonbn_get_recordings_array_cached($r->meetingid,$server);
            if(!$info) echo "BAD $id $r->meetingid\n";
              else
                echo "OK $id $r->meetingid\n";
        }
    }
    
    exit(0);
}

if($options['sync']) {
    $logs_rec = $DB->get_records_sql("
        select id,meetingid,timecreated,server from {bigbluebuttonbn_logs} where 
        log = ? and norecinfo = 0 and id not in (select id from {bigbluebuttonbn_info})",array('Create'));
    #print_r($logs_rec);
    $n = 5000;
    foreach($logs_rec as $rec) {
        if(isset($NO_REC[$rec->meetingid])) continue;
        if(!strchr($rec->meetingid,'-')) continue;
        add_rec_info($rec->meetingid,0,intval($rec->timecreated));
        $n--;
        if($n < 0) break;
    }
    exit(0);
}


foreach($unrecognized as $rec) {
    $dbg = 1;
    add_rec_info($rec,1,0);
}

exit(0);

function find_time_range(&$rec_tm_range,$ltc){
    foreach($rec_tm_range as $id => $tr) {
        $c = $ltc >= $tr[0] - 30  && $ltc <= $tr[1] + 30 ? 1:0;
#        echo "  {$tr[0]} <= $ltc <= {$tr[1]} ",
#            $ltc < $tr[0]-30 ? $ltc - $tr[0] : ($ltc > $tr[1]+30 ? $ltc - $tr[1]:0), "\n";
        if($c) {
#            echo "Found meetingid $id\n";
            return $id;
        }
    }
    return false;
}
function bigbluebuttonbn_get_recordings_array_cached($rec,$server=1) {
    global $EIDs;
#    if(file_exists("eid_info/$rec")) {
#        return unserialize(file_get_contents("eid_info/$rec"));
#    }
    if(!isset($EIDs[$rec])) {
        return false;
    }
    $info = array();
    foreach($EIDs[$rec] as $id=>$v) {
        if(!isset($v[0]) || !isset($v[1])) {
            return false;
        }
        $info[$id] = [];
        $info[$id]['startTime'] = $v[0].'000';
        $info[$id]['endTime'] = $v[1].'000';
        $info[$id]['recordID'] = $id;
    }
#    print_r($info);
#    $info = bigbluebuttonbn_get_recordings_array($rec,[],1);
#    if($info) {
#        file_put_contents("eid_info/$rec",serialize($info));
#    }
    return $info;
}

function add_rec_info($rec,$dbg = false,$timecreated = 0) {
    global $DB,$NO_REC,$server;
    $logs_rec = $DB->get_records('bigbluebuttonbn_logs',array('meetingid'=>$rec,'log'=>'Create'),'','*');
    if(!count($logs_rec)) {
        throw new \Exception("Bad log rec $rec");
    }
    $ilog_rec = $DB->get_records('bigbluebuttonbn_info',array('meetingid'=>$rec),'','*');
    $info = bigbluebuttonbn_get_recordings_array_cached($rec,$server);
    if(!$info) {
        if($timecreated && $timecreated < time() - 2*24*3600) {
            foreach($logs_rec as $lid => $logs) {
                if(isset($ilog_rec[$lid])) continue;
                $DB->update_record('bigbluebuttonbn_logs',(object)array('id'=>$lid,'norecinfo'=>1,'server'=>$server));
            }
            echo "OLDREC $rec\n";
        } else 
            echo "NOREC $rec\n";

        $NO_REC[$rec] = 1;
        return;
    }
    echo $rec,' uid ',implode(',',array_keys($info)),"\n";
    if($dbg) echo " info ",implode(',',array_keys($ilog_rec)),
                    "\n log ",implode(',',array_keys($logs_rec)),"\n";

    $rec_tm_range = array();
    foreach($info as $imid => $m_info) {
        $stime = intval(substr($m_info['startTime'],0,-3));
        $etime = intval(substr($m_info['endTime'],0,-3));
        $rec_tm_range[$imid] = [$stime,$etime];
    }
if($dbg) {
    $srec_time = array();
    foreach($logs_rec as $lid => $logs) {
        $srec_time[] = intval($logs->timecreated);
    }
    sort($srec_time);
    foreach($srec_time as $i => $ltc) {
        $trid = find_time_range($rec_tm_range,$ltc);
        if(!$trid) {
            echo " $ltc -\n";
        } else {
            echo " $ltc $trid\n";
        }
    }
}
foreach($info as $imid => $m_info) {
        if(!isset($m_info['recordID'])) continue;
        $stime = intval(substr($m_info['startTime'],0,-3));
        $etime = intval(substr($m_info['endTime'],0,-3));
        $rec_info = get_mid_info($m_info['recordID']);
        if(!$rec_info) {
            echo "No info for {$m_info['recordID']}\n";
            continue;
        }
        $rec_info['meetingid'] = $rec;

#        echo "$imid start $stime end $etime\n";
        $rec_info['starttime'] = $stime;
        $rec_info['endtime'] = $etime;

        foreach($logs_rec as $lid => $logs) {
            if(isset($ilog_rec[$lid])) {
                #echo "Skip lid $lid\n";
                continue;
            }
            $ltc = intval($logs->timecreated);
            $trid = find_time_range($rec_tm_range,$ltc);
            if($trid != $imid) {
                continue;
            }
            $rec_info['id'] = $lid;
            $rec_info['server'] = $server;
#            print_r(array($lid,$m_info['recordID'],$rec_info));
            $DB->insert_record_raw('bigbluebuttonbn_info',$rec_info,false,false,true);
            $ilog_rec[$lid] = 1;
            echo "ADD $rec {$m_info['recordID']}\n";
        }
    }
    if(count($ilog_rec) != count($logs_rec)) {
        echo "part $rec\n";
    }
    foreach($logs_rec as $lid => $logs) {
        if(isset($ilog_rec[$lid])) {
            #echo "Skip lid $lid\n";
            continue;
        }
        $ltc = intval($logs->timecreated);
        if($ltc > time() - 2*24*3600) continue;
        $DB->update_record('bigbluebuttonbn_logs',(object)array('id'=>$lid,'norecinfo'=>1,'server'=>$server));
    }
}


# vim: set tabstop=4 shiftwidth=4 expandtab:
