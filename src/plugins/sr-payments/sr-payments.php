<?php
/**
 * Plugin Name: SR Collectibles — Payment Methods
 * Plugin URI:  https://github.com/Shazid41
 * Description: Card payments with a branded secure-pay popup (Visa / Mastercard / Amex), plus bKash, Cash on Delivery and Bank Transfer. Card gateway runs in demo mode until Stripe API keys are saved in its settings — then checkout redirects to Stripe Checkout for real charges.
 * Author:      Md. Shazidur Rahaman
 * Author URI:  https://github.com/Shazid41
 * Version:     2.0.0
 * Requires PHP: 7.4
 * Text Domain: sr-payments
 *
 * @package sr-payments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True when the card gateway has no Stripe secret key (popup demo mode).
 *
 * @return bool
 */
function sr_payments_card_demo_mode() {
	$settings = get_option( 'woocommerce_sr_card_settings', array() );
	return '' === trim( isset( $settings['stripe_secret_key'] ) ? (string) $settings['stripe_secret_key'] : '' );
}

/**
 * Register the gateways once WooCommerce has loaded.
 */
function sr_payments_boot() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	/**
	 * Shared manual gateway: places order on-hold and shows instructions.
	 */
	abstract class SR_Manual_Gateway extends WC_Payment_Gateway {

		/**
		 * Gateway config supplied by the child class.
		 *
		 * @var array
		 */
		protected $sr_config = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id                 = $this->sr_config['id'];
			$this->method_title       = $this->sr_config['method_title'];
			$this->method_description = $this->sr_config['method_description'];
			$this->has_fields         = false;
			$this->init_form_fields();
			$this->init_settings();
			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}

		/**
		 * Admin settings fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'      => array(
					'title'   => __( 'Enable/Disable', 'sr-payments' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this payment method', 'sr-payments' ),
					'default' => 'yes',
				),
				'title'        => array(
					'title'   => __( 'Title', 'sr-payments' ),
					'type'    => 'text',
					'default' => $this->sr_config['title'],
				),
				'description'  => array(
					'title'   => __( 'Description', 'sr-payments' ),
					'type'    => 'textarea',
					'default' => $this->sr_config['description'],
				),
				'instructions' => array(
					'title'   => __( 'Checkout instructions', 'sr-payments' ),
					'type'    => 'textarea',
					'default' => $this->sr_config['instructions'],
				),
			);
		}

		/**
		 * Show instructions on the checkout below the method.
		 */
		public function payment_fields() {
			if ( $this->description ) {
				echo wp_kses_post( wpautop( $this->description ) );
			}
		}

		/**
		 * Show instructions in customer emails for on-hold orders.
		 *
		 * @param WC_Order $order Order object.
		 * @param bool     $sent_to_admin Whether this email goes to the admin.
		 * @param bool     $plain_text Whether this is a plain text email.
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
			if ( $sent_to_admin || ! $order || $order->get_payment_method() !== $this->id || ! $order->has_status( 'on-hold' ) ) {
				return;
			}
			$instructions = $this->get_option( 'instructions' );
			if ( ! $instructions ) {
				return;
			}
			echo $plain_text ? wp_strip_all_tags( $instructions ) . "\n\n" : wp_kses_post( wpautop( wptexturize( $instructions ) ) );
		}

		/**
		 * Process the (manual) payment: hold the order.
		 *
		 * @param int $order_id Order ID.
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			$order->update_status( 'on-hold', __( 'Awaiting manual payment confirmation.', 'sr-payments' ) );

			if ( function_exists( 'wc_reduce_stock_levels' ) ) {
				wc_reduce_stock_levels( $order_id );
			}
			WC()->cart->empty_cart();

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}

	/**
	 * Card gateway — secure-pay popup; real charges via Stripe Checkout when a
	 * secret key is configured, labelled demo mode otherwise.
	 */
	final class SR_Gateway_Card extends SR_Manual_Gateway {

		protected $sr_config = array(
			'id'                 => 'sr_card',
			'method_title'       => 'Cards — SR Secure Pay (Visa / Mastercard / Amex)',
			'method_description' => 'Branded card payment popup at checkout. Add a Stripe secret key below to charge real cards (Stripe Checkout hosted page).',
			'title'              => 'Credit / Debit Card',
			'description'        => 'Pay securely with Visa, Mastercard or Amex. A secure payment window opens when you place the order.',
			'instructions'       => 'Your card payment was confirmed in the secure SR Pay window.',
		);

		/**
		 * Constructor.
		 */
		public function __construct() {
			parent::__construct();

			$this->form_fields['stripe_secret_key'] = array(
				'title'             => __( 'Stripe Secret Key', 'sr-payments' ),
				'type'              => 'password',
				'description'       => sprintf(
					/* translators: %s: URL to Stripe dashboard */
					__( 'Paste your Stripe secret key (sk_live_… or sk_test_…) to charge real cards. Create a free account at %s. Leave empty to keep the checkout popup in demo mode (no charge, nothing stored).', 'sr-payments' ),
					'https://dashboard.stripe.com/register'
				),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => array( 'autocomplete' => 'off' ),
			);
		}

		/**
		 * Checkout display under the radio: description + accepted card marks.
		 */
		public function payment_fields() {
			if ( $this->description ) {
				echo wp_kses_post( wpautop( $this->description ) );
			}
			echo '<span class="sr-pay-marks" aria-hidden="true"><span class="sr-pay-mark sr-pay-mark--visa">VISA</span><span class="sr-pay-mark sr-pay-mark--mc"><i></i><i></i></span><span class="sr-pay-mark sr-pay-mark--amex">AMEX</span></span>';
			if ( sr_payments_card_demo_mode() ) {
				echo '<small class="sr-pay-demo-note">' . esc_html__( 'Demo mode active — card details are validated in the popup but never sent or stored, and no charge is made.', 'sr-payments' ) . '</small>';
			}
		}

		/**
		 * Create a Stripe Checkout session for the order.
		 *
		 * @param WC_Order $order Order object.
		 * @return array|WP_Error Array with url/id on success.
		 */
		private function create_stripe_session( $order ) {
			$sk = trim( (string) $this->get_option( 'stripe_secret_key' ) );
			if ( '' === $sk ) {
				return new WP_Error( 'sr_pay_no_key', __( 'No Stripe secret key configured.', 'sr-payments' ) );
			}

			$currency = strtolower( $order->get_currency() );
			// Currencies with zero decimals per Stripe.
			$zero_decimal = array( 'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf' );
			$total        = (float) $order->get_total();
			$unit         = in_array( $currency, $zero_decimal, true ) ? (int) round( $total ) : (int) round( $total * 100 );

			$body = array(
				'mode'                                 => 'payment',
				'success_url'                          => add_query_arg( 'sr_session_id', '{CHECKOUT_SESSION_ID}', $order->get_checkout_order_received_url() ),
				'cancel_url'                           => add_query_arg( 'sr_pay_cancel', '1', wc_get_checkout_url() ),
				'client_reference_id'                  => (string) $order->get_id(),
				'line_items[0][quantity]'              => 1,
				'line_items[0][price_data][currency]'  => $currency,
				'line_items[0][price_data][unit_amount]' => $unit,
				'line_items[0][price_data][product_data][name]' => 'Order ' . $order->get_order_number() . ' — ' . get_bloginfo( 'name' ),
			);
			if ( $order->get_billing_email() ) {
				$body['customer_email'] = $order->get_billing_email();
			}

			$response = wp_remote_post(
				'https://api.stripe.com/v1/checkout/sessions',
				array(
					'timeout' => 30,
					'headers' => array(
						'Authorization' => 'Bearer ' . $sk,
					),
					'body'    => $body,
				)
			);
			if ( is_wp_error( $response ) ) {
				return $response;
			}
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( empty( $data['url'] ) || empty( $data['id'] ) ) {
				$message = isset( $data['error']['message'] ) ? $data['error']['message'] : __( 'Unexpected Stripe response.', 'sr-payments' );
				return new WP_Error( 'sr_pay_stripe', $message );
			}
			return $data;
		}

		/**
		 * Process payment: Stripe redirect when configured, demo hold otherwise.
		 *
		 * @param int $order_id Order ID.
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! sr_payments_card_demo_mode() ) {
				$session = $this->create_stripe_session( $order );
				if ( is_wp_error( $session ) ) {
					wc_add_notice( __( 'Card payment could not start: ', 'sr-payments' ) . $session->get_error_message(), 'error' );
					return array( 'result' => 'failure' );
				}
				$order->set_payment_method_title( $this->get_option( 'title' ) . ' (Stripe)' );
				$order->update_meta_data( '_sr_stripe_session_id', sanitize_text_field( $session['id'] ) );
				$order->update_status( 'pending', __( 'Redirected to Stripe Checkout.', 'sr-payments' ) );
				$order->save();

				return array(
					'result'   => 'success',
					'redirect' => $session['url'],
				);
			}

			// Demo mode: popup already validated locally; never receive/store card data.
			$order->update_status( 'on-hold', __( 'Demo card payment confirmed in the SR Pay popup — no live processor connected, card data never transmitted or stored.', 'sr-payments' ) );
			if ( function_exists( 'wc_reduce_stock_levels' ) ) {
				wc_reduce_stock_levels( $order_id );
			}
			WC()->cart->empty_cart();

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}

	/**
	 * bKash gateway — manual Send Money verification.
	 */
	final class SR_Gateway_BKash extends SR_Manual_Gateway {
		protected $sr_config = array(
			'id'                 => 'sr_bkash',
			'method_title'       => 'bKash (Manual)',
			'method_description' => 'Manual bKash payment — customer sends money and submits the Transaction ID.',
			'title'              => 'bKash',
			'description'        => 'Send the total amount via bKash Send Money to 01710-000000 (SR Collectibles), then reply to the order confirmation email with your Transaction ID (TrxID).',
			'instructions'       => 'Send the total via bKash Send Money to 01710-000000 and reply with your TrxID. We verify and confirm your order within minutes during business hours.',
		);
	}
}
add_action( 'plugins_loaded', 'sr_payments_boot' );

