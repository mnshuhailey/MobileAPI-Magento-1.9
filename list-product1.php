<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();
include('lib/global.php');

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://tcstaging.trendycounty.com/media/catalog/product';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';

$categoryid = @$_REQUEST['subcategoryid'];
$customerid = @$_REQUEST['customerid'];
$lastId = @$_REQUEST['lastId'];
//$category = new Mage_Catalog_Model_Category();
//$category->load($categoryid);
//$collection = $category->getProductCollection();
////$collection->addAttributeToSelect('*');
//$collection->addAttributeToFilter('type_id', 'configurable');
if($lastId > 0){
    $collection = Mage::getResourceModel('catalog/product_collection')
	->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
	->addAttributeToFilter('category_id', array('in' => $categoryid))
	->addAttributeToFilter('type_id', 'configurable')
	->addAttributeToSelect('*')
        ->addFieldToFilter('entity_id', array('gt' => $lastId))
        ->SetPageSize(20)
	->load();
}else{
    $collection = Mage::getResourceModel('catalog/product_collection')
	->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
	->addAttributeToFilter('category_id', array('in' => $categoryid))
	->addAttributeToFilter('type_id', 'configurable')
	->addAttributeToSelect('*')
        ->addFieldToFilter('entity_id', array('gt' => 0))
        ->SetPageSize(20)
	->load();
}

//->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED)); 

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
$totalNumebr = 0;
foreach ($collection as $_product):
    //if($_REQUEST['mm'] == "yes"){
    //	var_dump($_product->getData());
    //	echo ">>>><br/>";
    //}
    $totalQTY = check_associated_product_qty($_product);
    
    //if($totalQTY > 0){
        //$totalNumebr++;
	$discountedPrice = number_format($_product->getFinalPrice(), 2);
	$image = array($_product->getImageUrl());
	$productType = $_product->getTypeID();
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
            'total_qty' => "$totalQTY",
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
    //}

endforeach;
if($data['data'] == "")
$data['data'] = array();

$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;