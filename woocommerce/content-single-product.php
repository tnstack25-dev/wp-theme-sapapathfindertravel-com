<?php
defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$post_id = $product ? $product->get_id() : get_the_ID();

$acf_get = static function ( $field, $default = '' ) use ( $post_id ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $field, $post_id );
		return null !== $value && false !== $value && '' !== $value ? $value : $default;
	}
	return $default;
};

$value_text = static function ( $value ) {
	if ( is_array( $value ) ) {
		if ( isset( $value['label'] ) ) {
			return (string) $value['label'];
		}
		if ( isset( $value['value'] ) ) {
			return (string) $value['value'];
		}
		$parts = array();
		foreach ( $value as $item ) {
			if ( is_object( $item ) && isset( $item->name ) ) {
				$parts[] = (string) $item->name;
			} elseif ( is_scalar( $item ) ) {
				$parts[] = (string) $item;
			}
		}
		return implode( ', ', array_filter( $parts ) );
	}
	if ( is_object( $value ) && isset( $value->name ) ) {
		return (string) $value->name;
	}
	return (string) $value;
};

$render_plain_list = static function ( $rows, $sub_field, $class = '' ) use ( $value_text ) {
	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return;
	}
	?>
	<ul class="<?php echo esc_attr( trim( 'tour-list ' . $class ) ); ?>">
		<?php foreach ( $rows as $row ) : ?>
			<?php
			$text = is_array( $row ) && isset( $row[ $sub_field ] ) ? $value_text( $row[ $sub_field ] ) : $value_text( $row );
			if ( '' === trim( $text ) ) {
				continue;
			}
			?>
			<li><?php echo esc_html( $text ); ?></li>
		<?php endforeach; ?>
	</ul>
	<?php
};

$overview         = $acf_get( 'overview', array() );
$tour_highlights  = $acf_get( 'tour_highlights', array() );
$itinerary_days   = $acf_get( 'Itinerary_days', array() );
$inclusions       = $acf_get( 'inclusions_loop', array() );
$exclusions       = $acf_get( 'exclusions_loop', array() );
$what_to_bring    = $acf_get( 'what_to_bring', array() );
$other_useful     = $acf_get( 'other_useful_info', array() );
$payment_policies = $acf_get( 'payment_policies', array() );
$faqs             = $acf_get( 'faq', array() );
$why_choose_us    = $acf_get( 'why_choose_us', array() );
$video_embed      = $value_text( $acf_get( 'tour_video', $acf_get( 'video_url', '' ) ) );
?>

