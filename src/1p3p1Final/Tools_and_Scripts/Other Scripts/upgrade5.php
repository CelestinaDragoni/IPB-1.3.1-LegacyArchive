<?php



/*

+--------------------------------------------------------------------------

|   IBFORUMS v1 UPGRADE FROM 1.1.X to v1.2

|   ========================================

|   by Matthew Mecham

|   (c) 2001,2002 IBForums

|   http://www.ibforums.com

|   ========================================

|   Web: http://www.ibforums.com

|   Email: phpboards@ibforums.com

|   Licence Info: phpib-licence@ibforums.com

+---------------------------------------------------------------------------

|

|   > Upgrade Script #5

|   > Script written by Matt Mecham

|   > Date started: 10th June 2003

|

+--------------------------------------------------------------------------

*/



//-----------------------------------------------

// USER CONFIGURABLE ELEMENTS

//-----------------------------------------------

 

// Root path



$root_path = "./";



//-----------------------------------------------

// NO USER EDITABLE SECTIONS BELOW

//-----------------------------------------------

 

error_reporting  (E_ERROR | E_WARNING | E_PARSE);

set_magic_quotes_runtime(0);



$template = new template;

$std      = new installer;



$VARS = $std->parse_incoming();



//--------------------------------

// Import $INFO, now!

//--------------------------------



require $root_path."conf_global.php";



//--------------------------------

// Load the DB driver and such

//--------------------------------



$INFO['sql_driver'] = !$INFO['sql_driver'] ? 'mySQL' : $INFO['sql_driver'];



$to_require = $root_path."sources/Drivers/".$INFO['sql_driver'].".php";

require ($to_require);



$DB = new db_driver;



$DB->obj['sql_database']     = $INFO['sql_database'];

$DB->obj['sql_user']         = $INFO['sql_user'];

$DB->obj['sql_pass']         = $INFO['sql_pass'];

$DB->obj['sql_host']         = $INFO['sql_host'];

$DB->obj['sql_tbl_prefix']   = $INFO['sql_tbl_prefix'];



// Get a DB connection



$DB->connect();



// Switch off auto_error



$DB->return_die = 1;



//---------------------------------------

// Sort out what to do..

//---------------------------------------



switch($VARS['a'])

{

	case 'tables':

		do_tables();

		break;

		

	case 'alter':

		do_alter();

		break;

		

	case 'index':

		do_index();

		break;

		

	case 'insert':

		do_insert();

		break;

		

	case 'templates':

		do_templates();

		break;

		

	case 'cleanup':

		do_cleanup();

		break;

		

	case 'optimize':

		do_optimize();

		break;

		

	default:

		do_intro();

		break;

}



//+---------------------------------------







function do_optimize()

{

	global $std, $template, $root, $DB;

	

	run_sql('optimize');

	

	$template->print_top('Optimization Complete');

	

	$template->contents .= "<div class='centerbox'>

							 <div class='tableborder'>

							  <div class='maintitle'>Upgrade from v1.1.X to v1.2 COMPLETE</div>

							  <div class='pformstrip'>Your new v1.2 installation has been optimized</div>

							  <div class='tablepad'>

							   The upgrade is now complete and you may go to your board!

							   <br /><br />

							   <b>PLEASE REMOVE THIS UPGRADE SCRIPT AS SOON AS POSSIBLE</b>

							  </div>

							 </div>

							 <div class='fade'>&nbsp;</div>

							</div>";

	

	$template->output();

}



//+---------------------------------------



function do_cleanup()

{

	global $std, $template, $root, $DB;

	

	// Insert new CSS

	

	$css_text = get_main_css();

	

	$DB->query("INSERT INTO ibf_css (css_name, css_text, css_comments) VALUES ('Invision Style Sheet (1.2)', '$css_text', NULL)");

	

	$new_css_id = $DB->get_insert_id();

	

	//---------------------------------------------

	// Save new wrapper

	//---------------------------------------------

	

	$DB->query("INSERT INTO ibf_templates set template='".get_main_wrapper()."', name='IPB 1.2 Wrapper'");

	

	$new_wrap_id = $DB->get_insert_id();

	

	//---------------------------------------------

	// Change all old skin sets to hidden

	//---------------------------------------------

	

	$DB->query("UPDATE ibf_skins SET hidden=1, default_set=0");

		

	// Get a new ID for the new skin set

	

	$barney = array( 'sname'      => "IPB Skin Set 1.2 (NEW)",

					 'set_id'     => 1,

					 'tmpl_id'    => $new_wrap_id,

					 'img_dir'    => 1,

					 'css_id'     => $new_css_id,

					 'hidden'     => 0,

					 'default_set'=> 1,

					 'macro_id'   => 1,

				   );

					   

	$DB->query("REPLACE INTO ibf_macro_name SET set_id=1, set_name='IPB 1.2 Macro Set'");

	

	$DB->query("SELECT MAX(sid) as new_id FROM ibf_skins");

	

	$row = $DB->fetch_row();

	

	$barney['sid'] = $row['new_id'] + 1;



	$db_string = $DB->compile_db_insert_string( $barney );

	

	$DB->query("INSERT INTO ibf_skins (".$db_string['FIELD_NAMES'].") VALUES(".$db_string['FIELD_VALUES'].")");

	

	// Change member skins to default

	

	$DB->query("UPDATE ibf_members SET skin=NULL");

	

	//---------------------------------------------

	// Sort out new forum masks

	//---------------------------------------------

	

	$oq = $DB->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_id");



	while( $r = $DB->fetch_row($oq) )

	{

		$nq = $DB->query("REPLACE INTO ibf_forum_perms SET perm_id={$r['g_id']}, perm_name='{$r['g_title']} Mask'");

		$bq = $DB->query("UPDATE ibf_groups SET g_perm_id={$r['g_id']} WHERE g_id={$r['g_id']}");

	}

	

	//---------------------------------------------

	// Remove old macros

	//---------------------------------------------

	

	$DB->query("DELETE FROM ibf_macro WHERE macro_value IN ('M_ADDMEM', 'M_DELETE', 'M_REPLY', 'tbl_width', 'tbl_border')");

	

	//---------------------------------------------

	// Other stuff

	//---------------------------------------------



	$DB->query("update ibf_members SET restrict_post=0");

	

	$oq = $DB->query("SELECT * FROM ibf_rules");

	

	while ( $r = $DB->fetch_row($oq) )

	{

		$nq = $DB->query("UPDATE ibf_forums SET rules_title='{$r['title']}', rules_text='{$r['body']}' WHERE id={$r['fid']}");

	}

	



	$DB->query("drop table if exists ibf_rules");



	$DB->query("update ibf_messages SET read_state=1 where vid='sent'");



	$DB->query("DROP TABLE if exists ibf_attachments");



	

	$template->print_top('Upgrade Complete');

	

	$template->contents .= "<div class='centerbox'>

							 <div class='tableborder'>

							  <div class='maintitle'>Upgrade from v1.1.X to v1.2</div>

							  <div class='pformstrip'>Upgrade Complete!</div>

							  <div class='tablepad'>

							   <b>The upgrade was successful!</b>

							   <br><br>

							   <b>Skin Set Changes</b>

							   <br>

							   Unfortunately v1.1.X

							   skins are not compatible with this new version. All old skin sets have been set to 'hidden' and all members

							   have been moved onto the new default skin.

							   <br><br>

							   The new skin set will use the directory 'style_images/1/' as the image directory. Ensure that all the images

							   in there have been updated from the download zip. If the directory does not exist, create it and upload

							   the contents of the folder 'style_images/1/' from the zip file.

							   <br><br>

							   <b>IF YOU DO NOT WISH TO OPTIMIZE PLEASE REMOVE THIS UPGRADE SCRIPT AS SOON AS POSSIBLE</b>

							   <br /><br />

							   <b>Optimization</b>

							   <br />

							   Proceed if you wish to complete your IPB upgrade by optimizing the tables - this step can take some time

							   depending on the amount of topics and posts you have. If you have a very large amount of posts (over 500,000) you

							   might want to skip this.

							   <br /><br />

							   <b><a href='upgrade5.php?a=optimize'>Optimize my IPB installation</a> &gt;&gt;</b>

							  </div>

							 </div>

							 <div class='fade'>&nbsp;</div>

							</div>";

	

	

	$template->output();

}





//+---------------------------------------







function do_templates()

{

	global $std, $template, $root, $DB;

	

	//-----------------------------------

	// Lets open the style file

	//-----------------------------------

	

	$style_file = $root.'install_templates.txt';

	

	if ( ! file_exists($style_file) )

	{

		install_error("Could not locate '$style_file'. <br><br>Check to ensure that this file exists in the same location as this script.<br><br>You may need to enter a value for the root path in this installer script, to do this, simply open up this script in a text editor and enter a value in \$root - remember to add a trailing slash. NT users will need to use double backslashes");

	}

	

	if ( $fh = fopen( $style_file, 'r' ) )

	{

		$data = fread($fh, filesize($style_file) );

		fclose($fh);

	}

	else

	{

		install_error("Could open '$style_file'");

	}

	

	if (strlen($data) < 100)

	{

		install_error("Err 1:'$style_file' is incomplete, please re-upload a fresh copy over the existing copy on the server'");

	}

	

	// Chop up the data file.

	

	$template_rows = explode( "||~&~||", $data );

	

	$crows = count($template_rows);

	

	if ( $crows < 100 )

	{

		install_error("Err2: (Found $crows rows) '$style_file' is incomplete, please re-upload a fresh copy over the existing copy on the server'");

	}

	

	//-----------------------------------

	// Delete current skins in #1

	//-----------------------------------

	

	$DB->query("DELETE FROM ibf_skin_templates WHERE set_id=1");

	

	//-----------------------------------

	// Lets populate the database!

	//-----------------------------------

	

	foreach( $template_rows as $q )

	{

		$DB->error = "";

		

		$q = trim($q);

		

		//print $q;

	   

		if (strlen($q) < 5)

		{

			continue;

		}

		

		$DB->query("INSERT INTO ibf_skin_templates (set_id, group_name, section_content, func_name, func_data, updated, can_remove) VALUES $q");

			

		if ( $DB->error != "" )

		{

			install_error( "mySQL Error: ".$DB->error );

		}

	}

   

    //---------------------------------------

	

	$template->print_top('Step 5 Complete');

	

	$template->contents .= "<div class='centerbox'>

							 <div class='tableborder'>

							  <div class='maintitle'>Upgrade from v1.1.X to v1.2</div>

							  <div class='pformstrip'>The templates have been inserted</div>

							  <div class='tablepad'>

							   You may now proceed to the next step.

							   <br /><br />

							   <b><a href='upgrade5.php?a=cleanup'>Proceed to Step 6: Finishing Up</a> &gt;&gt;</b>

							  </div>

							 </div>

							 <div class='fade'>&nbsp;</div>

							</div>";

	$template->output();

}





