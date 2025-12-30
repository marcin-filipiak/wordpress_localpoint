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

define('LOCALPOINT_VERSION', '2.0');

/*
 * Enqueue assets (frontend)
 */
add_action('wp_enqueue_scripts', 'localpoint_enqueue_assets');
function localpoint_enqueue_assets() {

    if (!is_singular()) {
        return;
    }

    global $post;
    if (!$post || !has_shortcode($post->post_content, 'localpoint')) {
        return;
    }

    wp_enqueue_style(
        'leaflet',
        plugin_dir_url(__FILE__) . 'assets/css/leaflet.css',
        [],
        LOCALPOINT_VERSION
    );

    wp_enqueue_script(
        'leaflet',
        plugin_dir_url(__FILE__) . 'assets/js/leaflet.js',
        [],
        LOCALPOINT_VERSION,
        true
    );

    wp_enqueue_style(
        'localpoint-style',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        [],
        LOCALPOINT_VERSION
    );

    wp_enqueue_script(
        'localpoint-map',
        plugin_dir_url(__FILE__) . 'assets/js/map.js',
        ['leaflet'],
        LOCALPOINT_VERSION,
        true
    );

    wp_localize_script(
        'localpoint-map',
        'localpointData',
        get_option('localpoint_data', [])
    );
}

/*
 * Shortcode
 */
add_shortcode('localpoint', 'localpoint_shortcode');
function localpoint_shortcode() {
    ob_start();

    $data = get_option('localpoint_data', []);

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
                                $key = strtolower($day);
                                echo esc_html($weekdays[$key] ?? ucfirst($day));
                                ?>
                            </td>
                            <td>
                                <?php
                                if (!empty($hours['closed'])) {
                                    echo esc_html__('Closed', 'localpoint');
                                } else {
                                    echo esc_html($hours['open'] ?? '') . ' - ' . esc_html($hours['close'] ?? '');
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2"><?php echo esc_html__('No opening hours data', 'localpoint'); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        <?php else: ?>
            <p><?php echo esc_html__('No data to display.', 'localpoint'); ?></p>
        <?php endif; ?>
    </div>

    <?php
    return ob_get_clean();
}

/*
 * Admin menu
 */
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

require_once plugin_dir_path(__FILE__) . 'admin-page.php';

