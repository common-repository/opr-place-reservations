function postAjax(data, successCallback) {
    let params = typeof data == 'string' ? data : Object.keys(data).map(
        function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
    ).join('&');

    let xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

    xhr.open('POST', '/wp-admin/admin-ajax.php');

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