//+---------------------------------------







function do_insert()

{

	global $std, $template, $root, $DB;

	

	run_sql('insert');

	

	$template->print_top('Step 4 Complete');

	

	$template->contents .= "<div class='centerbox'>

							 <div class='tableborder'>

							  <div class='maintitle'>Upgrade from v1.1.X to v1.2</div>

							  <div class='pformstrip'>The new data has been inserted</div>

							  <div class='tablepad'>

							   You may now proceed to the next step.

							   <br /><br />

							   <b><a href='upgrade5.php?a=templates'>Proceed to Step 5: Insert New Templates</a> &gt;&gt;</b>

							  </div>

							 </div>

							 <div class='fade'>&nbsp;</div>

							</div>";

	

	$template->output();

}





//+---------------------------------------





function do_index()

{

	global $std, $template, $root, $DB;

	

	run_sql('index');

	

	$template->print_top('Step 3 Complete');

	

	$template->contents .= "<div class='centerbox'>

							 <div class='tableborder'>

							  <div class='maintitle'>Upgrade from v1.1.X to v1.2</div>

							  <div class='pformstrip'>The indexes have been adjusted</div>

							  <div class='tablepad'>

							   You may now proceed to the next step.

							   <br /><br />

							   <b><a href='upgrade5.php?a=insert'>Proceed to Step 4: Insert New Data</a> &gt;&gt;</b>

							  </div>

							 </div>

							 <div class='fade'>&nbsp;</div>

							</div>";	

	$template->output();

}





//+---------------------------------------



function do_alter()

{

	global $std, $template, $root, $DB;

	

	run_sql('alter');

	

	$template->print_top('Step 2 Complete');

	

	$template->contents .= "<div class='centerbox'>

							 <div class='tableborder'>

							  <div class='maintitle'>Upgrade from v1.1.X to v1.2</div>

							  <div class='pformstrip'>The tables have been altered</div>

							  <div class='tablepad'>

							   You may now proceed to the next step.

							   <br /><br />

							   <b><a href='upgrade5.php?a=index'>Proceed to Step 3: Alter Indexes</a> &gt;&gt;</b>

							  </div>

							 </div>

							 <div class='fade'>&nbsp;</div>

							</div>";	

	$template->output();

}





//+---------------------------------------



function do_tables()

{

	global $std, $template, $root, $DB;

	

	run_sql('tables');

	

	$template->print_top('Step 1 Complete');

	

	$template->contents .= "<div class='centerbox'>

							 <div class='tableborder'>

							  <div class='maintitle'>Upgrade from v1.1.X to v1.2</div>

							  <div class='pformstrip'>The new tables have been created</div>

							  <div class='tablepad'>

							   You may now proceed to the next step.

							   <br /><br />

							   <b><a href='upgrade5.php?a=alter'>Proceed to Step 2: Alter Existing Tables</a> &gt;&gt;</b>

							  </div>

							 </div>

							 <div class='fade'>&nbsp;</div>

							</div>";

						 	

	$template->output();

}





//+---------------------------------------



function do_intro()

{

	global $std, $template, $root;

	

	$template->print_top('Welcome');

	

	$template->contents .= "<table width='80%' border='0' cellpadding='0' cellspacing='0' align='center'>

							<tr>

							 <td valign='top'><img src='html/sys-img/install_face.jpg' border='0' alt='Intro'></td>

							 <td><img src='html/sys-img/install_text.gif' border='0' alt='Welcome to IPB'>

							  <br /><br />

							   <b>Welcome to the Invision Board Upgrade Utility</b>

							   <br><br>

							   Before you run this upgrade tool, please ensure that ALL of the source, language and skin files

							   have been uploaded into the corresponding directories on your server.

							   <br><br>

							   This script should be in the root forum directory (the same directory that index.php is in).

							   <br><br>

							   This installer will alter the database for the new format and install the skin and macro files.

							   <br><br>

							   Please ensure that 'install_templates.txt' is uploaded and in the same directory as this script.

							   <br><br>

							   Also, please ensure that all the new skin_*.php files have been uploaded into 'Skin/s1' before starting

							   this upgrader. If you wish to keep the old skin data in that directory, please copy the directory now before

							   proceeding. Running this installer will set all existing skins to 'hidden' and install a new default skin pack

							   ";

						 

	$style_file = $root."install_templates.txt";

	

	$warnings = array();

	

	if ( ! file_exists($style_file) )

	{

		$warnings[] = "Cannot locate the file 'install_templates.txt'. This should be uploaded into the same directory as this script!";

	}

						 

	if ( count($warnings) > 0 )

	{

	

		$err_string = "<ul><li>".implode( "<li>", $warnings )."</ul>";

	

		$template->contents .= "<br /><br />

							    <div class='warnbox'>

							     <strong>Warning!</strong>

							     <b>The following errors must be rectified before continuing!</b>

								 <br><br>

								 $err_string

							    </div>";

	}

	else

	{

		$template->contents .= "<br /><br /><div align='center'><b><a href='upgrade5.php?a=tables'>Proceed to Step 1: Create New Tables</a> &gt;&gt;</b></div>";

	}

	

	$template->output();

}







function install_error($msg="")

{

	global $std, $template, $root;

	

	$template->print_top('Warning!');

	



	

	$template->contents .= "<tr>

						  <td id='warning'>&#149;&nbsp;WARNING!</td>

						<tr>

						<td>

						  <table cellpadding='8' cellspacing='0' width='100%' align='center' border='0' id='tablewrap'>

						  <tr>

							<td>

						  <table width='100%' cellspacing='1' cellpadding='0' align='center' border='0' id='table1'>

						   <tr>

							<td>

							 <table width='100%' cellspacing='2' cellpadding='3' align='center' border='0'>

							 <tr>

							   <td>

									<b>The following errors must be rectified before continuing!</b><br>Please go back and try again!

									<br><br>

									$msg

								</td>

							 </tr>

							</table>

						  </td>

						 </tr>

						</table>

					   </td>

					  </tr>

					 </table>";

	

	

	

	$template->output();

}









//+-------------------------------------------------

// GLOBAL ROUTINES

//+-------------------------------------------------



function fatal_error($message="", $help="") {

	echo("$message<br><br>$help");

	exit;

}





//+--------------------------------------------------------------------------

// CLASSES

//+--------------------------------------------------------------------------







class template

{

	var $contents = "";

	

	function output()

	{

		echo $this->contents;

		echo "   

				 </table>

				 <br><br><center><span id='copy'>&copy 2002 Invision Board (www.invisionboard.com)</span></center>

				 

				 </body>

				 </html>";

		exit();

	}

	

	//--------------------------------------



	function print_top($title="")

