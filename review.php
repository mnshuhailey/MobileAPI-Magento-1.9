<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://tcstaging.trendycounty.com/';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';

$method = $_REQUEST['method'];

if($method == 'submit'){
	$customerid = $_REQUEST['customerid'];
	$productid = $_REQUEST['productid'];
	$star = $_REQUEST['star']; //1 until 5 
	$title = $_REQUEST['title'];
	$reviewDetails = $_REQUEST['comment'];
	
	$customers = Mage::getModel('customer/customer')->load($customerid);
	$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
	
	$nickname = $customers->getFirstname();
	
	$review = Mage::getModel('review/review');
	$review->setEntityPkValue($productid);//product id
	$review->setEntityId(1);
	$review->setStatusId(1);
	$review->setTitle($title);
	$review->setDetail($reviewDetails);                                      
	$review->setStoreId(Mage::app()->getStore()->getId());            
	$review->setCustomerId($customerid);
	$review->setNickname($nickname);
	$review->setReviewId($review->getId());
	$review->setStores(array(Mage::app()->getStore()->getId()));
	$review->save();
	$review->aggregate();
	
	
	//foreach($rating_options as $rating_id => $option_ids):
	//try {
	//$_rating = Mage::getModel('rating/rating')
	//->setRatingId($rating_id)
	//->setReviewId($_review->getId())
	//->addOptionVote($option_ids[$rating_value-1],$_product->getId());
	//} catch (Exception $e) {
	//die($e->getMessage());
	//}
	//endforeach;
	
	
	$rating = Mage::getModel('rating/rating')
        ->setRatingId(1)            
        ->setReviewId($review->getId())            
        ->setCustomerId($customerid)            
        ->addOptionVote($star,$productid);
	
	
	$data['status'] = true;
	$data['message'] = '';
	$data['data']= array();
	
    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);
    
    echo $json_data;
}


if($method == 'list'){
$productid = $_REQUEST['productid'];

$reviews = Mage::getModel('review/review')->getCollection()
		->addStoreFilter(Mage::app()->getStore()->getId())
		->addEntityFilter('product', $productid)
		->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
		->setDateOrder()
		->addRateVotes();

$data['status'] = true;
$data['message'] = '';
$totalAllRating=0;
$totalAllCoount = 0;
foreach ($reviews as $review):

    $customerid = $review->getcustomer_id();
    if($_REQUEST['mm'] == "yes"){
	$customerid."<<<<< <br/>";
    }
	$customers = Mage::getModel('customer/customer')->load($customerid);
	$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customers);
	//Get address
	foreach ($customers->getAddresses() as $address)
	{
		$address = $address->toArray();
	}
	
	$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customers->getemail());
	//if($subscriber->getId())
	if($subscriber->getStatus() == 1)
	{
	    $dataNewsletter = true;
	}else{
	    $dataNewsletter = false;
	}

	$user[] = array(
			'id' => $customers->getId(),
			'first_name' => $customers->getfirstname(),
			'last_name' => $customers->getlastname(),
			'email_addres' => $customers->getemail(),
			'news_letter' => $dataNewsletter,
			'gender' => $customers->getgender(),
			'phone' => $customers->getphone(),       
			'dob' => $customers->getdob(),
			'default_address' => $address
		); 



    $votes = $review->getRatingVotes();
    //var_dump($votes); 
    //die();
    $total = 0;
    foreach($votes AS $vote)
    {
        $total += $vote->getPercent();
    } 
    $avg = $total / count($votes);
    $totalAllRating += $total;
    $totalAllCount++;
    
    //var_dump($total, count($votes), $avg); 
	
	$data['data'][] = array(
        'id' => $review->getId(),
        'message' => $review->getDetail(),
        'rating' => $avg,
        'data_added' => $review->getcreated_at(),
	'user' => $user
    );

endforeach;
$totalAllRatingAvg = $totalAllRating / $totalAllCount;
$data['total_rating'] = round($totalAllRatingAvg);
$json_data = json_encode($data);
$json_data = str_replace('\/','/',$json_data);
$json_data = str_replace('null','""',$json_data);

echo $json_data;

}
