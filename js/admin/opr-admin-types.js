/*------------- Place Types -------------*/
function gatherPlaceTypeValues(placeTypeId) {
    let data = {id: '', placeTypeName: '', active: ''};

    document.querySelectorAll('input[rel="placeType-'+placeTypeId+'"],select[rel="placeType-'+placeTypeId+'"],textarea[rel="placeType-'+placeTypeId+'"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button')
            data[e.getAttribute('id').split('-')[0]] = e.value;
    });

    return data;
}

function submitPlaceType (e) {
    e.preventDefault();

    const submitButton = e.target;
    submitButton.disabled = true;

    const placeTypeId = submitButton.getAttribute('rel').substring(10);
    const spinner = document.querySelector('#spinner-placeType-'+placeTypeId);

    if (spinner)
        spinner.setAttribute('class', 'loader');

    let data = gatherPlaceTypeValues(placeTypeId);
    data.id = placeTypeId;

    if (placeTypeId === 'new')
        data.action = 'opr_add_place_type';
    else
        data.action = 'opr_save_place_type';

    postAjax(data, function(response) {
        if (spinner)
            spinner.setAttribute('class', 'hidden');

        submitButton.disabled = false;

        if (response) {
            if (response.success) {
                if (placeTypeId === 'new')
                    resetNewPlaceType();

                showPlaceTypesSpinner();
                populatePlaceTypes(response.data);
            }
            if (response.shouldNotify && response.messageType && response.message) {
                notification(response.messageType, response.message);
            }
        }
    });
}

function resetNewPlaceType() {
    const submitButton = document.querySelector('#placeTypeSubmitButton-new');

    document.querySelectorAll('input[rel="placeType-new"],select[rel="placeType-new"],textarea[rel="placeType-new"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button') {
            if (e.getAttribute('id') === 'placeTypeActive-new') {
                e.value = '1';
            } else if (e.getAttribute('id') === 'placeTypeGeneralType-new') {
                e.value = 'Marina';
            } else {
                e.value = '';
            }
        }
    });

    if (submitButton)
        submitButton.disabled = false;
}

function fetchAndShowPlaceTypes() {
    const placeTypesContainer = document.querySelector('#placeTypesList');
    if (placeTypesContainer) {
        showPlaceTypesSpinner();

        let data = {action: 'opr_list_place_types'};

        postAjax(data, function (response) {
            if (response) {
                if (response.success) {
                    populatePlaceTypes(response.data);
                }
                if (response.shouldNotify && response.messageType && response.message) {
                    notification(response.messageType, response.message);
                }
            }
        });
    }
}

