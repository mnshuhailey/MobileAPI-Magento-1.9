<?php 
include('lib/token.php');
require_once('../app/Mage.php');
Mage::app();

Mage::init('default');  
Mage::getSingleton('core/session', array('name' => 'frontend')); 

header('Content-Type: application/json; Charset=UTF-8');
$mainURL = 'http://tcstaging.trendycounty.com/';
$mainURLApi = 'http://tcstaging.trendycounty.com/mobileapi/';

$method = $_REQUEST['method'];
//$email = $_REQUEST['email'];

$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
$session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);


function _createInvoice(Mage_Sales_Model_Order $order,$TypeOfReturn,$currency,$amount,$appcode,$skey,$tranID,$status,$paydate,$etcAmt) {
        if( $order->canInvoice() && ($order->hasInvoices() < 1));
            else 
        return false;
        //---------------------------------------------
        // convert order into invoice
        //---------------------------------------------
        // print_r( "INVOCE ".$newOrderStatus );           
        //need to convert from order into invoice
        $invoice = $order->prepareInvoice();
        $invoice->register()->capture();
        Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

        
        $order->setState(
            Mage_Sales_Model_Order::STATE_PROCESSING,
            Mage_Sales_Model_Order::STATE_PROCESSING,
                "Response from MOLPAY - ".$TypeOfReturn." (CAPTURED)"
                . "\n<br>Invoice #".$invoice->getIncrementId().""
                . "\n<br>Amount: ".$currency." ".$amount.$etcAmt
                . "\n<br>AppCode: " .$appcode
                . "\n<br>Skey: " . $skey
                . "\n<br>TransactionID: " . $tranID
                . "\n<br>Status: " . $status
                . "\n<br>PaidDate: " . $paydate
                ,
                true
        );
        return true;               
}


function _createInvoice_edited($orderIncrementId, $TypeOfReturn,$currency,$amount,$appcode,$skey,$tranID,$status,$paydate,$etcAmt) {
    $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
    //$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE);
    $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
    $order->save();
    die("sdasdasD");
    if( $order->canInvoice() && ($order->hasInvoices() < 1));
    else 
    return false;
    //---------------------------------------------
    // convert order into invoice
    //---------------------------------------------
    // print_r( "INVOCE ".$newOrderStatus );           
    //need to convert from order into invoice
    $invoice = $order->prepareInvoice();
    $invoice->register()->capture();
    Mage::getModel('core/resource_transaction')
	    ->addObject($invoice)
	    ->addObject($invoice->getOrder())
	    ->save();

    
    $order->setState(
	Mage_Sales_Model_Order::STATE_PROCESSING,
	Mage_Sales_Model_Order::STATE_PROCESSING,
	    "Response from MOLPAY - ".$TypeOfReturn." (CAPTURED)"
	    . "\n<br>Invoice #".$invoice->getIncrementId().""
	    . "\n<br>Amount: ".$currency." ".$amount.$etcAmt
	    . "\n<br>AppCode: " .$appcode
	    . "\n<br>Skey: " . $skey
	    . "\n<br>TransactionID: " . $tranID
	    . "\n<br>Status: " . $status
	    . "\n<br>PaidDate: " . $paydate
	    ,
	    true
    );
    return true;               
}

