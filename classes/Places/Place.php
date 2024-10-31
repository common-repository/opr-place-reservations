<?php

namespace Orwokki\PlaceReservations\Places;

use Exception;
use Orwokki\PlaceReservations\Reservations\PeriodTypeDirectory;

class Place
{
	const PLACE_GENERAL_TYPE_MARINA = 'Marina';
	const PLACE_GENERAL_TYPE_PARKING = 'Parking';

	private $data = [];
	private $placeTypeDir;
	private $periodTypeDir;
	private $locationDir;
	private $availablePlaceTypeIds = [];
	private $availablePeriodTypeIds = [];
	private $availableLocationIds = [];

	/**
	 * Place constructor.
	 * @param array $data
	 * @throws Exception
	 */
	public function __construct(array $data) {
		$this->placeTypeDir = new PlaceTypeDirectory;
		$this->periodTypeDir = new PeriodTypeDirectory;
		$this->locationDir = new LocationDirectory;
		$this->availablePlaceTypeIds = array_column($this->placeTypeDir->listActivePlaceTypes(), 'id');
		$this->availablePeriodTypeIds = array_column($this->periodTypeDir->listActivePeriodTypes(), 'id');
		$this->availableLocationIds = array_column($this->locationDir->listActiveLocations(), 'id');
		$this->populate($data);
	}

	/**
	 * @param array $data
	 * @throws Exception
	 */
	private function populate(array $data) {
		$this->data['id'] = $data['id'] ?? null;
		$this->data['active'] = $data['active'] ?? 0;
		$this->data['deleted'] = $data['deleted'] ?? 0;
		$this->data['deleteTime'] = $data['deleteTime'] ?? null;
		$this->setGeneralType($data['generalType'] ?? '');
		$this->setPlaceTypeId($data['placeTypeId'] ?? 0);
		$this->setPeriodTypeId($data['periodTypeId'] ?? 0);
		$this->setName($data['name'] ?? '');
		$this->setLocationId($data['locationId'] ?? 0);
		$this->setLength($data['length'] ?? 0.0);
		$this->setWidth($data['width'] ?? 0.0);
		$this->setDepth($data['depth'] ?? 0.0);
		$this->setDescription($data['description'] ?? '');
	}

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

	public function getId(): int {
		return (int) $this->data['id'];
	}

	public function getGeneralType(): string {
		return $this->data['generalType'];
	}

	public function isActive(): bool {
		return ($this->data['active']);
	}

	public function activate() {
		$this->data['active'] = 1;
	}

	public function deactivate() {
		$this->data['active'] = 0;
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
	 * @param string $generalType
	 * @throws Exception
	 */
	public function setGeneralType(string $generalType) {
		if ($generalType == '')
			throw new Exception('General type not set');

		if (!in_array($generalType, [self::PLACE_GENERAL_TYPE_MARINA, self::PLACE_GENERAL_TYPE_PARKING]))
			throw new Exception('Invalid general type');

		$this->data['generalType'] = $generalType;
	}

	public function getPlaceTypeId(): int {
		return (int) $this->data['placeTypeId'];
	}

	public function getPlaceTypeName(): string {
		if ($this->data['placeTypeId'] < 1)
			return '';

		try {
			$placeType = $this->placeTypeDir->getPlaceTypeById($this->getPlaceTypeId());
		}
		catch (Exception $e) {
			return '';
		}

		return $placeType['name'];
	}

	/**
	 * @param int $placeTypeId
	 * @throws Exception
	 */
	public function setPlaceTypeId(int $placeTypeId) {
		if ($placeTypeId > 0) {
			if (!in_array($placeTypeId, $this->availablePlaceTypeIds))
				throw new Exception("Invalid place type");
		}

		$this->data['placeTypeId'] = $placeTypeId;
	}

	public function getPeriodTypeId(): int {
		return (int) $this->data['periodTypeId'];
	}

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
	 * @param int $periodTypeId
	 * @throws Exception
	 */
	public function setPeriodTypeId(int $periodTypeId) {
		if ($periodTypeId > 0) {
			if (!in_array($periodTypeId, $this->availablePeriodTypeIds))
				throw new Exception("Invalid period type");
		}

		$this->data['periodTypeId'] = $periodTypeId;
	}

	public function getLocationId(): int {
		return (int) $this->data['locationId'];
	}

	public function getLocationName(): string {
		if ($this->getLocationId() < 1)
			return '';

		try {
			$location = $this->locationDir->getLocationById($this->getLocationId());
		}
		catch (Exception $e) {
			return '';
		}

		return $location['name'];
	}

	/**
	 * @param int $locationId
	 * @throws Exception
	 */
	public function setLocationId(int $locationId) {
		if ($locationId > 0) {
			if (!in_array($locationId, $this->availableLocationIds))
				throw new Exception("Invalid period type");
		}

		$this->data['locationId'] = $locationId;
	}

	public function getName(): string {
		return $this->data['name'];
	}

	/**
	 * @param string $name
	 * @throws Exception
	 */
	public function setName(string $name) {
		if (!$name)
			throw new Exception("Name not given");

		$this->data['name'] = $name;
	}

	public function getLocation(): string {
		return $this->data['location'];
	}

	public function getLength(): float {
		return (float) $this->data['length'];
	}

	public function setLength(float $length) {
		$this->data['length'] = $length;
	}

	public function getWidth(): float {
		return (float) $this->data['width'];
	}

	public function setWidth(float $width) {
		$this->data['width'] = $width;
	}

	public function getDepth(): float {
		return (float) $this->data['depth'];
	}

	public function setDepth(float $depth) {
		$this->data['depth'] = $depth;
	}

	public function getDescription(): string {
		return $this->data['description'];
	}

	public function setDescription(string $description) {
		$this->data['description'] = $description;
	}
}