	{

	

		$this->contents = "<html>

		          <head><title>Invision Power Board Set Up :: $title </title>

		          <style type='text/css'>

		          	

		          	BODY		          	

		          	{

		          		font-size: 11px;

		          		font-family: Verdana, Arial;

		          		color: #000;

		          		margin: 0px;

		          		padding: 0px;

		          		background-image: url(html/sys-img/fadebg.jpg);

		          		background-repeat: no-repeat;

		          		background-position: right bottom;

		          	}

		          	

		          	TABLE, TR, TD     { font-family:Verdana, Arial;font-size: 11px; color:#000 }

					

					a:link, a:visited, a:active  { color:#000055 }

					a:hover                      { color:#333377;text-decoration:underline }

					

					.centerbox { margin-right:10%;margin-left:10%;text-align:left }

					

					.warnbox {

							   border:1px solid #F00;

							   background: #FFE0E0;

							   padding:6px;

							   margin-right:10%;margin-left:10%;text-align:left;

							 }

					

					.tablepad    { background-color:#F5F9FD;padding:6px }



					.pformstrip { background-color: #D1DCEB; color:#3A4F6C;font-weight:bold;padding:7px;margin-top:1px;text-align:left }

					.pformleftw { background-color: #F5F9FD; padding:6px; margin-top:1px;width:40%; border-top:1px solid #C2CFDF; border-right:1px solid #C2CFDF; }

					.pformright { background-color: #F5F9FD; padding:6px; margin-top:1px;border-top:1px solid #C2CFDF; }



					.tableborder { border:1px solid #345487;background-color:#FFF; padding:0px; margin:0px; width:100% }



					.maintitle { text-align:left;vertical-align:middle;font-weight:bold; color:#FFF; letter-spacing:1px; padding:8px 0px 8px 5px; background-image: url(html/sys-img/tile_back.gif) }

					.maintitle a:link, .maintitle  a:visited, .maintitle  a:active { text-decoration: none; color: #FFF }

					.maintitle a:hover { text-decoration: underline }

					

					#copy { font-size:10px }

										

					#button   { background-color: #4C77B6; color: #FFFFFF; font-family:Verdana, Arial; font-size:11px }

					

					#textinput { background-color: #EEEEEE; color: #000000; font-family:Verdana, Arial; font-size:10px; width:100% }

					

					#dropdown { background-color: #EEEEEE; color: #000000; font-family:Verdana, Arial; font-size:10px }

					

					#multitext { background-color: #EEEEEE; color: #000000; font-family:Courier, Verdana, Arial; font-size:10px }

					

					#logostrip {

								 padding: 0px;

								 margin: 0px;

								 background: #7AA3D0;

							   }

							   

					.fade					

					{

						background-image: url(html/sys-img/fade.jpg);

						background-repeat: repeat-x;

					}

					

				  </style>

				  </head>

				 <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#FFFFFF'>

				 

				 <div id='logostrip'><img src='html/sys-img/title.gif' border='0' alt='Invision Power Board Installer' /></div>

				 <div class='fade'>&nbsp;</div>

				 <br />

				 ";

				  	   

	}





}





class installer

{



	function parse_incoming()

    {

    	global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_CLIENT_IP, $REQUEST_METHOD, $REMOTE_ADDR, $HTTP_PROXY_USER, $HTTP_X_FORWARDED_FOR;

    	$return = array();

    	

		if( is_array($HTTP_GET_VARS) )

		{

			while( list($k, $v) = each($HTTP_GET_VARS) )

			{

				//$k = $this->clean_key($k);

				if( is_array($HTTP_GET_VARS[$k]) )

				{

					while( list($k2, $v2) = each($HTTP_GET_VARS[$k]) )

					{

						$return[$k][ $this->clean_key($k2) ] = $this->clean_value($v2);

					}

				}

				else

				{

					$return[$k] = $this->clean_value($v);

				}

			}

		}

		

		// Overwrite GET data with post data

		

		if( is_array($HTTP_POST_VARS) )

		{

			while( list($k, $v) = each($HTTP_POST_VARS) )

			{

				//$k = $this->clean_key($k);

				if ( is_array($HTTP_POST_VARS[$k]) )

				{

					while( list($k2, $v2) = each($HTTP_POST_VARS[$k]) )

					{

						$return[$k][ $this->clean_key($k2) ] = $this->clean_value($v2);

					}

				}

				else

				{

					$return[$k] = $this->clean_value($v);

				}

			}

		}

		

		return $return;

	}

    

    function clean_key($key) {

    

    	if ($key == "")

    	{

    		return "";

    	}

    	

    	$key = preg_replace( "/\.\./"           , ""  , $key );

    	$key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );

    	$key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );

    	return $key;

    }

    

    function clean_value($val) {

    

    	if ($val == "")

    	{

    		return "";

    	}

    	

    	$val = preg_replace( "/&/"         , "&amp;"         , $val );

    	$val = preg_replace( "/<!--/"      , "&#60;&#33;--"  , $val );

    	$val = preg_replace( "/-->/"       , "--&#62;"       , $val );

    	$val = preg_replace( "/<script/i"  , "&#60;script"   , $val );

    	$val = preg_replace( "/>/"         , "&gt;"          , $val );

    	$val = preg_replace( "/</"         , "&lt;"          , $val );

    	$val = preg_replace( "/\"/"        , "&quot;"        , $val );

    	$val = preg_replace( "/\|/"        , "&#124;"        , $val );

    	$val = preg_replace( "/\n/"        , "<br>"          , $val ); // Convert literal newlines

    	$val = preg_replace( "/\\\$/"      , "&#036;"        , $val );

    	$val = preg_replace( "/\r/"        , ""              , $val ); // Remove literal carriage returns

    	$val = preg_replace( "/!/"         , "&#33;"         , $val );

    	$val = preg_replace( "/'/"         , "&#39;"         , $val ); // IMPORTANT: It helps to increase sql query safety.

    	$val = stripslashes($val);                                     // Swop PHP added backslashes

    	$val = preg_replace( "/\\\/"       , "&#092;"        , $val ); // Swop user inputted backslashes

    	return $val;

    }

   

}



// Sql stuff





function run_sql($type)

{

	global $std, $template, $root, $DB;

	

	$DB->error = "";

	

	if ($type == 'tables')

	{

		$SQL = sql_tables();

	}

	else if ($type == 'alter')

	{

		$SQL = sql_alter();

	}

	else if ($type == 'index')

	{

		$SQL = sql_index();

	}

	else if ($type == 'insert')

	{

		$SQL = sql_insert();

	}

	else if ($type == 'optimize')

	{

		$SQL = sql_optimize();

	}

	

	//--------------------------------

		

	foreach( $SQL as $q )

	{

		$DB->query($q);

		

		if ( $DB->error != "" )

		{

			install_error($DB->error);

		}

	}

	

	return TRUE;

}





function sql_optimize()

{

	$SQL = array();

	

	$SQL[] = "alter table ibf_calendar_events change eventid eventid mediumint(8) NOT NULL auto_increment;";

	$SQL[] = "alter table ibf_calendar_events change userid userid mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_contacts change id id mediumint(8) NOT NULL auto_increment;";

	$SQL[] = "alter table ibf_contacts change contact_id contact_id mediumint(8) NOT NULL;";

	$SQL[] = "alter table ibf_contacts change member_id member_id mediumint(8) NOT NULL;";

	$SQL[] = "alter table ibf_faq change id id mediumint(8) NOT NULL auto_increment;";

	$SQL[] = "alter table ibf_forum_tracker change frid frid mediumint(8) NOT NULL auto_increment;";

	$SQL[] = "alter table ibf_forum_tracker change forum_id forum_id smallint(5) NOT NULL default '0';";

	$SQL[] = "alter table ibf_forums change last_poster_id last_poster_id mediumint(8) NOT NULL DEFAULT '0';";

	$SQL[] = "alter table ibf_languages change lid lid mediumint(8) NOT NULL auto_increment;";

	$SQL[] = "alter table ibf_member_extra change id id mediumint(8) NOT NULL;";

	$SQL[] = "alter table ibf_members change id id mediumint(8) NOT NULL;";

	$SQL[] = "alter table ibf_members change mgroup mgroup smallint(3) NOT NULL;";

	$SQL[] = "alter table ibf_members change msg_from_id msg_from_id mediumint(8);";

	$SQL[] = "alter table ibf_messages change msg_id msg_id int(10) NOT NULL auto_increment;";

	$SQL[] = "alter table ibf_messages change from_id from_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_messages change member_id member_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_messages change recipient_id recipient_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_moderator_logs change id id int(10) NOT NULL auto_increment;";

	$SQL[] = "alter table ibf_moderator_logs change topic_id topic_id int(10) NOT NULL default '0';";

	$SQL[] = "alter table ibf_moderator_logs change post_id post_id int(10);";

	$SQL[] = "alter table ibf_moderator_logs change member_id member_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_moderator_logs change ip_address ip_address VARCHAR(16) NOT NULL default '0';";

	$SQL[] = "alter table ibf_moderators change mid mid mediumint(8) NOT NULL auto_increment;";

	$SQL[] = "alter table ibf_moderators change member_id member_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_pfields_content change member_id member_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_polls change pid pid mediumint(8) NOT NULL auto_increment;";

	$SQL[] = "alter table ibf_polls change tid tid int(10) NOT NULL default '0';";

	$SQL[] = "alter table ibf_polls change starter_id starter_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_polls change votes votes smallint(5) NOT NULL default '0';";

	$SQL[] = "alter table ibf_polls change forum_id forum_id smallint(5) NOT NULL default '0';";

	$SQL[] = "alter table ibf_posts change pid pid int(10) NOT NULL default '0' auto_increment;";

	$SQL[] = "alter table ibf_posts change author_id author_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_posts change topic_id topic_id int(10) NOT NULL default '0';";

	$SQL[] = "alter table ibf_posts change forum_id forum_id smallint(5) NOT NULL default '0';";

	$SQL[] = "alter table ibf_posts change ip_address ip_address varchar(16) NOT NULL DEFAULT '';";

	$SQL[] = "alter table ibf_posts change use_sig use_sig tinyint(1) NOT NULL DEFAULT '0';";

	$SQL[] = "alter table ibf_posts change use_emo use_emo tinyint(1) NOT NULL DEFAULT '0';";

	$SQL[] = "alter table ibf_sessions change member_id member_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_sessions change in_forum in_forum smallint(5) NOT NULL default '0';";

	$SQL[] = "alter table ibf_stats change TOTAL_REPLIES TOTAL_REPLIES int(10) NOT NULL default '0';";

	$SQL[] = "alter table ibf_stats change TOTAL_TOPICS TOTAL_TOPICS int(10) NOT NULL default '0';";

	$SQL[] = "alter table ibf_stats change LAST_MEM_ID LAST_MEM_ID mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_stats change MEM_COUNT MEM_COUNT mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_topics change tid tid int(10) NOT NULL default '0' auto_increment;";

	$SQL[] = "alter table ibf_topics change starter_id starter_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_topics change last_poster_id last_poster_id mediumint(8) NOT NULL DEFAULT '0';";

	$SQL[] = "alter table ibf_tracker change trid trid mediumint(8) NOT NULL auto_increment;";

	$SQL[] = "alter table ibf_tracker change member_id member_id mediumint(8) NOT NULL default '0';";

	$SQL[] = "alter table ibf_voters change vid vid int(10) NOT NULL default '0' auto_increment;";

	$SQL[] = "alter table ibf_voters change tid tid int(10) NOT NULL default '0';";

	$SQL[] = "alter table ibf_voters change forum_id forum_id smallint(5) NOT NULL default '0';";





	return $SQL;

}





function sql_tables()

{

	$SQL = array();

	

$SQL[]="CREATE TABLE ibf_forum_perms(

	perm_id int(10) auto_increment NOT NULL,

	perm_name varchar(250) NOT NULL,

	PRIMARY KEY(perm_id)

);";



$SQL[]="CREATE TABLE ibf_validating (

vid varchar(32) NOT NULL,

member_id mediumint(8) NOT NULL,

real_group smallint(3) NOT NULL default '0',

temp_group smallint(3) NOT NULL default '0',

entry_date int(10) NOT NULL default '0',

coppa_user tinyint(1) NOT NULL default '0',

lost_pass tinyint(1) NOT NULL default '0',

new_reg tinyint(1) NOT NULL default '0',

email_chg tinyint(1) NOT NULL default '0',

ip_address varchar(16) NOT NULL default '0',

PRIMARY KEY(vid),

KEY new_reg(new_reg)

);";



$SQL[]="create table ibf_cache_store (

  cs_key varchar(255) NOT NULL default '',

  cs_value text NOT NULL default '',

  cs_extra varchar(255) NOT NULL default '',

  PRIMARY KEY(cs_key)

);";



$SQL[]="create table ibf_email_logs (

email_id int(10) NOT NULL auto_increment,

email_subject varchar(255) NOT NULL,

email_content text NOT NULL,

email_date int(10) NOT NULL default '0',

from_member_id mediumint(8) NOT NULL default '0',

from_email_address varchar(250) NOT NULL,

from_ip_address varchar(16) NOT NULL default '127.0.0.1',

to_member_id mediumint(8) NOT NULL default '0',

to_email_address varchar(250) NOT NULL,

topic_id int(10) NOT NULL default '0',

PRIMARY KEY(email_id),

KEY from_member_id(from_member_id),

KEY email_date(email_date)

);";



$SQL[]="CREATE TABLE ibf_topic_mmod (

mm_id smallint(5) NOT NULL auto_increment,

mm_title varchar(250) NOT NULL,

mm_enabled tinyint(1) NOT NULL default '0',

topic_state varchar(10) NOT NULL default 'leave',

topic_pin varchar(10) NOT NULL default 'leave',

topic_move smallint(5) NOT NULL default '0',

topic_move_link tinyint(1) NOT NULL default '0',

topic_title_st varchar(250) NOT NULL default '',

topic_title_end varchar(250) NOT NULL default '',

topic_reply tinyint(1) NOT NULL default '0',

topic_reply_content text NOT NULL default '',

topic_reply_postcount tinyint(1) NOT NULL default '0',

PRIMARY KEY(mm_id)

);";



$SQL[]="create table ibf_spider_logs (

sid int(10) NOT NULL auto_increment,

bot varchar(255) NOT NULL default '',

query_string text NOT NULL default '',

entry_date int(10) NOT NULL default '0',

ip_address varchar(16) NOT NULL default '',

PRIMARY KEY(sid)

);";







$SQL[]="CREATE TABLE ibf_warn_logs (

wlog_id int(10) auto_increment NOT NULL,

wlog_mid mediumint(8) NOT NULL default '0',

wlog_notes text NOT NULL default '',

wlog_contact varchar(250) NOT NULL default 'none',

wlog_contact_content text NOT NULL default '',

wlog_date int(10) NOT NULL default '',

wlog_type varchar(6) NOT NULL default 'pos',

wlog_addedby mediumint(8) NOT NULL default '0',

PRIMARY KEY(wlog_id)

);";





