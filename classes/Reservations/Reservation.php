<?php

namespace Orwokki\PlaceReservations\Reservations;

use DateTime;
use Exception;
use Orwokki\PlaceReservations\Places\Place;
use Orwokki\PlaceReservations\Places\PlaceDirectory;
use Orwokki\PlaceReservations\Plugin\RolesAndCapabilities;

class Reservation
{
	private $data = [];
	private $placeDir;
	private $periodTypeDir;
	private $dataImport;

	/**
	 * Reservation constructor.
	 * @param array $data
	 * @param bool $dataImport
	 * @throws Exception
	 */
	public function __construct(array $data, bool $dataImport = false) {
		$this->dataImport = $dataImport;
		$this->placeDir = new PlaceDirectory;
		$this->periodTypeDir = new PeriodTypeDirectory;
		$this->populate($data);
	}

	/**
	 * @param array $data
	 * @throws Exception
	 */
	private function populate(array $data) {
		$this->data['id'] = $data['id'] ?? null;
		$this->data['active'] = $data['active'] ?? 0;
		$this->data['approved'] = $data['approved'] ?? 0;
		$this->data['deleted'] = $data['deleted'] ?? 0;
		$this->data['deleteTime'] = $data['deleteTime'] ?? null;
		$this->setPlaceId($data['placeId'] ?? 0);
		$this->setName($data['name'] ?? '');
		$this->setEmail($data['email'] ?? '');
		$this->setPhoneNumber($data['phoneNumber'] ?? '');
		$this->setPeriodTypeId($data['periodTypeId'] ?? 0);
		$this->setPeriodStartTime($data['periodStartTime'] ?? '');
		$this->setPeriodEndTime($data['periodEndTime'] ?? '');
		$this->setAdditionalInfo($data['additionalInfo'] ?? '');
	}

	/**
	 * @return array
	 */
	public function toArray(): array {
		return $this->data;
	}

