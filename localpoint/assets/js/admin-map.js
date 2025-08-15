document.addEventListener("DOMContentLoaded", function () {
    var lat = parseFloat(document.querySelector('input[name="lat"]').value);
    var lng = parseFloat(document.querySelector('input[name="lng"]').value);

    var map = L.map('map').setView([lat, lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    marker.on('dragend', function () {
        var pos = marker.getLatLng();
        document.querySelector('input[name="lat"]').value = pos.lat.toFixed(6);
        document.querySelector('input[name="lng"]').value = pos.lng.toFixed(6);
    });
});

