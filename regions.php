<?php 
include('lib/token.php');
include('lib/db.php');
require_once('../app/Mage.php');
Mage::app();

Mage::init('default');  
Mage::getSingleton('core/session', array('name' => 'frontend')); 

header('Content-Type: application/json; Charset=UTF-8');

$method = $_REQUEST['method'];
//$email = $_REQUEST['email'];
$country_code = $_REQUEST['country_code'];
$data = array();
//$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
//$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
$data['status'] = true;
$data['message'] = '';
if($method == 'list'){
    //Get Location
    $query = mysql_query("SELECT * FROM regions WHERE country_code='$country_code' ORDER BY name ASC");
    while($row = mysql_fetch_array($query))
    {
	$region = array(
            'region_id' => $row['id'],
            'region' => $row['name'],
            'country_code' => $row['country_code']
	);
	
	
	$data['data'][] = $region;
    }

    $json_data = json_encode($data);
    $json_data = str_replace('\/','/',$json_data);
    $json_data = str_replace('null','""',$json_data);
    
    echo $json_data;
}