	return $SQL;

}



function sql_alter()

{

	$SQL = array();

	

	

	$SQL[] = "ALTER TABLE ibf_members ADD org_supmod TINYINT(1) DEFAULT '0';";

	$SQL[] = "ALTER TABLE ibf_members ADD org_perm_id varchar(255) DEFAULT '';";

	$SQL[] = "alter table ibf_members drop validate_key;";

	$SQL[] = "alter table ibf_members drop new_pass;";

	$SQL[] = "alter table ibf_members drop prev_group;";

	$SQL[] = "alter table ibf_members add temp_ban varchar(100);";

	$SQL[] = "alter table ibf_members change allow_post restrict_post varchar(100) NOT NULL default '0';";

	$SQL[] = "alter table ibf_members change mod_posts mod_posts varchar(100) NOT NULL default '0';";

	$SQL[] = "alter table ibf_members add warn_lastwarn int(10) not null default '0' after warn_level;";



	$SQL[] = "ALTER TABLE ibf_groups ADD g_perm_id varchar(255) NOT NULL;";

	$SQL[] = "ALTER TABLE ibf_groups ADD g_photo_max_vars VARCHAR(200) DEFAULT '100:250:250';";

	$SQL[] = "alter table ibf_groups add g_dohtml tinyint(1) NOT NULL default '0';";

	$SQL[] = "alter table ibf_groups add g_edit_topic tinyint(1) NOT NULL DEFAULT '0';";

	$SQL[] = "alter table ibf_groups add g_email_limit varchar(15) NOT NULL DEFAULT '10:15';";



	$SQL[] = "alter table ibf_member_extra add photo_type varchar(10) default '';";

	$SQL[] = "alter table ibf_member_extra add photo_location varchar(255) default '';";

	$SQL[] = "alter table ibf_member_extra add photo_dimensions varchar(200) default '';";



	$SQL[] = "alter table ibf_calendar_events add end_day int(2), add end_month int(2), add end_year int(4);";

	$SQL[] = "alter table ibf_calendar_events add end_unix_stamp int(10);";

	$SQL[] = "alter table ibf_calendar_events add event_ranged tinyint(1) NOT NULL default '0';";

	$SQL[] = "alter table ibf_calendar_events add event_repeat tinyint(1) NOT NULL default '0';";

	$SQL[] = "alter table ibf_calendar_events add repeat_unit char(2) NOT NULL default '';";

	$SQL[] = "alter table ibf_calendar_events add event_bgcolor varchar(32) NOT NULL default '';";

	$SQL[] = "alter table ibf_calendar_events add event_color varchar(32) NOT NULL default '';";



	$SQL[] = "alter table ibf_skins add css_method varchar(100) default 'inline';";



	$SQL[] = "alter table ibf_css add updated int(10) default '0';";



	$SQL[] = "alter table ibf_members add integ_msg varchar(250) default '';";



	$SQL[] = "alter table ibf_search_results add query_cache text;";



	$SQL[] = "ALTER TABLE ibf_forums ADD quick_reply TINYINT(1) DEFAULT '0';";

	$SQL[] = "alter table ibf_forums add redirect_url VARCHAR(250) default '';";

	$SQL[] = "alter table ibf_forums add redirect_on	TINYINT(1) default '0' NOT NULL;";

	$SQL[] = "alter table ibf_forums add redirect_hits int(10) default '0' NOT NULL;";

	$SQL[] = "alter table ibf_forums add redirect_loc VARCHAR(250) default '';";

	$SQL[] = "alter table ibf_forums add rules_title varchar(255) NOT NULL default '', add rules_text text NOT NULL default '';";

	$SQL[] = "alter table ibf_forums add has_mod_posts tinyint(1) NOT NULL DEFAULT '0';";



	$SQL[] = "alter table ibf_forums add topic_mm_id varchar(250) NOT NULL default '';";

	$SQL[] = "alter table ibf_moderators add can_mm tinyint(1) NOT NULL default '0';";



	$SQL[] = "alter table ibf_topics change posts posts int(10) default null, change views views int(10) default '0';";







	



	return $SQL;

}



function sql_index()

{

	$SQL = array();

	

	$SQL[] = "ALTER TABLE ibf_topics DROP INDEX forum_id;";

	$SQL[] = "ALTER TABLE ibf_topics ADD INDEX forum_id(forum_id,approved,pinned);";

	$SQL[] = "ALTER TABLE ibf_topics ADD INDEX(last_post);";





	return $SQL;

}



function sql_insert()

