<?php 
require_once('../app/Mage.php');
Mage::app();
/*
http://tcstaging.trendycounty.com/mobileapi/register.php?key=5pkmEyb8TPhx7eex87BMxcY77vyhhnqXAzUQHVPt
&firstname=Kelvyn
&lastname=Law
&email=kelvynlaw@example.com
&newsletter=1
&OauthToken=EAAXtHKM4IacBANfpS2RZBgAgOWyuebarrR4ZBbzt0cPBs81ZAtdFAlAUO8VSxTdUXlGOHyKZBpssyZAl7P0Dqy3SCPNLNuDYD3ZA7uvjHMsbNpSy7tk0sQ4LizlNBcX7HOQknTE1C752wRIcZCpuckgqeWaZCCOxAAFrZAfuTJHE9b49K7dD8mZAJ4TJkKLKDknAvNIcXwH8tkHxHu66qmWzZA0
&OauthId=1013754748721811
&socialType=1
 */

$firstName = @$_REQUEST['firstname'];
$lastName = @$_REQUEST['lastname'];
$email = @$_REQUEST['email'];
$pass = @$_REQUEST['pass'];
$newsletter = @$_REQUEST['newsletter'];

$socialType = @$_REQUEST['socialType'];
if($socialType == 1){
    $fb_OauthId = @$_REQUEST['OauthId'];
    $fb_OauthToken = @$_REQUEST['OauthToken'];  
}else{
    $fb_OauthId = "";
    $fb_OauthToken = "";
}
if($socialType == 2){
    $gl_OauthId = @$_REQUEST['OauthId'];
    $gl_OauthToken = @$_REQUEST['OauthToken'];
}else{
    $gl_OauthId = "";
    $gl_OauthToken = "";
}


$websiteId = Mage::app()->getWebsite()->getId();
$store = Mage::app()->getStore();

$customer = Mage::getModel("customer/customer");
$customer->website_id = $websiteId;
$customer->setStore($store);

$data = array();
if($email !="" && !filter_var($email, FILTER_VALIDATE_EMAIL) === false){
    try {
    $sendPassToEmail = true;
    $customer->firstname = $firstName;
    $customer->lastname = $lastName;
    $customer->email = $email;
    $customer->password_hash = md5($pass);
    
    $customer->setFacebookoauthid($fb_OauthId);
    $customer->setFacebooktokenid($fb_OauthToken);
    $customer->setGoogleoauthid($gl_OauthId);
    $customer->setGoogletokenid($gl_OauthToken);
    
    $customer->save();
    $storeID = $customer->getSendemailStoreId();
    $customer->sendNewAccountEmail('registered', '', $storeID);
    if($newsletter == 1){
	Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($email);
	$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
	$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
	$subscriber->save();
    }
    $data['status'] = true;
    $data['message'] = 'successfully registered';
    
    //$customers = Mage::getModel("customer/customer"); 
    //$customers->setWebsiteId(Mage::app()->getWebsite('admin')->getId()); 
    $customer->loadByEmail($email);
		
    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
    if($subscriber->getId())
    {
	$dataNewsletter = true;
    }else{
	$dataNewsletter = false;
    }
    
    $data['data'] = array(
	    'id' => $customer->getId(),
	    'first_name' => $customer->getfirstname(),
	    'last_name' => $customer->getlastname(),
	    'email_address' => $customer->getemail(),
	    'news_letter' => $dataNewsletter,
	    'gender' => $customer->getgender(),
	    'phone' => $customer->getphone(),       
	    'dob' => $customer->getdob(),
	    'default_address' => '',
	    'facebook_id' => $customer->getFacebookoauthid(),
	    'google_plus_id' => $customer->getGoogleoauthid()
    );

    }catch(Exception $e){
        $systemError = $e->getMessage();
        $data['status'] = false;
        $data['message'] = $systemError; 
    }
}else{
    $data['status'] = false;
    $data['message'] = 'Invalid Email Address';
}


	
$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//sample url: http://staging.trendycounty.com/mobileapi/register.php?key=5pkmEyb8TPhx7eex87BMxcY77vyhhnqXAzUQHVPt&firstname=Mohamad&lastname=Developer&email=mshuhailey@gmail.com&pass=123qwe&newsletter=1
?>