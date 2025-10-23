<?php
/**
 * Plugin Name: CC Admin Popover Row Actions
 * Plugin URI:  https://github.com/CodeCornTech/mu-cc-admin-popover-row-actions
 * Description: Converte le row-actions della WP List Table in un popover apribile dal badge ID , con flag bilaterali ( define + filter ) e asset singleton .
 * Version:     1.0.2
 * Author:      CodeCorn™
 * Author URI:  https://github.com/CodeCornTech
 * License:     MIT
 * Text Domain: cc-admin-popover-row-actions
 *
 * @package   CodeCorn\CC_Admin_Popover_Row_Actions
 */
defined( 'ABSPATH' ) || exit;

/**
 * Definisce una costante solo se non già definita .
 *
 * @param string $k  Nome della costante .
 * @param mixed  $v  Valore della costante ( qualsiasi tipo accettato da define ) .
 * @return bool      true se definita ora , false se già esiste o se define fallisce .
 */
if ( ! function_exists( 'cc_def' ) ) {
	function cc_def( $k, $v ) {
		return ! defined( $k ) && define( $k, $v );
	}
}

/** @var non-empty-string $plugin_vers */
$plugin_vers = '1.0.2';
/** @var non-empty-string $plugin_dir  Percorso assoluto directory del plugin */
$plugin_dir = __DIR__;
/** @var non-empty-string $assets_path Path relativo degli assets dentro il plugin */
$assets_path = 'codecorn/admin-popover-row-actions/assets';
/** @var non-empty-string $assets_dir  Percorso assoluto assets */
$assets_dir = "{$plugin_dir}/{$assets_path}";
/** @var non-empty-string $assets_url  URL assoluto assets */
$assets_url = plugins_url( $assets_path, __FILE__ );

cc_def( 'CC_APRA_VERS', $plugin_vers );
cc_def( 'CC_APRA_SLUG', 'cc-admin-popover-row-actions' );
cc_def( 'CC_APRA_NS', 'cc_apra' );
cc_def( 'CC_APRA_DIR', $plugin_dir );
cc_def( 'CC_APRA_ASSETS_DIR', $assets_dir );
cc_def( 'CC_APRA_ASSETS_URL', $assets_url );
cc_def( 'CC_APRA_DEFAULT_POST_TYPES', array( 'wp_ar_clients', 'wp_ar_requests' ) ); // array OK su PHP 7+.

/**
 * ===== Bilateral flags ( defaults da define , i filtri vincono ) =====
 *
 * Opzionale in wp-config.php :
 *   define( 'CC_APRA_ENABLED', true );
 *   define( 'CC_APRA_ON_HOVER', true );
 *   define( 'CC_APRA_HIDE_QUICK_EDIT', true );
 *   define( 'CC_APRA_POST_TYPES', 'wp_ar_clients,post' ); // CSV semplice
 *
 * Opzionale in wp-config.php ( JSON o array serializzato come stringa ) :
 *   define( 'CC_APRA_SELECTORS', '{"triggerLink":".my-badge","rowActions":".row-actions, .extra-actions"}' );
 *
 * Filtri disponibili :
 *   - cc_apra_enabled          : bool
 *   - cc_apra_on_hover         : bool
 *   - cc_apra_hide_quick_edit  : bool
 *   - cc_apra_post_types       : array<string>
 *   - cc_apra_selectors        : array{ table:string , primaryCell:string , triggerLink:string , rowActions:string }
 *
 * Esempi :
 *
 * add_filter( 'cc_apra_post_types', function( $pts ) {
 *     $pts[] = 'product';
 *     return array_values( array_unique( $pts ) );
 * } );
 *
 * add_filter( 'cc_apra_selectors', function( $sel, $screen ) {
 *     if ( $screen && $screen->post_type === 'product' ) {
 *         $sel['triggerLink'] = 'a.product_id_badge';
 *     }
 *     return $sel;
 * }, 10, 2 );
 */

/**
 * Converte una lista CSV in array pulito di stringhe uniche non vuote .
 *
 * @param string|array<string|int|float>|null $csv  Stringa CSV o array già fornito .
 * @return array<int,string>                     Lista normalizzata .
 */
function cc_apra_csv_list( $csv ): array {
	if ( is_array( $csv ) ) {
		return array_values( array_filter( array_map( 'strval', $csv ) ) );
	}
	$csv = (string) $csv;
	if ( $csv === '' ) {
		return array();
	}
	return array_values(
		array_unique(
			array_filter(
				array_map( 'trim', explode( ',', $csv ) )
			)
		)
	);
}

