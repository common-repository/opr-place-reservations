function saveCustomerViewSettings() {
    let data = gatherCustomerShowFieldsData();
    data['settingsCustomerOrderByColumn'] = getCustomerOrderByColumn();
    data['action'] = 'opr_save_customer_view_settings';

    postAjax(data, function (response) {
        if (response) {
            if (response.shouldNotify && response.messageType && response.message) {
                notification(response.messageType, response.message);
            }
        }
    });
}

function gatherCustomerShowFieldsData() {
    let data = {};
    document.querySelectorAll('input[rel="settingsCustomerShowFields"]').forEach(function (e, i) {
        if (e.getAttribute('type') === 'checkbox') {
            let id = e.getAttribute('id');
            if (e.checked) {
                data[id] = 1;
            }
            else {
                data[id] = 0;
            }
        }
    });

    return data;
}

function getCustomerOrderByColumn() {
    return document.querySelector('#settingsCustomerOrderByColumn').value;
}


function saveReservationSettings() {
    console.log('foo1');
    let data = gatherReservationMandatoryFieldsData();
    data['action'] = 'opr_save_reservation_settings';

    postAjax(data, function (response) {
        if (response) {
            if (response.shouldNotify && response.messageType && response.message) {
                notification(response.messageType, response.message);
            }
        }
    });
}

function gatherReservationMandatoryFieldsData() {
    let data = {};
    document.querySelectorAll('input[rel="settingsReservationMandatoryFields"]').forEach(function (e, i) {
        if (e.getAttribute('type') === 'checkbox') {
            let id = e.getAttribute('id');
            if (e.checked) {
                data[id] = 1;
            }
            else {
                data[id] = 0;
            }
        }
    });

    return data;
}

function saveEmailSenderSettings() {
    let data = gatherEmailSenderSettingsValues();
    data['action'] = 'opr_save_email_general_settings';

    postAjax(data, function (response) {
        if (response) {
            if (response.shouldNotify && response.messageType && response.message) {
                notification(response.messageType, response.message);
            }
        }
    });
}

function gatherEmailSenderSettingsValues() {
    let data = {};
    document.querySelectorAll('input[rel="emailGeneralSettings"]').forEach(function (e, i) {
        let id = e.getAttribute('id');
        if (e.getAttribute('type') === 'checkbox') {
            if (e.checked) {
                data[id] = 1;
            }
            else {
                data[id] = 0;
            }
        }
        else {
            data[id] = e.value;
        }
    });

    return data;
}

function saveCustomerEmailContentSettings() {
    let data = gatherCustomerEmailContentSettingsValues();
    data['action'] = 'opr_save_customer_email_content_settings';

    postAjax(data, function (response) {
        if (response) {
            if (response.shouldNotify && response.messageType && response.message) {
                notification(response.messageType, response.message);
            }
        }
    });
}

function gatherCustomerEmailContentSettingsValues() {
    let data = {};
    document.querySelectorAll('input[rel="emailCustomerContentSettings"], textarea[rel="emailCustomerContentSettings"]').forEach(function (e, i) {
        let id = e.getAttribute('id');
        if (e.getAttribute('type') === 'checkbox') {
            if (e.checked) {
                data[id] = 1;
            }
            else {
                data[id] = 0;
            }
        }
        else {
            data[id] = e.value;
        }
    });

    return data;
}

function saveAdminEmailContentSettings() {
    let data = gatherAdminEmailContentSettingsValues();
    data['action'] = 'opr_save_admin_email_content_settings';

    postAjax(data, function (response) {
        if (response) {
            if (response.shouldNotify && response.messageType && response.message) {
                notification(response.messageType, response.message);
            }
        }
    });
}

function gatherAdminEmailContentSettingsValues() {
    let data = {};
    document.querySelectorAll('input[rel="emailAdminContentSettings"], textarea[rel="emailAdminContentSettings"]').forEach(function (e, i) {
        let id = e.getAttribute('id');
        if (e.getAttribute('type') === 'checkbox') {
            if (e.checked) {
                data[id] = 1;
            }
            else {
                data[id] = 0;
            }
        }
        else {
            data[id] = e.value;
        }
    });

    return data;
}
