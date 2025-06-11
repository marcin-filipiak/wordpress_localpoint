<?php

define('LOCALPOINT_JSON_FILE', __DIR__ . '/data/config.json');

function localpoint_settings_page() {
    $json_path = LOCALPOINT_JSON_FILE;

    $default_data = [
        'location' => [
            'lat' => 52.2297,
            'lng' => 21.0122,
        ],
        'contact' => [
            'phone' => '',
            'email' => '',
            'address' => '',
            'note' => '',
        ],
        'hours' => [
            'monday'    => ['open' => '', 'close' => '', 'closed' => false],
            'tuesday'   => ['open' => '', 'close' => '', 'closed' => false],
            'wednesday' => ['open' => '', 'close' => '', 'closed' => false],
            'thursday'  => ['open' => '', 'close' => '', 'closed' => false],
            'friday'    => ['open' => '', 'close' => '', 'closed' => false],
            'saturday'  => ['open' => '', 'close' => '', 'closed' => false],
            'sunday'    => ['open' => '', 'close' => '', 'closed' => false],
        ],
    ];

    if (!file_exists($json_path)) {
        file_put_contents($json_path, json_encode($default_data, JSON_PRETTY_PRINT));
    }

    $data = json_decode(file_get_contents($json_path), true);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('localpoint_save_settings')) {
        $data['location']['lat'] = floatval($_POST['lat']);
        $data['location']['lng'] = floatval($_POST['lng']);

        $data['contact']['phone'] = sanitize_text_field($_POST['phone']);
        $data['contact']['email'] = sanitize_email($_POST['email']);
        $data['contact']['address'] = sanitize_text_field($_POST['address']);
        $data['contact']['note'] = sanitize_text_field($_POST['note']);

        foreach ($data['hours'] as $day => $hours) {
            if (!empty($_POST['hours'][$day]['closed'])) {
                $data['hours'][$day]['closed'] = true;
                $data['hours'][$day]['open'] = '';
                $data['hours'][$day]['close'] = '';
            } else {
                $data['hours'][$day]['closed'] = false;
                $data['hours'][$day]['open'] = sanitize_text_field($_POST['hours'][$day]['open'] ?? '');
                $data['hours'][$day]['close'] = sanitize_text_field($_POST['hours'][$day]['close'] ?? '');
            }
        }

        file_put_contents($json_path, json_encode($data, JSON_PRETTY_PRINT));
        echo '<div class="updated"><p>' . esc_html__('Settings saved.', 'localpoint') . '</p></div>';
    }

    // Klucze i nazwy dni do tłumaczenia
    $days_keys = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    $days_names = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('LocalPoint Settings', 'localpoint'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('localpoint_save_settings'); ?>

            <h2><?php echo esc_html__('Location', 'localpoint'); ?></h2>
            <label><?php echo esc_html__('Latitude:', 'localpoint'); ?> <input type="text" name="lat" value="<?php echo esc_attr($data['location']['lat']); ?>" /></label><br>
            <label><?php echo esc_html__('Longitude:', 'localpoint'); ?> <input type="text" name="lng" value="<?php echo esc_attr($data['location']['lng']); ?>" /></label><br>

            <div id="map" style="height: 400px; margin: 20px 0;"></div>

            <h2><?php echo esc_html__('Contact', 'localpoint'); ?></h2>
            <label><?php echo esc_html__('Phone:', 'localpoint'); ?> <input type="text" name="phone" value="<?php echo esc_attr($data['contact']['phone']); ?>" /></label><br>
            <label><?php echo esc_html__('Email:', 'localpoint'); ?> <input type="text" name="email" value="<?php echo esc_attr($data['contact']['email']); ?>" /></label><br>
            <label><?php echo esc_html__('Address:', 'localpoint'); ?> <input type="text" name="address" value="<?php echo esc_attr($data['contact']['address']); ?>" /></label><br>
            <label><?php echo esc_html__('Note:', 'localpoint'); ?> <input type="text" name="note" value="<?php echo esc_attr($data['contact']['note']); ?>" style="width: 100%; max-width: 400px;" /></label>

            <h2><?php echo esc_html__('Opening hours', 'localpoint'); ?></h2>
            <table class="form-table">
                <?php foreach ($days_keys as $index => $day_key):
                    $day_name = $days_names[$index];
                    $hours = $data['hours'][$day_key];
                ?>
                    <tr>
                        <th><?php echo esc_html__($day_name, 'localpoint'); ?></th>
                        <td>
                            <label><?php echo esc_html__('Open:', 'localpoint'); ?> <input type="time" name="hours[<?php echo esc_attr($day_key); ?>][open]" value="<?php echo esc_attr($hours['open']); ?>" <?php if (!empty($hours['closed'])) echo 'disabled'; ?>></label>
                            <label><?php echo esc_html__('Close:', 'localpoint'); ?> <input type="time" name="hours[<?php echo esc_attr($day_key); ?>][close]" value="<?php echo esc_attr($hours['close']); ?>" <?php if (!empty($hours['closed'])) echo 'disabled'; ?>></label>
                            <label><?php echo esc_html__('Closed:', 'localpoint'); ?> <input type="checkbox" name="hours[<?php echo esc_attr($day_key); ?>][closed]" value="1" <?php if (!empty($hours['closed'])) echo 'checked'; ?>></label>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p><input type="submit" class="button button-primary" value="<?php echo esc_attr__('Save Changes', 'localpoint'); ?>" /></p>
        </form>
    </div>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        var lat = <?php echo json_encode($data['location']['lat']); ?>;
        var lng = <?php echo json_encode($data['location']['lng']); ?>;

        var map = L.map('map').setView([lat, lng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([lat, lng], {draggable: true}).addTo(map);

        marker.on('dragend', function (e) {
            var position = marker.getLatLng();
            document.querySelector('input[name="lat"]').value = position.lat.toFixed(6);
            document.querySelector('input[name="lng"]').value = position.lng.toFixed(6);
        });
    });
    </script>
    <?php
}

