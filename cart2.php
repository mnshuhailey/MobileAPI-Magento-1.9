<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();

Mage::init('default');  
Mage::getSingleton('core/session', array('name' => 'frontend')); 

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://tcstaging.trendycounty.com/media/catalog/product';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';

$method = $_REQUEST['method'];
$email = $_REQUEST['email'];
$isLike = false;
$qtyTotal=0;
$grandTotal = 0;
$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

if($method == 'submit'){
    $productid = @$_REQUEST['productid'];
    $qty = @$_REQUEST['quantity'];
    $getSize = @$_REQUEST['size'];
    $getSizeId = @$_REQUEST['size_id'];
    
    
    Mage::getSingleton('core/session', array('name' => 'frontend'));

    try {
	$product_id = $productid; // Replace id with your product id
    
	$product = Mage::getModel('catalog/product')->load($product_id);
    
	$cart = Mage::getModel('checkout/cart');
    
	$cart->init();
    
	$params = array(
    
	    'product' => $product_id,
    
	    'options' => array(
    
		243 => $getSizeId,
		//525 is the attribute id of size and 100 is the selected option value (small) of that attribute.
      
	    ),
    
	    'qty' => $qty,
    
	);
    
	$cart->addProduct($product, $params);
    
	$cart->save();
    
	Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
    
	Mage::getSingleton('core/session')->addSuccess('Product added successfully');
    
	//header('Location: ' . 'index.php/checkout/cart/');
	$quote = Mage::getSingleton('checkout/session')->getQuote();
	
	$cartItems = $quote->getAllVisibleItems();
	$quoteData = $quote->getData();
	
	$data['status'] = true;
	$data['message'] = '';
	

	foreach ($cartItems as $item) {
	//   var_dump($item->getCreatedAt());
	//echo "<br />";
	//    
	//    die();
	    $product = Mage::getModel('catalog/product')->load($item->getProductId());
	    
	    
	    var_dump($product->getData());
	    //die();
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
	    //var_dump($image[0]);
	    //echo "<br />";
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
	    
	    $colorOptions="";
	    $sizeOptions="";
	    $attributes = $product->getAttributes();
	    foreach ($attributes as $attribute) {
		if($attribute->getAttributeCode() == "color_lee_cooper"){
		    $colorOptions = $attribute->getSource()->getAllOptions(false);
		}
		
		if($attribute->getAttributeCode() == "size_lee_cooper"){
		    $sizeOptions = $attribute->getSource()->getAllOptions(false);
		}
	    }
	    if($colorOptions !="" && !empty($colorOptions))
	    $color = $colorOptions;
	    else
	    $color = array();
	    
	    if($sizeOptions !="" && !empty($sizeOptions)){
		//$size = $sizeOptions;
		if($getSizeId > 0 ){
		    $size = array();
		    for($j=0; $j < count($sizeOptions);$j++){
			//echo $sizeOptions[$j]['value']."<===> <br/>";
			if($sizeOptions[$j]['value'] == $product->getsize_lee_cooper()){
			    $size['value'] = $sizeOptions[$j]['value'];
			    $size['label'] = $sizeOptions[$j]['label'];
			}
		    }
		}else{
		   $size = $sizeOptions; 
		}
		//echo "<pre>";
		//var_dump($product->getsize_lee_cooper());
		//echo "<br/>";
		//var_dump($sizeOptions);
		//die();
	    }else{
		$size = array();
	    }
	    //
	    // die(">>>".var_dump($item->getProductId())."<<<<".$item->getId());
	    $customerid = $customer->getid();
	    //die(">>>>".$customerid."<<<<");
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
	    //echo $item->getProductId().">>>>".$product->getFinalPrice()."<<<<".$product->getPrice()."<br/>";
	    $dicount_amount = $product->getPrice() - $product->getFinalPrice();
	    $productInfo = array(
		'id' => $item->getProductId(),
		'productsku' => $product->getSKU(),
		'name' => $item->getName(),
		'price' => number_format($product->getPrice(), 2),
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
	    $count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	    $dicount_amount = $dicount_amount * $item->getQty();
	    $items[] = array(
		'quantity' => floor($item->getQty()),
		'product_count' => $count,
		'discount_amount' => number_format($dicount_amount, 2),
		'date_added' => $item->getCreatedAt(),
		'product' => $productInfo
		);
			
	    $discountTotal += number_format($dicount_amount, 2);
	    $qtyTotal += floor($item->getQty());
	    $grandTotal += ($item->getQty() * $product->getFinalPrice());
	}
			
	$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	//$grandTotal = number_format($quoteData['grand_total'], 2);
	//$grandTotal = number_format($quote->getGrandTotal(), 2);
	
	//$grandTotal = number_format($quote->getGrandTotal(), 2);
		$data['data'] = array(
			'total_price' => $grandTotal,
			'total_discount' => number_format($discountTotal, 2),
			'product_count' => $count,
			'item_count' => $qtyTotal,
			'items' => $items
		);
    $json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;
    } catch (Exception $e) {
    
	echo $e->getMessage();
    
    }
    
    die();
    
    try {
	// Get cart instance
	$cart = Mage::getSingleton('checkout/cart'); 
	$cart->init();

	// Add a product with custom options
	$productInstance = Mage::getModel('catalog/product')->load($productid);
	if($getSizeId > 0 ){
	    
	    $param = array(
		'product' => $productInstance->getId(),
		'qty' => $qty,
		'super_attribute' => array(
		    '243' => $getSizeId
		)
	    );
	}else{
	    $param = array(
		'product' => $productInstance->getId(),
		'qty' => $qty
	    );
	}
	
	
	$request = new Varien_Object();
	$request->setData($param);
	$cart->addProduct($productInstance, $param);
	//$currentTime = Varien_Date::now();
	//$object->setCreatedAt($currentTime);
	

	// update session
	$session->setCartWasUpdated(true);

	// save the cart
	$cart->save();
	//var_dump($cart->getData());
	

	$quote = Mage::getSingleton('checkout/session')->getQuote();
	
	$cartItems = $quote->getAllVisibleItems();
	$quoteData = $quote->getData();
	
	$data['status'] = true;
	$data['message'] = '';
	

	foreach ($cartItems as $item) {
	//   var_dump($item->getCreatedAt());
	//echo "<br />";
	//    
	//    die();
	    $product = Mage::getModel('catalog/product')->load($item->getProductId());
	    
	    
	    //var_dump($product->getData());
	    //die();
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
	    //var_dump($image[0]);
	    //echo "<br />";
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
	    
	    $colorOptions="";
	    $sizeOptions="";
	    $attributes = $product->getAttributes();
	    foreach ($attributes as $attribute) {
		if($attribute->getAttributeCode() == "color_lee_cooper"){
		    $colorOptions = $attribute->getSource()->getAllOptions(false);
		}
		
		if($attribute->getAttributeCode() == "size_lee_cooper"){
		    $sizeOptions = $attribute->getSource()->getAllOptions(false);
		}
	    }
	    if($colorOptions !="" && !empty($colorOptions))
	    $color = $colorOptions;
	    else
	    $color = array();
	    
	    if($sizeOptions !="" && !empty($sizeOptions)){
		//$size = $sizeOptions;
		if($getSizeId > 0 ){
		    $size = array();
		    for($j=0; $j < count($sizeOptions);$j++){
			//echo $sizeOptions[$j]['value']."<===> <br/>";
			if($sizeOptions[$j]['value'] == $product->getsize_lee_cooper()){
			    $size['value'] = $sizeOptions[$j]['value'];
			    $size['label'] = $sizeOptions[$j]['label'];
			}
		    }
		}else{
		   $size = $sizeOptions; 
		}
		//echo "<pre>";
		//var_dump($product->getsize_lee_cooper());
		//echo "<br/>";
		//var_dump($sizeOptions);
		//die();
	    }else{
		$size = array();
	    }
	    //
	    // die(">>>".var_dump($item->getProductId())."<<<<".$item->getId());
	    $customerid = $customer->getid();
	    //die(">>>>".$customerid."<<<<");
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
	    //echo $item->getProductId().">>>>".$product->getFinalPrice()."<<<<".$product->getPrice()."<br/>";
	    $dicount_amount = $product->getPrice() - $product->getFinalPrice();
	    $productInfo = array(
		'id' => $item->getProductId(),
		'productsku' => $product->getSKU(),
		'name' => $item->getName(),
		'price' => number_format($product->getPrice(), 2),
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
	    $count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	    $dicount_amount = $dicount_amount * $item->getQty();
	    $items[] = array(
		'quantity' => floor($item->getQty()),
		'product_count' => $count,
		'discount_amount' => number_format($dicount_amount, 2),
		'date_added' => $item->getCreatedAt(),
		'product' => $productInfo
		);
			
	    $discountTotal += number_format($dicount_amount, 2);
	    $qtyTotal += floor($item->getQty());
	    $grandTotal += ($item->getQty() * $product->getFinalPrice());
	}
			
	$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	//$grandTotal = number_format($quoteData['grand_total'], 2);
	//$grandTotal = number_format($quote->getGrandTotal(), 2);
	
	//$grandTotal = number_format($quote->getGrandTotal(), 2);
		$data['data'] = array(
			'total_price' => $grandTotal,
			'total_discount' => number_format($discountTotal, 2),
			'product_count' => $count,
			'item_count' => $qtyTotal,
			'items' => $items
		);


	}catch(Exception $e){
	    $systemError = $e->getMessage();
	    $data['status'] = false;
	    $data['message'] = $systemError; 
	}
	
	
$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;
	
	// http://tcstaging.trendycounty.com/mobileapi/cart.php?key=5pkmEyb8TPhx7eex87BMxcY77vyhhnqXAzUQHVPt&method=submit&email=mohamad@optima.com.my&productid=8&quantity=1
}


if($method == 'list'){
//$getSize = $_REQUEST['size'];
//$getSizeId = $_REQUEST['size_id'];
$quote = Mage::getSingleton('checkout/session')->getQuote();
$cartItems = $quote->getAllVisibleItems();
$quoteData = $quote->getData();
$data['status'] = true;
$data['message'] = '';
$grandTotal = 0;
if($cartItems):
	foreach ($cartItems as $item) {

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
		$prdImages[0]['value_id'] = "";
		$prdImages[0]['file'] = $image[0];
		$prdImages[0]['label'] = "";
		$prdImages[0]['position'] = "";
		$prdImages[0]['disabled'] = "";
		
		$prdImages[0]['label_default'] = "";
		$prdImages[0]['position_default'] = "";
		$prdImages[0]['disabled_default'] = "";
	    }
			
	    $colorOptions="";
	    $sizeOptions="";
	    $attributes = $product->getAttributes();
	    
	    foreach ($attributes as $attribute) {
		if($attribute->getAttributeCode() == "color_lee_cooper"){
		    $colorOptions = $attribute->getSource()->getAllOptions(false);
		}
		
		if($attribute->getAttributeCode() == "size_lee_cooper"){
		    $sizeOptions = $attribute->getSource()->getAllOptions(false);
		}
	    }
	    if($colorOptions !="" && !empty($colorOptions))
	    $color = $colorOptions;
	    else
	    $color = array();
	    
	    //echo $product->getsize_lee_cooper();
	    //echo "===<br/>";
	    //var_dump();
	    
	    if($sizeOptions !="" && !empty($sizeOptions)){
		if($product->getsize_lee_cooper() > 0 ){
		    $size = array();
		    for($j=0; $j < count($sizeOptions);$j++){
			if($sizeOptions[$j]['value'] == $product->getsize_lee_cooper()){
			    $size['value'] = $sizeOptions[$j]['value'];
			    $size['label'] = $sizeOptions[$j]['label'];
			}
		    }
		}else{
		   $size = $sizeOptions; 
		}
	    }else{
		$size = array();
	    }
	    
	    
	    
	    
	    $customerid = $customer->getid();	
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
	    
	    $dicount_amount = $product->getPrice() - $product->getFinalPrice();
	    $productInfo = array(
	    'id' => $item->getProductId(),
	    'productsku' => $product->getSKU(),
	    'name' => $item->getName(),
	    'price' => number_format($product->getPrice(), 2),
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

	    if(floor($item->getQty()) > 0):
	    $count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	    $dicount_amount = $dicount_amount * $item->getQty();
	    $items[] = array(
		    'quantity' => floor($item->getQty()),
		    'discount_amount' => number_format($dicount_amount, 2),
		    'date_added' => $item->getCreatedAt(),
		    'product' => $productInfo
	    );
	    else:

	    $items[] = array();
	    
	    endif;
		
	$discountTotal += number_format($dicount_amount, 2);
	$qtyTotal += floor($item->getQty());
	$grandTotal += ($item->getQty() * $product->getFinalPrice());

	}
	
else:
$items = array();
endif;
	
	//$grandTotal = number_format($quoteData['grand_total'], 2);
	$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	
	$data['data']= array(
		'total_price' => $grandTotal,
		'total_discount' => number_format($discountTotal, 2),
		'product_count' => $count,
		'item_count' => $qtyTotal,
		'items' => $items
	);


$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//print '<pre>';
//print_r($cartItems);


}

if($method == 'editquantity'){

$productid = @$_REQUEST['productid'];
$productQty = @$_REQUEST['quantity'];
$getSize = @$_REQUEST['size'];
$getSizeId = @$_REQUEST['size_id'];

$quote = Mage::getSingleton('checkout/session')->getQuote();

$cartItems = $quote->getAllVisibleItems();
$quoteData = $quote->getData();


$data['status'] = true;
$data['message'] = '';
/*
 *
 *$quote->hasProductId($pid)
 *($qty!=$item->getQty())
//get Product 
$product = Mage::getModel('catalog/product')->load($pid);
//get Item
$item = $quote->getItemByProduct($product);

$quote->getCart()->updateItem(array($item->getId()=>array('qty'=>$qty)));
$quote->getCart()->save();

 */
if($quote->hasProductId($productid)){
    $products = Mage::getModel('catalog/product')->load($productid);
    $item = $quote->getItemByProduct($products);
    
    if($productQty!=$item->getQty()){
	//$quote->getCart()->updateItem(array($item->getId()=>array('qty'=>$productQty)));
	//$quote->getCart()->save();
	$item->setQty($productQty);
	Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
	$quote->save();
    }
}



//$item->setQty($productQty);
//Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
//$quote->save();

$grandTotal = 0;
if($cartItems):
	foreach ($cartItems as $item) {

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
		$prdImages[0]['value_id'] = "";
		$prdImages[0]['file'] = $image[0];
		$prdImages[0]['label'] = "";
		$prdImages[0]['position'] = "";
		$prdImages[0]['disabled'] = "";
		
		$prdImages[0]['label_default'] = "";
		$prdImages[0]['position_default'] = "";
		$prdImages[0]['disabled_default'] = "";
	    }
			
	    $colorOptions="";
	    $sizeOptions="";
	    $attributes = $product->getAttributes();
	    foreach ($attributes as $attribute) {
		if($attribute->getAttributeCode() == "color_lee_cooper"){
		    $colorOptions = $attribute->getSource()->getAllOptions(false);
		}
		
		if($attribute->getAttributeCode() == "size_lee_cooper"){
		    $sizeOptions = $attribute->getSource()->getAllOptions(false);
		}
	    }
	    if($colorOptions !="" && !empty($colorOptions))
	    $color = $colorOptions;
	    else
	    $color = array();
	    
	    if($sizeOptions !="" && !empty($sizeOptions))
	    $size = $sizeOptions;
	    else
	    $size = array();
	    
	    $customerid = $customer->getid();	
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
	    
	    $dicount_amount = $product->getPrice() - $product->getFinalPrice();
	    $productInfo = array(
	    'id' => $item->getProductId(),
	    'productsku' => $product->getSKU(),
	    'name' => $item->getName(),
	    'price' => number_format($product->getPrice(), 2),
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

	    if(floor($item->getQty()) > 0):
	    $count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	    $dicount_amount = $dicount_amount * $item->getQty();
	    $items[] = array(
		    'quantity' => floor($item->getQty()),
		    'discount_amount' => number_format($dicount_amount, 2),
		    'date_added' => $item->getCreatedAt(),
		    'product' => $productInfo
	    );
	    else:

	    $items[] = array();
	    
	    endif;
		
	$discountTotal += number_format($dicount_amount, 2);
	$qtyTotal += floor($item->getQty());
	$grandTotal += ($item->getQty() * $product->getFinalPrice());

	}
	
else:
$items = array();
endif;
	
//$grandTotal = number_format($quoteData['grand_total'], 2);
$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();

$data['data'] = array(
    'total_price' => $grandTotal,
    'total_discount' => number_format($discountTotal, 2),
    'product_count' => $count,
    'item_count' => $qtyTotal,
    'items' => $items
);


$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//print '<pre>';
//print_r($cartItems);


}


if($method == 'remove'){

$productid = $_REQUEST['productid'];
$getSize = @$_REQUEST['size'];
$getSizeId = @$_REQUEST['size_id'];

$cartHelper = Mage::helper('checkout/cart');
$itemss = $cartHelper->getCart()->getItems();
foreach ($itemss as $itemsss) {
    if ($itemsss->getProduct()->getId() == $productid) {
	$itemId = $itemsss->getItemId();
        $cartHelper->getCart()->removeItem($itemId)->save();     
    }
}

/*
foreach ($items as $item) 
{
   $itemId = $item->getItemId();
   $cartHelper->getCart()->removeItem($itemId)->save();
} 
 */

$quote = Mage::getSingleton('checkout/session')->getQuote(); 
$cartItems = $quote->getAllVisibleItems();
$quoteData = $quote->getData();
$data['status'] = true;
$data['message'] = '';

if($cartItems):
    foreach ($cartItems as $item) {
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
	
	$colorOptions="";
	$sizeOptions="";
	$attributes = $product->getAttributes();
	foreach ($attributes as $attribute) {
	    if($attribute->getAttributeCode() == "color_lee_cooper"){
		$colorOptions = $attribute->getSource()->getAllOptions(false);
	    }
	    
	    if($attribute->getAttributeCode() == "size_lee_cooper"){
		$sizeOptions = $attribute->getSource()->getAllOptions(false);
	    }
	}
	if($colorOptions !="" && !empty($colorOptions))
	$color = $colorOptions;
	else
	$color = array();
	
	if($sizeOptions !="" && !empty($sizeOptions))
	$size = $sizeOptions;
	else
	$size = array();
	
	$customerid = $customer->getid();	
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
	$dicount_amount = $product->getPrice() - $product->getFinalPrice();
	$productInfo = array(
	    'id' => $item->getProductId(),
	    'productsku' => $product->getSKU(),
	    'name' => $item->getName(),
	    'price' => number_format($product->getPrice(), 2),
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
	$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	$dicount_amount = $dicount_amount * $item->getQty();
	$items[] = array(
	    'quantity' => floor($item->getQty()),
	    'product_count' => $count,
	    'discount_amount' => number_format($dicount_amount, 2),
	    'date_added' => $item->getCreatedAt(),
	    'product' => $productInfo
	);
		
	$discountTotal += number_format($dicount_amount, 2);
	$qtyTotal += floor($item->getQty());
	$grandTotal += ($item->getQty() * $product->getFinalPrice());
    }
else:
    $items = array();
endif;
//$grandTotal = number_format($quoteData['grand_total'], 2);
$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
$data['data'] = array(
    'total_price' => $grandTotal,
    'total_discount' => number_format($discountTotal, 2),
    'product_count' => $count,
    'item_count' => $qtyTotal,
    'items' => $items
);


$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//print '<pre>';
//print_r($cartItems);


}
