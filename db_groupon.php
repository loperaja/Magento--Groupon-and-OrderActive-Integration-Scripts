<?php
include 'includes/database_models.php';

$skus = array('123456789101' => array('sku' => array('MP10635'), 'delivery'=> 'Royal Mail Signed For', 'qty' => 1),
			  '123456789102' => array('sku' => array('MP10634'), 'delivery'=> 'Royal Mail Signed For', 'qty' => 1),
			  '123456789103' => array('sku' => array('MP10633'), 'delivery'=> 'Royal Mail Signed For', 'qty' => 1),
			  '123456789104' => array('sku' => array('MP10636'), 'delivery'=> 'Royal Mail Signed For', 'qty' => 1),
			  '123456789105' => array('sku' => array('MP10632'), 'delivery'=> 'Royal Mail Signed For', 'qty' => 1),			  
			  '123456789106' => array('sku' => array('MP10635'), 'delivery'=> 'Yodel', 'qty' => 2),
			  '123456789107' => array('sku' => array('MP10634'), 'delivery'=> 'Yodel', 'qty' => 2),
			  '123456789108' => array('sku' => array('MP10633'), 'delivery'=> 'Yodel', 'qty' => 2),
			  '123456789109' => array('sku' => array('MP10636'), 'delivery'=> 'Yodel', 'qty' => 2),
			  '123456789110' => array('sku' => array('MP10632'), 'delivery'=> 'Yodel', 'qty' => 2),			  
			  '123456789111' => array('sku' => array('MP10635','MP10634'), 'delivery'=> 'Yodel', 'qty' => 1),
			  '123456789112' => array('sku' => array('MP10635','MP10633'), 'delivery'=> 'Yodel', 'qty' => 1),
			  '123456789113' => array('sku' => array('MP10635','MP10636'), 'delivery'=> 'Yodel', 'qty' => 1),
			  '123456789114' => array('sku' => array('MP10635','MP10632'), 'delivery'=> 'Yodel', 'qty' => 1),
			  '123456789115' => array('sku' => array('MP10634','MP10633'), 'delivery'=> 'Yodel', 'qty' => 1),
			  '123456789116' => array('sku' => array('MP10634','MP10636'), 'delivery'=> 'Yodel', 'qty' => 1),
			  '123456789117' => array('sku' => array('MP10634','MP10632'), 'delivery'=> 'Yodel', 'qty' => 1),			  
			  '123456789118' => array('sku' => array('MP10635','MP10634','MP10633','MP10636','MP10632'), 'delivery'=> 'Yodel', 'qty' => '1'));


$time1 = date('m/d/Y%20H:i', strtotime('-24 hours'));
$time2 = date('m/d/Y%20H:i');
$response = file_get_contents ( "https://scm.commerceinterface.com/api/v2/get_orders?supplier_id=yoursuplierid&token=yourtoken&start_datetime=".$time1."&end_datetime=".$time2 );
if( $response ) {
	$response_json = json_decode ( $response , $assoc = true);
	if( $response_json['success'] == true ) {
		foreach($response_json['data'] as $data) {
			foreach($data['line_items'] as $line_item) {
				$order_details[] = array("order_no" => $data['orderid'], "lineitem" => $line_item['ci_lineitemid'], "carrier" => $skus[$line_item['sku']]['delivery']);
			}
		}
		foreach($order_details as $order) {
			$warehouse_int = new WarehouseIntegration;
			$warehouse_int->insert_groupon($order);
		} 
	}
}

?>