/**
 * Normalizza i selettori mantenendo solo le chiavi note e applicando i fallback .
 *
 * @param array<string,string>|string|null $val  Array associativo o JSON string .
 * @return array{ table:string , primaryCell:string , triggerLink:string , rowActions:string }
 */
function cc_apra_norm_selectors( $val ): array {
	$defaults = array(
		'table'       => '.wp-list-table.table-view-list',
		'primaryCell' => 'td.column-primary',
		'triggerLink' => 'a.wp_ar_id_td, .wp_ar-badge',
		'rowActions'  => '.row-actions',
	);

	// Supporto define come JSON string .
	if ( is_string( $val ) ) {
		$dec = json_decode( $val, true );
		if ( is_array( $dec ) ) {
			$val = $dec;
		}
	}

	if ( ! is_array( $val ) ) {
		$val = array();
	}

	$val = array_intersect_key( $val, $defaults );

	/** @var array{ table:string , primaryCell:string , triggerLink:string , rowActions:string } */
	$out = $val + $defaults;
	return $out;
}

/**
 * Recupera un flag di configurazione supportando define e filtri .
 *
 * @param 'enabled'|'on_hover'|'hide_quick_edit'|'post_types'|'selectors' $name    Nome del flag .
 * @param mixed                                                           $default Valore di default .
 * @return mixed
 *
 * @phpstan-return (
 *   $name is 'selectors' ? array{ table:string , primaryCell:string , triggerLink:string , rowActions:string } :
 *   ($name is 'post_types' ? array<int,string> : mixed)
 * )
 */
function cc_apra_get_flag( $name, $default ) {
	// leggi eventuale define
	$defined = null;
	switch ( $name ) {
		case 'enabled':
			$defined = defined( 'CC_APRA_ENABLED' ) ? (bool) CC_APRA_ENABLED : null;
			break;

		case 'on_hover':
			$defined = defined( 'CC_APRA_ON_HOVER' ) ? (bool) CC_APRA_ON_HOVER : null;
			break;

		case 'hide_quick_edit':
			$defined = defined( 'CC_APRA_HIDE_QUICK_EDIT' ) ? (bool) CC_APRA_HIDE_QUICK_EDIT : null;
			break;

		case 'post_types':
			// priorità ai define specifici , altrimenti default globale
			if ( defined( 'CC_APRA_POST_TYPES' ) ) {
				$defined = cc_apra_csv_list( CC_APRA_POST_TYPES );
			} elseif ( defined( 'CC_APRA_DEFAULT_POST_TYPES' ) ) {
				$defined = (array) constant( 'CC_APRA_DEFAULT_POST_TYPES' );
			}
			break;

		case 'selectors':
			if ( defined( 'CC_APRA_SELECTORS' ) ) {
				// accetta array o JSON string nel define
				$defined = cc_apra_norm_selectors( constant( 'CC_APRA_SELECTORS' ) );
			}
			break;
	}

	// Usa $defined se non null, altrimenti $default
	$base = $defined ?? $default;

	// mappa filtri centralizzata , i filter VINCONO sempre sui define
	$filter_map = array(
		'enabled'         => 'cc_apra_enabled',
		'on_hover'        => 'cc_apra_on_hover',
		'hide_quick_edit' => 'cc_apra_hide_quick_edit',
		'post_types'      => 'cc_apra_post_types',
		'selectors'       => 'cc_apra_selectors',
	);

	$tag = $filter_map[ $name ] ?? null;
	if ( $tag ) {
		/**
		 * Filtri di configurazione .
		 * passa anche $screen per i filtri più evoluti sui selectors
		 *
		 * @filter cc_apra_enabled          bool
		 * @filter cc_apra_on_hover         bool
		 * @filter cc_apra_hide_quick_edit  bool
		 * @filter cc_apra_post_types       array<string>
		 * @filter cc_apra_selectors        array{table:string,primaryCell:string,triggerLink:string,rowActions:string}
		 *
		 * @param mixed     $value  Valore corrente del flag .
		 * @param WP_Screen $screen Oggetto schermo corrente , utile per personalizzazioni contestuali .
		 * @return mixed
		 */
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$base   = apply_filters( $tag, $base, $screen );
	}

	// Normalizzazioni finali per sicurezza
	if ( $name === 'post_types' ) {
		$base = cc_apra_csv_list( $base ?: ( defined( 'CC_APRA_DEFAULT_POST_TYPES' ) ? constant( 'CC_APRA_DEFAULT_POST_TYPES' ) : array() ) );
	} elseif ( $name === 'selectors' ) {
		$base = cc_apra_norm_selectors( $base );
	}

	return $base;
}

