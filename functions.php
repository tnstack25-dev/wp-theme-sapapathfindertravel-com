<?php
// Add custom Theme Functions here
require_once get_stylesheet_directory() . '/inc/init.php';

function init_assets()
{
    // Enqueue custom styles and scripts here
    wp_enqueue_style('sapa-swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.1.15');
    wp_enqueue_style('init-main-css', get_stylesheet_directory_uri() . '/assets/css/main.css', array(), null);
    wp_enqueue_script('sapa-swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.1.15', true);
    wp_enqueue_script('init-main-js', get_stylesheet_directory_uri() . '/assets/js/main.js', array('jquery', 'sapa-swiper'), null, true);
}

add_action('wp_enqueue_scripts', 'init_assets');

function sapa_hide_single_product_quantity($sold_individually, $product)
{
    if (is_product()) {
        return true;
    }

    return $sold_individually;
}
add_filter('woocommerce_is_sold_individually', 'sapa_hide_single_product_quantity', 10, 2);

function sapa_get_tour_option_field($field_name, $default = null, $product_id = 0)
{
    $value = null;

    if (function_exists('get_field')) {
        $value = get_field($field_name, 'option');

        if ('' === $value || null === $value) {
            $value = get_field($field_name, 'options');
        }

        if (('' === $value || null === $value) && $product_id) {
            $value = get_field($field_name, $product_id);
        }
    }

    if (('' === $value || null === $value) && $product_id) {
        $value = get_post_meta($product_id, $field_name, true);
    }

    if ('' === $value || null === $value) {
        return $default;
    }

    return $value;
}

function sapa_get_tour_number_option($field_name, $default = 0, $product_id = 0)
{
    return (float) sapa_get_tour_option_field($field_name, $default, $product_id);
}

function sapa_get_tour_discount_config($product_id = 0)
{
    return array(
        'tour_children_2_3' => sapa_get_tour_number_option('discount_for_children_aged_2_3_years_old', 75, $product_id),
        'tour_children_4_5' => sapa_get_tour_number_option('discount_for_children_aged_4_5_years_old', 50, $product_id),
        'tour_children_6_7' => sapa_get_tour_number_option('discount_for_children_aged_6_7_years_old', 25, $product_id),
    );
}

function sapa_get_tour_room_options($product_id = 0)
{
    $rooms = sapa_get_tour_option_field('room_type', array(), $product_id);

    if (empty($rooms) || !is_array($rooms)) {
        $rooms = get_post_meta($product_id, 'room_type', true);
    }

    $options = array();

    if (is_array($rooms)) {
        foreach ($rooms as $index => $room) {
            if (!is_array($room)) {
                continue;
            }

            $name = trim((string) ($room['room_name'] ?? ''));
            $price = (float) ($room['room-price'] ?? $room['room_price'] ?? 0);

            if ('' === $name) {
                continue;
            }

            $key = 'room_' . absint($index);
            $label = $name;

            if ($price > 0) {
                $label .= ' (+' . sapa_clean_price_text($price) . ')';
            }

            $options[$key] = array(
                'label' => $label,
                'name' => $name,
                'price' => $price,
            );
        }
    }

    if (!$options) {
        $options = array(
            'deluxe_sea_view' => array(
                'label' => __('Deluxe sea view', 'flatsome-child'),
                'name' => __('Deluxe sea view', 'flatsome-child'),
                'price' => 0,
            ),
        );
    }

    return $options;
}

