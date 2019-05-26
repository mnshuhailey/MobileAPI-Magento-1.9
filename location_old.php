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

$method = $_REQUEST['method'];
//$email = $_REQUEST['email'];
$long = $_REQUEST['long'];
$lat = $_REQUEST['lat'];
$data = array();
//$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
//$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
$data['status'] = true;
$data['message'] = '';
if($method == 'nearby'){
    //Get Location
    $query = mysql_query("SELECT *, ( 3959 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($long) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance FROM location ORDER BY distance ASC", $con);
    //$query = mysql_query("SELECT *, (6371 * acos(cos(radians(" . $lat . ")) * cos(radians(latitude)) * cos(radians(longitude) - radians(" . $long . ")) + sin(radians(" . $lat . ")) * sin(radians(latitude)))) AS distance FROM location WHERE distance < 1000", $con);
    //6371 can be used for distance to KM directly
    //$countRow = mysql_fetch_row($query);
    //var_dump($countRow);
    //if(count($countRow) > 0):
    while($row = mysql_fetch_array($query))
    {
	$state = array(
	'id' => '',
	'name' => $row['statename']
	);
	//$country = array(
	//    'id' => '',
	//    'name' => $row['countryname']
	//);
	
	$address = array(
	'line_1' => $row['address1'],
	'line_2' => $row['address2'],
	'city' => $row['city'],
	'postcode' => $row['postcode'],
	'state' => $state,
	'country' => $row['countryname'],
	'phone' => $row['phone']
	);
	
	
	
	$coordinate = array(
	    'long' => $row['longitude'],
	    'lat' => $row['latitude']
	);
	
	$data['data'][] = array(
	    'id' => $row['locationid'],
	    'name' => $row['name'],
	    'image' => $row['image'],
	    'distance' => $row['distance'] * 1.609344,
	    'address' => $address,
	    'working_hour' => $row['hours'],
	    'coordinate' => $coordinate
	);
    }
    //else:
	//$data['status'] = false;
    //$data['message'] = '';
	//$data['data'] = array();
    //endif;
    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);
    
    echo $json_data;
}