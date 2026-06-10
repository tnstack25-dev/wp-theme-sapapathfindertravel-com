<?php
defined( 'ABSPATH' ) || exit;

global $wp_query;

get_header( 'shop' );

$shop_id = wc_get_page_id( 'shop' );
$hero_image = $shop_id > 0 ? get_the_post_thumbnail_url( $shop_id, 'full' ) : '';

if ( is_product_category() ) {
	$term = get_queried_object();
	if ( $term && ! is_wp_error( $term ) ) {
		$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
		if ( $thumbnail_id ) {
			$hero_image = wp_get_attachment_image_url( $thumbnail_id, 'full' );
		}
	}
}

if ( ! $hero_image && have_posts() ) {
	$first_product = $wp_query->posts[0] ?? null;
	if ( $first_product ) {
		$hero_image = get_the_post_thumbnail_url( $first_product->ID, 'full' );
	}
}

$shop_url = $shop_id > 0 ? get_permalink( $shop_id ) : get_post_type_archive_link( 'product' );
$clear_url = $shop_url;
if ( is_product_category() ) {
	$term = get_queried_object();
	if ( $term && ! is_wp_error( $term ) ) {
		$clear_url = get_term_link( $term );
	}
}
$found    = isset( $wp_query->found_posts ) ? absint( $wp_query->found_posts ) : 0;

$selected_types     = array_map( 'sanitize_title', wp_unslash( (array) ( $_GET['tour_types'] ?? array() ) ) );
$selected_durations = array_map( 'sanitize_text_field', wp_unslash( (array) ( $_GET['durations'] ?? array() ) ) );
$selected_languages = array_map( 'sanitize_text_field', wp_unslash( (array) ( $_GET['languages'] ?? array() ) ) );
$selected_min_price = isset( $_GET['min_price'] ) ? wc_format_decimal( wp_unslash( $_GET['min_price'] ) ) : '';
$selected_max_price = isset( $_GET['max_price'] ) ? wc_format_decimal( wp_unslash( $_GET['max_price'] ) ) : '';
$selected_location  = isset( $_GET['location'] ) ? sanitize_text_field( wp_unslash( $_GET['location'] ) ) : '';
$selected_travelers = isset( $_GET['travelers'] ) ? sanitize_text_field( wp_unslash( $_GET['travelers'] ) ) : '';

$sapa_distinct_meta_values = static function ( $meta_key ) {
	global $wpdb;

	$values = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != '' ORDER BY meta_value ASC",
			$meta_key
		)
	);

	return array_values( array_filter( array_map( 'sanitize_text_field', (array) $values ) ) );
};

$sapa_distinct_travelers_values = static function () {
	global $wpdb;

	$like = '%' . $wpdb->esc_like( 'travelers' ) . '%';
	$values = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT pm.meta_value
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE p.post_type = 'product_variation'
			AND pm.meta_key LIKE %s
			AND pm.meta_value != ''
			ORDER BY pm.meta_value ASC",
			$like
		)
	);

	return array_values( array_filter( array_map( 'sanitize_text_field', (array) $values ) ) );
};

$sapa_price_bounds = static function () {
	global $wpdb;

	$bounds = $wpdb->get_row(
		"SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) AS min_price, MAX(CAST(meta_value AS DECIMAL(10,2))) AS max_price
		FROM {$wpdb->postmeta}
		WHERE meta_key = '_price'
		AND meta_value != ''"
	);

	$min = $bounds && null !== $bounds->min_price ? floor( (float) $bounds->min_price ) : 0;
	$max = $bounds && null !== $bounds->max_price ? ceil( (float) $bounds->max_price ) : 1000;

	return array( max( 0, $min ), max( 1, $max ) );
};

$filter_types = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => 10,
	)
);
$filter_locations = $sapa_distinct_meta_values( 'overview_location' );
$filter_durations = $sapa_distinct_meta_values( 'overview_duration' );
$filter_languages = $sapa_distinct_meta_values( 'overview_language' );
$filter_travelers = $sapa_distinct_travelers_values();
list( $price_min_bound, $price_max_bound ) = $sapa_price_bounds();
$active_min_price = '' !== $selected_min_price ? (float) $selected_min_price : $price_min_bound;
$active_max_price = '' !== $selected_max_price ? (float) $selected_max_price : $price_max_bound;
?>

