function gatherReservationFilterCriterias() {
    let data = {};

    document.querySelectorAll('input[rel="reservationsFilter"],select[rel="reservationsFilter"],textarea[rel="reservationsFilter"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button')
            data[e.getAttribute('id').split('-')[1]] = e.value;
    });

    return data;
}

function gatherNewReservationValues() {
    let data = {};

    document.querySelectorAll('input[rel="addNewReservation"],select[rel="addNewReservation"],textarea[rel="addNewReservation"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button')
            data[e.getAttribute('id').split('-')[1]] = e.value;
    });

    return data;
}

function fetchAndShowReservations() {
    const reservationsListContainer = document.querySelector('#reservationsListContainer');
    if (reservationsListContainer) {
        reservationsListContainer.innerHTML = '';

        let filterCriterias = gatherReservationFilterCriterias();

        filterCriterias.action = 'opr_list_active_reservations';

        postAjax(filterCriterias, function (response) {
            if (response) {
                if (response.success) {
                    populateReservations(response.data);
                }
                if (response.shouldNotify && response.messageType && response.message) {
                    notification(response.messageType, response.message);
                }
            }
        });

    }
}

function showMoreReservations() {
    const reservationsListContainer = document.querySelector('#reservationsListContainer');
    if (reservationsListContainer) {
        showSpinner();

        let filterCriterias = gatherReservationFilterCriterias();
        filterCriterias.shownReservationsIds = gatherShownItemIds('reservations').join(',');
        filterCriterias.action = 'opr_list_active_reservations';

        postAjax(filterCriterias, function (response) {
            if (response) {
                if (response.success) {
                    removeShowMoreButtonRow();
                    populateReservations(response.data);
                    hideSpinner();
                }
                if (response.shouldNotify && response.messageType && response.message) {
                    notification(response.messageType, response.message);
                }
            }
        });
    }
}

function appendReservation(reservation, reservationsListContainer, reservationFieldTranslations) {
    if ((reservation) && (reservationsListContainer)) {
        let tableRow = document.createElement('tr');
        tableRow.setAttribute('rel', 'reservations');
        tableRow.setAttribute('id', 'reservation-'+reservation.id);

        // COLUMN for placeName
        let td = document.createElement('td');
        td.appendChild(document.createTextNode(reservation.placeName));
        tableRow.appendChild(td);

        // COLUMN for periodTypeName
        td = document.createElement('td');
        td.appendChild(document.createTextNode(reservation.periodTypeName));
        tableRow.appendChild(td);

        // COLUMN for name
        td = document.createElement('td');
        td.appendChild(document.createTextNode(reservation.name));
        tableRow.appendChild(td);

        // COLUMN for email
        td = document.createElement('td');
        td.appendChild(document.createTextNode(reservation.email));
        tableRow.appendChild(td);

        // COLUMN for phoneNumber
        td = document.createElement('td');
        td.appendChild(document.createTextNode(reservation.phoneNumber));
        tableRow.appendChild(td);

        // COLUMN for periodStartTime
        td = document.createElement('td');
        td.appendChild(document.createTextNode(reservation.periodStartTime));
        tableRow.appendChild(td);

        // COLUMN for periodEndTime
        td = document.createElement('td');

        let periodEndTime = '';
        if (reservation.periodEndTime !== '0000-00-00 00:00:00')
            periodEndTime = reservation.periodEndTime;

        td.appendChild(document.createTextNode(periodEndTime));
        tableRow.appendChild(td);

        // COLUMN for additionalInfo
        td = document.createElement('td');
        td.appendChild(document.createTextNode(reservation.additionalInfo));
        tableRow.appendChild(td);

        // COLUMN for actions
        let removeButton = document.createElement('input');
        removeButton.setAttribute('type', 'button');
        removeButton.setAttribute('id', 'removeReservation-'+reservation.id);
        removeButton.setAttribute('class', 'button button-primary button-remove-reservation');
        removeButton.value = reservationFieldTranslations['removeButton'].caption;
        removeButton.addEventListener('click', removeReservation);
        let p = document.createElement('p');
        p.appendChild(removeButton);
        td = document.createElement('td');
        td.appendChild(p);
        tableRow.appendChild(td);

        reservationsListContainer.appendChild(tableRow);
    }
}

function populateReservations(data) {
    const reservations = data.reservations;
    const reservationFieldTranslations = data.reservationFieldTranslations;
    const reservationsListContainer = document.querySelector('#reservationsListContainer');

    reservations.forEach(function(reservation) {
        appendReservation(reservation, reservationsListContainer, reservationFieldTranslations);
    });

    if (reservations.length > 0)
        appendShowMoreButton(reservationsListContainer, reservationFieldTranslations, showMoreReservations);
}

function addReservation(e) {
    let data = gatherNewReservationValues();
    data.action = 'opr_add_reservation';

    postAjax(data, function(response) {
        if (response) {
            if (response.success) {
                updateAddReservationPlacesList();
                resetAddReservationForm();
                fetchAndShowReservations();
            }
            if (response.shouldNotify && response.messageType && response.message) {
                notification(response.messageType, response.message);
            }
        }
    });
}

function removeReservation(e) {
    e.preventDefault();
    const button = e.target;
    const reservationId = button.getAttribute('id').split('-')[1];

    if (window.translationsFetched) {
        if (confirm(window.adminTranslations['CONFIRM_RESERVATION_REMOVE'])) {
            let data = {action: 'opr_remove_reservation', reservationId: reservationId};
            postAjax(data, function (response) {
                if (response) {
                    if (response.success) {
                        updateAddReservationPlacesList();
                        fetchAndShowReservations();
                    }
                    if (response.shouldNotify && response.messageType && response.message) {
                        notification(response.messageType, response.message);
                    }
                }
            });
        }
    }
}

function populateAddReservationFormPlaceList(data) {
    const places = data.places;
    const selectInput = document.querySelector('#addNewReservation-placeId');

    if (selectInput)
        selectInput.innerHTML = '<option></option>';

    places.forEach(function(place) {
        let option = document.createElement('option');
        option.setAttribute('value', place.id)
        option.appendChild(document.createTextNode(place.name));

        if (selectInput)
            selectInput.appendChild(option);
    });
}

function updateAddReservationPlacesList() {
    let data = {action: 'opr_list_available_places'};
    postAjax(data, function (response) {
        if ((response) && (response.success)) {
            populateAddReservationFormPlaceList(response.data);
        }
    });
}

function resetAddReservationForm() {
    document.querySelectorAll('input[rel="addNewReservation"],select[rel="addNewReservation"],textarea[rel="addNewReservation"]').forEach(function (e,i) {
        e.value = '';
    });
}

function updatePeriodTypeValueByPlace() {
    const placeInput = document.querySelector('#addNewReservation-placeId');
    const periodTypeInput = document.querySelector('#addNewReservation-periodTypeId');

    if ((placeInput) && (periodTypeInput) && (placeInput.value)) {
        allPlaceInputOptions = document.querySelectorAll('#addNewReservation-placeId option');
        if (allPlaceInputOptions) {
            let placePeriodTypeId = 0;
            allPlaceInputOptions.forEach(function (e,i) {
                if (e.value === placeInput.value) {
                    placePeriodTypeId = parseInt(e.getAttribute('data-periodTypeId'),10);
                }
            });
            if (placePeriodTypeId) {
                periodTypeInput.value = placePeriodTypeId;
            }
        }
    }
}