function populatePlaceTypes(data) {
    const placeTypes = data.placeTypes;
    const fieldTranslations = data.placeTypeFieldTranslations;
    const placeTypesContainer = document.querySelector('#placeTypesList');

    placeTypes.forEach(function(placeType) {
        let tableRow = document.createElement('tr');
        tableRow.setAttribute('id', 'placeType'+placeType.id);

        // OPTION for placeTypeGeneralType - Marina
        let optionMarina = document.createElement('option');
        optionMarina.setAttribute('value', 'Marina');
        optionMarina.appendChild(document.createTextNode(fieldTranslations['placeTypeGeneralType'].options['Marina']));

        // OPTION for placeTypeGeneralType - Parking
        let optionParking = document.createElement('option');
        optionParking.setAttribute('value', 'Parking');
        optionParking.appendChild(document.createTextNode(fieldTranslations['placeTypeGeneralType'].options['Parking']));

        // SELECT for generalType
        let generalTypeSelect = document.createElement('select');
        generalTypeSelect.setAttribute('id', 'placeTypeGeneralType-'+placeType.id);
        generalTypeSelect.setAttribute('rel', 'placeType-'+placeType.id);
        generalTypeSelect.setAttribute('alt', fieldTranslations['placeTypeGeneralType'].alt);
        generalTypeSelect.setAttribute('title', fieldTranslations['placeTypeGeneralType'].title);
        generalTypeSelect.appendChild(optionMarina);
        generalTypeSelect.appendChild(optionParking);
        generalTypeSelect.value = placeType.generalType;

        // P for generalType
        let p = document.createElement('p');
        p.appendChild(generalTypeSelect);

        // TD for generalType
        let td = document.createElement('td');
        td.appendChild(p);

        tableRow.append(td);

        // INPUT for placeTypeName
        let placeTypeNameInput = document.createElement('input');
        placeTypeNameInput.setAttribute('type', 'text');
        placeTypeNameInput.setAttribute('id', 'placeTypeName-'+placeType.id);
        placeTypeNameInput.setAttribute('rel', 'placeType-'+placeType.id);
        placeTypeNameInput.setAttribute('alt', fieldTranslations['placeTypeName'].alt);
        placeTypeNameInput.setAttribute('title', fieldTranslations['placeTypeName'].title);
        placeTypeNameInput.setAttribute('placeholder', fieldTranslations['placeTypeName'].placeholder);
        placeTypeNameInput.value = placeType.name;

        // P for placeTypeName
        p = document.createElement('p');
        p.appendChild(placeTypeNameInput);

        // TD for placeTypeName
        td = document.createElement('td');
        td.appendChild(p);

        tableRow.append(td);

        // OPTION for placeTypeActive - active
        let optionActive = document.createElement('option');
        optionActive.setAttribute('value', '1');
        optionActive.appendChild(document.createTextNode(fieldTranslations['placeTypeActive'].options['active']));

        // OPTION for placeTypeActive - inactive
        let optionInactive = document.createElement('option');
        optionInactive.setAttribute('value', '0');
        optionInactive.appendChild(document.createTextNode(fieldTranslations['placeTypeActive'].options['inactive']));

        // SELECT for placeTypeActive
        let placeTypeActiveSelect = document.createElement('select');
        placeTypeActiveSelect.setAttribute('id', 'placeTypeActive-'+placeType.id);
        placeTypeActiveSelect.setAttribute('rel', 'placeType-'+placeType.id);
        placeTypeActiveSelect.setAttribute('alt', fieldTranslations['placeTypeActive'].alt);
        placeTypeActiveSelect.setAttribute('title', fieldTranslations['placeTypeActive'].title);
        placeTypeActiveSelect.appendChild(optionActive);
        placeTypeActiveSelect.appendChild(optionInactive);
        placeTypeActiveSelect.value = placeType.active;

        // P for placeTypeActive
        p = document.createElement('p');
        p.appendChild(placeTypeActiveSelect);

        // TD for placeTypeActive
        td = document.createElement('td');
        td.appendChild(p);

        tableRow.append(td);

        // INPUT for submitButton
        let submitButton = document.createElement('input');
        submitButton.setAttribute('type', 'button');
        submitButton.setAttribute('id', 'placeTypeSubmitButton-'+placeType.id);
        submitButton.setAttribute('rel', 'placeType-'+placeType.id);
        submitButton.setAttribute('class', 'button button-primary placeType-submitButton');
        submitButton.value = fieldTranslations['submitButton'].caption;
        submitButton.addEventListener('click', submitPlaceType);

        // P for buttons
        p = document.createElement('p');
        p.appendChild(submitButton);

        // td for buttons
        td = document.createElement('td');
        td.appendChild(p);

        tableRow.appendChild(td);

        // DIV for spinner
        let spinner = document.createElement('div');
        spinner.setAttribute('class', 'hidden');
        spinner.setAttribute('id', 'spinner-placeType-'+placeType.id);

        // TD for spinner
        td = document.createElement('td');
        td.setAttribute('class', 'opr-spinner-column');

        tableRow.appendChild(td);

        if (placeTypesContainer) {
            placeTypesContainer.appendChild(tableRow);
        }
    });

    let placeTypesSpinner = document.querySelector('#placeTypesSpinnerTr');
    if (placeTypesSpinner)
        placeTypesContainer.removeChild(placeTypesSpinner);
}