{

	$SQL = array();

	

$SQL[] = "REPLACE INTO ibf_faq VALUES (1, 'Registration benefits', 'To be able to use all the features on this board, the administrator will probably require that you register for a member account. Registration is free and only takes a moment to complete.\r<br>\r<br>During registration, the administrator requires that you supply a valid email address. This is important as the administrator may require that you validate your registration via an email. If this is the case, you will be notified when registering. If your e-mail does not arrive, then on the member bar at the top of the page, there will be a link that will allow you to re-send the validation e-mail. \r<br>\r<br>In some cases, the administrator will need to approve your registration before you can use your member account fully. If this is the case you will be notified during registration.\r<br>\r<br>Once you have registered and logged in, you will have access to your personal messenger and your control panel.\r<br>\r<br>For more information on these items, please see the relevant sections in this documentation.', 'How to register and the added benefits of being a registered member.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (2, 'Cookies and cookie usage', 'Using cookies is optional, but strongly recommended. Cookies are used to track topics, showing you which topics have new replies since your last visit and to automatically log you in when you return.\r<br>\r<br>If your computer is unable to use the cookie system to browse the board correctly, then the board will automatically add in a session ID to each link to track you around the board.\r<br>\r<br><b>Clearing Cookies</b>\r<br>\r<br>You can clear the cookies at any time by clicking on the link found at the bottom of the main board page (the first page you see when returning to the board). If this does not work for you, you may need to remove the cookies manually.\r<br>\r<br><u>Removing Cookies in Internet Explorer for Windows</u>\r<br>\r<br><ul>\r<br><li> Close all open Internet Explorer Windows\r<br><li> Click on the \'start\' button\r<br><li> Move up to \'Find\' and click on \'Files and Folders\'\r<br><li> When the new window appears, type in the domain name of the board you are using into the \'containing text\' field. (If the boards address was \'http://www.invisionboard.com/forums/index.php\' you would enter \'invisionboard.com\' without the quotes)\r<br><li> In the \'look in\' box, type in <b>C:WindowsCookies</b> and press \'Find Now\'\r<br><li> After it has finished searching, highlight all files (click on a file then press CTRL+A) and delete them.\r<br></ul>\r<br>\r<br><u>Removing Cookies in Internet Explorer for Macintosh</u>\r<br>\r<br><ul>\r<br><li> With Internet Explorer active, choose \'Edit\' and then \'Preferences\' from the Macintosh menu bar at the top of the screen\r<br><li> When the preferences panel opens, choose \'Cookies\' found in the \'Receiving Files\' section.\r<br><li> When the cookie pane loads, look for the domain name of the board (If the boards address was \'http://www.invisionboard.com/forums/index.php\' look for \'invisionboard.com\' or \'www.invisionboard.com\'\r<br><li> For each cookie, click on the entry and press the delete button.\r<br></ul>\r<br>\r<br>Your cookies should now be removed. In some cases you may need to restart your computer for the changes to take effect.', 'The benefits of using cookies and how to remove cookies set by this board.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (3, 'Recovering lost or forgotten passwords', 'Security is a big feature on this board, and to that end, all passwords are encrypted when you register.\r<br>This means that we cannot email your password to you as we hold no record of your \'uncrypted\' password. You can however, apply to have your password reset.\r<br>\r<br>To do this, click on the <a href=\'index.php?act=Reg&CODE=10\'>Lost Password link</a> found on the log in page.\r<br>\r<br>Further instruction is available from there.', 'How to reset your password if you\'ve forgotton it.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (4, 'Your Control Panel (My Controls)', 'Your control panel is your own private board console. You can change how the board looks and feels as well as your own information from here.\r<br>\r<br><b>Subscriptions</b>\r<br>\r<br>This is where you manage your topic and forums subscriptions. Please see the help file \'Email Notification of new messages\' for more information on how to subscribe to topics.\r<br>\r<br><b>Edit Profile Info</b>\r<br>\r<br>This section allows you to add or edit your contact information and enter some personal information if you choose.\r<br>\r<br><b>Edit Signature</b>\r<br>\r<br>A board \'signature\' is very similar to an email signature. This signature is attached to the foot of every message you post unless you choose to check the box that allows you to ommit the signature in the message you are posting. You may use BB Code if available and in some cases, pure HTML (if the board administrator allows it).\r<br>\r<br><b>Edit Avatar Settings</b>\r<br>\r<br>An avatar is a little image that appears under your username when you view a topic or post you authored. If the administrator allows, you may either choose from the board gallery, enter a URL to an avatar stored on your server or upload an avatar to use. You may also set the width of the avatar to ensure that it\'s sized in proportion.\r<br>\r<br><b>Change Personal Photo</b>\r<br>\r<br>This section will allow you to add a photograph to your profile. This will be displayed when a user clicks to view your profile, on the mini-profile screen and will also be linked to from the member list.\r<br>\r<br><b>Email Settings</b>\r<br>\r<br><u>Hide my email address</u> allows you to deny the ability for other users to send you an email from the board.\r<br><u>Send me updates sent by the board administrator</u> will allow the administrator to include your email address in any mailings they send out - this is used mostly for important updates and community information.\r<br><u>Include a copy of the post when emailing me from a subscribed topic</u>, this allows you to have the new post included in any reply to topic notifications.\r<br><u>Send a confirmation email when I receive a new private message</u>, this will send you an e-mail notification to your registered e-mail address each time you receive a private message on the board.\r<br><u>Enable \'Email Notification\' by default?</u>, this will automatically subscribe you to any topic that you make a reply to. You may unsubscribe from the \'Subscriptions\' section of My Controls if you wish.\r<br>\r<br><b>Board Settings</b>\r<br>\r<br>From this section, you can set your time zone, choose to not see users signatures, avatars and posted images.\r<br>You can choose to get a pop up window informing you when you have a new message and choose to show or hide the \'Fast Reply\' box where it is enabled.\r<br>You are also able to choose display preferences for the number of topics/posts shown per page on the board.\r<br>\r<br><b>Skins and Languages</b>\r<br>\r<br>If available, you can choose a skin style and language choice. This affects how the board is displayed so you may wish to preview the skin before submitting the form.\r<br>\r<br><b>Change Email Address</b>\r<br>\r<br>At any time, you can change the email address that is registered to your account. In some cases, you will need to revalidate your account after changing your email address. If this is the case, you will be notified before your email address change is processed.\r<br>\r<br><b>Change Password</b>\r<br>\r<br>You may change your password from this section. Please note that you will need to know your current password before you can change your password.', 'Editing contact information, personal information, avatars, signatures, board settings, languages and style choices.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (5, 'Email Notification of new messages', 'This board can notify you when a new reply is added to a topic. Many users find this useful to keep up to date on topics without the need to view the board to check for new messages.\r<br>\r<br>There are three ways to subscribe to a topic:\r<br>\r<br><li>Click the \'Track This Topic\' link at the top of the topic that you wish to track\r<br><li> On the posting screen when replying to or creating a topic, check the \'Enable email notification of replies?\' checkbox\r<br><li> From the E-Mail settings section of your User CP (My Controls) check the \'Enable Email Notification by default?\' option, this will automatically subscribe you to any topic that you make a reply to\r<br>\r<br>Please note that to avoid multiple emails being sent to your email address, you will only get one e-mail for each topic you are subscribed to until the next time you visit the board.\r<br>\r<br>You are also able to subscribe to each individual forum on the board, to be notified when a new topic is created in that particular forum. To enable this, click the \'Subscribe to this forum\' link at the bottom of the forum that you wish to subscribe to.\r<br>\r<br>To unsubscribe from any forums or topics that you are subscribed to - just go to the \'Subscriptions\' section of \'My Controls\' and you can do this from there.', 'How to get emailed when a new reply is added to a topic.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (6, 'Your Personal Messenger', 'Your personal messenger acts much like an email account in that you can send and receive messages and store messages in folders.\r<br>\r<br><b>Send a new PM</b>\r<br>\r<br>This will allow you to send a message to another member. If you have names in your contact list, you can choose a name from it - or you may choose to enter a name in the relevant form field. This will be automatically filled in if you clicked a \'PM\' button on the board (from the member list or a post). If allowed, you may also be able to enter in multiple names in the box provided, will need to add one username per line.\r<br>If the administrator allows, you may use BB Code and HTML in your private message. If you choose to check the \'Add a copy of this message to you sent items folder\' box, a copy of the message will be saved for you for later reference. If you tick the \'Track this message?\' box, then the details of the message will be available in your \'Message Tracker\' where you will be able to see if/when it has been read.\r<br>\r<br><b>Go to Inbox</b>\r<br>\r<br>Your inbox is where all new messages are sent to. Clicking on the message title will show you the message in a similar format to the board topic view. You can also delete or move messages from your inbox.\r<br>\r<br><b>Empty PM Folders</b>\r<br>\r<br>This option provides you with a quick and easy way to clear out all of your PM folders.\r<br>\r<br><b>Edit Storage Folders</b>\r<br>\r<br>You may rename, add or remove folders to store messages is, allowing you to organise your messages to your preference. You cannot remove \'Sent Items\' or \'Inbox\'.\r<br>\r<br><b>PM Buddies/Block List</b>\r<br>\r<br>You may add in users names in this section, or edit any saved entries. You can also use this as a ban list, denying the named member the ability to message you.\r<br>Names entered in this section will appear in the drop down list when sending a new PM, allowing you to quickly choose the members name when sending a message.\r<br>\r<br><b>Archive Messages</b>\r<br>\r<br>If your messenger folders are full and you are unable to receive new messages, you can archive them off. This compiles the messages into a single HTML page or Microsoft © Excel Format. This page is then emailed to your registered email address for your convenience.\r<br>\r<br><b>Saved (Unsent) PMs</b>\r<br>\r<br>This area will allow you to go back to any PM\'s that you have chosen to save to be sent later.\r<br>\r<br><b>Message Tracker</B>\r<br>\r<br>This is the page that any messages that you have chosen to track will appear. Details of if and when they have been read by the recipient will appear here. This also gives you the chance to delete any messages that you have sent and not yet been read by the intended recipient.', 'How to send personal messages, track them, edit your messenger folders and archive stored messages.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (7, 'Contacting the moderating team & reporting posts', '<b>Contacting the moderating team</b>\r<br>\r<br>If you need to contact a moderator or simply wish to view the complete administration team, you can click the link \'The moderating team\' found at the bottom of the main board page (the first page you see when visiting the board), or from \'My Assistant\'.\r<br>\r<br>This list will show you administrators (those who have administration control panel access), global moderators (those who can moderate in all forums) and the moderators of the individual forums.\r<br>\r<br>If you wish to contact someone about your member account, then contact an administrator - if you wish to contact someone about a post or topic, contact either a global moderator or the forum moderator.\r<br>\r<br><b>Reporting a post</b>\r<br>\r<br>If the administrator has enabled this function on the board, you\'ll see a \'Report\' button in a post, next to the \'Quote\' button. This function will let you report the post to the forum moderator (or the administrator(s), if there isn\'t a specific moderator available). You can use this function when you think the moderator(s) should be aware of the existance of that post. However, <b>do not use this to chat with the moderator(s)!</b>. You can use the email function or the Personal Messenger function for that.', 'Where to find a list of the board moderators and administrators.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (8, 'Viewing members profile information', 'You can view a members profile at any time by clicking on their name when it is underlined (as a link) or by clicking on their name in a post within a topic.\r<br>\r<br>This will show you their profile page which contains their contact information (if they have entered some) and their \'active stats\'.\r<br>\r<br>You can also click on the \'Mini Profile\' button underneath their posts, this will show up a mini \'e-card\' with their contact information and a photograph if they have chosen to have one.', 'How to view members contact information.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (9, 'Viewing active topics and new posts', 'You can view which new topics have new replies today by clicking on the \'Today\'s Active Topics\' link found at the bottom of the main board page (the first page you see when visiting the board). You can set your own date criteria, choosing to view all topics  with new replies during several date choices.\r<br>\r<br>The \'View New Posts\' link in the member bar at the top of each page, will allow you to view all of the topics which have new replies in since your last visit to the board.', 'How to view all the topics which have a new reply today and the new posts made since your last visit.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (10, 'Searching Topics and Posts', 'The search feature is designed to allow you to quickly find topics and posts that contain the keywords you enter.\r<br>\r<br>There are two types of search form available, simple search and advanced search. You may switch between the two using the \'More Options\' and \'Simple Mode\' buttons.\r<br>\r<br><b>Simple Mode</b>\r<br>\r<br>All you need to do here is enter in a keyword into the search box, and select a forum(s) to search in. (to select multiple forums, hold down the control key on a PC, or the Shift/Apple key on a Mac) choose a sorting order and search.\r<br>\r<br><b>Advanced Mode</b>\r<br>\r<br>The advanced search screen, will give you a much greater range of options to choose from to refine your search. In addition to searching by keyword, you are able to search by a members username or a combination of both. You can also choose to refine your search by selecting a date range, and there are a number of sorting options available. There are also two ways of displaying the search results, can either show the post text in full or just show a link to the topic, can choose this using the radio buttons available.\r<br>\r<br>If the administrator has enabled it, you may have a minimum amount of time to wait between searches, this is known as search flood control.\r<br>\r<br>There are also search boxes available at the bottom of each forum, to allow you to carry out a quick search of all of the topics within that particular forum.', 'How to use the search feature.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (11, 'Logging in and out', 'If you have chosen not to remember your log in details in cookies, or you are accessing the board on another computer, you will need to log into the board to access your member profile and post with your registered name.\r<br>\r<br>When you log in, you have the choice to save cookies that will log you in automatically when you return. Do not use this option on a shared computer for security.\r<br>\r<br>You can also choose to hide - this will keep your name from appearing in the active users list.\r<br>\r<br>Logging out is simply a matter of clicking on the \'Log Out\' link that is displayed when you are logged in. If you find that you are not logged out, you may need to manually remove your cookies. See the \'Cookies\' help file for more information.', 'How to log in and out from the board and how to remain anonymous and not be shown on the active users list.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (13, 'My Assistant', 'This feature is sometimes referred to as a \'Browser Buddy\'. \r<br>\r<br>At the top it tells you how many posts have been made since you last visited the board.. Also underneath this the number of posts with replies that have been made in topics that the individual has also posted in.\r<br>Click on the \'View\' link on either of the two sentences to see the posts.\r<br>\r<br>The next section is five links to useful features:\r<br>\r<br><li>The link to the moderating team is basically a quick link to see all those that either administrate or moderate certain forums on the message board.\r<br><li> The link to \'Today\'s Active Topics\' shows you all the topics that have been created in the last 24 hours on the board.\r<br><li>Today\'s Top 10 Posters link shows you exactly as the name suggests. It shows you the amount of posts by the members and also their total percentage of the total posts made that day.\r<br><li>The overall Top 10 Posters link shows you the top 10 posters for the whole time that the board has been installed.\r<br><li>My last 10 posts links to the latest topics that you have made on the board. These are shortened on the page, to save space, and are linked to if you require to read more of them.\r<br>\r<br>The two search features allow you to search the whole board for certain words in a whole topic. It isn\'t as featured as the normal search option so it is not as comprehensive.\r<br>\r<br>The Help Search is just as comprehensive as the normal help section\'s search function and allows for quick searching of all the help topics on the board.', 'A comprehensive guide to use this handy little feature.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (12, 'Posting', 'There are three different posting screens available. The new topic button, visible in forums and in topics allows you to add a new topic to that particular forum. The new poll button (is the admin has enabled it) will also be viewable in topics and forums allowing you to create a new poll in the forum. When viewing a topic, there will be an add reply button, allowing you to add a new reply onto that particular topic. \r\n<br>\r\n<br><b>Posting new topics and replying</b>\r\n<br>\r\n<br>When making a post, you will most likely have the option to use IBF code when posting. This will allow you to add various types of formatting to your messages. For more information on this, click the \'BB Code Help\' link under the emoticon box to launch the help window.\r\n<br>\r\n<br>On the left of the text entry box, there is the clickable emoticons box - you can click on these to add them to the content of your message (these are sometimes known as \'smilies\').\r\n<br>\r\n<br>There are three options available when making a post or a reply. \'Enable emoticons?\' if this is unchecked, then any text that would normally be converted into an emoticon will not be. \'Enable signature?\' allows you to choose whether or not you would like your signature to appear on that individual post. \'Enable email notification of replies?\' ticking this box will mean that you will receive e-mail updates to the topic, see the \'Email Notification of new messages\' help topic for more information on this.\r\n<br>\r\n<br>You also have the option to choose a post icon for the topic/post when creating one. This icon will appear next to the topic name on the topic listing in that forum, or will appear next to the date/time of the message if making a reply to a topic.\r\n<br>\r\n<br>If the admin has enabled it, you will also see a file attachments option, this will allow you to attach a file to be uploaded when making a post. Click the browse button to select a file from your computer to be uploaded. If you upload an image file, it may be shown in the content of the post, all other file types will be linked to.\r\n<br>\r\n<br><b>Poll Options</b>\r\n<br>\r\n<br>If you have chosen to post a new poll, there will be an extra two option boxes at the top of the help screen. The first input box will allow you to enter the question that you are asking in the poll. The text field underneath is where you will input the choices for the poll. Simply enter a different option on each line. The maximum number of choices is set by the board admin, and this figure is displayed on the left.\r\n<br>\r\n<br><b>Quoting Posts</b>\r\n<br>\r\n<br>Displayed above each post in a topic, there is a \'Quote\' button. Pressing this button will allow you to reply to a topic, and have the text from a particular reply quoted in your own reply. When you choose to do this, an extra text field will appear below the main text input box to allow you to edit the content of the post being quoted.\r\n<br>\r\n<br><b>Editing Posts</b>\r\n<br>\r\n<br>Above any posts that you have made, you may see an \'Edit\' button. Pressing this will allow you to edit the post that you had previously made. \r\n<br>\r\n<br>When editing you may see an option to \'Add the \'Edit by\' line in this post?\'. If you tick this then it will show up in the posts that it has been edited and the time at which it was edited. If this option does not appear, then the edit by line will always be added to the post.\r\n<br>\r\n<br>If you are unable to see the edit button displayed on each post that you have made, then the administrator may have prevented you from editing posts, or the time limit for editing may have expired.\r\n<br>\r\n<br><b>Fast Reply</b>\r\n<br>\r\n<br>Where it has been enabled, there will be a fast reply button on each topic. Clicking this will open up a posting box on the topic view screen, cutting down on the time required to load the main posting screen. Click the fast reply button to expand the reply box and type the post inside of there. Although the fast reply box is not expanded by default, you can choose the option to have it expanded by default, from the board settings section of your control panel. Pressing the \'More Options\' button will take you to the normal posting screen.', 'A guide to the features avaliable when posting on the boards.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (14, 'Member List', 'The member list, accessed via the \'Members\' link at the top of each page, is basically a listing of all of the members that have registered on the board. \r\n<br>\r\n<br>If you are looking to search for a particular member by all/part of their username, then in the drop down box at the bottom of the page, change the selection from \'Search All Available\' to \'Name Begins With\' or \'Name Contains\' and input all/part of their name in the text input field and press the \'Go!\' button. \r\n<br>\r\n<br>Also, at the bottom of the member list page, there are a number of sorting options available to alter the way in which the list is displayed. \r\n<br>\r\n<br>If a member has chosen to add a photo to their profile information, then a camera icon will appear next to their name, and you may click this to view the photo.', 'Explaining the different ways to sort and search through the list of members.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (15, 'Topic Options', 'At the bottom of each topic, there is a \'Topic Options\' button. Pressing this button will expand the topic options box. \r\n<br>\r\n<br>From this box, you can select from the following options: \r\n<br>\r\n<br><li>Track this topic - this option will allow you to receive e-mail updates for the topic, see the \'Email Notification of new messages\' help file for more information on this \r\n<br><li>Subscribe to this forum - will allow you to receive e-mail updates for any new topics posted in the forum, see the Notification of new messages\' help file for more information on this \r\n<br><li>Download / Print this Topic - will show the topic in a number of different formats. \'Printer Friendly Version\' will display a version of the topic that is suitable for printing out. \'Download HTML Version\' will download a copy of the topic to your hard drive, and this can then be viewed in a web browser, without having to visit the board. \'Download Microsoft Word Version\' will allow you to download the file to your hard drive and open it up in the popular word processing application, Microsoft Word, for viewing offline.', 'A guide to the options avaliable when viewing a topic.');";

$SQL[] = "REPLACE INTO ibf_faq VALUES (16, 'Calendar', 'This board features it\'s very own calendar feature, which can be accessed via the calendar link at the top of the board.\r\n<br>\r\n<br>You are able to add your own personal events to the calendar - and these are only viewable by yourself. To add a new event, use the \'Add New Event\' button to be taken to the event posting screen. There are three types of events that you can now add:\r\n<br>\r\n<br><li>A single day/one off event can be added using the first option, by just selecting the date for it to appear on.\r\n<br><li>Ranged Event - is an event that spans across multiple days, to do this in addition to selecting the start date as above, will need to add the end date for the event. There are also options available  to highlight the message on the calendar, useful if there is more than one ranged event being displayed at any one time.\r\n<br><li>Recurring Event - is a one day event, that you can set to appear at set intervals on the calendar, either weekly, monthly or yearly.\r\n<br>\r\n<br>If the admistrator allows you, you may also be able to add a public event, that will not just be shown to yourself, but will be viewable by everyone.\r\n<br>\r\n<br>Also, if the admistrator has chosen,  there will be a link to all the birthdays happening on a particular day displayed on the calendar, and your birthday will appear if you have chosen to enter a date of birth in the Profile Info section of your control panel.', 'More information on the boards calendar feature.');";



$SQL[] = "REPLACE INTO ibf_macro VALUES (1, 'A_LOCKED_B', '<img src=\'style_images/<#IMG_DIR#>/t_closed.gif\' border=\'0\'  alt=\'Closed Topic\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (2, 'A_MOVED_B', '<img src=\'style_images/<#IMG_DIR#>/t_moved.gif\' border=\'0\'  alt=\'Moved Topic\'>', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (3, 'A_POLLONLY_B', '<img src=\'style_images/<#IMG_DIR#>/t_closed.gif\' border=\'0\'  alt=\'Poll Only\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (4, 'A_POST', '<img src=\'style_images/<#IMG_DIR#>/t_new.gif\' border=\'0\'  alt=\'Start new topic\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (5, 'A_REPLY', '<img src=\'style_images/<#IMG_DIR#>/t_reply.gif\' border=\'0\'  alt=\'Reply to this topic\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (6, 'A_POLL', '<img src=\'style_images/<#IMG_DIR#>/t_poll.gif\' border=\'0\'  alt=\'Start Poll\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (7, 'A_STAR', '<img src=\'style_images/<#IMG_DIR#>/pip.gif\' border=\'0\'  alt=\'*\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (8, 'B_HOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot.gif\' border=\'0\'  alt=\'Hot topic\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (9, 'B_HOT_NN', '<img src=\'style_images/<#IMG_DIR#>/f_hot_no.gif\' border=\'0\'  alt=\'No new\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (10, 'B_LOCKED', '<img src=\'style_images/<#IMG_DIR#>/f_closed.gif\' border=\'0\'  alt=\'Closed\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (11, 'B_MOVED', '<img src=\'style_images/<#IMG_DIR#>/f_moved.gif\' border=\'0\'  alt=\'Moved\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (12, 'B_NEW', '<img src=\'style_images/<#IMG_DIR#>/f_norm.gif\' border=\'0\'  alt=\'New Posts\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (13, 'B_NORM', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no.gif\' border=\'0\'  alt=\'No New Posts\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (14, 'B_PIN', '<img src=\'style_images/<#IMG_DIR#>/f_pinned.gif\' border=\'0\'  alt=\'Pinned\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (15, 'B_POLL', '<img src=\'style_images/<#IMG_DIR#>/f_poll.gif\' border=\'0\'  alt=\'Poll\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (16, 'B_POLL_NN', '<img src=\'style_images/<#IMG_DIR#>/f_poll_no.gif\' border=\'0\'  alt=\'No new votes\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (17, 'B_HOT_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot_dot.gif\' border=\'0\' alt=\'New Posts\'>', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (18, 'B_NEW_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_norm_dot.gif\' border=\'0\' alt=\'No New Posts\'>', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (19, 'B_HOT_NN_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot_no_dot.gif\' border=\'0\' alt=\'No New Posts*\'>', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (20, 'B_NORM_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no_dot.gif\' border=\'0\' alt=\'No New Posts*\'>', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (21, 'B_POLL_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_poll_dot.gif\' border=\'0\' alt=\'Poll*\'>', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (22, 'B_POLL_NN_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_poll_no_dot.gif\' border=\'0\' alt=\'No New Votes*\'>', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (23, 'C_LOCKED', '<img src=\'style_images/<#IMG_DIR#>/bf_readonly.gif\' border=\'0\'  alt=\'Read Only Forum\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (24, 'C_OFF', '<img src=\'style_images/<#IMG_DIR#>/bf_nonew.gif\' border=\'0\'  alt=\'No New Posts\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (25, 'C_OFF_CAT', '<img src=\'style_images/<#IMG_DIR#>/bc_nonew.gif\' border=\'0\'  alt=\'No New Posts\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (26, 'C_OFF_RES', '<img src=\'style_images/<#IMG_DIR#>/br_nonew.gif\' border=\'0\'  alt=\'No New Posts\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (27, 'C_ON', '<img src=\'style_images/<#IMG_DIR#>/bf_new.gif\' border=\'0\'  alt=\'New Posts\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (28, 'C_ON_CAT', '<img src=\'style_images/<#IMG_DIR#>/bc_new.gif\' border=\'0\'  alt=\'New Posts\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (29, 'C_ON_RES', '<img src=\'style_images/<#IMG_DIR#>/br_new.gif\' border=\'0\'  alt=\'New Posts\'  />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (30, 'F_ACTIVE', '<img src=\'style_images/<#IMG_DIR#>/user.gif\' border=\'0\'  alt=\'Active Users\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (31, 'F_NAV_SEP', '&nbsp;-&gt;&nbsp;', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (32, 'F_NAV', '<img src=\'style_images/<#IMG_DIR#>/nav.gif\' border=\'0\'  alt=\'&gt;\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (33, 'F_STATS', '<img src=\'style_images/<#IMG_DIR#>/stats.gif\' border=\'0\'  alt=\'Board Stats\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (34, 'NO_PHOTO', '<img src=\'style_images/<#IMG_DIR#>/nophoto.gif\' border=\'0\'  alt=\'No Photo\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (35, 'CAMERA', '<img src=\'style_images/<#IMG_DIR#>/camera.gif\' border=\'0\'  alt=\'Photo\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (36, 'M_READ', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no.gif\' border=\'0\'  alt=\'Read Msg\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (37, 'M_UNREAD', '<img src=\'style_images/<#IMG_DIR#>/f_norm.gif\' border=\'0\'  alt=\'Unread Msg\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (38, 'P_AOL', '<img src=\'style_images/<#IMG_DIR#>/p_aim.gif\' border=\'0\'  alt=\'AOL\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (39, 'P_DELETE', '<img src=\'style_images/<#IMG_DIR#>/p_delete.gif\' border=\'0\'  alt=\'Delete Post\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (40, 'P_EDIT', '<img src=\'style_images/<#IMG_DIR#>/p_edit.gif\' border=\'0\'  alt=\'Edit Post\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (41, 'P_EMAIL', '<img src=\'style_images/<#IMG_DIR#>/p_email.gif\' border=\'0\'  alt=\'Email Poster\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (42, 'P_ICQ', '<img src=\'style_images/<#IMG_DIR#>/p_icq.gif\' border=\'0\'  alt=\'ICQ\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (43, 'P_MSG', '<img src=\'style_images/<#IMG_DIR#>/p_pm.gif\' border=\'0\'  alt=\'PM\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (44, 'P_QUOTE', '<img src=\'style_images/<#IMG_DIR#>/p_quote.gif\' border=\'0\'  alt=\'Quote Post\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (45, 'P_WEBSITE', '<img src=\'style_images/<#IMG_DIR#>/p_www.gif\' border=\'0\'  alt=\'Users Website\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (46, 'P_YIM', '<img src=\'style_images/<#IMG_DIR#>/p_yim.gif\' border=\'0\' alt=\'Yahoo\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (47, 'P_REPORT', '<img src=\'style_images/<#IMG_DIR#>/p_report.gif\' border=\'0\'  alt=\'Report Post\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (48, 'P_MSN', '<img src=\'style_images/<#IMG_DIR#>/p_msn.gif\' border=\'0\' alt=\'MSN\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (49, 'CAT_IMG', '<img src=\'style_images/<#IMG_DIR#>/nav_m.gif\' border=\'0\'  alt=\'&gt;\' width=\'8\' height=\'8\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (50, 'NEW_POST', '<img src=\'style_images/<#IMG_DIR#>/newpost.gif\' border=\'0\'  alt=\'Goto last unread\' title=\'Goto last unread\' hspace=2>', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (51, 'LAST_POST', '<img src=\'style_images/<#IMG_DIR#>/lastpost.gif\' border=\'0\'  alt=\'Last Post\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (52, 'BR_REDIRECT', '<img src=\'style_images/<#IMG_DIR#>/br_redirect.gif\' border=\'0\'  alt=\'Redirect\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (53, 'INTEGRITY_MSGR', '<img src=\'style_images/<#IMG_DIR#>/p_im.gif\' border=\'0\'  alt=\'Integrity Messenger IM\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (54, 'ADDRESS_CARD', '<img src=\'style_images/<#IMG_DIR#>/addresscard.gif\' border=\'0\'  alt=\'Mini Profile\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (55, 'T_QREPLY', '<img src=\'style_images/<#IMG_DIR#>/t_qr.gif\' border=\'0\'  alt=\'Quick Reply\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (56, 'T_OPTS', '<img src=\'style_images/<#IMG_DIR#>/t_options.gif\' border=\'0\'  alt=\'Topic Options\' />', 1, 1);";

$SQL[] = "REPLACE INTO ibf_macro VALUES (57, 'CAL_NEWEVENT', '<img src=\'style_images/<#IMG_DIR#>/cal_newevent.gif\' border=\'0\'  alt=\'Add New Event\' />', 1, 1)";

$SQL[] = "REPLACE INTO ibf_macro VALUES (58, 'F_RULES', '<img src=\'style_images/<#IMG_DIR#>/forum_rules.gif\' border=\'0\'  alt=\'Forum Rules\' />', 1, 1)";

$SQL[] = "REPLACE INTO ibf_macro VALUES (59, 'WARN_0', '<img src=\'style_images/<#IMG_DIR#>/warn0.gif\' border=\'0\'  alt=\'-----\' />', 1, 1)";

$SQL[] = "REPLACE INTO ibf_macro VALUES (60, 'WARN_1', '<img src=\'style_images/<#IMG_DIR#>/warn1.gif\' border=\'0\'  alt=\'X----\' />', 1, 1)";

$SQL[] = "REPLACE INTO ibf_macro VALUES (61, 'WARN_2', '<img src=\'style_images/<#IMG_DIR#>/warn2.gif\' border=\'0\'  alt=\'XX---\' />', 1, 1)";

$SQL[] = "REPLACE INTO ibf_macro VALUES (62, 'WARN_3', '<img src=\'style_images/<#IMG_DIR#>/warn3.gif\' border=\'0\'  alt=\'XXX--\' />', 1, 1)";

$SQL[] = "REPLACE INTO ibf_macro VALUES (63, 'WARN_4', '<img src=\'style_images/<#IMG_DIR#>/warn4.gif\' border=\'0\'  alt=\'XXXX-\' />', 1, 1)";

$SQL[] = "REPLACE INTO ibf_macro VALUES (64, 'WARN_5', '<img src=\'style_images/<#IMG_DIR#>/warn5.gif\' border=\'0\'  alt=\'XXXXX\' />', 1, 1)";

$SQL[] = "REPLACE INTO ibf_macro VALUES (65, 'WARN_ADD', '<img src=\'style_images/<#IMG_DIR#>/warn_add.gif\' border=\'0\'  alt=\'+\' />', 1, 1)";

$SQL[] = "REPLACE INTO ibf_macro VALUES (66, 'WARN_MINUS', '<img src=\'style_images/<#IMG_DIR#>/warn_minus.gif\' border=\'0\'  alt=\'-\' />', 1, 1)";



	return $SQL;

}



