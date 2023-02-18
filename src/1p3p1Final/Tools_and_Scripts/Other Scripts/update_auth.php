<?php



/*

+--------------------------------------------------------------------------

|   INVISION POWER BOARD PATCH SCRIPT

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

|   > Patch script

|   > Script written by Matt Mecham

|   > Date started: 17th June 2003

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



$members = array();



$DB->query("SELECT * FROM ibf_members WHERE mgroup=".$INFO['auth_group']);



while ( $r = $DB->fetch_row() )

{

	$members[ $r['id'] ] = $r;

}



$tmp = $members;



$DB->query("SELECT * FROM ibf_validating");



while ( $v = $DB->fetch_row() )

{

	if ( is_array( $members[ $v['member_id'] ] ) )

	{

		unset($members[ $v['member_id'] ]);

	}

}



/*header("Content-type: text/plain");

print_r($tmp);

print_r($members);

exit();*/



$count = intval( count($members) );



if ( $count > 0 )

{

	foreach( $members as $mid => $mdata )

	{

		$dbs = array(

					  'vid'        => md5( time() . $mid ),

					  'member_id'  => $mdata['id'],

					  'real_group' => $INFO['member_group'],

					  'temp_group' => $INFO['auth_group'],

					  'entry_date' => time(),

					  'coppa_user' => 0,

					  'new_reg'    => 1,

					  'ip_address' => '127.0.0.1',

					);

					

		$dbstring = $DB->compile_db_insert_string($dbs);

		

		$DB->query("INSERT INTO ibf_validating ({$dbstring['FIELD_NAMES']}) VALUES({$dbstring['FIELD_VALUES']})");

	

	}

}



echo("<html><body><b>$count members found...</b><br /><br />Update complete, you may now remove this file</body></html>");



exit();





//+-------------------------------------------------

// GLOBAL ROUTINES

//+-------------------------------------------------



function fatal_error($message="", $help="") {

	echo("$message<br><br>$help");

	exit;

}

?>

