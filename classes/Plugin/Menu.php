<?php

namespace Orwokki\PlaceReservations\Plugin;

use Orwokki\PlaceReservations\Admin\Ui;

class Menu
{
	public function addReservationsMainMenu()
	{
		if (!is_user_logged_in())
			return;

		if (current_user_can('administrator')) {
			$this->addReservationsMainMenuWithCapability('administrator');
		} else if (current_user_can(RolesAndCapabilities::CAP_SHOW_MENU_RESERVATIONS_MAIN)) {
			$this->addReservationsMainMenuWithCapability(RolesAndCapabilities::CAP_SHOW_MENU_RESERVATIONS_MAIN);
		}
	}

	/**
	 * Do the actual adding of reservations main menu. This is done this way because of idiotic functionality of WP add_menu_page capability param.
	 *
	 * @param $capability
	 */
	private function addReservationsMainMenuWithCapability($capability) {
		$ui = new Ui;
		add_menu_page(__('Reservations', 'orwokki-pr'), __('Reservations', 'orwokki-pr'), $capability, 'opr-reservations', [$ui, 'showAdminReservations'], 'dashicons-admin-site-alt3');
	}

	public function addPlacesSubMenu() {
		if (!is_user_logged_in())
			return;

		if (current_user_can('administrator')) {
			$this->addPlacesSubMenuWithCapability('administrator');
		}
		else if (current_user_can(RolesAndCapabilities::CAP_SHOW_MENU_PLACES_SUB)) {
			$this->addPlacesSubMenuWithCapability(RolesAndCapabilities::CAP_SHOW_MENU_PLACES_SUB);
		}
	}

	/**
	 * Do the actual adding of Places sub menu. This is done this way because of idiotic functionality of WP add_submenu_page capability param.
	 *
	 * @param $capability
	 */
	private function addPlacesSubMenuWithCapability($capability) {
		$ui = new Ui;
		add_submenu_page('opr-reservations', __('Places', 'orwokki-pr'), __('Places', 'orwokki-pr'), $capability, 'opr-places', [$ui, 'showAdminPlaces']);
	}

	public function addTypesAndLocationsSubMenu() {
		if (!is_user_logged_in())
			return;

		if (current_user_can('administrator')) {
			$this->addTypesAndLocationsSubMenuWithCapability('administrator');
		}
		else if (current_user_can(RolesAndCapabilities::CAP_SHOW_MENU_TYPES_AND_LOCATIONS_SUB)) {
			$this->addTypesAndLocationsSubMenuWithCapability(RolesAndCapabilities::CAP_SHOW_MENU_TYPES_AND_LOCATIONS_SUB);
		}
	}

	/**
	 * Do the actual adding of Types & locations sub menu. This is done this way because of idiotic functionality of WP add_submenu_page capability param.
	 *
	 * @param $capability
	 */
	private function addTypesAndLocationsSubMenuWithCapability($capability) {
		$ui = new Ui;
		add_submenu_page('opr-reservations', __('Types & locations', 'orwokki-pr'), __('Types & locations', 'orwokki-pr'), $capability, 'opr-types', [$ui, 'showAdminTypes']);
	}

	public function addSettingsSubMenu() {
		if (!is_user_logged_in())
			return;

		if (current_user_can('administrator')) {
			$this->addSettingsSubMenuWithCapability('administrator');
		}
		else if (current_user_can(RolesAndCapabilities::CAP_SHOW_MENU_SETTINGS_SUB)) {
			$this->addSettingsSubMenuWithCapability(RolesAndCapabilities::CAP_SHOW_MENU_SETTINGS_SUB);
		}
	}

	/**
	 * Do the actual adding of Types & locations sub menu. This is done this way because of idiotic functionality of WP add_submenu_page capability param.
	 *
	 * @param $capability
	 */
	private function addSettingsSubMenuWithCapability($capability) {
		$ui = new Ui;
		add_submenu_page('opr-reservations', __('Settings', 'orwokki-pr'), __('Settings', 'orwokki-pr'), $capability, 'opr-settings', [$ui, 'showAdminSettings']);
	}

	public function addImporterSubMenu() {
		if (!is_user_logged_in())
			return;

		if (current_user_can('administrator')) {
			$this->addImporterSubMenuWithCapability('administrator');
		}
		else if (current_user_can(RolesAndCapabilities::CAP_SHOW_MENU_IMPORTER_SUB)) {
			$this->addImporterSubMenuWithCapability(RolesAndCapabilities::CAP_SHOW_MENU_IMPORTER_SUB);
		}
	}

	/**
	 * Do the actual adding of Importer sub menu. This is done this way because of idiotic functionality of WP add_submenu_page capability param.
	 *
	 * @param $capability
	 */
	private function addImporterSubMenuWithCapability($capability) {
		$ui = new Ui;
		add_submenu_page('opr-reservations', __('Importer', 'orwokki-pr'), __('Import', 'orwokki-pr'), $capability, 'opr-importer', [$ui, 'showAdminImporter']);
	}
}