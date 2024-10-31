<?php

namespace Orwokki\PlaceReservations\Importer;

use Exception;
use Orwokki\PlaceReservations\Places\Place;

class ReservationsWithPlacesImporter extends FileImporter
{
	/**
	 * @param string $filePath
	 * @param bool $removeExistingReservationsFromPlace
	 * @param bool $createMissingData
	 * @param bool $replaceExisting
	 * @throws Exception
	 */
	public function importCsv(string $filePath, bool $removeExistingReservationsFromPlace = false, bool $createMissingData = false, bool $replaceExisting = false) {
		global $wpdb;
		$wpdb->query('START TRANSACTION');

		$fp = $this->openFileHandle($filePath);

		$rowNumber = 1;
		while (($data = fgetcsv($fp, 0, ";", '"', "\\")) !== false) {
			if ($this->isCsvHeaderRow($data)) {
				$rowNumber++;
				continue;
			}

			$reservationData = [
				'active' => 1,
				'name' => substr(sanitize_text_field($data[0]), 0, 255),
				'email' => substr(sanitize_text_field($data[1]), 0, 255),
				'phoneNumber' => substr(sanitize_text_field($data[2]), 0, 255),
				'periodStartTime' => sanitize_text_field($data[3]),
				'periodEndTime' => sanitize_text_field($data[4]),
				'additionalInfo' => sanitize_textarea_field($data[5]),
			];

			$placeData = [
				'name' => substr(sanitize_text_field($data[6]), 0, 255),
				'generalType' => sanitize_text_field($data[7]),
				'placeTypeName' => sanitize_text_field($data[8]),
				'periodTypeName' => sanitize_text_field($data[9]),
				'locationName' => sanitize_text_field($data[10]),
				'length' => (float) str_replace(',', '.', sanitize_text_field($data[11])),
				'width' => (float) str_replace(',', '.', sanitize_text_field($data[12])),
				'depth' => (float) str_replace(',', '.', sanitize_text_field($data[13])),
				'description' => sanitize_textarea_field($data[14]),
			];

			try {
				$place = $this->handlePlace(
					$placeData['name'],
					$placeData['generalType'],
					$placeData['placeTypeName'],
					$placeData['periodTypeName'],
					$placeData['locationName'],
					$placeData['length'],
					$placeData['width'],
					$placeData['depth'],
					$placeData['description'],
					$removeExistingReservationsFromPlace,
					$createMissingData,
					$replaceExisting
				);
				$this->handleReservation($reservationData, $place, $removeExistingReservationsFromPlace);

			}
			catch (Exception $e) {
				$wpdb->query('ROLLBACK');
				$this->closeFileHandle($fp);
				throw new Exception(sprintf(__("Importing row #%d with reservation name '%s' failed: %s", 'orwokki-pr'), $rowNumber, $reservationData['name'], $e->getMessage()));
			}

			$rowNumber++;
		}

		$this->closeFileHandle($fp);
		$wpdb->query('COMMIT');
	}

	private function isCsvHeaderRow(array $data): bool {
		if (
			$data[0] == 'Name'
			&& $data[1] == 'Email'
			&& $data[2] == 'Phone Number'
			&& $data[3] == 'Reservation start time'
			&& $data[4] == 'Reservation end time'
			&& $data[5] == 'Additional information on reservation'
			&& $data[6] == 'Place name'
			&& $data[7] == 'General type'
			&& $data[8] == 'Place type'
			&& $data[9] == 'Period type'
			&& $data[10] == 'Location'
			&& $data[11] == 'Length'
			&& $data[12] == 'Width'
			&& $data[13] == 'Depth'
			&& $data[14] == 'Description of place'
		) {
			return true;
		}

		return false;
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
	 * @param bool $removeExistingReservationsFromPlace
	 * @param bool $createMissingData
	 * @param bool $replaceExisting
	 * @return Place
	 * @throws Exception
	 */
	protected function handlePlace(string $name, string $generalType, string $placeTypeName, string $periodTypeName, string $locationName, float $length, float $width, float $depth, string $description, bool $removeExistingReservationsFromPlace, bool $createMissingData, bool $replaceExisting): Place {
		// Create new place if place with the given name is not found
		if (!$existingPlace = $this->placeDir->getPlaceByName($name)) {
			return $this->createAndStoreNewPlace($name, $generalType, $placeTypeName, $periodTypeName, $locationName, $length, $width, $depth, $description, $createMissingData);
		}
		// Replace values if it's allowed and if something has changed
		else if (
			($replaceExisting) && (
				(($generalType) && ($existingPlace->getGeneralType() != $generalType))
				|| (($placeTypeName) && ($existingPlace->getPlaceTypeName() != $placeTypeName))
				|| (($periodTypeName) && ($existingPlace->getPeriodTypeName() != $periodTypeName))
				|| (($locationName) && ($existingPlace->getLocationName() != $locationName))
				|| (($length) && ($existingPlace->getLength() != $length))
				|| (($width) && ($existingPlace->getWidth() != $width))
				|| (($depth) && ($existingPlace->getDepth() != $depth))
				|| (($description) && ($existingPlace->getDescription() != $description))
			)
		) {
			if ($existingReservation = $this->reservationDir->getReservationByPlaceId($existingPlace->getId())) {
				if ($existingReservation->isActive()) {
					if ($removeExistingReservationsFromPlace) {
						$this->reservationDir->delete($existingReservation);
						$this->placeDir->delete($existingPlace);
						return $this->createAndStoreNewPlace($name, $generalType, $placeTypeName, $periodTypeName, $locationName, $length, $width, $depth, $description, $createMissingData);
					}
					else {
						throw new Exception(sprintf(__("Place '%s' has existing reservation it cannot be changed.", 'orwokki-pr'), $existingPlace->getName()));
					}
				}
			}
			else {
				$this->placeDir->delete($existingPlace);
				return $this->createAndStoreNewPlace($name, $generalType, $placeTypeName, $periodTypeName, $locationName, $length, $width, $depth, $description, $createMissingData);
			}
		}

		return $existingPlace;
	}
}