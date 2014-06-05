<?php
//Script to copy orders from Groupon's CommerceInterface to a standard magento database
//It's a bit tricky because I needed to create a fake order with free delivery. I don't really see the point on doing this, but my client insisted!
//I dindnt create the whole thing, Roberto-butti did it https://gist.github.com/roberto-butti/3509401 I just adapted his great creation to my needs

$config=array();
$config["hostname"] = "yourhost";
$config["login"] = "user";
$config["password"] = "yor password";
$config["customer_as_guest"] = TRUE;
//$config["customer_id"] = 261; //only if you don't want as Guest
 
$proxy = new SoapClient('http://'.$config["hostname"].'/index.php/api/soap/?wsdl=1');
$sessionId = $proxy->login($config["login"], $config["password"]);
echo $sessionId;

//ARRAY WITH ORDERS AND INFO
$skus = array('123456789101' => array('sku' => array('17'), 'delivery'=> '1STR', 'qty' => 1),
			  '123456789102' => array('sku' => array('20'), 'delivery'=> '1STR', 'qty' => 1),
			  '123456789103' => array('sku' => array('18'), 'delivery'=> '1STR', 'qty' => 1),
			  '123456789104' => array('sku' => array('21'), 'delivery'=> '1STR', 'qty' => 1),
			  '123456789105' => array('sku' => array('19'), 'delivery'=> '1STR', 'qty' => 1),			  
			  '123456789106' => array('sku' => array('23'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789107' => array('sku' => array('24'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789108' => array('sku' => array('25'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789109' => array('sku' => array('26'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789110' => array('sku' => array('27'), 'delivery'=> 'H72', 'qty' => 1),			  
			  '123456789111' => array('sku' => array('28'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789112' => array('sku' => array('29'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789113' => array('sku' => array('30'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789114' => array('sku' => array('31'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789115' => array('sku' => array('32'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789116' => array('sku' => array('33'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789117' => array('sku' => array('34'), 'delivery'=> 'H72', 'qty' => 1),
		  	  '123456789118' => array('sku' => array('35'), 'delivery'=> 'H72', 'qty' => 1),
			  'UK140409AMC09' => array('sku' => array('51'), 'delivery'=> 'H72', 'qty' => 1));


error_reporting(E_ALL);
ini_set("display_errors", 1);

$time1 = date('m/d/Y%20H:i', strtotime('-30 minutes'));
$time2 = date('m/d/Y%20H:i');
$response = file_get_contents ( "https://scm.commerceinterface.com/api/v2/get_orders?supplier_id=yoursuplierid&token=yourtoken&start_datetime=".$time1."&end_datetime=".$time2 );
if( $response ) {
	$response_json = json_decode ( $response , $assoc = true);
	if( $response_json['success'] == true ) {
		foreach($response_json['data'] as $data) {
			foreach($data['line_items'] as $line_item) {
				unset($products);
				foreach($skus[$line_item['sku']]['sku'] as $sku) {
					$products[] = array("product_id" => $sku, "quantity" => $skus[$line_item['sku']]['qty']);
					var_dump($products);
				}
				$shoppingCartIncrementId = $proxy->call( $sessionId, 'cart.create',array( 1 ));
				
				$resultCartProductAdd = $proxy->call($sessionId, "cart_product.add", array($shoppingCartIncrementId, $products));
				echo "\nAdding to Cart...\n";
				if ($resultCartProductAdd) {
				echo "Products added to cart. Cart with id:".$shoppingCartIncrementId;
				} else {
				echo "Products not added to cart";
				}
				
				//Will add a coupon code to have "free" shipping
				$couponCode = "GRPFREEBACKEND";
				$resultCartCouponRemove = $proxy->call($sessionId, "cart_coupon.add", array($shoppingCartIncrementId, $couponCode));
				echo "\nAdded Free Delivery \n";
				
				echo "\n";
				$shoppingCartId = $shoppingCartIncrementId;
				if ($config["customer_as_guest"]) {
				$fullname = explode(' ', $data['customer']['name']);
				$customer = array(
				"firstname" => $fullname[0] ?: "Unknown",
				"lastname" => $fullname[1] ?: "Groupon Customer",
				"website_id" => "1",
				"group_id" => "1",
				"store_id" => "1",
				"email" => "cmsmailer@ics-digital.com",
				"mode" => "guest",
				);
				}
				echo "\nSetting Customer...";
				$resultCustomerSet = $proxy->call($sessionId, 'cart_customer.set', array( $shoppingCartId, $customer) );
				if ($resultCustomerSet === TRUE) {
				echo "\nOK Customer is set";
				} else {
				echo "\nOK Customer is NOT set";
				}
				
				// Set customer addresses, for example guest's addresses
				$arrAddresses = array(
				array(
				"mode" => "shipping",
				"firstname" => $fullname[0] ?: "Unknown",
				"lastname" => $fullname[1] ?: "Groupon Customer",
				"company" => "",
				"street" => $data['customer']['address1'].$data['customer']['address2'],
				"city" => $data['customer']['city'],
				"region" => $data['customer']['state'],
				"postcode" => $data['customer']['zip'],
				"country_id" => "GB",
				"telephone" => $data['customer']['phone'] ?: "07428364365",
				"fax" => "",
				"is_default_shipping" => 0,
				"is_default_billing" => 0
				),
				array(
				"mode" => "billing",
 
				"firstname" => $fullname[0] ?: "Unknown",
				"lastname" => $fullname[1] ?: "Groupon Customer",
				"company" => "",
				"street" => $data['customer']['address1'].$data['customer']['address2'],
				"city" => $data['customer']['city'],
				"region" => $data['customer']['state'],
				"postcode" => $data['customer']['zip'],
				"country_id" => "GB",
				"telephone" => $data['customer']['phone'] ?: "07428364365",
				"fax" => "",
				"is_default_shipping" => 0,
				"is_default_billing" => 0
				)
				);
				echo "\nSetting addresses...";
				$resultCustomerAddresses = $proxy->call($sessionId, "cart_customer.addresses", array($shoppingCartId, $arrAddresses));
				if ($resultCustomerAddresses === TRUE) {
				echo "\nOK address is set\n";
				} else {
				echo "\nKO address is not set\n";
				}
				// get list of shipping methods
				$resultShippingMethods = $proxy->call($sessionId, "cart_shipping.list", array($shoppingCartId));
				print_r( $resultShippingMethods );
 
 
				// set shipping method
				$randShippingMethodIndex = rand(0, count($resultShippingMethods)-1 );
				$shippingMethod = $resultShippingMethods[$randShippingMethodIndex]["code"];
				echo "\nShipping method:".$shippingMethod;
				$resultShippingMethod = $proxy->call($sessionId, "cart_shipping.method", array($shoppingCartId, 'flatrate_flatrate'));
				echo "\nI will check total...\n";
				$resultTotalOrder = $proxy->call($sessionId,'cart.totals',array($shoppingCartId));
				print_r($resultTotalOrder);
 
				echo "\nThe products are...\n";
				$resultProductOrder = $proxy->call($sessionId,'cart_product.list',array($shoppingCartId));
				print_r($resultProductOrder);
 
 
				// get list of payment methods
				echo "\nList of payment methods...";
				$resultPaymentMethods = $proxy->call($sessionId, "cart_payment.list", array($shoppingCartId));
				print_r($resultPaymentMethods);
 
 
				// set payment method
				$paymentMethodString= "checkmo";
				echo "\nPayment method $paymentMethodString.";
				$paymentMethod = array(
				"method" => $paymentMethodString
				);
				$resultPaymentMethod = $proxy->call($sessionId, "cart_payment.method", array($shoppingCartId, $paymentMethod));
 
				// get full information about shopping cart
				echo "\nCart info:\n";
				$shoppingCartInfo = $proxy->call($sessionId, "cart.info", array($shoppingCartId));
				print_r( $shoppingCartInfo );
 
				$licenseForOrderCreation = null;
				
				// create order
				echo "\nI will create the order: ";
				$resultOrderCreation = $proxy->call($sessionId,"cart.order",array($shoppingCartId, null, $licenseForOrderCreation));
				echo "\nOrder created with code:".$resultOrderCreation."\n";
				
				

				//Completing Order
				$orderStatus = 'Complete';
				$comment = 'GROUPON order was Completed';
				$sendEmailToCustomer = false;
				
				$complete = $proxy->call($sessionId, 'sales_order.addComment', array('orderIncrementId' => $resultOrderCreation, 'status' => $orderStatus));
				
				
			}
		}
	} else {
//Alarm!

}
}


 

?>
