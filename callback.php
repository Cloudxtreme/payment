<?php

require_once 'php/bootstrap.php';

use Neuron\DB\Query;

function cwPaypalIsValidRequest ()
{
	/*
	return array (
		'id' => 15,
		'fee' => 0.1,
		'price' => 10,
		'currency' => 'EUR'
	);
	*/

	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';

	foreach ($_POST as $key => $value)
	{
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
	}

	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);

	if
	(
		!isset ($_POST['item_name']) ||
		!isset ($_POST['item_number']) ||
		!isset ($_POST['payment_status']) ||
		!isset ($_POST['mc_gross']) ||
		!isset ($_POST['mc_currency']) ||
		!isset ($_POST['txn_id']) ||
		!isset ($_POST['receiver_email']) ||
		!isset ($_POST['payer_email'])
	)
	{
		echo 'Required data not found.';
		return false;
	}

	// assign posted variables to local variables
	$item_name = $_POST['item_name'];
	$item_number = $_POST['item_number'];
	$payment_status = $_POST['payment_status'];
	$payment_amount = $_POST['mc_gross'];
	$payment_currency = $_POST['mc_currency'];
	$txn_id = $_POST['txn_id'];
	$receiver_email = $_POST['business'];
	$payer_email = $_POST['payer_email'];

	// Payment fee
	$fee = $_POST['mc_fee'];

	if (!$fp)
	{
		// HTTP ERROR
		echo 'HTTP error.';
	}
	else
	{
		fputs ($fp, $header . $req);
		while (!feof($fp))
		{
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0)
			{
				// check the payment_status is Completed
				if ($payment_status == 'Completed')
				{
					return array (
						'id' => $item_name,
						'fee' => $fee,
						'price' => $payment_amount,
						'currency' => $payment_currency
					);
				}
				else
				{
					echo 'Payment not completed.';
				}
			}
			else if (strcmp ($res, "INVALID") == 0)
			{
				// log for manual investigation
				echo 'Transfer not verified.';
			}
		}
		fclose ($fp);
	}

	return false;
}

function cwCreateAccount ($order)
{
	global $logs;

	$client = new CWResellerClient ();

	$accountId = $client->createAccount ($order['o_accountname'], $order['o_plan']);
	$logs[] = "Created account with id " . $accountId;

	if ($accountId)
	{
		$license = $client->addLicense ($accountId, $order['o_plan'], time (), time () + 60 * 60 * 24 * 365);
		$logs[] = "Created license with id " . $license;

		$password = cwSimpleDecrypt ($order['o_password']);

		$user = $client->addUser ($accountId, $order['o_email'], $password, $order['o_firstName'], $order['o_lastname']);
		$logs[] = "Created user with id " . $user;

		$logs[] = "DONE! Open the champagne!";

		// Update the log.
		Query::update (
			'cw_orders',
			array (
				'o_account_id' => $accountId,
				'o_password' => array (null, Query::PARAM_STRING, true),
				'o_status' => 'CREATED'
			),
			array (
				'o_id' => $order['o_id']
			)
		)->execute ();
	}

}

function cwExtendAccount ($order)
{
	global $logs;

	$client = new CWResellerClient ();

	$accountId = $order['o_account_id'];

	$license = $client->addLicense ($accountId, $order['o_plan'], time (), time () + 60 * 60 * 24 * 365);
	$logs[] = "Created license with id " . $license;
}

$logs = array ();

$details = cwPaypalIsValidRequest ();

if ($details)
{
	$order = Query::select (
		'cw_orders',
		array ('*'),
		array ('o_id' => $details['id'])
	)->execute ();

	if (count ($order) > 0)
	{
		// Found it!
		$order = $order[0];

		if ($order['o_status'] == 'INITIALIZED')
		{
			// Create the account!
			cwCreateAccount ($order);
		}
		else
		{
			cwExtendAccount ($order);
		}

		Query::insert (
			'cw_payments',
			array (
				'o_id' => $order['o_id'],
				'p_price' => $details['price'],
				'p_fee' => $details['fee'],
				'p_currency' => $details['currency']
			)
		)->execute ();
	}
	else
	{
		$logs[] = "Order " . $details['id'] . " was not found.";
	}
}
else
{
	$logs[] = "Received invalid paypal callback.";
}

echo '<pre>';
print_r ($logs);

mail ('thijs@bmgroup.be', 'Paypal callback', implode ("\n", $logs));