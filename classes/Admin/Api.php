<?php

namespace Orwokki\PlaceReservations\Admin;

use Exception;
use Orwokki\PlaceReservations\Importer\PlacesImporter;
use Orwokki\PlaceReservations\Importer\ReservationsImporter;
use Orwokki\PlaceReservations\Importer\ReservationsWithPlacesImporter;
use Orwokki\PlaceReservations\Places\LocationDirectory;
use Orwokki\PlaceReservations\Places\Place;
use Orwokki\PlaceReservations\Places\PlaceDirectory;
use Orwokki\PlaceReservations\Places\PlaceTypeDirectory;
use Orwokki\PlaceReservations\Plugin\RolesAndCapabilities;
use Orwokki\PlaceReservations\Reservations\Messaging;
use Orwokki\PlaceReservations\Reservations\PeriodTypeDirectory;
use Orwokki\PlaceReservations\Reservations\Reservation;
use Orwokki\PlaceReservations\Reservations\ReservationDirectory;

class Api {
	private $placeDirectory;
	private $reservationDirectory;
	private $placeTypeDirectory;
	private $locationDirectory;
	private $periodTypeDirectory;
	private $messaging;

	public function __construct() {
		$this->placeDirectory = new PlaceDirectory;
		$this->reservationDirectory = new ReservationDirectory;
		$this->placeTypeDirectory = new PlaceTypeDirectory;
		$this->locationDirectory = new LocationDirectory;
		$this->periodTypeDirectory = new PeriodTypeDirectory;
		$this->messaging = new Messaging;
	}

