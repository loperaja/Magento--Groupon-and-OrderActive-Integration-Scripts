<?php
//  This file retrieves order information from Magento
//
//
ini_set("display_errors", 1);
error_reporting(E_ALL);

// Set up variables
$magento_web_server = "server";
$magento_webservices_username = "user";
$magento_webservices_password="pass";

// Set Maximum execution time in case you have a ton of orders
set_time_limit(0);

echo  "Establishing New SOAP Client \n \n";

try
{
$client = new SoapClient($magento_web_server. 'api/soap/?wsdl=1');
$session_id =

$client->login($magento_webservices_username,
$magento_webservices_password);
}
catch (SoapFault $fault)
{
echo "fail";
die ("\n\n SOAP Fault:
(fault code: {$fault->faultcode},
fault string: {$fault->faultstring}) \n \n");
}
?>