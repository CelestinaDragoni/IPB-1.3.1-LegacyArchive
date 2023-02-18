<?php



/*

+--------------------------------------------------------------------------

|   IBFORUMS v1 UPGRADE FROM 1.2 to v1.3

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

|   > Date started: 24th September 2003 on a chilly but sunny day

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

		

	case 'insert':

		do_insert();

		break;

		

		

	default:

		do_intro();

		break;

}



//+---------------------------------------













//+---------------------------------------









function do_insert()

{

	global $std, $template, $root, $DB;

	

	run_sql('insert');

	

	$template->print_top('Step 4 Complete');

	

	$template->contents .= "<div class='centerbox'>

							 <div class='tableborder'>

							  <div class='maintitle'>Upgrade from v1.2 to v1.3</div>

							  <div class='pformstrip'>The new data has been inserted<!--sideways into a place where the sun don't shine :o (Yes, Iceland) --></div>

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





function do_index()

{

	global $std, $template, $root, $DB;

	

	run_sql('index');

	

	$template->print_top('Step 3 Complete');

	

	$template->contents .= "<div class='centerbox'>

							 <div class='tableborder'>

							  <div class='maintitle'>Upgrade from v1.2 to v1.3</div>

							  <div class='pformstrip'>The indexes have been adjusted</div>

							  <div class='tablepad'>

							   You may now proceed to the next step.

							   <br /><br />

							   <b><a href='upgrade6.php?a=insert'>Proceed to Step 4: Insert New Data</a> &gt;&gt;</b>

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

							  <div class='maintitle'>Upgrade from v1.2 to v1.3</div>

							  <div class='pformstrip'>The tables have been altered</div>

							  <div class='tablepad'>

							   You may now proceed to the next step.

							   <br /><br />

							   <b><a href='upgrade6.php?a=insert'>Proceed to Step 3: Insert New Data</a> &gt;&gt;</b>

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

							  <div class='maintitle'>Upgrade from v1.2 to v1.3</div>

							  <div class='pformstrip'>The new tables have been created</div>

							  <div class='tablepad'>

							   You may now proceed to the next step.

							   <br /><br />

							   <b><a href='upgrade6.php?a=alter'>Proceed to Step 2: Alter Existing Tables</a> &gt;&gt;</b>

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

							   <b style='font-size:14px'>Welcome to the Invision Board Upgrade Utility</b>

							   <br /><b>Invision Power Board 1.2 -> Invision Power Board 1.3</b>

							   <br><br>

							   Before you run this upgrade tool, please ensure that ALL of the source, language and skin files

							   have been uploaded into the corresponding directories on your server as noted in the documentation

							   included with the download package.

							   <br><br>

							   This script should be in the root forum directory (the same directory that index.php is in).

							   <br><br>

							   Once you have used this script, you can use the new 'Skin Version Control' to update your skins.

							   ";

						 

	

	$template->contents .= "<br /><br /><div align='center'><b><a href='upgrade6.php?a=tables'>Proceed to Step 1: Create New Tables</a> &gt;&gt;</b></div>";

	

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

	

	



	return $SQL;

}





function sql_tables()

{

	$SQL = array();

	

$SQL[] = "CREATE TABLE ibf_subscriptions (

 sub_id smallint(5) NOT NULL auto_increment,

 sub_title varchar(250) NOT NULL default '',

 sub_desc text,

 sub_new_group mediumint(8) NOT NULL default 0,

 sub_length smallint(5) NOT NULL default '1',

 sub_unit varchar(2) NOT NULL default 'm',

 sub_cost decimal(10,2) NOT NULL default '0.00',

 sub_run_module varchar(250) NOT NULL default '',

 PRIMARY KEY (sub_id)

) TYPE=MyISAM;";



$SQL[] = "CREATE TABLE ibf_subscription_extra (

 subextra_id smallint(5) NOT NULL auto_increment,

 subextra_sub_id smallint(5) NOT NULL default '0',

 subextra_method_id smallint(5) NOT NULL default '0',

 subextra_product_id varchar(250) NOT NULL default '0',

 subextra_can_upgrade tinyint(1) NOT NULL default '0',

 subextra_recurring tinyint(1) NOT NULL default '0',

 subextra_custom_1 text,

 subextra_custom_2 text,

 subextra_custom_3 text,

 subextra_custom_4 text,

 subextra_custom_5 text,

 PRIMARY KEY(subextra_id)

) TYPE=MyISAM;";





$SQL[] = "CREATE TABLE ibf_subscription_trans (

 subtrans_id int(10) NOT NULL auto_increment,

 subtrans_sub_id smallint(5) NOT NULL default '0',

 subtrans_member_id mediumint(8) NOT NULL default '0',

 subtrans_old_group smallint(5) NOT NULL default '0',

 subtrans_paid decimal(10,2) NOT NULL default '0.00',

 subtrans_cumulative decimal(10,2) NOT NULL default '0.00',

 subtrans_method varchar(20) NOT NULL default '',

 subtrans_start_date int(11) NOT NULL default '0',

 subtrans_end_date int(11) NOT NULL default '0',

 subtrans_state varchar(200) NOT NULL default '',

 subtrans_trxid varchar(200) NOT NULL default '',

 subtrans_subscrid varchar(200) NOT NULL default '',

 subtrans_currency varchar(10) NOT NULL default 'USD',

 PRIMARY KEY (subtrans_id)

) TYPE=MyISAM;";



$SQL[] = "CREATE TABLE ibf_subscription_logs (

 sublog_id int(10) NOT NULL auto_increment,

 sublog_date int(10) NOT NULL default '',

 sublog_member_id mediumint(8) NOT NULL default '0',

 sublog_transid int(10) NOT NULL default '',

 sublog_ipaddress varchar(16) NOT NULL default '',

 sublog_data text,

 sublog_postdata text,

 PRIMARY KEY (sublog_id)

) TYPE=MyISAM;";



$SQL[] = "CREATE TABLE ibf_subscription_methods (

 submethod_id smallint(5) NOT NULL auto_increment,

 submethod_title varchar(250) NOT NULL default '',

 submethod_name varchar(20) NOT NULL default '',

 submethod_email varchar(250) NOT NULL default '',

 submethod_sid text,

 submethod_custom_1 text,

 submethod_custom_2 text,

 submethod_custom_3 text,

 submethod_custom_4 text,

 submethod_custom_5 text,

 submethod_is_cc tinyint(1) NOT NULL default '0',

 submethod_is_auto tinyint(1) NOT NULL default '0',

 submethod_desc text,

 submethod_logo text,

 submethod_active tinyint(1) NOT NULL default '',

 submethod_use_currency varchar(10) NOT NULL default 'USD',

 PRIMARY KEY (submethod_id)

) TYPE=MyISAM;";



$SQL[] = "CREATE TABLE ibf_subscription_currency (

 subcurrency_code varchar(10) NOT NULL,

 subcurrency_desc varchar(250) NOT NULL default '',

 subcurrency_exchange decimal(10, 8) NOT NULL,

 subcurrency_default tinyint(1) NOT NULL default '0',

 PRIMARY KEY(subcurrency_code)

) TYPE=MyISAM;";





	return $SQL;

}



function sql_alter()

{

	$SQL = array();

	

	

	$SQL[] = "alter table ibf_members add sub_end int(10) NOT NULL default '0';";

	$SQL[] = "alter table ibf_forums add notify_modq_emails text default '';";

	





	



	return $SQL;

}



function sql_index()

{

	$SQL = array();

	

	return $SQL;

}



function sql_insert()

{

	$SQL = array();

	

	$SQL[] = "insert into ibf_subscription_currency SET subcurrency_code='USD', subcurrency_desc='United States Dollars', subcurrency_exchange='1.00', subcurrency_default=1;";

	$SQL[] = "insert into ibf_subscription_currency SET subcurrency_code='GBP', subcurrency_desc='United Kingdom Pounds', subcurrency_exchange=' 0.630776', subcurrency_default=0;";

	$SQL[] = "insert into ibf_subscription_currency SET subcurrency_code='CAD', subcurrency_desc='Canada Dollars', subcurrency_exchange='1.37080', subcurrency_default=0;";

	$SQL[] = "insert into ibf_subscription_currency SET subcurrency_code='EUR', subcurrency_desc='Euro', subcurrency_exchange='0.901517', subcurrency_default=0;";

	$SQL[] = "INSERT INTO ibf_subscription_methods VALUES (1, 'PayPal', 'paypal', '', '', '', '', '', '', '', 0, 1, 'All major credit cards accepted. See <a href=\"https://www.paypal.com/affil/pal=9DJEWQQKVB6WL\" target=\"_blank\">PayPal</a> for more information.', '', 1, 'USD');";

	$SQL[] = "INSERT INTO ibf_subscription_methods VALUES (2, 'NOCHEX', 'nochex', '', '', '', '', '', '', '', 0, 1, 'UK debit and credit cards, such as Switch, Solo and VISA Delta. All prices will be convereted into GBP (UK Pounds) upon ordering.', NULL, 1, 'GBP');";

	$SQL[] = "INSERT INTO ibf_subscription_methods VALUES (3, 'Post Service', 'manual', '', '', '', '', '', '', '', 0, 0, 'You can use this method if you wish to send us a check, postal order or international money order.', NULL, 1, 'USD');";

	$SQL[] = "INSERT INTO ibf_subscription_methods VALUES (4, '2CheckOut', '2checkout', '', '', '', '', '', '', '', 1, 1, 'All major credit cards accepted. See <a href=\'http://www.2checkout.com/cgi-bin/aff.2c?affid=28376\' target=\'_blank\'>2CheckOut</a> for more information.', NULL, 1, 'USD');";



	return $SQL;

}





?>