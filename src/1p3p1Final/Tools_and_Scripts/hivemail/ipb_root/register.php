<?php
/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2 HiveMail register.php file
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
*/

// This file allows Hive Mail to integrate to IPB without restructuring too much

// Simply direct to IPB's register.

require "./conf_global.php";

$url = $INFO['board_url'].'/index.'.$INFO['php_ext'].'?act=Reg&mailsignup=1&coppa_pass=1';

@header("Location: $url");

exit();


?>