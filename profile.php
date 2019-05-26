<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();


$method = $_REQUEST['method'];


if($method == 'getprofileNew'){
	
$customerid = $_REQUEST['customerid'];

$customers = Mage::getModel('customer/customer')->load($customerid);
$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
//Get address
foreach ($customers->getAddresses() as $address)
{
	$address = $address->toArray();
}
 
//$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();

$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();

$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customers->getemail());
if($subscriber->getId())
{
	$dataNewsletter = true;
}else{
	$dataNewsletter = false;
}

$phone = $address['telephone'];
		
		if($phone):
			$phoneNumber = $phone;
		else:
			$phoneNumber = '';
		endif;

$data['status'] = true;
$data['message'] = '';

$data['data'] = array(
		'id' => $customers->getId(),
		'first_name' => $customers->getfirstname(),
		'last_name' => $customers->getlastname(),
		'email_address' => $customers->getemail(),
		'news_letter' => $dataNewsletter,
		'gender' => $customers->getgender(),
		'phone' => $phoneNumber,       
		'dob' => $customers->getdob(),
		'product_count' => $count,
		'default_address' => $address
	);
	
$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;
}

if($method == 'getprofile'){
	
    $customerid = $_REQUEST['customerid'];
    
    $customers = Mage::getModel('customer/customer')->load($customerid);
    $session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
    //Get address
    
    
    $defaultShipping = $customers->getDefaultShippingAddress();
    if($defaultShipping){
	$address = $defaultShipping->toArray();
	if($_REQUEST['mm'] == "yes"){
	    //echo "here";
	}
    }else{
	//echo "there";
	foreach ($customers->getAddresses() as $address)
	{
	    if($address->getId() > 0)
		$address = $address->toArray();
	//    if ($defaultShipping && $defaultShipping->getId() > 0 && $address->getId() == $defaultShipping->getId() && $address->getId() > 0) {
	//	break;
	//    }
	}
    }
    
	
	
    $count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
    
    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customers->getemail());
    if($subscriber->getStatus() == 1)
    {
        $dataNewsletter = true;
    }else{
        $dataNewsletter = false;
    }
    
    $phone = $address['telephone'];
    
    $country_id = $address['country_id'];
    $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
    if($countryName != "" && $countryName != false)
        $addressCountryName = $countryName;
	else
	$addressCountryName = "";
    if($phone):
        $phoneNumber = $phone;
    else:
        $phoneNumber = '';
    endif;
    
    $data['status'] = true;
    $data['message'] = '';
    
    $addArr = split("\n",$address['street']);
    if($addArr[0] != "")
    $add1 = $addArr[0];
    else
    $add1 = "";
    
    if($addArr[1] != "")
    $add2 = $addArr[1];
    else
    $add2 = "";
    $regionInfo = array(
		'region_id' => '',
		'region' => $address['region']
	    );
    
    $addressEdited = array(
	    'id' => $address['entity_id'],
	    'first_name' => $address['firstname'],
	    'last_name' => $address['lastname'],
	    'email' => $customers->getEmail(),
	    'add1' => $add1,
	    'add2' => $add2,
	    //'street' => $address['street'],
	    'city' => $address['city'],
	    'postcode' => $address['postcode'],
	    'country_id' => $address['country_id'],
	    'country_name' => $addressCountryName,
	    'region' => $regionInfo,       
	    'telephone' => $address['telephone']
	    );
    if($customers->getContactno() !="")
	$contactNo = $customers->getContactno();
    else
	$contactNo = $phoneNumber;
    $data['data'] = array(
                    'id' => $customers->getId(),
                    'first_name' => $customers->getfirstname(),
                    'last_name' => $customers->getlastname(),
                    'email_address' => $customers->getemail(),
                    'news_letter' => $dataNewsletter,
                    'gender' => $customers->getgender(),
                    'phone' => $contactNo,       
                    'dob' => $customers->getdob(),
                    'product_count' => $count,
                    'default_address' => $addressEdited
            );
            
    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);
    
    echo $json_data;
}

