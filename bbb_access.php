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

/*
    location = /check_access {
	internal;
	proxy_pass         "https://lms.guap.ru/dev/mod/bigbluebuttonbn/bbb_access.php";
	proxy_pass_request_body off;
	proxy_set_header        Content-Length "";
	proxy_set_header   X-Sid   $cookie_MoodleSessiondev;
	proxy_set_header   X-Href  $request_uri;
	proxy_set_header   Cookie 'None=empty';
	include    fastcgi_params;
    }
 */
$data = false;
$ctm = time();

function check_moodle_session($sid) {
	require(__DIR__.'/../../config.php');
	global $CFG;
	$sclass = \core\session\manager::get_handler_class();
	if($sclass == '\core\session\file') {
		if (!empty($CFG->session_file_save_path)) {
		    $sessiondir = $CFG->session_file_save_path;
		} else {
		    $sessiondir = ini_get('session.save_path') ?: "$CFG->dataroot/sessions";
		}
		$sfile = $sessiondir.'/sess_'.$sid;
		if(file_exists($sfile)) {
			$sdata = session_decode(file_get_contents($sfile,0));
			if ($sdata !== false &&
				$_SESSION['USER']->lastaccess > $ctm-3*3600) {
			    error_log("check_moodle_session OK ".$_SESSION['USER']->lastaccess."\n",0);
			    return 1;
			}
			error_log("check_moodle_session too old ".($ctm - $_SESSION['USER']->lastaccess)."\n",0);
		}
		return 0;
	}
	error_log("check_moodle_session $sclass NO\n",0);
	return 0;
}
    #header("HTTP/1.0 200 OK");
    #die;

    $rfile = __DIR__.'/../../config.php';
    $data = file_exists($rfile) ? file_get_contents($rfile,0): false;
    if(!preg_match(':\$CFG->dataroot\s+=\s+[\"\']([^\'\"]+)[\"\'];:',$data,$matched)) {
    	header("HTTP/1.0 403 Forbidden");
    	die;
    }
    $dataroot = $matched[1];
    
    if (!isset($_SERVER['HTTP_X_SID']) ||
        !isset($_SERVER['HTTP_X_HREF'])) {
    	header("HTTP/1.0 403 Forbidden");
    	die;
        }
    $sid = $_SERVER['HTTP_X_SID'];
    if(!preg_match('/^[0-9a-f]+$/',$sid)) {
    	header("HTTP/1.0 403 Forbidden");
    	die;
    }
    $href = $_SERVER['HTTP_X_HREF'];
    $srv=$_SERVER['HTTP_X_SRC'] ?? '?';
    $rid='';
    if(preg_match(':/meetingId=([0-9a-f-]+):',$href,$matches)) {
    	$rid = $matches[1];
    } elseif(preg_match(':/presentation/([0-9a-f-]+)/:',$href,$matches)) {
    	$rid = $matches[1];
    } else {
    	# error_log("check href NO restrict $srv $sid $href\n",0);
    	header("HTTP/1.0 200 OK");
    	die;
    }
    $ret = false;
    $msg = '';
    do {

	$cachedir = $dataroot.'/bbbcache';
	if(!is_dir($cachedir)) break;
	$rfile = $cachedir .'/'. $rid;
	$data = file_exists($rfile) ? file_get_contents($rfile,0): false;
	if($data === false) {
		$msg = "No rid";
		break;
	}
	$info = unserialize($data);
	if(is_array($info) && isset($info[$sid]) && $info[$sid] > $ctm - 5*60) {
		$sfile = $cachedir .'/sid_'. $sid;
		if(file_exists($sfile) && filemtime($sfile) > $ctm - 1*60) {
			$ret = true;
			break;
		}
		if(check_moodle_session($sid)) {
			#error_log("check_moodle_session $sid OK\n",0);
			file_put_contents($sfile,'1');
			$ret = true;
			break;
		} else {
			if(file_exists($sfile)) unlink($sfile);
			$msg = " SID bad";
		}
	} else {
		$msg = " RID old";
	}
    } while(false);
    if(!$ret)
        error_log("check href $srv $sid $href ".($ret ? "OK":"BAD")."$msg\n",0);
    header($ret ? "HTTP/1.0 200 OK":"HTTP/1.0 403 Forbidden");
    die;