<article id="product-<?php the_ID(); ?>" <?php wc_product_class( 'tour-product-detail', $product ); ?>>
	<div class="tour-product-shell">
		<div class="tour-main-column">
			<section class="tour-gallery-card">
				<?php woocommerce_show_product_images(); ?>
			</section>

			<?php
			$overview_value = static function ( $key ) use ( $overview, $post_id, $value_text ) {
				if ( is_array( $overview ) && ! empty( $overview[ $key ] ) ) {
					return $value_text( $overview[ $key ] );
				}

				$raw = get_post_meta( $post_id, 'overview_' . $key, true );
				return '' !== $raw && null !== $raw ? $value_text( $raw ) : '';
			};
			$overview_copy = $overview_value( 'overview' );
			$overview_items = array_filter(
				array(
					'Duration'   => $overview_value( 'duration' ),
					'Start Time' => $overview_value( 'start_time' ),
					'End Time'   => $overview_value( 'end_time' ),
					'Tour Type'  => $overview_value( 'tour_type' ),
					'Language'   => $overview_value( 'language' ),
				),
				static function ( $value ) {
					return '' !== trim( (string) $value );
				}
			);
			?>

			<?php if ( has_excerpt() || $overview_copy || ! empty( $overview_items ) ) : ?>
				<section class="tour-section tour-overview-section" aria-label="<?php esc_attr_e( 'Tour overview', 'flatsome-child' ); ?>">
					<h2><?php esc_html_e( 'Overview', 'flatsome-child' ); ?></h2>
					<?php if ( $overview_copy || has_excerpt() ) : ?>
						<div class="tour-overview-copy">
							<?php
							if ( $overview_copy ) {
								echo wp_kses_post( wpautop( $overview_copy ) );
							} else {
								woocommerce_template_single_excerpt();
							}
							?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $overview_items ) ) : ?>
						<div class="tour-overview-grid">
							<?php foreach ( $overview_items as $label => $value ) : ?>
								<div class="tour-overview-item">
									<span><?php echo esc_html( $label ); ?>:</span>
									<strong><?php echo esc_html( $value ); ?></strong>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( $video_embed ) : ?>
				<section class="tour-section tour-video-section">
					<?php echo wp_kses_post( wp_oembed_get( esc_url_raw( $video_embed ) ) ?: $video_embed ); ?>
				</section>
			<?php endif; ?>

			<?php if ( trim( get_the_content() ) ) : ?>
				<section class="tour-section tour-content-section">
					<?php the_content(); ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $tour_highlights ) ) : ?>
				<section class="tour-section">
					<h2><?php esc_html_e( 'Highlights', 'flatsome-child' ); ?></h2>
					<?php $render_plain_list( $tour_highlights, 'highlights', 'tour-list-check' ); ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $inclusions ) || ! empty( $exclusions ) ) : ?>
				<section class="tour-section">
					<h2><?php esc_html_e( 'Inclusion', 'flatsome-child' ); ?></h2>
					<div class="tour-inclusion-grid">
						<?php if ( ! empty( $inclusions ) ) : ?>
							<div class="tour-info-panel tour-panel-included">
								<h3><?php esc_html_e( 'Included', 'flatsome-child' ); ?></h3>
								<?php $render_plain_list( $inclusions, 'inclusions', 'tour-list-check' ); ?>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $exclusions ) ) : ?>
							<div class="tour-info-panel tour-panel-excluded">
								<h3><?php esc_html_e( 'Not Included', 'flatsome-child' ); ?></h3>
								<?php $render_plain_list( $exclusions, 'exclusions', 'tour-list-cross' ); ?>
							</div>
						<?php endif; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $itinerary_days ) && is_array( $itinerary_days ) ) : ?>
				<section class="tour-section tour-itinerary-section">
					<h2><?php esc_html_e( 'Itinerary', 'flatsome-child' ); ?></h2>
					<div class="tour-itinerary">
						<?php foreach ( $itinerary_days as $day_index => $day ) : ?>
							<?php
							$day_title   = $value_text( $day['itinerary_days_title'] ?? $day['itinerary_day_title'] ?? '' );
							$detail_rows = $day['itinerary_details'] ?? array();
							?>
							<details class="tour-itinerary-day" <?php echo 0 === $day_index ? 'open' : ''; ?>>
								<summary>
									<span class="tour-day-title"><?php echo esc_html( $day_title ?: sprintf( __( 'Day %d', 'flatsome-child' ), $day_index + 1 ) ); ?></span>
								</summary>
								<?php if ( ! empty( $detail_rows ) && is_array( $detail_rows ) ) : ?>
									<div class="tour-itinerary-steps">
										<?php foreach ( $detail_rows as $detail ) : ?>
											<div class="tour-itinerary-step">
												<?php if ( ! empty( $detail['itinerary_time'] ) ) : ?>
													<span class="tour-step-time"><?php echo esc_html( $value_text( $detail['itinerary_time'] ) ); ?></span>
												<?php endif; ?>
												<?php if ( ! empty( $detail['itinerary_title'] ) ) : ?>
													<strong><?php echo esc_html( $value_text( $detail['itinerary_title'] ) ); ?></strong>
												<?php endif; ?>
												<?php if ( ! empty( $detail['itinerary_content'] ) ) : ?>
													<p><?php echo esc_html( $value_text( $detail['itinerary_content'] ) ); ?></p>
												<?php endif; ?>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</details>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $what_to_bring ) || ! empty( $other_useful ) ) : ?>
				<section class="tour-section">
					<h2><?php esc_html_e( 'Additional Info', 'flatsome-child' ); ?></h2>
					<?php if ( ! empty( $what_to_bring ) ) : ?>
						<div class="tour-soft-panel">
							<h3><?php esc_html_e( 'What to Bring', 'flatsome-child' ); ?></h3>
							<?php $render_plain_list( $what_to_bring, 'bring', 'tour-list-tags' ); ?>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $other_useful ) ) : ?>
						<div class="tour-soft-panel">
							<h3><?php esc_html_e( 'Other Useful Info', 'flatsome-child' ); ?></h3>
							<?php $render_plain_list( $other_useful, 'useful_info', 'tour-list-info' ); ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $payment_policies ) ) : ?>
				<section class="tour-section">
					<h2><?php esc_html_e( 'Payment & Policies', 'flatsome-child' ); ?></h2>
					<?php $render_plain_list( $payment_policies, 'payment_policies_item', 'tour-list-check' ); ?>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $faqs ) && is_array( $faqs ) ) : ?>
				<section class="tour-section">
					<h2><?php esc_html_e( 'FAQs', 'flatsome-child' ); ?></h2>
					<div class="tour-faq-list">
						<?php foreach ( $faqs as $index => $faq ) : ?>
							<?php
							$question = $value_text( $faq['questions'] ?? '' );
							$answer   = $value_text( $faq['answer'] ?? '' );
							if ( ! $question && ! $answer ) {
								continue;
							}
							?>
							<details class="tour-faq-item" <?php echo 0 === $index ? 'open' : ''; ?>>
								<summary><?php echo esc_html( $question ); ?></summary>
								<?php if ( $answer ) : ?><p><?php echo esc_html( $answer ); ?></p><?php endif; ?>
							</details>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

		</div>

		<aside class="tour-booking-column">
			<div class="tour-booking-card">
				<?php woocommerce_breadcrumb(); ?>
				<h1 class="tour-title"><?php the_title(); ?></h1>
				<?php woocommerce_template_single_rating(); ?>
				<div class="tour-price"
					data-base-price="<?php echo esc_attr( (float) $product->get_price( 'edit' ) ); ?>"
					data-currency-symbol="<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>"
					data-currency-position="<?php echo esc_attr( get_option( 'woocommerce_currency_pos', 'left' ) ); ?>"
					data-price-decimals="<?php echo esc_attr( wc_get_price_decimals() ); ?>">
					<?php woocommerce_template_single_price(); ?>
				</div>
				<div class="tour-booking-form">
					<?php woocommerce_template_single_add_to_cart(); ?>
				</div>
				<?php if ( ! empty( $why_choose_us ) ) : ?>
					<div class="tour-booking-why">
						<h2><?php esc_html_e( 'Why choose us?', 'flatsome-child' ); ?></h2>
						<?php $render_plain_list( $why_choose_us, 'reason', 'tour-list-check' ); ?>
					</div>
				<?php endif; ?>
				<div class="tour-trust-row">
					<span><?php esc_html_e( 'Secure booking', 'flatsome-child' ); ?></span>
					<span><?php esc_html_e( 'Local support', 'flatsome-child' ); ?></span>
				</div>
			</div>
		</aside>
	</div>

	<?php if ( comments_open() || get_comments_number() ) : ?>
		<section class="tour-review-section">
			<h2><?php esc_html_e( 'Review', 'flatsome-child' ); ?></h2>
			<?php comments_template(); ?>
		</section>
	<?php endif; ?>

	<?php woocommerce_output_related_products(); ?>
</article>
