<?php

namespace Orwokki\PlaceReservations\Reservations;

use Countable;
use Exception;
use Orwokki\PlaceReservations\Places\PlaceDirectory;
use Orwokki\PlaceReservations\Plugin\RolesAndCapabilities;

class ReservationDirectory
{
	const TABLE_BASE_NAME = 'opr_reservations';

	private $dataImport;
	private $periodTypeDir;

	public function __construct() {
		$this->dataImport = false;
		$this->periodTypeDir = new PeriodTypeDirectory;
	}

	public function activateDataImport() {
		if (current_user_can('administrator') || current_user_can(RolesAndCapabilities::CAP_IMPORTER)) {
			$this->dataImport = true;
		}
	}

	public function deactivateDataImport() {
		if (current_user_can('administrator') || current_user_can(RolesAndCapabilities::CAP_IMPORTER)) {
			$this->dataImport = false;
		}
	}

	/**
	 * @return string
	 */
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix.self::TABLE_BASE_NAME;
	}

	/**
	 * @return string
	 */
	public function getPlacesTableName(): string {
		global $wpdb;

		return $wpdb->prefix.PlaceDirectory::TABLE_BASE_NAME;
	}

	/**
	 * @param Reservation $reservation
	 * @return Reservation
	 * @throws Exception
	 */
	public function store(Reservation $reservation): Reservation {
		global $wpdb;

		$reservationData = $reservation->toArrayForSql();

		if ($reservationData['id'] > 0) {
			$wpdb->update($this->getTableName(), $reservationData, ['id' => $reservationData['id']]);

			return $reservation;
		}
		else {
			$reservationData['id'] = null;
			unset($reservationData['id']);
			$wpdb->insert($this->getTableName(), $reservationData);

			$reservationData['id'] = $wpdb->insert_id;

			return new Reservation($reservationData, $this->dataImport);
		}
	}

	/**
	 * @param Reservation $reservation
	 * @throws Exception
	 */
	public function delete(Reservation $reservation) {
		$reservation->deactivate();
		$reservation->delete();
		$this->store($reservation);
	}

	/**
	 * @param int $id
	 * @throws Exception
	 */
	public function removeById(int $id) {
		if ($id < 1)
			throw new Exception(sprintf(__("Invalid reservation ID '%d' given", 'orwokki-pr'), $id));

		$reservation = $this->getReservationById($id);

		if (!$reservation) {
			throw new Exception(sprintf(__("Could not find reservation with ID '%d'", 'orwokki-pr'), $id));
		}

		$this->delete($reservation);
	}

	/**
	 * @param int $id
	 * @return Reservation|null
	 * @throws Exception
	 */
	public function getReservationById(int $id): ?Reservation {
		if ($id < 1)
			throw new Exception(sprintf(__("Invalid reservation ID '%d' given", 'orwokki-pr'), $id));

		global $wpdb;

		$reservations = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE id = %d AND active = %d AND deleted = %d', $id, 1, 0), ARRAY_A);

		if (($reservations instanceof Countable || is_array($reservations)) && count($reservations) > 0) {
			return new Reservation( $reservations[0], $this->dataImport );
		}

		return null;
	}

	/**
	 * @param int $placeId
	 * @return Reservation|null
	 * @throws Exception
	 */
	public function getReservationByPlaceId(int $placeId): ?Reservation {
		if ($placeId < 1)
			throw new Exception(sprintf(__("Invalid place ID '%d' given", 'orwokki-pr'), $placeId));

		global $wpdb;

		$reservations = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE placeId = %d AND active = %d AND deleted = %d', $placeId, 1, 0), ARRAY_A);

		if (($reservations instanceof Countable || is_array($reservations)) && count($reservations) > 0) {
			return new Reservation( $reservations[0], $this->dataImport );
		}

		return null;
	}

	public function listReservationsFilteredArray(array $filterCriterias, int $limit = 20, int $start = 0): array {
		global $wpdb;

		$ret = [];
		$query = 'SELECT res.*, plc.name AS placeName, prd.name AS periodTypeName FROM '.$this->getTableName().' res JOIN '.$this->getPlacesTableName().' plc ON plc.id = res.placeId JOIN '.$this->periodTypeDir->getTableName().' prd ON prd.id = res.periodTypeId WHERE res.deleted = %d';

		foreach ($filterCriterias as $key => $value) {
			if (!$value = sanitize_text_field($value))
				continue;

			switch($key) {
				case 'active':
					$query .= sprintf(' AND res.active = %d', $value);
					break;
				case 'phoneNumber':
					$query .= sprintf(" AND res.%s LIKE '%s%%'", sanitize_text_field($key), $value);
					break;
				case 'name':
				case 'email':
					$query .= sprintf(" AND res.%s LIKE '%%%s%%'", sanitize_text_field($key), $value);
					break;
				case 'placeName':
					$query .= sprintf(" AND plc.name LIKE '%%%s%%'", $value);
					break;
				case 'placeTypeId':
					$query .= sprintf(" AND res.placeId IN (SELECT id FROM " . $this->getPlacesTableName() . " WHERE placeTypeId = %d)", (int)$value);
					break;
				case 'locationId':
					$query .= sprintf(" AND res.placeId IN (SELECT id FROM " . $this->getPlacesTableName() . " WHERE locationId = %d)", (int)$value);
					break;
				case 'periodTypeId':
					$query .= sprintf(" AND res.periodTypeId = %d", (int)$value);
					break;
				case 'shownReservationsIds':
					$query .= sprintf(" AND res.id NOT IN (%s)", $value);
					break;
			}
		}

		$query .= " ORDER BY res.name ASC, plc.name ASC";
		$query .= sprintf(' LIMIT %d, %d', $start, $limit);

		return $wpdb->get_results($wpdb->prepare($query, 0), ARRAY_A);
	}

	public function listActiveReservationsArray(): array {
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare('SELECT res.*, plc.name AS placeName, prd.name AS periodTypeName FROM '.$this->getTableName().' res JOIN '.$this->getPlacesTableName().' plc ON plc.id = res.placeId JOIN '.$this->periodTypeDir->getTableName().' prd ON prd.id = res.periodTypeId WHERE active = %d AND deleted = %d', 1, 0), ARRAY_A);
	}

	public function listReservationsArrayByPlaceId(int $placeId): array {
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare('SELECT res.*, plc.name AS placeName, prd.name AS periodTypeName FROM '.$this->getTableName().' res JOIN '.$this->getPlacesTableName().' plc ON plc.id = res.placeId JOIN '.$this->periodTypeDir->getTableName().' prd ON prd.id = res.periodTypeId WHERE placeId = %d AND deleted = %d', $placeId, 0), ARRAY_A);;
	}

	public function checkReservationMandatoryFields() {
		$missingMandatoryFields = [];
		$missingMandatoryFieldNames = [];

		if (!$_POST['placeId']) {
			$missingMandatoryFields[] = 'placeId';
			$missingMandatoryFieldNames[] = __('Place', 'orwokki-pr');
		}

		if ((get_option('oprSettingsReservationMandatoryPeriodType')) && (!$_POST['periodTypeId'])) {
			$missingMandatoryFields[] = 'periodTypeId';
			$missingMandatoryFieldNames[] = __('Period type', 'orwokki-pr');
		}

		if ((get_option('oprSettingsReservationMandatoryName')) && (!$_POST['name'])) {
			$missingMandatoryFields[] = 'name';
			$missingMandatoryFieldNames[] = __('Name', 'orwokki-pr');
		}

		if ((get_option('oprSettingsReservationMandatoryEmail')) && (!$_POST['email'])) {
			$missingMandatoryFields[] = 'email';
			$missingMandatoryFieldNames[] = __('Email', 'orwokki-pr');
		}

		if ((get_option('oprSettingsReservationMandatoryPhoneNumber')) && (!$_POST['phoneNumber'])) {
			$missingMandatoryFields[] = 'phoneNumber';
			$missingMandatoryFieldNames[] = __('Phone number', 'orwokki-pr');
		}

		if ((get_option('oprSettingsReservationMandatoryPeriodStartTime')) && (!$_POST['periodStartTime'])) {
			$missingMandatoryFields[] = 'periodStartTime';
			$missingMandatoryFieldNames[] = __('Reservation start time', 'orwokki-pr');
		}

		if ((get_option('oprSettingsReservationMandatoryPeriodEndTime')) && (!$_POST['periodEndTime'])) {
			$missingMandatoryFields[] = 'periodEndTime';
			$missingMandatoryFieldNames[] = __('Reservation end time', 'orwokki-pr');
		}

		if ((get_option('oprSettingsReservationMandatoryAdditionalInfo')) && (!$_POST['additionalInfo'])) {
			$missingMandatoryFields[] = 'additionalInfo';
			$missingMandatoryFieldNames[] = __('Additional information', 'orwokki-pr');
		}

		if (count($missingMandatoryFields) > 0) {
			wp_send_json(['success' => false, 'shouldNotify' => true, 'messageType' => 'error', 'message' => sprintf(__('There are mandatory fields without value. Field(s): %s', 'orwokki-pr'), implode(", ", $missingMandatoryFieldNames)), 'data' => ['missingReservationMandatoryFields' => $missingMandatoryFields]]);
			wp_die();
		}
	}
}