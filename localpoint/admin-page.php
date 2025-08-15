<?php
if (!defined('ABSPATH')) {
    exit;
}

define('LOCALPOINT_OPTION', 'localpoint_data');

add_action('admin_enqueue_scripts', 'localpoint_admin_assets');
function localpoint_admin_assets($hook) {
    // Sprawdzenie, czy jesteśmy na stronie naszego pluginu
    if ($hook !== 'toplevel_page_localpoint-settings') {
        return;
    }

    // Leaflet lokalnie z folderu assets
    wp_enqueue_style('leaflet-admin', plugin_dir_url(__FILE__) . 'assets/css/leaflet.css');
    wp_enqueue_script('leaflet-admin', plugin_dir_url(__FILE__) . 'assets/js/leaflet.js', [], null, true);

    // Nasz JS do mapy w panelu
    wp_enqueue_script('localpoint-admin-map', plugin_dir_url(__FILE__) . 'assets/js/admin-map.js', ['leaflet-admin'], null, true);
}

function localpoint_settings_page() {
    $data = get_option(LOCALPOINT_OPTION, [
        'location' => ['lat' => 52.2297, 'lng' => 21.0122],
        'contact'  => ['phone' => '', 'email' => '', 'address' => '', 'note' => ''],
        'hours'    => [
            'monday'    => ['open' => '', 'close' => '', 'closed' => false],
            'tuesday'   => ['open' => '', 'close' => '', 'closed' => false],
            'wednesday' => ['open' => '', 'close' => '', 'closed' => false],
            'thursday'  => ['open' => '', 'close' => '', 'closed' => false],
            'friday'    => ['open' => '', 'close' => '', 'closed' => false],
            'saturday'  => ['open' => '', 'close' => '', 'closed' => false],
            'sunday'    => ['open' => '', 'close' => '', 'closed' => false],
        ],
    ]);

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

        update_option(LOCALPOINT_OPTION, $data);
        echo '<div class="updated"><p>' . esc_html__('Settings saved.', 'localpoint') . '</p></div>';
    }

    $days_keys = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    $days_translations = [
        'monday'    => esc_html__('Monday', 'localpoint'),
        'tuesday'   => esc_html__('Tuesday', 'localpoint'),
        'wednesday' => esc_html__('Wednesday', 'localpoint'),
        'thursday'  => esc_html__('Thursday', 'localpoint'),
        'friday'    => esc_html__('Friday', 'localpoint'),
        'saturday'  => esc_html__('Saturday', 'localpoint'),
        'sunday'    => esc_html__('Sunday', 'localpoint'),
    ];
    ?>

    <div class="wrap">
        <h1><?php echo esc_html__('LocalPoint Settings', 'localpoint'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('localpoint_save_settings'); ?>

            <h2><?php echo esc_html__('Location', 'localpoint'); ?></h2>
            <label><?php echo esc_html__('Latitude:', 'localpoint'); ?>
                <input type="text" name="lat" value="<?php echo esc_attr($data['location']['lat']); ?>" />
            </label><br>
            <label><?php echo esc_html__('Longitude:', 'localpoint'); ?>
                <input type="text" name="lng" value="<?php echo esc_attr($data['location']['lng']); ?>" />
            </label><br>

            <div id="map" style="height: 400px; margin: 20px 0;"></div>

            <h2><?php echo esc_html__('Contact', 'localpoint'); ?></h2>
            <label><?php echo esc_html__('Phone:', 'localpoint'); ?>
                <input type="text" name="phone" value="<?php echo esc_attr($data['contact']['phone']); ?>" />
            </label><br>
            <label><?php echo esc_html__('Email:', 'localpoint'); ?>
                <input type="text" name="email" value="<?php echo esc_attr($data['contact']['email']); ?>" />
            </label><br>
            <label><?php echo esc_html__('Address:', 'localpoint'); ?>
                <input type="text" name="address" value="<?php echo esc_attr($data['contact']['address']); ?>" />
            </label><br>
            <label><?php echo esc_html__('Note:', 'localpoint'); ?>
                <input type="text" name="note" value="<?php echo esc_attr($data['contact']['note']); ?>" style="width:100%;max-width:400px;" />
            </label>

            <h2><?php echo esc_html__('Opening hours', 'localpoint'); ?></h2>
            <table class="form-table">
                <?php foreach ($days_keys as $day_key):
                    $day_name = $days_translations[$day_key];
                    $hours = $data['hours'][$day_key];
                ?>
                    <tr>
                        <th><?php echo esc_html($day_name); ?></th>
                        <td>
                            <label><?php echo esc_html__('Open:', 'localpoint'); ?>
                                <input type="time" name="hours[<?php echo esc_attr($day_key); ?>][open]" value="<?php echo esc_attr($hours['open']); ?>" <?php if (!empty($hours['closed'])) echo 'disabled'; ?>>
                            </label>
                            <label><?php echo esc_html__('Close:', 'localpoint'); ?>
                                <input type="time" name="hours[<?php echo esc_attr($day_key); ?>][close]" value="<?php echo esc_attr($hours['close']); ?>" <?php if (!empty($hours['closed'])) echo 'disabled'; ?>>
                            </label>
                            <label><?php echo esc_html__('Closed:', 'localpoint'); ?>
                                <input type="checkbox" name="hours[<?php echo esc_attr($day_key); ?>][closed]" value="1" <?php if (!empty($hours['closed'])) echo 'checked'; ?>>
                            </label>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p><input type="submit" class="button button-primary" value="<?php echo esc_attr__('Save Changes', 'localpoint'); ?>" /></p>
        </form>
    </div>
<?php
}

