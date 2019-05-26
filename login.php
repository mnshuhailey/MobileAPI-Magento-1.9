<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();

$websiteId = Mage::app()->getWebsite()->getId();
$store = Mage::app()->getStore();
$customer = Mage::getModel("customer/customer");
$customer->website_id = $websiteId;
$customer->setStore($store);
$data = array();
/*
$customer->website_id = 2;
$customer->setStore($store);
die(Mage::app()->getStore()->getId());
 */
$data['status'] = true;
$data['message'] = '';

$email = @$_REQUEST['email'];
$password = @$_REQUEST['pass'];
$socialType = @$_REQUEST['socialType'];







try {
    $customer->loadByEmail($email);
    if($customer->getId() > 0){
	//if(($socialType == 1 || $socialType == 2) && $password =="" && $_REQUEST['OauthId'] !="" && $_REQUEST['OauthToken'] !="" && $email !=""){
	if(($socialType == 1 || $socialType == 2) && $password =="" && $_REQUEST['OauthId'] !="" && $email !=""){
	    $verified=false;
	    if($socialType == 1){
		$fb_OauthId = @$_REQUEST['OauthId'];
		$fb_OauthToken = @$_REQUEST['OauthToken'];
		$DB_fbid = $customer->getFacebookoauthid();
		$DB_fbtoken = $customer->getFacebooktokenid();
		//if($fb_OauthId == $DB_fbid && $fb_OauthToken == $DB_fbtoken){
		if($fb_OauthId == $DB_fbid){
		    $verified = true;
		}
		if($_REQUEST['mm'] =="yes"){
		    echo "$fb_OauthId == $DB_fbid && $fb_OauthToken == $DB_fbtoken <<<<";
		}
	    }else{
		$gl_OauthId = @$_REQUEST['OauthId'];
		$gl_OauthToken = @$_REQUEST['OauthToken'];
		$DB_glid = $customer->getGoogleoauthid();
		$DB_gltoken = $customer->getGoogletokenid();
		//if($gl_OauthId == $DB_glid && $gl_OauthToken == $DB_gltoken){
		if($gl_OauthId == $DB_glid){
		    $verified = true;
		}
	    }
	    if($verified){
		$session = Mage::getSingleton('customer/session');
		if ($customer->getId()) {
		    $session->setCustomerAsLoggedIn($customer);
		    //return $session;
		}else
		{
		    $data['status'] = false;
		    $data['message'] = 'Invalid Data.';
		}
	    }else{
		$data['status'] = false;
		$data['message'] = 'Invalid Data';
	    }
	}else{
	    $session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
	    $session->login($email, $password);
	}
    }else{
	$data['status'] = false;
	$data['message'] = '99999';
    }
    
    
    
    
    
    if($data['status']){
	$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
	if($_REQUEST['mm'] == 'yes'){
	    //echo($addressItem['country_id']."<br/>");
	    //var_dump($subscriber->getStatus()."<<<<");
	    //die();
	}
	if($subscriber->getStatus() == "1")
	{
	    $dataNewsletter = true;
	}else{
	    $dataNewsletter = false;
	}
	foreach ($customer->getAddresses() as $address)
	{
	    $address = $address->toArray();
	}
		
	$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	
	$phone = $address['telephone'];
	$country_id = $address['country_id'];
	if($country_id != ""){
	    $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
	}else{
	    $countryName = "";
	}
	$address['country_name'] = $countryName;
	
	if($phone):
		$phoneNumber = $phone;
	else:
		$phoneNumber = '';
	endif;
	
	$data['data'] = array(
		'id' => $customer->getId(),
		'first_name' => $customer->getfirstname(),
		'last_name' => $customer->getlastname(),
		'email_address' => $customer->getemail(),
		'news_letter' => $dataNewsletter,
		'gender' => $customer->getgender(),
		'phone' => $phoneNumber,       
		'dob' => $customer->getdob(),
		'default_address' => $address,
		'product_count' => $count,
		'facebook_id' => $customer->getFacebookoauthid(),
		'google_plus_id' => $customer->getGoogleoauthid()
	);
    }else{
	$data['data'] = array();
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

//sample url:  http://staging.trendycounty.com/mobileapi/login.php?email=mohamad@optima.com.my&pass=123qwe
?>