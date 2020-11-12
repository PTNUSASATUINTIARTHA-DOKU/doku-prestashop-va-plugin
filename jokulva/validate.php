<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/jokulva.php');

$jokulva = new JokulVa();
					 
$trx = array();														

$trx['amount']           = $_POST['AMOUNT'];
$trx['invoice_number']  = $_POST['TRANSIDMERCHANT']; 
$trx['process_datetime'] = date("Y-m-d H:i:s");
$trx['process_type']     = 'PAYMENT REQUEST';
$trx['ip_address']       = $jokulva->getipaddress();
$trx['message']          = "Payment Request to DOKU";

$config = $jokulva->getServerConfig();
$order_state = $config['DOKU_AWAITING_PAYMENT'];

# Insert to table jokulva
$jokulva->add_jokulva($trx);								

?>