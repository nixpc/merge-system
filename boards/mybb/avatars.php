<?php
/**
 * MyBB 1.8 Merge System
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/download/merge-system/license/
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class MYBB_Converter_Module_Avatars extends Converter_Module_Avatars {

	var $settings = array(
		'friendly_name' => 'avatars',
		'progress_column' => 'uid',
		'default_per_screen' => 20,
	);

	function get_avatar_path()
	{
		$query = $this->old_db->simple_select("settings", "value", "name = 'bburl'", array('limit' => 1));
		$bburl = $this->old_db->fetch_field($query, 'value');
		$this->old_db->free_result($query);

		$query = $this->old_db->simple_select("settings", "value", "name = 'avataruploadpath'", array('limit' => 1));
		$uploadspath = str_replace('./', $bburl.'/', $this->old_db->fetch_field($query, 'value'));
		$this->old_db->free_result($query);

		return $uploadspath;
	}

	function import()
	{
		global $import_session;

		$query = $this->old_db->simple_select("users", "*", "avatars!='", array('limit_start' => $this->trackers['start_attachments'], 'limit' => $import_session['attachments_per_screen']));
		while($avatar = $this->old_db->fetch_array($query))
		{
			$this->insert($avatar);
		}
	}

	function convert_data($data)
	{
		global $insert_data, $mybb;

		$insert_data = array();

		// MyBB 1.8 values
		$insert_data['uid'] = $this->get_import->uid($data['uid']);
		// TODO: it's not always a jpg file, need to get the extension. Check whether get_extension ignores query strings, possible PR?
		$insert_data['avatar'] = $mybb->settings['avataruploadpath'] . "/avatar_{$insert_data['uid']}.jpg?dateline=".TIME_NOW;
		$insert_data['avatardimensions'] = $data['avatardimensions'];
		$insert_data['avatartype'] = $data['avatartype'];

		return $insert_data;
	}

	function fetch_total()
	{
		global $import_session;

		// Get number of users with avatar
		if(!isset($import_session['total_avatars']))
		{
			$query = $this->old_db->simple_select("users", "COUNT(*) as count", "avatar!='");
			$import_session['total_avatars'] = $this->old_db->fetch_field($query, 'count');
			$this->old_db->free_result($query);
		}

		return $import_session['total_avatars'];
	}

	function generate_raw_filename($avatar)
	{
		global $import_session;

		return ltrim(str_replace($import_session['avatarspath'], '', $avatar['avatar']), '/');
	}
}

