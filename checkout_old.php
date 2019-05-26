<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();

Mage::init('default');  
Mage::getSingleton('core/session', array('name' => 'frontend')); 

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://trendycounty.com/';
$mainURLApi = 'http://trendycounty.com/mobileapi/';

$method = $_REQUEST['method'];
$email = $_REQUEST['email'];

$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

if($method == 'placeorder'){
    $quote = Mage::getSingleton('checkout/session')->getQuote();
    if($quote->getId() > 0){
	$cartItems = $quote->getAllVisibleItems();
	$quoteData = $quote->getData();
	
	//http://trendycounty.com/mobileapi/checkout.php?key=5pkmEyb8TPhx7eex87BMxcY77vyhhnqXAzUQHVPt&method=placeorder&email=mohamad@optima.com.my
	$shippingMethod = $_REQUEST['shippingmethod'];
	    //Shipping / Billing information gather
	$firstName = $customer->getFirstname(); //get customers first name
	$lastName = $customer->getLastname(); //get customers last name
	$customerEmail = $customer->getEmail(); 
	if($customer->getId() > 0){    
	    $customerBillingAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling(); //get default billing address from session
	    
	    $customerShippingAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
	    if($_REQUEST['mm'] == 'yes'){
		    echo $customerShippingAddressId."<<<<".$customerBillingAddressId;
		    die();
	    }
	    //if we have a default billing addreess, try gathering its values into variables we need
	    if ($customerBillingAddressId > 0 && $customerShippingAddressId > 0){ 
		$address = Mage::getModel('customer/address')->load($customerBillingAddressId);
		$street = $address->getStreet();
		$city = $address->getCity();
		$postcode = $address->getPostcode();
		$phoneNumber = $address->getTelephone();
		$countryId = $address->getCountryId();
		$regionId = $address->getRegion();
		
		$addArr = split("\n",$address->getStreet());
		if($addArr[0] != "")
		$add1 = $addArr[0];
		else
		$add1 = "";
		
		if($addArr[1] != "")
		$add2 = $addArr[1];
		else
		$add2 = "";
	    
	 
		//$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app('default')->getStore('default')->getId()); 
		//please don't re-add this, its to show you where in the above script you would include this below cart check
		//$quote->assignCustomer($customer); //please don't re-add this, its to show you where in the above script you would include this below cart check
		
		     
		//Now we must loop over all the found items in the cart and scrub each one against our incoming product id
		/*foreach ($items as $item) {//<- plural to singular , becareful
			$item_to_product = Mage::getModel('catalog/product')->loadByAttribute('name',$item->getName());
			if($item_to_product->getId() == $product_id){
				$foundProduct = 'true';
			}
		 
		}*/
	 
		//Low lets setup a shipping / billing array of current customer's session
		$addressData = array(
		    'firstname' => $firstName,
		    'lastname' => $lastName,
		    'street' => $street,
		    'city' => $city,
		    'postcode'=>$postcode,
		    'telephone' => $phoneNumber,
		    'country_id' => $countryId,
		    'region_id' => $regionId
		);
		
		if($_REQUEST['mm'] == 'yes'){
		    $shippingAddress = $order->getShippingAddress();
		    
		    $regionInfo = array(
			'region_id' => '',
			'region' => $shippingAddress->getRegion()
		    );
		    
		    $streetArr = $shippingAddress->getStreet();
		    $addArr = $streetArr; //split("\n",$address_shipping['street']);
		    if($addArr[0] != "")
		    $add1 = $addArr[0];
		    else
		    $add1 = "";
		    
		    if($addArr[1] != "" && $addArr[0] != $addArr[1])
		    $add2 = $addArr[1];
		    else
		    $add2 = "";
		    
		    $country_id = $shippingAddress->getcountry_id(); //$address_shipping['country_id'];
		    $countryName = Mage::getModel('directory/country')->load($country_id)->getName();
		    
		    $shipping_address = array(
			'id' => $shippingAddress->getentity_id(),
			'first_name' => $shippingAddress->getfirstname(),
			'last_name' => $shippingAddress->getlastname(),
			'email' => $customers->getEmail(),
			'add1' => $add1,
			'add2' => $add2,
			//'street' => $address['street'],
			'city' => $shippingAddress->getcity(),
			'postcode' => $shippingAddress->getpostcode(),
			'country_id' => $shippingAddress->getcountry_id(),
			'country_name' => $countryName,
			'region' => $regionInfo,       
			'telephone' => $shippingAddress->gettelephone()
		    );
		    
		    die(var_dump($shipping_address));
		}
		
		
		//Add address array to both billing AND shipping address objects.   
		$billingAddress = $quote->getBillingAddress()->addData($addressData);
		
		$shippingAddress = $quote->getShippingAddress()->addData($addressData);
		
		
		$mmMessage="";
		if($billingAddress->getpostcode() == "" && $billingAddress->getstreet() == "" && $billingAddress->getcity() == "" && $billingAddress->getcountry_id() == "" && $billingAddress->gettelephone() == ""){
		    $mmMessage .="Billing address is not set yet";
		    $data['status'] = false;
		    $data['message'] = $mmMessage;
		    $data['data'] = array();
		}elseif($shippingAddress->getpostcode() == "" && $shippingAddress->getstreet() == "" && $shippingAddress->getcity() == "" && $shippingAddress->getcountry_id() == "" && $shippingAddress->gettelephone() == ""){
		    $mmMessage .="Shipping address is not set yet";
		    $data['status'] = false;
		    $data['message'] = $mmMessage;
		    $data['data'] = array();
		}else{
		    
		    //Set shipping objects rates to true to then gather any accrued shipping method costs a product main contain
		    $shippingAddress->setCollectShippingRates(true)
				    ->collectShippingRates()
				    ->setShippingMethod('flatrate_flatrate')
				    ->setPaymentMethod('molpay');
		//    if($_REQUEST['mm']=='yes'){
		//	echo "<pre>";
		//	var_dump($quote->getId());
		//	echo "<br/>";
		//	var_dump($shippingAddress);
		//	die();
		//    }
		    //Set quote object's payment method to check / money order to allow progromatic entries of orders 
		    //(kind of hard to programmatically guess and enter a customer's credit/debit cart so only money orders are allowed to be entered via api) 
		    $quote->getPayment()->importData(array('method' => 'molpay'));
		    $quote->setSendCconfirmation(1);
		    
		    //Save collected totals to quote object
		    $quote->collectTotals()->save();
		//    if($_GET['mm'] == 'yes'){
		//	try {
		//	    var_dump($quote->getorig_order_id());
		//	}
		//	catch (Exception $ex) {
		//	    echo $ex->getMessage();
		//	}
		//	die();//var_dump($service->getOrder()));
		//    }
		 
		    //Feed quote object into sales model
		    $service = Mage::getModel('sales/service_quote', $quote);
		    
		    
		 
		    //submit all orders to MAGE
		    $service->submitAll();
		    
			
		    $increment_id = $service->getOrder()->getRealOrderId();
			
		    $cart = Mage::getSingleton('checkout/cart');
			
		    $order = Mage::getModel('sales/order')->loadByIncrementId( $increment_id );
		    $orderId = $order->getId();
			
		    $currency_code = $order->getBaseCurrencyCode();
		    //$amount = $order->getBaseGrandTotal();
		    $amount = $order->getGrandTotal();
		    $amount = number_format( round(  $amount, 2 ) , 2, '.', '');
		    
		    $address = $order->getBillingAddress();
		    $shippingaddress = $order->getShippingAddress();
		    
		    $email = $billingAddress->getEmail(); 
		    if( $email == '' ) {
			$email = $order->getEmail();
			if($email == "")
			$email = $customerEmail;
		    }
		    /*
		     *      "id": "27",
      "first_name": "Mohd Noor Shuhailey",
      "last_name": "Abdul Majid",
      "street": "No 985, Lorong 50, Kampung Melayu Aulong\nNo 985, Lorong 50, Kampung Melayu Aulong",
      "city": "Taiping",
      "postcode": "34000",
      "region": {
        "region_id": "",
        "region": "Perak"
      },
      "telephone": "0128986765",
      "email": "mohd@gmail.com",
      "country": {
        "country_id": "1",
        "country_name": "Malaysia"
      }

		     */
		    $shippingCost = 0;
		//    $shippingAddress = array(
		//	'first_name' =>
		//			     
		//			     );
		    
		    
		    $data['status'] = true;
		    $data['message'] = '';
		    $data['data'] = array(
			    'orderid' => $increment_id,
			    'amount' => $amount, 
			    'currency_code' => 'MYR',
			    'bill_name' => $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
			    'bill_email' => $email,
			    'bill_mobile' => $billingAddress->getTelephone(),
			    'bill_desc' => 'Order from Mobile App - TrendyCounty',
			    'shipping' => $shippingCost,
			    'shipping_address' => $shippingAddress,
			    
		    );
		}		    
	    // otherwise, setup some custom entry values so we don't have a bunch of confusing un-descriptive orders in the backend
	    }else{
		//$address = $_REQUEST['address'];
		//$street = $_REQUEST['street'];
		//$city = $_REQUEST['city'];
		//$postcode = $_REQUEST['postcode'];
		//$phoneNumber = $_REQUEST['phone'];
		//$countryId = 'MY';
		//$regionId = $_REQUEST['region'];
		$data['status'] = false;
		if(!($customerShippingAddressId > 0))
		$data['message'] = 'Default Shipping Address is not set. Please set Default Shipping Address first.';
		else
		$data['message'] = 'Default Billing Address is not set. Please set Default Billing Address first.';
		$data['data'] = array();
		
	    }
	    
	}else{
	    $data['status'] = false;
	    $data['message'] = 'Customer is not verified.';
	    $data['data'] = array();
	}
    }else{
	$data['status'] = false;
	$data['message'] = 'No quote is found.';
	$data['data'] = array();
    }
	

	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);
	$json_data = str_replace('null','""',$json_data);
	echo $json_data;
	
	$clear = Mage::getSingleton('checkout/session')->clear();
    //Setup order object and gather newly entered order
    //$order = $service->getOrder();
 
    //Now set newly entered order's status to complete so customers can enjoy their goods. 
        //(optional of course, but most would like their orders created this way to be set to complete automagicly)
    //$order->setStatus('complete');
 
    //Finally we save our order after setting it's status to complete.
    //$order->save();      

}

