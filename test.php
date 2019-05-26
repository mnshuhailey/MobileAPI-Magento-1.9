<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();

//
//$installer = $this;
//
//$installer->startSetup();
//
//$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
//
//die($setup."<<<<");

$customerid = $_REQUEST['customerid'];

$customers = Mage::getModel('customer/customer')->load($customerid);
//$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
//$customer = Mage::getModel('customer/customer')->load($custid);
//$customer->getCustomattribute(); //1013754748721811
//$customers->setFbOauthId("EAAXtHKM4IacBANfpS2RZBgAgOWyuebarrR4ZBbzt0cPBs81ZAtdFAlAUO8VSxTdUXlGOHyKZBpssyZAl7P0Dqy3SCPNLNuDYD3ZA7uvjHMsbNpSy7tk0sQ4LizlNBcX7HOQknTE1C752wRIcZCpuckgqeWaZCCOxAAFrZAfuTJHE9b49K7dD8mZAJ4TJkKLKDknAvNIcXwH8tkHxHu66qmWzZA0");
$customers->setFacebookoauthid("1013754748721811");
try{
    $customers->save();
}
catch (Exception $e) {
    echo "<pre>";
    var_dump($e->getMessage());
}