if($method == 'editprofileOld'){
	
    $customerid = @$_REQUEST['customerid'];
    $firstname = @$_REQUEST['firstname'];
    $lastname = @$_REQUEST['lastname'];
    $gender = @$_REQUEST['gender'];
    $dob = @$_REQUEST['dob'];
    $phone = @$_REQUEST['phone'];
    $newsletter = @$_REQUEST['newsletter'];
    $country_id = @$_REQUEST['country_id'];
    $zip = @$_REQUEST['zip'];
    $city = @$_REQUEST['city'];
    $fax = @$_REQUEST['fax'];
    $company = @$_REQUEST['company'];
    $street = @$_REQUEST['street'];
    $defaultBilling = @$_REQUEST['defaultBilling'];
    //if($defaultBilling == "")
    //$defaultBilling = 0;
    
    $defaultShipping = @$_REQUEST['defaultShipping'];
    $saveInAddressBook = @$_REQUEST['saveInAddressBook'];
    //echo "<pre>";
    //var_dump($_REQUEST);
    //die();
    /*
     *setCompany('Inchoo')
                ->setStreet('Kersov')
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1')
     */

    $customers = Mage::getModel('customer/customer')->load($customerid);
    $session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
    $email = $customers->getemail();
    
    
    $defaultShipping = $customers->getDefaultShippingAddress();
    if($defaultShipping){
	$addressDef = $defaultShipping->toArray();
    }else{
	foreach ($customers->getAddresses() as $addressDef)
	{
	    if($addressDef->getId() > 0)
		$addressDef = $addressDef->toArray();
	    if ($defaultShipping && $defaultShipping->getId() > 0 && $addressDef->getId() == $defaultShipping->getId() && $addressDef->getId() > 0) {
		break;
	    }
	}
    }
    
    
    
    
    
    if($_REQUEST['mm'] == 'yes'){
	//echo($addressItem['country_id']."<br/>");
	//echo($email."<< sub<<");
    }
    if($email !="" && !filter_var($email, FILTER_VALIDATE_EMAIL) === false){
        $count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
        $customers->setFirstname($firstname); 
        $customers->setLastname($lastname); 
        $customers->setGender($gender); 
        $customers->setDob($dob);
        $customers->save();
        
        $address = Mage::getModel("customer/address");
        $address->setCustomerId($customers->getId());
        $address->setFirstname($customers->getFirstname());
        $address->setMiddleName($customers->getMiddlename());
        $address->setLastname($customers->getLastname());
        if(isset($_REQUEST['country_id']) && $_REQUEST['country_id'] != "")
        $address->setCountryId($_REQUEST['country_id']);
        
        //$address->setRegionId('1'); //state/province, only needed if the country is USA
        $myFlag=false;
        if(isset($_REQUEST['zip']) && $_REQUEST['zip'] != ""){
	    $address->setPostcode($_REQUEST['zip']);
	    $myFlag = true;
	}
        
        
        if(isset($_REQUEST['city']) && $_REQUEST['city'] != ""){
	    $address->setCity($_REQUEST['city']);
	    $myFlag = true;
	}
        
        
        if(isset($_REQUEST['phone']) && $_REQUEST['phone'] != ""){
	    $address->setTelephone($_REQUEST['phone']);
	    $myFlag = true;
	}
        
        
        if(isset($_REQUEST['fax']) && $_REQUEST['fax'] != ""){
	    $address->setFax($_REQUEST['fax']);
	    $myFlag = true;
	}
        
        
        if(isset($_REQUEST['company']) && $_REQUEST['company'] != ""){
	    $address->setCompany($_REQUEST['company']);
	    $myFlag = true;
	}
        
        
        if(isset($_REQUEST['street']) && $_REQUEST['street'] != ""){
	    $address->setStreet($_REQUEST['street']);
	    $myFlag = true;
	}
	
	if(!$myFlag){
	    if(isset($addressDef['postcode']) && $addressDef['postcode'] !="")
	    $address->setPostcode($addressDef['postcode']);
	    
	    if(isset($addressDef['city']) && $addressDef['city'] !="")
	    $address->setCity($addressDef['city']);
	    
	    if(isset($addressDef['telephone']) && $addressDef['telephone'] !="")
	    $address->setTelephone($addressDef['telephone']);
	    
	    if(isset($addressDef['company']) && $addressDef['company'] !="")
	    $address->setCompany($addressDef['company']);
	    
	    if(isset($addressDef['street']) && $addressDef['street'] !="")
	    $address->setStreet($addressDef['street']);
	}
        
	$address->setIsDefaultBilling('1');
	$address->setIsDefaultShipping('1');
	$address->setSaveInAddressBook('1');
        
        //if(isset($_REQUEST['defaultBilling']) && $_REQUEST['defaultBilling'] == "1")
        //$address->setIsDefaultBilling($_REQUEST['defaultBilling']);
        
        //if(isset($_REQUEST['defaultShipping']) && $_REQUEST['defaultShipping'] == "1")
        //$address->setIsDefaultShipping($_REQUEST['defaultShipping']);
        
        //if(isset($_REQUEST['saveInAddressBook']) && $_REQUEST['saveInAddressBook'] == "1")
        $address->setSaveInAddressBook($_REQUEST['saveInAddressBook']);
         
        try{
            $address->save();
        }
        catch (Exception $e) {
            //Zend_Debug::dump($e->getMessage());
            Mage::log('Error .' . $e->getMessage());
            //Mage::dump($e->getMessage());
        }
        
        if($newsletter == 1 && $email != ""){
            //Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($email);
            //$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            ////$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
            //$subscriber->setStatus(1);
            //$subscriber->save();
	    
	    Mage::getModel('newsletter/subscriber')->subscribe($email);
            $news_letter_val = true;
	//    if($_REQUEST['mm'] == 'yes'){
	//	//echo($addressItem['country_id']."<br/>");
	//	echo($email."<< sub<<");
	//    }
        }else{
            //Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($email);
            //$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            ////$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
            //$subscriber->setStatus(0);
            //$subscriber->save();
	    
	    Mage::getModel('newsletter/subscriber')->loadByEmail($email)->unsubscribe();
            $news_letter_val = false;
	//    if($_REQUEST['mm'] == 'yes'){
	//	//echo($addressItem['country_id']."<br/>");
	//	echo($email."<< un sub<<");
	//    }


        }
	
	//if($_REQUEST['mm'] == 'yes'){
	//    //echo($addressItem['country_id']."<br/>");
	//    die($email."<<<<");
	//}
        
        $data['status'] = true;
        $data['message'] = '';
        $customersNew = Mage::getModel('customer/customer')->load($customerid);
        foreach ($customersNew->getAddresses() as $addressItem)
        {
	    
            $addressItem = $addressItem->toArray();
        }
	
	
	
	///$addressItem = Mage::getModel('customer/address')->load($newAddress_id);
	    ///$addressItem = $addressItem->toArray();
	    $regionInfo = array(
		'region_id' => '',
		'region' => $addressItem['region']
	    );
	    
	    $addArr = split("\n",$addressItem['street']);
	    if($addArr[0] != "")
	    $add1 = $addArr[0];
	    else
	    $add1 = "";
	    
	    if($addArr[1] != "")
	    $add2 = $addArr[1];
	    else
	    $add2 = "";
	    //if($_new_address)
	    $data['status'] = true;
	    $data['message'] = '';
	    $country_id = $addressItem['country_id'];
	    $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
	    if($countryName == false)
	    $countryName = "";
	    $mmAddress = array(
		'id' => $addressItem['entity_id'],
		'first_name' => $addressItem['firstname'],
		'last_name' => $addressItem['lastname'],
		'email' => $customers->getEmail(),
		'add1' => $add1,
		'add2' => $add2,
		//'street' => $addressItem['street'],
		'city' => $addressItem['city'],
		'postcode' => $addressItem['postcode'],
		'country_id' => $addressItem['country_id'],
		'country_name' => $countryName,
		'region' => $regionInfo,       
		'telephone' => $addressItem['telephone']
	    );
	
	
	//if($_REQUEST['mm'] == 'yes'){
	//	die(var_dump($addressItemNew));
	//}
	
//        $country_id = $addressItem['country_id'];
//	
//        $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
//	
//	if($countryName != "" && $countryName != false)
//        $addressItem['country_name'] = $countryName;
//	else
//	$addressItem['country_name'] = "";

        $data['data'] = array(
                        'id' => $customers->getId(),
                        'first_name' => $customers->getfirstname(),
                        'last_name' => $customers->getlastname(),
                        'email_address' => $customers->getemail(),
                        'news_letter' => $news_letter_val,
                        'gender' => $customers->getgender(),
                        'phone' => $addressItem['telephone'],       
                        'dob' => $customers->getdob(),
                        'product_count' => $count,
                        'default_address' => $mmAddress
                        );
    }else{
        $data['status'] = false;
        $data['message'] = 'Invalid Data';
    }
    //echo "<pre>";
    //var_dump($email);
    //die();
    
    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);

    echo $json_data;
}

