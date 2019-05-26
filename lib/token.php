<?php
/*
* Token Key 
* Author: Mohamad
* Email: mshuhailey@gmail.com
* Version: 1.0
*/

//Parameter
$baseURL = 'http://tcstaging.trendycounty.com/';
$token_api_key = '5pkmEyb8TPhx7eex87BMxcY77vyhhnqXAzUQHVPt';

$token_app = trim($_REQUEST['key']);
$timestamp = trim($_REQUEST['timestamp']);

$generatedKey = sha1("trendycountymobile@".$timestamp);
if($_REQUEST['mm'] == "yes"){
    ///echo $generatedKey."<br/>";
}
// sha1 ("trendycountymobile@" + timestamp)
//timestamp
//if($_REQUEST['mm'] == "yes"){
//    //if($token_app != $token_api_key)
//    //{
//    //    echo 'Hackerzzz detected!';
//    //    exit();
//    //}
//}else{

    if($token_app != $generatedKey || $timestamp =="" || $token_app == "")
    {
        echo 'Hackerzzz detected!';
        exit();
    }
//}

?>