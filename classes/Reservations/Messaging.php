<?php


namespace Orwokki\PlaceReservations\Reservations;

use Exception;

class Messaging
{
	private $adminReceiverEmail;
	private $subjectCustomer;
	private $contentCustomer;
	private $subjectAdmin;
	private $contentAdmin;

	public function __construct() {
		$this->adminReceiverEmail = get_option('oprSettingsEmailAdminReceiverEmail');
		$this->subjectCustomer = get_option('oprSettingsEmailCustomerSubject');
		$this->contentCustomer = get_option('oprSettingsEmailCustomerContent');
		$this->subjectAdmin = get_option('oprSettingsEmailAdminSubject');
		$this->contentAdmin = get_option('oprSettingsEmailAdminContent');
	}

	public function sendReservationAddedAdminMessage(Reservation $reservation) {
		if (!$this->subjectAdmin || !$this->contentAdmin || !$this->adminReceiverEmail)
			return;

		if (!$subject = $this->replaceTags($this->subjectAdmin, $reservation))
			return;

		if (!$content = $this->replaceTags($this->contentAdmin, $reservation))
			return;

		wp_mail($this->adminReceiverEmail, $subject, $content, [], []);
	}

	public function sendReservationAddedCustomerMessage(Reservation $reservation) {
		if (!$this->subjectCustomer || !$this->contentCustomer || !$reservation->getEmail())
			return;

		if (!$subject = $this->replaceTags($this->subjectCustomer, $reservation))
			return;

		if (!$content = $this->replaceTags($this->contentCustomer, $reservation))
			return;

		wp_mail($reservation->getEmail(), $subject, $content, [], []);
	}

	public function getValueReplacementTagsAndFields(): array {
		return [
			'placeName' => ['className' => 'Place', 'fieldName' => 'name', 'translation' => __('Place name', 'orwokki-pr')],
			'placeGeneralType' => ['className' => 'Place', 'fieldName' => 'generalType', 'translation' => __('General type', 'orwokki-pr')],
			'placeType' => ['className' => 'Place', 'fieldName' => 'placeTypeName', 'translation' => __('Place type', 'orwokki-pr')],
			'placeLocation' => ['className' => 'Place', 'fieldName' => 'locationName', 'translation' => __('Location', 'orwokki-pr')],
			'placePeriodType' => ['className' => 'Place', 'fieldName' => 'periodTypeName', 'translation' => __('Period type', 'orwokki-pr')],
			'placeLength' => ['className' => 'Place', 'fieldName' => 'length', 'translation' => __('Length', 'orwokki-pr')],
			'placeWidth' => ['className' => 'Place', 'fieldName' => 'width', 'translation' => __('Width', 'orwokki-pr')],
			'placeDepth' => ['className' => 'Place', 'fieldName' => 'depth', 'translation' => __('Depth', 'orwokki-pr')],
			'placeDescription' => ['className' => 'Place', 'fieldName' => 'depth', 'translation' => __('Description', 'orwokki-pr')],

			'reservationName' => ['className' => 'Reservation', 'fieldName' => 'name', 'translation' => __('Name', 'orwokki-pr')],
			'reservationEmail' => ['className' => 'Reservation', 'fieldName' => 'email', 'translation' => __('Email', 'orwokki-pr')],
			'reservationPhoneNumber' => ['className' => 'Reservation', 'fieldName' => 'phoneNumber', 'translation' => __('Phone number', 'orwokki-pr')],
			'reservationPeriodType' => ['className' => 'Reservation', 'fieldName' => 'periodTypeName', 'translation' => __('Period type', 'orwokki-pr')],
			'reservationStartTime' => ['className' => 'Reservation', 'fieldName' => 'periodStartTime', 'translation' => __('Reservation start time', 'orwokki-pr')],
			'reservationEndTime' => ['className' => 'Reservation', 'fieldName' => 'periodEndTime', 'translation' => __('Reservation end time', 'orwokki-pr')],
			'reservationAdditionalInfo' => ['className' => 'Reservation', 'fieldName' => 'additionalInfo', 'translation' => __('Additional information', 'orwokki-pr')],
		];
	}

	private function replaceTags(string $text, Reservation $reservation): string {
		try {
			$place = $reservation->getPlace();
		}
		catch (Exception $e) {
			return '';
		}

		$replacementTags = $this->getValueReplacementTagsAndFields();
		$replacementClasses = ['Place' => $place, 'Reservation' => $reservation];
		foreach ($replacementTags as $replacementTagName => $replacementData) {
			$getter = 'get'.ucfirst($replacementData['fieldName']);

			switch ($replacementData['fieldName']) {
				case 'periodStartTime':
				case 'periodEndTime':
					$value = $replacementClasses[$replacementData['className']]->$getter()->format('d.m.Y');
					break;
				default:
					$value = $replacementClasses[$replacementData['className']]->$getter();
			}

			$text = str_replace('{'.$replacementTagName.'}', $value, $text);
			$text = str_replace('{ '.$replacementTagName.' }', $value, $text);
		}

		return $text;
	}
}