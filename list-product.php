<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();
include('lib/global.php');

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://tcstaging.trendycounty.com/media/catalog/product';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';

$categoryid = $_REQUEST['subcategoryid'];
$customerid = $_REQUEST['customerid'];

//$category = new Mage_Catalog_Model_Category();
//$category->load($categoryid);
//$collection = $category->getProductCollection();
////$collection->addAttributeToSelect('*');
//$collection->addAttributeToFilter('type_id', 'configurable');
$collection = Mage::getResourceModel('catalog/product_collection')
	->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
	->addAttributeToFilter('category_id', array('in' => $categoryid))
	->addAttributeToFilter('type_id', 'configurable')
	->addAttributeToSelect('*')
	->load();


$data['status'] = true;
$data['message'] = '';

$isLike = false;

if($_REQUEST['mm'] == "yes"){
	//var_dump($_product->getData());
	
	
	
	//$rootCatId = Mage::app()->getStore()->getRootCategoryId();
	//
	//$productCollection = Mage::getResourceModel('catalog/product_collection')
	//->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
	//->addAttributeToFilter('category_id', array('in' => $categoryid))
	//->addAttributeToFilter('type_id', 'configurable')
	//->addAttributeToSelect('*')
	//->load();
	//
	//foreach ($productCollection as $product) {
	//	echo $product->getName();
	//	echo ">>>><br/>";
	//	echo $product->getId();
	//	echo ">>>><br/>";
	//}
	//
	//echo ">>>> ==== >>>>><br/>";
	//die();
}
	
foreach ($collection as $_product):
    //if($_REQUEST['mm'] == "yes"){
    //	var_dump($_product->getData());
    //	echo ">>>><br/>";
    //}
    $totalQTY = check_associated_product_qty($_product);
    if($totalQTY > 0){
	//$cat = Mage::getModel('catalog/category')->load($categoryid) ;
	
	//$categoryName = $cat->getName();
	$discountedPrice = number_format($_product->getFinalPrice(), 2);
	
	//if($customerid != ''){
	//$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerid, true);
	//$wishListItemCollection = $wishlist->getItemCollection();
	
	//$productID = $_product->getId();
	
	//foreach ($wishListItemCollection as $ProductItem):
	//	if($ProductItem->getProductId() == $productID){
	//		$isLike = true;
	//	}else{
	//		$isLike = false;
	//	}
	//	
	//endforeach;
	//   
	//}
	//
	//$categoryParent = array(
	//    'id' => $categoryid,
	//    'name' => $categoryName
	//);
	//
	//$categoryChild = array(
	//    'id' => $categoryid,
	//    'name' => $categoryName
	//);
	
	$image = array($_product->getImageUrl());
	$productType = $_product->getTypeID();
	
	//$_product_new = Mage::getModel('catalog/product')->load($productID);
	//$galleryImgArr = $_product_new->getMediaGallery();
	////var_dump($image[0]);
	////echo "<br />";
	//$prdImages = "";
	//for($i=0;$i<count($galleryImgArr['images']);$i++){
	//    $prdImages[$i]['value_id'] = $galleryImgArr['images'][$i]['value_id'];
	//    $prdImages[$i]['file'] = $mainURL.$galleryImgArr['images'][$i]['file'];
	//    $prdImages[$i]['label'] = $galleryImgArr['images'][$i]['label'];
	//    $prdImages[$i]['position'] = $galleryImgArr['images'][$i]['position'];
	//    $prdImages[$i]['disabled'] = $galleryImgArr['images'][$i]['disabled'];
	//    
	//    $prdImages[$i]['label_default'] = $galleryImgArr['images'][$i]['label_default'];
	//    $prdImages[$i]['position_default'] = $galleryImgArr['images'][$i]['position_default'];
	//    $prdImages[$i]['disabled_default'] = $galleryImgArr['images'][$i]['disabled_default'];
	//}
	//
	//if(empty($prdImages)){
	//    //$prdImages = $image;
	//    $prdImages[0]['value_id'] = "";
	//    $prdImages[0]['file'] = $image[0];
	//    $prdImages[0]['label'] = "";
	//    $prdImages[0]['position'] = "";
	//    $prdImages[0]['disabled'] = "";
	//    
	//    $prdImages[0]['label_default'] = "";
	//    $prdImages[0]['position_default'] = "";
	//    $prdImages[0]['disabled_default'] = "";
	//}
	//
	//$colorOptions="";
	//$sizeOptions="";
//    $attributes = $_product->getAttributes();
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
    
	//$size = get_final_product_size_mm($_product);
	//if($size == "")
	//$size = array();
	//
	//$color = get_final_product_color_mm($_product);
	//if($color == "")
	//$color = array();
	/*1. id
2. name
3. price
4. discounted
5. images
6. productType
	 */
	
	
	//$variants = get_associated_products($_product); //array();
    
	$data['data'][] = array(
	    'id' => $_product->getId(),
	    'productsku' => $_product->getSKU(),
	    'productType' => $productType,
	    'name' => $_product->getName(),
	    'price' => number_format($_product->getPrice(), 2),
	    'discounted' => $discountedPrice,
	    //'category' => $categoryParent,
	    //'subcategory' => $categoryChild,
	    'images' => $image,
	    //'imagesgallery' => $prdImages,
	    //'is_like' => $isLike,
	    //'size' => $size,
	    //'color' => $color,
	    //'variants' => $variants,
	    //'description' => $_product->getDescription(),
	    //'share_url' => $baseURL.$_product->getUrlPath(),
	    //'totalConfPrdQTY' => "$totalQTY"
	    //'share_url' => $mainURLApi.'shareproduct.php?productid='.$_product->getId()
		    
	);
    }

endforeach;


$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;