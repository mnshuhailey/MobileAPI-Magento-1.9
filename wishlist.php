<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();
include('lib/global.php');

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://tcstaging.trendycounty.com/media/catalog/product';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';

$method = $_REQUEST['method'];

if($method == 'submitOld'){
	$customerId = $_REQUEST['customerid'];
	$productId = $_REQUEST['productid'];
	
	$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
	$product = Mage::getModel('catalog/product')->load($productId);

	$buyRequest = new Varien_Object(array()); // any possible options that are configurable and you want to save with the product

	$result = $wishlist->addNewItem($product, $buyRequest);
	try {
	    $wishlist->save();
	}catch(Exception $e){
	    $systemError = $e->getMessage();
	    $data['status'] = false;
	    $data['message'] = $systemError; 
	}
	
	if($_REQUEST['mm'] == 'yes'){
	    $wishlistFinal = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
	    $wishListItemCollection = $wishlistFinal->getItemCollection();
	    foreach ($wishListItemCollection as $_product):
		echo $_product->getProductId()." <<<<< <br/>";
	    endforeach;
	
	}
	
	$data['status'] = true;
	$data['message'] = '';
	$data['data'][] = array();
	
	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);

	echo $json_data;
	$clear = Mage::getSingleton('checkout/session')->clear();
	
}

if($method == 'submit'){
	$customerId = $_REQUEST['customerid'];
	$productId = $_REQUEST['productid'];
	//$productExisted=false;
	if($productId > 0 && $customerId){
	//    $wishlistFinal = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
	//    $wishListItemCollection = $wishlistFinal->getItemCollection();
	//    foreach ($wishListItemCollection as $_product):
	//	//echo $_product->getProductId()." <<<<< <br/>";
	//	if($_product->getProductId() == $productId){
	//	    $productExisted = true;
	//	    break;
	//	}
	//    endforeach;
	    //if($productExisted){
		$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
		$product = Mage::getModel('catalog/product')->load($productId);
	
		$buyRequest = new Varien_Object(array()); // any possible options that are configurable and you want to save with the product
	
		$result = $wishlist->addNewItem($product, $buyRequest);
		try {
		    $wishlist->save();
		    $data['status'] = true;
		    $data['message'] = '';
		}catch(Exception $e){
		    $systemError = $e->getMessage();
		    $data['status'] = false;
		    $data['message'] = $systemError; 
		}
		$data['data'] = array();
	//    }else{
	//	$data['status'] = true;
	//	$data['message'] = 'Product is already added to the wishlist';
	//	$data['data'] = array();
	//    }
	}else{
	    $data['status'] = false;
	    $data['message'] = 'Invalid data';
	    $data['data'] = array();
	}
	
	if($_REQUEST['mm'] == 'yes'){
	    $wishlistFinal = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId, true);
	    $wishListItemCollection = $wishlistFinal->getItemCollection();
	    foreach ($wishListItemCollection as $_product):
		echo $_product->getProductId()." <<<<< <br/>";
	    endforeach;
	
	}
	
	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);
	$json_data = str_replace('null','""',$json_data);

	echo $json_data;
	$clear = Mage::getSingleton('checkout/session')->clear();
	
}


if($method == 'remove'){
	$wishlistid = $_REQUEST['wishlistid'];
	
	Mage::getModel('wishlist/item')->load($wishlistid)->delete();
	
	$data['status'] = true;
	$data['message'] = '';
	$data['data'] = array(
		
    );
	
	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);

	echo $json_data;
	
}


if($method == 'list'){
    $customerid = $_REQUEST['customerid'];
    
    $websiteId = Mage::app()->getWebsite()->getId();
    $store = Mage::app()->getStore();
    
    $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerid, true);
    $wishListItemCollection = $wishlist->getItemCollection();
    
    $data['status'] = true;
    $data['message'] = '';
    if($_REQUEST['mm'] == 'yes'){
	//echo "<pre>";
	//var_dump($wishListItemCollection);
	//echo "<br/>";
	//die();
    }
    
    foreach ($wishListItemCollection as $_product):
    
	$productid = $_product->getProductId();
	$productInfo = Mage::getModel('catalog/product')->load($productid);
	if($_REQUEST['mm'] == 'yes'){
	    //echo $productInfo->getFinalPrice()."<<<<>>>>".$productInfo->getSpecialPrice()."<<<<>>>>".$productInfo->getPrice();
	    //echo $_product->getProductId();
	    echo $_product->getProductId()." <<<<< <br/>";
	
	}
	$discountedPrice = number_format($productInfo->getFinalPrice(), 2);
	
	$cats = $productInfo->getCategoryIds();
	foreach ($cats as $categoryid) {
	    $cat = Mage::getModel('catalog/category')->load($categoryid) ;
	    $categoryName = $cat->getName();
	} 
    
	$categoryParent = array(
	    'id' => $categoryid,
	    'name' => $categoryName
	);
	    
	$categoryChild = array(
	    'id' => $categoryid,
	    'name' => $categoryName
	);
	    
	$image = array($productInfo->getImageUrl());
	    
	$_product_new = Mage::getModel('catalog/product')->load($productid);
	$galleryImgArr = $_product_new->getMediaGallery();
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
	//$attributes = $productInfo->getAttributes();
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
	
	$size = get_final_product_size_mm($productInfo);
	if($size == "")
	$size = array();
	
	$color = get_final_product_color_mm($productInfo);
	if($color == "")
	$color = array();
	//$productType = $_product->getTypeID();
    
	$data['data'][] = array(
	    'wishlistid' => $_product->getID(),
	    'id' => $productid,
	    'productsku' => $productInfo->getSKU(),
	    'name' => $productInfo->getName(),
	    'price' => number_format($productInfo->getPrice(), 2),
	    'discounted' => $discountedPrice,
	    'category' => $categoryParent,
	    'subcategory' => $categoryChild,
	    'images' => $image,
	    'imagesgallery' => $prdImages,
	    'is_like' => true,
	    'size' => $size,
	    'color' => $color,
	    'description' => $productInfo->getDescription(),
	    'share_url' => $baseURL.$productInfo->getUrlPath()
	    //'share_url' => $mainURLApi.'shareproduct.php?productid='.$productInfo->getId()
	);
    
    endforeach;
    
    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);
    
    echo $json_data;

}
