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
$categories = Mage::getModel('catalog/category')->getCollection()
			->addAttributeToSelect('id')
			->addAttributeToSelect('name')
			->addAttributeToSelect('is_active')
			->addIdFilter(2);
$data = array();
$data['status'] = true;
$data['message'] = "";
foreach ($categories as $category)
{

    
    //print $category;
    if ($category->getIsActive()) {
	$img = getCategoryImage($category);
	if($img == false)
	$img = "";
	//if($category->getId() == 2) continue;
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
