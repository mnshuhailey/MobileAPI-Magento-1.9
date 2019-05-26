<?php
function get_associated_products_bkp($_product){
    $baseURL = 'https://trendycounty.com/';
    $mainURL = 'http://trendycounty.com/media/catalog/product';
    $productType = $_product->getTypeID();
    $assoc_variants = array();
    if($productType == 'configurable')
    {   
	$associatedSimpleProducts = $_product->getTypeInstance()->getUsedProductIds();
	//var_dump($associatedSimpleProducts);
	//die();
    // }elseif($productType == 'simple'){
    }else{
	$productid = $_product->getId();
	$parentID = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($productid);
	if(is_int($parentID) && $parentID > 0){
	    $_productNew = Mage::getModel('catalog/product')->load($parentID);
	    
	    if($_REQUEST['mm'] == "yes"){
		die(var_dump(is_int($parentID)));
		//$chckAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
	    }
	    
	    //if($_productNew->getTypeInstance()->getUsedProductIds())
	    $associatedSimpleProducts = $_productNew->getTypeInstance()->getUsedProductIds();
	    /*
	    //this part is for excluding simple product id from the array for future uage
	    $associatedSimpleProducts = array();
	    $associatedSimpleProductsPre = $_product->getTypeInstance()->getUsedProductIds();
	    $counter=0;
	    foreach($associatedSimpleProductsPre as $associatedSimpleProductPre){
		if($associatedSimpleProductPre != $productid){
		    $associatedSimpleProducts[$counter] = $associatedSimpleProductPre;
		    $counter++;
		}
	    }
	    */
	}
    }
    
    if($_REQUEST['mm'] == "yes"){
	//die(var_dump($associatedSimpleProducts));
	//$chckAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
    }
	foreach($associatedSimpleProducts as $associatedSimpleProduct){
	    //echo "$associatedSimpleProduct <br />";
	    $associatedSimpleProduct = Mage::getModel('catalog/product')->load($associatedSimpleProduct);
	    //$cat = Mage::getModel('catalog/category')->load($categoryid) ;
	    $assoc_cats = $associatedSimpleProduct->getCategoryIds();
	    foreach ($assoc_cats as $assoc_categoryid) {
		$assoc_cat = Mage::getModel('catalog/category')->load($assoc_categoryid) ;
		$assoc_categoryName = $assoc_cat->getName();
	    }
	    if(isset($assoc_cat) && $assoc_cat->getName())
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
		$assoc_prdImages[$i]['value_id'] = $assoc_galleryImgArr['images'][$i]['value_id'];
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
	    $productTypeVal = $associatedSimpleProduct->getTypeID();
	
		$assoc_variants[] = array(
		'id' => $associatedSimpleProduct->getId(),
		'productsku' => $associatedSimpleProduct->getSKU(),
		'productType' => $productTypeVal,
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
    
    return $assoc_variants;
}
function check_associated_product_qty($_product){
    $productType = $_product->getTypeID();
   
    if($productType == 'configurable')
    {
	$totalQTY="0";
	$associatedSimpleProducts = $_product->getTypeInstance()->getUsedProductIds();
	
	foreach($associatedSimpleProducts as $associatedSimpleProduct){
	    $associatedSimpleProductN = Mage::getModel('catalog/product')->load($associatedSimpleProduct);
	    $associatedSimpleQTY = number_format($associatedSimpleProductN->getStockItem()->getQty(), 0);
	    $totalQTY = $totalQTY + $associatedSimpleQTY;
	}
    }else{
	 $totalQTY="-1";
    }
    return $totalQTY;
}
function get_associated_products($_product){
    $baseURL = 'https://trendycounty.com/';
    $mainURL = 'http://trendycounty.com/media/catalog/product';
    $productType = $_product->getTypeID();
    $assoc_variants = array();
    if($productType == 'configurable')
    {   
	$associatedSimpleProducts = $_product->getTypeInstance()->getUsedProductIds();
	foreach($associatedSimpleProducts as $associatedSimpleProduct){
	    //echo "$associatedSimpleProduct <br />";
	    
	    $associatedSimpleProduct = Mage::getModel('catalog/product')->load($associatedSimpleProduct);
	    $associatedSimpleQTY = number_format($associatedSimpleProduct->getStockItem()->getQty(), 0);
	    //$cat = Mage::getModel('catalog/category')->load($categoryid) ;
	    if($associatedSimpleQTY > 0){
		$assoc_cats = $associatedSimpleProduct->getCategoryIds();
		foreach ($assoc_cats as $assoc_categoryid) {
		    $assoc_cat = Mage::getModel('catalog/category')->load($assoc_categoryid) ;
		    $assoc_categoryName = $assoc_cat->getName();
		}
		if(isset($assoc_cat) && $assoc_cat->getName())
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
		    $assoc_prdImages[$i]['value_id'] = $assoc_galleryImgArr['images'][$i]['value_id'];
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
		$productTypeVal = $associatedSimpleProduct->getTypeID();
		$assoc_size2="";
		$assoc_size2[] = get_final_product_size_mm($associatedSimpleProduct);
		if(empty($assoc_size2) || $assoc_size2[0] == "")
		$assoc_size2 = array();
		
		$assoc_color2="";
		$assoc_color2[] = get_final_product_color_mm($associatedSimpleProduct);
		if(empty($assoc_color2) || $assoc_color2[0] == "")
		$assoc_color2 = array();
	    
		    $assoc_variants[] = array(
		    'id' => $associatedSimpleProduct->getId(),
		    'productsku' => $associatedSimpleProduct->getSKU(),
		    'productType' => $productTypeVal,
		    'qty' => $associatedSimpleQTY,
		    'name' => $associatedSimpleProduct->getName(),
		    'price' => number_format($associatedSimpleProduct->getPrice(), 2),
		    'discounted' => $assoc_discountedPrice,
		    'category' => $assoc_categoryParent,
		    'subcategory' => $assoc_categoryChild,
		    'images' => $assoc_image,
		    'imagesgallery' => $assoc_prdImages,
		    'is_like' => $assoc_isLike,
		    'size' => $assoc_size2,
		    'color' => $assoc_color2,
		    'description' => $associatedSimpleProduct->getDescription(),
		    'share_url' => $baseURL.$associatedSimpleProduct->getUrlPath()
		    //'share_url' => $mainURLApi.'shareproduct.php?productid='.$associatedSimpleProduct->getId()
		);
	    }
	}
    }else{

    }
    
    if($_REQUEST['mm'] == "yes"){
	//die(var_dump($associatedSimpleProducts));
	//$chckAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
    }
    return $assoc_variants;
}

function get_final_product_size_mm($_product){
    $attributeCode = $_product->getsize_attribute_code();
    if($attributeCode == "")
    $attributeCode = "size_lee_cooper";
    $mmSizeObj = "get$attributeCode";
    
    $attributeMM = $_product->getResource()->getAttribute($attributeCode);
    $sizeNew = $attributeMM->getSource()->getAllOptions(false);
    
    $finaleSize = array();
    $productType = $_product->getTypeID();
    if($productType == "simple"){
	for($k=0;$k<=count($sizeNew);$k++){
	    if($_product->$mmSizeObj() == $sizeNew[$k]['value']){
		$finaleSize = $sizeNew[$k];
		break;
	    }
	}
    }elseif($productType == "configurable"){
	$associatedSimpleProducts = $_product->getTypeInstance()->getUsedProductIds();
	foreach($associatedSimpleProducts as $associatedSimpleProduct){
	    $associatedSimpleProduct = Mage::getModel('catalog/product')->load($associatedSimpleProduct);
	    $assocProductType = $associatedSimpleProduct->getTypeID();
	    
	    if($assocProductType == "simple"){
		for($k=0;$k<=count($sizeNew);$k++){
		    if($associatedSimpleProduct->$mmSizeObj() == $sizeNew[$k]['value']){
			if(!in_array($sizeNew[$k], $finaleSize, true) && !empty($sizeNew[$k])){
			    $finaleSize[]= $sizeNew[$k];
			}
			break;
		    }
		}
	    }
	    
	}
    }
    return $finaleSize;
}

function get_final_product_color_mm($_product){
    $attributeCode = $_product->getcolor_attribute_code();
    if($attributeCode == "")
    $attributeCode = "color_lee_cooper";
    $mmObj = "get$attributeCode";
    
    $attributeMM = $_product->getResource()->getAttribute($attributeCode);
    $colorNew = $attributeMM->getSource()->getAllOptions(false);
    
    
    $finaleColor = array();
    $productType = $_product->getTypeID();
    if($productType == "simple"){
	
	//var_dump($_product->$mmObj());
	//die();
	for($k=0;$k<=count($colorNew);$k++){
	    if($_product->$mmObj() == $colorNew[$k]['value']){
		$finaleColor = $colorNew[$k];
		break;
	    }
	}
    }elseif($productType == "configurable"){
	
	$associatedSimpleProducts = $_product->getTypeInstance()->getUsedProductIds();
	//var_dump($colorNew);
	//die();
	foreach($associatedSimpleProducts as $associatedSimpleProduct){
	    $associatedSimpleProduct = Mage::getModel('catalog/product')->load($associatedSimpleProduct);
	    $assocProductType = $associatedSimpleProduct->getTypeID();
	    //var_dump($finaleColor);
	    //echo "====";
	    if($assocProductType == "simple"){
		
		for($k=0;$k<=count($colorNew);$k++){
		    //var_dump(">>>>>>>>".$colorNew[$k]['value']);
		    if($associatedSimpleProduct->$mmObj() == $colorNew[$k]['value']){
			if(!in_array($colorNew[$k], $finaleColor, true) && !empty($colorNew[$k])){
			    $finaleColor[]= $colorNew[$k];
			}
			break;
		    }
		}
	    }
	    
	}
    }
    //if(empty($finaleColor))
    //$finaleColor = "";
    
    return $finaleColor;
}

function get_final_product_size($_product, $sizeNew){
    $finaleSize = array();
    $productType = $_product->getTypeID();
    if($productType == "simple"){
	for($k=0;$k<=count($sizeNew);$k++){
	    if($_product->getsize_lee_cooper() == $sizeNew[$k]['value']){
		$finaleSize = $sizeNew[$k];
		break;
	    }
	}
    }
    
    if($productType == "configurable"){
	$associatedSimpleProducts = $_product->getTypeInstance()->getUsedProductIds();
	foreach($associatedSimpleProducts as $associatedSimpleProduct){
	    $associatedSimpleProduct = Mage::getModel('catalog/product')->load($associatedSimpleProduct);
	    $assocProductType = $associatedSimpleProduct->getTypeID();
	    
	    if($assocProductType == "simple"){
		for($k=0;$k<=count($sizeNew);$k++){
		    if($associatedSimpleProduct->getsize_lee_cooper() == $sizeNew[$k]['value']){
			if(!in_array($sizeNew[$k], $finaleSize, true) && !empty($sizeNew[$k])){
			    $finaleSize[]= $sizeNew[$k];
			}
			break;
		    }
		}
	    }
	    
	}
    }
    //var_dump($_product->getId());
    //echo "<<<<<>>>";
    //var_dump($finaleSize);
    //die();
    //if(empty($finaleSize))
    //$finaleSize = "";
    return $finaleSize;
}

function get_final_product_color($_product, $colorNew){
    
    
    
    $finaleColor = array();
    $productType = $_product->getTypeID();
    if($productType == "simple"){
	for($k=0;$k<=count($colorNew);$k++){
	    if($_product->getcolor_lee_cooper() == $colorNew[$k]['value']){
		$finaleColor = $colorNew[$k];
		break;
	    }
	}
    }
    
    if($productType == "configurable"){
	
	$associatedSimpleProducts = $_product->getTypeInstance()->getUsedProductIds();
	//var_dump($colorNew);
	//die();
	foreach($associatedSimpleProducts as $associatedSimpleProduct){
	    $associatedSimpleProduct = Mage::getModel('catalog/product')->load($associatedSimpleProduct);
	    $assocProductType = $associatedSimpleProduct->getTypeID();
	    //var_dump($finaleColor);
	    //echo "====";
	    if($assocProductType == "simple"){
		
		for($k=0;$k<=count($colorNew);$k++){
		    //var_dump(">>>>>>>>".$colorNew[$k]['value']);
		    if($associatedSimpleProduct->getcolor_lee_cooper() == $colorNew[$k]['value']){
			if(!in_array($colorNew[$k], $finaleColor, true) && !empty($colorNew[$k])){
			    $finaleColor[]= $colorNew[$k];
			}
			break;
		    }
		}
	    }
	    
	}
    }
    //if(empty($finaleColor))
    //$finaleColor = "";
    
    return $finaleColor;
}

function loginByEmail($email, $websiteId)
{
    Mage::init($websiteId, 'website');
        // ensure that we are on the correct website
    $customer = Mage::getModel('customer/customer');
    $customer->setWebsiteId($websiteId);
        // the website must be set here again!!!
    $customer->loadByEmail($email);
    $session = Mage::getSingleton('customer/session');
    if ($customer->getId()) {
        $session->setCustomerAsLoggedIn($customer);
        return $session;
    }
    throw new Exception('Login failed');
}

function get_associated_products_new($_product){
    $baseURL = 'https://trendycounty.com/';
    $mainURL = 'http://trendycounty.com/media/catalog/product';
    $productType = $_product->getTypeID();
    $parentArr = array();
    $sizeArray = array();
    $colorArray = array();
    $assoc_variants = array();
    $prdQTYHas=false;
    if($productType == 'configurable')
    {   
	$associatedSimpleProducts = $_product->getTypeInstance()->getUsedProductIds();
	//var_dump($associatedSimpleProducts);
	//die();
    // }elseif($productType == 'simple'){
    }else{
	//$productid = $_product->getId();
	//$parentID = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($productid);
	//if(is_int($parentID) && $parentID > 0){
	//    $_productNew = Mage::getModel('catalog/product')->load($parentID);
	//    
	//    if($_REQUEST['mm'] == "yes"){
	//	die(var_dump(is_int($parentID)));
	//	//$chckAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
	//    }
	//    
	//    //if($_productNew->getTypeInstance()->getUsedProductIds())
	//    $associatedSimpleProducts = $_productNew->getTypeInstance()->getUsedProductIds();
	//    /*
	//    //this part is for excluding simple product id from the array for future uage
	//    $associatedSimpleProducts = array();
	//    $associatedSimpleProductsPre = $_product->getTypeInstance()->getUsedProductIds();
	//    $counter=0;
	//    foreach($associatedSimpleProductsPre as $associatedSimpleProductPre){
	//	if($associatedSimpleProductPre != $productid){
	//	    $associatedSimpleProducts[$counter] = $associatedSimpleProductPre;
	//	    $counter++;
	//	}
	//    }
	//    */
	//}
    }
    
    if($_REQUEST['mm'] == "yes"){
	//die(var_dump($associatedSimpleProducts));
	//$chckAddress->setIsDefaultBilling('1')->setIsDefaultShipping('1')->save();
    }
    if($productType == 'configurable'){
	foreach($associatedSimpleProducts as $associatedSimpleProduct){
	    //echo "$associatedSimpleProduct <br />";
	    $associatedSimpleProduct = Mage::getModel('catalog/product')->load($associatedSimpleProduct);
	    $associatedSimpleQTY = number_format($associatedSimpleProduct->getStockItem()->getQty(), 0);
	    if($associatedSimpleQTY > 0){
		//$cat = Mage::getModel('catalog/category')->load($categoryid) ;
		$assoc_cats = $associatedSimpleProduct->getCategoryIds();
		foreach ($assoc_cats as $assoc_categoryid) {
		    $assoc_cat = Mage::getModel('catalog/category')->load($assoc_categoryid) ;
		    $assoc_categoryName = $assoc_cat->getName();
		}
		if(isset($assoc_cat) && $assoc_cat->getName())
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
		    $assoc_prdImages[$i]['value_id'] = $assoc_galleryImgArr['images'][$i]['value_id'];
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
			if(!in_array($assoc_colorOptions, $colorArray)){
			    //$colorArray[]=$assoc_colorOptions;
			    array_push($colorArray, $assoc_colorOptions);
			}
		    }
		    
		    if($assoc_attribute->getAttributeCode() == "size_lee_cooper"){
			$assoc_sizeOptions = $assoc_attribute->getSource()->getAllOptions(false);
			if(!in_array($assoc_sizeOptions, $sizeArray)){
			    //$sizeArray[]=$assoc_sizeOptions;
			    array_push($sizeArray, $assoc_sizeOptions);
			}
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
		$productTypeVal = $associatedSimpleProduct->getTypeID();
		//if($associatedSimpleProduct->getQty() > 0){
		//    $assoc_variants[] = array(
		//	'id' => $associatedSimpleProduct->getId(),
		//	'productsku' => $associatedSimpleProduct->getSKU(),
		//	'productType' => $productTypeVal,
		//	'name' => $associatedSimpleProduct->getName(),
		//	'price' => number_format($associatedSimpleProduct->getPrice(), 2),
		//	'discounted' => $assoc_discountedPrice,
		//	'category' => $assoc_categoryParent,
		//	'subcategory' => $assoc_categoryChild,
		//	'images' => $assoc_image,
		//	'imagesgallery' => $assoc_prdImages,
		//	'is_like' => $assoc_isLike,
		//	'size' => $assoc_size,
		//	'color' => $assoc_color,
		//	'description' => $associatedSimpleProduct->getDescription(),
		//	'share_url' => $baseURL.$associatedSimpleProduct->getUrlPath()
		//	//'share_url' => $mainURLApi.'shareproduct.php?productid='.$associatedSimpleProduct->getId()
		//    );
		    
		//}
		$prdQTYHas = true;
	    }
	}
    }
    //$parentArr['assoc_variants'] = $assoc_variants;
    $parentArr['assoc_sizes'] = $sizeArray;
    $parentArr['assoc_colors'] = $colorArray;
    $parentArr['prdQTYHas'] = $prdQTYHas;
    // $product->getQty()
    return $parentArr;
}
?>