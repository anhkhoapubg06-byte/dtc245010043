<?php
/**
 * Plugin Name: ICTU Store VietQR
 * Description: Adds VietQR payment instructions for WooCommerce bank transfers.
 */

defined( 'ABSPATH' ) || exit;

function ictu_vietqr_account_details( $settings = null ) {
	if ( ! is_array( $settings ) ) {
		$settings = get_option( 'woocommerce_bacs_settings', array() );
	}

	$accounts = isset( $settings['account_details'] ) && is_array( $settings['account_details'] )
		? $settings['account_details']
		: array();

	foreach ( $accounts as $account ) {
		$bank_name = isset( $account['bank_name'] ) ? strtoupper( $account['bank_name'] ) : '';
		$bank_name = preg_replace( '/[^A-Z0-9]/', '', $bank_name );
		$bank_code = in_array( $bank_name, array( 'MB', 'MBBANK' ), true ) ? 'MB' : '';
		$account_number = isset( $account['account_number'] ) ? preg_replace( '/\s+/', '', $account['account_number'] ) : '';

		if ( '' !== $bank_code && '' !== $account_number ) {
			return array(
				'bank_code'      => $bank_code,
				'account_number' => $account_number,
				'account_name'   => isset( $account['account_name'] ) ? $account['account_name'] : '',
			);
		}
	}

	return null;
}

function ictu_vietqr_reference() {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return '';
	}

	$reference = WC()->session->get( 'ictu_vietqr_reference' );
	if ( ! is_string( $reference ) || '' === $reference ) {
		$reference = 'ICTU' . strtoupper( wp_generate_password( 8, false, false ) );
		WC()->session->set( 'ictu_vietqr_reference', $reference );
	}

	return $reference;
}

function ictu_vietqr_image_url( $amount, $reference, $settings = null ) {
	$account = ictu_vietqr_account_details( $settings );
	if ( ! $account || $amount <= 0 || '' === $reference ) {
		return '';
	}

	$query = array(
		'amount'  => (string) (int) round( $amount ),
		'addInfo' => $reference,
	);

	if ( '' !== $account['account_name'] ) {
		$query['accountName'] = $account['account_name'];
	}

	return add_query_arg(
		$query,
		sprintf(
			'https://img.vietqr.io/image/%s-%s-compact2.png',
			rawurlencode( $account['bank_code'] ),
			rawurlencode( $account['account_number'] )
		)
	);
}

function ictu_vietqr_markup( $amount, $reference, $settings = null ) {
	$image_url = ictu_vietqr_image_url( $amount, $reference, $settings );
	if ( '' === $image_url ) {
		return '<p class="ictu-vietqr-error">' . esc_html__( 'Không thể tạo mã QR. Vui lòng kiểm tra cấu hình tài khoản chuyển khoản trong WooCommerce.', 'ictu-store' ) . '</p>';
	}

	return sprintf(
		'<div class="ictu-vietqr-payment"><p>%1$s</p><img src="%2$s" alt="%3$s" width="280" height="280" loading="lazy" decoding="async" referrerpolicy="no-referrer"><p>%4$s <strong>%5$s</strong></p><p>%6$s</p></div>',
		esc_html__( 'Quét mã VietQR để chuyển khoản:', 'ictu-store' ),
		esc_url( $image_url ),
		esc_attr__( 'Mã QR chuyển khoản VietQR', 'ictu-store' ),
		esc_html__( 'Nội dung chuyển khoản:', 'ictu-store' ),
		esc_html( $reference ),
		esc_html__( 'Đơn hàng chỉ được xác nhận sau khi cửa hàng đối soát khoản chuyển tiền.', 'ictu-store' )
	);
}

function ictu_vietqr_update_bacs_description( $settings ) {
	if ( ! is_array( $settings ) || is_admin() ) {
		return $settings;
	}

	$settings['description'] = __( 'Quét mã VietQR bên dưới để chuyển khoản. Nội dung chuyển khoản đã được tạo sẵn; vui lòng giữ nguyên nội dung này.', 'ictu-store' );

	return $settings;
}
add_filter( 'option_woocommerce_bacs_settings', 'ictu_vietqr_update_bacs_description' );

