<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();
include('lib/global.php');
//header('Content-Type: application/json; Charset=UTF-8');

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

//$productType = $_product->getTypeID();
//echo $_product->getName();
//if($productType == 'simple')
//{   
//  echo "Simple Product";
//} 
//if($productType == 'configurable')
//{   
//  echo "Configurable Product";
//}
//if($productType == 'bundle')
//{   
//echo "Gundle Product";
//} 
//if($productType == 'grouped')
//{   
//echo "Grouped Product";
//} 
//if($productType == 'Downloadable')
//{   
//echo "Downloadable Product";
//} 
//if($productType == 'virtual')
//{   
//echo "Virtual Product";
//}

//$res = $_product->getResource()->getAttribute('size')->getFrontend()->getValue($_product);

//colors and sizes
	//$colorOptions="";
	//$sizeOptions="";
	//$attributes = $_product->getAttributes();
	//foreach ($attributes as $attribute) {
	//    if($attribute->getAttributeCode() == "color_lee_cooper"){
	//	$colorOptions = $attribute->getSource()->getAllOptions(false);
	//    }
	//    
	//    if($attribute->getAttributeCode() == "size_lee_cooper"){
	//	$sizeOptions = $attribute->getSource()->getAllOptions(false);
	//    }
	//}
	//echo "<pre>";
	//var_dump($colorOptions);
	//echo "<<<<<<<<<<<<<<<<<<<<<<br/>";
	//var_dump($sizeOptions);
