function gatherPlaceFilterCriterias() {
    let data = {};

    document.querySelectorAll('input[rel="placesFilter"],select[rel="placesFilter"],textarea[rel="placesFilter"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button')
            data[e.getAttribute('id').split('-')[1]] = e.value;
    });

    return data;
}
function gatherNewPlaceValues() {
    let data = {};

    document.querySelectorAll('input[rel="addNewPlace"],select[rel="addNewPlace"],textarea[rel="addNewPlace"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button')
            data[e.getAttribute('id').split('-')[1]] = e.value;
    });

    return data;
}

function fetchAndShowFilteredPlaces() {
    const placesListContainer = document.querySelector('#placesListContainer');
    if (placesListContainer) {
        placesListContainer.innerHTML = '';
        let filterCriterias = gatherPlaceFilterCriterias();

        filterCriterias.action = 'opr_list_all_places';

        postAjax(filterCriterias, function (response) {
            if (response) {
                if (response.success) {
                    populatePlaces(response.data);
                }
                if (response.shouldNotify && response.messageType && response.message) {
                    notification(response.messageType, response.message);
                }
            }
        });

    }
}

function showMorePlaces() {
    const reservationsListContainer = document.querySelector('#placesListContainer');
    if (reservationsListContainer) {
        showSpinner();

        let filterCriterias = gatherPlaceFilterCriterias();
        filterCriterias.shownPlacesIds = gatherShownItemIds('places').join(',');
        filterCriterias.action = 'opr_list_all_places';

        postAjax(filterCriterias, function (response) {
            if (response) {
                if (response.success) {
                    removeShowMoreButtonRow();
                    populatePlaces(response.data);
                    hideSpinner();
                }
                if (response.shouldNotify && response.messageType && response.message) {
                    notification(response.messageType, response.message);
                }
            }
        });
    }
}

function appendPlace(place, placesListContainer, placeFieldTranslations) {
    if ((place) && (placesListContainer)) {
        let tableRow = document.createElement('tr');
        tableRow.setAttribute('rel', 'places');
        tableRow.setAttribute('id', 'place-'+place.id);

        // COLUMN for name
        let td = document.createElement('td');
        td.appendChild(document.createTextNode(place.name));
        tableRow.appendChild(td);

        // COLUMN for generalType
        td = document.createElement('td');
        td.appendChild(document.createTextNode(place.generalType));
        tableRow.appendChild(td);

        // COLUMN for placeTypeName
        td = document.createElement('td');
        td.appendChild(document.createTextNode(place.placeTypeName));
        tableRow.appendChild(td);

        // COLUMN for locationName
        td = document.createElement('td');
        td.appendChild(document.createTextNode(place.locationName));
        tableRow.appendChild(td);

        // COLUMN for periodTypeName
        td = document.createElement('td');
        td.appendChild(document.createTextNode(place.periodTypeName));
        tableRow.appendChild(td);

        // COLUMN for length
        td = document.createElement('td');
        td.appendChild(document.createTextNode(place.length));
        tableRow.appendChild(td);

        // COLUMN for width
        td = document.createElement('td');
        td.appendChild(document.createTextNode(place.width));
        tableRow.appendChild(td);

        // COLUMN for depth
        td = document.createElement('td');
        td.appendChild(document.createTextNode(place.depth));
        tableRow.appendChild(td);

        // COLUMN for description
        td = document.createElement('td');
        td.appendChild(document.createTextNode(place.description));
        tableRow.appendChild(td);

        // COLUMN for actions
        let removeButton = document.createElement('input');
        removeButton.setAttribute('type', 'button');
        removeButton.setAttribute('id', 'removePlace-' + place.id);
        removeButton.setAttribute('class', 'button button-primary button-remove-place');
        removeButton.value = placeFieldTranslations['removeButton'].caption;
        removeButton.addEventListener('click', removePlace);
        let p = document.createElement('p');
        p.appendChild(removeButton);
        td = document.createElement('td');
        td.appendChild(p);
        tableRow.appendChild(td);

        placesListContainer.appendChild(tableRow);
    }
}

function populatePlaces(data) {
    const places = data.places;
    const placeFieldTranslations = data.placeFieldTranslations;
    const placesListContainer = document.querySelector('#placesListContainer');

    places.forEach(function(place) {
        appendPlace(place, placesListContainer, placeFieldTranslations);
    });
    if (places.length > 0)
        appendShowMoreButton(placesListContainer, placeFieldTranslations, showMorePlaces);
}

function addPlace(e) {
    let data = gatherNewPlaceValues();
    data.action = 'opr_add_place';

    postAjax(data, function(response) {
        if (response) {
            if (response.success) {
                resetAddPlaceForm();
                fetchAndShowFilteredPlaces();
            }
            if (response.shouldNotify && response.messageType && response.message) {
                notification(response.messageType, response.message);
            }
        }
    });
}

function removePlace(e) {
    e.preventDefault();
    const button = e.target;
    const placeId = button.getAttribute('id').split('-')[1];

    if (window.translationsFetched) {
        if (confirm(window.adminTranslations['CONFIRM_PLACE_REMOVE'])) {
            let data = {action: 'opr_remove_place', placeId: placeId};
            postAjax(data, function(response) {
                if (response) {
                    if (response.success) {
                        fetchAndShowFilteredPlaces();
                    }
                    if (response.shouldNotify && response.messageType && response.message) {
                        notification(response.messageType, response.message);
                    }
                }
            });
        }
    }
}

function resetAddPlaceForm() {
    document.querySelectorAll('input[rel="addNewPlace"],select[rel="addNewPlace"],textarea[rel="addNewPlace"]').forEach(function (e,i) {
        if (e.getAttribute('id') === 'addNewPlace-generalType') {
            e.value = 'Marina';
        }
        else {
            e.value = '';
        }
    });
}