function showPlaceTypesSpinner() {
    const placeTypesContainer = document.querySelector('#placeTypesList');

    if (placeTypesContainer)
        placeTypesContainer.innerHTML = '';

    let div = document.createElement('div');
    div.setAttribute('id', 'placeTypesSpinner');
    div.setAttribute('class', 'loader-big');

    let td = document.createElement('td');
    td.appendChild(div);

    let tr = document.createElement('tr');
    tr.setAttribute('id', 'placeTypesSpinnerTr');
    tr.appendChild(td);

    if (placeTypesContainer)
        placeTypesContainer.appendChild(tr);
}

/*------------- Locations -------------*/
function gatherLocationValues(locationId) {
    let data = {};

    document.querySelectorAll('input[rel="location-'+locationId+'"],select[rel="location-'+locationId+'"],textarea[rel="location-'+locationId+'"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button')
            data[e.getAttribute('id').split('-')[0]] = e.value;
    });

    return data;
}

function submitLocation (e) {
    e.preventDefault();

    const submitButton = e.target;
    submitButton.disabled = true;

    const locationId = submitButton.getAttribute('rel').split('-')[1];
    const spinner = document.querySelector('#spinner-location-'+locationId);

    if (spinner)
        spinner.setAttribute('class', 'loader');

    let data = gatherLocationValues(locationId);
    data.id = locationId;

    if (locationId === 'new')
        data.action = 'opr_add_location';
    else
        data.action = 'opr_save_location';

    postAjax(data, function(response) {
        if (spinner)
            spinner.setAttribute('class', 'hidden');

        submitButton.disabled = false;

        if (response) {
            if (response.success) {
                if (locationId === 'new')
                    resetNewLocation();

                showLocationsSpinner();
                populateLocations(response.data);

            }
            if (response.shouldNotify && response.messageType && response.message) {
                notification(response.messageType, response.message);
            }
        }
    });
}

function resetNewLocation() {
    const submitButton = document.querySelector('#locationSubmitButton-new');

    document.querySelectorAll('input[rel="location-new"],select[rel="location-new"],textarea[rel="location-new"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button') {
            if (e.getAttribute('id') === 'locationActive-new') {
                e.value = '1';
            } else if (e.getAttribute('id') === 'locationGeneralType-new') {
                e.value = 'Marina';
            } else {
                e.value = '';
            }
        }
    });

    if (submitButton)
        submitButton.disabled = false;
}

function fetchAndShowLocations() {
    const locationsContainer = document.querySelector('#locationsList');
    if (locationsContainer) {
        showLocationsSpinner();

        let data = {action: 'opr_list_locations'};

        postAjax(data, function (response) {
            if (response) {
                if (response.success) {
                    populateLocations(response.data);
                }
                if (response.shouldNotify && response.messageType && response.message) {
                    notification(response.messageType, response.message);
                }
            }
        });
    }
}

