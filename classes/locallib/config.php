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
 * The mod_bigbluebuttonbn locallib/config.
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
 * Handles the global configuration based on config.php.
 *
 * @copyright 2010 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config {

    /**
     * Returns moodle version.
     *
     * @return string
     */
    public static function get_moodle_version_major() {
        global $CFG;
        $versionarray = explode('.', $CFG->version);
        return $versionarray[0];
    }

    /**
     * Returns configuration default values.
     *
     * @return array
     */
    public static function defaultvalues() {
        return array(
            'server_url' => (string) BIGBLUEBUTTONBN_DEFAULT_SERVER_URL,
            'shared_secret' => (string) BIGBLUEBUTTONBN_DEFAULT_SHARED_SECRET,
            'voicebridge_editable' => false,
            'importrecordings_enabled' => false,
            'importrecordings_from_deleted_enabled' => false,
            'waitformoderator_default' => false,
            'waitformoderator_editable' => true,
            'waitformoderator_ping_interval' => '10',
            'waitformoderator_cache_ttl' => '60',
            'userlimit_default' => '0',
            'userlimit_editable' => false,
            'uidlimit_default' => '2',
            'uidlimit_editable' => false,
            'durationlimit_default' => 0,
            'durationlimit_editable' => false,
            'preuploadpresentation_enabled' => false,
            'sendnotifications_enabled' => false,
            'recordingready_enabled' => false,
            'recordingstatus_enabled' => false,
            'meetingevents_enabled' => false,
            'participant_moderator_default' => '0',
            'scheduled_duration_enabled' => false,
            'scheduled_duration_compensation' => '10',
            'scheduled_pre_opening' => '10',
            'recordings_enabled' => true,
            'recordings_html_default' => false,
            'recordings_html_editable' => false,
            'recordings_deleted_default' => false,
            'recordings_deleted_editable' => false,
            'recordings_imported_default' => false,
            'recordings_imported_editable' => false,
            'recordings_preview_default' => true,
            'recordings_preview_editable' => false,
            'recordings_validate_url' => true,
            'recording_default' => true,
            'recording_editable' => true,
            'recording_icons_enabled' => true,
            'recording_all_from_start_default' => false,
            'recording_all_from_start_editable' => false,
            'recording_hide_button_default' => false,
            'recording_hide_button_editable' => false,
            'general_warning_message' => '',
            'general_warning_roles' => 'editingteacher,teacher',
            'general_warning_box_type' => 'info',
            'general_warning_button_text' => '',
            'general_warning_button_href' => '',
            'general_warning_button_class' => '',
            'clienttype_enabled' => false,
            'clienttype_default' => '0',
            'clienttype_editable' => true,
            'muteonstart_default' => false,
            'muteonstart_editable' => false,
        );
    }

    /**
     * Returns default value for an specific setting.
     *
     * @param string $setting
     * @return string
     */
    public static function defaultvalue($setting) {
        $defaultvalues = self::defaultvalues();
        if (!array_key_exists($setting, $defaultvalues)) {
            return;
        }
        return $defaultvalues[$setting];
    }

    /**
     * Returns value for an specific setting.
     *
     * @param string $setting
     * @return string
     */
    public static function get($setting) {
        global $CFG;
	if($setting == 'shared_secret' || $setting == 'server_url') {
		error_log(date("Y-M-d H:m:s",time())." get '$setting' from\n".
			format_backtrace(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT),1),0);
		    throw new \Exception('bigbluebuttonbn_bad_config_option');
	}
	if (isset($CFG->bigbluebuttonbn[$setting])) {
            return (string)$CFG->bigbluebuttonbn[$setting];
        }
        if (isset($CFG->{'bigbluebuttonbn_'.$setting})) {
            return (string)$CFG->{'bigbluebuttonbn_'.$setting};
        }
        return self::defaultvalue($setting);
    }

    public static function server_list() {
	global $CFG;
	$servers = array();
	$opts = array('server_url'   =>1, 'shared_secret'=>1, 'server_name'  =>1,
		'denybbbserver'=>0, 'autobbbserver'=>0, 'connlimitserver'=>0,
		'costbbbserver'=>0, 'multbbbserver'=>0);
	$last_server = 0;
	for($i=1; $i < 10; $i++) {
	   if (!isset($CFG->bigbluebuttonbn[$i]) || 
	       !is_array($CFG->bigbluebuttonbn[$i])) continue;
	   $ok = 1;
	   foreach($opts as $k=>$v) {
		if($v && !isset($CFG->bigbluebuttonbn[$i][$k])) {
		    $ok = 0; break;
		}
	   }
	   if(!$ok) continue;
	   $last_server = $i;
	   $servers[$i] = $CFG->bigbluebuttonbn[$i];

	}
	if(!$last_server &&
	   isset($CFG->bigbluebuttonbn['server_url']) &&
	   isset($CFG->bigbluebuttonbn['shared_secret']) &&
	   isset($CFG->bigbluebuttonbn['server_name'])) {
		$last_server = 1;
		$servers[1]  = $CFG->bigbluebuttonbn;
	}
	return $last_server > 0 ? $servers : false;
    }

    public static function select_server() {
	global $CFG;
	$servers = self::server_list();
	$last_server = 0;
	for($i=1; $i < 10; $i++) {
	   if(!isset($servers[$i])) break;
	   $last_server = $i;
	   $servers[$i] = $servers[$i]['server_name'];
	}
	if($last_server)
	   $servers[0] = 'Any';
	return $last_server > 0 ? $servers : false;
    }


    /**
     * Validates if recording settings are enabled.
     *
     * @return boolean
     */
    public static function recordings_enabled() {
        return (boolean)self::get('recordings_enabled');
    }

    /**
     * Validates if imported recording settings are enabled.
     *
     * @return boolean
     */
    public static function importrecordings_enabled() {
        return (boolean)self::get('importrecordings_enabled');
    }

    /**
     * Validates if clienttype settings are enabled.
     *
     * @return boolean
     */
    public static function clienttype_enabled() {
        return (boolean)self::get('clienttype_enabled');
    }

    /**
     * Wraps current settings in an array.
     *
     * @return array
     */
    public static function get_options() {
        return array(
               'version_major' => self::get_moodle_version_major(),
               'voicebridge_editable' => self::get('voicebridge_editable'),
               'importrecordings_enabled' => self::get('importrecordings_enabled'),
               'importrecordings_from_deleted_enabled' => self::get('importrecordings_from_deleted_enabled'),
               'waitformoderator_default' => self::get('waitformoderator_default'),
               'waitformoderator_editable' => self::get('waitformoderator_editable'),
               'userlimit_default' => self::get('userlimit_default'),
               'userlimit_editable' => self::get('userlimit_editable'),
               'uidlimit_default' => self::get('uidlimit_default'),
               'uidlimit_editable' => self::get('uidlimit_editable'),
               'durationlimit_default' => self::get('durationlimit_default'),
               'durationlimit_editable' => self::get('durationlimit_editable'),
               'preuploadpresentation_enabled' => self::get('preuploadpresentation_enabled'),
               'sendnotifications_enabled' => self::get('sendnotifications_enabled'),
               'recordings_enabled' => self::get('recordings_enabled'),
               'meetingevents_enabled' => self::get('meetingevents_enabled'),
               'recordings_html_default' => self::get('recordings_html_default'),
               'recordings_html_editable' => self::get('recordings_html_editable'),
               'recordings_deleted_default' => self::get('recordings_deleted_default'),
               'recordings_deleted_editable' => self::get('recordings_deleted_editable'),
               'recordings_imported_default' => self::get('recordings_imported_default'),
               'recordings_imported_editable' => self::get('recordings_imported_editable'),
               'recordings_preview_default' => self::get('recordings_preview_default'),
               'recordings_preview_editable' => self::get('recordings_preview_editable'),
               'recordings_validate_url' => self::get('recordings_validate_url'),
               'recording_default' => self::get('recording_default'),
               'recording_editable' => self::get('recording_editable'),
               'recording_icons_enabled' => self::get('recording_icons_enabled'),
               'recording_all_from_start_default' => self::get('recording_all_from_start_default'),
               'recording_all_from_start_editable' => self::get('recording_all_from_start_editable'),
               'recording_hide_button_default' => self::get('recording_hide_button_default'),
               'recording_hide_button_editable' => self::get('recording_hide_button_editable'),
               'general_warning_message' => self::get('general_warning_message'),
               'general_warning_box_type' => self::get('general_warning_box_type'),
               'general_warning_button_text' => self::get('general_warning_button_text'),
               'general_warning_button_href' => self::get('general_warning_button_href'),
               'general_warning_button_class' => self::get('general_warning_button_class'),
               'clienttype_enabled' => self::get('clienttype_enabled'),
               'clienttype_editable' => self::get('clienttype_editable'),
               'clienttype_default' => self::get('clienttype_default'),
               'muteonstart_editable' => self::get('muteonstart_editable'),
               'muteonstart_default' => self::get('muteonstart_default'),
          );
    }
}
