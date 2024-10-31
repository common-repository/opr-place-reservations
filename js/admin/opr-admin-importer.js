function importCsvReservationsWithPlaces() {
    const spinner = document.querySelector('#importReservationsWithPlacesSpinner')
    const importButton = document.querySelector('#importReservationsWithPlacesButton');
    if (importButton)
        importButton.disabled = true;
    if (spinner)
        spinner.setAttribute('class', 'spinner is-active');

    let data = new FormData();
    data.set("action", "opr_import_csv_reservations_with_places");
    const fileInput = document.querySelector('#reservationsWithPlacesCsv');

    if (fileInput) {
        let files = fileInput.files;
        data.append('file', files[0]);

        const removeExistingReservationsFromPlace = document.querySelector('#rwpRemoveExistingReservationsFromPlace');
        if (removeExistingReservationsFromPlace) {
            if (removeExistingReservationsFromPlace.checked) {
                data.append('removeExistingReservationsFromPlace', '1');
            }
            else {
                data.append('removeExistingReservationsFromPlace', '0');
            }
        }

        const createMissingData = document.querySelector('#rwpCreateMissingData');
        if (createMissingData) {
            if (createMissingData.checked) {
                data.append('createMissingData', '1');
            }
            else {
                data.append('createMissingData', '0');
            }
        }

        const replaceExisting = document.querySelector('#rwpReplaceExisting');
        if (replaceExisting) {
            if (replaceExisting.checked) {
                data.append('replaceExisting', '1');
            }
            else {
                data.append('replaceExisting', '0');
            }
        }

        postAjaxFormData(data, function (response) {
            if (response) {
                if (response.shouldNotify && response.messageType && response.message) {
                    notification(response.messageType, response.message);
                }
                const spinner = document.querySelector('#importReservationsWithPlacesSpinner')
                const importButton = document.querySelector('#importReservationsWithPlacesButton');
                if (importButton)
                    importButton.disabled = false;
                if (spinner)
                    spinner.setAttribute('class', 'spinner');
            }
        });
    }
}

function importCsvReservations() {
    const spinner = document.querySelector('#importReservationsSpinner')
    const importButton = document.querySelector('#importReservationsButton');
    if (importButton)
        importButton.disabled = true;
    if (spinner)
        spinner.setAttribute('class', 'spinner is-active');

    let data = new FormData();
    data.set("action", "opr_import_csv_reservations");
    const fileInput = document.querySelector('#reservationsCsv');

    if (fileInput) {
        let files = fileInput.files;
        data.append('file', files[0]);

        const removeExistingReservationsFromPlace = document.querySelector('#resRemoveExistingReservationsFromPlace');
        if (removeExistingReservationsFromPlace) {
            if (removeExistingReservationsFromPlace.checked) {
                data.append('removeExistingReservationsFromPlace', '1');
            }
            else {
                data.append('removeExistingReservationsFromPlace', '0');
            }
        }

        postAjaxFormData(data, function (response) {
            if (response) {
                if (response.shouldNotify && response.messageType && response.message) {
                    notification(response.messageType, response.message);
                }
                const spinner = document.querySelector('#importReservationsSpinner')
                const importButton = document.querySelector('#importReservationsButton');
                if (importButton)
                    importButton.disabled = false;
                if (spinner)
                    spinner.setAttribute('class', 'spinner');
            }
        });
    }
}

function importCsvPlaces() {
    const spinner = document.querySelector('#importPlacesSpinner')
    const importButton = document.querySelector('#importPlacesButton');
    if (importButton)
        importButton.disabled = true;
    if (spinner)
        spinner.setAttribute('class', 'spinner is-active');

    let data = new FormData();
    data.set("action", "opr_import_csv_places");
    const fileInput = document.querySelector('#placesCsv');

    if (fileInput) {
        let files = fileInput.files;
        data.append('file', files[0]);

        const removeExistingReservationsFromPlace = document.querySelector('#plcRemoveExistingReservationsFromPlace');
        if (removeExistingReservationsFromPlace) {
            if (removeExistingReservationsFromPlace.checked) {
                data.append('removeExistingReservationsFromPlace', '1');
            }
            else {
                data.append('removeExistingReservationsFromPlace', '0');
            }
        }

        const createMissingData = document.querySelector('#plcCreateMissingData');
        if (createMissingData) {
            if (createMissingData.checked) {
                data.append('createMissingData', '1');
            }
            else {
                data.append('createMissingData', '0');
            }
        }

        const replaceExisting = document.querySelector('#plcReplaceExisting');
        if (replaceExisting) {
            if (replaceExisting.checked) {
                data.append('replaceExisting', '1');
            }
            else {
                data.append('replaceExisting', '0');
            }
        }

        postAjaxFormData(data, function (response) {
            if (response) {
                if (response.shouldNotify && response.messageType && response.message) {
                    notification(response.messageType, response.message);
                }
                const spinner = document.querySelector('#importPlacesSpinner')
                const importButton = document.querySelector('#importPlacesButton');
                if (importButton)
                    importButton.disabled = false;
                if (spinner)
                    spinner.setAttribute('class', 'spinner');
            }
        });
    }
}
