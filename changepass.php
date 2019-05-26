<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();

$customerid = $_REQUEST['customerid'];
$newpassword = $_REQUEST['newpassword'];
$currentpass = $_REQUEST['currentpass'];

$websiteId = Mage::app()->getWebsite()->getId();
$store = Mage::app()->getStore();
	
$data = array();

try {
	$customer = Mage::getModel('customer/customer')->load($customerid);
	$login_customer_result = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->authenticate($customer->getEmail(), $currentpass);
	$validate = 1;
}
catch(Exception $ex) {
     $validate = 0;
}
if($validate == 1) {
	
	try {
		$customer = Mage::getModel('customer/customer')->load($customerid);
		if($customer->getId()){
			$customer->setPassword($newpassword);
			$customer->save();
			$data['status'] = true;
			$data['message'] = "Your Password has been changed successfully"; 
			$data['data'] = array();
		}else{
			$data['status'] = false;
			$data['message'] = "The customer id does not exist in the system"; 
			$data['data'] = array();
		}
	}catch(Exception $e){
        $systemError = $e->getMessage();
        $data['status'] = false;
        $data['message'] = $systemError;
		$data['data'] = array();		
    }
}else{
        $data['status'] = false;
        $data['message'] = 'Incorrect current password.';
		$data['data'] = array();
}
	
$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//sample url:  http://staging.trendycounty.com/mobileapi/resetpass.php?email=mohamad@optima.com.my
?>