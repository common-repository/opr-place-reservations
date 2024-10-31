<?php

namespace Orwokki\PlaceReservations\Places;

use Exception;

class PlaceTypeDirectory
{
	const TABLE_BASE_NAME = 'opr_places_place_types';

	/**
	 * @return string
	 */
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix.self::TABLE_BASE_NAME;
	}

	public function add(string $generalType, string $placeType, int $active) {
		global $wpdb;

		$wpdb->insert($this->getTableName(), ['generalType' => esc_sql($generalType), 'name' => esc_sql($placeType), 'active' => esc_sql($active)]);

		return $wpdb->insert_id;
	}

	public function change(int $id, string $generalType, string $placeType, int $active) {
		global $wpdb;

		$wpdb->update($this->getTableName(), ['generalType' => esc_sql($generalType), 'name' => esc_sql($placeType), 'active' => esc_sql($active)], ['id' => $id]);
	}

	/**
	 * @param int $id
	 * @throws Exception
	 */
	public function removeById(int $id) {
		if ($id < 1)
			throw new Exception("Invalid place type ID '".$id."' given");

		global $wpdb;

		$wpdb->update($this->getTableName(), ['active' => 0], ['id' => $id]);
	}

	public function listActivePlaceTypes(): array {
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE active = %d AND deleted = %d ORDER BY name ASC', 1, 0), ARRAY_A);
	}

	public function listAllPlaceTypes(): array {
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE deleted = %d ORDER BY active DESC, name ASC', 0), ARRAY_A);
	}

	public function getPlaceTypeById(int $id) {
		if ($id < 1)
			throw new Exception("Invalid place type ID '".$id."' given");

		global $wpdb;

		$types = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE id = %d', $id), ARRAY_A);

		return $types[0];
	}

	public function getPlaceTypeByName(string $placeTypeName) {
		if (!$placeTypeName)
			throw new Exception("Invalid place type name '".$placeTypeName."' given");

		global $wpdb;

		$types = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE name = %s', $placeTypeName), ARRAY_A);

		return $types[0];
	}
}