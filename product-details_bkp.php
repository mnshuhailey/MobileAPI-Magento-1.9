<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();
include('lib/global.php');

header('Content-Type: application/json; Charset=UTF-8');

$productid = $_REQUEST['productid'];
$customerid = $_REQUEST['customerid'];

$_product = Mage::getModel('catalog/product')->load($productid);


$data['status'] = true;
$data['message'] = '';
$isLike = false;
//$categoryid = $_product->getCategoryIds();
$mainURL = 'http://trendycounty.com/media/catalog/product';
$mainURLApi = 'http://trendycounty.com/mobileapi/';

$cats = $_product->getCategoryIds();

//echo "<pre>";
$galleryImgArr = $_product->getMediaGallery();
//print_r(count($galleryImgArr['images']));
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
//["value_id"]=>
//string(4) "1218"
//["file"]=>
//string(21) "/s/e/sep_barbie_2.jpg"
//["product_id"]=>
//string(3) "431"
//["label"]=>
//string(9) "September"
//["position"]=>
//string(1) "9"
//["disabled"]=>
//string(1) "0"
//["label_default"]=>
//string(9) "September"
//["position_default"]=>
//string(1) "9"
//["disabled_default"]=>
//string(1) "0"
//die();
    
foreach ($cats as $categoryid) {
    $cat = Mage::getModel('catalog/category')->load($categoryid) ;
    $categoryName = $cat->getName();
} 

$discountedPrice = number_format($_product->getFinalPrice(), 2);

if($customerid != ''){
$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerid, true);
$wishListItemCollection = $wishlist->getItemCollection();

$productID = $productid;

foreach ($wishListItemCollection as $ProductItem):
    if($ProductItem->getProductId() == $productID){
	$isLike = true;
    }
endforeach;

}



	$categoryParent = array(
        'id' => $categoryid,
        'name' => $categoryName
    );
	
	$categoryChild = array(
        'id' => $categoryid,
        'name' => $categoryName
    );
	
    $image = array($_product->getImageUrl());
	
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
    $attributes = $_product->getAttributes();
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
    
    $variants = get_associated_products($_product); //array();
    $productType = $_product->getTypeID();

    $data['data'][] = array(
    'id' => $_product->getId(),
    'productsku' => $_product->getSKU(),
    'productType' => $productType,
    'storeId' => $_product->getStoreIds(),
    'name' => $_product->getName(),
    'price' => number_format($_product->getPrice(), 2),
    'discounted' => $discountedPrice,
    'category' => $categoryParent,
    'subcategory' => $categoryChild,
    'images' => $image,
    'imagesgallery' => $prdImages,
    'is_like' => $isLike,
    'size' => $size,
    'color' => $color,
    'variants' => $variants,
    'description' => $_product->getDescription(),
    'share_url' => $baseURL.$_product->getUrlPath()
    //'share_url' => $mainURLApi.'shareproduct.php?productid='.$_product->getId()
    );


$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//sample url: http://staging.trendycounty.com/mobileapi/product-details.php?key=5pkmEyb8TPhx7eex87BMxcY77vyhhnqXAzUQHVPt&productid=8&customerid=49
?>