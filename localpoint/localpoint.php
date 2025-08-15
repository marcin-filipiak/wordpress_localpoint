<?php
/*
 * Plugin Name:       LocalPoint
 * Plugin URI:        https://github.com/marcin-filipiak/wordpress_localpoint
 * Description:       Display your business location, opening hours and contact info using OpenStreetMap.
 * Version:           2.1
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
    wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], null, true);

    wp_enqueue_style('localpoint-style', plugin_dir_url(__FILE__) . 'assets/style.css');

    $json_data = get_option('localpoint_data', []);
    if (!empty($json_data)) {
        wp_localize_script('localpoint-map', 'localpointData', $json_data);
    }
}

// Shortcode
add_shortcode('localpoint', 'localpoint_shortcode');
function localpoint_shortcode() {
    ob_start();

    $data = get_option('localpoint_data', []);

    $lat = $data['location']['lat'] ?? 0;
    $lng = $data['location']['lng'] ?? 0;

    $weekdays = [
        'monday'    => __('Monday', 'localpoint'),
        'tuesday'   => __('Tuesday', 'localpoint'),
        'wednesday' => __('Wednesday', 'localpoint'),
        'thursday'  => __('Thursday', 'localpoint'),
        'friday'    => __('Friday', 'localpoint'),
        'saturday'  => __('Saturday', 'localpoint'),
        'sunday'    => __('Sunday', 'localpoint'),
    ];
    ?>

    <div id="localpoint-map"></div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof L !== "undefined") {
            var map = L.map('localpoint-map').setView([<?php echo esc_js($lat); ?>, <?php echo esc_js($lng); ?>], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            L.marker([<?php echo esc_js($lat); ?>, <?php echo esc_js($lng); ?>]).addTo(map);
        } else {
            console.error('Leaflet.js is not loaded');
        }
    });
    </script>

    <div id="localpoint-info">
        <?php if (!empty($data)): ?>
            <h3><?php _e('Contact', 'localpoint'); ?></h3>
            <p><?php _e('Phone:', 'localpoint'); ?> <?php echo esc_html($data['contact']['phone'] ?? '-'); ?></p>
            <p><?php _e('Email:', 'localpoint'); ?> <?php echo esc_html($data['contact']['email'] ?? '-'); ?></p>
            <p><?php _e('Address:', 'localpoint'); ?> <?php echo esc_html($data['contact']['address'] ?? '-'); ?></p>
            <?php if (!empty($data['contact']['note'])): ?>
                <p><em><?php echo esc_html($data['contact']['note']); ?></em></p>
            <?php endif; ?>

            <h3><?php _e('Opening hours', 'localpoint'); ?></h3>
            <table>
                <?php if (!empty($data['hours']) && is_array($data['hours'])): ?>
                    <?php foreach ($data['hours'] as $day => $hours): ?>
                        <tr>
                            <td><?php echo isset($weekdays[strtolower($day)]) ? esc_html($weekdays[strtolower($day)]) : ucfirst(esc_html($day)); ?></td>
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
                    <tr><td colspan="2"><?php _e('No opening hours data', 'localpoint'); ?></td></tr>
                <?php endif; ?>
            </table>
        <?php else: ?>
            <p><?php _e('No data to display.', 'localpoint'); ?></p>
        <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
}

// Admin menu
add_action('admin_menu', 'localpoint_admin_menu');
function localpoint_admin_menu() {
    add_menu_page(
        __('LocalPoint Settings', 'localpoint'),
        __('LocalPoint', 'localpoint'),
        'manage_options',
        'localpoint-settings',
        'localpoint_settings_page',
        'dashicons-location-alt'
    );
}

// Admin interface logic
require_once plugin_dir_path(__FILE__) . 'admin-page.php';

