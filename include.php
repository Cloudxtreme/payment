<?php
/**
 * This file offers the order flow to be included in an existing website.
 */
use Neuron\Core\Template;
use Neuron\Core\Tools;
use Neuron\DB\Database;
use Neuron\DB\Query;

require_once 'php/bootstrap.php';

function cw_get_order_form_html ()
{
	$out = "";

	//$out .= cw_show_plans ();
	$out .= cw_show_registration_form ();

	return $out;
}

function cw_show_plans ()
{
	$client = new CWResellerClient ();

	$plans = $client->getPlans ();
	if (!isset ($plans['reseller']))
	{
		return '<p class="error">Somethign went wrong while fetching the plans.</p>';
	}

	$plans = $plans['reseller']['plans'];

	$out = '<pre>';
	$out .= print_r ($plans, true);
	$out .= '</pre>';



	return $out;
}

function cw_show_registration_form ()
{
	global $cwPricingConfig;

	$client = new CWResellerClient ();
	$plans = $client->getPlans ();

	if (!isset ($plans['reseller']))
	{
		return '<p class="error">Somethign went wrong while fetching the plans.</p>';
	}
	$plans = $plans['reseller']['plans'];

	$errors = array ();

	// Process input
	$action = Tools::getInput ($_POST, 'action', 'varchar');
	if ($action == 'submit')
	{
		Database::getInstance ()->connect();

		$firstname = Tools::getInput ($_POST, 'firstName', 'varchar');
		if (!$firstname)
		{
			$errors[] = 'Please provide your first name.';
		}

		$name = Tools::getInput ($_POST, 'name', 'varchar');
		if (!$name)
		{
			$errors[] = 'Please provide your last name.';
		}

		$email = Tools::getInput ($_POST, 'email', 'varchar');
		if (!$email)
		{
			$errors[] = 'Please provide a valid email address.';
		}

		$accountname = Tools::getInput ($_POST, 'accountname', 'varchar');
		if (!$accountname)
		{
			$errors[] = 'Please provide a valid account name.';
		}

		$password1 = Tools::getInput ($_POST, 'password1', 'varchar');
		$password2 = Tools::getInput ($_POST, 'password2', 'varchar');

		if (!$password1)
		{
			$errors[] = 'Please provide a password.';
		}

		else if (strlen ($password1) < 6)
		{
			$errors[] = 'Your password must be at least 6 characters long.';
		}

		else if ($password1 != $password2)
		{
			$errors[] = 'Your passwords do not match.';
		}

		$plan = Tools::getInput ($_POST, 'plan', 'int');
		if (!$plan)
		{
			$errors[] = 'Please select a plan.';
		}

		if (!isset ($cwPricingConfig[$plan]))
		{
			$errors[] = 'You have selected an invalid plan.';
		}

		if (count ($errors) == 0)
		{
			$price = $cwPricingConfig[$plan];

			$id = Query::insert
			(
				'cw_orders',
				array (
					'o_accountname' => $accountname,
					'o_email' => $email,
					'o_firstName' => $firstname,
					'o_lastname' => $name,
					'o_password' => cwSimpleCrypt ($password1),
					'o_plan' => $plan,
					'o_registration' => array (time (), Query::PARAM_DATE),
					'o_price' => $price
				)
			)->execute ();

			$page = new Template ();

			// Paypal flow
			$page->set ('price', $price);
			$page->set ('name', 'Cloudwalkers plan ' . $plan);
			$page->set ('number', $plan);
			$page->set ('notify_url', BASE_URL . 'callback.php');

			return $page->parse ('registered.phpt');
		}
	}

	// Show form.
	$defaults = array ();
	$defaults['firstName'] = Tools::getInput ($_POST, 'firstName', 'varchar');
	$defaults['name'] = Tools::getInput ($_POST, 'name', 'varchar');
	$defaults['email'] = Tools::getInput ($_POST, 'email', 'varchar');
	$defaults['accountname'] = Tools::getInput ($_POST, 'accountname', 'varchar');



	$page = new Template ();
	$page->set ('plans', $plans);
	$page->set ('defaults', $defaults);
	$page->set ('errors', $errors);
	return $page->parse ('registration.phpt');
}