function populateLocations(data) {
    const locations = data.locations;
    const fieldTranslations = data.locationFieldTranslations;
    const locationsContainer = document.querySelector('#locationsList');

    locations.forEach(function(location) {
        let tableRow = document.createElement('tr');
        tableRow.setAttribute('id', 'location'+location.id);

        // OPTION for locationGeneralType - Marina
        let optionMarina = document.createElement('option');
        optionMarina.setAttribute('value', 'Marina');
        optionMarina.appendChild(document.createTextNode(fieldTranslations['locationGeneralType'].options['Marina']));

        // OPTION for locationGeneralType - Parking
        let optionParking = document.createElement('option');
        optionParking.setAttribute('value', 'Parking');
        optionParking.appendChild(document.createTextNode(fieldTranslations['locationGeneralType'].options['Parking']));

        // SELECT for generalType
        let generalTypeSelect = document.createElement('select');
        generalTypeSelect.setAttribute('id', 'locationGeneralType-'+location.id);
        generalTypeSelect.setAttribute('rel', 'location-'+location.id);
        generalTypeSelect.setAttribute('alt', fieldTranslations['locationGeneralType'].alt);
        generalTypeSelect.setAttribute('title', fieldTranslations['locationGeneralType'].title);
        generalTypeSelect.appendChild(optionMarina);
        generalTypeSelect.appendChild(optionParking);
        generalTypeSelect.value = location.generalType;

        // P for generalType
        let p = document.createElement('p');
        p.appendChild(generalTypeSelect);

        // TD for generalType
        let td = document.createElement('td');
        td.appendChild(p);

        tableRow.append(td);

        // INPUT for locationName
        let locationNameInput = document.createElement('input');
        locationNameInput.setAttribute('type', 'text');
        locationNameInput.setAttribute('id', 'locationName-'+location.id);
        locationNameInput.setAttribute('rel', 'location-'+location.id);
        locationNameInput.setAttribute('alt', fieldTranslations['locationName'].alt);
        locationNameInput.setAttribute('title', fieldTranslations['locationName'].title);
        locationNameInput.setAttribute('placeholder', fieldTranslations['locationName'].placeholder);
        locationNameInput.value = location.name;

        // P for locationName
        p = document.createElement('p');
        p.appendChild(locationNameInput);

        // TD for locationName
        td = document.createElement('td');
        td.appendChild(p);

        tableRow.append(td);

        // OPTION for locationActive - active
        let optionActive = document.createElement('option');
        optionActive.setAttribute('value', '1');
        optionActive.appendChild(document.createTextNode(fieldTranslations['locationActive'].options['active']));

        // OPTION for locationActive - inactive
        let optionInactive = document.createElement('option');
        optionInactive.setAttribute('value', '0');
        optionInactive.appendChild(document.createTextNode(fieldTranslations['locationActive'].options['inactive']));

        // SELECT for locationActive
        let locationActiveSelect = document.createElement('select');
        locationActiveSelect.setAttribute('id', 'locationActive-'+location.id);
        locationActiveSelect.setAttribute('rel', 'location-'+location.id);
        locationActiveSelect.setAttribute('alt', fieldTranslations['locationActive'].alt);
        locationActiveSelect.setAttribute('title', fieldTranslations['locationActive'].title);
        locationActiveSelect.appendChild(optionActive);
        locationActiveSelect.appendChild(optionInactive);
        locationActiveSelect.value = location.active;

        // P for locationActive
        p = document.createElement('p');
        p.appendChild(locationActiveSelect);

        // TD for locationActive
        td = document.createElement('td');
        td.appendChild(p);

        tableRow.append(td);

        // INPUT for submitButton
        let submitButton = document.createElement('input');
        submitButton.setAttribute('type', 'button');
        submitButton.setAttribute('id', 'locationSubmitButton-'+location.id);
        submitButton.setAttribute('rel', 'location-'+location.id);
        submitButton.setAttribute('class', 'button button-primary location-submitButton');
        submitButton.value = fieldTranslations['submitButton'].caption;
        submitButton.addEventListener('click', submitLocation);

        // P for buttons
        p = document.createElement('p');
        p.appendChild(submitButton);

        // td for buttons
        td = document.createElement('td');
        td.appendChild(p);

        tableRow.appendChild(td);

        // DIV for spinner
        let spinner = document.createElement('div');
        spinner.setAttribute('class', 'hidden');
        spinner.setAttribute('id', 'spinner-location-'+location.id);

        // TD for spinner
        td = document.createElement('td');
        td.setAttribute('class', 'opr-spinner-column');

        tableRow.appendChild(td);

        if (locationsContainer) {
            locationsContainer.appendChild(tableRow);
        }
    });

    let locationsSpinner = document.querySelector('#locationsSpinnerTr');
    if (locationsSpinner)
        locationsContainer.removeChild(locationsSpinner);
}