function sapa_tour_booking_field_schema($product_id = 0)
{
    $product_id = $product_id ?: get_the_ID();
    $discounts = sapa_get_tour_discount_config($product_id);

    return array(
        'tour_date' => array(
            'label' => __('Tour Date', 'flatsome-child'),
            'type' => 'date',
            'required' => true,
            'sanitize' => 'text',
        ),
        'tour_pickup_address' => array(
            'label' => __('Hotel address for pick up', 'flatsome-child'),
            'type' => 'text',
            'placeholder' => __('Hotel address for pick up', 'flatsome-child'),
            'sanitize' => 'textarea',
        ),
        'tour_adults' => array(
            'label' => __('Number of adults traveling', 'flatsome-child'),
            'type' => 'number',
            'required' => true,
            'default' => 1,
            'min' => 1,
            'sanitize' => 'absint',
        ),
        'tour_infants' => array(
            'label' => __('Number of Infant traveling (0-2 years old)', 'flatsome-child'),
            'type' => 'number',
            'default' => 0,
            'min' => 0,
            'sanitize' => 'absint',
        ),
        'tour_children_2_3' => array(
            'label' => sprintf(__('from 2-3 years old - (discount %s%%)', 'flatsome-child'), rtrim(rtrim(number_format($discounts['tour_children_2_3'], 2), '0'), '.')),
            'type' => 'number',
            'group_label' => __('Number of children traveling', 'flatsome-child'),
            'discount' => $discounts['tour_children_2_3'],
            'default' => 0,
            'min' => 0,
            'sanitize' => 'absint',
        ),
        'tour_children_4_5' => array(
            'label' => sprintf(__('from 4-5 years old - (discount %s%%)', 'flatsome-child'), rtrim(rtrim(number_format($discounts['tour_children_4_5'], 2), '0'), '.')),
            'type' => 'number',
            'discount' => $discounts['tour_children_4_5'],
            'default' => 0,
            'min' => 0,
            'sanitize' => 'absint',
        ),
        'tour_children_6_7' => array(
            'label' => sprintf(__('from 6-7 years old - (discount %s%%)', 'flatsome-child'), rtrim(rtrim(number_format($discounts['tour_children_6_7'], 2), '0'), '.')),
            'type' => 'number',
            'discount' => $discounts['tour_children_6_7'],
            'default' => 0,
            'min' => 0,
            'sanitize' => 'absint',
        ),
        'tour_room_type' => array(
            'label' => __('Room type', 'flatsome-child'),
            'type' => 'select',
            'options' => sapa_get_tour_room_options($product_id),
            'sanitize' => 'key',
        ),
        'tour_type' => array(
            'label' => __('Tour type', 'flatsome-child'),
            'type' => 'select',
            'options' => array(
                'private_tour' => __('Private tour', 'flatsome-child'),
                'group_tour' => __('Group tour', 'flatsome-child'),
            ),
            'sanitize' => 'key',
        ),
        'tour_request' => array(
            'label' => __('Special requirement', 'flatsome-child'),
            'type' => 'textarea',
            'placeholder' => __('Special requirement', 'flatsome-child'),
            'sanitize' => 'textarea',
        ),
    );
}

function sapa_get_posted_tour_value($key, $field)
{
    if (isset($_POST[$key])) {
        return wc_clean(wp_unslash($_POST[$key]));
    }

    return isset($field['default']) ? $field['default'] : '';
}

