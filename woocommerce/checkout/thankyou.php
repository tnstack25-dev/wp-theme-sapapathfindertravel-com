<?php
/**
 * Custom thank you page.
 *
 * @package Flatsome_Child
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;
?>

<div class="woocommerce-order sapa-order-received">
	<?php if ($order) : ?>
		<?php do_action('woocommerce_before_thankyou', $order->get_id()); ?>

		<?php if ($order->has_status('failed')) : ?>
			<section class="sapa-order-hero sapa-order-hero-failed">
				<div class="sapa-order-status-icon" aria-hidden="true"></div>
				<p class="sapa-order-eyebrow"><?php esc_html_e('Payment failed', 'flatsome-child'); ?></p>
				<h1><?php esc_html_e('We could not process your payment', 'flatsome-child'); ?></h1>
				<p><?php esc_html_e('Unfortunately your order cannot be processed as the originating bank or merchant declined your transaction. Please try again.', 'flatsome-child'); ?></p>
				<div class="sapa-order-actions">
					<a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php esc_html_e('Pay again', 'woocommerce'); ?></a>
					<?php if (is_user_logged_in()) : ?>
						<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button"><?php esc_html_e('My account', 'woocommerce'); ?></a>
					<?php endif; ?>
				</div>
			</section>
		<?php else : ?>
			<section class="sapa-order-hero">
				<div class="sapa-order-status-icon" aria-hidden="true"></div>
				<p class="sapa-order-eyebrow"><?php esc_html_e('Booking confirmed', 'flatsome-child'); ?></p>
				<h1><?php esc_html_e('Thank you. Your order has been received.', 'woocommerce'); ?></h1>
				<p><?php esc_html_e('We have saved your booking details and will contact you shortly if anything needs to be confirmed.', 'flatsome-child'); ?></p>
			</section>

			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details sapa-order-summary">
				<li class="woocommerce-order-overview__order order">
					<span><?php esc_html_e('Order number', 'woocommerce'); ?></span>
					<strong><?php echo esc_html($order->get_order_number()); ?></strong>
				</li>

				<li class="woocommerce-order-overview__date date">
					<span><?php esc_html_e('Date', 'woocommerce'); ?></span>
					<strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong>
				</li>

				<?php if (is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email()) : ?>
					<li class="woocommerce-order-overview__email email">
						<span><?php esc_html_e('Email', 'woocommerce'); ?></span>
						<strong><?php echo esc_html($order->get_billing_email()); ?></strong>
					</li>
				<?php endif; ?>

				<li class="woocommerce-order-overview__total total">
					<span><?php esc_html_e('Total', 'woocommerce'); ?></span>
					<strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
				</li>

				<?php if ($order->get_payment_method_title()) : ?>
					<li class="woocommerce-order-overview__payment-method method">
						<span><?php esc_html_e('Payment method', 'woocommerce'); ?></span>
						<strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
					</li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>

		<div class="sapa-order-content">
			<?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
			<?php do_action('woocommerce_thankyou', $order->get_id()); ?>
		</div>
	<?php else : ?>
		<section class="sapa-order-hero">
			<div class="sapa-order-status-icon" aria-hidden="true"></div>
			<?php wc_get_template('checkout/order-received.php', array('order' => false)); ?>
		</section>
	<?php endif; ?>
</div>
