<?php

namespace Orwokki\PlaceReservations\Admin;

use Orwokki\PlaceReservations\Places\LocationDirectory;
use Orwokki\PlaceReservations\Places\Place;
use Orwokki\PlaceReservations\Places\PlaceDirectory;
use Orwokki\PlaceReservations\Places\PlaceTypeDirectory;
use Orwokki\PlaceReservations\Plugin\RolesAndCapabilities;
use Orwokki\PlaceReservations\Reservations\Messaging;
use Orwokki\PlaceReservations\Reservations\PeriodTypeDirectory;

class Ui {
	private $placeTypeDir;
	private $locationDir;
	private $periodTypeDir;
	private $placeDir;

	public function __construct() {
		$this->placeTypeDir = new PlaceTypeDirectory;
		$this->locationDir = new LocationDirectory;
		$this->periodTypeDir = new PeriodTypeDirectory;
		$this->placeDir = new PlaceDirectory;
	}

	public function showAdminReservations() {
		$adminHtml = '<div class="wrap">';
		$adminHtml .= sprintf('<h2>%s</h2>', __('Reservations', 'orwokki-pr' ));
		$adminHtml .= '<div id="messageBox"></div>';

		if (
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_SHOW_RESERVATIONS)
				|| current_user_can(RolesAndCapabilities::CAP_ADD_RESERVATION)
				|| current_user_can(RolesAndCapabilities::CAP_REMOVE_RESERVATION)
			)
		) {
			if (current_user_can('administrator') || current_user_can(RolesAndCapabilities::CAP_ADD_RESERVATION)) {
				$adminHtml .= sprintf('<h3>%s</h3>', __('Add new reservation', 'orwokki-pr'));
				$adminHtml .= $this->showAddReservationForm();
			}
			if (current_user_can('administrator') || current_user_can(RolesAndCapabilities::CAP_SHOW_RESERVATIONS) || current_user_can(RolesAndCapabilities::CAP_REMOVE_RESERVATION)) {
				$adminHtml .= sprintf('<h3>%s</h3>', __('List of reservations', 'orwokki-pr'));
				$adminHtml .= $this->showReservationsFilterForm();
				$adminHtml .= $this->showReservationsList();
			}
		}
		else {
			$adminHtml .= $this->showForbidden();
		}

		$adminHtml .= '</div>';

		echo $adminHtml;
	}

	public function showAdminPlaces() {
		$adminHtml = '<div class="wrap">';
		$adminHtml .= sprintf('<h2>%s</h2>', __('Places', 'orwokki-pr' ));
		$adminHtml .= '<div id="messageBox"></div>';
		if (
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_SHOW_PLACES)
				|| current_user_can(RolesAndCapabilities::CAP_ADD_PLACE)
				|| current_user_can(RolesAndCapabilities::CAP_REMOVE_PLACE)
			)
		) {
			if (current_user_can('administrator') || current_user_can(RolesAndCapabilities::CAP_ADD_PLACE)) {
				$adminHtml .= sprintf('<h3>%s</h3>', __('Add new place', 'orwokki-pr'));
				$adminHtml .= $this->showAddPlaceForm();
			}

			if (current_user_can('administrator') || current_user_can(RolesAndCapabilities::CAP_SHOW_PLACES) || current_user_can(RolesAndCapabilities::CAP_REMOVE_PLACE)) {
				$adminHtml .= sprintf('<h3>%s</h3>', __('List of places', 'orwokki-pr'));
				$adminHtml .= $this->showPlacesFilterForm();
				$adminHtml .= $this->showPlacesList();
			}
		}
		else {
			$adminHtml .= $this->showForbidden();
		}
		$adminHtml .= '</div>';

		echo $adminHtml;
	}

	public function showAdminTypes() {
		$adminHtml = '<div class="wrap">';
		$adminHtml .= sprintf('<h2>%s</h2>', __('Types & locations', 'orwokki-pr' ));
		$adminHtml .= '<div id="messageBox"></div>';
		if (
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_TYPES_AND_LOCATIONS)
			)
		) {
			$adminHtml .= $this->showPlaceTypes();
			$adminHtml .= $this->showLocations();
			$adminHtml .= $this->showPeriodTypes();
		}
		else {
			$adminHtml .= $this->showForbidden();
		}
		$adminHtml .= '</div>';

		echo $adminHtml;
	}

	public function showAdminSettings() {
		$adminHtml = '<div class="wrap">';
		$adminHtml .= sprintf('<h2>%s</h2>', __('Settings', 'orwokki-pr' ));
		$adminHtml .= '<div id="messageBox"></div>';
		if (
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_PLACE_CUSTOMER_VIEW_SETTINGS)
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_RESERVATION_SETTINGS)
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_EMAIL_NOTIFICATION_SETTINGS)
			)
		) {
			$adminHtml .= sprintf('<h3>%s</h3>', __('Customer view settings', 'orwokki-pr'));

			if (current_user_can('administrator') || current_user_can(RolesAndCapabilities::CAP_MANAGE_PLACE_CUSTOMER_VIEW_SETTINGS)) {
				$adminHtml .= $this->showCustomerViewSettingsForm();
			}

			if (current_user_can('administrator')
				|| (
					current_user_can(RolesAndCapabilities::CAP_MANAGE_PLACE_CUSTOMER_VIEW_SETTINGS)
					&& current_user_can(RolesAndCapabilities::CAP_MANAGE_RESERVATION_SETTINGS)
				)
			) {
				$adminHtml .= '<hr />';
			}

			if (current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_RESERVATION_SETTINGS)
			) {
				$adminHtml .= $this->showReservationSettingsForm();
			}

			if (current_user_can('administrator')
				|| (
					current_user_can(RolesAndCapabilities::CAP_MANAGE_RESERVATION_SETTINGS)
					&& current_user_can(RolesAndCapabilities::CAP_MANAGE_EMAIL_NOTIFICATION_SETTINGS)
				)
				|| (
					current_user_can(RolesAndCapabilities::CAP_MANAGE_PLACE_CUSTOMER_VIEW_SETTINGS)
					&& current_user_can(RolesAndCapabilities::CAP_MANAGE_EMAIL_NOTIFICATION_SETTINGS)
				)
			) {
				$adminHtml .= '<hr />';
			}

			if (current_user_can('administrator') || current_user_can(RolesAndCapabilities::CAP_MANAGE_EMAIL_NOTIFICATION_SETTINGS)) {
				$adminHtml .= $this->showEmailSettingsForm();
			}
		}
		else {
			$adminHtml .= $this->showForbidden();
		}
		$adminHtml .= '</div>';

		echo $adminHtml;
	}

	public function showAdminImporter() {
		$adminHtml = '<div class="wrap">';
		$adminHtml .= sprintf('<h2>%s</h2>', __('Data import', 'orwokki-pr' ));
		$adminHtml .= '<div id="messageBox"></div>';
		if (
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_IMPORTER)
			)
		) {
			$adminHtml .= sprintf('<h3>%s</h3>', __('Import reservations with their places', 'orwokki-pr'));
			$adminHtml .= sprintf('<a href="https://orwokki.com/wp/plugins/opr/assets/examples/reservations-with-places-example.csv" target="_blank">%s</a>', __('Example CSV file', 'orwokki-pr'));
			$adminHtml .= $this->showImportReservationsWithPlacesForm();

			$adminHtml .= sprintf('<h3>%s</h3>', __('Import reservations', 'orwokki-pr'));
			$adminHtml .= sprintf('<a href="https://orwokki.com/wp/plugins/opr/assets/examples/reservations-example.csv" target="_blank">%s</a>', __('Example CSV file', 'orwokki-pr'));
			$adminHtml .= $this->showImportReservationsForm();

			$adminHtml .= sprintf('<h3>%s</h3>', __('Import places', 'orwokki-pr'));
			$adminHtml .= sprintf('<a href="https://orwokki.com/wp/plugins/opr/assets/examples/places-example.csv" target="_blank">%s</a>', __('Example CSV file', 'orwokki-pr'));
			$adminHtml .= $this->showImportPlacesForm();
		}
		else {
			$adminHtml .= $this->showForbidden();
		}
		$adminHtml .= '</div>';

		echo $adminHtml;
	}

	private function showCustomerViewSettingsForm() {
		$html = '<form id="customerSideSettingsForm">';
		$html .= '<table><tbody>';

		$html .= sprintf('<tr><th>%s</th>', __('Select which fields are shown at customer view', 'orwokki-pr'));
		$html .= '<td>';

		$rel = 'settingsCustomerShowFields';

		$fieldName = __('General type', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected %s is shown at customer view', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsCustomerShowGeneralType';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Place type', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected %s is shown at customer view', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsCustomerShowPlaceType';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Location', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected %s is shown at customer view', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsCustomerShowLocation';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Period type', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected %s is shown at customer view', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsCustomerShowPeriodType';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Length', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected %s is shown at customer view', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsCustomerShowLength';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Width', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected %s is shown at customer view', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsCustomerShowWidth';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Depth', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected %s is shown at customer view', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsCustomerShowDepth';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Description', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected %s is shown at customer view', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsCustomerShowDescription';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$html .= '</td></tr>';

		$value = get_option('oprSettingsCustomerOrderByColumn') ?: 'name';
		$html .= sprintf('<tr><th><label for="settingsCustomerOrderByColumn">%s</label></th>', __('Order places by column', 'orwokki-pr'));
		$html .= sprintf('<td><select rel="settingsCustomer" id="settingsCustomerOrderByColumn" name="settingsCustomerOrderByColumn" value="%s" alt="%s" title="%s" aria-lable="%s">', $value, __('Order places by this column at customer facing side'), __('Order places by this column at customer facing side'), __('Order places by column', 'orwokki-pr'));

		$selectedPlaceTypeName = ($value == 'name') ? ' selected' : '';
		$html .= sprintf('<option value="name"'.$selectedPlaceTypeName.'>%s</option>', __('Place name', 'orwokki-pr'));

		$selectedPlaceTypeName = ($value == 'placeTypeName') ? ' selected' : '';
		$html .= sprintf('<option value="placeTypeName"'.$selectedPlaceTypeName.'>%s</option>', __('Place type', 'orwokki-pr'));

		$selectedLocationName = ($value == 'locationName') ? ' selected' : '';
		$html .= sprintf('<option value="locationName"'.$selectedLocationName.'>%s</option>', __('Location', 'orwokki-pr'));

		$selectedPeriodTypeName = ($value == 'periodTypeName') ? ' selected' : '';
		$html .= sprintf('<option value="periodTypeName"'.$selectedPeriodTypeName.'>%s</option>', __('Period type', 'orwokki-pr'));

		$selectedLength = ($value == 'length') ? ' selected' : '';
		$html .= sprintf('<option value="length"'.$selectedLength.'>%s</option>', __('Length', 'orwokki-pr'));

		$selectedWidth = ($value == 'length') ? ' selected' : '';
		$html .= sprintf('<option value="width"'.$selectedWidth.'>%s</option>', __('Width', 'orwokki-pr'));

		$selectedDepth = ($value == 'depth') ? ' selected' : '';
		$html .= sprintf('<option value="depth" '.$selectedDepth.'>%s</option>', __('Depth', 'orwokki-pr'));

		$html .= '</select></td></tr>';

		$html .= '</tbody></table>';

		$html .= sprintf('<p><input type="button" class="button button-primary" id="settingsCustomerViewSaveButton" name="settingsCustomerViewSaveButton" value="%s" aria-label="%s" /></p>', __('Save', 'orwokki-pr'), __('Save', 'orwokki-pr'));
		$html .= '</form>';

		return $html;
	}

	private function showReservationSettingsForm() {
		$html = sprintf('<h3>%s</h3>', __('Reservation settings', 'orwokki-pr'));

		$html .= '<form id="reservationSettingsForm">';
		$html .= '<table><tbody>';

		$html .= sprintf('<tr><th>%s</th>', __('Select reservation fields which are mandatory to fill', 'orwokki-pr'));
		$html .= '<td>';

		$rel = 'settingsReservationMandatoryFields';

		$fieldName = __('Place', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected it is mandatory to fill field %s when making reservation', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsReservationMandatoryPlace';
		$selected = ' checked';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' readonly="readonly" disabled="disabled" /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Period type', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected it is mandatory to fill field %s when making reservation', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsReservationMandatoryPeriodType';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Name', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected it is mandatory to fill field %s when making reservation', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsReservationMandatoryName';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Email', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected it is mandatory to fill field %s when making reservation', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsReservationMandatoryEmail';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Phone number', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected it is mandatory to fill field %s when making reservation', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsReservationMandatoryPhoneNumber';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Reservation start time', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected it is mandatory to fill field %s when making reservation', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsReservationMandatoryPeriodStartTime';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Reservation end time', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected it is mandatory to fill field %s when making reservation', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsReservationMandatoryPeriodEndTime';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$fieldName = __('Additional information', 'orwokki-pr');
		$alt = $title = sprintf(__('When selected it is mandatory to fill field %s when making reservation', 'orwokki-pr'), $fieldName);
		$inputFieldId = $inputFieldName = 'settingsReservationMandatoryAdditionalInfo';
		$selected = (get_option('opr'.ucfirst($inputFieldId)) ?? 1) ? ' checked' : '';
		$html .= sprintf(' <input type="checkbox" rel="%s" class="cb" id="%s" id="%s" value="1" alt="%s" title="%s" aria-label="%s"'.$selected.' /><label for="%s" alt="%s" title="%s">%s</label>', $rel, $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldName, $alt, $title, $fieldName);

		$html .= '</td></tr>';

		$html .= '</tbody></table>';

		$html .= sprintf('<p><input type="button" class="button button-primary" id="settingsReservationSaveButton" name="settingsReservationSaveButton" value="%s" aria-label="%s" /></p>', __('Save', 'orwokki-pr'), __('Save', 'orwokki-pr'));
		$html .= '</form>';

		return $html;
	}

	private function showEmailSettingsForm() {
		$html = sprintf('<h3>%s</h3>', __('General email settings', 'orwokki-pr'));

		$html .= '<form id="emailGeneralSettingsForm">';
		$html .= '<table><tbody>';

		$fieldName = __('Admin notification receiver email', 'orwokki-pr');
		$alt = $title = __('Email address where notifications are sent', 'orwokki-pr');
		$inputFieldId = $inputFieldName = 'settingsEmailAdminReceiverEmail';
		$inputFieldValue = get_option('opr'.ucfirst($inputFieldId)) ?: '';
		$html .= sprintf('<tr><th><label for="%s">%s</label></th>', $inputFieldId, $fieldName);
		$html .= sprintf('<td><input type="text" rel="emailGeneralSettings" id="%s" name="%s" alt="%s" title="%s" aria-label="%s" value="%s" /></td></tr>', $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldValue);

		$html .= '</tbody></table>';
		$html .= sprintf('<p><input type="button" class="button button-primary" id="settingsEmailSenderSaveButton" value="%s" aria-label="%s" /></p>', __('Save', 'orwokki-pr'), __('Save', 'orwokki-pr'));
		$html .= '</form>';

		$html .= '<hr />';

		$html .= sprintf('<h3>%s</h3>', __('Reservation email content for customer', 'orwokki-pr'));

		$html .= '<p>';
		$html .= __('Following data replacements can be used in the subject and content of the email. Data replacements are going to be replaced with actual values when sending the email. Remember to include the "{" and "}" characters.', 'orwokki-pr');

		$html .= '<ul>';
		$tagsAndFields = (new Messaging)->getValueReplacementTagsAndFields();
		foreach ($tagsAndFields as $key => $data) {
			$html .= sprintf('<li>{%s} &mdash; %s</li>', $key, $data['translation']);
		}
		$html .= '</p>';

		$html .= '<form id="emailCustomerContentSettingsForm">';
		$html .= '<table><tbody>';
		$fieldName = __('Email subject', 'orwokki-pr');
		$alt = $title = __('Subject of the email sent to customer about the reservation', 'orwokki-pr');
		$inputFieldId = $inputFieldName = 'settingsEmailCustomerSubject';
		$inputFieldValue = get_option('opr'.ucfirst($inputFieldId)) ?: '';
		$html .= sprintf('<tr><th><label for="%s">%s</label></th>', $inputFieldId, $fieldName);
		$html .= sprintf('<td><input type="text" class="field-email-subject" rel="emailCustomerContentSettings" id="%s" name="%s" alt="%s" title="%s" aria-label="%s" value="%s" /></td></tr>', $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldValue);

		$fieldName = __('Email content', 'orwokki-pr');
		$alt = $title = __('Content of the email sent to customer about the reservation', 'orwokki-pr');
		$inputFieldId = $inputFieldName = 'settingsEmailCustomerContent';
		$inputFieldValue = get_option('opr'.ucfirst($inputFieldId)) ?: '';
		$html .= sprintf('<tr><th><label for="%s">%s</label></th>', $inputFieldId, $fieldName);
		$html .= sprintf('<td><textarea class="field-email-content" rel="emailCustomerContentSettings" id="%s" name="%s" alt="%s" title="%s" aria-label="%s" value="%s">%s</textarea></td></tr>', $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldValue, $inputFieldValue);

		$html .= '</tbody></table>';

		$html .= sprintf('<p><input type="button" class="button button-primary" id="settingsCustomerEmailContentSaveButton" value="%s" aria-label="%s" /></p>', __('Save', 'orwokki-pr'), __('Save', 'orwokki-pr'));
		$html .= '</form>';

		$html .= '<hr />';

		$html .= sprintf('<h3>%s</h3>', __('Reservation email content for administration', 'orwokki-pr'));

		$html .= '<p>';
		$html .= __('Following data replacements can be used in the subject and content of the email. Data replacements are going to be replaced with actual values when sending the email. Remember to include the "{" and "}" characters.', 'orwokki-pr');

		$html .= '<ul>';
		$tagsAndFields = (new Messaging)->getValueReplacementTagsAndFields();
		foreach ($tagsAndFields as $key => $data) {
			$html .= sprintf('<li>{%s} &mdash; %s</li>', $key, $data['translation']);
		}
		$html .= '</p>';

		$html .= '<form id="emailAdminContentSettingsForm">';
		$html .= '<table><tbody>';
		$fieldName = __('Email subject', 'orwokki-pr');
		$alt = $title = __('Subject of the email sent to administration about the reservation', 'orwokki-pr');
		$inputFieldId = $inputFieldName = 'settingsEmailAdminSubject';
		$inputFieldValue = get_option('opr'.ucfirst($inputFieldId)) ?: '';
		$html .= sprintf('<tr><th><label for="%s">%s</label></th>', $inputFieldId, $fieldName);
		$html .= sprintf('<td><input type="text" class="field-email-subject" rel="emailAdminContentSettings" id="%s" name="%s" alt="%s" title="%s" aria-label="%s" value="%s" /></td></tr>', $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldValue);

		$fieldName = __('Email content', 'orwokki-pr');
		$alt = $title = __('Content of the email sent to administration about the reservation', 'orwokki-pr');
		$inputFieldId = $inputFieldName = 'settingsEmailAdminContent';
		$inputFieldValue = get_option('opr'.ucfirst($inputFieldId)) ?: '';
		$html .= sprintf('<tr><th><label for="%s">%s</label></th>', $inputFieldId, $fieldName);
		$html .= sprintf('<td><textarea class="field-email-content" rel="emailAdminContentSettings" id="%s" name="%s" alt="%s" title="%s" aria-label="%s" value="%s">%s</textarea></td></tr>', $inputFieldId, $inputFieldName, $alt, $title, $fieldName, $inputFieldValue, $inputFieldValue);

		$html .= '</tbody></table>';

		$html .= sprintf('<p><input type="button" class="button button-primary" id="settingsAdminEmailContentSaveButton" value="%s" aria-label="%s" /></p>', __('Save', 'orwokki-pr'), __('Save', 'orwokki-pr'));
		$html .= '</form>';

		return $html;
	}

	private function showAddReservationForm() {
		$html = '<form id="addNewReservationForm">';
		$html .= '<table><tbody>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-placeId">%s</label></th>', __('Place', 'orwokki-pr'));
		$html .= sprintf('<td><select rel="addNewReservation" id="addNewReservation-placeId" name="addNewReservation-placeId" placeholder="%s" aria-placeholder="%s" required>', __('Place', 'orwokki-pr'), __('Place', 'orwokki-pr'));
		$html .= '<option></option>';
		foreach ($this->placeDir->listActiveFreePlacesArray() as $placeData) {
			$html .= sprintf('<option value="%d" data-periodTypeId="%s">%s</option>', $placeData['id'], $placeData['periodTypeId'], $placeData['name']);
		}
		$html .= '</select></td>';
		$html .= '</tr>';

		$required = (get_option('oprSettingsReservationMandatoryPeriodType')) ? ' required' : '';
		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-periodTypeId">%s</label></th>', __('Period type', 'orwokki-pr'));
		$html .= sprintf('<td><select rel="addNewReservation" id="addNewReservation-periodTypeId" name="addNewReservation-periodTypeId"placeholder="%s" aria-placeholder="%s"'.$required.'>', __('Period type', 'orwokki-pr'), __('Period type', 'orwokki-pr'));
		$html .= '<option></option>';
		foreach ($this->periodTypeDir->listActivePeriodTypes() as $periodType) {
			$html .= sprintf('<option value="%d">%s</option>', $periodType['id'], $periodType['name']);
		}
		$html .= '</select></td>';
		$html .= '</tr>';

		$required = (get_option('oprSettingsReservationMandatoryName')) ? ' required' : '';
		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-name">%s</label></th>', __('Name', 'orwokki-pr'));
		$html .= sprintf('<td><input type="text" rel="addNewReservation" id="addNewReservation-name" name="addNewReservation-name" placeholder="%s" aria-placeholder="%s"'.$required.' /></td>', __('Name', 'orwokki-pr'), __('Name', 'orwokki-pr'));
		$html .= '</tr>';

		$required = (get_option('oprSettingsReservationMandatoryEmail')) ? ' required' : '';
		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-email">%s</label></th>', __('Email', 'orwokki-pr'));
		$html .= sprintf('<td><input type="text" rel="addNewReservation" id="addNewReservation-email" name="addNewReservation-email" placeholder="%s" aria-placeholder="%s"'.$required.' /></td>', __('Email', 'orwokki-pr'), __('Email', 'orwokki-pr'));
		$html .= '</tr>';

		$required = (get_option('oprSettingsReservationMandatoryPhoneNumber')) ? ' required' : '';
		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-phoneNumber">%s</label></th>', __('Phone number', 'orwokki-pr'));
		$html .= sprintf('<td><input type="text" rel="addNewReservation" id="addNewReservation-phoneNumber" name="addNewReservation-phoneNumber" placeholder="%s" aria-placeholder="%s"%s /></td>', __('Phone number', 'orwokki-pr'), __('Phone number', 'orwokki-pr'), $required);
		$html .= '</tr>';

		$required = (get_option('oprSettingsReservationMandatoryPeriodStartTime')) ? ' required' : '';
		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-periodStartTime">%s</label></th>', __('Reservation start time', 'orwokki-pr'));
		$html .= sprintf('<td><input type="date" rel="addNewReservation" id="addNewReservation-periodStartTime" name="addNewReservation-periodStartTime" placeholder="%s" aria-placeholder="%s"%s /></td>', __('Reservation start time', 'orwokki-pr'), __('Reservation start time', 'orwokki-pr'), $required);
		$html .= '</tr>';

		$required = (get_option('oprSettingsReservationMandatoryPeriodEndTime')) ? ' required' : '';
		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-periodEndTime">%s</label></th>', __('Reservation end time', 'orwokki-pr'));
		$html .= sprintf('<td><input type="date" rel="addNewReservation" id="addNewReservation-periodEndTime" name="addNewReservation-periodEndTime" placeholder="%s" aria-placeholder="%s"%s /></td>', __('Reservation end time', 'orwokki-pr'), __('Reservation end time', 'orwokki-pr'), $required);
		$html .= '</tr>';

		$required = (get_option('oprSettingsReservationMandatoryAdditionalInfo')) ? ' required' : '';
		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-additionalInfo">%s</label></th>', __('Additional information', 'orwokki-pr'));
		$html .= '<td><textarea rel="addNewReservation" id="addNewReservation-additionalInfo" name="addNewReservation-additionalInfo"'.$required.'></textarea>';
		$html .= '</tr>';

		$html .= '</tbody></table>';
		$html .= sprintf('<p><input type="button" class="button button-primary" id="addNewReservation-action-add" value="%s" aria-label="%s" /></p>', __('Add', 'orwokki-pr'), __('Add', 'orwokki-pr'));
		$html .= '</form>';

		return $html;
	}

	private function showReservationsList() {
		$html = '<table class="wp-list-table widefat alignfull striped" id="reservationsListTable">';
		$html .= '<thead><tr>';
		$html .= sprintf('<th class="manage-column">%s</th>', __('Place', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Period type', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Name', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Email', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Phone number', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Reservation start time', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Reservation end time', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Additional information', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Actions', 'orwokki-pr'));
		$html .= '</tr></thead>';
		$html .= '<tbody id="reservationsListContainer"></tbody>';
		$html .= '</table>';

		return $html;
	}

	private function showAddPlaceForm() {
		$html = '<form id="addNewPlaceForm">';
		$html .= '<table><tbody>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewPlace-generalType">%s</label></th>', __('General type', 'orwokki-pr'));
		$html .= sprintf('<td><select rel="addNewPlace" id="addNewPlace-generalType" name="addNewPlace-generalType" placeholder="%s" aria-placeholder="%s">', __('General type', 'orwokki-pr'), __('General type', 'orwokki-pr'));
		$html .= sprintf('<option value="%s">%s</option>', Place::PLACE_GENERAL_TYPE_MARINA, __(Place::PLACE_GENERAL_TYPE_MARINA, 'orwokki-pr'));
		$html .= sprintf('<option value="%s">%s</option>', Place::PLACE_GENERAL_TYPE_PARKING, __(Place::PLACE_GENERAL_TYPE_PARKING, 'orwokki-pr'));
		$html .= '</select></td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewPlace-placeTypeId">%s</label></th>', __('Place type', 'orwokki-pr'));
		$html .= sprintf('<td><select rel="addNewPlace" id="addNewPlace-placeTypeId" name="addNewPlace-placeTypeId" placeholder="%s" aria-placeholder="%s">', __('Place type', 'orwokki-pr'), __('Place type', 'orwokki-pr'));
		$html .= '<option></option>';
		foreach ($this->placeTypeDir->listActivePlaceTypes() as $placeType) {
			$html .= sprintf('<option value="%d">%s</option>', $placeType['id'], $placeType['name']);
		}
		$html .= '</select></td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewPlace-locationId">%s</label></th>', __('Location', 'orwokki-pr'));
		$html .= sprintf('<td><select rel="addNewPlace" id="addNewPlace-locationId" name="addNewPlace-locationId" placeholder="%s" aria-placeholder="%s">', __('Location', 'orwokki-pr'), __('Location', 'orwokki-pr'));
		$html .= '<option></option>';
		foreach ($this->locationDir->listActiveLocations() as $location) {
			$html .= sprintf('<option value="%d">%s</option>', $location['id'], $location['name']);
		}
		$html .= '</select></td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewPlace-periodTypeId">%s</label></th>', __('Period type', 'orwokki-pr'));
		$html .= sprintf('<td><select rel="addNewPlace" id="addNewPlace-periodTypeId" name="addNewPlace-periodTypeId"placeholder="%s" aria-placeholder="%s">', __('Period type', 'orwokki-pr'), __('Period type', 'orwokki-pr'));
		$html .= '<option></option>';
		foreach ($this->periodTypeDir->listActivePeriodTypes() as $periodType) {
			$html .= sprintf('<option value="%d">%s</option>', $periodType['id'], $periodType['name']);
		}
		$html .= '</select></td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewPlace-name">%s</label></th>', __('Place name', 'orwokki-pr'));
		$html .= sprintf('<td><input type="text" rel="addNewPlace" id="addNewPlace-name" name="addNewPlace-name" placeholder="%s" aria-placeholder="%s" /></td>', __('Name of the place', 'orwokki-pr'), __('Name of the place', 'orwokki-pr'));
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewPlace-length">%s</label></th>', __('Length', 'orwokki-pr'));
		$html .= sprintf('<td><input type="number" step="0.01" rel="addNewPlace" id="addNewPlace-length" name="addNewPlace-length" placeholder="%s" aria-placeholder="%s" /></td>', __('Length', 'orwokki-pr'), __('Length', 'orwokki-pr'));
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewPlace-width">%s</label></th>', __('Width', 'orwokki-pr'));
		$html .= sprintf('<td><input type="number" step="0.01" rel="addNewPlace" id="addNewPlace-width" name="addNewPlace-width" placeholder="%s" aria-placeholder="%s" /></td>', __('Width', 'orwokki-pr'), __('Width', 'orwokki-pr'));
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewPlace-depth">%s</label></th>', __('Depth', 'orwokki-pr'));
		$html .= sprintf('<td><input type="number" step="0.01" rel="addNewPlace" id="addNewPlace-depth" name="addNewPlace-depth" placeholder="%s" aria-placeholder="%s" /></td>', __('Depth', 'orwokki-pr'), __('Depth', 'orwokki-pr'));
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewPlace-description">%s</label></th>', __('Description', 'orwokki-pr'));
		$html .= '<td><textarea rel="addNewPlace" id="addNewPlace-description" name="addNewPlace-description"></textarea>';
		$html .= '</tr>';

		$html .= '</tbody></table>';
		$html .= sprintf('<p><input type="button" class="button button-primary" id="addNewPlace-action-add" value="%s" aria-label="%s" /></p>', __('Add', 'orwokki-pr'), __('Add', 'orwokki-pr'));
		$html .= '</form>';

		return $html;
	}

	private function showPlacesFilterForm() {
		$html = '<table id="placesFilterFormTable">';
		$html .= '<tbody><tr>';

		$html .= sprintf('<td><input type="text" rel="placesFilter" id="placesFilter-name" placeholder="%s" aria-placeholder="%s" /></td>', __('Name', 'orwokki-pr'), __('Name', 'orwokki-pr'));

		$html .= '<td>';
		$html .= $this->showGeneralTypeField('placesFilter-generalType', 'placesFilter');
		$html .= '</td>';
		$html .= sprintf('<td><select rel="placesFilter" id="placesFilter-placeTypeId" placeholder="%s" aria-placeholder="%s">', __('Place type', 'orwokki-pr'), __('Place type', 'orwokki-pr'));
		$html .= sprintf('<option>%s</option>', __('Place type', 'orwokki-pr'));
		foreach ($this->placeTypeDir->listActivePlaceTypes() as $placeType) {
			$html .= sprintf('<option value="%d">%s</option>', $placeType['id'], $placeType['name']);
		}
		$html .= '</select></td>';

		$html .= sprintf('<td><select rel="placesFilter" id="placesFilter-locationId" placeholder="%s" aria-placeholder="%s">', __('Location', 'orwokki-pr'), __('Location', 'orwokki-pr'));
		$html .= sprintf('<option>%s</option>', __('Location', 'orwokki-pr'));
		foreach ($this->locationDir->listActiveLocations() as $location) {
			$html .= sprintf('<option value="%d">%s</option>', $location['id'], $location['name']);
		}
		$html .= '</select></td>';

		$html .= sprintf('<td><input type="number" step="0.1" rel="placesFilter" id="placesFilter-length" placeholder="%s" aria-placeholder="%s" /></td>', __('Length', 'orwokki-pr'), __('Length', 'orwokki-pr'));

		$html .= sprintf('<td><input type="number" step="0.1" rel="placesFilter" id="placesFilter-width" placeholder="%s" aria-placeholder="%s" /></td>', __('Width', 'orwokki-pr'), __('Width', 'orwokki-pr'));

		$html .= sprintf('<td><input type="number" step="0.1" rel="placesFilter" id="placesFilter-depth" placeholder="%s" aria-placeholder="%s" /></td>', __('Depth', 'orwokki-pr'), __('Depth', 'orwokki-pr'));

		$html .= sprintf('<td><input type="button" class="button button-primary" id="placesFilter-action-filter" value="%s" aria-label="%s" /></td>', __('Filter', 'orwokki-pr'), __('Filter', 'orwokki-pr'));

		$html .= '</tr></tbody>';
		$html .= '</table>';

		return $html;
	}

	private function showReservationsFilterForm() {
		$html = '<table id="reservationsFilterFormTable">';
		$html .= '<tbody><tr>';

		$html .= sprintf('<td><input type="text" rel="reservationsFilter" id="reservationsFilter-placeName" placeholder="%s" aria-placeholder="%s" /></td>', __('Place', 'orwokki-pr'), __('Place', 'orwokki-pr'));

		$html .= sprintf('<td><input type="text" rel="reservationsFilter" id="reservationsFilter-name" placeholder="%s" aria-placeholder="%s" /></td>', __('Name', 'orwokki-pr'), __('Name', 'orwokki-pr'));

		$html .= sprintf('<td><input type="text" rel="reservationsFilter" id="reservationsFilter-email" placeholder="%s" aria-placeholder="%s" /></td>', __('Email', 'orwokki-pr'), __('Email', 'orwokki-pr'));

		$html .= sprintf('<td><input type="text" rel="reservationsFilter" id="reservationsFilter-phoneNumber" placeholder="%s" aria-placeholder="%s" /></td>', __('Phone number', 'orwokki-pr'), __('Phone number', 'orwokki-pr'));

		$html .= sprintf('<td><select rel="reservationsFilter" id="reservationsFilter-placeTypeId" placeholder="%s" aria-placeholder="%s">', __('Place type', 'orwokki-pr'), __('Place type', 'orwokki-pr'));
		$html .= sprintf('<option>%s</option>', __('Place type', 'orwokki-pr'));
		foreach ($this->placeTypeDir->listActivePlaceTypes() as $placeType) {
			$html .= sprintf('<option value="%d">%s</option>', $placeType['id'], $placeType['name']);
		}
		$html .= '</select></td>';

		$html .= sprintf('<td><select rel="reservationsFilter" id="reservationsFilter-locationId" placeholder="%s" aria-placeholder="%s">', __('Location', 'orwokki-pr'), __('Location', 'orwokki-pr'));
		$html .= sprintf('<option>%s</option>', __('Location', 'orwokki-pr'));
		foreach ($this->locationDir->listActiveLocations() as $location) {
			$html .= sprintf('<option value="%d">%s</option>', $location['id'], $location['name']);
		}
		$html .= '</select></td>';

		$html .= sprintf('<td><select rel="reservationsFilter" id="reservationsFilter-periodTypeId" placeholder="%s" aria-placeholder="%s">', __('Period type', 'orwokki-pr'), __('Period type', 'orwokki-pr'));
		$html .= sprintf('<option>%s</option>', __('Period type', 'orwokki-pr'));
		foreach ($this->periodTypeDir->listActivePeriodTypes() as $periodType) {
			$html .= sprintf('<option value="%d">%s</option>', $periodType['id'], $periodType['name']);
		}
		$html .= '</select></td>';

		$html .= sprintf('<td><input type="button" class="button button-primary" id="reservationsFilter-action-filter" value="%s" aria-label="%s" /></td>', __('Filter', 'orwokki-pr'), __('Filter', 'orwokki-pr'));

		$html .= '</tr></tbody>';
		$html .= '</table>';

		return $html;
	}

	private function showPlacesList() {
		$html = '<table class="wp-list-table widefat fixed striped" id="placesListTable">';
		$html .= '<thead><tr>';
		$html .= sprintf('<th class="manage-column">%s</th>', __('Name', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('General type', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Place type', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Location', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Period type', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Length', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Width', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Depth', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Description', 'orwokki-pr'));
		$html .= sprintf('<th class="manage-column">%s</th>', __('Actions', 'orwokki-pr'));
		$html .= '</tr></thead>';
		$html .= '<tbody id="placesListContainer"></tbody>';
		$html .= '</table>';

		return $html;
	}

	private function showPlaceTypes() {
		$html = sprintf('<h3>%s</h3>', __('Place types', 'orwokki-pr'));
		$html .= '<table>';
		$html .= '<tbody id="placeTypesList"></tbody>';
		$html .= '<tbody id="newPlaceType">';
		$html .= '<tr>';
		$html .= '<td>';
		$html .= $this->showGeneralTypeField('placeTypeGeneralType-new', 'placeType-new', false);
		$html .= '</td>';
		$html .= sprintf('<td><p><input type="text" id="placeTypeName-new" rel="placeType-new" alt="%s" title="%s" placeholder="%s" /></p></td>', __("Name of the place-type", 'orwokki-pr'), __("Name of the place-type", 'orwokki-pr'), __("Name of the place-type", 'orwokki-pr'));
		$html .= sprintf('<td><p><select id="placeTypeActive-new" rel="placeType-new" alt="%s" title="%s"><option value="1">%s</option><option value="0">%s</option></select></p></td>', __("Activity", 'orwokki-pr'), __("Activity", 'orwokki-pr'), __("Active", 'orwokki-pr'), __("Not in use", 'orwokki-pr'));
		$html .= sprintf('<td><p><input type="button" id="placeTypeSubmitButton-new" rel="placeType-new" class="button button-primary placeType-submitButton" value="%s" /></p></td>', __("Add place type", 'orwokki-pr'));
		$html .= '<td class="opr-spinner-column"><div id="spinner-placeType-new"></div></td>';
		$html .= '</tr>';
		$html .= '</tbody>';
		$html .= '</table>';

		return $html;
	}

	private function showLocations() {
		$html = sprintf('<h3>%s</h3>', __('Locations', 'orwokki-pr'));
		$html .= '<table>';
		$html .= '<tbody id="locationsList"></tbody>';
		$html .= '<tbody id="newLocation">';
		$html .= '<tr>';
		$html .= '<td>';
		$html .= $this->showGeneralTypeField('locationGeneralType-new', 'location-new', false);
		$html .= '</td>';
		$html .= sprintf('<td><p><input type="text" id="locationName-new" rel="location-new" alt="%s" title="%s" placeholder="%s" /></p></td>', __("Name of the location", 'orwokki-pr'), __("Name of the location", 'orwokki-pr'), __("Name of the location", 'orwokki-pr'));
		$html .= sprintf('<td><p><select id="locationActive-new" rel="location-new" alt="%s" title="%s"><option value="1">%s</option><option value="0">%s</option></select></p></td>', __("Activity", 'orwokki-pr'), __("Activity", 'orwokki-pr'), __("Active", 'orwokki-pr'), __("Not in use", 'orwokki-pr'));
		$html .= sprintf('<td><p><input type="button" id="locationSubmitButton-new" rel="location-new" class="button button-primary location-submitButton" value="%s" /></p></td>', __("Add location", 'orwokki-pr'));
		$html .= '<td class="opr-spinner-column"><div id="spinner-location-new"></div></td>';
		$html .= '</tr>';
		$html .= '</tbody>';
		$html .= '</table>';

		return $html;
	}

	private function showPeriodTypes() {
		$html = sprintf('<h3>%s</h3>', __('Period types', 'orwokki-pr'));
		$html .= '<table>';
		$html .= '<tbody id="periodTypesList"></tbody>';
		$html .= '<tbody id="newPeriodType">';
		$html .= '<tr>';
		$html .= sprintf('<td><p><input type="text" id="periodTypeName-new" rel="periodType-new" alt="%s" title="%s" placeholder="%s" /></p></td>', __("Name of the period type", 'orwokki-pr'), __("Name of the period type", 'orwokki-pr'), __("Name of the period type", 'orwokki-pr'));
		$html .= sprintf('<td><p><select id="periodTypeActive-new" rel="periodType-new" alt="%s" title="%s"><option value="1">%s</option><option value="0">%s</option></select></p></td>', __("Activity", 'orwokki-pr'), __("Activity", 'orwokki-pr'), __("Active", 'orwokki-pr'), __("Not in use", 'orwokki-pr'));
		$html .= sprintf('<td><p><input type="button" id="periodTypeSubmitButton-new" rel="periodType-new" class="button button-primary periodType-submitButton" value="%s" /></p></td>', __("Add period type", 'orwokki-pr'));
		$html .= '<td class="opr-spinner-column"><div id="spinner-periodType-new"></div></td>';
		$html .= '</tr>';
		$html .= '</tbody>';
		$html .= '</table>';

		return $html;
	}

	private function showGeneralTypeField($id, $rel = null, $showEmptyOptionPlaceHolder = true) {
		if ($rel)
			$rel = ' rel="'.$rel.'"';

		$html = sprintf('<select id="%s"%s placeholder="%s" aria-placeholder="%s">', $id, $rel, __('General type', 'orwokki-pr'), __('General type', 'orwokki-pr'));

		if ($showEmptyOptionPlaceHolder)
			$html .= sprintf('<option>%s</option>', __('General type', 'orwokki-pr'));

		$html .= sprintf('<option value="%s">%s</option>', Place::PLACE_GENERAL_TYPE_MARINA, __(Place::PLACE_GENERAL_TYPE_MARINA, 'orwokki-pr'));
		$html .= sprintf('<option value="%s">%s</option>', Place::PLACE_GENERAL_TYPE_PARKING, __(Place::PLACE_GENERAL_TYPE_PARKING, 'orwokki-pr'));
		$html .= '</select>';

		return $html;
	}

	private function showImportReservationsWithPlacesForm() {
		$html = '<form method="post" id="importReservationsWithPlacesForm" name="importReservationsWithPlacesForm"><table><tbody>';

		$inputFieldType = 'file';
		$inputFieldId = $inputFieldName = 'reservationsWithPlacesCsv';
		$alt = $title = __('CSV file that contains reservations and their places to be imported into the system', 'orwokki-pr');
		$label = __('CSV import file', 'orwokki-pr');
		$html .= sprintf('<tr><th><label for="%s" alt="%s" title="%s">%s</label></th>', $inputFieldName, $alt, $title, $label);
		$html .= sprintf('<td><input type="%s" id="%s" name="%s" aria-label="%s" title="%s" alt="%s" /></td></tr>', $inputFieldType, $inputFieldId, $inputFieldName, $label, $title, $alt);

		$inputFieldType = 'checkbox';
		$inputFieldId = $inputFieldName = 'rwpRemoveExistingReservationsFromPlace';
		$alt = $title = __('If imported place already exists and has existing reservations remove the reservations', 'orwokki-pr');
		$label = __('Remove existing reservation from place', 'orwokki-pr');
		$html .= sprintf('<tr><th><label for="%s" alt="%s" title="%s">%s</label></th>', $inputFieldName, $alt, $title, $label);
		$html .= sprintf('<td><input type="%s" id="%s" name="%s" aria-label="%s" title="%s" alt="%s" /></td></tr>', $inputFieldType, $inputFieldId, $inputFieldName, $label, $title, $alt);

		$inputFieldType = 'checkbox';
		$inputFieldId = $inputFieldName = 'rwpCreateMissingData';
		$alt = $title = __('If imported place has types and locations defined that does not exist create them', 'orwokki-pr');
		$label = __('Create types and locations', 'orwokki-pr');
		$html .= sprintf('<tr><th><label for="%s" alt="%s" title="%s">%s</label></th>', $inputFieldName, $alt, $title, $label);
		$html .= sprintf('<td><input type="%s" id="%s" name="%s" aria-label="%s" title="%s" alt="%s" /></td></tr>', $inputFieldType, $inputFieldId, $inputFieldName, $label, $title, $alt);

		$inputFieldType = 'checkbox';
		$inputFieldId = $inputFieldName = 'rwpReplaceExisting';
		$alt = $title = __('Change data of existing places found by place name', 'orwokki-pr');
		$label = __('Change data of existing places', 'orwokki-pr');
		$html .= sprintf('<tr><th><label for="%s" alt="%s" title="%s">%s</label></th>', $inputFieldName, $alt, $title, $label);
		$html .= sprintf('<td><input type="%s" id="%s" name="%s" aria-label="%s" title="%s" alt="%s" /></td></tr>', $inputFieldType, $inputFieldId, $inputFieldName, $label, $title, $alt);

		$html .= '</tbody></table>';
		$html .= sprintf('<p><input type="button" class="button button-primary" id="importReservationsWithPlacesButton" name="importReservationsWithPlacesButton" value="%s" aria-label="%s" title="%s" alt="%s"/><div id="importReservationsWithPlacesSpinner" class="spinner" style="float:none;"></div></p>', __('Import', 'orwokki-pr'), __('Import', 'orwokki-pr'), __('Import the contents of the selected file', 'orwokki-pr'), __('Import the contents of the selected file', 'orwokki-pr'));
		$html .= '</form>';

		return $html;
	}

	private function showImportReservationsForm() {
		$html = '<form method="post" id="importReservationsForm" name="importReservationsForm"><table><tbody>';

		$inputFieldType = 'file';
		$inputFieldId = $inputFieldName = 'reservationsCsv';
		$alt = $title = __('CSV file that contains reservations to be imported into the system', 'orwokki-pr');
		$label = __('CSV import file', 'orwokki-pr');
		$html .= sprintf('<tr><th><label for="%s" alt="%s" title="%s">%s</label></th>', $inputFieldName, $alt, $title, $label);
		$html .= sprintf('<td><input type="%s" id="%s" name="%s" aria-label="%s" title="%s" alt="%s" /></td></tr>', $inputFieldType, $inputFieldId, $inputFieldName, $label, $title, $alt);

		$inputFieldType = 'checkbox';
		$inputFieldId = $inputFieldName = 'resRemoveExistingReservationsFromPlace';
		$alt = $title = __('If duplicate reservation is found remove the existing reservation', 'orwokki-pr');
		$label = __('Remove existing reservation', 'orwokki-pr');
		$html .= sprintf('<tr><th><label for="%s" alt="%s" title="%s">%s</label></th>', $inputFieldName, $alt, $title, $label);
		$html .= sprintf('<td><input type="%s" id="%s" name="%s" aria-label="%s" title="%s" alt="%s" /></td></tr>', $inputFieldType, $inputFieldId, $inputFieldName, $label, $title, $alt);

		$html .= '</tbody></table>';
		$html .= sprintf('<p><input type="button" class="button button-primary" id="importReservationsButton" name="importReservationsButton" value="%s" aria-label="%s" title="%s" alt="%s"/><div id="importReservationsSpinner" class="spinner" style="float:none;"></div></p>', __('Import', 'orwokki-pr'), __('Import', 'orwokki-pr'), __('Import the contents of the selected file', 'orwokki-pr'), __('Import the contents of the selected file', 'orwokki-pr'));
		$html .= '</form>';

		return $html;
	}

	private function showImportPlacesForm() {
		$html = '<form method="post" id="importPlacesForm" name="importPlacesForm"><table><tbody>';

		$inputFieldType = 'file';
		$inputFieldId = $inputFieldName = 'placesCsv';
		$alt = $title = __('CSV file that contains places to be imported into the system', 'orwokki-pr');
		$label = __('CSV import file', 'orwokki-pr');
		$html .= sprintf('<tr><th><label for="%s" alt="%s" title="%s">%s</label></th>', $inputFieldName, $alt, $title, $label);
		$html .= sprintf('<td><input type="%s" id="%s" name="%s" aria-label="%s" title="%s" alt="%s" /></td></tr>', $inputFieldType, $inputFieldId, $inputFieldName, $label, $title, $alt);

		$inputFieldType = 'checkbox';
		$inputFieldId = $inputFieldName = 'plcRemoveExistingReservationsFromPlace';
		$alt = $title = __('If imported place already exists and has existing reservations remove the reservations, this is needed to change the place in case reservations were found', 'orwokki-pr');
		$label = __('Remove existing reservation from place to be changed', 'orwokki-pr');
		$html .= sprintf('<tr><th><label for="%s" alt="%s" title="%s">%s</label></th>', $inputFieldName, $alt, $title, $label);
		$html .= sprintf('<td><input type="%s" id="%s" name="%s" aria-label="%s" title="%s" alt="%s" /></td></tr>', $inputFieldType, $inputFieldId, $inputFieldName, $label, $title, $alt);

		$inputFieldType = 'checkbox';
		$inputFieldId = $inputFieldName = 'plcCreateMissingData';
		$alt = $title = __('If imported place has types and locations defined that does not exist then create them', 'orwokki-pr');
		$label = __('Create types and locations', 'orwokki-pr');
		$html .= sprintf('<tr><th><label for="%s" alt="%s" title="%s">%s</label></th>', $inputFieldName, $alt, $title, $label);
		$html .= sprintf('<td><input type="%s" id="%s" name="%s" aria-label="%s" title="%s" alt="%s" /></td></tr>', $inputFieldType, $inputFieldId, $inputFieldName, $label, $title, $alt);

		$inputFieldType = 'checkbox';
		$inputFieldId = $inputFieldName = 'plcReplaceExisting';
		$alt = $title = __('Change data of existing places found by place name', 'orwokki-pr');
		$label = __('Change data of existing places', 'orwokki-pr');
		$html .= sprintf('<tr><th><label for="%s" alt="%s" title="%s">%s</label></th>', $inputFieldName, $alt, $title, $label);
		$html .= sprintf('<td><input type="%s" id="%s" name="%s" aria-label="%s" title="%s" alt="%s" /></td></tr>', $inputFieldType, $inputFieldId, $inputFieldName, $label, $title, $alt);

		$html .= '</tbody></table>';
		$html .= sprintf('<p><input type="button" class="button button-primary" id="importPlacesButton" name="importPlacesButton" value="%s" aria-label="%s" title="%s" alt="%s"/><div id="importPlacesSpinner" class="spinner" style="float:none;"></div></p>', __('Import', 'orwokki-pr'), __('Import', 'orwokki-pr'), __('Import the contents of the selected file', 'orwokki-pr'), __('Import the contents of the selected file', 'orwokki-pr'));
		$html .= '</form>';

		return $html;
	}

	private function showForbidden() {
		$html = sprintf('<div class="error"><p><strong>%s</strong></p></div>', __('Insufficient permissions.', 'orwokki-pr'));

		return $html;
	}
}