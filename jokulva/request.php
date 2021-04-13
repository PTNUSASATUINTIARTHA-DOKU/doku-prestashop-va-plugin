<?php

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/jokulva.php');

$jokulva = new JokulVa();

$task = $_GET['task'];

$json_data_input = json_decode(file_get_contents('php://input'), true);

switch ($task) {
	case "notify":
		if (empty($json_data_input)) {
			http_response_code(404);
			die;
		} else {
			$trx = array();
			$trx['invoice_number']           = $json_data_input['order']['invoice_number'];
			$trx['result_msg']                = null;
			$trx['process_type']             = 'NOTIFY';

			$config = $jokulva->getServerConfig();

			$order_id = $jokulva->get_order_id_jokul($trx['invoice_number'], $json_data_input['virtual_account_info']['virtual_account_number']);

			if (!$order_id) {
				$order_state = $config['DOKU_AWAITING_PAYMENT'];
				$trx['amount'] = $json_data_input["order"]["amount"];
			}

			$order = new Order($order_id);

			$headers = getallheaders();
			$signature = generateSignature($headers, $jokulva->getKey());
			if ($headers['Signature'] == $signature) {
				$trx['raw_post_data']         = file_get_contents('php://input');
				$trx['ip_address']            = $jokulva->getipaddress();
				$trx['amount']                = $json_data_input['order']['amount'];
				$trx['invoice_number']        = $json_data_input['order']['invoice_number'];
				$trx['order_id']       		  = $order_id;
				$trx['payment_channel']       = $json_data_input['channel']['id'];
				$trx['payment_code']          = $json_data_input['virtual_account_info']['virtual_account_number'];
				$trx['doku_payment_datetime'] = $json_data_input['virtual_account_payment']['date'];
				$trx['process_datetime']      = date("Y-m-d H:i:s");

				$result = $jokulva->checkTrxNotify($trx);

				if ($result < 1) {
					http_response_code(404);
				} else {
					$trx['message'] = "Notify process message come from DOKU. Success : completed";
					$status         = "completed";
					$status_no      = $config['DOKU_PAYMENT_RECEIVED'];
					$jokulva->emptybag();

					$jokulva->set_order_status($order_id, $status_no);

					$checkStatusTrx = $jokulva->checkStatusTrx($trx);
					if ($checkStatusTrx < 1) {
						$jokulva->add_jokulva($trx);
					}
				}
			} else {
				http_response_code(400);
			}
		}

		break;

	default:
		echo "Stop : Access Not Valid";
		die;
		break;
}

function generateSignature($headers, $secret)
{
	$digest = base64_encode(hash('sha256', file_get_contents('php://input'), true));
	$rawSignature = "Client-Id:" . $headers['Client-Id'] . "\n"
		. "Request-Id:" . $headers['Request-Id'] . "\n"
		. "Request-Timestamp:" . $headers['Request-Timestamp'] . "\n"
		. "Request-Target:" . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) . "\n"
		. "Digest:" . $digest;

	$signature = base64_encode(hash_hmac('sha256', $rawSignature, $secret, true));
	return 'HMACSHA256=' . $signature;
}