<?php
require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/jokulva.php');

$jokulva = new JokulVa();

if (!$_POST) {
    header('Location: javascript:history.go(-1)');
    die;
}

$invoiceNumber = $_POST['invoice_number'];
$amount = $_POST['amount'];

# generate CheckSum
$data = array(
    "order" => array(
        "invoice_number" => $invoiceNumber,
        "amount" => $amount
    ),
    "virtual_account_info" => array(
        "expired_time" => $_POST['EXP_TIME'],
        "reusable_status" => false,
        "info1" => '',
        "info2" => '',
        "info3" => '',
    ),
    "customer" => array(
        "name" => $_POST['NAME'],
        "email" => $_POST['EMAIL']
    ),
    "additional_info" => array(
        "integration" => array(
            "name" => "prestashop-plugin",
            "version" => "1.1.0"
        )
    )
);

$config = $jokulva->getServerConfig();
$bodyJson = json_encode($data);
$dataBody = str_replace(array("\r", "\n"), array("\\r", "\\n"), $bodyJson);
$digest = base64_encode(hash("sha256", $dataBody, True));

$requestId = $_POST['REGID'];
$clientId = $config['MALL_ID'];
$requestTimestamp = $_POST['DATETIME'];

$paymentChannel = $_POST['PAYMENTCHANNEL'];
$requestTarget = "";
if ($paymentChannel == "DOKU_VA") {
    $requestTarget = "/doku-virtual-account/v2/payment-code";
} else if ($paymentChannel == "MANDIRI") {
    $requestTarget = "/mandiri-virtual-account/v2/payment-code";
} else if ($paymentChannel == "MANDIRI_SYARIAH") {
    $requestTarget = "/bsm-virtual-account/v2/payment-code";
} else if ($paymentChannel == "BCA") {
    $requestTarget = "/bca-virtual-account/v2/payment-code";
} else if ($paymentChannel == "PERMATA") {
    $requestTarget = "/permata-virtual-account/v2/payment-code";
}

$dataWords = "Client-Id:" . $clientId . "\n" .
    "Request-Id:" . $requestId . "\n" .
    "Request-Timestamp:" . $requestTimestamp . "\n" .
    "Request-Target:" . $requestTarget . "\n" .
    "Digest:" . $digest;


$signature = base64_encode(hash_hmac('SHA256', htmlspecialchars_decode($dataWords), htmlspecialchars_decode($config['SHARED_KEY']), True));

$configarray = parse_ini_file($_POST['CUSTOMERID']);
$URL = $configarray[$paymentChannel];

define('POSTURL', $URL);

$ch = curl_init(POSTURL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJson);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FAILONERROR, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Signature:' . "HMACSHA256=" . $signature,
    'Request-Id:' . $requestId,
    'Client-Id:' . $clientId,
    'Request-Timestamp:' . $requestTimestamp
));

$GETDATARESULT = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error_msg = curl_error($ch);
$myservername = _PS_BASE_URL_ . __PS_BASE_URI__;
$GETDATARESULT = json_decode($GETDATARESULT);

if ($httpcode == 200) {
    $PAYMENTCODE = $GETDATARESULT->virtual_account_info->virtual_account_number;
    $PAYMENTEXP = $GETDATARESULT->virtual_account_info->expired_date;
    $PAYMENTHOW = $GETDATARESULT->virtual_account_info->how_to_pay_page;
    $STATUSCODE = '';

    curl_close($ch);
?>

    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>DOKU Payment Page - Redirect</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script type="text/javascript" src="https://pay.doku.com/merchant_data/ocov2/js/doku.analytics.js"></script>

        <link rel="stylesheet" type="text/css" href="https://pay.doku.com/merchant_data/ocov2/css/default.min.css" />
        <link rel="stylesheet" type="text/css" href="https://pay.doku.com/merchant_data/ocov2/css/style.min.css" />

    </head>

    <body class="tempdefault tempcolor tempone" onload="document.formRedirect.submit()">
        <section class="default-width">
            <div class="head padd-default">
                <div class="left-head fleft">
                </div>

                <div class="clear"></div>
            </div>
            <br />
            <div class="">
                <div class="loading">
                    <div class="spinner">
                        <div class="double-bounce1"></div>
                        <div class="double-bounce2"></div>
                    </div>
                    <div class="color-one">
                        Please wait.<br />
                        Your request is being processed...<br />
                        <br />
                        <span id="TEXT-CONTINUE">Click button below if the page is not change</span>
                    </div>
                </div>

                <form action="<?php echo $myservername; ?>index.php?fc=module&module=jokulva&controller=request&task=redirect" method="POST" id="formRedirect" name="formRedirect">
                    <input type="hidden" name="DATABODY" value="<?php echo $bodyJson; ?>">
                    <input type="hidden" name="AMOUNT" value="<?php echo $amount; ?>">
                    <input type="hidden" name="TRANSIDMERCHANT" value="<?php echo $invoiceNumber; ?>">
                    <input type="hidden" name="STATUSCODE" value="<?php echo $STATUSCODE; ?>">
                    <input type="hidden" name="PAYMENTCODE" value="<?php echo $PAYMENTCODE; ?>">
                    <input type="hidden" name="PAYMENTEXP" value="<?php echo $PAYMENTEXP; ?>">
                    <input type="hidden" name="PAYMENTHOW" value="<?php echo $PAYMENTHOW; ?>">
                    <input type="hidden" name="PAYMENTCHANNEL" value="<?php echo $paymentChannel; ?>">
                </form>
            </div>
        </section>
        <div class="footer">
            <div id="copyright" class="">Copyright DOKU 2020</div>
        </div>
    </body>

    </html>
<?php
} else {
    curl_close($ch);
?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>DOKU Payment Page - Redirect</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script type="text/javascript" src="https://pay.doku.com/merchant_data/ocov2/js/doku.analytics.js"></script>

        <link rel="stylesheet" type="text/css" href="https://pay.doku.com/merchant_data/ocov2/css/default.min.css" />
        <link rel="stylesheet" type="text/css" href="https://pay.doku.com/merchant_data/ocov2/css/style.min.css" />

    </head>

    <body class="tempdefault tempcolor tempone" onload="document.formRedirect.submit()">
        <section class="default-width">
            <div class="head padd-default">
                <div class="left-head fleft">
                </div>

                <div class="clear"></div>
            </div>
            <br />
            <div class="">
                <div class="loading">
                    <div class="spinner">
                        <div class="double-bounce1"></div>
                        <div class="double-bounce2"></div>
                    </div>
                    <div class="color-one">
                        Please wait.<br />
                        Your request is being processed...<br />
                        <br />
                        <span id="TEXT-CONTINUE">Click button below if the page is not change</span>
                    </div>
                </div>

                <form action="<?php echo $myservername; ?>index.php?fc=module&module=jokulva&controller=request&task=redirectFailed" method="POST" id="formRedirect" name="formRedirect">

                </form>

            </div>
        </section>
        <div class="footer">
            <div id="copyright" class="">Copyright DOKU 2020</div>
        </div>
    </body>

    </html>
<?php
}
?>