if($method == 'editprofile'){
    $customerid = @$_REQUEST['customerid'];
    $firstname = @$_REQUEST['firstname'];
    $lastname = @$_REQUEST['lastname'];
    $gender = @$_REQUEST['gender'];
    $dob = @$_REQUEST['dob'];
    $phone = @$_REQUEST['phone'];
    $newsletter = @$_REQUEST['newsletter'];
    $country_id = @$_REQUEST['country_id'];
    $zip = @$_REQUEST['zip'];
    $city = @$_REQUEST['city'];
    $fax = @$_REQUEST['fax'];
    $company = @$_REQUEST['company'];
    $street = @$_REQUEST['street'];
    $defaultBilling = @$_REQUEST['defaultBilling'];
    $region = @$_REQUEST['region'];
    $region_id = @$_REQUEST['region_id'];
    
    $data['status'] = true;
    $data['message'] = '';
    //if($defaultBilling == "")$defaultBilling = @$_REQUEST['defaultBilling'];
    //$defaultBilling = 0;
    if($street == ""){
	$getAdd1 = @$_REQUEST['add1'];
	$getAdd2= @$_REQUEST['add2'];
	$street[0] = urldecode($getAdd1);
	$street[1] = urldecode($getAdd2);
    }
    
    $defaultShipping = @$_REQUEST['defaultShipping'];
    $saveInAddressBook = @$_REQUEST['saveInAddressBook'];
    //echo "<pre>";
    //var_dump($_REQUEST);
    //die();

    $customers = Mage::getModel('customer/customer')->load($customerid);
    $session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
    $email = $customers->getEmail();
    
    
//    $defaultShipping = $customers->getDefaultShippingAddress();
//    if($defaultShipping){
//	$addressDef = $defaultShipping->toArray();
//    }else{
//	foreach ($customers->getAddresses() as $addressDef)
//	{
//	    if($addressDef->getId() > 0)
//		$addressDef = $addressDef->toArray();
//	    if ($defaultShipping && $defaultShipping->getId() > 0 && $addressDef->getId() == $defaultShipping->getId() && $addressDef->getId() > 0) {
//		break;
//	    }
//	}
//    }
    
    
    
    
    
    if($_REQUEST['mm'] == 'yes'){
	echo "<pre>";
	var_dump($customers->getEmail());
	die("<< sub<<");
    }
    if($email !="" && !filter_var($email, FILTER_VALIDATE_EMAIL) === false){
        $count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
        $customers->setFirstname($firstname); 
        $customers->setLastname($lastname); 
        $customers->setGender($gender); 
        $customers->setDob($dob);
	$customers->setContactno($phone);
        $customers->save();
        if($_REQUEST['mm'] == 'yes'){
	    if($phone != ""){
	        //$customerAddressInfo = Mage::getModel('customer/address');
		$customersNewMM = Mage::getModel('customer/customer')->load($customerid);
		//var_dump($customersNewMM->getAddressesByMM());
	    //    $customerAddressInfo->setTelephone($phone);
	    //    try{
	    //	$customerAddressInfo->save();
	    //    }
	    //    catch (Exception $e) {
	    //	//Zend_Debug::dump($e->getMessage());
	    //	//$data['message'] = $e->getMessage();
	    //    }
	    }
	}
	
	//
	//if ($defaultShippingId = $customers->getDefaultShipping()){
	//    $customerAddress->load($defaultShippingId);
	//    $customerAddressArray = $customerAddress->toArray();
	//    //if($street == "" || empty($street)){
	//       $street = $customerAddressArray['street'];
	//    //}
	//    
	//    if($city == ""){
	//       $city = $customerAddressArray['city'];
	//    }
	//    
	//    if($zip == ""){
	//       $zip = $customerAddressArray['postcode'];
	//    }
	//    
	//    if($country_id == ""){
	//       $country_id = $customerAddressArray['country_id'];
	//    }
	//    
	//    if($company == ""){
	//       $company = $customerAddressArray['company'];
	//    }
	//    
	//    if($region == ""){
	//       $region = $customerAddressArray['region'];
	//    }
	//    
	//    if($region_id == ""){
	//       $region_id = $customerAddressArray['region_id'];
	//    }
	//    
	//    if($phone == ""){
	//       $phone = $customerAddressArray['telephone'];
	//    }
	//     //die(var_dump($customerAddress->toArray()));
	//} else {   
	//     $customerAddress
	//	->setCustomerId($customers->getId())
	//	->setIsDefaultShipping('1')
	//	->setSaveInAddressBook('1')
	//     ;   
	//
	//     $customers->addAddress($customerAddress);
	//}
	//
	//$dataShipping = array(
	//    'firstname'  => $firstname,
	//    'lastname'   => $lastname,
	//    'street'     => $street,
	//    'city'       => $city,
	//    'region'     => $region,
	//    'region_id'  => $region_id,
	//    'postcode'   => $zip,
	//    'country_id' => $country_id,
	//    'telephone'  => $phone,
	//);
	//
	//try {
	//    $customerAddress->addData($dataShipping)->save();           
	//} catch(Exception $e){
	//    Mage::log('Address Save Error::' . $e->getMessage());
	//   //Mage::log('Error .' . $e->getMessage());
	//}

        if($newsletter == 1 && $email != ""){
	    Mage::getModel('newsletter/subscriber')->subscribe($email);
            $news_letter_val = true;
        }else{	    
	    Mage::getModel('newsletter/subscriber')->loadByEmail($email)->unsubscribe();
            $news_letter_val = false;
        }
        
        
        $customersNew = Mage::getModel('customer/customer')->load($customerid);
        foreach ($customersNew->getAddresses() as $addressItem)
        {
            $addressItem = $addressItem->toArray();
        }
	
	if(isset($addressItem['region_id']) && $addressItem['region_id'] !=""){
	    $ItemRegionId = $addressItem['region_id'];
	}else{
	    $ItemRegionId='';
	}
	$regionInfo = array(
	    'region_id' => $ItemRegionId,
	    'region' => $addressItem['region']
	);
	
	$addArr = split("\n",$addressItem['street']);
	if($addArr[0] != "")
	$add1 = $addArr[0];
	else
	$add1 = "";
	
	if($addArr[1] != "")
	$add2 = $addArr[1];
	else
	$add2 = "";
	//if($_new_address)
	
	$country_id = $addressItem['country_id'];
	$countryName = Mage::getModel('directory/country')->load($country_id)->getName();
	if($countryName == false)
	$countryName = "";
	$mmAddress = array(
	    'id' => $addressItem['entity_id'],
	    'first_name' => $addressItem['firstname'],
	    'last_name' => $addressItem['lastname'],
	    'email' => $customers->getEmail(),
	    'add1' => $add1,
	    'add2' => $add2,
	    //'street' => $addressItem['street'],
	    'city' => $addressItem['city'],
	    'postcode' => $addressItem['postcode'],
	    'country_id' => $addressItem['country_id'],
	    'country_name' => $countryName,
	    'region' => $regionInfo,       
	    'telephone' => $addressItem['telephone']
	);

        $data['data'] = array(
	    'id' => $customers->getId(),
	    'first_name' => $customers->getfirstname(),
	    'last_name' => $customers->getlastname(),
	    'email_address' => $customers->getemail(),
	    'news_letter' => $news_letter_val,
	    'gender' => $customers->getgender(),
	    //'phone' => $addressItem['telephone'],
	    'phone' => $customers->getContactno(),       
	    'dob' => $customers->getdob(),
	    'product_count' => $count,
	    //'default_address' => $mmAddress
	);
    }else{
        $data['status'] = false;
        $data['message'] = 'Invalid Data';
    }
    //echo "<pre>";
    //var_dump($email);
    //die();
    
    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);

    echo $json_data;
}
if($method == 'editprofileNew'){
	
	$customerid = $_REQUEST['customerid'];
	$firstname = $_REQUEST['firstname'];
	$lastname = $_REQUEST['lastname'];
	$gender = $_REQUEST['gender'];
	$dob = $_REQUEST['dob'];
	$phone = $_REQUEST['phone'];
	$newsletter = $_REQUEST['newsletter'];

	$customers = Mage::getModel('customer/customer')->load($customerid);
	$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
	
	$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();

	$customers->setFirstname($firstname); 
	$customers->setLastname($lastname); 
	$customers->setGender($gender); 
	$customers->setDob($dob); 
	
	$email = $customers->getemail();
	
	$addressData = array (
		'telephone' => $phone
	);

	$customerAddress = Mage::getModel('customer/address');
	if($customers->getDefaultBilling()){
		$defaultShippingId = $customers->getDefaultShipping();
		$customerAddress->load($defaultShippingId); 
	} else {   
		$customerAddress
			->setCustomerId($customerid)
			->setIsDefaultBilling('1')
			->setIsDefaultShipping('1')
			->setSaveInAddressBook('1');
	}
	
	try {
		$customerAddress->setData($addressData);
		$customerAddress->save();
	} catch (Exception $e) {
		Mage::log('Error .' . $e->getMessage());
	}
	$customers->save();	
	
	if($newsletter == 1){
		Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($email);
		$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
		$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
		$subscriber->save();
	}else{
		Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($email);
		$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
		$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
		$subscriber->save();
	}

	//Get address
	foreach ($customers->getAddresses() as $address)
	{
		$address = $address->toArray();
	}

	$data['status'] = true;
	$data['message'] = '';
	if($customers->getContactno() !="")
	$contactNo = $customers->getContactno();
	else
	$contactNo = $address['telephone'];

	$data['data'] = array(
			'id' => $customers->getId(),
			'first_name' => $customers->getfirstname(),
			'last_name' => $customers->getlastname(),
			'email_address' => $customers->getemail(),
			'news_letter' => '',
			'gender' => $customers->getgender(),
			'phone' => $contactNo,
			'dob' => $customers->getdob(),
			'product_count' => $count,
			'default_address' => $address
		);
		

	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);
	$json_data = str_replace('null','""',$json_data);

	echo $json_data;
}

