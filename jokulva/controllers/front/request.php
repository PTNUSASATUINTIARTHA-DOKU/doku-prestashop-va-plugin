<?php

class JokulVaRequestModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	public function postProcess()
	{
		$jokulva = new jokulva();
		$task		         = $_GET['task'];
		$path = 'module:jokulva/views/templates/front/';

		switch ($task) {
			case "redirect":
				if (empty($_POST)) {
					echo "Stop : Access Not Valid";
					die;
				}

				$trx = array();

				$WORDS = $_POST['WORDS'];
				$BODY = $_POST['BODY'];
				$SIGNATURE = $_POST['SIGNATURE'];

				$rawdata =	"Words:".$WORDS ."\n". 
            				"Data-Body:".$BODY . "\n".
            				"Signature:".$SIGNATURE;

				$trx['words']                	= $_POST['WORDS'];
				$trx['amount']               	= $_POST['AMOUNT'];
				$trx['invoice_number']      	= $_POST['TRANSIDMERCHANT'];
				$trx['status_code']          	= $_POST['STATUSCODE'];
				$trx['payment_exp']          	= $_POST['PAYMENTEXP'];
				$trx['payment_how']          	= $_POST['PAYMENTHOW'];
				$trx['raw_post_data']          	= $rawdata;

				if (isset($_POST['PAYMENTCODE'])) $trx['payment_code'] = $_POST['PAYMENTCODE'];

				$config = $jokulva->getServerConfig();

				$order_id = $jokulva->get_order_id($trx['invoice_number']);

				if (!$order_id) {
					$order_state = $config['DOKU_AWAITING_PAYMENT'];
					$trx['amount']                = $_POST['AMOUNT'];
					$jokulva->validateOrder($trx['invoice_number'], $order_state, $trx['amount'], $jokulva->displayName, $trx['invoice_number']);
					$order_id = $jokulva->get_order_id($trx['invoice_number']);
				}

				$order = new Order($order_id);
				$trx_amount = number_format($order->getOrdersTotalPaid(), 2, '.', '');
				$words = $trx['words'];
				
					$trx['payment_channel']  = $_POST['PAYMENTCHANNEL'];
					$trx['ip_address']       = $jokulva->getipaddress();
					$trx['process_datetime'] = date("Y-m-d H:i:s");
					$trx['process_type']     = 'REDIRECT';

					$statuscode = $trx['status_code'];
					$statusnotify = 'SUCCESS';
					$resultcheck = $jokulva->checkTrx($trx, 'NOTIFY', $statusnotify);

					switch ($trx['status_code']) {
						case "0000":
							$result_msg = "SUCCESS";
							break;

						default:
							$result_msg = "FAILED";
							break;
					}

					# Check if the transaction have notify message  
					$result = $jokulva->checkTrx($trx, 'NOTIFY', $result_msg);
					$checkredirect = $jokulva->checkTrx($trx, 'REDIRECT');
					
						$trx['message'] = "Redirect process come from DOKU. Transaction is awaiting for payment";
						$status         = "pending";
						$status_no      = $config['DOKU_AWAITING_PAYMENT'];
						$template       = "pending_va.tpl";

						switch ($trx['payment_channel']) {
							case "MANDIRI":
								$payment_channel = "Mandiri VA";
								break;

							case "MANDIRI_SYARIAH":
								$payment_channel = "Mandiri Syariah VA";
								break;

							default:
								$payment_channel = "unknown channel";
								break;
						}

						$this->context->smarty->assign(array(
							'payment_channel' => $payment_channel, # ATM Transfer / Alfa Payment
							'payment_code'    => $trx['payment_code']
						));

						# Update order status
						$howToPay = $this->fetchEmailTemplate($payment_channel, $trx);
						$email_data = array(
							'{payment_channel}' => $payment_channel,
							'{amount}' => $trx['amount'],
							'{payment_code}' => $trx['payment_code'],
							'{how_to_pay}' => $howToPay
						);
						$jokulva->set_order_status($order_id, $status_no, $email_data);

						# Insert transaction redirect to table jokulva
						$jokulva->add_jokulva($trx);
						
						$this->setTemplate($path . $template);
						
						$cart = $this->context->cart;

    					if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            				Tools::redirect('index.php?controller=order&step=1');
						}
						
						$customer = new Customer($cart->id_customer);
        				if (!Validate::isLoadedObject($customer))
        				     Tools::redirect('index.php?controller=order&step=1');

						$currency = $this->context->currency;
						
						$total = (float)$cart->getOrderTotal(true, Cart::BOTH);

						Configuration::updateValue('PAYMENT_CHANNEL',trim($payment_channel));
						Configuration::updateValue('PAYMENT_CODE',trim($trx['payment_code']));
						
						Configuration::updateValue('PAYMENT_AMOUNT',$trx_amount);
						
						$newDate = new DateTime($trx['payment_exp']);
						Configuration::updateValue('PAYMENT_EXP',$newDate->format('d M yy H:m'));
						Configuration::updateValue('PAYMENTHOW',$trx['payment_how']);
						

						$config = Configuration::getMultiple(array('SERVER_DEST', 'MALL_ID_DEV', 'SHARED_KEY_DEV', 'MALL_ID_PROD', 'SHARED_KEY_PROD'));

						if ( empty($config['SERVER_DEST']) || intval($config['SERVER_DEST']) == 0 )
						{
							$MALL_ID    = Tools::safeOutput(Configuration::get('MALL_ID_DEV'));
							$SHARED_KEY = Tools::safeOutput(Configuration::get('SHARED_KEY_DEV'));				
						}
						else
						{
							$MALL_ID    = Tools::safeOutput(Configuration::get('MALL_ID_PROD'));
							$SHARED_KEY = Tools::safeOutput(Configuration::get('SHARED_KEY_PROD'));					
						}

						$mailVars = array(
							'{jokulva_mall_id}'     => $MALL_ID,
							'{jokulva_shared_key}'  => $SHARED_KEY
						);

						// $this->module->validateOrder($cart->id, Configuration::get('PS_OS_BANKWIRE'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
						Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
				break;
            case "redirectFailed":
                $template       = "failed.tpl";
                parent::initContent();
                $this->setTemplate($path . $template);
                break;
		}
	}

	private function fetchEmailTemplate($paymentChannel, $trx)
 	{
        switch ($paymentChannel) {
        	case "MANDIRI":
                return "1.&nbsp;&emsp;Masukkan kartu ATM Mandiri, lalu masukkan PIN ATM<br>
                    	2.&nbsp;&emsp;Pilih menu Bayar/Beli<br>
                    	3.&nbsp;&emsp;Pilih “Lainnya” dan pilih “Lainnya” kembali<br>
                        4.&nbsp;&emsp;Pilih “Ecommerce”<br>
                        5.&nbsp;&emsp;Masukkan 5 digit awal dari nomor Mandiri VA (Virtual Account) yang didapat (" . substr($trx['payment_code'], 0, 5) . ")<br>
                        6.&nbsp;&emsp;Masukkan keseluruhan nomor VA " . $trx['payment_code'] . "<br>
                        7.&nbsp;&emsp;Masukkan jumlah pembayaran<br>
                        8.&nbsp;&emsp;Nomor VA, Nama, dan jumlah pembayaran akan ditampilkan di layar<br>
                        9.&nbsp;&emsp;Tekan angka 1 dan pilih “YA”<br>
                        10.&emsp;Konfirmasi pembayaran dan pilih “YA<br>
                        11.&emsp;Transaksi selesai. Mohon simpan bukti transaksi";
  
                    default:
                        return "1.&nbsp;&emsp;Masukkan kartu ATM Mandiri, lalu masukkan PIN ATM<br>
                                2.&nbsp;&emsp;Pilih menu Bayar/Beli<br>
                                3.&nbsp;&emsp;Pilih “Lainnya” dan pilih “Lainnya” kembali<br>
                                4.&nbsp;&emsp;Pilih “Ecommerce”<br>
                            	5.&nbsp;&emsp;Masukkan 5 digit awal dari nomor Mandiri VA (Virtual Account) yang didapat (" . substr($trx['payment_code'], 0, 5) . ")<br>
                            	6.&nbsp;&emsp;Masukkan keseluruhan nomor VA " . $trx['payment_code'] . "<br>
                                7.&nbsp;&emsp;Masukkan jumlah pembayaran<br>
                                8.&nbsp;&emsp;Nomor VA, Nama, dan jumlah pembayaran akan ditampilkan di layar<br>
                                9.&nbsp;&emsp;Tekan angka 1 dan pilih “YA”<br>
                                10.&emsp;Konfirmasi pembayaran dan pilih “YA<br>
                                11.&emsp;Transaksi selesai. Mohon simpan bukti transaksi";
        }
    }
}
