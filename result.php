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




if($method == 'paymentrespond'){
	$TypeOfReturn = "ReturnURL";
	$transactionid = @$_REQUEST['order_id'];
	$status = @$_REQUEST['status_code'];
	$currency = @$_REQUEST['currency'];
	$amount = @$_REQUEST['amount'];
	$paydate = @$_REQUEST['paydate'];
	$tranID = @$_REQUEST['payment_trans_id'];
	$appcode = @$_REQUEST['app_code'];
	$skey = @$_REQUEST['skey'];
	$channel = @$_REQUEST['channel'];
	
	$data['status'] = true;
	$data['message'] = '';
	
	if($transactionid < 100000000){
	    $data['status'] = false;
	    $data['message'] = 'The Transaction ID is not valid';
	    $data['data'] = array();
	    $json_data = json_encode($data);
	    $json_data = str_replace('\/','/',$json_data);
	    $json_data = str_replace('null','""',$json_data);
	    
	    echo $json_data;
	    die();
	}
	$order = Mage::getModel('sales/order')->loadByIncrementId( $transactionid );
	$orderId = $order->getId();
	
	if( $status != '00' ) {
            if($status == '22') {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_NEW,
                    'Customer Redirect from MOLPAY - ReturnURL (PENDING)' . "\n<br>Amount: " . $currency . " " . $amount . $etcAmt . "\n<br>PaidDate: " . $paydate,
                    $notified = true );
                $order->save();
		$data['status'] = true;
		$data['message'] = 'Customer Redirect from MOLPAY - ReturnURL (PENDING)' . "\n<br>Amount: " . $currency . " " . $amount . $etcAmt . "\n<br>PaidDate: " . $paydate;
                //$this->_redirect('checkout/onepage/success');
            } else {
                // if($order->canCancel()) {
                    // foreach($order->getAllItems() as $item){
                        // $item->cancel();
                        // $item->save();
                    // }
                // }
                $order->setState(
                    Mage_Sales_Model_Order::STATE_CANCELED,
                    Mage_Sales_Model_Order::STATE_CANCELED,
                    'Customer Redirect from MOLPAY - ReturnURL (FAILED)' . "\n<br>Amount: " . $currency . " " . $amount . $etcAmt . "\n<br>PaidDate: " . $paydate,
                    $notified = true );
                $order->save();
		
		$data['status'] = true;
		$data['message'] ='Customer Redirect from MOLPAY - ReturnURL (FAILED)' . "\n<br>Amount: " . $currency . " " . $amount . $etcAmt . "\n<br>PaidDate: " . $paydate;
               // $this->_redirect('checkout/cart');
            }
           // return;
        }

        if( $status == '00') {
            $etcAmt = '';
            $currency_code = $order->getOrderCurrencyCode();

            $order->getPayment()->setTransactionId( $tranID );

            //if($order,$TypeOfReturn,$currency,$amount,$appcode,$skey,$tranID,$status,$paydate,$etcAmt) {
            //    $order->sendNewOrderEmail();
            //}
	    
	    if(isset($order) && isset($TypeOfReturn) && isset($currency) && isset($amount) && isset($appcode) && isset($skey)
		&& isset($tranID) && isset($status) && isset($paydate) && isset($etcAmt)) {
                $order->sendNewOrderEmail();
            }
            
            $order->save();
            //$this->_redirect('checkout/onepage/success');
            //return;
	    
	    if($transactionid > 100000000){
		completeMyOrder($transactionid);
	    }
	    

        } else {
            $order->setState(
                Mage_Sales_Model_Order::STATUS_FRAUD,
                Mage_Sales_Model_Order::STATUS_FRAUD,
                'Payment Error: Signature key not match' . "\n<br>Amount: " . $currency . " " . $amount . $etcAmt . "\n<br>PaidDate: " . $paydate,
                $notified = true
            );
            $order->save();
            //$this->_redirect('checkout/cart');
            //return;
	    $data['status'] = false;
	    $data['message'] ='Payment Error: Signature key not match' . "\n<br>Amount: " . $currency . " " . $amount . $etcAmt . "\n<br>PaidDate: " . $paydate;
        }
		
	if($channel != ""){
	    $data['data'] = array(
		'order_id' => $transactionid,
		'channel' => $channel,
		'transaction_date' => $paydate,
		'receipt_url' => 'http://tcstaging.trendycounty.com/sales/order/print/order_id/'.$orderId.'/'
	    );
	}else{
	    $data['data'] = array(
		'order_id' => $transactionid,
		'transaction_date' => $paydate,
		'receipt_url' => 'http://tcstaging.trendycounty.com/sales/order/print/order_id/'.$orderId.'/'
	    );
	}
	    
	
	$json_data = json_encode($data);
	$json_data = str_replace('\/','/',$json_data);
	$json_data = str_replace('null','""',$json_data);
	
	echo $json_data;

}

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
