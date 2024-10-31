<?php

namespace Orwokki\PlaceReservations\Places;

use Exception;

class LocationDirectory
{
	const TABLE_BASE_NAME = 'opr_places_locations';

	/**
	 * @return string
	 */
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix.self::TABLE_BASE_NAME;
	}

	public function add(string $generalType, string $locationName, int $active) {
		global $wpdb;

		$wpdb->insert($this->getTableName(), ['generalType' => esc_sql($generalType), 'name' => esc_sql($locationName), 'active' => esc_sql($active)]);

		return $wpdb->insert_id;
	}

	public function change(int $id, string $generalType, string $locationName, int $active) {
		global $wpdb;

		$wpdb->update($this->getTableName(), ['generalType' => esc_sql($generalType), 'name' => esc_sql($locationName), 'active' => esc_sql($active)], ['id' => $id]);
	}

	/**
	 * @param int $id
	 * @throws Exception
	 */
	public function removeById(int $id) {
		if ($id < 1)
			throw new Exception("Invalid location ID '".$id."' given");

		global $wpdb;

		$wpdb->query($wpdb->prepare('UPDATE '.$this->getTableName().' SET active = %d WHERE id = %d', 0, $id));
	}

	public function listActiveLocations(): array {
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE active = %d AND deleted = %d ORDER BY name ASC',  1, 0), ARRAY_A);
	}

	public function listAllLocations(): array {
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE deleted = %d ORDER BY active DESC, name ASC', 0), ARRAY_A);
	}

	public function getLocationById(int $id) {
		if ($id < 1)
			throw new Exception(sprintf(__("Invalid location ID '%d' given", 'orwokki-pr'), $id));

		global $wpdb;

		$types = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE id = %d', $id), ARRAY_A);

		return $types[0] ?? null;
	}

	public function getLocationByName(string $locationName) {
		if (!$locationName)
			throw new Exception(sprintf(__("Invalid location name '%s' given", 'orwokki-pr'), $locationName));

		global $wpdb;

		$types = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE name = %s', $locationName), ARRAY_A);

		return $types[0] ?? null;
	}
}