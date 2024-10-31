function documentReady(e) {
    window.translationsFetched = false;
    fetchAdminTranslations();
    fetchAndShowPlaceTypes();
    fetchAndShowLocations();
    fetchAndShowPeriodTypes();
    fetchAndShowFilteredPlaces();
    fetchAndShowReservations();

    const addPlaceTypeButton = document.querySelector('#placeTypeSubmitButton-new');
    if (addPlaceTypeButton)
        addPlaceTypeButton.addEventListener('click', submitPlaceType);

    const addLocationButton = document.querySelector('#locationSubmitButton-new');
    if (addLocationButton)
        addLocationButton.addEventListener('click', submitLocation);

    const addPeriodTypeButton = document.querySelector('#periodTypeSubmitButton-new');
    if (addPeriodTypeButton)
        addPeriodTypeButton.addEventListener('click', submitPeriodType);

    const addPlaceButton = document.querySelector('#addNewPlace-action-add');
    if (addPlaceButton)
        addPlaceButton.addEventListener('click', addPlace);

    const addReservationButton = document.querySelector('#addNewReservation-action-add');
    if (addReservationButton)
        addReservationButton.addEventListener('click', addReservation);

    const filterPlacesListButton = document.querySelector('#placesFilter-action-filter');
    if (filterPlacesListButton)
        filterPlacesListButton.addEventListener('click', fetchAndShowFilteredPlaces);

    const filterReservationsListButton = document.querySelector('#reservationsFilter-action-filter');
    if (filterReservationsListButton)
        filterReservationsListButton.addEventListener('click', fetchAndShowReservations);

    const settingsCustomerViewSaveButton = document.querySelector('#settingsCustomerViewSaveButton');
    if (settingsCustomerViewSaveButton)
        settingsCustomerViewSaveButton.addEventListener('click', saveCustomerViewSettings);

    const settingsReservationSaveButton = document.querySelector('#settingsReservationSaveButton');
    if (settingsReservationSaveButton)
        settingsReservationSaveButton.addEventListener('click', saveReservationSettings);

    const settingsEmailSenderSaveButton = document.querySelector('#settingsEmailSenderSaveButton');
    if (settingsEmailSenderSaveButton)
        settingsEmailSenderSaveButton.addEventListener('click', saveEmailSenderSettings);

    const settingsCustomerEmailContentSaveButton = document.querySelector('#settingsCustomerEmailContentSaveButton');
    if (settingsCustomerEmailContentSaveButton)
        settingsCustomerEmailContentSaveButton.addEventListener('click', saveCustomerEmailContentSettings);

    const settingsAdminEmailContentSaveButton = document.querySelector('#settingsAdminEmailContentSaveButton');
    if (settingsAdminEmailContentSaveButton)
        settingsAdminEmailContentSaveButton.addEventListener('click', saveAdminEmailContentSettings);

    const importReservationsWithPlacesButton = document.querySelector('#importReservationsWithPlacesButton');
    if (importReservationsWithPlacesButton)
        importReservationsWithPlacesButton.addEventListener('click', importCsvReservationsWithPlaces);

    const importReservationsButton = document.querySelector('#importReservationsButton');
    if (importReservationsButton)
        importReservationsButton.addEventListener('click', importCsvReservations);

    const importPlacesButton = document.querySelector('#importPlacesButton');
    if (importPlacesButton)
        importPlacesButton.addEventListener('click', importCsvPlaces);

    const placeInput = document.querySelector('#addNewReservation-placeId');
    if (placeInput)
        placeInput.addEventListener('change', updatePeriodTypeValueByPlace);

}
document.addEventListener('DOMContentLoaded', documentReady, false);