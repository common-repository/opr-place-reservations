<?php
/**
 * Plugin Name:       OPR Place Reservations
 * Plugin URI:        https://wordpress.org/plugins/opr-place-reservations/
 * Description:       Plugin to handle and show places to rent and their reservations
 * Version:           1.1.14
 * Requires at least: 5.8
 * Requires PHP:      8.0
 * Author:            Orwokki <info@orwokki.com>
 * Author URI:        https://orwokki.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       orwokki-pr
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (!function_exists('wp_handle_upload')) {
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
}
if (!function_exists('dbDelta')) {
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
}

// Import classes
require_once plugin_dir_path(__FILE__).'classes/Places/LocationDirectory.php';
require_once plugin_dir_path(__FILE__).'classes/Places/PlaceTypeDirectory.php';
require_once plugin_dir_path(__FILE__).'classes/Reservations/PeriodTypeDirectory.php';
require_once plugin_dir_path(__FILE__).'classes/Places/Place.php';
require_once plugin_dir_path(__FILE__).'classes/Places/PlaceDirectory.php';
require_once plugin_dir_path(__FILE__).'classes/Reservations/Reservation.php';
require_once plugin_dir_path(__FILE__).'classes/Reservations/ReservationDirectory.php';
require_once plugin_dir_path(__FILE__).'classes/Reservations/Messaging.php';
require_once plugin_dir_path(__FILE__).'classes/Importer/FileImporter.php';
require_once plugin_dir_path(__FILE__).'classes/Importer/ReservationsWithPlacesImporter.php';
require_once plugin_dir_path(__FILE__).'classes/Importer/ReservationsImporter.php';
require_once plugin_dir_path(__FILE__).'classes/Importer/PlacesImporter.php';
require_once plugin_dir_path(__FILE__).'classes/Admin/Ui.php';
require_once plugin_dir_path(__FILE__).'classes/Admin/Api.php';
require_once plugin_dir_path(__FILE__).'classes/Plugin/Activation.php';
require_once plugin_dir_path(__FILE__).'classes/Plugin/RolesAndCapabilities.php';
require_once plugin_dir_path(__FILE__).'classes/Plugin/Menu.php';
require_once plugin_dir_path(__FILE__).'classes/Site/Ui.php';
require_once plugin_dir_path(__FILE__).'classes/Site/Api.php';

use Orwokki\PlaceReservations\Plugin\Activation;
use Orwokki\PlaceReservations\Plugin\RolesAndCapabilities;
use Orwokki\PlaceReservations\Plugin\Menu;
use Orwokki\PlaceReservations\Admin\Api as AdminApi;
use Orwokki\PlaceReservations\Site\Ui as SiteUi;
use Orwokki\PlaceReservations\Site\Api as SiteApi;

$adminApi = new AdminApi;
add_action('wp_ajax_opr_list_admin_translations', [$adminApi, 'listAdminTranslations']);
add_action('wp_ajax_opr_add_reservation', [$adminApi, 'addReservation']);
add_action('wp_ajax_opr_remove_reservation', [$adminApi, 'removeReservation']);
add_action('wp_ajax_opr_list_active_reservations', [$adminApi, 'listActiveReservations']);
add_action('wp_ajax_opr_list_all_places', [$adminApi, 'listAllPlaces']);
add_action('wp_ajax_opr_add_place', [$adminApi, 'addPlace']);
add_action('wp_ajax_opr_remove_place', [$adminApi, 'removePlace']);
add_action('wp_ajax_opr_list_place_types', [$adminApi, 'listPlaceTypes']);
add_action('wp_ajax_opr_add_place_type', [$adminApi, 'addPlaceType']);
add_action('wp_ajax_opr_save_place_type', [$adminApi, 'savePlaceType']);
add_action('wp_ajax_opr_list_locations', [$adminApi, 'listLocations']);
add_action('wp_ajax_opr_add_location', [$adminApi, 'addLocation']);
add_action('wp_ajax_opr_save_location', [$adminApi, 'saveLocation']);
add_action('wp_ajax_opr_list_period_types', [$adminApi, 'listPeriodTypes']);
add_action('wp_ajax_opr_add_period_type', [$adminApi, 'addPeriodType']);
add_action('wp_ajax_opr_save_period_type', [$adminApi, 'savePeriodType']);
add_action('wp_ajax_opr_save_customer_view_settings', [$adminApi, 'saveCustomerViewSettings']);
add_action('wp_ajax_opr_save_reservation_settings', [$adminApi, 'saveReservationSettings']);
add_action('wp_ajax_opr_save_email_general_settings', [$adminApi, 'saveEmailGeneralSettings']);
add_action('wp_ajax_opr_save_customer_email_content_settings', [$adminApi, 'saveCustomerEmailContentSettings']);
add_action('wp_ajax_opr_save_admin_email_content_settings', [$adminApi, 'saveAdminEmailContentSettings']);
add_action('wp_ajax_opr_import_csv_reservations_with_places', [$adminApi, 'importCsvReservationsWithPlaces']);
add_action('wp_ajax_opr_import_csv_reservations', [$adminApi, 'importCsvReservations']);
add_action('wp_ajax_opr_import_csv_places', [$adminApi, 'importCsvPlaces']);

$siteApi = new SiteApi;
add_action('wp_ajax_opr_list_available_places', [$siteApi, 'listAvailablePlaces']);
add_action('wp_ajax_nopriv_opr_list_available_places', [$siteApi, 'listAvailablePlaces']);
add_action('wp_ajax_nopriv_opr_add_reservation', [$siteApi, 'addReservation']);

$rolesAndCapabilities = new RolesAndCapabilities;
add_action('init', [$rolesAndCapabilities, 'initializeRolesAndCapabilities']);

function oprAdminActions() {
	$menu = new Menu;

	$menu->addReservationsMainMenu();
	$menu->addPlacesSubMenu();
	$menu->addTypesAndLocationsSubMenu();
	$menu->addSettingsSubMenu();
	$menu->addImporterSubMenu();
}
add_action("admin_menu", "oprAdminActions");

$activation = new Activation;
register_activation_hook(__FILE__, [$activation, 'doActivation']);
register_deactivation_hook(__FILE__, [$activation, 'doDeactivation']);

function oprSetAdminScriptsAndStyles($hook) {
	if (
		is_user_logged_in() &&
		(
			current_user_can('administrator')
			|| current_user_can(RolesAndCapabilities::ROLE_RESERVATIONS_ADMIN)
			|| current_user_can(RolesAndCapabilities::ROLE_PLACES_ADMIN)
			|| current_user_can(RolesAndCapabilities::ROLE_GLOBAL_ADMIN)
		)
	) {

		wp_register_style('opr-admin-general-styles', plugins_url('/css/admin/opr-admin.css', __FILE__));
		wp_enqueue_style('opr-admin-general-styles');

		wp_register_script('opr-admin-general-functions-script', plugins_url('/js/admin/opr-admin-general-functions.js', __FILE__));
		wp_enqueue_script('opr-admin-general-functions-script');
		wp_register_script('opr-admin-reservations-script', plugins_url('/js/admin/opr-admin-reservations.js', __FILE__));
		wp_enqueue_script('opr-admin-reservations-script');
		wp_register_script('opr-admin-places-script', plugins_url('/js/admin/opr-admin-places.js', __FILE__));
		wp_enqueue_script('opr-admin-places-script');
		wp_register_script('opr-admin-types-script', plugins_url('/js/admin/opr-admin-types.js', __FILE__));
		wp_enqueue_script('opr-admin-types-script');
		wp_register_script('opr-admin-settings-script', plugins_url('/js/admin/opr-admin-settings.js', __FILE__));
		wp_enqueue_script('opr-admin-settings-script');
		wp_register_script('opr-admin-importer-script', plugins_url('/js/admin/opr-admin-importer.js', __FILE__));
		wp_enqueue_script('opr-admin-importer-script');
		wp_register_script('opr-admin-domready-script', plugins_url('/js/admin/opr-admin-domready.js', __FILE__));
		wp_enqueue_script('opr-admin-domready-script');
	}
}
add_action("admin_enqueue_scripts", "oprSetAdminScriptsAndStyles");

function oprSetSiteScriptsAndStyles() {
	wp_register_style('opr-ui-styles', plugins_url('/css/opr-ui.css', __FILE__));
	wp_enqueue_style('opr-ui-styles');

	wp_register_script('opr-general-functions', plugins_url('/js/opr-general-functions.js', __FILE__));
	wp_enqueue_script('opr-general-functions');
	wp_register_script('opr-places', plugins_url('/js/opr-places.js', __FILE__));
	wp_enqueue_script('opr-places');
	wp_register_script('opr-domready', plugins_url('/js/opr-domready.js', __FILE__));
	wp_enqueue_script('opr-domready');
}
add_action("wp_enqueue_scripts", "oprSetSiteScriptsAndStyles");

function oprShowAvailablePlacesShortCode($attributes = [], $content = null) {
	$ui = new SiteUi;

	if ($attributes['filtering'] == 'true')
		$content .= $ui->showPlacesFilterForm();

	$content .= $ui->showPlacesList();
	$content .= $ui->showAddReservationForm();

	return $content;
}

function oprShortCodesInit() {
	add_shortcode('show-available-places', 'oprShowAvailablePlacesShortCode');
}
add_action('init', 'oprShortCodesInit');

function oprLoadTextDomain() {
	load_plugin_textdomain('orwokki-pr', false, basename(dirname(__FILE__)).'/languages/');
}
add_action('plugins_loaded', 'oprLoadTextDomain');
