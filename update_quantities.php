<?php
//This file will update all the stock of caffecagliari's shop using the information provided by the warehouse people using CSV files
//CSV files contain: SKU - QTY
//The information will be read will be send to Magento (Caffecagliari.co.uk)

//Connection with Magento API 
include 'includes/soap_client.php';

//Server path for CSV Files 
$path = "/your_path/import_stock";

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
$quantities[] = explode(',', $k);
}

$i=0;
foreach($quantities as $qty){
	if ($i != 0){
		$productId = $qty[0];
		$stockItemData = array(
			'qty' => $qty[1]
		);
	//After retirieving quantities and SKU - Call API to update products.
		$update_result = $client->call($session_id,'product_stock.update', array($productId, $stockItemData));
		echo "{$qty[0]}: {$qty[1]}\n";
	}
	$i++;
}
//Move files to archive after finishing
$archive_path = "{$path}/Archive/{$latest_filename}";
rename($fullpath, $archive_path);

?>
