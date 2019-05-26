<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();


$customerid = $_REQUEST['customerid'];

$customers = Mage::getModel('customer/customer')->load($customerid);
//$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
//$customer = Mage::getModel('customer/customer')->load($custid);
//$customer->getFacebookOauthId(); //1013754748721811
//$customers->setFacebookOauthId("1013754748721811");
echo "<pre>";
var_dump($customers->getFacebookoauthid());