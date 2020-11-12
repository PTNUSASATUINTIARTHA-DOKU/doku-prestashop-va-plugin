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

			$order_id = $jokulva->get_order_id($trx['invoice_number']);

			if (!$order_id) {
				$order_state = $config['DOKU_AWAITING_PAYMENT'];
				$trx['amount'] = $json_data_input["order"]["amount"];
				// $jokulva->validateOrder($trx['invoice_number'], $order_state, $trx['amount'], $jokulva->displayName, $trx['invoice_number']);
				$order_id = $jokulva->get_order_id($trx['invoice_number']);
			}

			$order = new Order($order_id);

			$headers = getallheaders();
			$signature = generateSignature($headers, $jokulva->getKey());
			if ($headers['Signature'] == $signature) {
				$trx['raw_post_data']         = file_get_contents('php://input');
				$trx['ip_address']            = $jokulva->getipaddress();
				$trx['amount']                = $json_data_input['order']['amount'];
				$trx['payment_channel']       = $json_data_input['channel']['id'];
				$trx['payment_code']          = $json_data_input['virtual_account_info']['virtual_account_number'];
				$trx['doku_payment_datetime'] = $json_data_input['virtual_account_payment']['date'];
				$trx['process_datetime']      = date("Y-m-d H:i:s");
				// Check Transaction Exist
				$result = $jokulva->checkTrx($trx);

				if ($result < 1) {
					error_log('VA Number : ' . $json_data_input['virtual_account_info']['virtual_account_number'] . ' Transaction Not Found');
					http_response_code(404);
					returnResponse($json_data_input);
				} else {
					$order_id = $jokulva->get_order_id($trx['invoice_number']);
					$trx['message'] = "Notify process message come from DOKU. Success : completed";
					$status         = "completed";
					$status_no      = $config['DOKU_PAYMENT_RECEIVED'];
					$jokulva->emptybag();

					$jokulva->set_order_status($order_id, $status_no);

					# Insert transaction notify to table jokulva
					$jokulva->add_jokulva($trx);
					returnResponse($json_data_input);
				}
			} else {
				http_response_code(400);
				returnResponse($json_data_input);
				error_log('VA Number ' . $json_data_input['virtual_account_info']['virtual_account_number'] . ' CheckSum Doesn Match : ' . $words);

				$trx['message']       = "WORDS not match";
				$trx['raw_post_data'] = "WORDS component: " . $words_components;

				$jokulva->add_jokulva($trx);
				die;
			}
		}

		break;

	default:
		echo "Stop : Access Not Valid";
		die;
		break;
}

function returnResponse($json_data_input)
{
	header("Access-Control-Allow-Origin: *");
	header("Content-Type: application/json; charset=UTF-8");

	$json_data_output = array(
		"order" => array(
			"invoice_number" => $json_data_input['order']['invoice_number'],
			"amount" => $json_data_input['order']['amount']
		),
		"virtual_account_info" => array(
			"virtual_account_number" => $json_data_input['virtual_account_info']['virtual_account_number']
		)
	);

	echo json_encode($json_data_output);
	die;
}

function generateSignature($headers, $secret)
{
	$digest = base64_encode(hash('sha256', file_get_contents('php://input'), true));
	$rawSignature = "Client-Id:" . $headers['Client-Id'] . "\n"
		. "Request-Id:" . $headers['Request-Id'] . "\n"
		. "Request-Timestamp:" . $headers['Request-Timestamp'] . "\n"
		. "Request-Target:" . $headers['Request-Target'] . "\n"
		. "Digest:" . $digest;

	$signature = base64_encode(hash_hmac('sha256', $rawSignature, $secret, true));
	return 'HMACSHA256=' . $signature;
}