if($method == 'editprofileNew2'){
	
	$customerid = $_REQUEST['customerid'];
	$firstname = $_REQUEST['firstname'];
	$lastname = $_REQUEST['lastname'];
	$gender = $_REQUEST['gender'];
	$dob = $_REQUEST['dob'];
	$phone = $_REQUEST['phone'];
	$newsletter = $_REQUEST['newsletter'];

	$customers = Mage::getModel('customer/customer')->load($customerid);
	$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
	
	$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();

	$customers->setFirstname($firstname); 
	$customers->setLastname($lastname); 
	$customers->setGender($gender); 
	$customers->setDob($dob); 
	
	$email = $customers->getemail();
	
	$addressData = array (
		'telephone' => $phone
	);

	$customerAddress = Mage::getModel('customer/address');
	if($customers->getDefaultBilling()){
		$defaultShippingId = $customers->getDefaultShipping();
		$customerAddress->load($defaultShippingId); 
	} else {   
		$customerAddress
			->setCustomerId($customerid)
			->setIsDefaultBilling('1')
			->setIsDefaultShipping('1')
			->setSaveInAddressBook('1');
	}
	
	try {
		$customerAddress->setData($addressData);
		$customerAddress->save();
	} catch (Exception $e) {
		Mage::log('Error .' . $e->getMessage());
	}
	$customers->save();	
	
	if($newsletter == 1){
		Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($email);
		$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
		$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
		$subscriber->save();
	}else{
		Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($email);
		$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
		$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED);
		$subscriber->save();
	}

	//Get address
	foreach ($customers->getAddresses() as $address)
	{
		$address = $address->toArray();
	}

	$data['status'] = true;
	$data['message'] = '';

	$data['data'] = array(
			'id' => $customers->getId(),
			'first_name' => $customers->getfirstname(),
			'last_name' => $customers->getlastname(),
			'email_address' => $customers->getemail(),
			'news_letter' => '',
			'gender' => $customers->getgender(),
			'phone' => $address['telephone'],       
			'dob' => $customers->getdob(),
			'product_count' => $count,
			'default_address' => $address
		);
		

	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);
	$json_data = str_replace('null','""',$json_data);

	echo $json_data;
}
if($method == 'addnewaddress'){
	
	$customerid = @$_REQUEST['customerid'];
	$firstname = @$_REQUEST['firstname'];
	$lastname = @$_REQUEST['lastname'];
	$street1 = @$_REQUEST['add1'];
	$street2 = @$_REQUEST['add2'];
	
	$street1 = urldecode($street1);
	$street2 = urldecode($street2);
	
	$city = @$_REQUEST['city'];
	$postcode = @$_REQUEST['postcode'];
	$phone = @$_REQUEST['phone'];
	$region = @$_REQUEST['region'];
	$addEmail = @$_REQUEST['addemail'];
	$country_id = @$_REQUEST['country_id'];
	if($country_id == "")
	$country_id = 'MY';
	
	if(isset($addEmail) && $addEmail !="" && !filter_var($addEmail, FILTER_VALIDATE_EMAIL) === false){
	    $streetArray = array (
			'0' => $street1,
			'1' => $street2,
			'2' => $addEmail,
			);
	}else{
	    $streetArray = array (
			'0' => $street1,
			'1' => $street2
			);
	}

	$_new_address = array (
		'firstname' => $firstname,
		'lastname' => $lastname,
		'street' => $streetArray,
		'city' => $city,
		'region_id' => '',
		'region' => $region,
		'postcode' => $postcode,
		'country_id' => $country_id,
		'telephone' => $phone
	);
	
	$newAddress = Mage::getModel('customer/address');
	$newAddress->setData($_new_address)
            ->setCustomerId($customerid)
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1');
	    
	    
	try {
	    $newAddress->save();
	    //if($_GET['mm'] == 'yes'){
	    $customers = Mage::getModel('customer/customer')->load($customerid);
	    $newAddress_id = $newAddress->getEntityId();
	    $address = Mage::getModel('customer/address')->load($newAddress_id);
	    $address = $address->toArray();
	    $regionInfo = array(
		'region_id' => '',
		'region' => $address['region']
	    );
	    
	    $addArr = split("\n",$address['street']);
	    if($addArr[0] != "")
	    $add1 = $addArr[0];
	    else
	    $add1 = "";
	    
	    if($addArr[1] != "")
	    $add2 = $addArr[1];
	    else
	    $add2 = "";
	    
	    if($addArr[2] != "")
	    $add_email = $addArr[2];
	    else
	    $add_email = $customers->getEmail();
	    
	    //if($_new_address)
	    $data['status'] = true;
	    $data['message'] = '';
	    $country_id = $address['country_id'];
	    $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
	    if($countryName == false)
	    $countryName = "";
	    $data['data'] = array(
		'id' => $address['entity_id'],
		'first_name' => $address['firstname'],
		'last_name' => $address['lastname'],
		'email' => $add_email,
		'add1' => $add1,
		'add2' => $add2,
		//'street' => $address['street'],
		'city' => $address['city'],
		'postcode' => $address['postcode'],
		'country_id' => $address['country_id'],
		'country_name' => $countryName,
		'region' => $regionInfo,       
		'telephone' => $address['telephone']
	    );
		
		//die("<pre>".var_dump($data)."</pre>");
	    //}
	}
	catch(Exception $e){
        $systemError = $e->getMessage();
        $data['status'] = false;
        $data['message'] = $systemError; 
	}

	
		

	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);
	$json_data = str_replace('null','""',$json_data);

	echo $json_data;
}

