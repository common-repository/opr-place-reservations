function notification(type, message) {
    const messageBox = document.querySelector('#messageBox');

    if (messageBox)
        messageBox.innerHTML = '';

    let strong = document.createElement('strong');
    strong.appendChild(document.createTextNode(message));

    let p = document.createElement('p');
    p.appendChild(strong);

    let div = document.createElement('div');
    div.setAttribute('class', type);
    div.appendChild(p);

    if (messageBox)
        messageBox.appendChild(div);
}

function postAjax(data, successCallback) {
    let params = typeof data == 'string' ? data : Object.keys(data).map(
        function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
    ).join('&');

    let xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

    xhr.open('POST', ajaxurl);

    xhr.onreadystatechange = function() {
        if (xhr.readyState > 3 && xhr.status == 200) {
            let json = JSON.parse(xhr.responseText)
            if (json)
                successCallback(json);
        }
    };

    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(params);

    return xhr;
}

function postAjaxFormData(data, successCallback) {
    let xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

    xhr.open('POST', ajaxurl);

    xhr.onreadystatechange = function() {
        if (xhr.readyState > 3 && xhr.status == 200) {
            let json = JSON.parse(xhr.responseText)
            if (json)
                successCallback(json);
        }
    };

    xhr.send(data);

    return xhr;
}

function fetchAdminTranslations() {
    let data = {action:  'opr_list_admin_translations'};

    postAjax(data, function (response) {
        if ((response) && (response.success)) {
            window.adminTranslations = response.data;
            window.translationsFetched = true;
        }
    });
}

function gatherShownItemIds(itemType) {
    let ret = [];

    document.querySelectorAll('tr[rel="'+itemType+'"]').forEach(function (placeRow) {
        ret[ret.length] = placeRow.getAttribute('id').split('-')[1];
    });

    return ret;
}

function removeShowMoreButtonRow() {
    let buttonRow = document.querySelector('#showMoreButtonRow');
    if (buttonRow)
        buttonRow.remove();
}

function showSpinner() {
    const reservationsSpinnerRow = document.querySelector('#showMoreSpinner');
    if (reservationsSpinnerRow)
        reservationsSpinnerRow.setAttribute('class', 'spinner is-active');
}

function hideSpinner() {
    const reservationsSpinnerRow = document.querySelector('#showMoreSpinner');
    if (reservationsSpinnerRow)
        reservationsSpinnerRow.setAttribute('class', 'spinner');
}

function appendShowMoreButton(listContainer, fieldTranslations, callback) {
    if (listContainer) {
        let spinner = document.createElement('div');
        spinner.setAttribute('id', 'showMoreSpinner');
        spinner.setAttribute('class', 'spinner');
        spinner.setAttribute('style', 'float: none;');

        let showMoreButton = document.createElement('input');
        showMoreButton.setAttribute('type', 'button');
        showMoreButton.setAttribute('id', 'showMoreButton');
        showMoreButton.value = fieldTranslations['showMoreButton'].caption;
        showMoreButton.setAttribute('class', 'button');
        showMoreButton.addEventListener('click', callback);

        let td = document.createElement('td')
        td.setAttribute('colspan', '9');
        td.setAttribute('style', 'text-align: center;');
        td.appendChild(showMoreButton);
        td.appendChild(spinner);

        let tr = document.createElement('tr');
        tr.setAttribute('id', 'showMoreButtonRow');
        tr.appendChild(td);

        listContainer.appendChild(tr);
    }
}