function sapa_tour_booking_fields()
{
    if (!is_product()) {
        return;
    }

    $product_id = get_the_ID();
    $fields = sapa_tour_booking_field_schema($product_id);
    $discounts = sapa_get_tour_discount_config($product_id);
    ?>
    <div class="tour-extra-fields"
        data-discount-2-3="<?php echo esc_attr($discounts['tour_children_2_3']); ?>"
        data-discount-4-5="<?php echo esc_attr($discounts['tour_children_4_5']); ?>"
        data-discount-6-7="<?php echo esc_attr($discounts['tour_children_6_7']); ?>">
        <?php foreach ($fields as $key => $field) : ?>
            <?php
            $value = sapa_get_posted_tour_value($key, $field);
            $required = !empty($field['required']);
            ?>
            <?php if (!empty($field['group_label'])) : ?>
                <div class="tour-extra-group-title"><?php echo esc_html($field['group_label']); ?></div>
            <?php endif; ?>
            <div class="tour-extra-field tour-extra-field-<?php echo esc_attr($field['type']); ?>">
                <label for="<?php echo esc_attr($key); ?>">
                    <?php echo esc_html($field['label']); ?>
                    <?php if ($required) : ?><span>*</span><?php endif; ?>
                </label>

                <?php if ('select' === $field['type']) : ?>
                    <select id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>"<?php echo $required ? ' required' : ''; ?>>
                        <?php foreach ((array) $field['options'] as $option_value => $option) : ?>
                            <?php
                            $option_label = is_array($option) ? ($option['label'] ?? '') : $option;
                            $option_price = is_array($option) ? (float) ($option['price'] ?? 0) : 0;
                            ?>
                            <option value="<?php echo esc_attr($option_value); ?>"
                                data-room-price="<?php echo esc_attr($option_price); ?>"
                                <?php selected((string) $value, (string) $option_value); ?>>
                                <?php echo esc_html($option_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ('textarea' === $field['type']) : ?>
                    <textarea id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" rows="4"
                        placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"><?php echo esc_textarea($value); ?></textarea>
                <?php else : ?>
                    <input id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>"
                        type="<?php echo esc_attr($field['type']); ?>"
                        value="<?php echo esc_attr($value); ?>"
                        <?php echo isset($field['min']) ? ' min="' . esc_attr($field['min']) . '"' : ''; ?>
                        <?php echo isset($field['placeholder']) ? ' placeholder="' . esc_attr($field['placeholder']) . '"' : ''; ?>
                        <?php echo $required ? ' required' : ''; ?>>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}
add_action('woocommerce_before_add_to_cart_button', 'sapa_tour_booking_fields', 20);

function sapa_get_tour_total_travelers($data)
{
    return (isset($data['tour_adults']) ? absint($data['tour_adults']) : 1)
        + (isset($data['tour_infants']) ? absint($data['tour_infants']) : 0)
        + (isset($data['tour_children_2_3']) ? absint($data['tour_children_2_3']) : 0)
        + (isset($data['tour_children_4_5']) ? absint($data['tour_children_4_5']) : 0)
        + (isset($data['tour_children_6_7']) ? absint($data['tour_children_6_7']) : 0);
}

function sapa_parse_travelers_limit($value)
{
    if (preg_match('/\d+/', (string) $value, $matches)) {
        return absint($matches[0]);
    }

    return 0;
}

function sapa_get_variation_travelers_limit($variation_id, $variation_attrs = array())
{
    foreach ((array) $variation_attrs as $key => $value) {
        if (false !== stripos((string) $key, 'travelers')) {
            return sapa_parse_travelers_limit($value);
        }
    }

    if ($variation_id) {
        $variation = wc_get_product($variation_id);

        if ($variation && $variation->is_type('variation')) {
            foreach ($variation->get_attributes() as $key => $value) {
                if (false !== stripos((string) $key, 'travelers')) {
                    return sapa_parse_travelers_limit($value);
                }
            }
        }
    }

    foreach ($_POST as $key => $value) {
        if (false !== stripos((string) $key, 'travelers')) {
            return is_scalar($value) ? sapa_parse_travelers_limit(wp_unslash($value)) : 0;
        }
    }

    return 0;
}

function sapa_validate_tour_booking_fields($passed, $product_id, $quantity, $variation_id = 0, $variations = array())
{
    $fields = sapa_tour_booking_field_schema($product_id);

    if (empty($_POST['tour_date'])) {
        wc_add_notice(__('Please choose your tour date.', 'flatsome-child'), 'error');
        return false;
    }

    foreach ($fields as $key => $field) {
        if (!empty($field['required']) && (!isset($_POST[$key]) || '' === trim((string) wp_unslash($_POST[$key])))) {
            wc_add_notice(sprintf(__('%s is required.', 'flatsome-child'), $field['label']), 'error');
            return false;
        }

        if ('number' === $field['type'] && isset($_POST[$key])) {
            $value = absint(wp_unslash($_POST[$key]));
            $min = isset($field['min']) ? absint($field['min']) : 0;

            if ($value < $min) {
                wc_add_notice(sprintf(__('%s must be at least %d.', 'flatsome-child'), $field['label'], $min), 'error');
                return false;
            }
        }
    }

    $posted_data = array();
    foreach ($fields as $key => $field) {
        if (isset($_POST[$key])) {
            $posted_data[$key] = sapa_sanitize_tour_booking_value(wp_unslash($_POST[$key]), $field);
        }
    }

    $travelers_limit = sapa_get_variation_travelers_limit($variation_id, $variations);
    $total_travelers = sapa_get_tour_total_travelers($posted_data);

    if ($travelers_limit && $total_travelers > $travelers_limit) {
        wc_add_notice(
            sprintf(
                __('The total number of adults and children cannot exceed the selected Travelers option (%d). Current total: %d.', 'flatsome-child'),
                $travelers_limit,
                $total_travelers
            ),
            'error'
        );
        return false;
    }

    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'sapa_validate_tour_booking_fields', 10, 5);

function sapa_sanitize_tour_booking_value($value, $field)
{
    if ('absint' === ($field['sanitize'] ?? '')) {
        return absint($value);
    }

    if ('key' === ($field['sanitize'] ?? '')) {
        $value = sanitize_key($value);
        return isset($field['options'][$value]) ? $value : '';
    }

    if ('textarea' === ($field['sanitize'] ?? '')) {
        return sanitize_textarea_field($value);
    }

    return sanitize_text_field($value);
}

function sapa_calculate_tour_price_multiplier($data, $discounts = array())
{
    $adults = isset($data['tour_adults']) ? absint($data['tour_adults']) : 1;
    $children_2_3 = isset($data['tour_children_2_3']) ? absint($data['tour_children_2_3']) : 0;
    $children_4_5 = isset($data['tour_children_4_5']) ? absint($data['tour_children_4_5']) : 0;
    $children_6_7 = isset($data['tour_children_6_7']) ? absint($data['tour_children_6_7']) : 0;
    $discount_2_3 = isset($discounts['tour_children_2_3']) ? (float) $discounts['tour_children_2_3'] : (float) ($data['tour_discount_2_3'] ?? 75);
    $discount_4_5 = isset($discounts['tour_children_4_5']) ? (float) $discounts['tour_children_4_5'] : (float) ($data['tour_discount_4_5'] ?? 50);
    $discount_6_7 = isset($discounts['tour_children_6_7']) ? (float) $discounts['tour_children_6_7'] : (float) ($data['tour_discount_6_7'] ?? 25);
    $rate_2_3 = max(0, (100 - $discount_2_3) / 100);
    $rate_4_5 = max(0, (100 - $discount_4_5) / 100);
    $rate_6_7 = max(0, (100 - $discount_6_7) / 100);

    // Infants from 0-2 years old are kept as booking data but do not affect the tour price.
    return max(1, ($adults * 1) + ($children_2_3 * $rate_2_3) + ($children_4_5 * $rate_4_5) + ($children_6_7 * $rate_6_7));
}

function sapa_clean_price_text($price)
{
    return html_entity_decode(wp_strip_all_tags(wc_price((float) $price)), ENT_QUOTES, get_bloginfo('charset'));
}

function sapa_get_selected_room_price($room_key, $fields)
{
    if (empty($room_key) || empty($fields['tour_room_type']['options'][$room_key])) {
        return 0;
    }

    $option = $fields['tour_room_type']['options'][$room_key];

    return is_array($option) ? (float) ($option['price'] ?? 0) : 0;
}

function sapa_add_tour_booking_cart_data($cart_item_data, $product_id, $variation_id)
{
    $product_for_schema = $variation_id ? wp_get_post_parent_id($variation_id) : $product_id;
    $fields = sapa_tour_booking_field_schema($product_for_schema);

    foreach ($fields as $key => $field) {
        if (isset($_POST[$key]) && '' !== trim((string) $_POST[$key])) {
            $cart_item_data[$key] = sapa_sanitize_tour_booking_value(wp_unslash($_POST[$key]), $field);
        }
    }

    if (array_intersect_key($cart_item_data, $fields)) {
        $priced_product = wc_get_product($variation_id ? $variation_id : $product_id);
        $base_price = $priced_product ? (float) $priced_product->get_price('edit') : 0;
        $discounts = sapa_get_tour_discount_config($product_for_schema);
        $price_multiplier = sapa_calculate_tour_price_multiplier($cart_item_data, $discounts);
        $room_price = sapa_get_selected_room_price($cart_item_data['tour_room_type'] ?? '', $fields);
        $travelers_limit = sapa_get_variation_travelers_limit($variation_id);
        $total_travelers = sapa_get_tour_total_travelers($cart_item_data);

        $cart_item_data['tour_base_price'] = $base_price;
        $cart_item_data['tour_price_multiplier'] = $price_multiplier;
        $cart_item_data['tour_room_price'] = $room_price;
        $cart_item_data['tour_discount_2_3'] = $discounts['tour_children_2_3'];
        $cart_item_data['tour_discount_4_5'] = $discounts['tour_children_4_5'];
        $cart_item_data['tour_discount_6_7'] = $discounts['tour_children_6_7'];
        $cart_item_data['tour_total_travelers'] = $total_travelers;
        $cart_item_data['tour_travelers_limit'] = $travelers_limit;
        $cart_item_data['tour_adjusted_price'] = ($base_price * $price_multiplier) + $room_price;
        $cart_item_data['tour_booking_key'] = md5(wp_json_encode($cart_item_data) . microtime());
    }

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'sapa_add_tour_booking_cart_data', 10, 3);

function sapa_apply_tour_booking_cart_price($cart)
{
    if (is_admin() && !wp_doing_ajax()) {
        return;
    }

    if (!$cart || did_action('woocommerce_before_calculate_totals') > 1) {
        return;
    }

    foreach ($cart->get_cart() as $cart_item) {
        if (empty($cart_item['data']) || !isset($cart_item['tour_base_price'], $cart_item['tour_price_multiplier'])) {
            continue;
        }

        $base_price = (float) $cart_item['tour_base_price'];
        $price_multiplier = (float) $cart_item['tour_price_multiplier'];
        $room_price = isset($cart_item['tour_room_price']) ? (float) $cart_item['tour_room_price'] : 0;
        $cart_item['data']->set_price(($base_price * $price_multiplier) + $room_price);
    }
}
add_action('woocommerce_before_calculate_totals', 'sapa_apply_tour_booking_cart_price', 20);

function sapa_show_tour_booking_cart_data($item_data, $cart_item)
{
    $product_id = !empty($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
    $fields = sapa_tour_booking_field_schema($product_id);

    foreach ($fields as $key => $field) {
        if (isset($cart_item[$key]) && '' !== (string) $cart_item[$key]) {
            $value = $cart_item[$key];

            if ('select' === $field['type'] && isset($field['options'][$value])) {
                $option = $field['options'][$value];
                $value = is_array($option) ? ($option['name'] ?? $option['label'] ?? $value) : $option;
            }

            $item_data[] = array(
                'key' => $field['label'],
                'value' => wc_clean($value),
            );
        }
    }

    if (isset($cart_item['tour_price_multiplier']) && (float) $cart_item['tour_price_multiplier'] > 1) {
        $item_data[] = array(
            'key' => __('Billable travelers', 'flatsome-child'),
            'value' => wc_clean(rtrim(rtrim(number_format((float) $cart_item['tour_price_multiplier'], 2), '0'), '.')),
        );
    }

    if (!empty($cart_item['tour_total_travelers'])) {
        $value = absint($cart_item['tour_total_travelers']);

        if (!empty($cart_item['tour_travelers_limit'])) {
            $value .= ' / ' . absint($cart_item['tour_travelers_limit']);
        }

        $item_data[] = array(
            'key' => __('Total travelers', 'flatsome-child'),
            'value' => wc_clean($value),
        );
    }

    if (!empty($cart_item['tour_room_price'])) {
        $item_data[] = array(
            'key' => __('Room price', 'flatsome-child'),
            'value' => wc_clean(sapa_clean_price_text($cart_item['tour_room_price'])),
        );
    }

    return $item_data;
}
add_filter('woocommerce_get_item_data', 'sapa_show_tour_booking_cart_data', 10, 2);

function sapa_save_tour_booking_order_item_meta($item, $cart_item_key, $values, $order)
{
    $product_id = !empty($values['product_id']) ? absint($values['product_id']) : 0;
    $fields = sapa_tour_booking_field_schema($product_id);

    foreach ($fields as $key => $field) {
        if (isset($values[$key]) && '' !== (string) $values[$key]) {
            $value = $values[$key];

            if ('select' === $field['type'] && isset($field['options'][$value])) {
                $option = $field['options'][$value];
                $value = is_array($option) ? ($option['name'] ?? $option['label'] ?? $value) : $option;
            }

            $item->add_meta_data($field['label'], $value, true);
        }
    }

    if (isset($values['tour_base_price'], $values['tour_price_multiplier'], $values['tour_adjusted_price'])) {
        $item->add_meta_data(__('Base tour price', 'flatsome-child'), sapa_clean_price_text($values['tour_base_price']), true);
        $item->add_meta_data(__('Billable travelers', 'flatsome-child'), rtrim(rtrim(number_format((float) $values['tour_price_multiplier'], 2), '0'), '.'), true);
        if (!empty($values['tour_room_price'])) {
            $item->add_meta_data(__('Room price', 'flatsome-child'), sapa_clean_price_text($values['tour_room_price']), true);
        }
        $item->add_meta_data(__('Calculated tour price', 'flatsome-child'), sapa_clean_price_text($values['tour_adjusted_price']), true);
    }

    if (!empty($values['tour_total_travelers'])) {
        $total_travelers = absint($values['tour_total_travelers']);
        $travelers_limit = !empty($values['tour_travelers_limit']) ? absint($values['tour_travelers_limit']) : 0;

        $item->add_meta_data(
            __('Total travelers', 'flatsome-child'),
            $travelers_limit ? $total_travelers . ' / ' . $travelers_limit : $total_travelers,
            true
        );
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'sapa_save_tour_booking_order_item_meta', 10, 4);

function sapa_filter_shop_archive_query($query)
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if (!(is_shop() || is_product_taxonomy() || ($query->is_search() && 'product' === $query->get('post_type')))) {
        return;
    }

    $tax_query = (array) $query->get('tax_query');
    $meta_query = (array) $query->get('meta_query');
    $post__in = (array) $query->get('post__in');

    if (!empty($_GET['location'])) {
        $location = sanitize_text_field(wp_unslash($_GET['location']));
        $meta_query[] = array(
            'key' => 'overview_location',
            'value' => $location,
            'compare' => '=',
        );
    }

    if (!empty($_GET['tour_types']) && is_array($_GET['tour_types'])) {
        $types = array_filter(array_map('sanitize_title', wp_unslash($_GET['tour_types'])));
        if ($types) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $types,
            );
        }
    }

    if (!empty($_GET['durations']) && is_array($_GET['durations'])) {
        $durations = array_filter(array_map('sanitize_text_field', wp_unslash($_GET['durations'])));
        if ($durations) {
            $meta_query[] = array(
                'key' => 'overview_duration',
                'value' => $durations,
                'compare' => 'IN',
            );
        }
    }

    if (!empty($_GET['languages']) && is_array($_GET['languages'])) {
        $languages = array_filter(array_map('sanitize_text_field', wp_unslash($_GET['languages'])));
        if ($languages) {
            $meta_query[] = array(
                'key' => 'overview_language',
                'value' => $languages,
                'compare' => 'IN',
            );
        }
    }

    $min_price = isset($_GET['min_price']) && '' !== $_GET['min_price'] ? wc_format_decimal(wp_unslash($_GET['min_price'])) : '';
    $max_price = isset($_GET['max_price']) && '' !== $_GET['max_price'] ? wc_format_decimal(wp_unslash($_GET['max_price'])) : '';

    if ('' !== $min_price || '' !== $max_price) {
        $price_filter = array(
            'key' => '_price',
            'type' => 'DECIMAL(10,2)',
            'compare' => 'BETWEEN',
            'value' => array(
                '' !== $min_price ? (float) $min_price : 0,
                '' !== $max_price ? (float) $max_price : 999999999,
            ),
        );
        $meta_query[] = $price_filter;
    }

    if (!empty($_GET['travelers'])) {
        global $wpdb;

        $travelers = sanitize_text_field(wp_unslash($_GET['travelers']));
        $like = '%' . $wpdb->esc_like('travelers') . '%';
        $variation_parent_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT p.post_parent
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
                WHERE p.post_type = 'product_variation'
                AND p.post_parent > 0
                AND pm.meta_key LIKE %s
                AND pm.meta_value = %s",
                $like,
                $travelers
            )
        );
        $variation_parent_ids = array_map('absint', (array) $variation_parent_ids);

        if ($post__in) {
            $post__in = array_values(array_intersect($post__in, $variation_parent_ids));
        } else {
            $post__in = $variation_parent_ids;
        }

        if (!$post__in) {
            $post__in = array(0);
        }
    }

    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }
    if (count($meta_query) > 1) {
        $meta_query['relation'] = 'AND';
    }

    $query->set('tax_query', $tax_query);
    $query->set('meta_query', $meta_query);
    if ($post__in) {
        $query->set('post__in', $post__in);
    }
}
add_action('pre_get_posts', 'sapa_filter_shop_archive_query');

