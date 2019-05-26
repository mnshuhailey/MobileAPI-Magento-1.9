<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();
include('lib/global.php');

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://tcstaging.trendycounty.com/media/catalog/product';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';

$method = $_REQUEST['method'];
$customerid = $_REQUEST['customerid'];
$isLike = false;

$data['status'] = true;
$data['message'] = '';
$data['data'] = array();

if($method == 'pending'){
if($_REQUEST['mm'] =="yes"){
$orders = Mage::getResourceModel('sales/order_collection')
->addFieldToSelect('*')
->addFieldToFilter('customer_id', $customerid)
->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
->addFieldToFilter('status', 'canceled')
->setOrder('created_at', 'desc');	
}else{
$orders = Mage::getResourceModel('sales/order_collection')
->addFieldToSelect('*')
->addFieldToFilter('customer_id', $customerid)
->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
->addFieldToFilter('status', 'pending')
->setOrder('created_at', 'desc');	
}




foreach ($orders as $order)
{
	$order_id = $order->getRealOrderId();
	$order = Mage::getModel('sales/order')->load($order_id, 'increment_id'); 
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
		$cats = $product->getCategoryIds();
		
		//
		if($_GET['mm'] == 'yes'){
		    //die(var_dump($item->getProductId()));
		}
			
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
			
		//$colorOptions="";
		//$sizeOptions="";
		//$attributes = $product->getAttributes();
		//foreach ($attributes as $attribute) {
		//    if($attribute->getAttributeCode() == "color_lee_cooper"){
		//	$colorOptions = $attribute->getSource()->getAllOptions(false);
		//    }
		//    
		//    if($attribute->getAttributeCode() == "size_lee_cooper"){
		//	$sizeOptions = $attribute->getSource()->getAllOptions(false);
		//    }
		//}
		////if($colorOptions !="" && !empty($colorOptions))
		////$color = $colorOptions;
		////else
		//$color = array();
		//
		////if($sizeOptions !="" && !empty($sizeOptions))
		////$size = $sizeOptions;
		////else
		//$size = array();
		
		$size = get_final_product_size_mm($product);
		if($size == "")
		$size = array();
		
		$color = get_final_product_color_mm($product);
		if($color == "")
		$color = array();
		//$productType = $_product->getTypeID();
		
			
		if($customerid != ''){
			$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerid, true);
			$wishListItemCollection = $wishlist->getItemCollection();

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
	
	
	
	
	
	$data['data'][] = array(
		'orderid' => $order_id,
		'date' => $order->getCreatedAt(),
		'total_price' => number_format($order->getGrandTotal(), 2),
		'product_count' =>  floor($order->getData('total_qty_ordered')),
		'items' => $items
	);
}

$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;
}


//Completed
if($method == 'complete'){

$orders = Mage::getResourceModel('sales/order_collection')
->addFieldToSelect('*')
->addFieldToFilter('customer_id', $customerid)
->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
->addFieldToFilter('status', 'complete')
->setOrder('created_at', 'desc');



foreach ($orders as $order)
{
	$order_id = $order->getRealOrderId();
	$order = Mage::getModel('sales/order')->load($order_id, 'increment_id'); 
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
		
		//$colorOptions="";
		//$sizeOptions="";
		//$attributes = $product->getAttributes();
		//foreach ($attributes as $attribute) {
		//    if($attribute->getAttributeCode() == "color_lee_cooper"){
		//	$colorOptions = $attribute->getSource()->getAllOptions(false);
		//    }
		//    
		//    if($attribute->getAttributeCode() == "size_lee_cooper"){
		//	$sizeOptions = $attribute->getSource()->getAllOptions(false);
		//    }
		//}
		////if($colorOptions !="" && !empty($colorOptions))
		////$color = $colorOptions;
		////else
		//$color = array();
		//
		////if($sizeOptions !="" && !empty($sizeOptions))
		////$size = $sizeOptions;
		////else
		//$size = array();
		
		$size = get_final_product_size_mm($product);
		if($size == "")
		$size = array();
		
		$color = get_final_product_color_mm($product);
		if($color == "")
		$color = array();
		//$productType = $_product->getTypeID();
			
		if($customerid != ''){
			$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerid, true);
			$wishListItemCollection = $wishlist->getItemCollection();

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
	
	
	
	
	
	$data['data'][] = array(
		'orderid' => $order_id,
		'date' => $order->getCreatedAt(),
		'total_price' => number_format($order->getGrandTotal(), 2),
		'product_count' =>  floor($order->getData('total_qty_ordered')),
		'items' => $items
	);
}

$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;
}