function ictu_vietqr_classic_gateway_description( $description, $gateway_id ) {
	if ( 'bacs' !== $gateway_id || ! function_exists( 'is_checkout' ) || ! is_checkout() || ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $description;
	}

	$amount = (float) WC()->cart->get_total( 'edit' );
	if ( $amount <= 0 ) {
		return $description;
	}

	return $description . ictu_vietqr_markup( $amount, ictu_vietqr_reference() );
}
add_filter( 'woocommerce_gateway_description', 'ictu_vietqr_classic_gateway_description', 10, 2 );

function ictu_vietqr_enqueue_checkout_script() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) ) {
		return;
	}

	$account = ictu_vietqr_account_details();
	$settings = array(
		'reference' => ictu_vietqr_reference(),
		'error'     => '',
	);

	if ( $account ) {
		$settings['imageBaseUrl'] = sprintf(
			'https://img.vietqr.io/image/%s-%s-compact2.png',
			rawurlencode( $account['bank_code'] ),
			rawurlencode( $account['account_number'] )
		);
		$settings['accountName'] = $account['account_name'];
	} else {
		$settings['error'] = __( 'Không thể tạo mã QR. Vui lòng kiểm tra cấu hình tài khoản chuyển khoản trong WooCommerce.', 'ictu-store' );
	}

	$script_path = __DIR__ . '/ictu-store-vietqr.js';
	wp_enqueue_script(
		'ictu-store-vietqr',
		plugin_dir_url( __FILE__ ) . 'ictu-store-vietqr.js',
		array( 'wp-data' ),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0.0',
		true
	);
	wp_localize_script( 'ictu-store-vietqr', 'ictuVietQrSettings', $settings );
}
add_action( 'wp_enqueue_scripts', 'ictu_vietqr_enqueue_checkout_script', 20 );

function ictu_vietqr_save_order_reference( $order ) {
	if ( ! is_a( $order, 'WC_Order' ) || 'bacs' !== $order->get_payment_method() ) {
		return;
	}

	$reference = ictu_vietqr_reference();
	if ( '' !== $reference ) {
		$order->update_meta_data( '_ictu_vietqr_reference', $reference );
	}
}
add_action( 'woocommerce_checkout_create_order', 'ictu_vietqr_save_order_reference' );
add_action( 'woocommerce_store_api_checkout_update_order_meta', 'ictu_vietqr_save_order_reference' );

function ictu_vietqr_clear_session_reference( $order_id ) {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( $order && 'bacs' === $order->get_payment_method() ) {
		WC()->session->set( 'ictu_vietqr_reference', '' );
	}
}
add_action( 'woocommerce_checkout_order_processed', 'ictu_vietqr_clear_session_reference' );
add_action( 'woocommerce_store_api_checkout_order_processed', 'ictu_vietqr_clear_session_reference' );

function ictu_vietqr_show_order_qr( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || 'bacs' !== $order->get_payment_method() ) {
		return;
	}

	$reference = $order->get_meta( '_ictu_vietqr_reference' );
	if ( ! is_string( $reference ) || '' === $reference ) {
		echo '<p>' . esc_html__( 'Không tìm thấy mã chuyển khoản cho đơn hàng này. Vui lòng liên hệ cửa hàng trước khi thanh toán.', 'ictu-store' ) . '</p>';
		return;
	}

	echo '<section class="ictu-vietqr-order">';
	echo '<h2>' . esc_html__( 'Thanh toán bằng chuyển khoản', 'ictu-store' ) . '</h2>';
	echo ictu_vietqr_markup( (float) $order->get_total(), $reference ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</section>';
}
add_action( 'woocommerce_thankyou_bacs', 'ictu_vietqr_show_order_qr', 20 );
