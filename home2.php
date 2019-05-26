<?php 
include('lib/token.php');
include('lib/db.php');
require_once('../app/Mage.php');
Mage::app();

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://tcstaging.trendycounty.com/media/catalog/product';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';
$emBanner = 'https://tcstaging.trendycounty.com/media/em_minislideshow/';


$banners=array();
$query = mysql_query("SELECT * FROM em_minislideshow_slider WHERE id='18' LIMIT 1") or die(mysql_error());
$row = mysql_fetch_array($query);
$bannerImages = json_decode($row['images'], true);

for($i=0;$i<=count($bannerImages);$i++){
    if($bannerImages[$i]['url'] !=""){
	$vPath = str_replace("http://tcstaging.trendycounty.com/", "", $bannerImages[$i]['link']);
	$vPath = str_replace("https://tcstaging.trendycounty.com/", "", $vPath);
	$oRewrite = Mage::getModel('core/url_rewrite')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->loadByRequestPath($vPath);
	$iProductId = $oRewrite->getProductId();
	$banners[]=array(
	    'banner_image' => $emBanner.$bannerImages[$i]['url'],
	    'link' => $bannerImages[$i]['link'],
	    'action' => 1,
	    'product_id' => $iProductId
	);
    }
    
}
//var_dump($bannerImages['url']);



$customerid = $_REQUEST['customerid'];
$isLikedeals = false;
$isLikehot = false;

$data['status'] = true;
$data['message'] = '';
$isLike = false;
//$categoryid = $_product->getCategoryIds();

//$banners = array(array(
//		'banner_image' => 'https://tcstaging.trendycounty.com/media/em_minislideshow/1471509701_0_remaxbannerundone-07.jpg',
//		'action' => 1,
//		'product_id' => 527
//));


//Deals of the week
//$productidDeals = 299;
//$_productDeals = Mage::getModel('catalog/product')->load($productidDeals);
//
//$cats = $_productDeals->getCategoryIds();
//foreach ($cats as $categoryid) {
//    $cat = Mage::getModel('catalog/category')->load($categoryid) ;
//	$categoryName = $cat->getName();
//} 
//$discountedPrice = number_format($_productDeals->getFinalPrice(), 2);
//
//if($customerid != ''){
//    $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerid, true);
//    $wishListItemCollection = $wishlist->getItemCollection();
//    
//    $productID = $_productDeals->getId();
//
//    foreach ($wishListItemCollection as $Product):
//
//	if($Product->getProductID() == $productID){
//	    $isLikedeals = true;
//	}else{
//	    $isLikedeals = false;
//	}
//	    
//    endforeach;
//}
//
//    $categoryParent = array(
//        'id' => $categoryid,
//        'name' => $categoryName
//    );
//	
//    $categoryChild = array(
//	'id' => $categoryid,
//	'name' => $categoryName
//    );
//    
//    $image = array($_productDeals->getImageUrl());
//    
//    $galleryImgArr = $_productDeals->getMediaGallery();
//    $prdImages = "";
//    for($i=0;$i<count($galleryImgArr['images']);$i++){
//	$prdImages[$i]['value_id'] = $galleryImgArr['images'][$i]['value_id'];
//	$prdImages[$i]['file'] = $mainURL.$galleryImgArr['images'][$i]['file'];
//	$prdImages[$i]['label'] = $galleryImgArr['images'][$i]['label'];
//	$prdImages[$i]['position'] = $galleryImgArr['images'][$i]['position'];
//	$prdImages[$i]['disabled'] = $galleryImgArr['images'][$i]['disabled'];
//	
//	$prdImages[$i]['label_default'] = $galleryImgArr['images'][$i]['label_default'];
//	$prdImages[$i]['position_default'] = $galleryImgArr['images'][$i]['position_default'];
//	$prdImages[$i]['disabled_default'] = $galleryImgArr['images'][$i]['disabled_default'];
//    }
//    
//    if(empty($prdImages)){
//	//$prdImages = $image;
//	$prdImages[0]['value_id'] = "";
//	$prdImages[0]['file'] = $image[0];
//	$prdImages[0]['label'] = "";
//	$prdImages[0]['position'] = "";
//	$prdImages[0]['disabled'] = "";
//	
//	$prdImages[0]['label_default'] = "";
//	$prdImages[0]['position_default'] = "";
//	$prdImages[0]['disabled_default'] = "";
//    }
//	
//    
//    $productType = $_productDeals->getTypeID();
//    $size = array();
//    
//    $variants = array();
//
//    $deals = array(array(
//    'id' => $_productDeals->getId(),
//    'productsku' => $_productDeals->getSKU(),
//    'productType' => $productType,
//    'name' => $_productDeals->getName(),
//    'price' => number_format($_productDeals->getPrice(), 2),
//    'discounted' => $discountedPrice,
//    'category' => $categoryParent,
//    'subcategory' => $categoryChild,
//    'images' => $image,
//    'imagesgallery' => $prdImages,
//    'is_like' => $isLikedeals,
//    'size' => $size,
//    'variants' => $variants,
//    'description' => $_productDeals->getDescription(),
//    'share_url' => $baseURL.$_productDeals->getUrlPath()
//    //'share_url' => $mainURLApi.'shareproduct.php?productid='.$_productDeals->getId()
//		
//    ));
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
	    //var_dump($_product->getData());
	    //echo "</pre>";
	    
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
	
	    $deals[] = array(
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
	    //'share_url' => $mainURLApi.'shareproduct.php?productid='.$_product->getId()
			
	    );
	}
    endif;
}	
	

