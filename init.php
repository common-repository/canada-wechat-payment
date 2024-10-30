<?php
/*
 * Plugin Name: Canada Wechat Payment
 * Plugin URI: https://www.paybuzz.ca/try-now/
 * Description:Provide Canadian Online Store with Wechat Pay and Ali pay, refund function available. Without wechat /Ali register in China, without Chinese bank account needed.针对加拿大客户使用，在线购物Woocommerce系统添加支付宝支付,支持扫码支付和退款功能，在线购物Woocommerce系统添加微信支付功能,支持扫码支付和退款功能。 无需中国微信和支付宝注册，无需中国银行账户，直接收款到加拿大银行账户。
 * Version: 2.1.10
 * Author: Paybuzz
 * Author URI:https://www.paybuzz.ca
 * Text Domain: wechat Payments for WooCommerce
 */

if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly

if (! defined ( 'C_WEIXINPAY' )) {define ( 'C_WEIXINPAY', 'C_WEIXINPAY' );} else {return;}
define('C_WeChat_VERSION','1.0.0');
define('C_WC_WeChat_ID','cwechatwcpaymentgateway' );
define('C_WC_WeChat_DIR',rtrim(plugin_dir_path(__FILE__),'/'));
define('C_WC_WeChat_URL',rtrim(plugin_dir_url(__FILE__),'/'));
load_plugin_textdomain( 'wechatpay', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/'  );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'c_wechat_wc_payment_gateway_plugin_edit_link' );
add_action( 'init', 'c_wechat_wc_payment_gateway_init' );

register_activation_hook ( __FILE__, function(){
    global $wpdb;
    $wpdb->query(
       "update {$wpdb->prefix}postmeta
        set meta_value='wechat'
        where meta_key='_payment_method'
        and meta_value='wechatwcpaymentgateway';");
});

if(!function_exists('c_wechat_wc_payment_gateway_init')){
    function c_wechat_wc_payment_gateway_init() {
        if( !class_exists('WC_Payment_Gateway') )  return;
        require_once C_WC_WeChat_DIR .'/class-wechat-wc-payment-gateway.php';
        $api = new CXHWechatWCPaymentGateway();
        
        $api->check_wechatpay_response();
        
        add_filter('woocommerce_payment_gateways',array($api,'woocommerce_wechatpay_add_gateway' ),10,1);
        add_action( 'wp_ajax_XH_WECHAT_PAYMENT_GET_ORDER', array($api, "get_order_status" ) );
        add_action( 'wp_ajax_nopriv_XH_WECHAT_PAYMENT_GET_ORDER', array($api, "get_order_status") );
        add_action( 'woocommerce_receipt_'.$api->id, array($api, 'receipt_page'));
        add_action( 'woocommerce_update_options_payment_gateways_' . $api->id, array ($api,'process_admin_options') ); // WC >= 2.0
        add_action( 'woocommerce_update_options_payment_gateways', array ($api,'process_admin_options') );
        add_action( 'wp_enqueue_scripts', array ($api,'wp_enqueue_scripts') );


    }
}

function c_wechat_wc_payment_gateway_plugin_edit_link( $links ){
    return array_merge(
        array(
            'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section='.C_WC_WeChat_ID) . '">'.__( 'Settings', 'wechatpay' ).'</a>'
        ),
        $links
    );
}
?>