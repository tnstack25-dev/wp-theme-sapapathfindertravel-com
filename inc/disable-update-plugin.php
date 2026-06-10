<?php
// Disable updates for specific plugins
add_filter('site_transient_update_plugins', function ($value) {
    $plugins_to_block = [
        'advanced-custom-fields-pro/acf.php',
        'button-contact-vr/button-contact-vr.php',
    ];

    if (isset($value) && is_object($value)) {
        foreach ($plugins_to_block as $plugin) {
            if (isset($value->response[$plugin])) {
                unset($value->response[$plugin]);
            }
        }
    }

    return $value;
});