	public function toArrayForSql(): array {
		$ret = [];
		foreach ($this->data as $key => $value) {
			$ret[$key] = esc_sql($value);
		}

		return $ret;
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return (int) $this->data['id'];
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool {
		return ($this->data['active']);
	}

	public function activate() {
		$this->data['active'] = 1;
	}

	public function deactivate() {
		$this->data['active'] = 0;
	}

	/**
	 * @return bool
	 */
	public function isApproved(): bool {
		return ($this->data['approved']);
	}

	public function approve() {
		$this->data['approved'] = 1;
	}

	public function unapprove() {
		$this->data['approved'] = 0;
	}

	public function delete() {
		$this->data['deleted'] = 1;
		$this->data['deleteTime'] = date('Y-m-d H:i:s');
	}

	/**
	 * @return bool
	 */
	public function isDeleted(): bool {
		return ($this->data['deleted']);
	}

	/**
	 * @return int
	 */
	public function getPlaceId(): int {
		return (int) $this->data['placeId'];
	}

	/**
	 * @return Place
	 * @throws Exception
	 */
	public function getPlace(): Place {
		return $this->placeDir->getPlaceById($this->getPlaceId());
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getPlaceName(): string {
		return $this->getPlace()->getName();
	}

	/**
	 * @param int $placeId
	 * @throws Exception
	 */
	public function setPlaceId(int $placeId) {
		if ($placeId < 1)
			throw new Exception("Place ID is not set");

		if (!($this->dataImport && (current_user_can('administrator') || current_user_can(RolesAndCapabilities::CAP_IMPORTER) || current_user_can(RolesAndCapabilities::CAP_REMOVE_RESERVATION)))) {
			if (!in_array($placeId, $this->getAvailablePlaceIds())) {
				throw new Exception("Invalid place ID '" . $placeId . "'");
			}
		}

		$this->data['placeId'] = $placeId;
	}

	/**
	 * @param string $name
	 * @throws Exception
	 */
	public function setName(string $name) {
		if ($name == '')
			throw new Exception("Name is not set");

		$this->data['name'] = strip_tags($name);
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->data['name'];
	}

	/**
	 * @param string $email
	 * @throws Exception
	 */
	public function setEmail(string $email) {
		if ($email == '') {
			$this->data['email'] = '';
			return;
		}

		if (!is_email($email))
			throw new Exception("Invalid email address given");

		$this->data['email'] = strip_tags($email);
	}

	/**
	 * @return string
	 */
	public function getEmail(): string {
		return $this->data['email'];
	}

	/**
	 * @param string $phoneNumber
	 */
	public function setPhoneNumber(string $phoneNumber) {
		$this->data['phoneNumber'] = strip_tags($phoneNumber);
	}

	/**
	 * @return string
	 */
	public function getPhoneNumber(): string {
		return $this->data['phoneNumber'];
	}

	/**
	 * @param int $periodTypeId
	 * @throws Exception
	 */
	public function setPeriodTypeId(int $periodTypeId) {
		if ($periodTypeId < 1) {
			$this->data['periodTypeId'] = 0;
			return;
		}

		if (!in_array($periodTypeId, $this->getAvailablePeriodTypeIds()))
			throw new Exception("Invalid period type");

		$this->data['periodTypeId'] = $periodTypeId;
	}

	/**
	 * @return int
	 */
	public function getPeriodTypeId(): int {
		return $this->data['periodTypeId'];
	}

	/**
	 * @return string
	 */
	public function getPeriodTypeName(): string {
		if ($this->getPeriodTypeId() < 1)
			return '';

		try {
			$periodType = $this->periodTypeDir->getPeriodTypeById($this->getPeriodTypeId());
		}
		catch (Exception $e) {
			return '';
		}

		return $periodType['name'];
	}
	/**
	 * @param string $periodStartTime
	 * @throws Exception
	 */
	public function setPeriodStartTime(string $periodStartTime) {
		if ($periodStartTime == '' || $periodStartTime == '0000-00-00 00:00:00') {
			$this->data['periodStartTime'] = '';
			return;
		}

		try {
			$dateTime = new DateTime($periodStartTime);
		}
		catch (Exception $e) {
			throw new Exception("Invalid period start time");
		}

		$this->data['periodStartTime'] = $dateTime->format('Y-m-d H:i:s');
	}

	/**
	 * @return DateTime
	 * @throws Exception
	 */
	public function getPeriodStartTime(): ?DateTime {
		if ($this->data['periodStartTime'] == '0000-00-00 00:00:00')
			return null;

		try {
			return new DateTime($this->data['periodStartTime']);
		}
		catch(Exception $e) {
			throw new Exception('Failed to create period start time object');
		}
	}

	/**
	 * @param string $periodEndTime
	 * @throws Exception
	 */
	public function setPeriodEndTime(string $periodEndTime) {
		if ($periodEndTime == '' || $periodEndTime == '0000-00-00 00:00:00') {
			$this->data['periodEndTime'] = '';
			return;
		}

		try {
			$dateTime = new DateTime($periodEndTime);
		}
		catch (Exception $e) {
			throw new Exception("Invalid period end time");
		}

		$this->data['periodEndTime'] = $dateTime->format('Y-m-d H:i:s');
	}

	/**
	 * @return DateTime
	 * @throws Exception
	 */
	public function getPeriodEndTime(): ?DateTime {
		if ($this->data['periodEndTime'] == '0000-00-00 00:00:00')
			return null;

		try {
			return new DateTime($this->data['periodEndTime']);
		}
		catch(Exception $e) {
			throw new Exception('Failed to create period end time object');
		}
	}

	public function setAdditionalInfo(string $additionalInfo) {
		$this->data['additionalInfo'] = strip_tags($additionalInfo);
	}

	public function getAdditionalInfo(): string {
		return $this->data['additionalInfo'];
	}

	/**
	 * @return array
	 */
	private function getAvailablePeriodTypeIds(): array {
		return array_column($this->periodTypeDir->listActivePeriodTypes(), 'id');
	}

	/**
	 * @return array
	 */
	private function getAvailablePlaceIds(): array {
		$ret = [];
		foreach ($this->placeDir->listActivePlacesArray() as $placeData) {
			$ret[] = $placeData['id'];
		}

		return $ret;
	}

}