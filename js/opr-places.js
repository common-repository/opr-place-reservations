function gatherPlaceFilterCriterias() {
    let data = {};

    document.querySelectorAll('input[rel="placesFilter"],select[rel="placesFilter"],textarea[rel="placesFilter"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button')
            data[e.getAttribute('id').split('-')[1]] = e.value;
    });

    return data;
}

function fetchAndShowAvailableFilteredPlaces() {
    const placesListContainer = document.querySelector('#placesListContainer');
    if (placesListContainer) {
        placesListContainer.innerHTML = '';
        let filterCriterias = gatherPlaceFilterCriterias();

        filterCriterias.action = 'opr_list_available_places';

        postAjax(filterCriterias, function (response) {
            if (response) {
                if (response.success) {
                    populatePlaces(response.data);
                    populateAddReservationFormPlaceList(response.data);
                }
                if (response.shouldNotify && response.messageType && response.message) {
                    alert(response.message);
                }
            }
        });

    }
}

function populatePlaces(data) {
    const places = data.places;
    const placeFieldTranslations = data.placeFieldTranslations;
    const customerViewSettings = data.customerViewSettings;
    const placesListContainer = document.querySelector('#placesListContainer');

    places.forEach(function(place, i) {
        let tableRow = document.createElement('tr');
        if (i % 2) {
            tableRow.setAttribute('class', 'even');
        }
        else {
            tableRow.setAttribute('class', 'odd');
        }

        // COLUMN for name
        let td = document.createElement('td');
        td.appendChild(document.createTextNode(place.name));
        tableRow.appendChild(td);

        // COLUMN for generalType
        if (customerViewSettings.settingsCustomerShowGeneralType) {
            td = document.createElement('td');
            td.appendChild(document.createTextNode(place.generalType));
            tableRow.appendChild(td);
        }

        // COLUMN for placeTypeName
        if (customerViewSettings.settingsCustomerShowPlaceType) {
            td = document.createElement('td');
            td.appendChild(document.createTextNode(place.placeTypeName));
            tableRow.appendChild(td);
        }

        // COLUMN for locationName
        if (customerViewSettings.settingsCustomerShowLocation) {
            td = document.createElement('td');
            td.appendChild(document.createTextNode(place.locationName));
            tableRow.appendChild(td);
        }

        // COLUMN for periodTypeName
        if (customerViewSettings.settingsCustomerShowPeriodType) {
            td = document.createElement('td');
            td.appendChild(document.createTextNode(place.periodTypeName));
            tableRow.appendChild(td);
        }

        // COLUMN for length
        if (customerViewSettings.settingsCustomerShowLength) {
            td = document.createElement('td');
            td.appendChild(document.createTextNode(place.length));
            tableRow.appendChild(td);
        }

        // COLUMN for width
        if (customerViewSettings.settingsCustomerShowWidth) {
            td = document.createElement('td');
            td.appendChild(document.createTextNode(place.width));
            tableRow.appendChild(td);
        }

        // COLUMN for depth
        if (customerViewSettings.settingsCustomerShowDepth) {
            td = document.createElement('td');
            td.appendChild(document.createTextNode(place.depth));
            tableRow.appendChild(td);
        }

        // COLUMN for description
        if (customerViewSettings.settingsCustomerShowDescription) {
            td = document.createElement('td');
            td.appendChild(document.createTextNode(place.description));
            tableRow.appendChild(td);
        }

        // COLUMN for actions
        let reserveButton = document.createElement('input');
        reserveButton.setAttribute('type', 'button');
        reserveButton.setAttribute('id', 'reservePlace-'+place.id);
        reserveButton.setAttribute('class', 'button button-primary button-reserve-place');
        reserveButton.setAttribute('data-periodTypeId', place.periodTypeId);
        reserveButton.value = placeFieldTranslations['reserveButton'].caption;
        reserveButton.addEventListener('click', reservePlace);
        let p = document.createElement('p');
        p.appendChild(reserveButton);
        td = document.createElement('td');
        td.appendChild(p);
        tableRow.appendChild(td);

        if (placesListContainer)
            placesListContainer.appendChild(tableRow);
    });
}
function populateAddReservationFormPlaceList(data) {
    const places = data.places;
    const selectInput = document.querySelector('#addNewReservation-placeId');

    if (selectInput)
        selectInput.innerHTML = '<option></option>';

    places.forEach(function(place) {
        let option = document.createElement('option');
        option.setAttribute('value', place.id);
        option.setAttribute('data-periodTypeId', place.periodTypeId);
        option.appendChild(document.createTextNode(place.name));

        if (selectInput)
            selectInput.appendChild(option);
    });
}

function reservePlace(e) {
    e.preventDefault();
    const button = e.target;
    const placeId = button.getAttribute('id').split('-')[1];
    const periodTypeId = button.getAttribute('data-periodTypeId');
    const placeIdField = document.querySelector('#addNewReservation-placeId');
    const periodTypeIdField = document.querySelector('#addNewReservation-periodTypeId');

    if (placeIdField)
        placeIdField.value = placeId;

    if (periodTypeIdField)
        periodTypeIdField.value = periodTypeId;

    window.location.href = window.location.href + '#addNewReservationFormContainer';
}

function gatherNewReservationValues() {
    let data = {};

    document.querySelectorAll('input[rel="addNewReservation"],select[rel="addNewReservation"],textarea[rel="addNewReservation"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button')
            data[e.getAttribute('id').split('-')[1]] = e.value;
    });

    return data;
}

function resetNewReservationValues() {
    document.querySelectorAll('input[rel="addNewReservation"],select[rel="addNewReservation"],textarea[rel="addNewReservation"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button')
            e.value = '';
    });
}

function addReservation(e) {
    let data = gatherNewReservationValues();
    data.action = 'opr_add_reservation';

    postAjax(data, function(response) {
        if (response) {
            if (response.success) {
                resetNewReservationValues();
                fetchAndShowAvailableFilteredPlaces();
            }
            if (response.shouldNotify && response.messageType && response.message) {
                alert(response.message);
            }
        }
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