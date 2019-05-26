<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();
include('lib/global.php');

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
Mage::getSingleton('checkout/session')->clear();
Mage::getSingleton('customer/session')->clear();
//Mage::getSingleton('checkout/cart')->clear();
$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

if($method == 'submit'){
    $productid = $_REQUEST['productid'];
    $qty = $_REQUEST['quantity'];
    $getSize = $_REQUEST['size'];
    $getSizeId = $_REQUEST['size_id'];
    try {
	
//$quote = Mage::getSingleton('checkout/session')->getQuote();
//$quote->addProduct($product, $qty);
//
//$quote->collectTotals()->save();

	//$product = Mage::getModel('catalog/product')->load($productid);
	//$cart = Mage::getModel('checkout/cart');
	//$cart->init();
	//$cart->addProduct($product, array('qty' => $qty));
	//$cart->save();
	//Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
	/*
	 *$cart = Mage::getSingleton('checkout/cart'); 
	$cart->init();
	$cart->addProduct($productInstance, array('qty' => $qty));
	$cart->save();
	Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
	 */
	



	// Get cart instance
	$cart = Mage::getSingleton('checkout/cart'); 
	$cart->init();
	
	$quote2 = Mage::getSingleton('checkout/session')->getQuote();
	$cartItems2 = $quote2->getAllVisibleItems();
	foreach ($cartItems2 as $item2) {
	    $productid2 = $item2->getProductId();
	    if($productid2 > 0){
		$productInstance2 = Mage::getModel('catalog/product')->load($productid2);
		$param2 = array(
		    'product_id' => $productInstance2->getId(),
		    'qty' => $item2->getQty()
		);
		$request2 = new Varien_Object();
		$request2->setData($param2);
		$cart->removeItem( $item2->getId() );
		$cart->addProduct($productInstance2, $param2);
	    }
	}
	/*
	 *$cart = Mage::getSingleton('checkout/cart'); 
$quoteItems = Mage::getSingleton('checkout/session')
                  ->getQuote()
                  ->getItemsCollection();
 
foreach( $quoteItems as $item ){
    $cart->removeItem( $item->getId() );    
}
$cart->save();
	 *
	 */

	// Add a product with custom options
	$productInstance = Mage::getModel('catalog/product')->load($productid);
	
	$param = array(
	    'product_id' => $productInstance->getId(),
	    'qty' => $qty
	);
	//$param = array('qty' => $qty);
	$request = new Varien_Object();
	$request->setData($param);
	$cart->addProduct($productInstance, $param);
	//$currentTime = Varien_Date::now();
	//$object->setCreatedAt($currentTime);
	
	// save the cart
	$cart->save();
	
	// update session
	$session->setCartWasUpdated(true);

	
	

	$quote = Mage::getSingleton('checkout/session')->getQuote();
	
	$cartItems = $quote->getAllVisibleItems();
	$quoteData = $quote->getData();
	
	
	//var_dump();
	//echo "<br />";
	    
	    //die();
	$data['status'] = true;
	$data['message'] = '';
	

	foreach ($cartItems as $item) {
	//   var_dump($item->getCreatedAt());
	//echo "<br />";
	//    
	//    die();
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
	    $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($item->getProductId());
	    //echo $item->getProductId().">>>>".$product->getFinalPrice()."<<<<".$product->getPrice()."<br/>";
	    $dicount_amount = $product->getPrice() - $product->getFinalPrice();
	    $productInfo = array(
		'id' => $item->getProductId(),
		'parent_id' => $parentIds,
		'productsku' => $product->getSKU(),
		'name' => $item->getName(),
		'price' => number_format($product->getPrice(), 2),
		'discounted' => number_format($product->getFinalPrice(), 2),
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
	    //$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	    $count = Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount();
	    $dicount_amount = $dicount_amount * $item->getQty();
	    $items[] = array(
		'quantity' => floor($item->getQty()),
		///'product_count' => $count,
		'discount_amount' => number_format($dicount_amount, 2),
		'date_added' => $item->getCreatedAt(),
		'product' => $productInfo
		);
			
	    $discountTotal += number_format($dicount_amount, 2);
	    $qtyTotal += floor($item->getQty());
	    $grandTotal += ($item->getQty() * $product->getFinalPrice());
		
	//    $productInfo = array(
	//	'id' => $item->getProductId(),
	//	'productsku' => $product->getSKU(),
	//	'name' => $item->getName(),
	//	'price' => number_format($item->getPrice(), 2),
	//	'category' => $categoryParent,
	//	'subcategory' => $categoryChild,
	//	'images' => $image,
	//	'imagesgallery' => $prdImages,
	//	'is_like' => $isLike,
	//	'size' => $size,
	//	'description' => $product->getDescription(),
	//	'share_url' => $mainURLApi.'shareproduct.php?productid='.$item->getProductId()
	//);
	//$items[] = array(
	//	'quantity' => floor($item->getQty()),
	//	'product_count' => $count,
	//	'discount_amount' => number_format($item->getDiscountAmount(), 2),
	//	'date_added' => '',
	//	'product' => $productInfo
	//	);
	//		
	//	$discountTotal += number_format($item->getDiscountAmount(), 2);
	//	$qtyTotal += floor($item->getQty());
	//
	//}
	//	
	//	$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	//	$grandTotal = number_format($quoteData['grand_total'], 2);
	}
			
	//$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	$count = Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount();
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

//Mage::getSingleton('customer/session')->clear();
//Mage::getSingleton('checkout/session')->clear();
echo $json_data;
	//$clear = Mage::getSingleton('checkout/session')->clear();
	// http://tcstaging.trendycounty.com/mobileapi/cart.php?key=5pkmEyb8TPhx7eex87BMxcY77vyhhnqXAzUQHVPt&method=submit&email=mohamad@optima.com.my&productid=8&quantity=1
}


if($method == 'list'){
if(isset($_GET['mm']) && $_GET['mm'] == "yes"){
    //$totalQTY = getProductQTY_mm($product);
    //echo ">>>>>>>>>===AAAAAAAAA==>>>>>><br/><pre>"; print_r($totalQTY); echo ">>>>>=====AAAAAAAAAAA=====>>>>>> <br/></pre>";
    //var_dump(">>ssss>".$customer->getid());
}
//$quote = Mage::getSingleton('checkout/session')->getQuote();
$quote = Mage::getModel('sales/quote')->loadByCustomer($customer);
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
	$productType = $product->getTypeID();
	    
	    
	    
	    
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
	    $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($item->getProductId());
	    $totalQTY = getProductQTY_mm($product);
	    $dicount_amount = $product->getPrice() - $product->getFinalPrice();
	    $productInfo = array(
	    'id' => $item->getProductId(),
	    'parent_id' => $parentIds,
	    'productsku' => $product->getSKU(),
	    'product_qty' => "$totalQTY", //(int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty(),
	    'name' => $item->getName(),
	    'price' => number_format($product->getPrice(), 2),
	    'discounted' => number_format($product->getFinalPrice(), 2),
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
	    //$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
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
	//$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	$count = Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount();
	
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

$productid = $_REQUEST['productid'];
$productQty = $_REQUEST['quantity'];

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
	    if(isset($_GET['mm']) && $_GET['mm'] == "yes"){
		//$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
		//echo ">>>>>>>>>>>>>>><br/><pre>"; print_r($stock->getData()); echo ">>>>>>>>>>> <br/></pre>";   
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
	    $totalQTY = getProductQTY_mm($product);
	    $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($item->getProductId());
	    $dicount_amount = $product->getPrice() - $product->getFinalPrice();
	    $productInfo = array(
	    'id' => $item->getProductId(),
	    'parent_id' => $parentIds,
	    'productsku' => $product->getSKU(),
	    'product_qty' => "$totalQTY", //(int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty(),
	    'name' => $item->getName(),
	    'price' => number_format($product->getPrice(), 2),
	    'discounted' => number_format($product->getFinalPrice(), 2),
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
	    //$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
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
//$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
$count = Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount();

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
	//if($colorOptions !="" && !empty($colorOptions))
	//$color = $colorOptions;
	//else
	//$color = array();
	//
	//if($sizeOptions !="" && !empty($sizeOptions))
	//$size = $sizeOptions;
	//else
	//$size = array();
	$size = get_final_product_size_mm($product);
	if($size == "")
	$size = array();
	
	$color = get_final_product_color_mm($product);
	if($color == "")
	$color = array();
	
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
	$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($item->getProductId());
	$dicount_amount = $product->getPrice() - $product->getFinalPrice();
	$productInfo = array(
	    'id' => $item->getProductId(),
	    'parent_id' => $parentIds,
	    'productsku' => $product->getSKU(),
	    'name' => $item->getName(),
	    'price' => number_format($product->getPrice(), 2),
	    'discounted' => number_format($product->getFinalPrice(), 2),
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
	//$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
	$count = Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount();
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
//$count = Mage::helper('checkout/cart')->getCart()->getItemsCount();
$count = Mage::getSingleton('checkout/cart')->getQuote()->getItemsCount();
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