<<<<<<< Local Changes
<?php


include 'includes/soap_client.php';
include 'includes/database_models.php';


//Escape special chars 
function xmlEscape($string) {
    return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
}

// Retrieve the Orders
$filters = array ('status'  => 'Processing');

// in my case i want the orders in ‘Processing’ status, you may want ‘Shipped’ or some other status. The filter is based on key/value pairs and can use virtually anything in the var_dump output below to filter with (store number, customer, etc.)

echo "Retrieving Orders  ";
$orders = $client->call($session_id,'sales_order.list', array($filters));
//$order_num = count($orders);
$current_date = date('d/m/Y H:i:s');
//XML DOC


foreach($orders as $order ) {
	$warehouse_int = new WarehouseIntegration; //You can skip this line and validation. I was using a database connection file to check if order had already been exported
	if ($warehouse_int->non_existing_order($order['increment_id'])) {
		$output_file = $order['increment_id'].".xml";
		$handle = fopen($output_file, 'w') or die( 'Cannot open file: '.$output_file);
	
		$xml = "<?xml version='1.0'?>
<WebRelayXML>
  <Summary>
    <SiteID>CAG</SiteID>
    <FileCreationDate>".$current_date."</FileCreationDate>
    <FileType>Order</FileType>
    <FileRef>CAG-".$order['increment_id']."</FileRef>
    <Version>WebRelayXML Version 5.94</Version>
  </Summary>
  <OrderHeader>
    <OrderNumber>".$order['increment_id']."</OrderNumber>
    <CampaignCode>WEB</CampaignCode>
    <SourceCode>WEB</SourceCode>
    <MediaID>WEB_WEB</MediaID>
    <LastOrderNumber />
    <PurchaseOrderNumber></PurchaseOrderNumber>
    <LoyaltyCardNumber />
    <OrderNotes />
    <OrderType>0</OrderType>
    <OrderStatusMajor>0</OrderStatusMajor>
    <OrderStatusMinor>0</OrderStatusMinor>
    <ShippingCode>2NDPP</ShippingCode>
    <CurrencyCode>GBP</CurrencyCode>
    <CurrencyRate>0</CurrencyRate>
    <OrderNetValue>".$order['base_total_paid']."</OrderNetValue>
    <OrderTaxValue>0</OrderTaxValue>
    <OrderGrossValue>".$order['base_total_paid']."</OrderGrossValue>
    <OrderValuePaid>0</OrderValuePaid>
    <VoucherValue>0</VoucherValue>
    <DeliveryNetValue>".$order['shipping_incl_tax']."</DeliveryNetValue>
    <DeliveryTaxValue>0</DeliveryTaxValue>
    <DeliveryGrossValue>".$order['shipping_incl_tax']."</DeliveryGrossValue>
    <DeliveryTaxCode>01</DeliveryTaxCode>
    <OrderDiscountValue>0</OrderDiscountValue>
    <DiscountReasonCode />
    <OrderDate>".date('d/m/Y H:i:s', strtotime($order['created_at']))."</OrderDate>
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
  $order_details = $client->call($session_id,'sales_order.info', $order['increment_id']);
  $itemSeq = -1;
	foreach($order_details['items'] as $item){
		$itemSeq++;
		$xml = $xml."<OrderItem>
      <ItemSeq>$itemSeq</ItemSeq>
      <ItemAlias />
      <Sku>".$item['sku']."</Sku>
      <Qty>".$item['qty_invoiced']."</Qty>
      <QtyAllocated>0</QtyAllocated>
      <ItemStatusMajor>0</ItemStatusMajor>
      <ItemStatusMinor>0</ItemStatusMinor>
      <CompositionProfile>0</CompositionProfile>
      <StockStatus>0</StockStatus>
      <ReservationID>0</ReservationID>
      <PaymentStatusMajor>0</PaymentStatusMajor>
      <PaymentStatusMinor>0</PaymentStatusMinor>
      <ItemUnitPrice>".$item['price']."</ItemUnitPrice>
      <ItemNetValue>".$item['base_price']."</ItemNetValue>
      <ItemTaxValue>0</ItemTaxValue>
      <ItemGrossValue>".$item['base_price_incl_tax']."</ItemGrossValue>
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
	$xml = $xml."
		</OrderItems>
		<Payments>
		          <CashPayments>
                         <CashPayment>
                              <CashPaymentID>0</CashPaymentID>
                              <CashPaymentRef>Cash Payment</CashPaymentRef>
                              <CashPaymentValue>".$order['total_paid']."</CashPaymentValue>
                         </CashPayment>      
                   </CashPayments>
                   <VoucherPayments />
                   <OnAccountPayments />
                   <CreditCardPayments />
                  </Payments>
				  <Installments />
				  <Addresses>
				    <Address>
				      <AddressID>0</AddressID>
				      <AddressTypeID>2</AddressTypeID>
				      <CompanyName>".xmlEscape($order_details['billing_address']['company'])."</CompanyName>
				      <Add1>".xmlEscape($order_details['billing_address']['street'])."</Add1>
				      <Add2></Add2>
				      <Add3></Add3>
				      <AddTown>".xmlEscape($order_details['billing_address']['city'])."</AddTown>
				      <AddCounty>".xmlEscape($order_details['billing_address']['region'])."</AddCounty>
				      <AddPostCode>".xmlEscape($order_details['billing_address']['postcode'])."</AddPostCode>
				      <AddCountryCode>".xmlEscape($order_details['billing_address']['country_id'])."</AddCountryCode>
				      <InternalMailrestrict>1</InternalMailrestrict>
				      <ExternalMailrestrict>1</ExternalMailrestrict>
				      <CreationDate>".$current_date."</CreationDate>
				      <Action>Create</Action>
  			     </Address>
			    <Address>
			      <AddressID>1</AddressID>
			      <AddressTypeID>2</AddressTypeID>
			      <CompanyName>".xmlEscape($order_details['shipping_address']['company'])."</CompanyName>
			      <Add1>".xmlEscape($order_details['shipping_address']['street'])."</Add1>
			      <Add2></Add2>
			      <Add3></Add3>
			      <AddTown>".xmlEscape($order_details['shipping_address']['city'])."</AddTown>
			      <AddCounty>".xmlEscape($order_details['shipping_address']['region'])."</AddCounty>
			      <AddPostCode>".xmlEscape($order_details['shipping_address']['postcode'])."</AddPostCode>
			      <AddCountryCode>".xmlEscape($order_details['shipping_address']['country_id'])."</AddCountryCode>
			      <InternalMailrestrict>1</InternalMailrestrict>
			      <ExternalMailrestrict>1</ExternalMailrestrict>
			      <CreationDate>".$current_date."</CreationDate>
			      <Action>Create</Action>
		     	</Address>
				  </Addresses>
					<WebSiteCredentials>
						<WebLogin />     
						<WebPassword />
						<WebPasswordFormat />
						<WebPasswordSalt />
					</WebSiteCredentials> 
				  <Contacts>
			    <Contact>
			      <ContactID>0</ContactID>
			      <Title></Title>
			      <ForeNames>".xmlEscape($order_details['shipping_address']['firstname'])."</ForeNames>
			      <Initials />
			      <Surname>".xmlEscape($order_details['shipping_address']['lastname'])."</Surname>
			      <PersonalTel>".xmlEscape($order_details['shipping_address']['telephone'])."</PersonalTel>
			      <PersonalFax />
			      <PersonalEmail>".xmlEscape($order_details['shipping_address']['email'])."</PersonalEmail>
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
			      <Salutation>Mr</Salutation>     
			    </Contact>
				    <Contact>
				      <ContactID>1</ContactID>
				      <Title></Title>
				      <ForeNames>".xmlEscape($order_details['shipping_address']['firstname'])."</ForeNames>
				      <Initials />
				      <Surname>".xmlEscape($order_details['shipping_address']['lastname'])."</Surname>
				      <PersonalTel>".xmlEscape($order_details['shipping_address']['telephone'])."</PersonalTel>
				      <PersonalFax />
				      <PersonalEmail>".xmlEscape($order_details['shipping_address']['email'])."</PersonalEmail>
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
				      <Salutation>Mr</Salutation>     
				    </Contact>
				  </Contacts>
				  <CustomSection />
				  <Messages />
				  <Promotions />      
				</WebRelayXML>";
				
	fwrite($handle, $xml);
	$warehouse_int->insert($order['increment_id']);
	}
}


?>

=======
>>>>>>> External Changes
