<br />
<br />
<p>
    {l s='Your order on' mod='jokulva'} <b>{$shop.name}</b> {l s='is WAITING FOR PAYMENT.' mod='jokulva'}
	  <br />
	  {l s='You have chosen'} <b>{$payment_channel}</b> {l s='Payment Channel Method via' mod='jokulva'} <b></b>{l s='DOKU' mod='jokulva'}</b>
		<br />
		{l s='This is your Payment Code : ' mod='jokulva'} <b>{$payment_code}</b> {l s='Please do the payment immediately' mod='jokulva'}
    <br />
    <br />
    <b>{l s='After we receive your payment, we will process your order.' mod='jokulva'}</b>
    <br />
    <br />
    <b>{l s='For any questions or for further information, please contact our' mod='jokulva'} <a href="{$urls.pages.contact}">{l s='customer support' mod='jokulva'}</a>.</b>
    
</p>