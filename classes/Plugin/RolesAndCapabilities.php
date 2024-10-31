<?php

namespace Orwokki\PlaceReservations\Plugin;

class RolesAndCapabilities
{
	const ROLE_RESERVATIONS_ADMIN = 'opr_reservations_admin';
	const ROLE_PLACES_ADMIN = 'opr_places_admin';
	const ROLE_GLOBAL_ADMIN = 'opr_global_admin';

	const CAP_SHOW_MENU_RESERVATIONS_MAIN = 'opr_cap_show_reservations_main_menu';
	const CAP_SHOW_MENU_PLACES_SUB = 'opr_cap_show_places_sub_menu';
	const CAP_SHOW_MENU_TYPES_AND_LOCATIONS_SUB = 'opr_cap_show_types_and_locations_sub_menu';
	const CAP_SHOW_MENU_SETTINGS_SUB = 'opr_cap_show_settings_sub_menu';
	const CAP_SHOW_MENU_IMPORTER_SUB = 'opr_cap_show_importer_sub_menu';

	const CAP_SHOW_RESERVATIONS = 'opr_cap_show_reservations';
	const CAP_ADD_RESERVATION = 'opr_cap_add_reservation';
	const CAP_REMOVE_RESERVATION = 'opr_cap_remove_reservation';
	const CAP_SHOW_PLACES = 'opr_cap_show_places';
	const CAP_ADD_PLACE = 'opr_cap_add_place';
	const CAP_REMOVE_PLACE = 'opr_cap_remove_place';
	const CAP_MANAGE_PLACE_CUSTOMER_VIEW_SETTINGS = 'opr_cap_manage_place_customer_view_settings';
	const CAP_MANAGE_RESERVATION_SETTINGS = 'opr_cap_manage_reservation_settings';
	const CAP_MANAGE_EMAIL_NOTIFICATION_SETTINGS = 'opr_cap_manage_email_notification_settings';
	const CAP_MANAGE_TYPES_AND_LOCATIONS = 'opr_cap_manage_types_locations';
	const CAP_IMPORTER = 'opr_cap_importer';

    public function initializeRolesAndCapabilities() {
        $this->addRoleReservationAdmin();
        $this->addRolePlacesAdmin();
        $this->addRoleGlobalAdmin();
    }

    public function removeRoles() {
    	remove_role(self::ROLE_RESERVATIONS_ADMIN);
    	remove_role(self::ROLE_PLACES_ADMIN);
    	remove_role(self::ROLE_GLOBAL_ADMIN);
	}

    private function addRoleReservationAdmin() {
        add_role(
            self::ROLE_RESERVATIONS_ADMIN,
            __('Reservations Administrator', 'orwokki-pr'),
            [
                'read'         => true,
                'edit_posts'   => false,
                'upload_files' => false,
            ]
        );

        $role = get_role(self::ROLE_RESERVATIONS_ADMIN);

        $role->add_cap(self::CAP_SHOW_MENU_RESERVATIONS_MAIN, true);
        $role->add_cap(self::CAP_SHOW_RESERVATIONS, true);
        $role->add_cap(self::CAP_ADD_RESERVATION, true);
        $role->add_cap(self::CAP_REMOVE_RESERVATION, true);
    }

    private function addRolePlacesAdmin() {
        add_role(
            self::ROLE_PLACES_ADMIN,
            __('Reservations Places Administrator', 'orwokki-pr'),
            [
                'read'         => true,
                'edit_posts'   => false,
                'upload_files' => false,
            ]
        );

        $role = get_role(self::ROLE_PLACES_ADMIN);

        $role->add_cap(self::CAP_SHOW_MENU_RESERVATIONS_MAIN, true);
        $role->add_cap(self::CAP_SHOW_MENU_PLACES_SUB, true);
		$role->add_cap(self::CAP_SHOW_MENU_SETTINGS_SUB, true);
        $role->add_cap(self::CAP_SHOW_PLACES, true);
        $role->add_cap(self::CAP_ADD_PLACE, true);
        $role->add_cap(self::CAP_REMOVE_PLACE, true);
		$role->add_cap(self::CAP_SHOW_RESERVATIONS, true);
		$role->add_cap(self::CAP_ADD_RESERVATION, true);
		$role->add_cap(self::CAP_REMOVE_RESERVATION, true);
		$role->add_cap(self::CAP_MANAGE_PLACE_CUSTOMER_VIEW_SETTINGS, true);
    }

    private function addRoleGlobalAdmin() {
        add_role(
            self::ROLE_GLOBAL_ADMIN,
            __('Reservations Global Administrator', 'orwokki-pr'),
            [
                'read'         => true,
                'edit_posts'   => false,
                'upload_files' => false,
            ]
        );

        $role = get_role(self::ROLE_GLOBAL_ADMIN);

        $role->add_cap(self::CAP_SHOW_MENU_RESERVATIONS_MAIN, true);
        $role->add_cap(self::CAP_SHOW_MENU_PLACES_SUB, true);
        $role->add_cap(self::CAP_SHOW_MENU_TYPES_AND_LOCATIONS_SUB, true);
        $role->add_cap(self::CAP_SHOW_MENU_SETTINGS_SUB, true);
        $role->add_cap(self::CAP_SHOW_MENU_IMPORTER_SUB, true);
        $role->add_cap(self::CAP_MANAGE_TYPES_AND_LOCATIONS, true);
		$role->add_cap(self::CAP_MANAGE_PLACE_CUSTOMER_VIEW_SETTINGS, true);
		$role->add_cap(self::CAP_MANAGE_RESERVATION_SETTINGS, true);
		$role->add_cap(self::CAP_MANAGE_EMAIL_NOTIFICATION_SETTINGS, true);
        $role->add_cap(self::CAP_SHOW_PLACES, true);
        $role->add_cap(self::CAP_ADD_PLACE, true);
        $role->add_cap(self::CAP_REMOVE_PLACE, true);
        $role->add_cap(self::CAP_SHOW_RESERVATIONS, true);
        $role->add_cap(self::CAP_ADD_RESERVATION, true);
        $role->add_cap(self::CAP_REMOVE_RESERVATION, true);
        $role->add_cap(self::CAP_IMPORTER, true);
    }
}