/**
 * Hook di bootstrap : carica gli asset solo quando serve .
 *
 * @hooked current_screen
 *
 * @param WP_Screen $screen
 * @return void
 */
add_action(
	'current_screen',
	function ( $screen ) {
		if ( ! $screen || $screen->base !== 'edit' ) {
			return;
		}

		$enabled    = cc_apra_get_flag( 'enabled', true );
		$post_types = cc_apra_get_flag( 'post_types', constant( 'CC_APRA_DEFAULT_POST_TYPES' ) );  // default CPT tuo

		if ( ! $enabled || ! in_array( $screen->post_type, (array) $post_types, true ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', 'cc_apra_enqueue_assets', 20 );
	}
);

/**
 * Enqueue singleton .
 * Ordine : pre.js → first.js → init.js .
 * CSS critico inlined per ridurre richieste ( overridabile via filtro cc_apra_critical_css ).
 *
 * @param string $hook Hook corrente di enqueue ( non usato ) .
 * @return void
 */
function cc_apra_enqueue_assets( $hook ) {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	// Flags risolte ( filter > define ).
	/** @var bool $enabled */
	$enabled = (bool) cc_apra_get_flag( 'enabled', true );
	/** @var bool $onHover */
	$onHover = (bool) cc_apra_get_flag( 'on_hover', true );
	/** @var bool $hideQuick */
	$hideQuick = (bool) cc_apra_get_flag( 'hide_quick_edit', true );
	/** @var array<int,string> $postTypes */
	$postTypes = array_values( (array) cc_apra_get_flag( 'post_types', constant( 'CC_APRA_DEFAULT_POST_TYPES' ) ) );
	/** @var array{ table:string , primaryCell:string , triggerLink:string , rowActions:string } $selectors */
	$selectors = cc_apra_get_flag( 'selectors', cc_apra_norm_selectors( array() ) );

	// flags risolte (filter > define)
	$data = array(
		'enabled'       => $enabled,
		'onHover'       => $onHover,
		'hideQuickEdit' => $hideQuick,
		'postTypes'     => $postTypes,
		'selectors'     => $selectors,
	);

	// ===== CSS critico =====
	$critical_path = constant( 'CC_APRA_ASSETS_DIR' ) . '/css/critical.css';
	$critical_css  = file_exists( $critical_path ) ? trim( (string) file_get_contents( $critical_path ) ) : '';
	/**
	 * Permette di modificare il CSS critico iniettato inline .
	 *
	 * @param string $critical_css CSS da iniettare .
	 * @return string
	 */
	$critical_css = apply_filters( 'cc_apra_critical_css', $critical_css );

	// Registra ed inietta
	wp_register_script( 'cc-apra-pre', constant( 'CC_APRA_ASSETS_URL' ) . '/js/pre.js', array( 'jquery' ), constant( 'CC_APRA_VERS' ), true );
	wp_register_script( 'cc-apra-first', constant( 'CC_APRA_ASSETS_URL' ) . '/js/first.js', array( 'jquery', 'cc-apra-pre' ), constant( 'CC_APRA_VERS' ), true );
	wp_register_script( 'cc-apra-init', constant( 'CC_APRA_ASSETS_URL' ) . '/js/init.js', array( 'jquery', 'cc-apra-first' ), constant( 'CC_APRA_VERS' ), true );

	// Localize opzioni ai JS
	wp_localize_script( 'cc-apra-pre', 'CC_APRA', $data );

	// Enqueue
	wp_enqueue_script( 'cc-apra-pre' );
	wp_enqueue_script( 'cc-apra-first' );
	wp_enqueue_script( 'cc-apra-init' );

	// CSS inline come singleton
	if ( $critical_css ) {
		$style_handle = 'cc-apra-critical';     // handle “fittizio” : agganciamo al core per garantire stampa nel footer admin
		wp_register_style( $style_handle, false, array(), constant( 'CC_APRA_VERS' ) );
		wp_enqueue_style( $style_handle );
		wp_add_inline_style( $style_handle, $critical_css );
	}
}