if($_REQUEST['mm'] == "yes"){
    
    function create_invoice_mm($orderIncrementId){
	//echo "ID is: $orderIncrementId <br/>";
	//$order = Mage::getModel("sales/order")->load($orderId);
	$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
	
	$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
	
	//$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true)->save();
	$order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
	$order->save();
	
	//$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true);
	//$order->save();
	
	//$order = Mage::getModel("sales/order")->load($order_id)
	try {
	    if($order->canInvoice())
	    { 
		$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
		 
		if ($invoice->getTotalQty()) { 
		 
		    $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
		    $invoice->register();
		    $transaction = Mage::getModel('core/resource_transaction')
		    ->addObject($invoice)
		    ->addObject($invoice->getOrder()); 
		    $transaction->save();
		    Mage::getSingleton('core/session')->addSuccess('Invoice Created Successfully.');
		    //echo "1<br/>";
		}else{
		    Mage::getSingleton('core/session')->addError('Can not create invoice without Product Quantities');
		    //echo "2<br/>";
		}
	     
	    }else{
		Mage::getSingleton('core/session')->addError('Can not create invoice');
		//echo "3<br/>";
	    }
	}catch (Mage_Core_Exception $e) {
	    echo $e->getMessage();
	    //echo "4<br/>";
	}
    }
    function _submitShipment($order){
	if($order->canShip())
	{
	    $itemQty =  $order->getItemsCollection()->count();
	    $ship = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQty);
	    $ship = new Mage_Sales_Model_Order_Shipment_Api();
	    $shipmentId = $ship->create($increment_id);
	}
	
	$shipment_collection = Mage::getResourceModel('sales/order_shipment_collection');
	$shipment_collection->addAttributeToFilter('order_id', $order_id);
	$res = "";
	foreach($shipment_collection as $sc) {
	    $shipment = Mage::getModel('sales/order_shipment');
	    $shipment->load($sc->getId());
	    if($shipment->getId() != '') { 
		$track = Mage::getModel('sales/order_shipment_track')
			 ->setShipment($shipment)
			 ->setData('title', $type)
			 ->setData('number', $code)
			 ->setData('carrier_code', 'custom')
			 ->setData('order_id', $shipment->getData('order_id'))
			 ->save();
	    }else{
		$res .= "Error in tracking,";
	    }
	    if($shipment){
		if(!$shipment->getEmailSent()){
		    $shipment->sendEmail(true);
		    $shipment->setEmailSent(true);
		    $shipment->save();                          
		}
	    }else{
		$res .= "Error in shipping,";
	    }   
	}
	return $res;
    }
    
    function _submitInvoice($order){
	try {
	    if(!$order->canInvoice())
	    {
		Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
	    }
	     
	    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
	     
	    if (!$invoice->getTotalQty()) {
		Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
	    }
	     
	    $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
	    $invoice->register();
	    $transactionSave = Mage::getModel('core/resource_transaction')
	    ->addObject($invoice)
	    ->addObject($invoice->getOrder());
	     
	    $transactionSave->save();

	    $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
	    $order->save();
	}
	catch (Mage_Core_Exception $e) {
	    return $e->getMessage();
	}
    }
    
    function newMethodToCompleteOrder($transactionid){
        $order = Mage::getModel('sales/order')->loadByIncrementId($transactionid);
        try{
            $order->setData('state', "complete");
            $order->setStatus("complete");
            $history = $order->addStatusHistoryComment('Order was set to Complete by our automation tool.', false);
            $history->setIsCustomerNotified(false);
            $order->save();
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
    }
    
    function completeMyOrder($transactionid){
	$order = Mage::getModel('sales/order')->loadByIncrementId($transactionid);
	$invRes = _submitInvoice($order);
	$order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
	$order->save();
	newMethodToCompleteOrder($transactionid);
    }
    
    function createInvoiceMM($orderIncrementId){
	//$order = Mage::getModel("sales/order")->load($order_id)
	$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
	try {
	    if(!$order->canInvoice())
	    {
		Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
		//echo ">>>1 <br/>";
	    }
	     
	    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
	     
	    if (!$invoice->getTotalQty()) {
		Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
		//echo ">>>2 <br/>";
	    }
	     
	    $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
	    $invoice->register();
	    $transactionSave = Mage::getModel('core/resource_transaction')
	    ->addObject($invoice)
	    ->addObject($invoice->getOrder());
	     
	    $transactionSave->save();
	    //echo ">>>3 <br/>";
	    
	    //$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
	
	    //$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true)->save();
	    $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
	    $order->save();
	}
	catch (Mage_Core_Exception $e) {
	    echo $e->getMessage();
	    //echo ">>>4 <br/>";
	}
    }
    
    function mm_mm($orderIncrementId){
	$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
	try {
	    if($order->canInvoice())
	    { 
		$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
		 
		if ($invoice->getTotalQty()) { 
		 
		    $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
		    $invoice->register();
		    $transaction = Mage::getModel('core/resource_transaction')
		    ->addObject($invoice)
		    ->addObject($invoice->getOrder()); 
		    $transaction->save();
		    Mage::getSingleton('core/session')->addSuccess('Invoice Created Successfully.');
		    //echo "1<br/>";
		}else{
		    Mage::getSingleton('core/session')->addError('Can not create invoice without Product Quantities');
		    //echo "2<br/>";
		}
	     
	    }else{
		//Mage::getSingleton('core/session')->addError('Can not create invoice');
		Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
		//echo "3<br/>";
	    }
	}catch (Mage_Core_Exception $e) {
	    echo $e->getMessage();
	    //echo "4<br/>";
	}
    }
    
    
    if($method == 'completeit'){
	$transactionid = @$_REQUEST['order_id'];
	//create_invoice_mm($transactionid);
	if($transactionid > 100000000){
	    //createInvoiceMM($transactionid);
	    completeMyOrder($transactionid);
	 
	    echo ">>>=== My id is $transactionid <br/>";
	    //_createInvoice_edited($orderIncrementId, $TypeOfReturn,$currency,$amount,$appcode,$skey,$tranID,$status,$paydate,$etcAmt) ;
	}
    }

}