//colors and sizes
function get_associated_products($_product){
    $productType = $_product->getTypeID();
    $assoc_variants = array();
    if($productType == 'configurable')
    {   
	$associatedSimpleProducts = $_product->getTypeInstance()->getUsedProductIds();
	//var_dump($associatedSimpleProducts);
	//die();
	foreach($associatedSimpleProducts as $associatedSimpleProduct){
	    //echo "$associatedSimpleProduct <br />";
	    $associatedSimpleProduct = Mage::getModel('catalog/product')->load($associatedSimpleProduct);
	    //$cat = Mage::getModel('catalog/category')->load($categoryid) ;
	    $assoc_cats = $associatedSimpleProduct->getCategoryIds();
	    foreach ($assoc_cats as $assoc_categoryid) {
		$assoc_cat = Mage::getModel('catalog/category')->load($assoc_categoryid) ;
		$assoc_categoryName = $assoc_cat->getName();
	    }
	    $assoc_categoryName = $assoc_cat->getName();
	    $assoc_discountedPrice = number_format($associatedSimpleProduct->getFinalPrice(), 2);
	    
	    if($customerid != ''){
	    $assoc_wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerid, true);
	    $assoc_wishListItemCollection = $assoc_wishlist->getItemCollection();
	    
	    $assoc_productID = $associatedSimpleProduct->getId();
	    
	    foreach ($assoc_wishListItemCollection as $assoc_ProductItem):
		    if($assoc_ProductItem->getProductId() == $assoc_productID){
			    $assoc_isLike = true;
		    }else{
			    $assoc_isLike = false;
		    }
		    
	    endforeach;
	    
	    }
	    
	    $assoc_categoryParent = array(
		'id' => $assoc_categoryid,
		'name' => $assoc_categoryName
	    );
		
	    $categoryChild = array(
		'id' => $assoc_categoryid,
		'name' => $assoc_categoryName
	    );
		    
	    $assoc_image = array($associatedSimpleProduct->getImageUrl());
	    $assoc_productID = $associatedSimpleProduct->getId();
	    
	    $assoc__product_new = Mage::getModel('catalog/product')->load($assoc_productID);
	    $assoc_galleryImgArr = $assoc__product_new->getMediaGallery();
	    //var_dump($image[0]);
	    //echo "<br />";
	    $assoc_prdImages = "";
	    for($i=0;$i<count($assoc_galleryImgArr['images']);$i++){
		$assoc_prdImages[$i]['value_id'] = $mainURL.$assoc_galleryImgArr['images'][$i]['value_id'];
		$assoc_prdImages[$i]['file'] = $mainURL.$assoc_galleryImgArr['images'][$i]['file'];
		$assoc_prdImages[$i]['label'] = $assoc_galleryImgArr['images'][$i]['label'];
		$assoc_prdImages[$i]['position'] = $assoc_galleryImgArr['images'][$i]['position'];
		$assoc_prdImages[$i]['disabled'] = $assoc_galleryImgArr['images'][$i]['disabled'];
		
		$assoc_prdImages[$i]['label_default'] = $assoc_galleryImgArr['images'][$i]['label_default'];
		$assoc_prdImages[$i]['position_default'] = $assoc_galleryImgArr['images'][$i]['position_default'];
		$assoc_prdImages[$i]['disabled_default'] = $assoc_galleryImgArr['images'][$i]['disabled_default'];
	    }
	    
	    if(empty($assoc_prdImages)){
		//$assoc_prdImages = $image;
		$assoc_prdImages[0]['value_id'] = "";
		$assoc_prdImages[0]['file'] = $image[0];
		$assoc_prdImages[0]['label'] = "";
		$assoc_prdImages[0]['position'] = "";
		$assoc_prdImages[0]['disabled'] = "";
		
		$assoc_prdImages[0]['label_default'] = "";
		$assoc_prdImages[0]['position_default'] = "";
		$assoc_prdImages[0]['disabled_default'] = "";
	    }
		    
	    $assoc_colorOptions="";
	    $assoc_sizeOptions="";
	    $assoc_attributes = $associatedSimpleProduct->getAttributes();
	    foreach ($assoc_attributes as $assoc_attribute) {
		if($assoc_attribute->getAttributeCode() == "color_lee_cooper"){
		    $assoc_colorOptions = $assoc_attribute->getSource()->getAllOptions(false);
		}
		
		if($assoc_attribute->getAttributeCode() == "size_lee_cooper"){
		    $assoc_sizeOptions = $assoc_attribute->getSource()->getAllOptions(false);
		}
	    }
	    if($assoc_colorOptions !="" && !empty($assoc_colorOptions))
	    $assoc_color = $assoc_colorOptions;
	    else
	    $assoc_color = array();
	    
	    if($assoc_sizeOptions !="" && !empty($assoc_sizeOptions))
	    $assoc_size = $assoc_sizeOptions;
	    else
	    $assoc_size = array();
	
		$assoc_variants[] = array(
		'id' => $associatedSimpleProduct->getId(),
		'productsku' => $associatedSimpleProduct->getSKU(),
		'name' => $associatedSimpleProduct->getName(),
		'price' => number_format($associatedSimpleProduct->getPrice(), 2),
		'discounted' => $assoc_discountedPrice,
		'category' => $assoc_categoryParent,
		'subcategory' => $assoc_categoryChild,
		'images' => $assoc_image,
		'imagesgallery' => $assoc_prdImages,
		'is_like' => $assoc_isLike,
		'size' => $assoc_size,
		'color' => $assoc_color,
		'description' => $associatedSimpleProduct->getDescription(),
		'share_url' => $baseURL.$associatedSimpleProduct->getUrlPath()
		//'share_url' => $mainURLApi.'shareproduct.php?productid='.$associatedSimpleProduct->getId()
	    );
	}
    }
    return $assoc_variants;
}

var_dump(get_associated_products($_product));
die();



//$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
//$attributeValue = "Size";
//foreach ($attributes as $attribute) {
    //if ($attribute->usesSource()) {
	//$options = $attribute->getSource()->getAllOptions(false);
	//foreach ($options as $option) {
	//    if ($option['label'] == $attributeValue) {
	//	$attributeValueId = $option['value'];
	//    }
	//}
	//var_dump($options);
	//echo "<br/>=================================================<br/>";
    //}
	
//    //if ($attribute->getIsVisibleOnFront()) {
//        $attributeCode = $attribute->getAttributeCode();
//        $label = $attribute->getFrontend()->getLabel($_product);
//	//if($label == "Color"){
//	    $value = $attribute->getFrontend()->getValue($_product);
//	    echo $attributeCode . '-' . $label . '-' . $value; echo "<br />";
//	//}
//                
//    //}
//}

