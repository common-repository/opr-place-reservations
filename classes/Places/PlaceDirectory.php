<?php

namespace Orwokki\PlaceReservations\Places;

use Countable;
use Exception;
use Orwokki\PlaceReservations\Reservations\PeriodTypeDirectory;
use Orwokki\PlaceReservations\Reservations\Reservation;
use Orwokki\PlaceReservations\Reservations\ReservationDirectory;

class PlaceDirectory
{
	const TABLE_BASE_NAME = 'opr_places';

	private $reservationDir;
	private $placeTypeDir;
	private $locationDir;
	private $periodTypeDir;

	public function __construct() {
		$this->reservationDir = new ReservationDirectory;
		$this->placeTypeDir = new PlaceTypeDirectory;
		$this->periodTypeDir = new PeriodTypeDirectory;
		$this->locationDir = new LocationDirectory;
	}

	/**
	 * @return string
	 */
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix.self::TABLE_BASE_NAME;
	}

	/**
	 * @param Place $place
	 * @return Place
	 * @throws Exception
	 */
	public function store(Place $place): Place {
		global $wpdb;

		if ($this->isDuplicate($place)) {
			throw new Exception(sprintf(__("Place with name '%s' already exists.", 'orwokki-pr'), $place->getName()));
		}

		$placeData = $place->toArrayForSql();

		if ($placeData['id'] > 0) {
			$wpdb->update($this->getTableName(), $placeData, ['id' => $placeData['id']]);

			return $place;
		}
		else {
			$placeData['id'] = null;
			unset($placeData['id']);
			$wpdb->insert($this->getTableName(), $placeData);

			$placeData['id'] = $wpdb->insert_id;

			return new Place($placeData);
		}
	}

	/**
	 * @param Place $place
	 * @throws Exception
	 */
	public function delete(Place $place) {
		foreach ($this->reservationDir->listReservationsArrayByPlaceId($place->getId()) as $reservationArray) {
			$reservation = new Reservation($reservationArray);
			$this->reservationDir->delete($reservation);
		}

		$place->deactivate();
		$place->delete();
		$this->store($place);
	}

	/**
	 * @param int $id
	 * @throws Exception
	 */
	public function removeById(int $id) {
		if ($id < 1)
			throw new Exception(sprintf(__("Invalid place ID '%d' given", 'orwokki-pr'), $id));

		$place = $this->getPlaceById($id);
		if (!$place) {
			throw new Exception(sprintf(__("Could not find place with ID '%d'", 'orwokki-pr'), $id));
		}

		$this->delete($place);
	}

	/**
	 * @param int $id
	 * @return Place|null
	 * @throws Exception
	 */
	public function getPlaceById(int $id): ?Place {
		if ($id < 1)
			throw new Exception(sprintf(__("Invalid place ID '%d' given", 'orwokki-pr'), $id));

		global $wpdb;

		$places = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE id = %d', $id), ARRAY_A);

		if (($places instanceof Countable || is_array($places)) && count($places) > 0) {
			return new Place( $places[0] );
		}

		return null;
	}

	/**
	 * @param string $placeName
	 * @return Place|null
	 * @throws Exception
	 */
	public function getPlaceByName(string $placeName): ?Place {
		if (!$placeName)
			throw new Exception(sprintf(__("Invalid place name '%s' given", 'orwokki-pr'), $placeName));

		global $wpdb;

		$places = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE name = %s AND active = %d AND deleted = %d', $placeName, 1, 0), ARRAY_A);

		if (($places instanceof Countable || is_array($places)) && count($places) > 0) {
			return new Place( $places[0] );
		}

		return null;
	}

	public function listPlacesFilteredArray(array $filterCriterias, int $limit = 20, int $start = 0): array {
		global $wpdb;

		$query = "SELECT";
		$query .= " plc.*";
		$query .= ", plct.name AS placeTypeName";
		$query .= ", prdt.name AS periodTypeName";
		$query .= ", loc.name AS locationName";
		$query .= " FROM ".$this->getTableName()." plc";
		$query .= " JOIN ".$this->placeTypeDir->getTableName()." plct ON plct.id = plc.placeTypeId";
		$query .= " JOIN ".$this->periodTypeDir->getTableName()." prdt ON prdt.id = plc.periodTypeId";
		$query .= " JOIN ".$this->locationDir->getTableName()." loc ON loc.id = plc.locationId";
		$query .= " WHERE plc.deleted = %d";

		foreach ($filterCriterias as $key => $value) {
			if (!$value = sanitize_text_field($value))
				continue;

			switch($key) {
				case 'active':
					$query .= sprintf(' AND plc.active = %d', $value);
					break;
				case 'name':
					$query .= sprintf(" AND plc.%s LIKE '%%%s%%'", sanitize_text_field($key), $value);
					break;
				case 'length':
				case 'width':
				case 'depth':
					$query .= sprintf(" AND plc.%s >= %s", sanitize_text_field($key), str_replace(',', '.', $value));
					break;
				case 'placeTypeId':
					$query .= sprintf(" AND plc.placeTypeId = %d", (int)$value);
					break;
				case 'locationId':
					$query .= sprintf(" AND plc.locationId = %d", (int)$value);
					break;
				case 'periodTypeId':
					$query .= sprintf(" AND plc.periodTypeId = %d", (int)$value);
					break;
				case 'shownPlacesIds':
					$query .= sprintf(" AND plc.id NOT IN (%s)", $value);
					break;
				case 'onlyFreePlaces':
					$query .= sprintf(" AND plc.id NOT IN (SELECT placeId FROM ".$this->reservationDir->getTableName()." WHERE active = %d AND deleted = %d)", 1, 0);
					break;
			}
		}

		$query .= $this->getPlacesOrderBy();
		if ($limit > 0)
			$query .= sprintf(' LIMIT %d, %d', $start, $limit);

		return $wpdb->get_results($wpdb->prepare($query, 0), ARRAY_A);
	}

	public function listActivePlacesArray(): array {
		global $wpdb;

		$query = "SELECT";
		$query .= " plc.*";
		$query .= ", plct.name AS placeTypeName";
		$query .= ", prdt.name AS periodTypeName";
		$query .= ", loc.name AS locationName";
		$query .= " FROM ".$this->getTableName()." plc";
		$query .= " JOIN ".$this->placeTypeDir->getTableName()." plct ON plct.id = plc.placeTypeId";
		$query .= " JOIN ".$this->periodTypeDir->getTableName()." prdt ON prdt.id = plc.periodTypeId";
		$query .= " JOIN ".$this->locationDir->getTableName()." loc ON loc.id = plc.locationId";
		$query .= " WHERE plc.active = %d AND plc.deleted = %d";
		$query .= $this->getPlacesOrderBy();

		return $wpdb->get_results($wpdb->prepare($query, 1, 0), ARRAY_A);
	}

	public function listActiveFreePlacesArray(): array {
		global $wpdb;

		$query = "SELECT";
		$query .= " plc.*";
		$query .= ", plct.name AS placeTypeName";
		$query .= ", prdt.name AS periodTypeName";
		$query .= ", loc.name AS locationName";
		$query .= " FROM ".$this->getTableName()." plc";
		$query .= " JOIN ".$this->placeTypeDir->getTableName()." plct ON plct.id = plc.placeTypeId";
		$query .= " JOIN ".$this->periodTypeDir->getTableName()." prdt ON prdt.id = plc.periodTypeId";
		$query .= " JOIN ".$this->locationDir->getTableName()." loc ON loc.id = plc.locationId";
		$query .= " WHERE plc.active = %d AND plc.deleted = %d";
		$query .= " AND plc.id NOT IN (SELECT placeId FROM ".$this->reservationDir->getTableName()." WHERE active = %d AND deleted = %d)";
		$query .= $this->getPlacesOrderBy();

		return $wpdb->get_results($wpdb->prepare($query, 1, 0, 1, 0), ARRAY_A);
	}

	public function listAllPlacesArray(): array {
		global $wpdb;

		$query = "SELECT";
		$query .= " plc.*";
		$query .= ", plct.name AS placeTypeName";
		$query .= ", prdt.name AS periodTypeName";
		$query .= ", loc.name AS locationName";
		$query .= " FROM ".$this->getTableName()." plc";
		$query .= " JOIN ".$this->placeTypeDir->getTableName()." plct ON plct.id = plc.placeTypeId";
		$query .= " JOIN ".$this->periodTypeDir->getTableName()." prdt ON prdt.id = plc.periodTypeId";
		$query .= " JOIN ".$this->locationDir->getTableName()." loc ON loc.id = plc.locationId";
		$query .= " WHERE plc.deleted = %d";
		$query .= $this->getPlacesOrderBy();

		return $wpdb->get_results($wpdb->prepare($query, 0), ARRAY_A);;
	}

	public function isDuplicate(Place $place): bool {
		global $wpdb;

		$query = "SELECT id FROM ".$this->getTableName()." WHERE";
		$query .= " name = %s";
		if ($place->getId())
			$query .= " AND id != ".$place->getId();

		$query .= " AND active = %d AND deleted = %d";

		$places = $wpdb->get_results($wpdb->prepare($query, 1, 0), ARRAY_A);

		if (($places instanceof Countable || is_array($places)) && count($places) > 0) {
			return true;
		}

		return false;
	}

	private function getPlacesOrderBy(): string {
		switch(get_option('oprSettingsCustomerOrderByColumn') ?: 'name') {
			case 'name':
				return ' ORDER BY plc.name ASC';
			case 'length':
				return ' ORDER BY plc.length ASC';
			case 'width':
				return ' ORDER BY plc.width ASC';
			case 'depth':
				return ' ORDER BY plc.depth ASC';
			case 'placeTypeName':
				return ' ORDER BY plct.name ASC';
			case 'periodTypeName':
				return ' ORDER BY prdt.name ASC';
			case 'locationName':
				return ' ORDER BY loc.name ASC';
		}
	}
}