function sapa_product_categories_shortcode($atts)
{
    if (!taxonomy_exists('product_cat')) {
        return '';
    }

    $atts = shortcode_atts(
        array(
            'number' => 12,
            'hide_empty' => '1',
            'orderby' => 'name',
            'order' => 'ASC',
            'ids' => '',
        ),
        $atts,
        'sapa_product_categories'
    );

    $args = array(
        'taxonomy' => 'product_cat',
        'hide_empty' => '1' === (string) $atts['hide_empty'],
        'number' => absint($atts['number']),
        'orderby' => sanitize_key($atts['orderby']),
        'order' => 'DESC' === strtoupper((string) $atts['order']) ? 'DESC' : 'ASC',
    );

    if (!empty($atts['ids'])) {
        $args['include'] = array_filter(array_map('absint', explode(',', (string) $atts['ids'])));
    }

    $terms = get_terms($args);

    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    ob_start();
    ?>
    <div class="sapa-category-showcase sapa-category-slider swiper">
        <div class="swiper-wrapper" role="list">
            <?php foreach ($terms as $term) : ?>
                <?php
                $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : '';

                if (!$image_url && function_exists('wc_placeholder_img_src')) {
                    $image_url = wc_placeholder_img_src('large');
                }

                $count = absint($term->count);
                $count_text = sprintf(
                    _n('%s tour', '%s tours', $count, 'flatsome-child'),
                    number_format_i18n($count)
                );
                ?>
                <a class="sapa-category-card swiper-slide" href="<?php echo esc_url(get_term_link($term)); ?>" role="listitem">
                    <?php if ($image_url) : ?>
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($term->name); ?>" loading="lazy">
                    <?php endif; ?>
                    <span class="sapa-category-card-shade" aria-hidden="true"></span>
                    <span class="sapa-category-card-content">
                        <span class="sapa-category-card-title"><?php echo esc_html($term->name); ?></span>
                        <span class="sapa-category-card-count"><?php echo esc_html($count_text); ?></span>
                    </span>
                    <span class="sapa-category-card-arrow" aria-hidden="true"><i class="fa-solid fa-angles-right" style="color: rgb(255, 255, 255);"></i></span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="sapa-category-pagination swiper-pagination"></div>
        <button class="sapa-category-nav sapa-category-nav-prev swiper-button-prev" type="button" aria-label="<?php esc_attr_e('Previous categories', 'flatsome-child'); ?>"></button>
        <button class="sapa-category-nav sapa-category-nav-next swiper-button-next" type="button" aria-label="<?php esc_attr_e('Next categories', 'flatsome-child'); ?>"></button>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('sapa_product_categories', 'sapa_product_categories_shortcode');
add_shortcode('sapa_tour_categories', 'sapa_product_categories_shortcode');

function sapa_triangle_images_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'left' => '',
            'right' => '',
            'left_alt' => '',
            'right_alt' => '',
        ),
        $atts,
        'sapa_triangle_images'
    );

    $image_from_attr = static function ($value) {
        $value = trim((string) $value);

        if ('' === $value) {
            return '';
        }

        if (ctype_digit($value)) {
            return wp_get_attachment_image_url(absint($value), 'large') ?: '';
        }

        return esc_url_raw($value);
    };

    $left_image = $image_from_attr($atts['left']);
    $right_image = $image_from_attr($atts['right']);

    if (!$left_image && !$right_image) {
        return '';
    }

    ob_start();
    ?>
    <div class="sapa-triangle-images" aria-label="<?php echo esc_attr__('Travel image composition', 'flatsome-child'); ?>">
        <svg class="sapa-triangle-route" viewBox="0 0 760 250" aria-hidden="true" focusable="false">
            <path d="M50 112 C160 76 245 188 330 84 C392 8 430 160 352 170 C500 224 548 36 710 88" />
        </svg>
        <span class="sapa-triangle-pin" aria-hidden="true"></span>

        <?php if ($left_image) : ?>
            <figure class="sapa-triangle-figure sapa-triangle-left">
                <img src="<?php echo esc_url($left_image); ?>" alt="<?php echo esc_attr($atts['left_alt']); ?>" loading="lazy">
            </figure>
        <?php endif; ?>

        <?php if ($right_image) : ?>
            <figure class="sapa-triangle-figure sapa-triangle-right">
                <img src="<?php echo esc_url($right_image); ?>" alt="<?php echo esc_attr($atts['right_alt']); ?>" loading="lazy">
            </figure>
        <?php endif; ?>

        <span class="sapa-triangle-badge" aria-hidden="true">
            <span>Explore · Wild Journey · Nature ·</span>
        </span>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('sapa_triangle_images', 'sapa_triangle_images_shortcode');