<main class="sapa-shop-page">
	<section class="sapa-shop-hero" <?php echo $hero_image ? 'style="background-image:url(' . esc_url( $hero_image ) . ')"' : ''; ?>>
		<div class="sapa-shop-hero-inner">
			<h1><?php woocommerce_page_title(); ?></h1>
			<form class="sapa-shop-search" role="search" method="get" action="<?php echo esc_url( $shop_url ); ?>">
				<input type="hidden" name="post_type" value="product">
				<label class="sapa-search-field sapa-search-where">
					<span><?php esc_html_e( 'Destination', 'flatsome-child' ); ?></span>
					<select name="location">
						<option value=""><?php esc_html_e( 'Where to?', 'flatsome-child' ); ?></option>
						<?php foreach ( $filter_locations as $location ) : ?>
							<option value="<?php echo esc_attr( $location ); ?>" <?php selected( $selected_location, $location ); ?>><?php echo esc_html( $location ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="sapa-search-field sapa-search-date">
					<span><?php esc_html_e( 'Travel date', 'flatsome-child' ); ?></span>
					<input type="date" name="tour_date" value="<?php echo isset( $_GET['tour_date'] ) ? esc_attr( wp_unslash( $_GET['tour_date'] ) ) : ''; ?>" placeholder="<?php esc_attr_e( 'Select date', 'flatsome-child' ); ?>">
				</label>
				<label class="sapa-search-field sapa-search-guests">
					<span><?php esc_html_e( 'Travelers', 'flatsome-child' ); ?></span>
					<select name="travelers">
						<option value=""><?php esc_html_e( 'Number of guests', 'flatsome-child' ); ?></option>
						<?php foreach ( $filter_travelers as $travelers ) : ?>
							<option value="<?php echo esc_attr( $travelers ); ?>" <?php selected( $selected_travelers, $travelers ); ?>><?php echo esc_html( str_replace( '-', ' - ', $travelers ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<button type="submit" aria-label="<?php esc_attr_e( 'Search tours', 'flatsome-child' ); ?>"></button>
			</form>
		</div>
	</section>

	<section class="sapa-shop-body">
		<button class="sapa-mobile-filter-toggle" type="button" aria-controls="sapa-shop-filter-drawer" aria-expanded="false">
			<span aria-hidden="true"></span>
			<?php esc_html_e( 'Filter tours', 'flatsome-child' ); ?>
		</button>
		<div class="sapa-filter-drawer-overlay" aria-hidden="true"></div>
		<aside class="sapa-shop-sidebar">
			<form id="sapa-shop-filter-drawer" class="sapa-filter-panel" method="get" action="<?php echo esc_url( $clear_url ); ?>">
				<div class="sapa-filter-mobile-head">
					<strong><?php esc_html_e( 'Filters', 'flatsome-child' ); ?></strong>
					<button class="sapa-filter-drawer-close" type="button" aria-label="<?php esc_attr_e( 'Close filters', 'flatsome-child' ); ?>"></button>
				</div>
				<?php if ( get_search_query() ) : ?>
					<input type="hidden" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>
				<?php if ( $selected_location ) : ?>
					<input type="hidden" name="location" value="<?php echo esc_attr( $selected_location ); ?>">
				<?php endif; ?>
				<?php if ( $selected_travelers ) : ?>
					<input type="hidden" name="travelers" value="<?php echo esc_attr( $selected_travelers ); ?>">
				<?php endif; ?>
				<?php if ( ! empty( $_GET['tour_date'] ) ) : ?>
					<input type="hidden" name="tour_date" value="<?php echo esc_attr( wp_unslash( $_GET['tour_date'] ) ); ?>">
				<?php endif; ?>

				<section class="sapa-filter-section sapa-filter-price">
					<h2><?php esc_html_e( 'Price range', 'flatsome-child' ); ?></h2>
					<div class="sapa-price-range" data-min="<?php echo esc_attr( $price_min_bound ); ?>" data-max="<?php echo esc_attr( $price_max_bound ); ?>">
						<div class="sapa-price-track" aria-hidden="true"><span></span></div>
						<input class="sapa-price-range-min" type="range" min="<?php echo esc_attr( $price_min_bound ); ?>" max="<?php echo esc_attr( $price_max_bound ); ?>" step="1" value="<?php echo esc_attr( $active_min_price ); ?>">
						<input class="sapa-price-range-max" type="range" min="<?php echo esc_attr( $price_min_bound ); ?>" max="<?php echo esc_attr( $price_max_bound ); ?>" step="1" value="<?php echo esc_attr( $active_max_price ); ?>">
					</div>
					<div class="sapa-price-inputs">
						<label>
							<span><?php esc_html_e( 'Min. price', 'flatsome-child' ); ?></span>
							<input class="sapa-price-number-min" type="number" min="<?php echo esc_attr( $price_min_bound ); ?>" max="<?php echo esc_attr( $price_max_bound ); ?>" step="1" name="min_price" value="<?php echo esc_attr( $active_min_price ); ?>" placeholder="$0">
						</label>
						<label>
							<span><?php esc_html_e( 'Max. price', 'flatsome-child' ); ?></span>
							<input class="sapa-price-number-max" type="number" min="<?php echo esc_attr( $price_min_bound ); ?>" max="<?php echo esc_attr( $price_max_bound ); ?>" step="1" name="max_price" value="<?php echo esc_attr( $active_max_price ); ?>" placeholder="$2,250">
						</label>
					</div>
				</section>

				<?php if ( ! empty( $filter_types ) && ! is_wp_error( $filter_types ) ) : ?>
					<section class="sapa-filter-section">
						<h2><?php esc_html_e( 'Types', 'flatsome-child' ); ?></h2>
						<ul class="sapa-checkbox-list">
							<?php foreach ( $filter_types as $type ) : ?>
								<li>
									<label>
										<input type="checkbox" name="tour_types[]" value="<?php echo esc_attr( $type->slug ); ?>" <?php checked( in_array( $type->slug, $selected_types, true ) ); ?>>
										<span><?php echo esc_html( $type->name ); ?></span>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php if ( ! empty( $filter_durations ) ) : ?>
					<section class="sapa-filter-section">
						<h2><?php esc_html_e( 'Durations', 'flatsome-child' ); ?></h2>
						<ul class="sapa-checkbox-list">
							<?php foreach ( $filter_durations as $duration ) : ?>
								<li>
									<label>
										<input type="checkbox" name="durations[]" value="<?php echo esc_attr( $duration ); ?>" <?php checked( in_array( $duration, $selected_durations, true ) ); ?>>
										<span><?php echo esc_html( $duration ); ?></span>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php if ( ! empty( $filter_languages ) ) : ?>
					<section class="sapa-filter-section">
						<h2><?php esc_html_e( 'Languages', 'flatsome-child' ); ?></h2>
						<ul class="sapa-checkbox-list">
							<?php foreach ( $filter_languages as $language ) : ?>
								<li>
									<label>
										<input type="checkbox" name="languages[]" value="<?php echo esc_attr( $language ); ?>" <?php checked( in_array( $language, $selected_languages, true ) ); ?>>
										<span><?php echo esc_html( $language ); ?></span>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<div class="sapa-filter-actions">
					<button class="sapa-filter-submit" type="submit"><?php esc_html_e( 'Apply filters', 'flatsome-child' ); ?></button>
					<a href="<?php echo esc_url( $clear_url ); ?>"><?php esc_html_e( 'Clear filter', 'flatsome-child' ); ?></a>
				</div>
			</form>
		</aside>

		<div class="sapa-shop-products">
			<?php do_action( 'woocommerce_before_main_content' ); ?>
			<?php do_action( 'woocommerce_before_shop_loop' ); ?>

			<div class="sapa-shop-topbar">
				<div class="sapa-shop-count">
					<?php
					printf(
						esc_html( _n( '%s tour found', '%s tours found', $found, 'flatsome-child' ) ),
						esc_html( number_format_i18n( $found ) )
					);
					?>
				</div>
				<div class="sapa-shop-actions">
					<a href="<?php echo esc_url( $clear_url ); ?>"><?php esc_html_e( 'Clear filter', 'flatsome-child' ); ?></a>
					<?php woocommerce_catalog_ordering(); ?>
				</div>
			</div>

			<?php if ( woocommerce_product_loop() ) : ?>
				<?php woocommerce_product_loop_start(); ?>

				<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
					<?php while ( have_posts() ) : ?>
						<?php the_post(); ?>
						<?php do_action( 'woocommerce_shop_loop' ); ?>
						<?php wc_get_template_part( 'content', 'product' ); ?>
					<?php endwhile; ?>
				<?php endif; ?>

				<?php woocommerce_product_loop_end(); ?>
				<?php do_action( 'woocommerce_after_shop_loop' ); ?>
			<?php else : ?>
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			<?php endif; ?>

			<?php do_action( 'flatsome_products_after' ); ?>
			<?php do_action( 'woocommerce_after_main_content' ); ?>
		</div>
	</section>
</main>

<?php
get_footer( 'shop' );