function showLocationsSpinner() {
    const locationsContainer = document.querySelector('#locationsList');

    if (locationsContainer)
        locationsContainer.innerHTML = '';

    let div = document.createElement('div');
    div.setAttribute('id', 'locationsSpinner');
    div.setAttribute('class', 'loader-big');

    let td = document.createElement('td');
    td.appendChild(div);

    let tr = document.createElement('tr');
    tr.setAttribute('id', 'locationsSpinnerTr');
    tr.appendChild(td);

    if (locationsContainer)
        locationsContainer.appendChild(tr);
}

/*------------- Period Types -------------*/
function gatherPeriodTypeValues(periodTypeId) {
    let data = {};

    document.querySelectorAll('input[rel="periodType-'+periodTypeId+'"],select[rel="periodType-'+periodTypeId+'"],textarea[rel="periodType-'+periodTypeId+'"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button')
            data[e.getAttribute('id').split('-')[0]] = e.value;
    });

    return data;
}

function submitPeriodType (e) {
    e.preventDefault();

    const submitButton = e.target;
    submitButton.disabled = true;

    const periodTypeId = submitButton.getAttribute('rel').split('-')[1];
    const spinner = document.querySelector('#spinner-periodType-'+periodTypeId);

    if (spinner)
        spinner.setAttribute('class', 'loader');

    let data = gatherPeriodTypeValues(periodTypeId);
    data.id = periodTypeId;

    if (periodTypeId === 'new')
        data.action = 'opr_add_period_type';
    else
        data.action = 'opr_save_period_type';

    postAjax(data, function(response) {
        if (spinner)
            spinner.setAttribute('class', 'hidden');

        submitButton.disabled = false;

        if (response) {
            if (response.success) {
                if (periodTypeId === 'new')
                    resetNewPeriodType();

                showPeriodTypesSpinner();
                populatePeriodTypes(response.data);
            }
            if (response.shouldNotify && response.messageType && response.message) {
                notification(response.messageType, response.message);
            }
        }
    });
}

function resetNewPeriodType() {
    const submitButton = document.querySelector('#periodTypeSubmitButton-new');

    document.querySelectorAll('input[rel="periodType-new"],select[rel="periodType-new"],textarea[rel="periodType-new"]').forEach(function (e, i) {
        if (e.getAttribute('type') !== 'button') {
            if (e.getAttribute('id') === 'periodTypeActive-new') {
                e.value = '1';
            } else {
                e.value = '';
            }
        }
    });

    if (submitButton)
        submitButton.disabled = false;
}

function fetchAndShowPeriodTypes() {
    const periodTypesContainer = document.querySelector('#periodTypesList');
    if (periodTypesContainer) {
        showPeriodTypesSpinner();

        let data = {action: 'opr_list_period_types'};

        postAjax(data, function (response) {
            if (response) {
                if (response.success) {
                    populatePeriodTypes(response.data);
                }
                if (response.shouldNotify && response.messageType && response.message) {
                    notification(response.messageType, response.message);
                }
            }
        });
    }
}

