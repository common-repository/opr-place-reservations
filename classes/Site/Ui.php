<?php

namespace Orwokki\PlaceReservations\Site;

use Orwokki\PlaceReservations\Places\LocationDirectory;
use Orwokki\PlaceReservations\Places\Place;
use Orwokki\PlaceReservations\Places\PlaceDirectory;
use Orwokki\PlaceReservations\Places\PlaceTypeDirectory;
use Orwokki\PlaceReservations\Reservations\PeriodTypeDirectory;

class Ui
{
	private $placeTypeDir;
	private $locationDir;
	private $periodTypeDir;
	private $placeDir;

	public function __construct() {
		$this->placeTypeDir = new PlaceTypeDirectory;
		$this->locationDir = new LocationDirectory;
		$this->periodTypeDir = new PeriodTypeDirectory;
		$this->placeDir = new PlaceDirectory;
	}

	public function showPlacesList() {
		$html = '<table class="wp-list-table widefat fixed striped tablepress" id="placesListTable">';
		$html .= '<thead><tr>';
		$html .= sprintf('<th>%s</th>', __('Place name', 'orwokki-pr'));

		if (get_option('oprSettingsCustomerShowGeneralType')) {
			$html .= sprintf('<th>%s</th>', __('General type', 'orwokki-pr'));
		}

		if (get_option('oprSettingsCustomerShowPlaceType')) {
			$html .= sprintf('<th>%s</th>', __('Place type', 'orwokki-pr'));
		}

		if (get_option('oprSettingsCustomerShowLocation')) {
			$html .= sprintf('<th>%s</th>', __('Location', 'orwokki-pr'));
		}

		if (get_option('oprSettingsCustomerShowPeriodType')) {
			$html .= sprintf('<th>%s</th>', __('Period type', 'orwokki-pr'));
		}

		if (get_option('oprSettingsCustomerShowLength')) {
			$html .= sprintf('<th>%s</th>', __('Length', 'orwokki-pr'));
		}

		if (get_option('oprSettingsCustomerShowWidth')) {
			$html .= sprintf('<th>%s</th>', __('Width', 'orwokki-pr'));
		}

		if (get_option('oprSettingsCustomerShowDepth')) {
			$html .= sprintf('<th>%s</th>', __('Depth', 'orwokki-pr'));
		}

		if (get_option('oprSettingsCustomerShowDescription')) {
			$html .= sprintf('<th>%s</th>', __('Description', 'orwokki-pr'));
		}

		$html .= sprintf('<th>%s</th>', __('Actions', 'orwokki-pr'));
		$html .= '</tr></thead>';
		$html .= '<tbody class="row-hover" id="placesListContainer"></tbody>';
		$html .= '</table>';

		return $html;
	}

	public function showPlacesFilterForm() {
		$html = '<table id="placesFilterFormTable">';
		$html .= '<tbody><tr>';

		if (get_option('oprSettingsCustomerShowGeneralType')) {
			$html .= '<td>';
			$html .= $this->showGeneralTypeField('placesFilter-generalType', 'placesFilter');
			$html .= '</td>';
		}

		if (get_option('oprSettingsCustomerShowPlaceType')) {
			$html .= sprintf('<td><select class="opr-filter-select" rel="placesFilter" id="placesFilter-placeTypeId" placeholder="%s" aria-placeholder="%s">', __('Place type', 'orwokki-pr'), __('Place type', 'orwokki-pr'));
			$html .= sprintf('<option>%s</option>', __('Place type', 'orwokki-pr'));
			foreach ($this->placeTypeDir->listActivePlaceTypes() as $placeType) {
				$html .= sprintf('<option value="%d">%s</option>', $placeType['id'], $placeType['name']);
			}
			$html .= '</select></td>';
		}

		if (get_option('oprSettingsCustomerShowLocation')) {
			$html .= sprintf('<td><select class="opr-filter-select" rel="placesFilter" id="placesFilter-locationId" placeholder="%s" aria-placeholder="%s">', __('Location', 'orwokki-pr'), __('Location', 'orwokki-pr'));
			$html .= sprintf('<option>%s</option>', __('Location', 'orwokki-pr'));
			foreach ($this->locationDir->listActiveLocations() as $location) {
				$html .= sprintf('<option value="%d">%s</option>', $location['id'], $location['name']);
			}
			$html .= '</select></td>';
		}

		if (get_option('oprSettingsCustomerShowPeriodType')) {
			$html .= sprintf('<td><select class="opr-filter-select" rel="placesFilter" id="placesFilter-periodTypeId" placeholder="%s" aria-placeholder="%s">', __('Period type', 'orwokki-pr'), __('Period type', 'orwokki-pr'));
			$html .= sprintf('<option>%s</option>', __('Period type', 'orwokki-pr'));
			foreach ($this->periodTypeDir->listActivePeriodTypes() as $periodType) {
				$html .= sprintf('<option value="%d">%s</option>', $periodType['id'], $periodType['name']);
			}
			$html .= '</select></td>';
		}

		if (get_option('oprSettingsCustomerShowLength')) {
			$html .= sprintf('<td><input type="number" class="opr-filter-number" step="0.1" rel="placesFilter" id="placesFilter-length" placeholder="%s" aria-placeholder="%s" /></td>', __('Length', 'orwokki-pr'), __('Length', 'orwokki-pr'));
		}

		if (get_option('oprSettingsCustomerShowWidth')) {
			$html .= sprintf('<td><input type="number" class="opr-filter-number" step="0.1" rel="placesFilter" id="placesFilter-width" placeholder="%s" aria-placeholder="%s" /></td>', __('Width', 'orwokki-pr'), __('Width', 'orwokki-pr'));
		}

		if (get_option('oprSettingsCustomerShowDepth')) {
			$html .= sprintf('<td><input type="number" class="opr-filter-number" step="0.1" rel="placesFilter" id="placesFilter-depth" placeholder="%s" aria-placeholder="%s" /></td>', __('Depth', 'orwokki-pr'), __('Depth', 'orwokki-pr'));
		}

		$html .= sprintf('<td><input type="button" class="button button-primary" id="placesFilter-action-filter" value="%s" aria-label="%s" /></td>', __('Filter', 'orwokki-pr'), __('Filter', 'orwokki-pr'));

		$html .= '</tr></tbody>';
		$html .= '</table>';

		return $html;
	}

