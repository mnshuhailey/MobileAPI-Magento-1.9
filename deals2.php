<?php 
include('lib/token.php');
include('lib/db.php');
require_once('../app/Mage.php');
Mage::app();

Mage::init('default');  
Mage::getSingleton('core/session', array('name' => 'frontend')); 
header('Content-Type: application/json; Charset=UTF-8');

$mainURL = 'http://tcstaging.trendycounty.com/media/catalog/product';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';

if(isset($_REQUEST['categoryid']) && $_REQUEST['categoryid'] > 0)
    $categoryid = $_REQUEST['categoryid'];
else
    $categoryid = 3;
    
    

    
    
//em_multideal_store  em_multideal
$customerid = $_REQUEST['customerid'];

//$category = new Mage_Catalog_Model_Category();
//$category->load($categoryid);
//$collection = $category->getProductCollection();
//$collection->addAttributeToSelect('*');

$data['status'] = true;
$data['message'] = '';

$isLike = false;

$websiteId = Mage::app()->getWebsite()->getId();
$store = Mage::app()->getStore();
$currentTime = time();
$query = mysql_query("SELECT * FROM em_multideal 
		     LEFT JOIN em_multideal_store
		     ON em_multideal.deal_id = em_multideal_store.deal_id
		     WHERE em_multideal_store.store_id='1'
			    AND em_multideal.date_from <= '$currentTime'
			    AND em_multideal.date_to >='$currentTime'
			    AND em_multideal.is_active='1'
			    AND em_multideal.status='1'
		    ORDER BY em_multideal.date_to ASC") or die(mysql_error());

		//    echo "SELECT * FROM em_multideal 
		//     LEFT JOIN em_multideal_store
		//     ON em_multideal.deal_id = em_multideal_store.deal_id
		//     WHERE em_multideal_store.store_id='1'
		//	    AND em_multideal.date_from <= '$currentTime'
		//	    AND em_multideal.date_to >='$currentTime'
		//	    AND em_multideal.is_active='1'
		//	    AND em_multideal.status='1'
		//    ORDER BY em_multideal.date_to ASC";
		//    echo "<br />";
		//    echo "<pre>";
		//    var_dump($query);
		//    echo "</pre>";
		//    
		//    echo "<br/>";
while($row = mysql_fetch_array($query))
{
    $deal_id = $row['deal_id'];
    $product_id = $row['product_id'];
    $after_end = $row['after_end'];
    $recent = $row['recent'];
    $price = $row['price'];
    $DB_qty = $row['qty'];
    $date_from = $row['date_from'];
    $date_to = $row['date_to'];
    $status = $row['status'];
    $qty_sold = $row['qty_sold'];
    $creation_time = $row['creation_time'];
    $update_time = $row['update_time'];
    $is_active = $row['is_active'];
    if($product_id > 0):
	$_product = Mage::getModel('catalog/product')->load($product_id);
	    //echo ">>>> $product_id <<<< <pre>";
	    //var_dump($_product->getId());
	    //echo "</pre>";
	    //
	    //echo "<br/>";
	if ($_product->getId() > 0) {
	    $categoryIds = $_product->getCategoryIds();
	    //$parentCatId = 1;
	    $subCarentCatId = 1;
	    for($i=0;$i<count($categoryIds);$i++){
		//if($categoryIds[$i] > 1)
		$subCarentCatId = $categoryIds[$i];
		if($subCarentCatId == 3)
		break;
	    }
	    if (is_array($categoryIds) and count($categoryIds) >= 1) {
		$cat = Mage::getModel('catalog/category')->load($subCarentCatId);
		$categoryName = $cat->getName();
		$categoryid = $cat->getId();
		if($cat->getParentId() > 0){
		    if($cat->getParentId() == 1){
			$categoryParentName = $cat->getName();
			$categoryParentId = $cat->getId();
		    }else{
			$parentCat = Mage::getModel('catalog/category')->load($cat->getParentId());
			$categoryParentName = $parentCat->getName();
			$categoryParentId = $parentCat->getId();
		    }
		}else{
		    $categoryParentName = "";
		    $categoryParentId = "";
		}
	    }else{
		$categoryName="";
		$categoryid="";
		$categoryParentName = "";
		$categoryParentId = "";
	    }
	
	//    if($cat !="" && $cat->getId() > 0){
	//	$categoryName = $cat->getName();
	//	$categoryid = $cat->getId();
	//    }else{
	//	$categoryName="";
	//	$categoryid="";
	//    }
	    $categoryParent = array(
		'id' => $categoryParentId,
		'name' => $categoryParentName
	    );
		
	    $categoryChild = array(
		'id' => $categoryid,
		'name' => $categoryName
	    );
	    //$cat = Mage::getModel('catalog/category')->load($categoryid) ;
	    
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
	    //if($colorOptions !="" && !empty($colorOptions))
	    //$color = $colorOptions;
	    //else
	    $color = array();
	    
	    //if($sizeOptions !="" && !empty($sizeOptions))
	    //$size = $sizeOptions;
	    //else
	    $size = array();
	    //$productType = $_product->getTypeID();
	
	    $data['data'][] = array(
	    'id' => $_product->getId(),
	    'productsku' => $_product->getSKU(),
	    'productType' => $_product->getTypeID(),
	    'name' => $_product->getName(),
	    'price' => number_format($_product->getPrice(), 2),
	    'deal_price'=> number_format($price, 2),
	    'deal_qty' => $DB_qty,
	    'date_from' => $date_from,
	    'date_to' => $date_to,
	    'qty_sold' => $qty_sold,
	    'is_active' => $is_active,
	    'discounted' => $discountedPrice,
	    'category' => $categoryParent,
	    'subcategory' => $categoryChild,
	    'images' => $image,
	    'is_like' => $isLike,
	    'size' => $size,
	    'color' => $color,
	    'description' => $_product->getDescription(),
	    'share_url' => $baseURL.$_product->getUrlPath()
			
	    );
	}
    endif;
}

$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//sample url: http://tcstaging.trendycounty.com/mobileapi/list-product.php?subcategoryid=4
?>