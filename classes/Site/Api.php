<?php


namespace Orwokki\PlaceReservations\Site;


use Exception;
use Orwokki\PlaceReservations\Places\Place;
use Orwokki\PlaceReservations\Places\PlaceDirectory;
use Orwokki\PlaceReservations\Reservations\Messaging;
use Orwokki\PlaceReservations\Reservations\Reservation;
use Orwokki\PlaceReservations\Reservations\ReservationDirectory;

class Api
{
	private $placeDir;
	private $reservationDir;
	private $messaging;

	public function __construct() {
		$this->placeDir = new PlaceDirectory;
		$this->reservationDir = new ReservationDirectory;
		$this->messaging = new Messaging;
	}

	public function listAvailablePlaces() {
		$criterias = $this->parseFilterCriterias($_POST);
		$criterias['onlyFreePlaces'] = 'on';
		$places = $this->placeDir->listPlacesFilteredArray($criterias, 0);

		wp_send_json(['success' => true, 'shouldNotify' => false, 'messageType' => 'none', 'message' => '', 'data' => ['places' => $places, 'placeFieldTranslations' => $this->getPlaceFieldTranslations(), 'customerViewSettings' => $this->createCustomerViewSettingsArray()]]);
		wp_die();
	}

	public function addReservation() {
		$this->reservationDir->checkReservationMandatoryFields();

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
			'additionalInfo' => sanitize_text_field($_POST['additionalInfo'] ?? ''),
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
			if (!$this->reservationDir->store($reservation)) {
				wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed storing new reservation.', 'orwokki-pr')]);
				wp_die();
			}
		}
		catch (Exception $e) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => __('Failed storing new reservation.', 'orwokki-pr')]);
			wp_die();
		}

		$this->messaging->sendReservationAddedAdminMessage($reservation);
		$this->messaging->sendReservationAddedCustomerMessage($reservation);

		wp_send_json(['success' => true, 'shouldNotify' => true, 'messageType' => 'updated', 'message' => __('New reservation created successfully.', 'orwokki-pr'), 'data' => []]);
		wp_die();
	}

	private function createCustomerViewSettingsArray() {
		return [
			'settingsCustomerShowGeneralType' => (int) get_option('oprSettingsCustomerShowGeneralType') ?? 1,
			'settingsCustomerShowPlaceType' => (int) get_option('oprSettingsCustomerShowPlaceType') ?? 1,
			'settingsCustomerShowLocation' => (int) get_option('oprSettingsCustomerShowLocation') ?? 1,
			'settingsCustomerShowPeriodType' => (int) get_option('oprSettingsCustomerShowPeriodType') ?? 1,
			'settingsCustomerShowLength' => (int) get_option('oprSettingsCustomerShowLength') ?? 1,
			'settingsCustomerShowWidth' => (int) get_option('oprSettingsCustomerShowWidth') ?? 1,
			'settingsCustomerShowDepth' => (int) get_option('oprSettingsCustomerShowDepth') ?? 1,
			'settingsCustomerShowDescription' => (int) get_option('oprSettingsCustomerShowDescription') ?? 1,
			'settingsCustomerOrderByColumn' => get_option('oprSettingsCustomerOrderByColumn') ?: 'name',
		];
	}

	private function parseFilterCriterias($data) {
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

	private function getPlaceFieldTranslations() {
		return [
			'reserveButton' => [
				'caption' => __("Reserve", 'orwokki-pr'),
			]
		];
	}
}