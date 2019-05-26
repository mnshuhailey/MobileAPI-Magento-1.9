<?php
/*
* Database 
* Author: Mohamad
* Email: mshuhailey@gmail.com
* Version: 1.0
*/

include('config.php');

$con = mysql_connect(DBHOST,DBUSER,DBPASS);
mysql_select_db(DBNAME, $con);

//if($con) echo 'success connect';

?>