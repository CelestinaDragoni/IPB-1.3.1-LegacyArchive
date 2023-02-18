<?php
/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2 Module File
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Member Sync Module File
|   > Module written by Matt Mecham
|   > Date started: 7th July 2003
|
+--------------------------------------------------------------------------
|
| USAGE:
| ------
|
| This module is designed to hold any module modifications to with registration
| It doesn't do much in itself, but custom code can be added to handle
| synchronization, etc.
|
| - on_create_account: Is called upon successful account creation
| - on_register_form: Is called when the form is displayed
| - on_login: Is called when logged in succcessfully
| - on_delete: Is called when member deleted (single, multiple)
| - on_email_change: When email address change is confirmed
| - on_profile_update: When profile is updated (msn, sig, etc)
| - on_pass_change: When password is updated
| - on_group_change: When the member's membergroup has changed
| - on_name_change: When the member's name has been changed
+--------------------------------------------------------------------------
*/

class ipb_member_sync
{
	var $class  = "";
	var $hiveid = 0;
	var $html   = "";
	var $hive   = "";
	
	function ipb_member_sync()
	{
		global $DB, $ibforums;
		
		if ( $ibforums->member['id'] )
		{
			$DB->query("SELECT id, hiveuserid FROM ibf_member_extra WHERE id={$ibforums->member['id']}");
			
			$r = $DB->fetch_row();
			
			$this->hiveid = intval($r['hiveuserid']);
		}
	}
	
	//-----------------------------------------------
	// register_class($class)
	//
	// Register a $this-> with this class 
	//
	//-----------------------------------------------

	function register_class($class)
	{
		$this->class = &$class;
		$this->error = "";
	}

	//-----------------------------------------------
	// on_create_account($member)
	//
	// $member = array( 'id', 'name', 'email',
	// 'password', 'mgroup'...etc)
	//
	//-----------------------------------------------
	
	function on_create_account($member)
	{
		global $DB, $std, $ibforums;
		
		//---- START
		
		//-------------------------------------------
		// Add hivemail account?
		//-------------------------------------------
		
		$ibforums->input['hive_username'] = trim($ibforums->input['hive_username']);
		
		if ( $ibforums->input['hive_username'] != "" )
		{
			
			define('IPB_PLUGIN', true);
			require_once('../mail/includes/invision_plugin.php');
			
			$this->hive = new invision_plugin();
			
			$this->hive->hivemail_register_user($member, true);
			
			if ( $this->hive->hive_error != "" )
			{
				$ibforums->lang['thing_name_taken']     = sprintf( $ibforums->lang['thing_name_taken']    , 'Board Email Address' );
				$ibforums->lang['incorrect_thing_name'] = sprintf( $ibforums->lang['incorrect_thing_name'], 'Board Email Address' );
		
				$this->class->show_reg_form($this->hive->hive_error);
				$this->error = 1;
				$this->hive->destructor();
				return;
			}
			
			$this->hive->destructor();
		}
		
		//---- END
	}
	
	//-----------------------------------------------
	// on_register_form()
	//
	//
	//-----------------------------------------------
	
