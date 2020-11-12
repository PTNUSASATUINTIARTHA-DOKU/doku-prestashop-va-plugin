<?php
require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/jokulva.php');

if (!$_POST) {
    header('Location: javascript:history.go(-1)');
    die;
}

$URL;
$PAYMENTCHANNEL = $_POST['PAYMENTCHANNEL'];
$AMOUNT = $_POST['AMOUNT'];
$MALL_ID = $_POST['MALLID'];
$EMAIL = $_POST['EMAIL'];
$NAME = $_POST['NAME'];
$EXPIRED_TIME = $_POST['EXP_TIME'];
$TRANSIDMERCHANT = $_POST['TRANSIDMERCHANT']; //invoice
$WORDS = $_POST['WORDS'];
$configarray = parse_ini_file($_POST['CUSTOMERID']);
$URL = $configarray[$PAYMENTCHANNEL];
$REGID = rand(1,100000);
$TARGETPATH = "/bsm-virtual-account/v2/payment-code";
$DATETIME = date("Y-m-d H:i:s");
$DATETIME = date(DATE_ISO8601, strtotime($DATETIME));

$data = array(
    "order" => array(
        "invoice_number" => $TRANSIDMERCHANT,
        "amount" => $AMOUNT
    ),
    "virtual_account_info" => array(
        "expired_time" => $EXPIRED_TIME,
        "reusable_status" => false,
        "info1" => '',
        "info2" => '',
        "info3" => '',
    ),
    "customer" => array(
        "name" => $NAME,
        "email" => $EMAIL
    ),
    "additional_info" => array (
        "integration" => array (
            "name" => "prestashop-plugin",
            "version" => "1.0.0"
        )
    )
);

define('POSTURL', $URL);

$ch = curl_init(POSTURL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 18);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FAILONERROR, true);

$headers = str_replace(array("\r","\n"),array("\\r","\\n"),json_encode($data));
$BODY = base64_encode(hash("sha256", $headers, True));
$DATETIMEFINAL = substr($DATETIME,0,19)."Z";
$dataWords ="Client-Id:".$MALL_ID ."\n". 
            "Request-Id:".$REGID . "\n".
            "Request-Timestamp:".$DATETIMEFINAL ."\n". 
            "Request-Target:".$TARGETPATH ."\n".
            "Digest:".htmlspecialchars_decode($BODY); 

$SHARED_KEY = "SK-hCJ42G28TA0MKG9LE2E_1";
$SIGNATURE = base64_encode(hash_hmac('SHA256',htmlspecialchars_decode($dataWords) ,htmlspecialchars_decode($SHARED_KEY), True));

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Signature:'."HMACSHA256=".$SIGNATURE,
    'Request-Id:'.$REGID,
    'Client-Id:'.$MALL_ID,
    'Request-Timestamp:'.$DATETIMEFINAL,
    'Request-Target:'.$TARGETPATH,

));
$GETDATARESULT = curl_exec($ch);

$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$error_msg = curl_error($ch);
$myservername = _PS_BASE_URL_ . __PS_BASE_URI__;

$GETDATARESULT = json_decode($GETDATARESULT);

if (!curl_errno($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
    if ($PAYMENTCHANNEL == "MANDIRI") { //VA Mandiri
        $PAYMENTCODE = $GETDATARESULT->virtual_account_info->virtual_account_number;
        $PAYMENTEXP = $GETDATARESULT->virtual_account_info->expired_date;
        $PAYMENTHOW = $GETDATARESULT->virtual_account_info->how_to_pay_page;
    } else if ($PAYMENTCHANNEL == "MANDIRI_SYARIAH") { //VA Mandiri Syariah
        $PAYMENTCODE = $GETDATARESULT->virtual_account_info->virtual_account_number;
        $PAYMENTEXP = $GETDATARESULT->virtual_account_info->expired_date;
        $PAYMENTHOW = $GETDATARESULT->virtual_account_info->how_to_pay_page;
    }
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
                <input type="hidden" name="WORDS" value="<?php echo $dataWords; ?>">
                <input type="hidden" name="BODY" value="<?php echo $BODY; ?>">
                <input type="hidden" name="SIGNATURE" value="<?php echo $SIGNATURE; ?>">
                <input type="hidden" name="AMOUNT" value="<?php echo $AMOUNT; ?>">
                <input type="hidden" name="TRANSIDMERCHANT" value="<?php echo $TRANSIDMERCHANT; ?>">
                <input type="hidden" name="STATUSCODE" value="<?php echo $STATUSCODE; ?>">
                <input type="hidden" name="PAYMENTCODE" value="<?php echo $PAYMENTCODE; ?>">
                <input type="hidden" name="PAYMENTEXP" value="<?php echo $PAYMENTEXP; ?>">
                <input type="hidden" name="PAYMENTHOW" value="<?php echo $PAYMENTHOW; ?>">
                <input type="hidden" name="PAYMENTCHANNEL" value="<?php echo $PAYMENTCHANNEL; ?>">
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
    error_log($error_msg);
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