	public function listActiveReservations() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_SHOW_RESERVATIONS)
				|| current_user_can(RolesAndCapabilities::CAP_REMOVE_RESERVATION)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$reservations = $this->reservationDirectory->listReservationsFilteredArray($this->parseReservationsFilterCriterias($_POST));

		wp_send_json(['success' => true, 'shouldNotify' => false, 'messageType' => 'none', 'message' => '', 'data' => ['reservations' => $reservations, 'reservationFieldTranslations' => $this->getReservationFieldTranslations()]]);
		wp_die();
	}

	private function parseReservationsFilterCriterias($data) {
		$data['action'] = null;
		unset($data['action']);

		$hasCriteriasSet = false;
		foreach ($data as $key => $value) {
			switch ($key) {
				case 'locationId':
				case 'placeTypeId':
				case 'periodTypeId':
					$data[$key] = (int) $value;
					if ($data[$key] > 0)
						$hasCriteriasSet = true;
					break;
				case 'placeName':
				case 'name':
				case 'email':
				case 'phoneNumber':
				case 'shownReservationsIds':
					if ($value)
						$hasCriteriasSet = true;
					break;
			}
		}

		if (!$hasCriteriasSet)
			return [];

		$data['active'] = 1;

		return $data;
	}

	public function addReservation() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_ADD_RESERVATION)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$this->reservationDirectory->checkReservationMandatoryFields();

		$reservationData = [
			'active' => 1,
			'approved' => 0,
			'placeId' => (int) $_POST['placeId'] ?? 0,
			'periodTypeId' => (int) $_POST['periodTypeId'] ?? 0,
			'name' => sanitize_text_field($_POST['name'] ?? ''),
			'email' => sanitize_email($_POST['email'] ?? ''),
			'phoneNumber' => sanitize_text_field($_POST['phoneNumber'] ?? ''),
			'periodStartTime' => sanitize_text_field($_POST['periodStartTime'] ?? ''),
			'periodEndTime' => sanitize_text_field($_POST['periodEndTime'] ?? ''),
			'additionalInfo' => sanitize_textarea_field($_POST['additionalInfo'] ?? ''),
		];

		if (strlen($reservationData['name']) > 255) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Value of name field is too long. Expected maximum of 255 characters, got %d characters.', 'orwokki-pr'), strlen($reservationData['name']))]);
			wp_die();
		}

		if (strlen($reservationData['email']) > 255) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Value of email field is too long. Expected maximum of 255 characters, got %d characters.', 'orwokki-pr'), strlen($reservationData['email']))]);
			wp_die();
		}

		if (strlen($reservationData['email']) > 255) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Value of phone number field is too long. Expected maximum of 255 characters, got %d characters.', 'orwokki-pr'), strlen($reservationData['phoneNumber']))]);
			wp_die();
		}

		try {
			$reservation = new Reservation($reservationData);
		}
		catch (Exception $e) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed to create new reservation. '.$e->getMessage(), 'orwokki-pr')]);
			wp_die();
		}

		try {
			if (!$this->reservationDirectory->store($reservation)) {
				wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed storing new reservation.', 'orwokki-pr')]);
				wp_die();
			}
		}
		catch (Exception $e) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed storing new reservation.', 'orwokki-pr')]);
			wp_die();
		}

		$this->messaging->sendReservationAddedCustomerMessage($reservation);

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('New reservation created successfully.', 'orwokki-pr'), 'data' => []]);
		wp_die();
	}

	public function removeReservation() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_REMOVE_RESERVATION)
				|| current_user_can(RolesAndCapabilities::CAP_SHOW_RESERVATIONS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$id = (int) $_POST['reservationId'] ?? 0;
		try {
			$reservationDir = new ReservationDirectory();
			$reservationDir->activateDataImport();
			$reservationDir->removeById($id);
		}
		catch (Exception $e) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed to remove reservation: ', 'orwokki-pr').$id.'. '.$e->getMessage()]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Reservation removed successfully.', 'orwokki-pr'), 'data' => []]);
		wp_die();
	}

	private function getReservationFieldTranslations() {
		return [
			'removeButton' => [
				'caption' => __("Remove", 'orwokki-pr'),
			],
			'showMoreButton' => [
				'caption' => __("Show more reservations", 'orwokki-pr'),
			],
		];
	}

	private function parsePlaceFilterCriterias($data) {
		$data['action'] = null;
		unset($data['action']);

		$hasCriteriasSet = false;
		foreach ($data as $key => $value) {
			switch ($key) {
				case 'generalType':
					if (!in_array($value, [Place::PLACE_GENERAL_TYPE_MARINA, Place::PLACE_GENERAL_TYPE_PARKING]))
						$data[$key] = null;
					else
						$hasCriteriasSet = true;
					break;
				case 'locationId':
				case 'placeTypeId':
				case 'periodTypeId':
					$data[$key] = (int) $value;
					if ($data[$key] > 0)
						$hasCriteriasSet = true;
					break;
				case 'name':
				case 'legth':
				case 'depth':
				case 'width':
				case 'shownPlacesIds':
					if ($value)
						$hasCriteriasSet = true;
					break;
			}
		}

		if (!$hasCriteriasSet)
			return [];

		$data['active'] = 1;

		return $data;
	}

	public function listAllPlaces() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_SHOW_PLACES)
				|| current_user_can(RolesAndCapabilities::CAP_REMOVE_PLACE)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$places = $this->placeDirectory->listPlacesFilteredArray($this->parsePlaceFilterCriterias($_POST));

		wp_send_json(['success' => true, 'shouldNotify' => false, 'messageType' => 'none', 'message' => '', 'data' => ['places' => $places, 'placeFieldTranslations' => $this->getPlaceFieldTranslations()]]);
		wp_die();
	}

	public function addPlace() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_ADD_PLACE)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$placeData = [
			'active' => 1,
			'generalType' => sanitize_text_field($_POST['generalType'] ?? ''),
			'placeTypeId' => (int) $_POST['placeTypeId'] ?? 0,
			'locationId' => (int) $_POST['locationId'] ?? 0,
			'periodTypeId' => (int) $_POST['periodTypeId'] ?? 0,
			'name' => sanitize_text_field($_POST['name'] ?? ''),
			'length' => (float) str_replace(',', '.', $_POST['length'] ?? '') ?: 0.0,
			'width' => (float) str_replace(',', '.', $_POST['width'] ?? '') ?: 0.0,
			'depth' => (float) str_replace(',', '.', $_POST['depth'] ?? '') ?: 0.0,
			'description' => sanitize_textarea_field($_POST['description'] ?? ''),
		];

		if (strlen($placeData['name']) > 255) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Value of name field is too long. Expected maximum of 255 characters, got %d characters.', 'orwokki-pr'), strlen($placeData['name']))]);
			wp_die();
		}

		try {
			$place = new Place($placeData);
		}
		catch (Exception $e) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed to create new place. '.$e->getMessage(), 'orwokki-pr')]);
			wp_die();
		}

		if (!$this->placeDirectory->store($place)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed storing new place.', 'orwokki-pr')]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('New place created successfully.', 'orwokki-pr'), 'data' => []]);
		wp_die();
	}

	public function removePlace() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_REMOVE_PLACE)
				|| current_user_can(RolesAndCapabilities::CAP_SHOW_PLACES)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$id = (int) $_POST['placeId'] ?? 0;
		try {
			$this->placeDirectory->removeById($id);
		}
		catch (Exception $e) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed to remove place: ', 'orwokki-pr').$id.'. '.$e->getMessage()]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Place removed successfully.', 'orwokki-pr'), 'data' => []]);
		wp_die();
	}

	private function getPlaceFieldTranslations() {
		return [
			'removeButton' => [
				'caption' => __("Remove", 'orwokki-pr'),
			],
			'showMoreButton' => [
				'caption' => __("Show more places", 'orwokki-pr'),
			]
		];
	}

	public function listPlaceTypes() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_TYPES_AND_LOCATIONS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => false, 'messageType' => 'none', 'message' => '', 'data' => ['placeTypes' => $this->placeTypeDirectory->listAllPlaceTypes(), 'placeTypeFieldTranslations' => $this->getPlaceTypeFieldTranslations()]]);
		wp_die();
	}

	public function addPlaceType() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_TYPES_AND_LOCATIONS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$generalType = sanitize_text_field($_POST['placeTypeGeneralType']);
		$name = sanitize_text_field($_POST['placeTypeName']);
		$active = sanitize_text_field($_POST['placeTypeActive']);

		if (strlen($name) > 255) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Value of name field is too long. Expected maximum of 255 characters, got %d characters.', 'orwokki-pr'), strlen($name))]);
			wp_die();
		}

		if (!$this->placeTypeDirectory->add($generalType, $name, $active)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed to add new place-type.', 'orwokki-pr')]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('New place-type added succesfully', 'orwokki-pr'), 'data' => ['placeTypes' => $this->placeTypeDirectory->listAllPlaceTypes(), 'placeTypeFieldTranslations' => $this->getPlaceTypeFieldTranslations()]]);
		wp_die();
	}

	public function savePlaceType() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_TYPES_AND_LOCATIONS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$id = (int) $_POST['id'];
		$generalType = sanitize_text_field($_POST['placeTypeGeneralType']);
		$name = sanitize_text_field($_POST['placeTypeName']);
		$active = sanitize_text_field($_POST['placeTypeActive']);

		if (strlen($name) > 255) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Value of name field is too long. Expected maximum of 255 characters, got %d characters.', 'orwokki-pr'), strlen($name))]);
			wp_die();
		}

		$this->placeTypeDirectory->change($id, $generalType, $name, $active);

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Changed place-type succesfully', 'orwokki-pr'), 'data' => ['placeTypes' => $this->placeTypeDirectory->listAllPlaceTypes(), 'placeTypeFieldTranslations' => $this->getPlaceTypeFieldTranslations()]]);
		wp_die();
	}

	private function getPlaceTypeFieldTranslations() {
		return [
			'placeTypeGeneralType' => [
				'alt' => __("General type", 'orwokki-pr'),
				'title' => __("General type", 'orwokki-pr'),
				'placeholder' => __("General type", 'orwokki-pr'),
				'options' => [
					'Marina' => __("Marina", 'orwokki-pr'),
					'Parking' => __("Parking", 'orwokki-pr'),
				]
			],
			'placeTypeName' => [
				'alt' => __("Name of the place-type", 'orwokki-pr'),
				'title' => __("Name of the place-type", 'orwokki-pr'),
				'placeholder' => __("Name of the place-type", 'orwokki-pr'),
			],
			'placeTypeActive' => [
				'alt' => __("Activity", 'orwokki-pr'),
				'title' => __("Activity", 'orwokki-pr'),
				'placeholder' => __("Activity", 'orwokki-pr'),
				'options' => [
					'active' => __("Active", 'orwokki-pr'),
					'inactive' => __("Not in use", 'orwokki-pr'),
				]
			],
			'submitButton' => [
				'caption' => __("Save", 'orwokki-pr'),
			]
		];
	}

	public function listLocations() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_TYPES_AND_LOCATIONS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => false, 'messageType' => 'none', 'message' => '', 'data' => ['locations' => $this->locationDirectory->listAllLocations(), 'locationFieldTranslations' => $this->getLocationFieldTranslations()]]);
		wp_die();
	}

	public function addLocation() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_TYPES_AND_LOCATIONS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$generalType = sanitize_text_field($_POST['locationGeneralType']);
		$name = sanitize_text_field($_POST['locationName']);
		$active = sanitize_text_field($_POST['locationActive']);

		if (strlen($name) > 255) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Value of name field is too long. Expected maximum of 255 characters, got %d characters.', 'orwokki-pr'), strlen($name))]);
			wp_die();
		}

		if (!$this->locationDirectory->add($generalType, $name, $active)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed to add new location.', 'orwokki-pr')]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('New location added successfully', 'orwokki-pr'), 'data' => ['locations' => $this->locationDirectory->listAllLocations(), 'locationFieldTranslations' => $this->getLocationFieldTranslations()]]);
		wp_die();
	}

	public function saveLocation() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_TYPES_AND_LOCATIONS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$id = (int) $_POST['id'];
		$generalType = sanitize_text_field($_POST['locationGeneralType']);
		$name = sanitize_text_field($_POST['locationName']);
		$active = sanitize_text_field($_POST['locationActive']);

		$this->locationDirectory->change($id, $generalType, $name, $active);

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Changed location succesfully', 'orwokki-pr'), 'data' => ['locations' => $this->locationDirectory->listAllLocations(), 'locationFieldTranslations' => $this->getLocationFieldTranslations()]]);
		wp_die();
	}

	private function getLocationFieldTranslations() {
		return [
			'locationGeneralType' => [
				'alt' => __("General type", 'orwokki-pr'),
				'title' => __("General type", 'orwokki-pr'),
				'placeholder' => __("General type", 'orwokki-pr'),
				'options' => [
					'Marina' => __("Marina", 'orwokki-pr'),
					'Parking' => __("Parking", 'orwokki-pr'),
				]
			],
			'locationName' => [
				'alt' => __("Name of the location", 'orwokki-pr'),
				'title' => __("Name of the location", 'orwokki-pr'),
				'placeholder' => __("Name of the location", 'orwokki-pr'),
			],
			'locationActive' => [
				'alt' => __("Activity", 'orwokki-pr'),
				'title' => __("Activity", 'orwokki-pr'),
				'placeholder' => __("Activity", 'orwokki-pr'),
				'options' => [
					'active' => __("Active", 'orwokki-pr'),
					'inactive' => __("Not in use", 'orwokki-pr'),
				]
			],
			'submitButton' => [
				'caption' => __("Save", 'orwokki-pr'),
			]
		];
	}

	public function listPeriodTypes() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_TYPES_AND_LOCATIONS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => false, 'messageType' => 'none', 'message' => '', 'data' => ['periodTypes' => $this->periodTypeDirectory->listAllPeriodTypes(), 'periodTypeFieldTranslations' => $this->getPeriodTypeFieldTranslations()]]);
		wp_die();
	}

	public function addPeriodType() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_TYPES_AND_LOCATIONS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$name = sanitize_text_field($_POST['periodTypeName']);
		$active = sanitize_text_field($_POST['periodTypeActive']);

		if (strlen($name) > 255) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Value of name field is too long. Expected maximum of 255 characters, got %d characters.', 'orwokki-pr'), strlen($name))]);
			wp_die();
		}

		if (!$this->periodTypeDirectory->add($name, $active)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed to add new period type.', 'orwokki-pr')]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('New period type added successfully', 'orwokki-pr'), 'data' => ['periodTypes' => $this->periodTypeDirectory->listAllPeriodTypes(), 'periodTypeFieldTranslations' => $this->getPeriodTypeFieldTranslations()]]);
		wp_die();
	}

	public function savePeriodType() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_TYPES_AND_LOCATIONS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$id = (int) sanitize_text_field($_POST['id']);
		$name = sanitize_text_field($_POST['periodTypeName']);
		$active = sanitize_text_field($_POST['periodTypeActive']);

		if (strlen($name) > 255) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Value of name field is too long. Expected maximum of 255 characters, got %d characters.', 'orwokki-pr'), strlen($name))]);
			wp_die();
		}

		$this->periodTypeDirectory->change($id, $name, $active);

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Changed period type successfully', 'orwokki-pr'), 'data' => ['periodTypes' => $this->periodTypeDirectory->listAllPeriodTypes(), 'periodTypeFieldTranslations' => $this->getPeriodTypeFieldTranslations()]]);
		wp_die();
	}

	private function getPeriodTypeFieldTranslations() {
		return [
			'periodTypeName' => [
				'alt' => __("Name of the period type", 'orwokki-pr'),
				'title' => __("Name of the period type", 'orwokki-pr'),
				'placeholder' => __("Name of the period type", 'orwokki-pr'),
			],
			'periodTypeActive' => [
				'alt' => __("Activity", 'orwokki-pr'),
				'title' => __("Activity", 'orwokki-pr'),
				'placeholder' => __("Activity", 'orwokki-pr'),
				'options' => [
					'active' => __("Active", 'orwokki-pr'),
					'inactive' => __("Not in use", 'orwokki-pr'),
				]
			],
			'submitButton' => [
				'caption' => __("Save", 'orwokki-pr'),
			]
		];
	}

	public function saveCustomerViewSettings() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_PLACE_CUSTOMER_VIEW_SETTINGS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$settingsCustomerShowGeneralType = (int) $_POST['settingsCustomerShowGeneralType'] ?? 0;
		$settingsCustomerShowPlaceType = (int) $_POST['settingsCustomerShowPlaceType'] ?? 0;
		$settingsCustomerShowLocation = (int) $_POST['settingsCustomerShowLocation'] ?? 0;
		$settingsCustomerShowPeriodType = (int) $_POST['settingsCustomerShowPeriodType'] ?? 0;
		$settingsCustomerShowLength = (int) $_POST['settingsCustomerShowLength'] ?? 0;
		$settingsCustomerShowWidth = (int) $_POST['settingsCustomerShowWidth'] ?? 0;
		$settingsCustomerShowDepth = (int) $_POST['settingsCustomerShowDepth'] ?? 0;
		$settingsCustomerShowDescription = (int) $_POST['settingsCustomerShowDescription'] ?? 0;

		$settingsCustomerOrderByColumn = sanitize_text_field($_POST['settingsCustomerOrderByColumn']);
		if (!in_array($settingsCustomerOrderByColumn, ['name', 'placeTypeName', 'locationName', 'periodTypeName', 'length', 'width', 'depth'])) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Invalid value for "Order places by column".', 'orwokki-pr')]);
			wp_die();
		}

		update_option('oprSettingsCustomerShowGeneralType', $settingsCustomerShowGeneralType);
		update_option('oprSettingsCustomerShowPlaceType', $settingsCustomerShowPlaceType);
		update_option('oprSettingsCustomerShowLocation', $settingsCustomerShowLocation);
		update_option('oprSettingsCustomerShowPeriodType', $settingsCustomerShowPeriodType);
		update_option('oprSettingsCustomerShowLength', $settingsCustomerShowLength);
		update_option('oprSettingsCustomerShowWidth', $settingsCustomerShowWidth);
		update_option('oprSettingsCustomerShowDepth', $settingsCustomerShowDepth);
		update_option('oprSettingsCustomerShowDescription', $settingsCustomerShowDescription);

		update_option('oprSettingsCustomerOrderByColumn', $settingsCustomerOrderByColumn);

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Customer view settings saved successfully', 'orwokki-pr'), 'data' => []]);
		wp_die();
	}

	public function saveReservationSettings() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_RESERVATION_SETTINGS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$settingsReservationMandatoryPeriodType = (int) $_POST['settingsReservationMandatoryPeriodType'] ?? 0;
		$settingsReservationMandatoryName = (int) $_POST['settingsReservationMandatoryName'] ?? 0;
		$settingsReservationMandatoryEmail = (int) $_POST['settingsReservationMandatoryEmail'] ?? 0;
		$settingsReservationMandatoryPhoneNumber = (int) $_POST['settingsReservationMandatoryPhoneNumber'] ?? 0;
		$settingsReservationMandatoryPeriodStartTime = (int) $_POST['settingsReservationMandatoryPeriodStartTime'] ?? 0;
		$settingsReservationMandatoryPeriodEndTime = (int) $_POST['settingsReservationMandatoryPeriodEndTime'] ?? 0;
		$settingsReservationMandatoryAdditionalInfo = (int) $_POST['settingsReservationMandatoryAdditionalInfo'] ?? 0;

		update_option('oprSettingsReservationMandatoryPeriodType', $settingsReservationMandatoryPeriodType);
		update_option('oprSettingsReservationMandatoryName', $settingsReservationMandatoryName);
		update_option('oprSettingsReservationMandatoryEmail', $settingsReservationMandatoryEmail);
		update_option('oprSettingsReservationMandatoryPhoneNumber', $settingsReservationMandatoryPhoneNumber);
		update_option('oprSettingsReservationMandatoryPeriodStartTime', $settingsReservationMandatoryPeriodStartTime);
		update_option('oprSettingsReservationMandatoryPeriodEndTime', $settingsReservationMandatoryPeriodEndTime);
		update_option('oprSettingsReservationMandatoryAdditionalInfo', $settingsReservationMandatoryAdditionalInfo);

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Reservation settings saved successfully', 'orwokki-pr'), 'data' => []]);
		wp_die();
	}

	public function saveEmailGeneralSettings() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_EMAIL_NOTIFICATION_SETTINGS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$settingsEmailAdminReceiverEmail = sanitize_email($_POST['settingsEmailAdminReceiverEmail']);

		update_option('oprSettingsEmailAdminReceiverEmail', $settingsEmailAdminReceiverEmail);

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Email general settings saved successfully', 'orwokki-pr'), 'data' => []]);
		wp_die();
	}

	public function saveCustomerEmailContentSettings() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_EMAIL_NOTIFICATION_SETTINGS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$emailSubject = sanitize_text_field($_POST['settingsEmailCustomerSubject']);
		$emailContent = sanitize_textarea_field($_POST['settingsEmailCustomerContent']);

		update_option('oprSettingsEmailCustomerSubject',$emailSubject);
		update_option('oprSettingsEmailCustomerContent', $emailContent);

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Customer email content settings saved successfully', 'orwokki-pr'), 'data' => []]);
		wp_die();
	}

	public function saveAdminEmailContentSettings() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_MANAGE_EMAIL_NOTIFICATION_SETTINGS)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$emailSubject = sanitize_text_field($_POST['settingsEmailAdminSubject']);
		$emailContent = sanitize_textarea_field($_POST['settingsEmailAdminContent']);

		update_option('oprSettingsEmailAdminSubject',$emailSubject);
		update_option('oprSettingsEmailAdminContent', $emailContent);

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Administration email content settings saved successfully', 'orwokki-pr'), 'data' => []]);
		wp_die();
	}

	public function listAdminTranslations() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::ROLE_RESERVATIONS_ADMIN)
				|| current_user_can(RolesAndCapabilities::ROLE_PLACES_ADMIN)
				|| current_user_can(RolesAndCapabilities::ROLE_GLOBAL_ADMIN)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$data = [
			'CONFIRM_RESERVATION_REMOVE' => __('Are you sure you want to remove this reservation?', 'orwokki-pr'),
			'CONFIRM_PLACE_REMOVE' => __('Are you sure you want to remove this place and all reservations related to it?', 'orwokki-pr'),
		];

		wp_send_json(['success' => true, 'shouldNotify' => false, 'messageType' => 'none', 'message' => '', 'data' => $data]);
		wp_die();
	}

	public function importCsvReservationsWithPlaces() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_IMPORTER)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$uploadedFile = $_FILES['file'];
		$fileData = wp_handle_upload($uploadedFile, ['test_form' => false]);

		$removeExistingReservationsFromPlace = (sanitize_text_field($_POST['removeExistingReservationsFromPlace'] ?? '0'));
		$createMissingData = (sanitize_text_field($_POST['createMissingData'] ?? '0'));
		$replaceExisting = (sanitize_text_field($_POST['replaceExisting'] ?? '0'));

		if ($fileData && !isset($fileData['error'])) {
			try {
				$importer = new ReservationsWithPlacesImporter;
				$importer->importCsv($fileData['file'], $removeExistingReservationsFromPlace, $createMissingData, $replaceExisting);
			}
			catch (Exception $e) {
				wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Import failed: %s', 'orwokki-pr'), $e->getMessage())]);
				wp_die();
			}
		}
		else {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Uploading import-file failed. ', 'orwokki-pr').$fileData['error']]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Reservations and places imported successfully.'), 'data' => []]);
		wp_die();
	}

	public function importCsvReservations() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_IMPORTER)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$uploadedFile = $_FILES['file'];
		$fileData = wp_handle_upload($uploadedFile, ['test_form' => false]);

		$removeExistingReservationsFromPlace = (sanitize_text_field($_POST['removeExistingReservationsFromPlace'] ?? '0'));

		if ($fileData && !isset($fileData['error'])) {
			try {
				$importer = new ReservationsImporter;
				$importer->importCsv($fileData['file'], $removeExistingReservationsFromPlace);
			}
			catch (Exception $e) {
				wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Import failed: %s', 'orwokki-pr'), $e->getMessage())]);
				wp_die();
			}
		}
		else {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Uploading import-file failed. ', 'orwokki-pr').$fileData['error']]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Reservations imported successfully.'), 'data' => []]);
		wp_die();
	}

	public function importCsvPlaces() {
		if (!(
			is_user_logged_in() && (
				current_user_can('administrator')
				|| current_user_can(RolesAndCapabilities::CAP_IMPORTER)
			)
		)) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Not allowed.', 'orwokki-pr')]);
			wp_die();
		}

		$uploadedFile = $_FILES['file'];
		$fileData = wp_handle_upload($uploadedFile, ['test_form' => false]);

		$removeExistingReservationsFromPlace = (sanitize_text_field($_POST['removeExistingReservationsFromPlace'] ?? '0'));
		$createMissingData = (sanitize_text_field($_POST['createMissingData'] ?? '0'));
		$replaceExisting = (sanitize_text_field($_POST['replaceExisting'] ?? '0'));

		if ($fileData && !isset($fileData['error'])) {
			try {
				$importer = new PlacesImporter;
				$importer->importCsv($fileData['file'], $removeExistingReservationsFromPlace, $createMissingData, $replaceExisting);
			}
			catch (Exception $e) {
				wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('Import failed: %s', 'orwokki-pr'), $e->getMessage())]);
				wp_die();
			}
		}
		else {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Uploading import-file failed. ', 'orwokki-pr').$fileData['error']]);
			wp_die();
		}

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('Places imported successfully.'), 'data' => []]);
		wp_die();
	}
}