<?php 
require_once('../app/Mage.php');
Mage::app();
function getCategoryImage($category) 
{
    $categoryCollection = Mage::getModel('catalog/category')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->getCollection()
                        ->addAttributeToSelect('image')
                        ->addIdFilter($category->getId());

    foreach($categoryCollection as $category) 
    {
        return $category->getImageUrl();
    }
}

$categoryid = $_REQUEST['categoryid'];

$categories = Mage::getModel('catalog/category')->load($categoryid)->getChildrenCategories();

$data = array();
$data['status'] = true;
$data['message'] = "";
$imageName = '';
foreach ($categories as $category)
{ 
    if ($category->getIsActive()) {
	$img = getCategoryImage($category);
	if($img == false)
	$img = "";
	$data['data'][] = array(
				'id' => $category->getId(),
				'name' => $category->getName(),
				'image' => $img
				);
    }
}

$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;
