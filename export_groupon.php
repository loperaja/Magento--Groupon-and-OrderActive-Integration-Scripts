<?php
include 'includes/database_models.php';

//Script to export arders from groupon to OrderActive XML so the warehouse can process it

//EXAMPLE ARRAY WITH ORDERS AND INFO 
$skus = array('123456789101' => array('sku' => array('MP10635'), 'delivery'=> '1STR', 'qty' => 1),
			  '123456789102' => array('sku' => array('MP10634'), 'delivery'=> '1STR', 'qty' => 1),
			  '123456789103' => array('sku' => array('MP10633'), 'delivery'=> '1STR', 'qty' => 1),
			  '123456789104' => array('sku' => array('MP10636'), 'delivery'=> '1STR', 'qty' => 1),
			  '123456789105' => array('sku' => array('MP10632'), 'delivery'=> '1STR', 'qty' => 1),			  
			  '123456789106' => array('sku' => array('MP10635'), 'delivery'=> 'H72', 'qty' => 2),
			  '123456789107' => array('sku' => array('MP10634'), 'delivery'=> 'H72', 'qty' => 2),
			  '123456789108' => array('sku' => array('MP10633'), 'delivery'=> 'H72', 'qty' => 2),
			  '123456789109' => array('sku' => array('MP10636'), 'delivery'=> 'H72', 'qty' => 2),
			  '123456789110' => array('sku' => array('MP10632'), 'delivery'=> 'H72', 'qty' => 2),			  
			  '123456789111' => array('sku' => array('MP10635','MP10634'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789112' => array('sku' => array('MP10635','MP10633'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789113' => array('sku' => array('MP10635','MP10636'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789114' => array('sku' => array('MP10635','MP10632'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789115' => array('sku' => array('MP10634','MP10633'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789116' => array('sku' => array('MP10634','MP10636'), 'delivery'=> 'H72', 'qty' => 1),
			  '123456789117' => array('sku' => array('MP10634','MP10632'), 'delivery'=> 'H72', 'qty' => 1),
			  'UK140409AMC09' => array('sku' => array('MP10636','MP10632'), 'delivery'=> 'H72', 'qty' => 1),			  
			  '123456789118' => array('sku' => array('MP10635','MP10634','MP10633','MP10636','MP10632'), 'delivery'=> 'H72', 'qty' => '1'));

$w=1;
//Required variables 
$supplier_id = "yourid";
$token = "yourtoken";
$orders_url = "https://scm.commerceinterface.com/api/v2/get_orders?supplier_id={$supplier_id}&token={$token}";
$current_date = date('d/m/Y H:i:s');
//Escape special chars
function xmlEscape($string) {
    return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
}



$response = file_get_contents ($orders_url);
if( $response ) {
	$response_json = json_decode ( $response , $assoc = true);
	if( $response_json['success'] == true) {
		//var_dump($response_json);
		foreach($response_json["data"] as $order) {
			$warehouse_int = new WarehouseIntegration;
			$output_file ="/var/www/vhosts/caffecagliari.co.uk/httpdocs/warehouse_int/exported_orders/CAG-GRP".$order['orderid'].".xml";
			//$output_file ="CAG-GRP".$order['orderid'].".xml";
			$shipping_method = $skus[$order['line_items'][0]['sku']]['delivery'];
			$handle = fopen($output_file, 'w') or die( 'Cannot open file: '.$output_file);
			//CHANGING DELIVERY METHOD IF POSTCODE IS BT
			if( strtoupper(substr($order['customer']['zip'], 0, 1)) == "BT") {
				$shipping_method == "H72NI"; //Delivery code changed ti H72NI if it is a northern Ireland Postcode
			}		
			
		$xml = "<?xml version='1.0'?>
<WebRelayXML>
  <Summary>
    <SiteID>CAG</SiteID>
    <FileCreationDate>".$current_date."</FileCreationDate>
    <FileType>Order</FileType>
    <FileRef>CAG-GRP".$order['orderid']."</FileRef>
    <Version>WebRelayXML Version 5.94</Version>
  </Summary>
  <OrderHeader>
    <OrderNumber>".$order['orderid']."</OrderNumber>
    <CampaignCode>GRP</CampaignCode>
    <SourceCode>GRP</SourceCode>
    <MediaID>GRP_GRP</MediaID>
    <LastOrderNumber />
    <PurchaseOrderNumber></PurchaseOrderNumber>
    <LoyaltyCardNumber />
    <OrderNotes />
    <OrderType>0</OrderType>
    <OrderStatusMajor>0</OrderStatusMajor>
    <OrderStatusMinor>0</OrderStatusMinor>
    <ShippingCode>".$shipping_method."</ShippingCode>
    <CurrencyCode>GBP</CurrencyCode>
    <CurrencyRate>0</CurrencyRate>
    <OrderNetValue>".$order['amount']['total']."</OrderNetValue>
    <OrderTaxValue>0</OrderTaxValue>
    <OrderGrossValue>".$order['amount']['total']."</OrderGrossValue>
    <OrderValuePaid>0</OrderValuePaid>
    <VoucherValue>0</VoucherValue>
    <DeliveryNetValue>".$order['amount']['shipping']."</DeliveryNetValue>
    <DeliveryTaxValue>0</DeliveryTaxValue>
    <DeliveryGrossValue>".$order['amount']['shipping']."</DeliveryGrossValue>
    <DeliveryTaxCode>01</DeliveryTaxCode>
    <OrderDiscountValue>0</OrderDiscountValue>
    <DiscountReasonCode />
    <OrderDate>".date('d/m/Y H:i:s', strtotime($order['date']))."</OrderDate>
		<DueDate />
		<DeliverByDate />
    <ContactID>1</ContactID>
    <CardID>0</CardID>
    <BillToID>0</BillToID>
    <ShipToID>1</ShipToID>
    <OrderedByID>0</OrderedByID>
    <MailToID>0</MailToID>
    <GiftMessageID>-1</GiftMessageID>
    <DeliveryInstructionID>-1</DeliveryInstructionID>
    <ReferalRef />
    <AffiliateID />
    <MessageID>-1</MessageID>
    <VatReceiptRequired>0</VatReceiptRequired>
  </OrderHeader>
  <OrderItems>";
		$itemSeq = -1;
				foreach($order['line_items'] as $item){
					$delivery = $skus[$item['sku']]['delivery'] == '1STR' ? 'Royal Mail Signed For' : 'Yodel';
					
					$order_details = array("order_no" => $order['orderid'], "lineitem" => $item['ci_lineitemid'], "carrier" => $delivery);
					$warehouse_int->insert_groupon($order_details);
					$groupon_sku = $item['sku'];
				foreach($skus[$groupon_sku]['sku'] as $sku){
				$itemSeq++;
				$unit_price = ($item['unit_price']/$skus[$groupon_sku]['qty']);
				$xml = $xml."<OrderItem>
    <ItemSeq>{$itemSeq}</ItemSeq>
    <ItemAlias />
    <Sku>".$sku."</Sku>
    <Qty>".$skus[$groupon_sku]['qty']."</Qty>
    <QtyAllocated>0</QtyAllocated>
    <ItemStatusMajor>0</ItemStatusMajor>
    <ItemStatusMinor>0</ItemStatusMinor>
    <CompositionProfile>0</CompositionProfile>
    <StockStatus>0</StockStatus>
    <ReservationID>0</ReservationID>
    <PaymentStatusMajor>0</PaymentStatusMajor>
    <PaymentStatusMinor>0</PaymentStatusMinor>
    <ItemUnitPrice>".$unit_price."</ItemUnitPrice>
    <ItemNetValue>".$unit_price."</ItemNetValue>
    <ItemTaxValue>0</ItemTaxValue>
    <ItemGrossValue>".$unit_price."</ItemGrossValue>
    <ItemTaxCode>01</ItemTaxCode>
    <ItemPostageValue>0</ItemPostageValue>
    <ItemPostageTaxValue>0</ItemPostageTaxValue>
    <ItemPostageGrossValue>0</ItemPostageGrossValue>
    <ItemPostageTaxCode />
    <ItemDiscountValue/>
    <ItemDiscountCode>0</ItemDiscountCode>
    <ItemDiscountReasonCode />
    <HasPersonalisation>0</HasPersonalisation>
    <SKUStatus>0</SKUStatus>
    <ParentItem>-1</ParentItem>
    <InstallmentID>-1</InstallmentID>
    <ShipToID>-1</ShipToID>
    <GiftMessageID>-1</GiftMessageID>
    <ContactID>-1</ContactID>
    <ItemPersonalisationID>-1</ItemPersonalisationID>
    <MessageID>-1</MessageID>
  </OrderItem>";
			}
		}
			$xml = $xml."
				</OrderItems>
				<Payments>
				          <CashPayments>
		                         <CashPayment>
		                              <CashPaymentID>0</CashPaymentID>
		                              <CashPaymentRef>Cash Payment</CashPaymentRef>
		                              <CashPaymentValue>".$order['amount']['total']."</CashPaymentValue>
		                         </CashPayment>      
		                   </CashPayments>
		                   <VoucherPayments />
		                   <OnAccountPayments />
		                   <CreditCardPayments />
		                  </Payments>
						  <Installments />
						  <Addresses>";
						  for($address_id = 0; $address_id <= 1; $address_id++){
							  $xml = $xml."
							<Address>
							<AddressID>{$address_id}</AddressID>
						      <AddressTypeID>2</AddressTypeID>
						      <CompanyName></CompanyName>
						      <Add1>".xmlEscape($order['customer']['address1'])."</Add1>
						      <Add2>".xmlEscape($order['customer']['address2'])."</Add2>
						      <Add3></Add3>
						      <AddTown>".xmlEscape($order['customer']['city'])."</AddTown>
						      <AddCounty>".xmlEscape($order['customer']['state'])."</AddCounty>
						      <AddPostCode>".xmlEscape($order['customer']['zip'])."</AddPostCode>
						      <AddCountryCode>GB</AddCountryCode>
						      <InternalMailrestrict>1</InternalMailrestrict>
						      <ExternalMailrestrict>1</ExternalMailrestrict>
						      <CreationDate>".$current_date."</CreationDate>
						      <Action>Create</Action>
		  			     </Address>";
						}
					    $xml = $xml."</Addresses>
							<WebSiteCredentials>
								<WebLogin />     
								<WebPassword />
								<WebPasswordFormat />
								<WebPasswordSalt />
							</WebSiteCredentials> 
						  <Contacts>";
						  
						  for($contact_id = 0; $contact_id <= 1; $contact_id++) {
							  $xml = $xml."<Contact>
					      <ContactID>{$contact_id}</ContactID>
					      <Title></Title>
					      <ForeNames>".xmlEscape($order['customer']['name'])."</ForeNames>
					      <Initials />
					      <Surname></Surname>
					      <PersonalTel>".xmlEscape($order['customer']['phone'])."</PersonalTel>
					      <PersonalFax />
					      <PersonalEmail></PersonalEmail>
					      <MobilePhone />
					      <WorkTel />
					      <WorkFax />
					      <WorkEmail />
					      <JobTitle />
					      <InternalMailrestrict>1</InternalMailrestrict>
					      <ExternalMailrestrict>1</ExternalMailrestrict>
					      <CatalogueRestrict>0</CatalogueRestrict>
					      <CreationDate>".$current_date."</CreationDate>
					      <LastOrderDate />
					      <LastMailingDate />
					      <CreditRating>0</CreditRating>
					      <Priority>0</Priority>
					      <LoyaltyCardMember>0</LoyaltyCardMember>
					      <LoyaltyCardNumber />
					      <DefaultCurrency />
					      <ContactNotes />
					      <DefaultTaxCode />
					      <DefaultDiscountRate>0</DefaultDiscountRate>
					      <ContactStatus>1</ContactStatus>
					      <CategoryCode />
					      <CategoryType />
					      <DefaultBillToID>0</DefaultBillToID>
					      <DefaultShipToID>0</DefaultShipToID>
					      <DefaultMailToID>0</DefaultMailToID>
					      <EMailRestrict>1</EMailRestrict>
					      <Action>Create</Action>
					      <CustomerURN />
					      <VATNumber />
					      <CustomerAlias />
					      <EmailFormat>0</EmailFormat>
					      <DOB />
					      <Salutation></Salutation>     
					    </Contact>"; 
					}
						    $xml = $xml."</Contacts>
						  <CustomSection />
						  <Messages />
						  <Promotions />      
						</WebRelayXML>";
				
			fwrite($handle, $xml);
			
		   foreach($order['line_items'] as $item){
			// requires PHP cURL http://no.php.net/curl
		   $datatopost = array (
		   "supplier_id" => $supplier_id,
		   "token" => $token,
		   "ci_lineitem_ids" => json_encode ( array ($item['ci_lineitemid']) ),
		   );
		   $ch = curl_init ("https://scm.commerceinterface.com/api/v2/mark_exported");
		   curl_setopt ($ch, CURLOPT_POST, true);
		   curl_setopt ($ch, CURLOPT_POSTFIELDS, $datatopost);
		   curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		   $response = curl_exec ($ch);
	   }
		}

} else {
//Alarm!
}
}


?>
