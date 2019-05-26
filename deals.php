<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();
include('lib/global.php');

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://tcstaging.trendycounty.com/media/catalog/product';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';

if(isset($_REQUEST['categoryid']) && $_REQUEST['categoryid'] > 0)
    $categoryid = $_REQUEST['categoryid'];
else
    $categoryid = 3;
$customerid = $_REQUEST['customerid'];

$category = new Mage_Catalog_Model_Category();
$category->load($categoryid);
$collection = $category->getProductCollection();
$collection->addAttributeToSelect('*');


$data['status'] = true;
$data['message'] = '';

$isLike = false;

foreach ($collection as $_product):

    $cat = Mage::getModel('catalog/category')->load($categoryid) ;
    $categoryName = $cat->getName();
    $discountedPrice = number_format($_product->getFinalPrice(), 2);

    if($customerid != ''){
	$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerid, true);
	$wishListItemCollection = $wishlist->getItemCollection();
	
	$productID = $_product->getId();

	foreach ($wishListItemCollection as $ProductItem):
	    if($ProductItem->getProductId() == $productID){
		$isLike = true;
	    }else{
		$isLike = false;
	    }
	endforeach;
	
    }
//echo "<pre>";
//echo $ProductItem->getProductId()."<<<<".$ProductItem->getProductName().">>>>".$productID;
//echo "<br />";
//die();
    $categoryParent = array(
        'id' => $categoryid,
        'name' => $categoryName
    );
	
    $categoryChild = array(
        'id' => $categoryid,
        'name' => $categoryName
    );
	
    $image = array($_product->getImageUrl());
    $galleryImgArr = $_product->getMediaGallery();
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
    //if($colorOptions !="" && !empty($colorOptions))
    //$color = $colorOptions;
    //else
    //$color = array();
    //
    ////if($sizeOptions !="" && !empty($sizeOptions))
    ////$size = $sizeOptions;
    ////else
    //$size = array();
    $size = get_final_product_size_mm($_product);
    if($size == "")
    $size = array();
    
    $color = get_final_product_color_mm($_product);
    if($color == "")
    $color = array();
    //$productType = $_product->getTypeID();
    //$variants = get_associated_products($product);

    $data['data'][] = array(
    'id' => $_product->getId(),
    'productsku' => $_product->getSKU(),
    'name' => $_product->getName(),
    'price' => number_format($_product->getPrice(), 2),
    'discounted' => $discountedPrice,
    'category' => $categoryParent,
    'subcategory' => $categoryChild,
    'images' => $image,
    'is_like' => $isLike,
    'size' => $size,
    'color' => $color,
    'description' => $_product->getDescription(),
    'share_url' => $baseURL.$_product->getUrlPath()
    //'share_url' => $mainURLApi.'shareproduct.php?productid='.$_product->getId()
		
    );

endforeach;


$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//sample url: http://tcstaging.trendycounty.com/mobileapi/list-product.php?subcategoryid=4
