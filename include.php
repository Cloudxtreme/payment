<?php
/**
 * This file offers the order flow to be included in an existing website.
 */
use Neuron\Core\Template;

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
	$client = new CWResellerClient ();

	$plans = $client->getPlans ();

	print_r ($plans);

	if (!isset ($plans['reseller']))
	{
		return '<p class="error">Somethign went wrong while fetching the plans.</p>';
	}

	$plans = $plans['reseller']['plans'];

	$page = new Template ();
	$page->set ('plans', $plans);
	return $page->parse ('registration.phpt');
}