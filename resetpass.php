<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();

$email = $_REQUEST['email'];

	$websiteId = Mage::app()->getWebsite()->getId();
	$store = Mage::app()->getStore();
	
	$data = array();
	
	try {
		$customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email);
		if($customer->getId()){
			$customer->sendPasswordResetConfirmationEmail();
			$data['status'] = true;
			$data['message'] = "Reset password instruction has been sent to your email"; 
		}else{
			$data['status'] = false;
			$data['message'] = "The email address does not exist in the system"; 
		}
	}catch(Exception $e){
        $systemError = $e->getMessage();
        $data['status'] = false;
        $data['message'] = $systemError; 
    }
	
$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//sample url:  http://staging.trendycounty.com/mobileapi/resetpass.php?email=mohamad@optima.com.my
?>