	function on_register_form()
	{
		global $DB, $std, $ibforums;
		
		//---- START
		
		//-------------------------------------------
		// HiveMail integration
		// As register_form is defualt action, we can
		// either show the hivemail entry in the reg
		// form if they are not a member, or we can
		// show the hive mail only sign up form
		//-------------------------------------------
		
		if ( $this->hive == "" )
		{
			define('IPB_PLUGIN', true);
			require_once('../mail/includes/invision_plugin.php');
			
			$this->hive = new invision_plugin();
		}
		
		if ( $ibforums->member['id'] )
		{
			//-------------------------------------------
			// They are a member, are they a hivemail member?
			//-------------------------------------------
			
			if ( $this->hiveid AND $hiveuser = $this->hive->DB_Hive->query_first("SELECT userid FROM ".HIVE_TBL."user WHERE userid = {$this->hiveid}"))
			{
				//-------------------------------------------
				// yeah, chuck 'em to ucp then
				//-------------------------------------------
				
				if ( $ibforums->input['mailsignup'] )
				{
					$this->hive->destructor();
					$std->boink_it( $ibforums->base_url.'act=UserCP' );
				}
			}
			else
			{
				//-------------------------------------------
				// Forward them to the IPB module to show
				// mail only sign-up
				//-------------------------------------------
				
				if ( $ibforums->input['mailsignup'] )
				{
					$this->hive->destructor();
					$std->boink_it( $ibforums->base_url.'act=module&module=hivemail&show=forumform' );
				}
			}
		}
		else
		{
			//-------------------------------------------
			// Normal reg form! chop in hivemail option
			//-------------------------------------------
			
			$extra = $this->class->html->field_entry(
													 'Enter a username for your own board email account',
													 'This is optional, please leave blank if you do not wish to get an email account.<br />Only letters and numbers please.',
													 $this->class->html->field_textinput('hive_username', $ibforums->input['hive_username'])."&nbsp;<select name='hive_userdomain' class='forminput'>".$this->hive->hive_domainname_options."</select>"
													);
			
			$this->class->output = str_replace( "<!--IBF.MODULES.EXTRA-->", $extra, $this->class->output );
			
		}
		
		$this->hive->destructor();
		
		//---- END
	}
	
	//-----------------------------------------------
	// on_login()
	//
	// $member = array( 'id', 'name', 'email', 'pass')
	//           ...etc
	//-----------------------------------------------
	
	function on_login($member=array())
	{
		global $DB, $std, $ibforums;
		
		//---- START
		
		
		
		//---- END
	}
	
	//-----------------------------------------------
	// on_delete($ids)
	//
	// $ids = array | integer
	// If array, will contain list of ids
	//-----------------------------------------------
	
	function on_delete($ids=array())
	{
		global $DB, $std, $ibforums;
		
		$type = "";
		
		//---- START
		
		if ( is_array($ids) and count($ids) > 0 )
		{
			$type = arr;
		}
		else
		{
			$type = int;
		}
		
		
		
		//---- END
	}

	//-----------------------------------------------
	// on_email_change($id, $new_email)
	//
	// $id        = int member_id
	// $new_email = string new email address
	//-----------------------------------------------
	
	function on_email_change($id, $new_email)
	{
		global $DB, $std, $ibforums;
		
		//---- START
		
		
		//---- END
	}
	
	//-----------------------------------------------
	// on_pass_change($id, $new_raw)
	//
	// $id        = int member_id
	// $new_raw   = string new plain text password
	//-----------------------------------------------
	
	function on_pass_change($id, $new_raw)
	{
		global $DB, $std, $ibforums;
		
		//---- START
		
		//-------------------------------------------
		// HiveMail integration
		// Update hivemail passy
		//-------------------------------------------
		
		if ( $this->hive == "" )
		{
			define('IPB_PLUGIN', true);
			require_once('../mail/includes/invision_plugin.php');
			
			$this->hive = new invision_plugin();
		}
		
		$this->hive->hivemail_update_password(md5($new_raw), $id);
		
		$this->hive->destructor();
		
		//---- END
	}
	
	//-----------------------------------------------
	// on_profile_update($member)
	// 
	// 'website','icq_number','aim_name','yahoo','msnname','integ_msg'
	// 'location','interests', 'title'
	// 'id'
	//-----------------------------------------------
	
	function on_profile_update( $member=array(), $custom_fields=array() )
	{
		global $DB, $std, $ibforums;
		
		//---- START
		
		
		//---- END
	}
	
	//-----------------------------------------------
	// on_group_change()
	// 
	// $id        = int member_id
	// $new_group = new int() group id
	//-----------------------------------------------
	
	function on_group_change( $id, $new_group )
	{
		global $DB, $std, $ibforums;
		
		//---- START
		
		
		//---- END
	}
	
	//-----------------------------------------------
	// on_name_change()
	// 
	// $id        = int member_id
	// $new_group = new name
	//-----------------------------------------------
	
	function on_name_change( $id, $new_name )
	{
		global $DB, $std, $ibforums;
		
		//---- START
		
		
		//---- END
	}


}


?>