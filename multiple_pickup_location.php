<?php
/*
Plugin Name: Custom Shipping Method
Description: Adds a custom shipping method for WooCommerce with regions and cities options.
Version: 1.0
Author: Dmytro Zamaraiev
*/

if ( ! defined( 'WPINC' ) ) {
    die;
}

// Register custom shipping method
add_action( 'woocommerce_shipping_init', 'custom_shipping_method_init' );
function custom_shipping_method_init() {
    class WC_Custom_Shipping_Method extends WC_Shipping_Method {
        public function __construct() {
            $this->id                 = 'custom_shipping_method';
            $this->method_title       = __( 'Custom Shipping Method', 'woocommerce' );
            $this->method_description = __( 'Custom Shipping Method with regions and cities.', 'woocommerce' );
            $this->enabled            = "yes";
            $this->title              = __( 'Custom Shipping', 'woocommerce' );
            $this->init();
        }

        function init() {
            // Load the settings API
            $this->init_form_fields();
            $this->init_settings();
            
            // Save settings in admin if updated
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        // Define settings field for this shipping method
        function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => __( 'Enable', 'woocommerce' ),
                    'type'        => 'checkbox',
                    'description' => __( 'Enable this shipping method.', 'woocommerce' ),
                    'default'     => 'yes'
                ),
                'title' => array(
                    'title'       => __( 'Title', 'woocommerce' ),
                    'type'        => 'text',
                    'description' => __( 'Title to be displayed on the checkout.', 'woocommerce' ),
                    'default'     => __( 'Custom Shipping', 'woocommerce' )
                )
            );
        }

        // Calculate shipping cost
        public function calculate_shipping( $package = array() ) {
            $cost = 0;
            $region = isset( $_POST['shipping_region'] ) ? sanitize_text_field( $_POST['shipping_region'] ) : '';
            $city = isset( $_POST['shipping_city'] ) ? sanitize_text_field( $_POST['shipping_city'] ) : '';

            if ( $region && $city ) {
                // Example: flat rate cost based on region (you can modify this as needed)
                $cost = 10;
            }

            $rate = array(
                'id'    => $this->id,
                'label' => $this->title,
                'cost'  => $cost,
                'calc_tax' => 'per_item'
            );

            $this->add_rate( $rate );
        }
    }
}

// Add custom shipping method to WooCommerce
add_filter( 'woocommerce_shipping_methods', 'add_custom_shipping_method' );
function add_custom_shipping_method( $methods ) {
    $methods['custom_shipping_method'] = 'WC_Custom_Shipping_Method';
    return $methods;
}

// Add custom fields to checkout for selecting region and city
add_action( 'woocommerce_after_order_notes', 'custom_shipping_fields' );
function custom_shipping_fields( $checkout ) {
    echo '<div id="custom_shipping_fields"><h3>' . __('Shipping Region and City') . '</h3>';
    echo '<div class="region-selector">';
    $regions = custom_get_regions_options();
    foreach ( $regions as $region_key => $region_name ) {
        if ( ! empty( $region_key ) ) {
            echo '<div class="region" data-region="' . esc_attr( $region_key ) . '">
                    <button type="button" class="toggle-region">' . esc_html( $region_name ) . '</button>
                    <div class="cities" style="display:none;">';
            $cities = custom_get_cities_by_region( $region_key );
            foreach ( $cities as $city ) {
                echo '<label><input type="radio" name="shipping_city" value="' . esc_attr( $city ) . '"> ' . esc_html( $city ) . '</label><br />';
            }
            echo '  </div>
                  </div>';
        }
    }
    echo '</div>';
    echo '</div>';
}

// Populate cities based on selected region via AJAX
add_action( 'wp_footer', 'custom_shipping_js' );
function custom_shipping_js() {
    if ( is_checkout() ) :
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.toggle-region').click(function() {
                $(this).next('.cities').slideToggle();
            });
        });
    </script>
    <style>
        .region-selector {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
        }
        .region {
            margin-bottom: 10px;
        }
        .toggle-region {
            background-color: #f7f7f7;
            border: none;
            cursor: pointer;
            text-align: left;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
        }
        .cities {
            padding: 10px;
        }
    </style>
    <?php
    endif;
}

