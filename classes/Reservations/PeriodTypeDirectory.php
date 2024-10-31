<?php

namespace Orwokki\PlaceReservations\Reservations;

use Exception;

class PeriodTypeDirectory
{
	const TABLE_BASE_NAME = 'opr_reservations_period_types';

	/**
	 * @return string
	 */
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix.self::TABLE_BASE_NAME;
	}

	public function add(string $periodTypeName, int $active) {
		global $wpdb;

		$wpdb->insert($this->getTableName(), ['name' => esc_sql($periodTypeName), 'active' => esc_sql($active)]);

		return $wpdb->insert_id;
	}

	public function change(int $id, string $periodTypeName, int $active) {
		global $wpdb;

		$wpdb->update($this->getTableName(), ['name' => esc_sql($periodTypeName), 'active' => esc_sql($active)], ['id' => $id]);
	}

	/**
	 * @param int $id
	 * @throws Exception
	 */
	public function removeById(int $id) {
		if ($id < 1)
			throw new Exception("Invalid period type ID '".$id."' given");

		global $wpdb;

		$wpdb->update($this->getTableName(), ['active' => 0], ['id' => $id]);
	}

	public function listActivePeriodTypes(): array {
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE active = %d AND deleted = %d ORDER BY name ASC', 1, 0), ARRAY_A);
	}

	public function listAllPeriodTypes(): array {
		global $wpdb;

		return $wpdb->get_results($wpdb->prepare('SELECT * FROM '. $this->getTableName().' WHERE deleted = %d ORDER BY active DESC, name ASC', 0), ARRAY_A);
	}

	/**
	 * @param int $id
	 * @return mixed
	 * @throws Exception
	 */
	public function getPeriodTypeById(int $id) {
		if ($id < 1)
			throw new Exception(sprintf(__("Invalid period type ID '%d' given", 'orwokki-pr'), $id));

		global $wpdb;

		$types = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE id = %d', $id), ARRAY_A);

		return $types[0];
	}

	/**
	 * @param string $periodTypeName
	 * @return mixed
	 * @throws Exception
	 */
	public function getPeriodTypeByName(string $periodTypeName) {
		if (!$periodTypeName)
			throw new Exception(sprintf(__("Invalid period type name '%s' given", 'orwokki-pr'), $periodTypeName));

		global $wpdb;

		$types = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$this->getTableName().' WHERE name = %s', $periodTypeName), ARRAY_A);

		return $types[0];
	}
}