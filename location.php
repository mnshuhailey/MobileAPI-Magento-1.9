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

//die(date('l'));
$weekDay = strtolower(date('l'));
$weekDayOpen = "$weekDay"."_open";
$weekDayClose = "$weekDay"."_close";
$weekDayStat = "$weekDay"."_status";
//$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
//$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
$data['status'] = true;
$data['message'] = '';
if($method == 'nearby'){
    
    //`store_id`, `store_name`, `store_manager`, `store_email`, `store_phone`, `store_fax`, `description`, `status`, `address`,
    //`address_2`, `state`, `suburb`, `city`, `region_id`, `city_id`, `suburb_id`, `zipcode`, `state_id`, `country`,
    //`store_latitude`, `store_longitude`, `monday_status`, `monday_time_interval`, `monday_open`, `monday_open_break`,
    //`monday_close`, `monday_close_break`, `monday_available_slot`, `tuesday_status`, `tuesday_time_interval`, `tuesday_open`,
    //`tuesday_open_break`, `tuesday_close`, `tuesday_close_break`, `tuesday_available_slot`, `wednesday_status`,
    //`wednesday_time_interval`, `wednesday_open`, `wednesday_open_break`, `wednesday_close`, `wednesday_close_break`,
    //`wednesday_available_slot`, `thursday_status`, `thursday_time_interval`, `thursday_open`, `thursday_open_break`,
    //`thursday_close`, `thursday_close_break`, `thursday_available_slot`, `friday_status`, `friday_time_interval`,
    //`friday_open`, `friday_open_break`, `friday_close`, `friday_close_break`, `friday_available_slot`, `saturday_status`,
    //`saturday_time_interval`, `saturday_open`, `saturday_open_break`, `saturday_close`, `saturday_close_break`,
    //`saturday_available_slot`, `sunday_status`, `sunday_time_interval`, `sunday_open`, `sunday_open_break`, `sunday_close`,
    //`sunday_close_break`, `sunday_available_slot`, `pin_color`, `minimum_gap`, `status_order`, `url_id_path`,
    //`shipping_price`,
    //`zoom_level`, `image_icon`, `tag_ids`
    //`image_id`, `statuses`, `del`, `options`, `name`, `store_id`
    
    
    $query2 = mysql_query("SELECT *, ( 3959 * acos( cos( radians($lat) ) * cos( radians( storepickup_store.store_latitude ) ) * cos( radians( storepickup_store.store_longitude ) - radians($long) ) + sin( radians($lat) ) * sin( radians( storepickup_store.store_latitude ) ) ) ) AS distance FROM storepickup_store 
		     LEFT JOIN storepickup_image
		     ON storepickup_store.store_id = storepickup_image.store_id
		     WHERE storepickup_store.status='1'
		    ORDER BY storepickup_store.store_name ASC") or die(mysql_error());
    
    //Get Location
    $query = mysql_query("SELECT *, ( 3959 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($long) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance FROM location ORDER BY distance ASC", $con);

    while($row = mysql_fetch_array($query2))
    {
	$state = array(
	'id' => $row['state_id'],
	'name' => $row['state']
	);
	//$country = array(
	//    'id' => '',
	//    'name' => $row['countryname']
	//);
	
	$address = array(
	'line_1' => $row['address'],
	'line_2' => $row['address_2'],
	'city' => $row['city'],
	'postcode' => $row['zipcode'],
	'state' => $state,
	'country' => $row['country'],
	'phone' => $row['store_phone']
	);
	//&& $row[$weekDayOpen] != "0" && $row[$weekDayClose] !="0"
	if($row[$weekDayStat] == 2){
	    $workingHours = "Today (".date('l')."): Closed";
	}elseif($row[$weekDayOpen] != "" && $row[$weekDayClose] !=""){
	    $workingHours = "Today (".date('l')."): ".$row[$weekDayOpen]." to ".$row[$weekDayClose];
	}else{
	    $workingHours = "";
	}
	    
	if($row['monday_open'] != "" && $row['monday_close'] !="")
	    $normallWorkingHours = "Working days: ".$row['monday_open']." to ".$row['monday_close'];
	else
	    $normallWorkingHours = "";
	
	
	
	$coordinate = array(
	    'long' => $row['store_longitude'],
	    'lat' => $row['store_latitude']
	);
	$storeImage = "";
	if($row['name'] != "")
	$storeImage = 'http://tcstaging.trendycounty.com/media/storepickup/images/'.$row['name'];
	
	$data['data'][] = array(
	    'id' => $row['store_id'],
	    'name' => $row['store_name'],
	    'image' => $storeImage,
	    'distance' => $row['distance'] * 1.609344,
	    'address' => $address,
	    'working_hour' => $normallWorkingHours,
	    'today_working_hour' => $workingHours,
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