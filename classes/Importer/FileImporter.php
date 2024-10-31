<?php


namespace Orwokki\PlaceReservations\Importer;

use Exception;
use Orwokki\PlaceReservations\Places\LocationDirectory;
use Orwokki\PlaceReservations\Places\Place;
use Orwokki\PlaceReservations\Places\PlaceDirectory;
use Orwokki\PlaceReservations\Places\PlaceTypeDirectory;
use Orwokki\PlaceReservations\Reservations\PeriodTypeDirectory;
use Orwokki\PlaceReservations\Reservations\Reservation;
use Orwokki\PlaceReservations\Reservations\ReservationDirectory;

abstract class FileImporter
{
	protected $reservationDir;
	protected $placeDir;
	protected $placeTypeDir;
	protected $locationDir;
	protected $periodTypeDir;

	public function __construct() {
		$this->reservationDir = new ReservationDirectory;
		$this->reservationDir->activateDataImport();
		$this->placeDir = new PlaceDirectory;
		$this->placeTypeDir = new PlaceTypeDirectory;
		$this->locationDir = new LocationDirectory;
		$this->periodTypeDir = new PeriodTypeDirectory;
	}

	/**
	 * @param array $reservationData
	 * @param Place $place
	 * @param bool $removeExistingReservationsFromPlace
	 * @return Reservation
	 * @throws Exception
	 */
	protected function handleReservation(array $reservationData, Place $place, bool $removeExistingReservationsFromPlace): Reservation {
		if ($existingReservation = $this->reservationDir->getReservationByPlaceId($place->getId())) {
			if ($existingReservation->isActive()) {
				if ($removeExistingReservationsFromPlace) {
					$this->reservationDir->delete($existingReservation);
				}
				else {
					throw new Exception(sprintf(__("Place '%s' already has existing reservation.", 'orwokki-pr'), $place->getName()));
				}
			}
		}

		$reservationData['placeId'] = $place->getId();
		$reservationData['periodTypeId'] = $place->getPeriodTypeId();
		$reservation = new Reservation($reservationData, true);

		return $this->reservationDir->store($reservation);
	}

	/**
	 * @param string $name
	 * @param string $generalType
	 * @param string $placeTypeName
	 * @param string $periodTypeName
	 * @param string $locationName
	 * @param float $length
	 * @param float $width
	 * @param float $depth
	 * @param string $description
	 * @param bool $createMissingData
	 * @return Place
	 * @throws Exception
	 */
	protected function createAndStoreNewPlace(string $name, string $generalType, string $placeTypeName, string $periodTypeName, string $locationName, float $length, float $width, float $depth, string $description, bool $createMissingData) {
		$placeTypeId = $this->handlePlaceTypeName($generalType, $placeTypeName, $createMissingData);
		$periodTypeId = $this->handlePlacePeriodTypeName($periodTypeName, $createMissingData);
		$locationId = $this->handleLocationName($generalType, $locationName, $createMissingData);

		$place = new Place([
			'active' => 1,
			'generalType' => $generalType,
			'placeTypeId' => $placeTypeId,
			'periodTypeId' => $periodTypeId,
			'locationId' => $locationId,
			'name' => $name,
			'length' => $length,
			'width' => $width,
			'depth' => $depth,
			'description' => $description,
		]);

		return $this->placeDir->store($place);
	}

	/**
	 * @param string $generalType
	 * @param string|null $placeTypeName
	 * @param bool $createMissingData
	 * @return int
	 * @throws Exception
	 */
	protected function handlePlaceTypeName(string $generalType, ?string $placeTypeName, bool $createMissingData): int {
		if (!$placeTypeName) {
			$placeTypeId = 0;
		}
		else {
			if (!$placeType = $this->placeTypeDir->getPlaceTypeByName($placeTypeName)) {
				if (!$createMissingData) {
					throw new Exception(sprintf(__("Place type with name '%s' could not be found.", 'orwokki-pr'), $placeTypeName));
				} else {
					$placeTypeId = $this->placeTypeDir->add($generalType, $placeTypeName, 1);
				}
			} else {
				$placeTypeId = $placeType['id'];
			}
		}

		return $placeTypeId;
	}

	/**
	 * @param string|null $periodTypeName
	 * @param bool $createMissingData
	 * @return int
	 * @throws Exception
	 */
	protected function handlePlacePeriodTypeName(?string $periodTypeName, bool $createMissingData): int {
		if (!$periodTypeName) {
			$periodTypeId = 0;
		}
		else {
			if (!$periodType = $this->periodTypeDir->getPeriodTypeByName($periodTypeName)) {
				if (!$createMissingData) {
					throw new Exception(sprintf(__("Period type with name '%s' could not be found.", 'orwokki-pr'), $periodTypeName));
				} else {
					$periodTypeId = $this->periodTypeDir->add($periodTypeName, 1);
				}
			} else {
				$periodTypeId = $periodType['id'];
			}
		}

		return $periodTypeId;
	}

	/**
	 * @param string $generalType
	 * @param string|null $locationName
	 * @param bool $createMissingData
	 * @return int
	 * @throws Exception
	 */
	protected function handleLocationName(string $generalType, ?string $locationName, bool $createMissingData): int {
		if (!$locationName) {
			$locationId = 0;
		}
		else {
			if (!$location = $this->locationDir->getLocationByName($locationName)) {
				if (!$createMissingData) {
					throw new Exception(sprintf(__("Location with name '%s' could not be found.", 'orwokki-pr'), $locationName));
				}
				else {
					$locationId = $this->locationDir->add($generalType, $locationName, 1);
				}
			}
			else {
				$locationId = $location['id'];
			}
		}

		return $locationId;
	}

	/**
	 * @param string $filePath
	 * @return false|resource
	 * @throws Exception
	 */
	protected function openFileHandle(string $filePath) {
		if (!file_exists($filePath))
			throw new Exception(sprintf(__('Uploaded file %s does not exist', 'orwokki-pr'), $filePath));

		if (!$fp = fopen($filePath, "r+"))
			throw new Exception(sprintf(__('Could not open uploaded file %s for reading', 'orwokki-pr'), $filePath));

		flock($fp, LOCK_EX);

		return $fp;
	}

	protected function closeFileHandle($fp) {
		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}