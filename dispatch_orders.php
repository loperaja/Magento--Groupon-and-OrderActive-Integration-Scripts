<?php
//This file will generate shippings based on the information sent by people in the warehouse in CSV files
//CSV files contain: ORDER NUMBER - SKU - QTY - DATE
//The information will be read will be sent to Magento

//Connection with Magento API 
include 'includes/soap_client.php';

//Server path for CSV Files 
$path = "/var/www/yourcsvpath";

//Routine to get the most recent file added to the folder with stock information
//Just in case there are many files
$latest_ctime = 0;
$latest_filename = '';    
$d = dir($path);
while (false !== ($entry = $d->read())) {
  $filepath = "{$path}/{$entry}";
  // could do also other checks than just checking whether the entry is a file
  if (is_file($filepath) && filectime($filepath) > $latest_ctime) {
    $latest_ctime = filectime($filepath);
    $latest_filename = $entry;
  }
}

// now $latest_filename contains the filename of the file that changed last
$fullpath = "{$path}/{$latest_filename}";

//Redad CSVs and create an array
$file = file($fullpath);
foreach($file as $k){
$despatched_orders[] = explode(',', $k);
}

$i=0;
$prev_order_number = 0;
foreach($despatched_orders as $despatched_order){
	if ($i != 0){
		$order_number = trim($despatched_order[0],'"');
		if($prev_order_number != $order_number){
			$order_details = $client->call($session_id,'sales_order.info', $order_number);
			//If order hasnt been despatched and has been processed
			if ($order_details['status'] != 'complete' && $order_details['status'] == 'processing') {
				$result = $client->call($session_id, 'order_shipment.create',array($order_number, array(), 'Shipment Created', true, false));
				//echo $order_details['increment_id'];
			}
				
		}
		$prev_order_number = $order_number;
	}
	$i++;
}
//Move files to archive after finishing
$archive_path = "{$path}/Archive/{$latest_filename}";
rename($fullpath, $archive_path);
?>
