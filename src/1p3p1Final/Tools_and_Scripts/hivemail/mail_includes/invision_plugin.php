<?php
//
// +-------------------------------------------------------------+
// | HiveMail version 1.2 (English)
// | Copyright ©2002-2003 Chen Avinadav <chen@hivemail.com>
// +-------------------------------------------------------------+
// | HIVEMAIL IS NOT FREE SOFTWARE
// | If you have downloaded this software from a website other
// | than www.hivemail.com or if you have otherwise received
// | this software from someone who is not a representative of
// | this organization you are involved in an illegal activity.
// | License agreement: http://www.hivemail.com/license.html
// +-------------------------------------------------------------+
// |
// | INVISION POWER BOARD v1.2 (RC2+) INTEGRATION
// | 
// +-------------------------------------------------------------+

error_reporting(E_ALL & ~E_NOTICE);

//-------------------------------------------------------
// Stop people from calling this file directly
//-------------------------------------------------------

if ( ! defined('IPB_PLUGIN') )
{
	return;
}

class invision_plugin
{
	var $hive_error   = "";
	var $hive_options = array();
	var $this_folder  = "";
	var $DB_Hive      = "";


	function invision_plugin()
	{
	
		//=========================================
		// EDIT THIS IF NEEDED
		//=========================================
		
		define( 'HIVE_TBL', 'hive_' );
		
		
		
		//-------------------------------------------------------
		// Change to right directory
		//-------------------------------------------------------
		
		$this->this_folder = getcwd();
		
		chdir(dirname(__FILE__));
		
		//-------------------------------------------------------
		// We are outside the admin CP
		//-------------------------------------------------------
		
		define('INADMIN', false);
		
		define('VB_PLUGIN', 1 );
		
		//-------------------------------------------------------
		// Start database connection
		//-------------------------------------------------------
		
		require('../includes/config.php');
		require('../includes/db_mysql.php');
		
		if ( ! function_exists('version_compare') or version_compare(phpversion(), '4.2.0') < 0 )
		{
			$config['server'] .= ':3306';
		}
		
		$this->DB_Hive = new DB_MySQL($config, true, true);
		
		//-------------------------------------------------------
		// Get all common functions
		//-------------------------------------------------------

		require('../includes/init.php');
		
		//-------------------------------------------------------
		// Get options
		//-------------------------------------------------------
		
		while ( $setting = $this->DB_Hive->fetch_array($settings, 'SELECT * FROM '.HIVE_TBL.'setting') )
		{
			$this->hive_options["$setting[variable]"] = $setting['value'];
		}
		
		//-------------------------------------------------------
		// The domain names array
		//-------------------------------------------------------
		
		$this->hive_options['domainnames'] = preg_split("#\r?\n#", $this->hive_options['domainname']);
		$this->hive_options['domainname']  = $this->hive_options['domainnames'][0];
		
		//-------------------------------------------------------
		// Create the option list of domain names
		//-------------------------------------------------------
		
		$this->hive_domainname_options = '';
		
		if ( is_array($this->hive_options['domainnames']) and count($this->hive_options['domainnames']) > 0 )
		{
			foreach ( $this->hive_options['domainnames'] as $curdomainname )
			{
				$this->hive_domainname_options .= "<option value=\"$curdomainname\"".iif($userdomain == $curdomainname, ' selected="selected"', '').">$curdomainname</option>\n";
			}
		}
	}
	
	//-------------------------------------------------------
	// Creates a user
	//-------------------------------------------------------
	