//Completed
if($method == 'details'){

$orderid = $_REQUEST['orderid'];
 //$order = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id',$customer_id);
    $order = Mage::getModel('sales/order')->loadByIncrementId($orderid); 
    $orderItems = $order->getItemsCollection()
			->addAttributeToSelect('*')
			->load();
    
    if($order->getCustomerId() == $customerid){
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
	    
	    if($_GET['mm'] == 'yes'){
		//die(var_dump($item->getProductId()));
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
	    $galleryImgArr = $product->getMediaGallery();
	    $prdImages = "";
	    for($i=0;$i<count($galleryImgArr['images']);$i++){
		$prdImages[$i]['value_id'] = $galleryImgArr['images'][$i]['value_id'];
		$prdImages[$i]['file'] = $mainURL.$galleryImgArr['images'][$i]['file'];
		$prdImages[$i]['label'] = $galleryImgArr['images'][$i]['label'];
		$prdImages[$i]['position'] = $galleryImgArr['images'][$i]['position'];
		$prdImages[$i]['disabled'] = $galleryImgArr['images'][$i]['disabled'];
		
		$prdImages[$i]['label_default'] = $galleryImgArr['images'][$i]['label_default'];
		$prdImages[$i]['position_default'] = $galleryImgArr['images'][$i]['position_default'];
		$prdImages[$i]['disabled_default'] = $galleryImgArr['images'][$i]['disabled_default'];
	    }
	    
	    if(empty($prdImages)){
		//$prdImages = $image;
		$prdImages[0]['value_id'] = "";
		$prdImages[0]['file'] = $image[0];
		$prdImages[0]['label'] = "";
		$prdImages[0]['position'] = "";
		$prdImages[0]['disabled'] = "";
		
		$prdImages[0]['label_default'] = "";
		$prdImages[0]['position_default'] = "";
		$prdImages[0]['disabled_default'] = "";
	    }
		    
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
	//    //if($colorOptions !="" && !empty($colorOptions))
	//    //$color = $colorOptions;
	//    //else
	//    $color = array();
	//    
	//    //if($sizeOptions !="" && !empty($sizeOptions))
	//    //$size = $sizeOptions;
	//    //else
	//    $size = array();
	
		$size = get_final_product_size_mm($product);
		if($size == "")
		$size = array();
		
		$color = get_final_product_color_mm($product);
		if($color == "")
		$color = array();
		//$productType = $_product->getTypeID();
	
		    
	    if($customerid != ''){
		$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerid, true);
		$wishListItemCollection = $wishlist->getItemCollection();

		$productID = $item->getProductId();
		//if($_GET['mm'] == 'yes'){
		//    die(var_dump($productID));
		//}
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
		    'imagesgallery' => $prdImages,
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
	
	$customers = Mage::getModel('customer/customer')->load($customerid);
	
	$shippingAddress = $order->getShippingAddress();
	
	//$shipping_address_id=$order->shipping_address_id;
	//$address_shipping = Mage::getModel('customer/address')->load($shipping_address_id);
	if($_GET['mm'] == 'yes'){
	    $shippingAddressDef = $order->getDefaultShippingAddress();
	    //$billingAddress = $order->getBillingAddress();
	    //$shippingAddress = $order->getShippingAddress();
	    echo "<<<<<<";
	    print_r($shippingAddressDef);
	    echo "<br />";
	    //echo $shippingAddress->getCountryId();
	    //echo "<br /> First Name: ";
	    //echo $shippingAddress->getfirstname();
	    //echo "<br />";
	    //echo $shippingAddress->getRegion();
	    //echo "<br />";
	    die("<pre>".var_dump($shippingAddress->getData())."</pre>");
	    
	    //if(!($address_shipping['entity_id'] > 0)){
	    //    
	    //}
	}
	
    
	//$model->getPrimaryBillingAddress();
	
	//$address_shipping = $address_shipping->toArray();
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
	
	$billingAddress = $order->getBillingAddress();
	$regionInfo2 = array(
	    'region_id' => '',
	    'region' => $billingAddress->getRegion()
	);
	
	$streetArr2 = $billingAddress->getStreet();
	$addArr2 = $streetArr2; //split("\n",$address_shipping['street']);
	if($addArr2[0] != "")
	$add12 = $addArr2[0];
	else
	$add12 = "";
	
	if($addArr2[1] != "" && $addArr2[0] != $addArr2[1])
	$add22 = $addArr2[1];
	else
	$add22 = "";
	
	$country_id2 = $billingAddress->getcountry_id(); //$address_shipping['country_id'];
	$countryName2 = Mage::getModel('directory/country')->load($country_id2)->getName();
	
	$billing_address = array(
	    'id' => $billingAddress->getentity_id(),
	    'first_name' => $billingAddress->getfirstname(),
	    'last_name' => $billingAddress->getlastname(),
	    'email' => $customers->getEmail(),
	    'add1' => $add12,
	    'add2' => $add22,
	    //'street' => $address['street'],
	    'city' => $billingAddress->getcity(),
	    'postcode' => $billingAddress->getpostcode(),
	    'country_id' => $billingAddress->getcountry_id(),
	    'country_name' => $countryName2,
	    'region' => $regionInfo2,       
	    'telephone' => $billingAddress->gettelephone()
	);
	if($order->getShippingAmount() && $order->getShippingAmount() == 0 && $order->getGrandTotal() > $order->getSubtotal()){
	    $shippingAmount = $order->getGrandTotal() - $order->getSubtotal();
	}else{
	    $shippingAmount = $order->getShippingAmount();
	}
	$data['data'][] = array(
	    'orderid' => $orderid,
	    'date' => $order->getCreatedAt(),
	    'total_price' => number_format($order->getGrandTotal(), 2),
	    'subtotal_price' => number_format($order->getSubtotal(), 2),
	    'shipping_price' => number_format($shippingAmount, 2),
	    'product_count' =>  floor($order->getData('total_qty_ordered')),
	    'items' => $items,
	    'shipping_address' => $shipping_address,
	    'billing_address' => $billing_address
	);
    }
    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);
    echo $json_data;
}