function get_main_css()

{

return "/* FIX IE6 Scrollbars bug - Leave this in! */

html { overflow-x: auto; } 



/* Body entry, change forum page background colour, default font, font size, etc. Leave text-align:center to center board content

   #ipwrapper will set text-align back to left for the forum. Any other tables / divs you use must use text-align:left to re-align

   the content properly. This is a work around to a known Internet Explorer bug */

BODY { font-family: Verdana, Tahoma, Arial, sans-serif; font-size: 11px; color: #000; margin:0px;padding:0px;background-color:#FFF; text-align:center }

TABLE, TR, TD { font-family: Verdana, Tahoma, Arial, sans-serif; font-size: 11px; color: #000; }



/* MAIN WRAPPER: Adjust forum width here. Leave margins alone to auto-center content */

#ipbwrapper { text-align:left; width:95%; margin-left:auto;margin-right:auto }



a:link, a:visited, a:active { text-decoration: underline; color: #000 }

a:hover { color: #465584; text-decoration:underline }



fieldset.search { padding:6px; line-height:150% }

label      { cursor:pointer; }

form       { display:inline; }

img        { vertical-align:middle; border:0px }

img.attach { border:2px outset #EEF2F7;padding:2px }



.googleroot  { padding:6px; line-height:130% }

.googlechild { padding:6px; margin-left:30px; line-height:130% }

.googlebottom, .googlebottom a:link, .googlebottom a:visited, .googlebottom a:active { font-size:11px; color: #3A4F6C; }

.googlish, .googlish a:link, .googlish a:visited, .googlish a:active { font-size:14px; font-weight:bold; color:#00D; }

.googlepagelinks { font-size:1.1em; letter-spacing:1px }

.googlesmall, .googlesmall a:link, .googlesmall a:active, .googlesmall a:visited { font-size:10px; color:#434951 }



li.helprow { padding:0px; margin:0px 0px 10px 0px }

ul#help    { padding:0px 0px 0px 15px }



option.cat { font-weight:bold; }

option.sub { font-weight:bold;color:#555 }

.caldate   { text-align:right;font-weight:bold;font-size:11px;color:#777;background-color:#DFE6EF;padding:4px;margin:0px }



.warngood { color:green }

.warnbad  { color:red }



#padandcenter { margin-left:auto;margin-right:auto;text-align:center;padding:14px 0px 14px 0px }



#profilename { font-size:28px; font-weight:bold; }

#calendarname { font-size:22px; font-weight:bold; }



#photowrap { padding:6px; }

#phototitle { font-size:24px; border-bottom:1px solid black }

#photoimg   { text-align:center; margin-top:15px } 



#ucpmenu    { line-height:150%;width:22%; border:1px solid #345487;background-color: #F5F9FD }

#ucpmenu p  { padding:2px 5px 6px 9px;margin:0px; }

#ucpcontent { background-color: #F5F9FD; border:1px solid #345487;line-height:150%; width:auto }

#ucpcontent p  { padding:10px;margin:0px; }



#ipsbanner { position:absolute;top:1px;right:5%; }

#logostrip { border:1px solid #345487;background-color: #3860BB;background-image:url(style_images/<#IMG_DIR#>/tile_back.gif);padding:0px;margin:0px; }

#submenu   { border:1px solid #BCD0ED;background-color: #DFE6EF;font-size:10px;margin:3px 0px 3px 0px;color:#3A4F6C;font-weight:bold;}

#submenu a:link, #submenu  a:visited, #submenu a:active { font-weight:bold;font-size:10px;text-decoration: none; color: #3A4F6C; }

#userlinks { border:1px solid #C2CFDF; background-color: #F0F5FA }



#navstrip  { font-weight:bold;padding:6px 0px 6px 0px; }



.activeuserstrip { background-color:#BCD0ED; padding:6px }



/* Form stuff (post / profile / etc) */

.pformstrip { background-color: #D1DCEB; color:#3A4F6C;font-weight:bold;padding:7px;margin-top:1px }

.pformleft  { background-color: #F5F9FD; padding:6px; margin-top:1px;width:25%; border-top:1px solid #C2CFDF; border-right:1px solid #C2CFDF; }

.pformleftw { background-color: #F5F9FD; padding:6px; margin-top:1px;width:40%; border-top:1px solid #C2CFDF; border-right:1px solid #C2CFDF; }

.pformright { background-color: #F5F9FD; padding:6px; margin-top:1px;border-top:1px solid #C2CFDF; }



/* Topic View elements */

.signature   { font-size: 10px; color: #339; line-height:150% }

.postdetails { font-size: 10px }

.postcolor   { font-size: 12px; line-height: 160% }



.normalname { font-size: 12px; font-weight: bold; color: #003 }

.normalname a:link, .normalname a:visited, .normalname a:active { font-size: 12px }

.unreg { font-size: 11px; font-weight: bold; color: #900 }



.post1 { background-color: #F5F9FD }

.post2 { background-color: #EEF2F7 }

.postlinksbar { background-color:#D1DCEB;padding:7px;margin-top:1px;font-size:10px; background-image: url(style_images/<#IMG_DIR#>/tile_sub.gif) }



/* Common elements */

.row1 { background-color: #F5F9FD }

.row2 { background-color: #DFE6EF }

.row3 { background-color: #EEF2F7 }

.row4 { background-color: #E4EAF2 }



.darkrow1 { background-color: #C2CFDF; color:#4C77B6; }

.darkrow2 { background-color: #BCD0ED; color:#3A4F6C; }

.darkrow3 { background-color: #D1DCEB; color:#3A4F6C; }



.hlight { background-color: #DFE6EF }

.dlight { background-color: #EEF2F7 }



.titlemedium { font-weight:bold; color:#3A4F6C; padding:7px; margin:0px; background-image: url(style_images/<#IMG_DIR#>/tile_sub.gif) }

.titlemedium  a:link, .titlemedium  a:visited, .titlemedium  a:active  { text-decoration: underline; color: #3A4F6C }



/* Main table top (dark blue gradient by default) */

.maintitle { vertical-align:middle;font-weight:bold; color:#FFF; padding:8px 0px 8px 5px; background-image: url(style_images/<#IMG_DIR#>/tile_back.gif) }

.maintitle a:link, .maintitle  a:visited, .maintitle  a:active { text-decoration: none; color: #FFF }

.maintitle a:hover { text-decoration: underline }



/* tableborders gives the white column / row lines effect */

.plainborder { border:1px solid #345487;background-color:#F5F9FD }

.tableborder { border:1px solid #345487;background-color:#FFF; padding:0; margin:0 }

.tablefill   { border:1px solid #345487;background-color:#F5F9FD;padding:6px;  }

.tablepad    { background-color:#F5F9FD;padding:6px }

.tablebasic  { width:100%; padding:0px 0px 0px 0px; margin:0px; border:0px }



.wrapmini    { float:left;line-height:1.5em;width:25% }

.pagelinks   { float:left;line-height:1.2em;width:35% }



.desc { font-size:10px; color:#434951 }

.edit { font-size: 9px }





.searchlite { font-weight:bold; color:#F00; background-color:#FF0 }



#QUOTE { font-family: Verdana, Arial; font-size: 11px; color: #465584; background-color: #FAFCFE; border: 1px solid #000; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }

#CODE  { font-family: Courier, Courier New, Verdana, Arial;  font-size: 11px; color: #465584; background-color: #FAFCFE; border: 1px solid #000; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }



.copyright { font-family: Verdana, Tahoma, Arial, Sans-Serif; font-size: 9px; line-height: 12px }



.codebuttons  { font-size: 10px; font-family: verdana, helvetica, sans-serif; vertical-align: middle }

.forminput, .textinput, .radiobutton, .checkbox  { font-size: 11px; font-family: verdana, helvetica, sans-serif; vertical-align: middle }



.thin { padding:6px 0px 6px 0px;line-height:140%;margin:2px 0px 2px 0px;border-top:1px solid #FFF;border-bottom:1px solid #FFF }



.purple { color:purple;font-weight:bold }

.red    { color:red;font-weight:bold }

.green  { color:green;font-weight:bold }

.blue   { color:blue;font-weight:bold }

.orange { color:#F90;font-weight:bold }";

}



function get_main_wrapper()

{

return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 

<head> 

<title><% TITLE %></title> 

<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" /> 

<% GENERATOR %> 

<% CSS %> 

<% JAVASCRIPT %> 

</head> 

<body>

<div id="ipbwrapper">

<% BOARD HEADER %> 

<% NAVIGATION %> 

<% BOARD %> 

<% STATS %> 

<% COPYRIGHT %>

</div>

</body> 

</html>';

}





?>