if($method == 'addnewaddressMM'){
	
	$customerid = @$_REQUEST['customerid'];
	$firstname = @$_REQUEST['firstname'];
	$lastname = @$_REQUEST['lastname'];
	$street1 = @$_REQUEST['add1'];
	$street2 = @$_REQUEST['add2'];
	$city = @$_REQUEST['city'];
	$postcode = @$_REQUEST['postcode'];
	$phone = @$_REQUEST['phone'];
	$region = @$_REQUEST['region'];
	$addEmail = @$_REQUEST['addemail'];
	$country_id = @$_REQUEST['country_id'];
	if($country_id == "")
	$country_id = 'MY';

	$_new_address = array (
		'firstname' => $firstname,
		'lastname' => $lastname,
		'street' => array (
			'0' => $street1,
			'1' => $street2,
			'2' => $addEmail,
		),
		'city' => $city,
		'region_id' => '',
		'region' => $region,
		'postcode' => $postcode,
		'country_id' => $country_id,
		'telephone' => $phone,
		'email' =>$addEmail
	);
	
	$newAddress = Mage::getModel('customer/address');
	$newAddress->setData($_new_address)
            ->setCustomerId($customerid)
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook('1');
	    
	    
	try {
	    $newAddress->save();
	    //if($_GET['mm'] == 'yes'){
	    $customers = Mage::getModel('customer/customer')->load($customerid);
	    $newAddress_id = $newAddress->getEntityId();
	    $address = Mage::getModel('customer/address')->load($newAddress_id);
	    $address = $address->toArray();
	    $regionInfo = array(
		'region_id' => '',
		'region' => $address['region']
	    );
	    
	    $addArr = split("\n",$address['street']);
	    if($addArr[0] != "")
	    $add1 = $addArr[0];
	    else
	    $add1 = "";
	    
	    if($addArr[1] != "")
	    $add2 = $addArr[1];
	    else
	    $add2 = "";
	    
	    if($addArr[2] != "")
	    $add_email = $addArr[2];
	    else
	    $add_email = $customers->getEmail();
	    //if($_new_address)
	    $data['status'] = true;
	    $data['message'] = '';
	    $country_id = $address['country_id'];
	    $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
	    if($countryName == false)
	    $countryName = "";
	    $data['data'] = array(
		'id' => $address['entity_id'],
		'first_name' => $address['firstname'],
		'last_name' => $address['lastname'],
		'email' => $add_email,
		'add1' => $add1,
		'add2' => $add2,
		//'street' => $address['street'],
		'city' => $address['city'],
		'postcode' => $address['postcode'],
		'country_id' => $address['country_id'],
		'country_name' => $countryName,
		'region' => $regionInfo,       
		'telephone' => $address['telephone']
	    );
		
		//die("<pre>".var_dump($data)."</pre>");
	    //}
	}
	catch(Exception $e){
        $systemError = $e->getMessage();
        $data['status'] = false;
        $data['message'] = $systemError; 
	}

	
		

	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);
	$json_data = str_replace('null','""',$json_data);

	echo $json_data;
}