/**
 * Register the gateways with WooCommerce.
 *
 * @param array $gateways Existing gateways.
 * @return array
 */
function sr_payments_add_gateways( $gateways ) {
	$gateways[] = 'SR_Gateway_Card';
	$gateways[] = 'SR_Gateway_BKash';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'sr_payments_add_gateways' );

/**
 * Front-end assets for the secure-pay popup (checkout only).
 */
function sr_payments_enqueue_frontend() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || ! sr_payments_card_demo_mode() ) {
		return; // Real Stripe mode: hosted Stripe page handles input.
	}
	$base = plugin_dir_url( __FILE__ );
	wp_enqueue_style( 'sr-payments', $base . 'assets/sr-payments.css', array(), (string) filemtime( plugin_dir_path( __FILE__ ) . 'assets/sr-payments.css' ) );
	wp_enqueue_script( 'sr-payments', $base . 'assets/sr-payments.js', array(), (string) filemtime( plugin_dir_path( __FILE__ ) . 'assets/sr-payments.js' ), true );
	wp_localize_script(
		'sr-payments',
		'srPay',
		array(
			'demo'    => sr_payments_card_demo_mode(),
			'preview' => isset( $_GET['fh-pay-preview'] ) ? '1' : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		)
	);
}
add_action( 'wp_enqueue_scripts', 'sr_payments_enqueue_frontend' );

