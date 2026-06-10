<?php
defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || false === wc_get_loop_product_visibility( $product->get_id() ) || ! $product->is_visible() ) {
	return;
}

$product_id = $product->get_id();
$permalink  = get_permalink( $product_id );
$overview   = function_exists( 'get_field' ) ? get_field( 'overview', $product_id ) : array();
$out_of_stock = ! $product->is_in_stock();
$classes   = array();
$classes[] = 'product-small';
$classes[] = 'col';
$classes[] = 'has-hover';
$classes[] = 'sapa-loop-card';

if ( $out_of_stock ) {
	$classes[] = 'out-of-stock';
}

$loop_value_text = static function ( $value ) {
	if ( is_array( $value ) ) {
		if ( isset( $value['label'] ) ) {
			return (string) $value['label'];
		}
		if ( isset( $value['value'] ) ) {
			return (string) $value['value'];
		}
		if ( isset( $value['name'] ) ) {
			return (string) $value['name'];
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

$overview_value = static function ( $key ) use ( $overview, $product_id, $loop_value_text ) {
	if ( is_array( $overview ) && ! empty( $overview[ $key ] ) ) {
		return $loop_value_text( $overview[ $key ] );
	}

	$raw = get_post_meta( $product_id, 'overview_' . $key, true );
	return '' !== $raw && null !== $raw ? $loop_value_text( $raw ) : '';
};

$duration  = $overview_value( 'duration' );
$tour_type = $overview_value( 'tour_type' );
$language  = $overview_value( 'language' );
$location  = $overview_value( 'location' );

if ( '' === trim( $location ) ) {
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$location = $terms[0]->name;
	}
}

$excerpt = $product->get_short_description();
if ( '' === trim( $excerpt ) ) {
	$excerpt = get_the_excerpt( $product_id );
}
?>

<div <?php wc_product_class( $classes, $product ); ?>>
	<div class="col-inner">
		<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

		<article class="sapa-loop-card-inner">
			<a class="sapa-loop-image" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( get_the_title( $product_id ) ); ?>">
				<?php echo wp_kses_post( woocommerce_get_product_thumbnail( 'woocommerce_thumbnail' ) ); ?>
			</a>

			<?php if ( $out_of_stock ) : ?>
				<div class="out-of-stock-label"><?php esc_html_e( 'Out of stock', 'woocommerce' ); ?></div>
			<?php endif; ?>

			<div class="sapa-loop-content">
				<?php if ( $location ) : ?>
					<div class="sapa-loop-location"><?php echo esc_html( $location ); ?></div>
				<?php endif; ?>

				<h3 class="sapa-loop-title">
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $product_id ) ); ?></a>
				</h3>

				<?php if ( $duration || $tour_type || $language ) : ?>
					<div class="sapa-loop-meta">
						<?php if ( $duration ) : ?>
							<div class="sapa-loop-meta-item sapa-loop-meta-duration"><?php echo esc_html( $duration ); ?></div>
						<?php endif; ?>
						<?php if ( $tour_type ) : ?>
							<div class="sapa-loop-meta-item sapa-loop-meta-type"><?php echo esc_html( $tour_type ); ?></div>
						<?php endif; ?>
						<?php if ( $language ) : ?>
							<div class="sapa-loop-meta-item sapa-loop-meta-language"><?php echo esc_html( $language ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $excerpt ) : ?>
					<p class="sapa-loop-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 9, '...' ) ); ?></p>
				<?php endif; ?>

				<div class="sapa-loop-footer">
					<?php if ( $product->get_price_html() ) : ?>
						<div class="sapa-loop-price">
							<span><?php esc_html_e( 'From', 'flatsome-child' ); ?></span>
							<strong><?php echo wp_kses_post( $product->get_price_html() ); ?></strong>
						</div>
					<?php endif; ?>

					<a class="sapa-loop-button" href="<?php echo esc_url( $permalink ); ?>">
						<?php esc_html_e( 'View tour', 'flatsome-child' ); ?>
					</a>
				</div>
			</div>
		</article>

		<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
	</div>
</div>