function populatePeriodTypes(data) {
    const periodTypes = data.periodTypes;
    const fieldTranslations = data.periodTypeFieldTranslations;
    const periodTypesContainer = document.querySelector('#periodTypesList');

    periodTypes.forEach(function(periodType) {
        let tableRow = document.createElement('tr');
        tableRow.setAttribute('id', 'periodType'+periodType.id);

        // INPUT for periodTypeName
        let periodTypeNameInput = document.createElement('input');
        periodTypeNameInput.setAttribute('type', 'text');
        periodTypeNameInput.setAttribute('id', 'periodTypeName-'+periodType.id);
        periodTypeNameInput.setAttribute('rel', 'periodType-'+periodType.id);
        periodTypeNameInput.setAttribute('alt', fieldTranslations['periodTypeName'].alt);
        periodTypeNameInput.setAttribute('title', fieldTranslations['periodTypeName'].title);
        periodTypeNameInput.setAttribute('placeholder', fieldTranslations['periodTypeName'].placeholder);
        periodTypeNameInput.value = periodType.name;

        // P for periodTypeName
        p = document.createElement('p');
        p.appendChild(periodTypeNameInput);

        // TD for periodTypeName
        td = document.createElement('td');
        td.appendChild(p);

        tableRow.append(td);

        // OPTION for periodTypeActive - active
        let optionActive = document.createElement('option');
        optionActive.setAttribute('value', '1');
        optionActive.appendChild(document.createTextNode(fieldTranslations['periodTypeActive'].options['active']));

        // OPTION for periodTypeActive - inactive
        let optionInactive = document.createElement('option');
        optionInactive.setAttribute('value', '0');
        optionInactive.appendChild(document.createTextNode(fieldTranslations['periodTypeActive'].options['inactive']));

        // SELECT for periodTypeActive
        let periodTypeActiveSelect = document.createElement('select');
        periodTypeActiveSelect.setAttribute('id', 'periodTypeActive-'+periodType.id);
        periodTypeActiveSelect.setAttribute('rel', 'periodType-'+periodType.id);
        periodTypeActiveSelect.setAttribute('alt', fieldTranslations['periodTypeActive'].alt);
        periodTypeActiveSelect.setAttribute('title', fieldTranslations['periodTypeActive'].title);
        periodTypeActiveSelect.appendChild(optionActive);
        periodTypeActiveSelect.appendChild(optionInactive);
        periodTypeActiveSelect.value = periodType.active;

        // P for periodTypeActive
        p = document.createElement('p');
        p.appendChild(periodTypeActiveSelect);

        // TD for periodTypeActive
        td = document.createElement('td');
        td.appendChild(p);

        tableRow.append(td);

        // INPUT for submitButton
        let submitButton = document.createElement('input');
        submitButton.setAttribute('type', 'button');
        submitButton.setAttribute('id', 'periodTypeSubmitButton-'+periodType.id);
        submitButton.setAttribute('rel', 'periodType-'+periodType.id);
        submitButton.setAttribute('class', 'button button-primary periodType-submitButton');
        submitButton.value = fieldTranslations['submitButton'].caption;
        submitButton.addEventListener('click', submitPeriodType);

        // P for buttons
        p = document.createElement('p');
        p.appendChild(submitButton);

        // td for buttons
        td = document.createElement('td');
        td.appendChild(p);

        tableRow.appendChild(td);

        // DIV for spinner
        let spinner = document.createElement('div');
        spinner.setAttribute('class', 'hidden');
        spinner.setAttribute('id', 'spinner-periodType-'+periodType.id);

        // TD for spinner
        td = document.createElement('td');
        td.setAttribute('class', 'opr-spinner-column');

        tableRow.appendChild(td);

        if (periodTypesContainer) {
            periodTypesContainer.appendChild(tableRow);
        }
    });

    let periodTypesSpinner = document.querySelector('#periodTypesSpinnerTr');
    if (periodTypesSpinner)
        periodTypesContainer.removeChild(periodTypesSpinner);
}

function showPeriodTypesSpinner() {
    const periodTypesContainer = document.querySelector('#periodTypesList');

    if (periodTypesContainer)
        periodTypesContainer.innerHTML = '';

    let div = document.createElement('div');
    div.setAttribute('id', 'periodTypesSpinner');
    div.setAttribute('class', 'loader-big');

    let td = document.createElement('td');
    td.appendChild(div);

    let tr = document.createElement('tr');
    tr.setAttribute('id', 'periodTypesSpinnerTr');
    tr.appendChild(td);

    if (periodTypesContainer)
        periodTypesContainer.appendChild(tr);
}