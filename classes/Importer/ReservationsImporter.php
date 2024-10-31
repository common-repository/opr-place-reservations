<?php

namespace Orwokki\PlaceReservations\Importer;

use Exception;
use Orwokki\PlaceReservations\Places\Place;

class ReservationsImporter extends FileImporter
{
	/**
	 * @param string $filePath
	 * @param bool $removeExistingReservationsFromPlace
	 * @param bool $createMissingData
	 * @param bool $replaceExisting
	 * @throws Exception
	 */
	public function importCsv(string $filePath, bool $removeExistingReservationsFromPlace = false) {
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

			$placeName = $data[6];

			try {
				$place = $this->getPlace($placeName);
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
		) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $placeName
	 * @return Place|null
	 * @throws Exception
	 */
	private function getPlace(string $placeName) {
		$place = $this->placeDir->getPlaceByName($placeName);

		if (!$place || !$place->isActive() || $place->isDeleted()) {
			throw new Exception(sprintf(__("Could not find place with name '%s'.", 'orwokki-pr'), $placeName));
		}

		return $place;
	}
}