/**
 * Render the secure-pay popup markup on the checkout page.
 */
function sr_payments_render_modal() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || ! sr_payments_card_demo_mode() ) {
		return;
	}
	?>
	<div class="fh-paymodal" id="fh-paymodal" role="dialog" aria-modal="true" aria-labelledby="fh-paymodal-title" aria-hidden="true">
		<div class="fh-paymodal__backdrop" data-srpay-close></div>
		<div class="fh-paymodal__panel" role="document">
			<span class="fh-paymodal__bar" aria-hidden="true"></span>
			<div class="fh-paymodal__head">
				<span class="fh-paymodal__brand">SR</span>
				<div>
					<h3 id="fh-paymodal-title">SR Secure Pay</h3>
					<p>Enter your card details — this window closes when payment is confirmed.</p>
				</div>
				<button type="button" class="fh-paymodal__close" data-srpay-close aria-label="Close payment window">&times;</button>
			</div>

			<div class="fh-paymodal__body" data-srpay-step="form">
				<div class="fh-paymodal__amount-row">
					<span>Amount due</span>
					<strong id="fh-paymodal-amount">$0.00</strong>
				</div>

				<label class="fh-payfield">
					<span>Card number</span>
					<div class="fh-payfield__num">
						<input id="fh-pay-num" inputmode="numeric" autocomplete="cc-number" placeholder="1234 5678 9012 3456" maxlength="19" />
						<span class="fh-payfield__brand" data-srpay-brand aria-hidden="true"></span>
					</div>
					<em data-srpay-error="num"></em>
				</label>

				<div class="fh-paymodal__row">
					<label class="fh-payfield">
						<span>Expiry</span>
						<input id="fh-pay-exp" inputmode="numeric" autocomplete="cc-exp" placeholder="MM / YY" maxlength="7" />
						<em data-srpay-error="exp"></em>
					</label>
					<label class="fh-payfield">
						<span>CVV</span>
						<input id="fh-pay-cvv" inputmode="numeric" autocomplete="cc-csc" placeholder="•••" maxlength="4" type="password" />
						<em data-srpay-error="cvv"></em>
					</label>
				</div>

				<label class="fh-payfield">
					<span>Name on card</span>
					<input id="fh-pay-name" autocomplete="cc-name" placeholder="Name as printed on card" />
					<em data-srpay-error="name"></em>
				</label>

				<button type="button" class="fh-btn fh-btn--gold fh-paymodal__pay" id="fh-pay-confirm">Pay <span data-srpay-amount>$0.00</span></button>
			</div>

			<div class="fh-paymodal__done" data-srpay-step="done" hidden>
				<svg viewBox="0 0 52 52" width="64" height="64" aria-hidden="true"><circle class="fh-paycheck__circle" cx="26" cy="26" r="24" fill="none"></circle><path class="fh-paycheck__tick" d="M14 27l8 8 16-17" fill="none"></path></svg>
				<h4>Payment confirmed</h4>
				<p>Placing your order…</p>
			</div>

			<div class="fh-paymodal__foot">
				<svg width="12" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 118 0v4"></path></svg>
				<?php if ( sr_payments_card_demo_mode() ) : ?>
					<span><strong>Demo mode</strong> — nothing is sent or stored. Add a Stripe key in WooCommerce → Payments to charge real cards.</span>
				<?php else : ?>
					<span>256-bit encrypted · PCI-DSS compliant</span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'sr_payments_render_modal', 30 );