if($method == 'listaddressMM'){
    $customerid = $_REQUEST['customerid'];
    try {
	$data['status'] = true;
	$data['message'] = '';
	
	$customers = Mage::getModel('customer/customer')->load($customerid);
	$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
	//Get address
	
	foreach ($customers->getAddresses() as $address)
	{
	    
	    $addArr = split("\n",$address['street']);
	    if($addArr[0] != "")
	    $add1 = $addArr[0];
	    else
	    $add1 = "";
	    
	    if($addArr[1] != "")
	    $add2 = $addArr[1];
	    else
	    $add2 = "";
	    
	    if($addArr[2] != "")
	    $add_email = $addArr[2];
	    else
	    $add_email = $customers->getEmail();
	    //echo "<pre>";
	    //echo var_dump($customers->getEmail());
	    //echo "<br />";
	    //die();
	    $address = $address->toArray();
	    $regionInfo = array(
		'region_id' => '',
		'region' => $address['region']
	    );
	    
	    $country_id = $address['country_id'];
	    $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
	    
	    //die(var_dump($address['email']));
	    //if(isset($address['email']) && $address['email'] != "")
	    //$add_email = $address['email'];
	    //else
	    //$add_email = $customers->getEmail();
	    
	    $data['data'][] = array(
		'id' => $address['entity_id'],
		'first_name' => $address['firstname'],
		'last_name' => $address['lastname'],
		'email' => $add_email,
		'add1' => $add1,
		'add2' => $add2,
		//'street' => $address['street'],
		'city' => $address['city'],
		'postcode' => $address['postcode'],
		'country_id' => $address['country_id'],
		'country_name' => $countryName,
		'region' => $regionInfo,
		'telephone' => $address['telephone']
	    );
	}
    }
    catch(Exception $e){
	$systemError = $e->getMessage();
	$data['status'] = false;
	$data['message'] = $systemError; 
    }
    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);

    echo $json_data;
}