function chen_js_slider_chay_muot_abweb()
{
    ?>
    <script type="text/javascript" src="https://unpkg.com/flickity@2.2.1/dist/flickity.pkgd.min.js"
        id="wp-hooks-js"></script>
    <script>
        let tocdo = 1;
        let flickity_chay = null;
        let trang_thai_dung_lai = false;
        const class_slider_chay = document.querySelector('.slider-chay-muot');
        if (class_slider_chay) {
            const cap_nhat_slider = () => {
                if (trang_thai_dung_lai) return;
                if (flickity_chay.slides) {
                    flickity_chay.x = (flickity_chay.x - tocdo) % flickity_chay.slideableWidth;
                    flickity_chay.selectedIndex = flickity_chay.dragEndRestingSelect();
                    flickity_chay.updateSelectedSlide();
                    flickity_chay.settle(flickity_chay.x);
                }
                window.requestAnimationFrame(cap_nhat_slider);
            };

            const dung_lai = () => {
                trang_thai_dung_lai = true;
            };

            const play = () => {
                if (trang_thai_dung_lai) {
                    trang_thai_dung_lai = false;
                    window.requestAnimationFrame(cap_nhat_slider);
                }
            };
            flickity_chay = new Flickity(class_slider_chay, {
                autoPlay: false,
                prevNextButtons: true,
                pageDots: false,
                draggable: true,
                wrapAround: true,
                selectedAttraction: 0.015,
                friction: 0.25
            });
            flickity_chay.x = 0;
            class_slider_chay.addEventListener('mouseenter', dung_lai, false);
            class_slider_chay.addEventListener('focusin', dung_lai, false);
            class_slider_chay.addEventListener('mouseleave', play, false);
            class_slider_chay.addEventListener('focusout', play, false);

            flickity_chay.on('dragStart', () => {
                trang_thai_dung_lai = true;
            });
            cap_nhat_slider();
        }

    </script>
    <?php
}

add_action('wp_footer', 'chen_js_slider_chay_muot_abweb');