/**
 * Verify the Stripe session on the thank-you page and complete the order.
 *
 * @param int $order_id Order ID.
 */
function sr_payments_verify_stripe_return( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || 'sr_card' !== $order->get_payment_method() || $order->is_paid() ) {
		return;
	}
	$session_id = $order->get_meta( '_sr_stripe_session_id' );
	if ( ! $session_id ) {
		return;
	}
	$settings = get_option( 'woocommerce_sr_card_settings', array() );
	$sk       = isset( $settings['stripe_secret_key'] ) ? trim( (string) $settings['stripe_secret_key'] ) : '';
	if ( '' === $sk ) {
		return;
	}
	$response = wp_remote_get(
		'https://api.stripe.com/v1/checkout/sessions/' . rawurlencode( $session_id ),
		array(
			'timeout' => 30,
			'headers' => array( 'Authorization' => 'Bearer ' . $sk ),
		)
	);
	if ( is_wp_error( $response ) ) {
		return;
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( isset( $data['payment_status'] ) && 'paid' === $data['payment_status'] ) {
		$order->payment_complete( isset( $data['payment_intent'] ) ? sanitize_text_field( $data['payment_intent'] ) : 'stripe_' . $session_id );
		$order->add_order_note( __( 'Stripe Checkout confirmed the payment.', 'sr-payments' ) );
	}
}
add_action( 'woocommerce_thankyou', 'sr_payments_verify_stripe_return' );

/**
 * Notice when a buyer cancels the Stripe page back to checkout.
 */
function sr_payments_cancel_notice() {
	if ( function_exists( 'is_checkout' ) && is_checkout() && isset( $_GET['sr_pay_cancel'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wc_add_notice( __( 'Card payment was cancelled — you can try again or pick another method.', 'sr-payments' ), 'notice' );
	}
}
add_action( 'template_redirect', 'sr_payments_cancel_notice' );

/**
 * Admin notice while the card gateway runs in demo mode.
 */
function sr_payments_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! sr_payments_card_demo_mode() ) {
		return;
	}
	if ( false === strpos( (string) $screen->id, 'woocommerce' ) && 'dashboard' !== $screen->id ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>SR Payments:</strong> the card popup is in demo mode. Paste a Stripe secret key in <em>WooCommerce → Settings → Payments → Cards</em> to charge real cards.</p></div>';
}
add_action( 'admin_notices', 'sr_payments_admin_notice' );