if($method == 'defaultaddress'){
	
	$customerid = $_REQUEST['customerid'];
	$addressid = $_REQUEST['addressid'];

	try {

	    $data['status'] = true;
	    $data['message'] = '';
	    
	    $customers = Mage::getModel('customer/customer')->load($customerid);
	    //Get address
	    foreach ($customers->getAddresses() as $address)
	    {
		$address = $address->toArray();
		if($address['entity_id'] == $addressid) {
		    
		    $addressInfo = Mage::getModel('customer/address')->load($addressid);
		    $addressInfo->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
		    
		    $regionInfo = array(
			    'region_id' => '',
			    'region' => $address['region']
		    );
		    
		    $addArr = split("\n",$address['street']);
		    if($addArr[0] != "")
		    $add1 = $addArr[0];
		    else
		    $add1 = "";
		    
		    if($addArr[1] != "")
		    $add2 = $addArr[1];
		    else
		    $add2 = "";
		    $country_id = $address['country_id'];
		    $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
		    $data['data'][] = array(
				    'id' => $address['entity_id'],
				    'first_name' => $address['firstname'],
				    'last_name' => $address['lastname'],
				    'email' => $customers->getEmail(),
				    'add1' => $add1,
				    'add2' => $add2,
				    //'street' => $address['street'],
				    'city' => $address['city'],
				    'postcode' => $address['postcode'],
				    'country_id' => $address['country_id'],
				    'country_name' => $countryName,
				    'region' => $regionInfo,
				    'telephone' => $address['telephone']
				    );
		}
	    }
	}
	catch(Exception $e){
        $systemError = $e->getMessage();
        $data['status'] = false;
        $data['message'] = $systemError; 
    }

	
		

	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);
	$json_data = str_replace('null','""',$json_data);

	echo $json_data;
}


if($method == 'listaddress'){
    $customerid = $_REQUEST['customerid'];
    try {
	$data['status'] = true;
	$data['message'] = '';
	
	$customers = Mage::getModel('customer/customer')->load($customerid);
	$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
	//Get address
	
	foreach ($customers->getAddresses() as $address)
	{
	    
	    $addArr = split("\n",$address['street']);
	    if($addArr[0] != "")
	    $add1 = $addArr[0];
	    else
	    $add1 = "";
	    
	    if($addArr[1] != "")
	    $add2 = $addArr[1];
	    else
	    $add2 = "";
	    
	    if($addArr[2] != "")
	    $add_email = $addArr[2];
	    else
	    $add_email = $customers->getEmail();
	    //echo "<pre>";
	    //echo var_dump($customers->getEmail());
	    //echo "<br />";
	    //die();
	    $address = $address->toArray();
	    $regionInfo = array(
		'region_id' => '',
		'region' => $address['region']
	    );
	    
	    $country_id = $address['country_id'];
	    $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
	    
	    $data['data'][] = array(
		'id' => $address['entity_id'],
		'first_name' => $address['firstname'],
		'last_name' => $address['lastname'],
		'email' => $add_email,
		'add1' => $add1,
		'add2' => $add2,
		//'street' => $address['street'],
		'city' => $address['city'],
		'postcode' => $address['postcode'],
		'country_id' => $address['country_id'],
		'country_name' => $countryName,
		'region' => $regionInfo,
		'telephone' => $address['telephone']
	    );
	}
    }
    catch(Exception $e){
	$systemError = $e->getMessage();
	$data['status'] = false;
	$data['message'] = $systemError; 
    }
    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);

    echo $json_data;
}

