<?php
/**
 * This file offers the order flow to be included in an existing website.
 */
require_once 'php/bootstrap.php';

function cw_get_order_form_html ()
{
	$client = new CWResellerClient ();

	$out = '<pre>';
	$out .= print_r ($client->post (array ('test' => 1)), true);
	$out .= '</pre>';

	return $out;
}