//foreach ($attributes as $attribute) {    
//    $attributeCode = $attribute->getAttributeCode();
//    $code = 'size_lee_cooper';
//    if ($attributeCode == $code) {
//        $label = $attribute->getStoreLabel($_product);    
//        $value = $attribute->getFrontend()->getValue($_product);
//        echo $attributeCode . '-' . $label . '-' . $value;
//    } 
//}
//
//$attributeCode = 'color';
//$attributeValue = 'Size';
// 
//$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
//if ($attribute->usesSource()) {
//    $options = $attribute->getSource()->getAllOptions(false);
//}
// 
//$attributeValueId = 0;
//foreach ($options as $option) {
//    if ($option['label'] == $attributeValue) {
//        $attributeValueId = $option['value'];
//    }
//}
//echo "<pre>";
$attributeCode = "size_lee_cooper";
//// Lets say $_product is the product object.
////$_attributes = Mage::helper('core')->decorateArray($_product->getAllowAttributes());
////$_attributes = $_product->getAttributes('catalog_product', $attributeCode);
$_attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
$attCode = $_attribute->getAttributeCode();
$attrId =  $_attribute->getAttributeId();
$frontend_label = $_attribute->getfrontend_label();
$frontend_title = $_attribute->getTitle();
//if($attCode == "size_lee_cooper"){
    echo "Code: ".$attCode."<br />";
    echo "ID: ".$attrId."<br />";
    echo "Label: ".$frontend_label."<br />";
    
$options = $_attribute->getSource()->getAllOptions(false);
foreach ($options as $option) {
    //echo $option['label']." <<<>>>>".$option['value']."<br/>";
//    if ($option['label'] == $attributeValue) {
//	$attributeValueId = $option['value'];
//    }
}
var_dump($attributes);
	
//}
//$_attributes = Mage::getSingleton('eav/config')->getAttributes();
//foreach($_attributes as $_attribute){
//    //$attCode = $_attribute->getProductAttribute()->getFrontend()->getAttribute()->getAttributeCode();
//    $attCode = $_attribute->getAttributeCode();
//    $attrId =  $_attribute->getAttributeId();
//    $frontend_label = $_attribute->getfrontend_label();
//    $frontend_title = $_attribute->getTitle();
//    if($attCode == "size_lee_cooper"){
//	echo "Code: ".$attCode."<br />";
//	echo "ID: ".$attrId."<br />";
//	echo "Label: ".$frontend_label."<br />";
//    }
//    //var_dump($frontend_label);
//    echo "<br/>";
//}
//frontend_label

//echo "==============================<br/>";
//// Lets say $_product is the product object.  
//$_attributes = Mage::helper('core')->decorateArray($_product->getAllowAttributes());  
//foreach($_attributes as $_attribute):  
//// Get Attribute Code  
//$attCode = $_attribute->getProductAttribute()->getFrontend()->getAttribute()->getAttributeCode();  
//// Get Attribute Id  
//$attrId =  $_attribute->getAttributeId();
//echo "Code: ".$attCode."<br />";
//echo "ID: ".$attrId."<br />";
//endforeach; 



var_dump();
die("<<<<");
$galleryImgArr = $_product->getMediaGallery();
//print_r(count($galleryImgArr['images']));
$prdImages = "";
for($i=0;$i<count($galleryImgArr['images']);$i++){
    $prdImages[$i]['value_id'] = $mainURL.$galleryImgArr['images'][$i]['value_id'];
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
	
	$size = array();
	
	$variants = array();

    $data['data'][] = array(
    'id' => $_product->getId(),
    'productsku' => $_product->getSKU(),
    'name' => $_product->getName(),
    'price' => number_format($_product->getPrice(), 2),
    'discounted' => $discountedPrice,
    'category' => $categoryParent,
    'subcategory' => $categoryChild,
    'images' => $image,
    'imagesgallery' => $prdImages,
    'is_like' => $isLike,
    'size' => $size,
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