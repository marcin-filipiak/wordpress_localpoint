<?php
/*
 * Plugin Name:       LocalPoint
 * Plugin URI:        https://github.com/marcin-filipiak/wordpress_localpoint
 * Description:       Display your business location, opening hours and contact info using OpenStreetMap.
 * Version:           2.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Marcin Filipiak
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       localpoint
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue assets
add_action('wp_enqueue_scripts', 'localpoint_enqueue_assets');
function localpoint_enqueue_assets() {
    // Zakładamy, że leaflet.css i leaflet.js są w folderze assets/js i assets/css
    wp_enqueue_style('leaflet', plugin_dir_url(__FILE__) . 'assets/css/leaflet.css');
    wp_enqueue_script('leaflet', plugin_dir_url(__FILE__) . 'assets/js/leaflet.js', [], null, true);

    wp_enqueue_style('localpoint-style', plugin_dir_url(__FILE__) . 'assets/style.css');

    wp_register_script('localpoint-map', plugin_dir_url(__FILE__) . 'assets/js/map.js', ['leaflet'], null, true);

    $data = get_option('localpoint_data', []);
    wp_localize_script('localpoint-map', 'localpointData', $data);

    wp_enqueue_script('localpoint-map');
}

// Shortcode
add_shortcode('localpoint', 'localpoint_shortcode');
function localpoint_shortcode() {
    ob_start();
    $data = get_option('localpoint_data', []);

    $lat = isset($data['location']['lat']) ? floatval($data['location']['lat']) : 0;
    $lng = isset($data['location']['lng']) ? floatval($data['location']['lng']) : 0;

    $weekdays = [
        'monday'    => esc_html__('Monday', 'localpoint'),
        'tuesday'   => esc_html__('Tuesday', 'localpoint'),
        'wednesday' => esc_html__('Wednesday', 'localpoint'),
        'thursday'  => esc_html__('Thursday', 'localpoint'),
        'friday'    => esc_html__('Friday', 'localpoint'),
        'saturday'  => esc_html__('Saturday', 'localpoint'),
        'sunday'    => esc_html__('Sunday', 'localpoint'),
    ];
    ?>

    <div id="localpoint-map"></div>

    <div id="localpoint-info">
        <?php if (!empty($data)): ?>
            <h3><?php echo esc_html__('Contact', 'localpoint'); ?></h3>
            <p><?php echo esc_html__('Phone:', 'localpoint') . ' ' . esc_html($data['contact']['phone'] ?? '-'); ?></p>
            <p><?php echo esc_html__('Email:', 'localpoint') . ' ' . esc_html($data['contact']['email'] ?? '-'); ?></p>
            <p><?php echo esc_html__('Address:', 'localpoint') . ' ' . esc_html($data['contact']['address'] ?? '-'); ?></p>
            <?php if (!empty($data['contact']['note'])): ?>
                <p><em><?php echo esc_html($data['contact']['note']); ?></em></p>
            <?php endif; ?>

            <h3><?php echo esc_html__('Opening hours', 'localpoint'); ?></h3>
            <table>
                <?php if (!empty($data['hours']) && is_array($data['hours'])): ?>
                    <?php foreach ($data['hours'] as $day => $hours): ?>
                        <tr>
                            <td>
                                <?php 
                                $day_label = isset($weekdays[strtolower($day)]) ? $weekdays[strtolower($day)] : esc_html(ucfirst($day)); 
                                echo esc_html($day_label); 
                                ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($hours['closed']) && $hours['closed'] === true) {
                                    echo esc_html__('Closed', 'localpoint');
                                } else {
                                    echo esc_html($hours['open'] ?? '') . ' - ' . esc_html($hours['close'] ?? '');
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2"><?php echo esc_html__('No opening hours data', 'localpoint'); ?></td></tr>
                <?php endif; ?>
            </table>
        <?php else: ?>
            <p><?php echo esc_html__('No data to display.', 'localpoint'); ?></p>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof L !== "undefined") {
            var map = L.map('localpoint-map').setView([<?php echo esc_js($lat); ?>, <?php echo esc_js($lng); ?>], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            L.marker([<?php echo esc_js($lat); ?>, <?php echo esc_js($lng); ?>]).addTo(map);
        } else {
            console.error('Leaflet.js is not loaded');
        }
    });
    </script>

    <?php
    return ob_get_clean();
}

// Admin menu
add_action('admin_menu', 'localpoint_admin_menu');
function localpoint_admin_menu() {
    add_menu_page(
        esc_html__('LocalPoint Settings', 'localpoint'),
        esc_html__('LocalPoint', 'localpoint'),
        'manage_options',
        'localpoint-settings',
        'localpoint_settings_page',
        'dashicons-location-alt'
    );
}

// Admin interface logic
require_once plugin_dir_path(__FILE__) . 'admin-page.php';

