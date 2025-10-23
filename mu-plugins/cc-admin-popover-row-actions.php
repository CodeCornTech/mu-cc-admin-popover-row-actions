<?php
/**
 * Plugin Name: CC Admin Popover Row Actions
 * Description: Converte le row-actions della WP List Table in un popover apribile dal badge ID , con flag bilaterali (define + filter) e asset singleton .
 * Author: CodeCorn™
 * Version: 1.0.0
 * License: MIT
 */

if ( ! defined('ABSPATH') ) exit;

define('CC_APRA_DIR', __DIR__);
define('CC_APRA_SLUG', 'cc-admin-popover-row-actions');
define('CC_APRA_NS', 'cc_apra');
define('CC_APRA_ASSETS_DIR', CC_APRA_DIR . '/codecorn/admin-popover-row-actions/assets');
define('CC_APRA_ASSETS_URL', plugins_url('codecorn/admin-popover-row-actions/assets', __FILE__));

/**
 * ===== Bilateral flags (defaults from define , filters win) =====
 *
 * wp-config.php facoltativo :
 *   define('CC_APRA_ENABLED', true);
 *   define('CC_APRA_ON_HOVER', true);
 *   define('CC_APRA_HIDE_QUICK_EDIT', true);
 *   define('CC_APRA_POST_TYPES', 'wp_ar_clients,post'); // CSV semplice
 */

function cc_apra_get_flag( $name, $default ) {
    // leggi eventuale define
    $defined = null;
    switch ($name) {
        case 'enabled':
            $defined = defined('CC_APRA_ENABLED') ? (bool) CC_APRA_ENABLED : null; break;
        case 'on_hover':
            $defined = defined('CC_APRA_ON_HOVER') ? (bool) CC_APRA_ON_HOVER : null; break;
        case 'hide_quick_edit':
            $defined = defined('CC_APRA_HIDE_QUICK_EDIT') ? (bool) CC_APRA_HIDE_QUICK_EDIT : null; break;
        case 'post_types':
            if ( defined('CC_APRA_POST_TYPES') ) {
                $csv = (string) CC_APRA_POST_TYPES;
                $defined = array_filter(array_map('trim', explode(',', $csv)));
            }
            break;
    }

    $base = $defined !== null ? $defined : $default;

    // i filter VINCONO sempre sui define
    $filter_map = [
        'enabled'         => 'cc_apra_enabled',
        'on_hover'        => 'cc_apra_on_hover',
        'hide_quick_edit' => 'cc_apra_hide_quick_edit',
        'post_types'      => 'cc_apra_post_types',
    ];

    $tag = isset($filter_map[$name]) ? $filter_map[$name] : null;
    if ( $tag ) {
        /**
         * @filter cc_apra_enabled         -> bool
         * @filter cc_apra_on_hover        -> bool
         * @filter cc_apra_hide_quick_edit -> bool
         * @filter cc_apra_post_types      -> array<string>
         */
        $base = apply_filters( $tag, $base );
    }
    return $base;
}

/**
 * Carica asset solo nella lista dei post supportati .
 */
add_action('current_screen', function( $screen ) {
    if ( ! $screen || $screen->base !== 'edit' ) return;

    $enabled    = cc_apra_get_flag('enabled', true);
    $post_types = cc_apra_get_flag('post_types', ['wp_ar_clients']); // default CPT tuo
    if ( ! $enabled || ! in_array( $screen->post_type, (array)$post_types, true ) ) return;

    add_action('admin_enqueue_scripts', 'cc_apra_enqueue_assets', 20);
});

/**
 * Enqueue singleton . Ordine : pre.js → first.js → init.js .
 * CSS critico inlined per ridurre richieste ( ma overridabile via filter ) .
 */
function cc_apra_enqueue_assets( $hook ) {
    static $done = false;
    if ( $done ) return;
    $done = true;

    // flags risolte (filter > define)
    $data = [
        'enabled'        => (bool) cc_apra_get_flag('enabled', true),
        'onHover'        => (bool) cc_apra_get_flag('on_hover', true),
        'hideQuickEdit'  => (bool) cc_apra_get_flag('hide_quick_edit', true),
        'postTypes'      => array_values( (array) cc_apra_get_flag('post_types', ['wp_ar_clients']) ),
        'selectors'      => [
            'table'         => '.wp-list-table.table-view-list',
            'primaryCell'   => 'td.column-primary',
            'triggerLink'   => 'a.wp_ar_id_td, .wp_ar-badge',
            'rowActions'    => '.row-actions',
        ],
    ];

    // ===== CSS critico =====
    $critical_path = CC_APRA_ASSETS_DIR . '/css/critical.css';
    $critical_css  = file_exists($critical_path) ? trim(file_get_contents($critical_path)) : '';
    $critical_css  = apply_filters('cc_apra_critical_css', $critical_css);

    // Registra ed inietta
    wp_register_script( 'cc-apra-pre',   CC_APRA_ASSETS_URL . '/js/pre.js',   ['jquery'], '1.0.0', true );
    wp_register_script( 'cc-apra-first', CC_APRA_ASSETS_URL . '/js/first.js', ['jquery','cc-apra-pre'], '1.0.0', true );
    wp_register_script( 'cc-apra-init',  CC_APRA_ASSETS_URL . '/js/init.js',  ['jquery','cc-apra-first'], '1.0.0', true );

    // Localize opzioni ai JS
    wp_localize_script( 'cc-apra-pre', 'CC_APRA', $data );

    // Enqueue
    wp_enqueue_script('cc-apra-pre');
    wp_enqueue_script('cc-apra-first');
    wp_enqueue_script('cc-apra-init');

    // CSS inline come singleton
    if ( $critical_css ) {
        $style_handle = 'cc-apra-critical';
        // handle “fittizio” : agganciamo al core per garantire stampa nel footer admin
        wp_register_style( $style_handle, false, [], '1.0.0' );
        wp_enqueue_style( $style_handle );
        wp_add_inline_style( $style_handle, $critical_css );
    }
}