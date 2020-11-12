
<div class="col-md-12">
    <h2>Your Transaction is Waiting for Your Payment</h2>
    <p>
       Please transfer your payment to this payment code / VA number:
    </p>

    <div class="alert alert-warning">
        <h3>{$payment_code}</h3>
        <h5>Payment Amount: {$currency.sign}{$payment_amount}</h5>
    </div>

    <p>
        Payment Channel: {$payment_channel}<br>
        Make your payment before: {$payment_exp}
    </p>

    <p>
        <a href={$payment_how}>Click here to see payment instructions</a>
    </p>
</div>