// Helper function to get regions options
function custom_get_regions_options() {
    return array(
        '' => __('Select a Region'),
        'Baden-Württemberg' => __('Baden-Württemberg'),
        'Bayern' => __('Bayern'),
        'Berlin' => __('Berlin'),
        'Brandenburg' => __('Brandenburg'),
        'Bremen' => __('Bremen'),
        'Hamburg' => __('Hamburg'),
        'Hessen' => __('Hessen'),
        'Mecklenburg-Vorpommern' => __('Mecklenburg-Vorpommern'),
        'Niedersachsen' => __('Niedersachsen'),
        'Nordrhein-Westfalen' => __('Nordrhein-Westfalen'),
        'Rheinland-Pfalz' => __('Rheinland-Pfalz'),
        'Saarland' => __('Saarland'),
        'Sachsen' => __('Sachsen'),
        'Sachsen-Anhalt' => __('Sachsen-Anhalt'),
        'Schleswig-Holstein' => __('Schleswig-Holstein'),
        'Thüringen' => __('Thüringen')
    );
}

// Helper function to get cities by region
function custom_get_cities_by_region( $region ) {
    $cities = array(
        'Baden-Württemberg' => ["Aalen", "Albstadt", "Backnang", "Bad Dürrheim", "Baden-Baden", "Bruchsal", "Böblingen", "Crailsheim", "Eislingen", "Emmendingen", "Esslingen", "Fellbach", "Freiburg", "Freiburg-City", "Friedrichshafen", "Heidelberg", "Heidelberg-Rohrbach", "Heidenheim", "Heilbronn", "Karlsruhe - Ettlinger Tor", "Karlsruhe-Bulach", "Kirchheim", "Konstanz", "Lahr", "Leinfelden-Echterdingen", "Ludwigsburg", "Mannheim-City", "Mannheim-Neckarau", "Mannheim-Sandhofen", "Mosbach", "Müllheim", "Nagold", "Nürtingen", "Offenburg", "Pforzheim", "Ravensburg", "Reutlingen", "Schorndorf", "Schwäbisch Gmünd", "Schwäbisch Hall", "Sindelfingen", "Singen", "Sinsheim", "Stuttgart Feuerbach", "Stuttgart Milaneo", "Tübingen", "Ulm"],
        'Bayern' => ["Amberg", "Ansbach", "Aschaffenburg", "Aschaffenburg City", "Augsburg-Göggingen", "Augsburg-Oberhausen", "Bad Neustadt", "Bayreuth", "Burghausen", "Coburg", "Deggendorf", "Donauwörth", "Erding", "Erlangen", "Forchheim", "Hallstadt", "Hof", "Ingolstadt", "Karlsfeld", "Kempten", "Kulmbach", "Landsberg am Lech", "Landshut", "Marktredwitz", "Memmingen", "Mühldorf am Inn", "München OEZ", "München PEP", "München-Euroindustriepark", "München-Haidhausen", "München-Pasing", "München-Solln", "Neu-Ulm", "Neuburg an der Donau", "Neumarkt", "Nördlingen", "Nürnberg-City", "Nürnberg-Kleinreuth", "Nürnberg-Langwasser", "Nürnberg-Schoppershof", "Passau", "Regensburg", "Rosenheim", "Schwabach", "Schweinfurt", "Schweinfurt City", "Stadtgalerie Passau", "Straubing", "Traunreut", "Traunstein", "Unterföhring", "Weiden", "Weilheim", "Würzburg", "Würzburg-City", "Würzburg-Dürrbachau"],
        'Berlin' => ["Berlin Friedrichshain", "Berlin-Biesdorf", "Berlin-Charlottenburg", "Berlin-Gropiusstadt", "Berlin-Hohenschönhausen", "Berlin-Mitte", "Berlin-Neukölln", "Berlin-Prenzlauer Berg", "Berlin-Schöneweide", "Berlin-Spandau", "Berlin-Steglitz", "Berlin-Tegel", "Berlin-Tempelhof", "Berlin-Wedding"],
        'Brandenburg' => ["Berlin-Waltersdorf", "Brandenburg", "Cottbus", "Eiche", "Potsdam", "Potsdam-City", "Schwedt"],
        'Bremen' => ["Bremen-Waterfront", "Bremen-Weserpark", "bremen-habenhausen"],
        'Hamburg' => ["Hamburg Poppenbüttel", "Hamburg-Altona", "Hamburg-Billstedt", "Hamburg-Harburg", "Hamburg-Hummelsbüttel", "Hamburg-Nedderfeld", "Hamburg-Wandsbek"],
        'Hessen' => ["Dietzenbach", "Baunatal", "Bischofsheim", "Egelsbach", "Frankfurt-Borsigallee", "Frankfurt-Nordwestzentrum", "Fulda", "Gießen", "Groß Gerau", "Gründau Lieblos", "Hanau", "Heppenheim", "Kassel", "Limburg", "Main Taunus Zentrum", "Marburg", "Pfungstadt", "Viernheim", "Weinheim", "Weiterstadt", "Wetzlar", "Wiesbaden-Hasengarten", "Wiesbaden-Äppelallee"],
        'Mecklenburg-Vorpommern' => ["Greifswald", "Neubrandenburg", "Rostock-Brinckmansdorf", "Rostock-City", "Rostock-Sievershagen", "Schwerin", "Stralsund"],
        'Niedersachsen' => ["Belm-Osnabrück", "Braunschweig", "Bremerhaven-Schiffdorf-Spaden", "Buchholz in der Nordheide", "Buxtehude", "Celle", "Dein MediaMarkt Wolfsburg-City", "Delmenhorst", "Emden", "Gifhorn", "Goslar", "Göttingen", "Hameln", "Hannover Ernst-August-Platz", "Hannover-Vahrenheide", "Hannover-Wülfel", "Hildesheim", "Holzminden", "Isernhagen", "Leer", "Lingen", "Lüneburg", "Nienburg", "Nordhorn", "Oldenburg", "Osnabrück-City", "Papenburg", "Peine", "Salzgitter", "Stade", "Stadthagen", "Stuhr", "Wilhelmshaven", "Wolfsburg"],
        'Nordrhein-Westfalen' => ["Aachen", "Bergisch Gladbach", "Bielefeld", "Bocholt", "Bochum-Hofstede", "Bochum-Ruhrpark", "Bonn", "Bornheim", "Castrop-Rauxel", "Dein MediaMarkt Dortmund-Eving", "Dorsten", "Dortmund-Hörde", "Dortmund-Oespel", "Duisburg-Großenbaum", "Duisburg-Marxloh", "Düren", "Düsseldorf-Bilk", "Düsseldorf-Metrostraße", "Eschweiler", "Essen", "Gütersloh", "Hagen", "Herzogenrath", "Hückelhoven", "Hürth", "Kerpen", "Krefeld", "Köln-City am Dom", "Köln-Kalk", "Köln-Marsdorf", "Lippstadt", "Lüdenscheid", "Marl", "Mönchengladbach", "Mülheim", "Münster", "Neuss", "Paderborn", "Porta Westfalica (Minden)", "Recklinghausen", "Rheine", "Siegen", "Velbert", "Wuppertal", "Wuppertal - City"],
        'Rheinland-Pfalz' => ["Alzey", "Bad Kreuznach", "Idar-Oberstein", "Kaiserslautern", "Koblenz", "Landau", "Ludwigshafen-Oggersheim (im Einkaufspark Oggersheim)", "Mainz", "Mainz-City", "Neustadt an der Weinstraße", "Neuwied", "Pirmasens", "Speyer", "Trier", "Worms"],
        'Saarland' => ["Homburg", "Neunkirchen", "Saarbrücken auf den Saarterrassen", "Saarbrücken-Saarbasar", "Saarlouis"],
        'Sachsen' => ["Leipzig Höfe am Brühl", "Chemnitz-Röhrsdorf", "Chemnitz-Sachsenallee (im EKZ Sachsenallee)", "Dresden Centrum", "Dresden-Mickten", "Dresden-Prohlis", "Leipzig-Paunsdorf (im Paunsdorf-Center)", "Meerane", "Plauen", "Riesa", "Zwickau", "Zwickau am Glueck auf Center"],
        'Sachsen-Anhalt' => ["Dessau", "Dessau-City", "Günthersdorf", "Halberstadt", "Halle", "Madgeburg-Bördepark", "Magdeburg-City", "Magdeburg-Pfahlberg"],
        'Schleswig-Holstein' => ["Elmshorn", "Flensburg", "Halstenbek", "Hamburg-Oststeinbek", "Heide", "Henstedt-Ulzburg", "Itzehoe", "Kiel", "Kiel-Schwentinental", "Kiel-Sophienhof", "Lübeck", "Lübeck-Dänischburg", "Neumünster", "Rendsburg"],
        'Thüringen' => ["Erfurt Thüringen Park", "Eisenach", "Erfurt T.E.C.", "Jena", "Jena City", "Nordhausen", "Zella-Mehlis"]
    );
    return $cities[ $region ] ?? array();
}
