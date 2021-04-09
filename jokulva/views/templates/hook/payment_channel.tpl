<!DOCTYPE html>
<html>
<head></head>

<body>

<script type="text/javascript">
		function checkChannel() {
				var ischeck = ValidateInputs();
				if (ischeck) {
						return true;
				} else {
						alert("Please choose Payment Channel to use!");
						console.log('error');
						return false;
				}
		}
		const queryString = window.location.search;
		const urlParams = new URLSearchParams(queryString);
		const error_message = urlParams.get('error_message')

		if(error_message!=null){
			alert("Failed to Checkout: " + error_message + ", Please contact our support team");
		}

		
		function ValidateInputs() {
				var x = false;
				if(document.formDokuOrder.PAYMENTCHANNEL.value != ''){
					x = true;
				}
				return x;
		}
		function channelVA(paymentChannel){
			console.log(paymentChannel)
		
				document.formDokuOrder.action = '{$urls.base_url}/modules/jokulva/merchanthostedva.php';
				document.formDokuOrder.CUSTOMERID.value = '{$URL_MERCHANTHOSTED}';
				document.formDokuOrder.EXP_TIME.value = '{$EXPIRY_TIME}';
				document.formDokuOrder.PAYMENTCHANNEL.value = paymentChannel;	
		}
		
</script>

<style>
.doku_payment_module {
    display: block;
	background-color: #FBFBFB;
    border: 1px solid #D6D4D4;
    border-radius: 4px;
    line-height: 23px;
    color: #333;
    padding: 10px 0px 20px 20px;
	margin-bottom: 10px;		
}

.doku_payment_module td {
		line-height: 15px;
}

.doku_payment_module_submit {
    border-radius: 10px;
    color: white;
    background-color: #c6122f;
}
</style>

<div class="doku_payment_module">
<form name="formDokuOrder" id="formDokuOrder" action="{$URL}" method="post" enctype="multipart/form-data" >
		<table cellpadding="0" cellspacing="0" border="0" width="400">
			<tr>
				<td><p style="font-size:16px; font-weight:normal; text-align:justify">Choose Virtual Account you wish to pay</p></td>
			</tr>
		</table>
		
		<li style="list-style-type: none;">

		{if $PAYMENT_CHANNELS_BCA}
			<ul><input type="radio" name="PAYMENTCHANNEL" value="BCA" onclick="return channelVA('BCA')"> BCA VA</ul>
		{/if}

		{if $PAYMENT_CHANNELS_MANDIRI}
    	    <ul><input type="radio" name="PAYMENTCHANNEL" value="MANDIRI" onclick="return channelVA('MANDIRI')"> Bank Mandiri VA</ul>
    	{/if}						
		
		{if $PAYMENT_CHANNELS_MANDIRI_SYARIAH}
			<ul><input type="radio" name="PAYMENTCHANNEL" value="MANDIRI_SYARIAH" onclick="return channelVA('MANDIRI_SYARIAH')"> Bank Syariah Indonesia VA</ul>
		{/if}	

		{if $PAYMENT_CHANNELS_PERMATA}
			<ul><input type="radio" name="PAYMENTCHANNEL" value="PERMATA" onclick="return channelVA('PERMATA')"> Bank Permata VA</ul>
		{/if}											

		{if $PAYMENT_CHANNELS_DOKU_VA}
			<ul><input type="radio" name="PAYMENTCHANNEL" value="DOKU_VA" onclick="return channelVA('DOKU_VA')"> Other Banks (VA by DOKU)</ul>
		{/if}

		</li>
		
		<input type="submit" class="btn btn-primary" value="ORDER WITH AN OBLIGATION TO PAY" onclick="return checkChannel();">
		
		<input type=hidden name="REGID"  		   value="{$REGID}">
		<input type=hidden name="DATETIME"  	   value="{$DATETIME}">
    	<input type=hidden name="invoice_number"   value="{$invoice_number}">
		<input type=hidden name="order_id"   	   value="{$order_id}">
    	<input type=hidden name="amount"           value="{$amount}">
    	<input type=hidden name="REQUESTDATETIME"  value="{$REQUESTDATETIME}">
    	<input type=hidden name="CURRENCY"         value="{$CURRENCY}">
    	<input type=hidden name="PURCHASECURRENCY" value="{$PURCHASECURRENCY}">				
    	<input type=hidden name="NAME"             value="{$NAME}">
		<input type=hidden name="EMAIL"            value="{$EMAIL}">		
    	<input type=hidden name="HOMEPHONE"        value="{$HOMEPHONE}">
    	<input type=hidden name="MOBILEPHONE"      value="{$MOBILEPHONE}"> 
    	<input type=hidden name="BASKET"           value="{$BASKET}">				
    	<input type=hidden name="ADDRESS"          value="{$ADDRESS}"> 
		<input type=hidden name="EXP_TIME"         value=""> 
		<input type=hidden name="CITY"             value="{$CITY}"> 
    	<input type=hidden name="STATE"            value="{$STATE}"> 
    	<input type=hidden name="ZIPCODE"          value="{$ZIPCODE}"> 				
    	<input type=hidden name="SHIPPING_COUNTRY" value="{$SHIPPING_COUNTRY}"> 
    	<input type=hidden name="CUSTOMERID" 	   value="{$EMAIL}"> 
		<input type=hidden name="SHIPPING_ADDRESS" value="{$SHIPPING_ADDRESS}"> 
    	<input type=hidden name="SHIPPING_CITY"    value="{$SHIPPING_CITY}">
    	<input type=hidden name="SHIPPING_ZIPCODE" value="{$SHIPPING_ZIPCODE}"> 				

</form>
</div>
</body>
</html>