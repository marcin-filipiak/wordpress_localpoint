document.addEventListener("DOMContentLoaded", function () {
    if (typeof localpointData === 'undefined' || typeof L === 'undefined') {
        return;
    }

    var location = localpointData.location || {};
    var lat = parseFloat(location.lat);
    var lng = parseFloat(location.lng);

    if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) {
        return;
    }

    var mapContainer = document.getElementById('localpoint-map');
    if (!mapContainer) {
        return;
    }

    var map = L.map('localpoint-map').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map);
});