if($method == 'shippingcost'){
	// Change to your postcode / country. addressid=27&email=mohamad@optima.com.my
$addressid = $_REQUEST['addressid'];
//$customerid = $_REQUEST['customerid'];
$country = 'MY';
$data['status'] = true;
$data['message'] = '';
	$customers = Mage::getModel('customer/customer')->load($customer->getId());
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
			
			$postcode = $address['postcode'];
			
			$country_id2 = $address['country_id']; //$address_shipping['country_id'];
			$countryName2 = Mage::getModel('directory/country')->load($country_id2)->getName();
			
			$streetArr2 = $address['street'];
			$addArr2 = $streetArr2; //split("\n",$address_shipping['street']);
			if($addArr2[0] != "")
			$add12 = $addArr2[0];
			else
			$add12 = "";
			
			if($addArr2[1] != "" && $addArr2[0] != $addArr2[1])
			$add22 = $addArr2[1];
			else
			$add22 = "";
			
			
			$finaleAddress = array(
					'id' => $address['entity_id'],
					'first_name' => $address['firstname'],
					'last_name' => $address['lastname'],
					'email' => $customers->getEmail(),
					'add1' => $add12,
					'add2' => $add22,
					//'street' => $address['street'],
					'city' => $address['city'],
					'postcode' => $address['postcode'],
					'country_id' => $address['country_id'],
					'country_name' => $countryName2,
					'region' => $regionInfo,
					'telephone' => $address['telephone']
				);
			$country = $address['country_id'];
			}
			
		}

// Update the cart's quote.
$cart = Mage::getSingleton('checkout/cart');
$addressShip = $cart->getQuote()->getShippingAddress();
$addressShip->setCountryId($country)->setPostcode($postcode)->setCollectShippingrates(true);
$cart->save();

// Find if our shipping has been included.
$rates = $addressShip->collectShippingRates()->getGroupedAllShippingRates();
$price = 0;
foreach ($rates as $carrier) {
    foreach ($carrier as $rate) {
        $price = $rate->getPrice();
	//echo $price."<<<< <br />";
    }
}

//$data['data'] = 10;
$data['data'][]= array(
		       'shipping_cost' => number_format($price, 2),
		       'address' => $finaleAddress
			);

$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);
echo $json_data;
}




?>