	function hivemail_register_user($member, $onreg = true)
	{
		global $ibforums, $DB, $std;
		
		if ( ! $this->hive_options['regopen'] )
		{
			return;
		}
		
		$hive_username   = trim($ibforums->input['hive_username']);
		$hive_userdomain = trim($ibforums->input['hive_userdomain']);
		
		//-------------------------------------------------------
		// Domain name
		//-------------------------------------------------------
		
		if ( ! in_array( $hive_userdomain, $this->hive_options['domainnames'] ) )
		{
			$hive_userdomain = $hive_options['domainname'];
		}
		
		//-------------------------------------------------------
		// Some error checking
		//-------------------------------------------------------
		
		if ($this->DB_Hive->query_first("SELECT username FROM ".HIVE_TBL."user WHERE username = '$hive_username'"))
		{
			if ($onreg)
			{
				$this->delete_reg_user($member['id']);
			}
			
			$this->hive_error = 'thing_name_taken';
			
			return;
			
		}
		elseif ( preg_match('#[^a-z0-9_.]#i', $hive_username) or preg_match('#^[^a-z]#i', $hive_username) or strlen($hive_username) < 2 )
		{
			if ($onreg)
			{
				$this->delete_reg_user($member['id']);
			}
			
			$this->hive_error = 'incorrect_thing_name';
			
			return;
		}
		
		//-------------------------------------------------------
		// Create user
		//-------------------------------------------------------
		
		$this->DB_Hive->query("
			INSERT INTO ".HIVE_TBL."user
			(userid, username, password, usergroupid, skinid, realname, regdate, lastvisit, cols, birthday, options, replyto, font, timezone, soundid, domain, vbuserid, altemail)
			VALUES
			(NULL, '".addslashes($hive_username)."', '".addslashes($member['password'])."', ".iif($this->hive_options['moderate'], 3, 2).", ".$this->hive_options['defaultskin'].", '".addslashes($member['name'])."', ".TIMENOW.", ".TIMENOW.", '".addslashes(USER_DEFAULTCOLS)."', '".addslashes('0')."', ".USER_DEFAULTBITS.", '".addslashes($hive_username.$hive_userdomain)."', 'Verdana|10|Regular|Black|None', '".addslashes('0')."', ".intval($this->DB_Hive->get_field('SELECT soundid FROM '.HIVE_TBL.'sound WHERE userid <= 0 ORDER BY userid LIMIT 1')).", '".addslashes($ibforums->input['hive_userdomain'])."', {$member['id']}, '".addslashes($member['email'])."')
		");
		
		$hive_userid = $this->DB_Hive->insert_id();
		
		//-------------------------------------------------------
		// Log user in
		//-------------------------------------------------------
		
		if ($onreg)
		{
			$userid = $member['id'];
			ini_set('session.name', 'hivesession');
			session_start();
			$std->my_setcookie(session_name(), session_id(), TIMENOW + (60 * 60 * 24 * 365));
			$_SESSION['userid']     = $hive_userid;
			$_SESSION['password']   = md5(md5($member['password']));
			$_SESSION['ipaddress']  = md5(IPADDRESS);
			$_SESSION['staylogged'] = 365;
		}
		
		//-------------------------------------------------------
		// Update Invision's database
		//-------------------------------------------------------
		
		if ( ! $DB->field_exists( 'hiveuserid', 'ibf_member_extra' ) )
		{
			$DB->query("ALTER TABLE ibf_member_extra ADD hiveuserid mediumint(8) NOT NULL DEFAULT ''");
		}
		
		$DB->query("SELECT id, hiveuserid FROM ibf_member_extra WHERE id={$member['id']}");
		
		if ( $r = $DB->fetch_row() )
		{
			// User _extra exists, update...
			
			$DB->query("UPDATE ibf_member_extra SET hiveuserid=$hive_userid WHERE id={$member['id']}");
		}
		else
		{
			// User _extra doesn't exist...
			
			$DB->query("INSERT INTO ibf_member_extra SET hiveuserid=$hive_userid, id={$member['id']}");
		}
	
		return $hive_userid;
	}
	
	//-------------------------------------------------------
	// Delete new reg. user if error during registration
	//-------------------------------------------------------
	
	function delete_reg_user($userid)
	{
		global $DB;
		
		$DB->query("DELETE FROM ibf_members WHERE id=$userid");
		$DB->query("DELETE FROM ibf_member_extra WHERE id=$userid");
		$DB->query("DELETE FROM ibf_pfields_content WHERE member_id=$userid");
	}
	
	//-------------------------------------------------------
	// Updates the user's hivemail password
	//-------------------------------------------------------
	
	function hivemail_update_password($password, $userid)
	{
		$this->DB_Hive->query("
			UPDATE ".HIVE_TBL."user
			SET password = '".addslashes($password)."'
			WHERE vbuserid = $userid
		");
	}
	
	//-------------------------------------------------------
	// Desctructor
	//-------------------------------------------------------
	
	function destructor()
	{
		chdir($this->this_folder);
	}

}

function stripslashesarray($array) {
	if (is_array($array)) {
		foreach($array as $key => $val) {
			if (is_array($val)) {
				$array["$key"] = stripslashesarray($val);
			} elseif (is_string($val)) {
				if (get_cfg_var('magic_quotes_sybase')) {
					$array["$key"] = str_replace("''", "'", $val);
				} else {
					$array["$key"] = stripslashes($val);
				}
			}
		}
	}

	return $array;
}

function iif($eval, $true, $false = '') {
	return (($eval == true) ? ($true) : ($false));
}

?>