//Hot Deals
$categoryidHotDeals = 8;
$categoryhot = new Mage_Catalog_Model_Category();
$categoryhot->load($categoryidHotDeals);
$collectionhot = $categoryhot->getProductCollection();
$collectionhot->addAttributeToSelect('*');


$data['status'] = true;
$data['message'] = '';


foreach ($collectionhot as $_producthot){

$cat = Mage::getModel('catalog/category')->load($categoryidHotDeals) ;
$categoryName = $cat->getName();
$discountedPrice = number_format($_producthot->getFinalPrice(), 2);

if($customerid != ''){
$wishlisthot = Mage::getModel('wishlist/wishlist')->loadByCustomer(49, true);
$wishListItemCollectionhot = $wishlisthot->getItemCollection();

$productIDhot = $_producthot->getId();

foreach ($wishListItemCollectionhot as $Producthot):
	if($Producthot->getProductID() == $productIDhot){
		$isLikehot = true;
	}else{
		$isLikehot = false;
	}
	
endforeach;

}

	$categoryParent = array(
        'id' => $categoryidHotDeals,
        'name' => $categoryName
    );
	
	$categoryChild = array(
        'id' => $categoryidHotDeals,
        'name' => $categoryName
    );
	
	$image = array($_producthot->getImageUrl());
	
	$size = array();
	$productHotType = $_producthot->getTypeID();

	$datahotdeals[] = array(
        'id' => $_producthot->getId(),
        'productsku' => $_producthot->getSKU(),
	'productType' => $productHotType,
        'name' => $_producthot->getName(),
        'price' => number_format($_producthot->getPrice(), 2),
        'discounted' => $discountedPrice,
	'category' => $categoryParent,
	'subcategory' => $categoryChild,
	'images' => $image,
	'imagesgallery' => $prdImages,
	'is_like' => $isLikehot,
	'size' => $size,
	'description' => $_producthot->getDescription(),
	'share_url' => $baseURL.$_producthot->getUrlPath()
	//'share_url' => $mainURLApi.'shareproduct.php?productid='.$_producthot->getId()
		
    );

}
	$hotdealsCat = array(
        'id' => 3,
        'name' => 'Hot Deals'
	);
	
	$hotdeals[] = array(
        'category' => $hotdealsCat,
        'products' => $datahotdeals
	);
	



	$data['data'][] = array(
        'banners' => $banners,
        'deals_of_week' => $deals,
        'hotdeals' => $hotdeals
	);



$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//sample url: http://tcstaging.trendycounty.com/mobileapi/product-details.php?key=5pkmEyb8TPhx7eex87BMxcY77vyhhnqXAzUQHVPt&productid=8&customerid=49
?>