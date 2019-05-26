<?php 
require_once('../app/Mage.php');
Mage::app();

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://staging.trendycounty.com/';
$mainURLApi = 'http://staging.trendycounty.com/mobileapi/';

$productid = $_REQUEST['productid'];

$productid = $product->getId();
$reviews = Mage::getModel('review/review')
				->getResourceCollection()
				->addStoreFilter(Mage::app()->getStore()->getId())
				->addEntityFilter('product', $productid)
				->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
				->setDateOrder()
				->addRateVotes();

$data['status'] = true;
$data['message'] = '';

$avg = 0;
$ratings = array();

if (count($reviews) > 0) {
	foreach ($reviews->getItems() as $review) {
		foreach( $review->getRatingVotes() as $vote ) {
			$ratings[] = $vote->getPercent();
		}
	}
	$avg = array_sum($ratings)/count($ratings);
}


$json_data = json_encode($avg);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

//sample url: http://staging.trendycounty.com/mobileapi/product-details.php?productid=8