if($method == 'deleteaddress'){
	
	$customerid = $_REQUEST['customerid'];
	$addressid = $_REQUEST['addressid'];
	$data['status'] = true;
	$data['message'] = '';
	
	$customers = Mage::getModel('customer/customer')->load($customerid);
	$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
	
	//$address = Mage::getModel('customer/address')->load($addressid);
	//$customer = $address->getCustomer();
	
	$defaultBilling = $customers->getDefaultBillingAddress();
	$defaultShipping = $customers->getDefaultShippingAddress();
	if ($defaultBilling) {
	    if ($defaultBilling->getId() == $addressid) {
		//is default billing
		$newDefAdd="";
		foreach ($customers->getAddresses() as $chckAddress)
		{
			//continue; // we only want to set first address of the customer as default billing address
		    if($chckAddress->getId() > 0 && $chckAddress->getId() != $addressid){
			$newDefAdd = $chckAddress->getId();
			break;
		    }
		}
		if($newDefAdd > 0){
		    //$chckAddress->setIsDefaultBilling('1')->save();
		    $chckAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
		    //$chckAddress->setIsDefaultBilling(true);
		    $removeAdd = Mage::getModel('customer/address')->load($addressid)->delete();
		}else{
		    //echo "111";
		    $data['status'] = false;
		    $data['message'] = 'Address list cannot be empty. The list should have at least one address.';
		}
	    } else {
		if ($defaultShipping) {
		    if ($defaultShipping->getId() == $addressid) {
			//is default shipping
			$newDefAdd="";
			foreach ($customers->getAddresses() as $chckAddress)
			{
			    if($chckAddress->getId() > 0 && $chckAddress->getId() != $addressid){
				$newDefAdd = $chckAddress->getId();
				break;
			    }
			}
			if($newDefAdd > 0){
			    //$chckAddress->setIsDefaultShipping(true);
			   //$chckAddress->setIsDefaultShipping('1')->save();
			   $chckAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
			    $removeAdd = Mage::getModel('customer/address')->load($addressid)->delete();
			}else{
			    $data['status'] = false;
			    $data['message'] = 'Address list cannot be empty. The list should have at least one address.';
			}
		    } else {
			$removeAdd = Mage::getModel('customer/address')->load($addressid)->delete();
		    }
		} else {
		    $removeAdd = Mage::getModel('customer/address')->load($addressid)->delete();
		}
	    }
	} else {
	    if ($defaultShipping) {
		if ($defaultShipping->getId() == $addressid) {
		    //is default shipping
		    $newDefAdd="";
		    foreach ($customers->getAddresses() as $chckAddress)
		    {
			if($chckAddress->getId() > 0 && $chckAddress->getId() != $addressid){
			    $newDefAdd = $chckAddress->getId();
			    break;
			}
		    }
		    if($newDefAdd > 0){
			//$chckAddress->setIsDefaultShipping(true);
			//$chckAddress->setIsDefaultShipping('1')->save();
			$chckAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
			$removeAdd = Mage::getModel('customer/address')->load($addressid)->delete();
		    }else{
			$data['status'] = false;
			$data['message'] = 'Address list cannot be empty. The list should have at least one address.';
		    }
		} else {
		    $removeAdd = Mage::getModel('customer/address')->load($addressid)->delete();
		}
	    } else {
		$removeAdd = Mage::getModel('customer/address')->load($addressid)->delete();
	    }
	}
	
	if(!$defaultShipping || !($defaultShipping->getId() > 0)){
	    $newDefAdd="";
	    foreach ($customers->getAddresses() as $chckAddress)
	    {
		if($chckAddress->getId() > 0 && $chckAddress->getId() != $addressid){
		    $newDefAdd = $chckAddress->getId();
		    //
		    //echo "hello $newDefAdd >>>";
		    //$chckAddress->setIsDefaultShipping(true);
		    //$chckAddress->setIsDefaultShipping('1')->save();
		    $chckAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
		    break;
		}
	    }
	//    if($newDefAdd > 0){
	//	$chckAddress->setIsDefaultShipping(true);
	//    }
	}
	
	
	
	//if ( ! $customers->getDefaultBillingAddress() ) {
	//	foreach ($customers->getAddresses() as $addressMM) {
	//		//$addressMM->setIsDefaultBilling(true);
	//		$chckAddress->setIsDefaultBilling('1')->save();
	//		continue; // we only want to set first address of the customer as default billing address
	//	}
	//}
	//if ( ! $customers->getDefaultShippingAddress() ) {
	//	foreach ($customers->getAddresses() as $addressMM) {
	//		//$addressMM->setIsDefaultShipping(true);
	//		$chckAddress->setIsDefaultShipping('1')->save();
	//		continue; // we only want to set first address of the customer as default shipping address
	//	}
	//}
	
	if($_REQUEST['mm'] == "yes"){
	    die(">>>>".$newDefAdd);
	    //$chckAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
	}

	try {
	    $customers = Mage::getModel('customer/customer')->load($customerid);
	    $addressID = $customers->getDefaultShipping();
	    $address = Mage::getModel('customer/address')->load($addressID);
	    $address = $address->toArray();
	    //die("<pre>".var_dump($address)."</pre>");
	    $regionInfo = array(
		'region_id' => '',
		'region' => $address['region']
	    );
	    $addArr = split("\n",$address['street']);
	    if($addArr[0] != "")
	    $add1 = $addArr[0];
	    else
	    $add1 = "";
	    
	    if($addArr[1] != "")
	    $add2 = $addArr[1];
	    else
	    $add2 = "";
	    
	    $country_id = $address['country_id'];
	    $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
	    if($countryName == false)
	    $countryName = "";
	    $data['data'][] = array(
		'id' => $address['entity_id'],
		'first_name' => $address['firstname'],
		'last_name' => $address['lastname'],
		'email' => $customers->getEmail(),
		'add1' => $add1,
		'add2' => $add2,
		//'street' => $address['street'],
		'city' => $address['city'],
		'postcode' => $address['postcode'],
		'country_id' => $address['country_id'],
		'country_name' => $countryName,
		'region' => $regionInfo,
		'telephone' => $address['telephone']
	    );
		
		//Get address
		//foreach ($customers->getAddresses() as $address)
		//{
		//    $address = $address->toArray();
		//    
		//    $regionInfo = array(
		//	    'region_id' => '',
		//	    'region' => $address['region']
		//    );
		//    $addArr = split("\n",$address['street']);
		//    if($addArr[0] != "")
		//    $add1 = $addArr[0];
		//    else
		//    $add1 = "";
		//    
		//    if($addArr[1] != "")
		//    $add2 = $addArr[1];
		//    else
		//    $add2 = "";
		//    $data['data'][] = array(
		//	'id' => $address['entity_id'],
		//	'first_name' => $address['firstname'],
		//	'last_name' => $address['lastname'],
		//	'email' => $customers->getEmail(),
		//	'add1' => $add1,
		//	'add2' => $add2,
		//	//'street' => $address['street'],
		//	'city' => $address['city'],
		//	'postcode' => $address['postcode'],       
		//	'region' => $regionInfo,
		//	'telephone' => $address['telephone']
		//    );
		//}
	}
	catch(Exception $e){
	    $systemError = $e->getMessage();
	    $data['status'] = false;
	    $data['message'] = $systemError; 
	}
	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);
	$json_data = str_replace('null','""',$json_data);
	echo $json_data;
}
//sample url:  http://staging.trendycounty.com/mobileapi/profile.php?key=5pkmEyb8TPhx7eex87BMxcY77vyhhnqXAzUQHVPt&method=getprofile&customerid=49


/*
$address = Mage::getModel("customer/address");
$address->setCustomerId($customers->getId())
        ->setFirstname($customers->getFirstname())
        ->setMiddleName($customers->getMiddlename())
        ->setLastname($customers->getLastname())
        ->setCountryId('HR')
		//->setRegionId('1') //state/province, only needed if the country is USA
        ->setPostcode('31000')
        ->setCity('Osijek')
        ->setTelephone('0038511223344')
        ->setFax('0038511223355')
        ->setCompany('Inchoo')
        ->setStreet('Kersov')
        ->setIsDefaultBilling('1')
        ->setIsDefaultShipping('1')
        ->setSaveInAddressBook('1');
 
try{
    $address->save();
}
catch (Exception $e) {
    Zend_Debug::dump($e->getMessage());
}



$customer   ->setWebsiteId($websiteId)
            ->setStore($store)
            ->setGroupId(2)
            ->setPrefix('Sir')
            ->setFirstname('John')
            ->setMiddleName('2')
            ->setLastname('Doe')
            ->setSuffix('II')
            ->setEmail('jd2@ex.com')
            ->setPassword('somepassword');
 */
