<?php
/**
 * Plugin Name: BP Groups Location
 * Description: Geolocation for Buddypress/Buddyboss Groups.
 * Version:     1.0.0
 * Author:      BuddyActivist
 * Text Domain: bp-groups-location
 * Domain Path: /languages
 * License:     GPL3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load plugin textdomain for translations.
 */
function bgl_load_textdomain() {
    load_plugin_textdomain(
        'bp-groups-location',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'plugins_loaded', 'bgl_load_textdomain' );

/* ----------------------------------------------------------
 * 1. ENQUEUE LEAFLET + MARKERCLUSTER ASSETS
 * ---------------------------------------------------------- */

/**
 * Enqueue Leaflet and MarkerCluster from CDN.
 */
function bgl_enqueue_leaflet_assets() {

    // Leaflet core
    wp_enqueue_style(
        'bgl-leaflet',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        array(),
        '1.9.4'
    );

    wp_enqueue_script(
        'bgl-leaflet',
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        array(),
        '1.9.4',
        true
    );

    // MarkerCluster styles
    wp_enqueue_style(
        'bgl-leaflet-markercluster',
        'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css',
        array(),
        '1.5.3'
    );

    wp_enqueue_style(
        'bgl-leaflet-markercluster-default',
        'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css',
        array( 'bgl-leaflet-markercluster' ),
        '1.5.3'
    );

    // MarkerCluster script
    wp_enqueue_script(
        'bgl-leaflet-markercluster',
        'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js',
        array( 'bgl-leaflet' ),
        '1.5.3',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'bgl_enqueue_leaflet_assets' );

/* ----------------------------------------------------------
 * 2. GROUP ADMIN FIELD: GEOLOCATED ADDRESS
 * ---------------------------------------------------------- */

/**
 * Output the "Geolocated address" field in group admin (create/edit).
 */
function bgl_group_location_field_markup() {
    if ( ! function_exists( 'bp_get_group_id' ) ) {
        return;
    }

    $location = groups_get_groupmeta( bp_get_group_id(), 'group-location', true );
    ?>
    <div class="bgl-group-location-field">
        <label for="group-location">
            <strong><?php esc_html_e( 'Geolocated address', 'bp-groups-location' ); ?></strong>
        </label>
        <input
            type="text"
            id="group-location"
            name="group-location"
            value="<?php echo esc_attr( $location ); ?>"
            style="width:100%; max-width:400px;"
        >
        <p class="description">
            <?php esc_html_e( 'Enter an address, city, or place name.', 'bp-groups-location' ); ?>
        </p>
    </div>
    <?php
}
add_action( 'groups_custom_group_fields_editable', 'bgl_group_location_field_markup' );

/**
 * Save the "Geolocated address" field when group details are saved.
 *
 * @param int $group_id Group ID.
 */
function bgl_group_location_field_save( $group_id ) {
    if ( ! isset( $_POST['group-location'] ) ) {
        return;
    }

    $raw   = wp_unslash( $_POST['group-location'] );
    $value = sanitize_text_field( $raw );

    if ( ! empty( $value ) ) {
        groups_update_groupmeta( $group_id, 'group-location', $value );
    } else {
        groups_delete_groupmeta( $group_id, 'group-location' );
    }
}
add_action( 'groups_group_details_edited', 'bgl_group_location_field_save' );
add_action( 'groups_created_group', 'bgl_group_location_field_save' );

/* ----------------------------------------------------------
 * 3. GROUP TAB: "MAP"
 * ---------------------------------------------------------- */

/**
 * Add a "Map" tab to the group navigation.
 */
function bgl_add_group_map_tab() {

    if ( ! function_exists( 'bp_is_group' ) || ! bp_is_group() ) {
        return;
    }

    $group = groups_get_current_group();
    if ( empty( $group ) ) {
        return;
    }

    bp_core_new_subnav_item(
        array(
            'name'            => __( 'Map', 'bp-groups-location' ),
            'slug'            => 'map',
            'parent_slug'     => bp_get_current_group_slug(),
            'parent_url'      => bp_get_group_permalink( $group ),
            'screen_function' => 'bgl_group_map_screen',
            'position'        => 40,
            'user_has_access' => true,
        )
    );
}
add_action( 'bp_groups_setup_nav', 'bgl_add_group_map_tab' );

/**
 * Screen function for the "Map" tab.
 */
function bgl_group_map_screen() {
    add_action( 'bp_template_content', 'bgl_group_map_screen_content' );
    bp_core_load_template( 'groups/single/plugins' );
}

/**
 * Content of the "Map" tab: uses the single group map shortcode with current group ID.
 */
function bgl_group_map_screen_content() {
    if ( function_exists( 'bp_get_group_id' ) ) {
        $group_id = bp_get_group_id();
        echo do_shortcode( '[group_location_map id="' . (int) $group_id . '"]' );
    } else {
        echo '<p>' . esc_html__( 'Group context not available.', 'bp-groups-location' ) . '</p>';
    }
}

/* ----------------------------------------------------------
 * 4. SHORTCODE: SINGLE GROUP MAP (REQUIRES ID)
 * ---------------------------------------------------------- */

/**
 * Shortcode [group_location_map id="123"]
 * Shows an OSM map for a specific group ID.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function bgl_group_location_map_shortcode( $atts = array() ) {

    $atts = shortcode_atts(
        array(
            'id' => 0, // group ID
        ),
        $atts,
        'group_location_map'
    );

    $group_id = (int) $atts['id'];

    if ( $group_id <= 0 ) {
        return '<p>' . esc_html__( 'Error: you must provide a valid group ID.', 'bp-groups-location' ) . '</p>';
    }

    $group = groups_get_group( array( 'group_id' => $group_id ) );
    if ( empty( $group ) || empty( $group->id ) ) {
        return '<p>' . esc_html__( 'Error: the specified group does not exist.', 'bp-groups-location' ) . '</p>';
    }

    $location = groups_get_groupmeta( $group_id, 'group-location', true );
    if ( empty( $location ) ) {
        return '<p>' . esc_html__( 'This group has no location set.', 'bp-groups-location' ) . '</p>';
    }

    $map_id = 'bgl-group-map-' . $group_id;

    ob_start();
    ?>
    <div id="<?php echo esc_attr( $map_id ); ?>" style="width:100%; height:400px;"></div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {

        const locationString = "<?php echo esc_js( $location ); ?>";
        const mapId = "<?php echo esc_js( $map_id ); ?>";

        fetch("https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(locationString))
            .then(response => response.json())
            .then(data => {

                if (!data || !data.length) {
                    document.getElementById(mapId).innerHTML =
                        "<?php echo esc_js( __( 'Unable to find this location.', 'bp-groups-location' ) ); ?>";
                    return;
                }

                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);

                const map = L.map(mapId).setView([lat, lon], 13);

                L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    maxZoom: 19,
                    attribution: "© OpenStreetMap contributors"
                }).addTo(map);

                L.marker([lat, lon]).addTo(map)
                    .bindPopup(locationString)
                    .openPopup();
            })
            .catch(() => {
                document.getElementById(mapId).innerHTML =
                    "<?php echo esc_js( __( 'Error loading the map.', 'bp-groups-location' ) ); ?>";
            });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'group_location_map', 'bgl_group_location_map_shortcode' );

/* ----------------------------------------------------------
 * 5. SHORTCODE: ALL GROUPS MAP (WITH CLUSTERING)
 * ---------------------------------------------------------- */

/**
 * Shortcode [all_groups_map]
 * Shows an OSM map with all groups that have a location,
 * using marker clustering.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function bgl_all_groups_map_shortcode( $atts = array() ) {

    $atts = shortcode_atts(
        array(
            'per_page' => 9999,
        ),
        $atts,
        'all_groups_map'
    );

    $groups = groups_get_groups(
        array(
            'per_page'    => (int) $atts['per_page'],
            'show_hidden' => false,
        )
    );

    if ( empty( $groups['groups'] ) ) {
        return '<p>' . esc_html__( 'No groups found.', 'bp-groups-location' ) . '</p>';
    }

    $data = array();

    foreach ( $groups['groups'] as $g ) {
        $loc = groups_get_groupmeta( $g->id, 'group-location', true );
        if ( ! empty( $loc ) ) {
            $data[] = array(
                'name'     => $g->name,
                'url'      => bp_get_group_permalink( $g ),
                'location' => $loc,
            );
        }
    }

    if ( empty( $data ) ) {
        return '<p>' . esc_html__( 'No groups with a location set.', 'bp-groups-location' ) . '</p>';
    }

    $map_id = 'bgl-all-groups-map-' . wp_generate_uuid4();
    $json   = wp_json_encode( $data );

    ob_start();
    ?>
    <div id="<?php echo esc_attr( $map_id ); ?>" style="width:100%; height:600px;"></div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {

        const mapId  = "<?php echo esc_js( $map_id ); ?>";
        const groups = <?php echo $json; ?>;

        const map = L.map(mapId).setView([41.9, 12.5], 5); // Default: Italy

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution: "© OpenStreetMap contributors"
        }).addTo(map);

        const cluster = L.markerClusterGroup();
        const geocodePromises = groups.map(group => {
            return fetch("https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(group.location))
                .then(response => response.json())
                .then(data => {
                    if (!data || !data.length) {
                        return null;
                    }

                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);

                    const marker = L.marker([lat, lon]);
                    marker.bindPopup(
                        "<strong>" + group.name + "</strong><br>" +
                        "<a href='" + group.url + "'><?php echo esc_js( __( 'View group', 'bp-groups-location' ) ); ?></a><br>" +
                        group.location
                    );

                    cluster.addLayer(marker);
                    return { lat, lon };
                })
                .catch(() => null);
        });

        Promise.all(geocodePromises).then(points => {
            map.addLayer(cluster);

            const validPoints = points.filter(p => p !== null);
            if (validPoints.length) {
                const bounds = L.latLngBounds(validPoints.map(p => [p.lat, p.lon]));
                map.fitBounds(bounds, { padding: [20, 20] });
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'all_groups_map', 'bgl_all_groups_map_shortcode' );