	public function showAddReservationForm() {
		$html = '<div id="addNewReservationFormContainer">';
		$html .= sprintf('<h3>%s</h3>', __('Make reservation', 'orwokki-pr'));
		$html .= '<form id="addNewReservationForm">';
		$html .= '<table><tbody>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-placeId">%s</label></th>', __('Place', 'orwokki-pr'));
		$html .= sprintf('<td><select rel="addNewReservation" id="addNewReservation-placeId" name="addNewReservation-placeId" placeholder="%s" aria-placeholder="%s">', __('Place', 'orwokki-pr'), __('Place', 'orwokki-pr'));
		$html .= '<option></option>';
		foreach ($this->placeDir->listActiveFreePlacesArray() as $placeData) {
			$html .= sprintf('<option value="%d" data-periodTypeId="%d">%s</option>', $placeData['id'], $placeData['periodTypeId'], $placeData['name']);
		}
		$html .= '</select></td>';
		$html .= '</tr>';

		if (get_option('oprSettingsCustomerShowPeriodType')) {
			$html .= '<tr>';
			$html .= sprintf('<th><label for="addNewReservation-periodTypeId">%s</label></th>', __('Period type', 'orwokki-pr'));
			$html .= sprintf('<td><select rel="addNewReservation" id="addNewReservation-periodTypeId" name="addNewReservation-periodTypeId" placeholder="%s" aria-placeholder="%s">', __('Period type', 'orwokki-pr'), __('Period type', 'orwokki-pr'));
			$html .= '<option></option>';
			foreach ($this->periodTypeDir->listActivePeriodTypes() as $periodType) {
				$html .= sprintf('<option value="%d">%s</option>', $periodType['id'], $periodType['name']);
			}
			$html .= '</select></td>';
			$html .= '</tr>';
		}

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-name">%s</label></th>', __('Name', 'orwokki-pr'));
		$html .= sprintf('<td><input type="text" rel="addNewReservation" id="addNewReservation-name" name="addNewReservation-name" placeholder="%s" aria-placeholder="%s" /></td>', __('Name', 'orwokki-pr'), __('Name', 'orwokki-pr'));
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-email">%s</label></th>', __('Email', 'orwokki-pr'));
		$html .= sprintf('<td><input type="text" rel="addNewReservation" id="addNewReservation-email" name="addNewReservation-email" placeholder="%s" aria-placeholder="%s" /></td>', __('Email', 'orwokki-pr'), __('Email', 'orwokki-pr'));
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-phoneNumber">%s</label></th>', __('Phone number', 'orwokki-pr'));
		$html .= sprintf('<td><input type="text" rel="addNewReservation" id="addNewReservation-phoneNumber" name="addNewReservation-phoneNumber" placeholder="%s" aria-placeholder="%s" /></td>', __('Phone number', 'orwokki-pr'), __('Phone number', 'orwokki-pr'));
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-periodStartTime">%s</label></th>', __('Reservation start time', 'orwokki-pr'));
		$html .= sprintf('<td><input type="date" rel="addNewReservation" id="addNewReservation-periodStartTime" name="addNewReservation-periodStartTime" placeholder="%s" aria-placeholder="%s" /></td>', __('Reservation start time', 'orwokki-pr'), __('Reservation start time', 'orwokki-pr'));
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-periodEndTime">%s</label></th>', __('Reservation end time', 'orwokki-pr'));
		$html .= sprintf('<td><input type="date" rel="addNewReservation" id="addNewReservation-periodEndTime" name="addNewReservation-periodEndTime" placeholder="%s" aria-placeholder="%s" /></td>', __('Reservation end time', 'orwokki-pr'), __('Reservation end time', 'orwokki-pr'));
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= sprintf('<th><label for="addNewReservation-additionalInfo">%s</label></th>', __('Additional information', 'orwokki-pr'));
		$html .= '<td><textarea rel="addNewReservation" id="addNewReservation-additionalInfo" name="addNewReservation-additionalInfo"></textarea>';
		$html .= '</tr>';

		$html .= '</tbody></table>';
		$html .= sprintf('<p><input type="button" class="button button-primary" id="addNewReservation-action-add" value="%s" aria-label="%s" /></p>', __('Make reservation', 'orwokki-pr'), __('Add', 'orwokki-pr'));
		$html .= '</form></div>';

		return $html;
	}

	private function showGeneralTypeField($id, $rel = null, $showEmptyOptionPlaceHolder = true) {
		if ($rel)
			$rel = ' rel="'.$rel.'"';

		$html = sprintf('<select id="%s"%s placeholder="%s" aria-placeholder="%s">', $id, $rel, __('General type', 'orwokki-pr'), __('General type', 'orwokki-pr'));

		if ($showEmptyOptionPlaceHolder)
			$html .= sprintf('<option>%s</option>', __('General type', 'orwokki-pr'));

		$html .= sprintf('<option>%s</option>', __(Place::PLACE_GENERAL_TYPE_MARINA, 'orwokki-pr'));
		$html .= sprintf('<option>%s</option>', __(Place::PLACE_GENERAL_TYPE_PARKING, 'orwokki-pr'));
		$html .= '</select>';

		return $html;
	}
}