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
 * The mod_bigbluebuttonbn locallib/bigbluebutton.
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2010 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 */

namespace mod_bigbluebuttonbn\locallib;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/bigbluebuttonbn/locallib.php');

/**
 * Wrapper for executing http requests on a BigBlueButton server.
 *
 * @copyright 2010 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bigbluebutton {

    /**
     * Returns the right URL for the action specified.
     *
     * @param string $action
     * @param array  $data
     * @param array  $metadata
     * @return string
     */
    public static function action_url($action = '', $data = array(), $metadata = array(),$server=false) {
	if($server === false || intval($server) <= 0) {
                error_log(date("Y-M-d H:m:s",time())." get '$action' from\n".
                        format_backtrace(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT),1),0);
		throw new \Exception("action_url");
	}
	$baseurl = self::sanitized_url($server) . $action . '?';
        $metadata = array_combine(
            array_map(
                function($k) {
                    return 'meta_' . $k;
                }
                , array_keys($metadata)
            ),
            $metadata
        );
        $params = http_build_query($data + $metadata, '', '&');
        return $baseurl . $params . '&checksum=' . sha1($action . $params . self::sanitized_secret($server));
    }

    /**
     * Makes sure the url used doesn't is in the format required.
     *
     * @return string
     */
    public static function sanitized_url($server=false) {
	global $CFG;    
	if($server === false || intval($server) <= 0 || !isset($CFG->bigbluebuttonbn[$server]))
		throw new \Exception("sanitized_url server ".intval($server));

	$serverurl = trim($CFG->bigbluebuttonbn[$server]['server_url']);
        if (substr($serverurl, -1) == '/') {
            $serverurl = rtrim($serverurl, '/');
        }
        if (substr($serverurl, -4) == '/api') {
            $serverurl = rtrim($serverurl, '/api');
        }
        return $serverurl . '/api/';
    }

    /**
     * Makes sure the shared_secret used doesn't have trailing white characters.
     *
     * @return string
     */
    public static function sanitized_secret($server=false) {
	global $CFG;    
	if($server === false || intval($server) <= 0 || !isset($CFG->bigbluebuttonbn[$server]))
		throw new \Exception("sanitized_secret");

	return trim($CFG->bigbluebuttonbn[$server]['shared_secret']);
    }

    /**
     * Returns the BigBlueButton server root URL.
     *
     * @return string
     */
    public static function root($server=false) {
	global $CFG;    
	if($server === false || intval($server) <= 0 || !isset($CFG->bigbluebuttonbn[$server])) {
                error_log(date("Y-M-d H:m:s",time())." get '$server' root\n".
			format_backtrace(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT),1),0);
		throw new \Exception("server_url");
	}

        $pserverurl = parse_url(trim($CFG->bigbluebuttonbn[$server]['server_url']));
        $pserverurlport = "";
        if (isset($pserverurl['port'])) {
            $pserverurlport = ":" . $pserverurl['port'];
        }
        return $pserverurl['scheme'] . "://" . $pserverurl['host'] . $pserverurlport . "/";
    }
}
