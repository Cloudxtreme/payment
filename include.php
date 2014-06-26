<?php
/**
 * This file offers the order flow to be included in an existing website.
 */
use Neuron\Core\Template;

require_once 'php/bootstrap.php';

function cw_get_order_form_html ()
{
	$out = "";

	$out .= cw_show_plans ();
	$out .= cw_show_registration_form ();

	return $out;
}

function cw_show_plans ()
{
	$client = new CWResellerClient ();


	$out = '<pre>';
	$out .= print_r ($client->getPlans (), true);
	$out .= '</pre>';



	return $out;
}

function cw_show_registration_form ()
{
	$page = new Template ();



	return $page->parse ('registration.phpt');
}