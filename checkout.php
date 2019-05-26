<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();
include('lib/global.php');

Mage::init('default');  
Mage::getSingleton('core/session', array('name' => 'frontend')); 
if($_REQUEST['mm']!='yes'){
header('Content-Type: application/json; Charset=UTF-8');
}
$mainURL = 'http://tcstaging.trendycounty.com/';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';

$method = $_REQUEST['method'];
$email = $_REQUEST['email'];

$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

if($method == 'placeorder'){
    $quote = Mage::getSingleton('checkout/session')->getQuote();
    if($quote->getId() > 0){
	$cartItems = $quote->getAllVisibleItems();
	$quoteData = $quote->getData();
	
	//http://tcstaging.trendycounty.com/mobileapi/checkout.php?key=5pkmEyb8TPhx7eex87BMxcY77vyhhnqXAzUQHVPt&method=placeorder&email=mohamad@optima.com.my
	$shippingMethod = $_REQUEST['shippingmethod'];
	    //Shipping / Billing information gather
	$firstName = $customer->getFirstname(); //get customers first name
	$lastName = $customer->getLastname(); //get customers last name
	$customerEmail = $customer->getEmail(); 
	if($customer->getId() > 0){    
	    $customerBillingAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling(); //get default billing address from session
	    
	    $customerShippingAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
	//    if($_REQUEST['mm'] == 'yes'){
	//	    echo $customerShippingAddressId."<<<<".$customerBillingAddressId;
	//	    die();
	//    }
	    //if we have a default billing addreess, try gathering its values into variables we need
	    if ($customerBillingAddressId > 0 && $customerShippingAddressId > 0){ 
		$address = Mage::getModel('customer/address')->load($customerBillingAddressId);
		$street = $address->getStreet();
		$city = $address->getCity();
		$postcode = $address->getPostcode();
		$phoneNumber = $address->getTelephone();
		$countryId = $address->getCountryId();
		$regionId = $address->getRegion();
		if($address->getRegionId())
		$region_id = $address->getRegionId();
		else
		$region_id = "";
		
		if($address->getRegion())
		$region_name = $address->getRegion();
		else
		$region_name = "";
		
		$addArr = split("\n",$address->getStreet());
		if($addArr[0] != "")
		$add1 = $addArr[0];
		else
		$add1 = "";
		
		if($addArr[1] != "")
		$add2 = $addArr[1];
		else
		$add2 = "";
	 
		//Low lets setup a shipping / billing array of current customer's session
		$addressBillingData = array(
		    'firstname' => $firstName,
		    'lastname' => $lastName,
		    'street' => $street,
		    'city' => $city,
		    'postcode'=>$postcode,
		    'telephone' => $phoneNumber,
		    'country_id' => $countryId,
		    'region_id' => $region_id,
		    'region' => $region_name
		);
		
		if($_REQUEST['mm'] == 'yes'){
		    
		}
		
		
		//Add address array to both billing AND shipping address objects.   
		$billingAddress = $quote->getBillingAddress()->addData($addressBillingData);
		
		
		
		$address2 = Mage::getModel('customer/address')->load($customerShippingAddressId);
		$street2 = $address2->getStreet();
		$city2 = $address2->getCity();
		$postcode2 = $address2->getPostcode();
		$phoneNumber2 = $address2->getTelephone();
		$countryId2 = $address2->getCountryId();
		$regionId2 = $address2->getRegion();
		$phone2 = $address2->gettelephone();
		
		
		
		$addArr2 = split("\n",$street2);
		if($street2[0] != "")
		$add12 = $street2[0];
		else
		$add12 = "";
		
		if($street2[1] != "")
		$add22 = $street2[1];
		else
		$add22 = "";
		if($address2->getRegionId() > 0)
		$region_id2 = $address2->getRegionId();
		else
		$region_id2 = "";
		
		if($address2->getRegion() > 0)
		$region_name2 = $address2->getRegion();
		else
		$region_name2 = "";
		
		
		if($address2->getRegionId())
		$region_id2 = $address2->getRegionId();
		else
		$region_id2 = "";
		
		if($address2->getRegion())
		$region_name2 = $address2->getRegion();
		else
		$region_name2 = "";
		
		if($countryId2 != "")
		$countryName2 = Mage::getModel('directory/country')->load($countryId2)->getName();
		else
		$countryName2="";
		$regionInfo2 = array(
		    'region_id' => $region_id2,
		    'region' => $address2->getRegion()
		);
		$countryInfo2 = array(
		    'country_id' => $countryId2,
		    'country_name' => $countryName2,
		);
		
		/*
		 *{
      "id": "328",
      "first_name": "Kelvyn",
      "last_name": "Lawe",
      "email": "kelvynlaw86@gmail.com",
      "add1": "",
      "add2": "",
      "city": "Selangor",
      "postcode": "47300",
      "country_id": "MY",
      "country_name": "Malaysia",
      "region": {
        "region_id": "",
        "region": "Selangor"
      },
      "telephone": "0126573912"
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
    }
		 *
		 */
	 
		//Low lets setup a shipping / billing array of current customer's session
		$addressShippingData = array(
		    'firstname' => $firstName,
		    'lastname' => $lastName,
		    'street' => $street2,
		    //'add1' => $add12,
		    //'add2' => $add22,
		    'city' => $city2,
		    'postcode'=>$postcode2,
		    'telephone' => $phoneNumber2,
		    'country_id' => $countryId2,
		    'region_id' => $region_id2,
		    'region' => $region_name2,
		    'telephone' =>$phone2
		);
		
		
		$addressShippingDataForFeed = array(
		    'first_name' => $firstName,
		    'last_name' => $lastName,
		    'email' => $email,
		    'add1' => $add12,
		    'add2' => $add22,
		    'city' => $city2,
		    'postcode' => $postcode2,
		    'country_id' => $countryId2,
		    'country_name' => $countryName2,
		    'region' => $regionInfo2,
		    'telephone' => $phoneNumber2
		);
		
		$shippingAddress = $quote->getShippingAddress()->addData($addressShippingData);
		
		
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
		    if($_REQUEST['mm']=='yes'){
			//echo "<pre>";
			//var_dump($quote->getId());
			//echo "<br/>";
			//var_dump($shippingAddress->getpostcode());
			//echo "<br/>";
			//var_dump($shippingAddress->getstreet());
			//echo "<br/>";
			//var_dump($shippingAddress->getcity());
			//echo "<br/>";
			//var_dump($shippingAddress->getCountryId());
			//echo "<br/>";
			//var_dump($shippingAddress->gettelephone());
			//echo "<br/>";
			//var_dump($shippingAddress->getId());
			//die();
		    }
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
		    
		    
		    $getSubtotal = $order->getSubtotal();
		    $getSubtotal = number_format( round(  $getSubtotal, 2 ) , 2, '.', '');
		    
		    
		    $orderItems = $order->getItemsCollection()
			    ->addAttributeToSelect('*')
			    ->load();
		    //print '<pre>';
		    //print_r($order);
		    $items = array();
		    //$productInfo = array();
		    foreach($orderItems as $item)
		    {
			    //print '<pre>';
			    //print_r($items->getId());
			    //$item = Mage::getModel('catalog/product')->setStoreId($items->getStoreId())->load($items->getId());
			    //print $item->getId().'<br>';
	    
			    $product = Mage::getModel('catalog/product')->load($item->getProductId());
			    if($_REQUEST['mm']=='yes'){
				var_dump($item->getData());
				echo "<<<<<<br/>";
			    }
			    $cats = $product->getCategoryIds();
				    
			    foreach ($cats as $category_id) {
				    $_cat = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($category_id);
				    $categoryID = $category_id;             
				    $categoryName = $_cat->getName();             
			    }
				    
			    $categoryParent = array(
				    'id' => $categoryID,
				    'name' => $categoryName
			    );
				    
			    $categoryChild = array(
				    'id' => $categoryID,
				    'name' => $categoryName
			    );
				    
			    $image = array($product->getImageUrl());
			    
			//    $colorOptions="";
			//    $sizeOptions="";
			//    $attributes = $product->getAttributes();
			//    foreach ($attributes as $attribute) {
			//	if($attribute->getAttributeCode() == "color_lee_cooper"){
			//	    $colorOptions = $attribute->getSource()->getAllOptions(false);
			//	}
			//	
			//	if($attribute->getAttributeCode() == "size_lee_cooper"){
			//	    $sizeOptions = $attribute->getSource()->getAllOptions(false);
			//	}
			//    }
			    //if($colorOptions !="" && !empty($colorOptions))
			    //$color = $colorOptions;
			    //else
			//    $colorOptions="";
			//    $sizeOptions="";
			//    $attributes = $product->getAttributes();
			//    foreach ($attributes as $attribute) {
			//	if($attribute->getAttributeCode() == "color_lee_cooper"){
			//	    $colorOptions = $attribute->getSource()->getAllOptions(false);
			//	}
			//	
			//	if($attribute->getAttributeCode() == "size_lee_cooper"){
			//	    $sizeOptions = $attribute->getSource()->getAllOptions(false);
			//	}
			//    }
			//    if($colorOptions !="" && !empty($colorOptions))
			//    $color = $colorOptions;
			//    else
			//    $color = array();
			//    
			//    if($sizeOptions !="" && !empty($sizeOptions))
			//    $size = $sizeOptions;
			//    else
			//    $size = array();
			
			$size = get_final_product_size_mm($product);
			if($size == "")
			$size = array();
			
			$color = get_final_product_color_mm($product);
			if($color == "")
			$color = array();
			//$productType = $_product->getTypeID();
			//$variants = get_associated_products($product);
				    
			    if($customerid != ''){
				    $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerid, true);
				    $wishListItemCollection = $wishlist->getItemCollection_MM();
	    
				    $productID = $item->getProductId();
	    
				    foreach ($wishListItemCollection as $ProductItem):
					if($ProductItem->getProductId() == $productID){
					    $isLike = true;
					}else{
					    $isLike = false;
					}
				    endforeach;
	    
			    }
				    
			    $productInfo = array(
				    'productid' => $item->getProductId(),
				    'productsku' => $product->getSKU(),
				    'name' => $item->getName(),
				    'price' => number_format($item->getPrice(), 2),
				    'category' => $categoryParent,
				    'subcategory' => $categoryChild,
				    'images' => $image,
				    'is_like' => $isLike,
				    'size' => $size,
				    'color' => $color,
				    'description' => $product->getDescription(),
				    'share_url' => $baseURL.$product->getUrlPath()
				    //'share_url' => $mainURLApi.'shareproduct.php?productid='.$item->getProductId()
			    );
			    
			    $items[] = array(
				    'quantity' => floor($item->getQtyOrdered()),
				    'discount_amount' => number_format($item->getdiscount_amount(), 2),
				    'date_added' => $order->getCreatedAt(),
				    'product' => $productInfo
			    );
	    
		    }
		    
		    
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
		    //$shippingCost = 0;
		//    $shippingAddress = array(
		//	'first_name' =>
		//			     
		//			     );
		
		$cart_new = Mage::getSingleton('checkout/cart');
		$addressShip = $cart_new->getQuote()->getShippingAddress();
		$addressShip->setCountryId($country)->setPostcode($postcode)->setCollectShippingrates(true);
		$cart_new->save();
		
		// Find if our shipping has been included.
		$rates = $addressShip->collectShippingRates()->getGroupedAllShippingRates();
		$shippingPrice = 0;
		foreach ($rates as $carrier) {
		    foreach ($carrier as $rate) {
			$shippingPrice = $rate->getPrice();
			//echo $price."<<<< <br />";
		    }
		}
		
		///($shippingPrice == 0 || $shippingPrice == 0.00)
		if($_REQUEST['mm']=='yes'){
		    var_dump($shippingPrice);
		}
		
		if($shippingPrice > 0){
		    $shippingAmount = $shippingPrice;
		}else
		{
		    if($amount > $getSubtotal){
			$shippingAmount = $amount - $getSubtotal;
		    }else{
			$shippingAmount = $shippingPrice;
		    }
		}
		    
		
		//
		//    $address2->setCountryId($countryName2)->setPostcode($postcode2)->setCollectShippingrates(true);
		//    $shippingRates = $address2->collectShippingRates()->getGroupedAllShippingRates();
		//    $shippingPrice = 0;
		//    foreach ($shippingRates as $carrier) {
		//	foreach ($carrier as $rate) {
		//	    $shippingPrice = $rate->getPrice();
		//	    //echo $price."<<<< <br />";
		//	}
		//    }
		    
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
			    'shipping' => number_format($shippingAmount, 2),
			    'shipping_address' => $addressShippingDataForFeed,
			    'subtotal' => $getSubtotal,
			    'items' =>$items
			    
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

if($method == 'cancel'){
    // Change status of order by order id
    $order_id = $_REQUEST['order_id'];

    $data['status'] = false;
    $data['message'] = '';
    $orderId = $order_id;
    if($orderId > 0){
	//if($order_id > 100000000){
	//    $orderIncrementId = $order_id;
	//    $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
	//}else{
	//    $order = Mage::getModel('sales/order')->load($orderId);
	//}
	$orderIncrementId = $order_id;
	$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
	
	/**
	* change order status to 'Completed'
	*/
       //$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true)->save();
       /**
	* change order status to 'Pending'
	*/
       //$order->setState(Mage_Sales_Model_Order::STATE_NEW, true)->save();
	
       /**
	* change order status to 'Pending Paypal'
	*/
      // $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true)->save();
	
       /**
	* change order status to 'Processing'
	*/
       //$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
	
       /**
	* change order status to 'Completed'
	*/
       //$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true)->save();
	
       /**
	* change order status to 'Closed'
	*/
       //$order->setState(Mage_Sales_Model_Order::STATE_CLOSED, true)->save();
	
       /**
	* change order status to 'Canceled'
	*/
       //$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
	
       /**
	* change order status to 'Holded'
	*/
       //$order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true)->save();
       
	if($order->canCancel()) {
	    $order->cancel()->save();
	    $data['status'] = true;
	    $data['message'] = '';
	}else{
	    $data['status'] = false;
	    $data['message'] = '101';
	}
    }else{
	$data['status'] = false;
	$data['message'] = '102';
    }
    $data['data']= array();

    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);
    echo $json_data;
}


