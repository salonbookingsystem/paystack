<?php
/*
Plugin Name: Salon Booking Paystack Payment Plugin
Description: This is an official add-on of Salon Booking plugin that allows you to integrate Paystack as payment method.
Version: 1.0
Author: Salon Booking team
Author URI: http://salonbookingsystem.com/

*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if (!function_exists('is_plugin_inactive')) {
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
if (is_plugin_inactive('salon-booking-plugin/salon.php') && is_plugin_inactive('salon-booking-plugin-pro/salon.php')) {
	return;
}

if( !class_exists( 'SLN_PaymentMethod_Abstract' ) ) {
	return ;
} // do nothing if main plugin is not installed yet!


// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'SLB_PAYSTACK_STORE_URL', 'http://salonbookingsystem.com/' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the name of your product. This should match the download name in EDD exactly
define( 'SLB_PAYSTACK_ITEM_NAME', 'Paystack payment method' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

define( 'SLB_PAYSTACK_AUTHOR', 'Salon Booking team' );
define( 'SLB_PAYSTACK_VERSION', '1.0' );

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include( dirname( __FILE__ ) . '/slb-paystack-updater-class.php' );
}

include 'slb-paystack-plugin-class.php';

$obj = new SLB_Paystack_Plugin();
