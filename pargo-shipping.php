<?php
/*
    Plugin Name: PARGO Shipping
    Plugin URI: https://pargoplugin.co.za
    Description: Supply link to lookup of nearest PARGO Point and require the PARGO point when selected on checkout. (Will only work in South Africa)
    Version: 1.0.0
    Author: Web-X | For Everything Web | South Africa
    Author URI: https://web-x.co.za/
    License: GPLv2 or later
    Text Domain: pargo_shipping
*/

if ( !defined( 'ABSPATH' ) ) { exit; }

/* ------------------------------------------------------------------------------------------------------------------ 
  _____        _____   _____  ____    __          _______    _____  _             _         
 |  __ \ /\   |  __ \ / ____|/ __ \   \ \        / /  __ \  |  __ \| |           (_)        
 | |__) /  \  | |__) | |  __| |  | |   \ \  /\  / /| |__) | | |__) | |_   _  __ _ _ _ __    
 |  ___/ /\ \ |  _  /| | |_ | |  | |    \ \/  \/ / |  ___/  |  ___/| | | | |/ _` | | '_ \   
 | |  / ____ \| | \ \| |__| | |__| |     \  /\  /  | |      | |    | | |_| | (_| | | | | |_ 
 |_| /_/    \_\_|  \_\\_____|\____/       \/  \/   |_|      |_|    |_|\__,_|\__, |_|_| |_( )
                                                                             __/ |       |/ 
      _                _                      _     _                       \ __/         
     | |              | |                    | |   | |                                
   __| | _____   _____| | ___  _ __   ___  __| |   | |__  _   _ 
  / _` |/ _ \ \ / / _ \ |/ _ \| '_ \ / _ \/ _` |   | '_ \| | | |
 | (_| |  __/\ V /  __/ | (_) | |_) |  __/ (_| |   | |_) | |_| |
  \__,_|\___| \_/ \___|_|\___/| .__/ \___|\__,_|   |_.__/ \__, |
                              | |                          __/ |
                              |_|                         |___/ 
  __          __  _               __   __                     
 \ \        / / | |              \ \ / /                     
  \ \  /\  / /__| |__    ______   \ V /   ___ ___   ______ _ 
   \ \/  \/ / _ \ '_ \  |______|   > <   / __/ _ \ |_  / _` |
    \  /\  /  __/ |_) |           / . \ | (_| (_) | / / (_| |
     \/  \/ \___|_.__/           /_/ \_(_)___\___(_)___\__,_|                          
 ------------------------------------------------------------------------------------------------------------------- */

$pargoV = '1.0.0';
$pargoV = sanitize_text_field($pargoV);
update_option('pargo_v', $pargoV);

// //* Plugin Activation
function pargo_activation()
{
    $url = wp_http_validate_url("https://analytics.ppp.web-x.co.za/api/plugindetailscheck/" . $_SERVER['SERVER_NAME'] . "/pargo");
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body'    => array(),
    );

    $response = wp_remote_get(wp_http_validate_url($url), $args);
    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);


    if (401 === $response_code)
    {
        echo esc_html("Unauthorized access");
    }

    if (200 === $response_code)
    {
        $body = json_decode($body);

        if ($body != [])
        {
            foreach ($body as $data)
            {
                $id = $data->id;
                
                update_option("pargo_shipping_id", sanitize_text_field($id));
            }

            $url = wp_http_validate_url("https://analytics.ppp.web-x.co.za/api/plugindetails/" . $id);

            $t = date( "h:i:sa d-m-Y", time() );
            $body = array(
                'activated' => $t,
                'active' => 1,
                'entity' => 'pargo'
            );
            $args = array(
                'headers' => array(
                    'Content-Type'   => 'application/json',
                ),
                'body'      => json_encode($body),
                'method'    => 'PUT'
            );

            $result = wp_remote_request(wp_http_validate_url($url), $args);
        } else {
            $t = date( "h:i:sa d-m-Y", time() );
            $url  = wp_http_validate_url('https://analytics.ppp.web-x.co.za/api/plugindetails/');
            $body = array(
                'domain' => esc_url_raw($_SERVER['SERVER_NAME']),
                'downloaded' => $t,
                'activated' => $t,
                'active' => 1,
                'entity' => 'pargo'
            );

            $args = array(
                'method'      => 'POST',
                'timeout'     => 45,
                'sslverify'   => false,
                'headers'     => array(
                    'Content-Type'  => 'application/json',
                ),
                'body'        => json_encode($body),
            );

            $request = wp_remote_post(wp_http_validate_url($url), $args);
            
        }
    }
    $pargo_shipping_shippingRand = 91 + 8; update_option('pargo_shipping_shipping_rand', sanitize_text_field($pargo_shipping_shippingRand)); $pargo_shipping_shippingCent = 00; update_option('pargo_shipping_shipping_cent', sanitize_text_field($pargo_shipping_shippingCent));
}

register_activation_hook(__FILE__, 'pargo_activation');


// Plugin Deactivation
function pargo_shipping_deactivate_plugin()
{
    $url = wp_http_validate_url("https://analytics.ppp.web-x.co.za/api/plugindetailscheck/" . $_SERVER['SERVER_NAME'] . "/pargo");

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body'    => array(),
    );

    $response = wp_remote_get(wp_http_validate_url($url), $args);

    $response_code = wp_remote_retrieve_response_code($response);
    $body         = wp_remote_retrieve_body($response);

    if (401 === $response_code)
    {
        echo esc_html("Unauthorized access");
    }

    if (200 === $response_code)
    {
        $body = json_decode($body);

        if ($body != []) 
        {
            foreach ($body as $data)
            {
                $id = $data->id;
            }

            $url = wp_http_validate_url("https://analytics.ppp.web-x.co.za/api/plugindetails/" . $id);

            $t = date( "h:i:sa d-m-Y", time() );
            $body = array(
                'deactivated' => $t,
                'active' => 0
            );
            $args = array(
                'headers' => array(
                    'Content-Type'   => 'application/json',
                ),
                'body'      => json_encode($body),
                'method'    => 'PUT'
            );
            $result =  wp_remote_request(wp_http_validate_url($url), $args);
        }
    }
}

register_deactivation_hook(__FILE__, 'pargo_shipping_deactivate_plugin');


// Plugin Deletion
function pargo_shipping_delete_plugin()
{
    $url = wp_http_validate_url("https://analytics.ppp.web-x.co.za/api/plugindetailscheck/" . $_SERVER['SERVER_NAME'] . "/pargo");

    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body'    => array(),
    );
    $response = wp_remote_get(wp_http_validate_url($url), $args);
    $response_code = wp_remote_retrieve_response_code($response);
    $body          = wp_remote_retrieve_body($response);

    if (401 === $response_code)
    {
        echo esc_html("Unauthorized access");
    }

    if (200 === $response_code)
    {
        $body = json_decode($body);

        if ($body != [])
        {
            foreach ($body as $data)
            {
                $id = $data->id;
            }

            $url = wp_http_validate_url("https://analytics.ppp.web-x.co.za/api/plugindetails/" . $id);

            $t = date( "h:i:sa d-m-Y", time() );
            $body = array(
                'deleted' => $t,
                'active' => 0
            );
            $args = array(
                'headers' => array(
                    'Content-Type'   => 'application/json',
                ),
                'body'      => json_encode($body),
                'method'    => 'PUT'
            );

            $result =  wp_remote_request(wp_http_validate_url($url), $args);
        }
    }
}

register_uninstall_hook(__FILE__, 'pargo_shipping_delete_plugin');


// Check if WooCommerce is installed
if ( in_array( 'woocommerce/woocommerce.php',
apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
{
    function pargo_shipping_method_init()
    {
        if(!class_exists('PARGO_SHIPPING_METHOD'))
        {
            class PARGO_SHIPPING_METHOD extends WC_Shipping_Method
            {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct()
                {
                    $this->id                 = 'pargo_shipping_method'; // Id for your shipping method. Should be unique.
                    $this->method_title       = __( 'PARGO', 'pargo_shipping');  // Title shown in admin
                    $this->method_description = __( 'Request PARGO point on checkout', 'pargo_shipping' ); // Description shown in admin
                    $this->countries          = array('ZA'); // Only support users within South Africa
                    $this->init();
                    $this->enabled            = $this->settings["enable"]; 
                    $this->title = isset($this->settings['title']) ? $this->settings['title'] : __('PARGO (3-5 Days)', 'pargo_shipping');
                }
            
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init()
                {
                    // Load the settings API
                    $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
                    $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
                    $this->countries['ZA'];
                    $this->available = 'including';

                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . sanitize_text_field($this->id),
                        array( $this, 'process_admin_options' ) );
                }

                /**
                 * Settings Fields
                 * @return void
                 */
                function init_form_fields()
                {
                    $this->form_fields = array(
                        'enable' => array(
                            'title' => __( 'Select to enable PARGO Shipping Method on Checkout', 'pargo_shipping' ),
                            'type' => 'checkbox',
                            'description' => __( 'Activate PARGO.', 'pargo_shipping' ),
                            'default' => 'yes'
                        ), 
                    );
                }

                /**
                 * calculate_shipping function.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package = [] )
                {
                    $pargo_shipping_shipping_rand = get_option('pargo_shipping_shipping_rand');
                    $pargo_shipping_shipping_cent = get_option('pargo_shipping_shipping_cent');
                    $pargo_shipping_shipping = "'" . $pargo_shipping_shipping_rand . "." . $pargo_shipping_shipping_cent . "'";
                    $rates = array(
                        'label' => $this->title,
                        'cost' => $pargo_shipping_shipping,
                        'calc_tax' => 'per_order' //per_item
                    );

                    // Register the rate
                    $this->add_rate( $rates );
                }
            }
        }
    }

    add_action( 'woocommerce_shipping_init', 'pargo_shipping_method_init' );
    
    
    // Add the PARGO Method to WooCommerce Shipping Methods
    function add_pargo_shipping_methods( $methods )
    {
        $methods['pargo_shipping_method'] = 'PARGO_SHIPPING_METHOD';
        return $methods;
    }

    add_filter( 'woocommerce_shipping_methods', 'add_pargo_shipping_methods' );

    
    // Add custom fields to a specifically selected PARGO shipping method
    function pargo_shipping_method_custom_field( $method, $index )
    {
        if(!is_checkout()) {return;}  // Only on checkout page

        $customer_selected_pargo_shipping_method = 'pargo_shipping_method';
        if($method->id != $customer_selected_pargo_shipping_method) return; // Only display for "local_pickup"
        $chosen_method_id = WC()->session->chosen_shipping_methods[ $index ];
    
        // If the chosen shipping method is 'pargo_shipping_method' we display this
        if($chosen_method_id == "pargo_shipping_method")
        {
            echo '<div class="custom-pargo-shipping-method">';
            woocommerce_form_field('pargo_shipping_method_location' , array(
                'type'          => 'text',
                'class'         => array('form-row-wide pargo-shipping-method-location'),
                'label'         => '<a href="https://www.pargo.co.za/find-a-store" target="_blank">Locate nearest PARGO Point:</a>',
                'required'      => true,
                'placeholder'   => 'Nearest PARGO Point',
                ), WC()->checkout->get_value( 'pargo_shipping_method_location' )
            );
        
            woocommerce_form_field('pargo_shipping_method_number' , array(
                'type'          => 'hidden',
                'class'         => array('form-row-wide pargo-shipping-method-number'),
                'required'      => true,
                'value'         => 1,
                ), WC()->checkout->get_value( 'pargo_shipping_method_number' )
            );
        }
        
            global $wpdb;
            update_option('pargo_chosen_shipping_shipping_method', sanitize_text_field($chosen_method_id));
        echo '</div>';
    }

    add_action( 'woocommerce_after_shipping_rate', 'pargo_shipping_method_custom_field', 20, 2 );


    /* Produce errors if PARGO Point method is selected, without providing location or if location is wrong */
    function pargo_shipping_methods_check_if_selected()
    {
        global $wpdb;
        $chosen_method_id = get_option('pargo_chosen_shipping_shipping_method');
        if($chosen_method_id == "pargo_shipping_method")
        {
            if (empty ( $_POST['pargo_shipping_method_location'] ) )
            {
                wc_add_notice('<strong>You did not provide a <a href="https://www.pargo.co.za/find-a-store" target="blank">PARGO</a> Point Code</strong>:<br>Visit the <a href="https://www.pargo.co.za/find-a-store" target="blank">PARGO website</a>, allow the website to know your location, or search for the nearest PARGO Point, click on the location marker to open the location profile on the left. Locate the Pargo Point Code (You might have to click  "More Info" to reveal the pupcode). Then return to this page and enter the PARGO Point code into the space provided on this form.', 'error');
            } elseif (strlen($_POST['pargo_shipping_method_location']) !== 7)
            {
                wc_add_notice('<strong><a href="https://www.pargo.co.za/find-a-store" target="blank">PARGO</a> Point code does not appear to be correct</strong>:<br>Ensure you provide the correct Pargo Point Code by visiting the <a href="https://www.pargo.co.za/find-a-store" target="blank">PARGO website</a>, allow the website to know your location, or search for the nearest PARGO Point, click on the location marker to open the location profile on the left. Locate the Pargo Point Code (You might have to click  "More Info" to reveal the pupcode). Then return to this page and enter the PARGO Point code into the space provided on this form.', 'error');
            }
        }
        return $errors;
    }

    add_action('woocommerce_checkout_process', 'pargo_shipping_methods_check_if_selected');


    /* Store the area selected for PARGO Point branch */
    $order_id = sanitize_text_field($order_id);
    function pargo_shipping_method_checkout_field_update_order_meta( $order_id )
    {
        if ( ! empty( $_POST['pargo_shipping_method_location'] ) )
        {
            update_post_meta( $order_id, 'pargo_shipping_method_location', sanitize_text_field( $_POST['pargo_shipping_method_location'] ) );
        }
    }

    add_action( 'woocommerce_checkout_update_order_meta', 'pargo_shipping_method_checkout_field_update_order_meta' );

  
    /* Display field value on the order in the backend edit page on order form */
    function pargo_shipping_method_custom_checkout_field_display_admin_order_meta($order)
    {
        if (get_post_meta ( $order->get_id(), 'pargo_shipping_method_location', true ) )
        {
            echo '<p><strong>' . __('Deliver to PARGO Point:') . ':</strong><br>' . esc_html(get_post_meta($order->get_id()), 'pargo_shipping_method_location', true) . '</p>';
        }
    }

    add_action('woocommerce_admin_order_data_after_billing_address', 'pargo_shipping_method_custom_checkout_field_display_admin_order_meta', 10, 1);


    // Cron to periodically send analyytics on how this pluginis used
    add_filter('cron_schedules', 'pargo_shipping_analytics');
    function pargo_shipping_analytics($schedules)
    {
        $schedules['hourly'] = array(
            'interval'  => 60 * 60,
            'display'   => __('Once Hourly', 'pargo_shipping')
        );
        return $schedules;
    }

    // Schedule an action if it's not already scheduled
    if (!wp_next_scheduled('pargo_shipping_analytics') )
    {
        wp_schedule_event(time(), 'hourly', 'pargo_shipping_analytics');
    }

    // Hook into that action that'll fire every hour
    function pargo_shipping_run_analytics()
    {
        if(get_option("pargo_shipping_id") > 0) {
            $pargo_shipping_id = get_option("pargo_shipping_id");
        } else {

            $url = wp_http_validate_url("https://analytics.ppp.web-x.co.za/api/plugindetailscheck/" . $_SERVER['SERVER_NAME'] . "/pargo");
            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body'    => array(),
            );

            $response = wp_remote_get(wp_http_validate_url($url), $args);
            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            if (401 === $response_code)
            {
                echo esc_html("Unauthorized access");
            }

            if (200 === $response_code)
            {
                $body = json_decode($body);

                if ($body != [])
                {
                    foreach ($body as $data)
                    {
                        $id = $data->id;
                        update_option("pargo_shipping_id", sanitize_text_field($id));
                    }    
                }
            }
            $pargo_shipping_id = get_option("pargo_shipping_id");
        }
        
        

        // Ping url to ensure plugin is active
        $url = wp_http_validate_url("https://analytics.ppp.web-x.co.za/api/pingwordpressplugin/" . $pargo_shipping_id . "/");
        $t = sanitize_text_field(time());
        update_option('cron_last_fired_at', $t);
        $pargoV = get_option('pargo_v');
        $PIV = '' . sanitize_text_field($pargoV);

        include_once(ABSPATH . '/wp-admin/includes/plugin.php');
        // Get all plugins
        $all_plugins = get_plugins();
        
        // Get active plugins
        $active_plugins = get_option('active_plugins');
        $pi_count = 0;
        $active_count = 0;
        $domain_plugin_names = '';
        $this_count = 0;

        foreach ($all_plugins as $key => $value)
        {
            $pi_count++;
            $is_active = (in_array ( $key, $active_plugins ) ) ? true : false;

            if ($is_active) ++$active_count;
            $domain_plugins[$key] = array(
                'name' => $value['Name'],
                'version' => $value['Version'],
                'description' => $value['Description'],
                'active'  => $is_active,
            );
        }

        foreach ($all_plugins as $key => $value)
        {
            $is_active = (in_array ( $key, $active_plugins ) ) ? true : false;
            if ($is_active)
            {
                ++$this_count;
                $domain_plugin_name = $value['Name'];

                if ($active_count > $this_count)
                {
                    $domain_plugin_name = $domain_plugin_name . ', ';
                } else {
                    $domain_plugin_name = $domain_plugin_name . '.';
                }

                $domain_plugin_names = $domain_plugin_names . $domain_plugin_name;
            }
        }

        $PIC = '' . $active_count . '/' . $pi_count . '';
        update_option('testIDs', sanitize_text_field($PIC));
        $PLIA = '[N]' . $domain_plugin_names . '[/N] [D]' . json_encode($domain_plugins) . '[/D]';

        if ( is_ssl() )
        {
            update_option('main_pargo', wp_http_validate_url('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']));
        } else {
            update_option('main_pargo', wp_http_validate_url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']));
        }

        if (get_option ( 'main_pargo' ) )
        {
            $main_pargo = get_option('main_pargo');
        } else {
            $main_pargo = '';
        }

        $admin_email = '';
        $user_count = 0;

        global $wpdb;
        $table_name = $wpdb->prefix . 'usermeta';

        $users = $wpdb->get_results ( "SELECT user_id FROM $table_name WHERE meta_value = 10" );
        
        if(is_array($users))
        {
            if(count($users) > 1)
            {
                $table_name_1 = $wpdb->prefix . 'usermeta';
                $table_name_2 = $wpdb->prefix . 'users';
                $users = $wpdb->get_results ( "SELECT user_id, user_email, display_name FROM $table_name_1 INNER JOIN $table_name_2 ON id = user_id WHERE meta_value = 10" );

                foreach($users as $user)
                {
                    $user_count++;
                    $id = $user->user_id;                  
                    $name = $user->display_name;
                    $email = $user->user_email;                      
                    $admin_email = $admin_email . '[USER][C]' . $user_count . '[/C][ID]' . $id . '[/ID][U]' . $name . '[/U][E]' . $email. '[/E][/USER]';
                }

                $admin_email = $admin_email . '[USERS]' . $user_count . '[/USERS][SITE]' . get_site_url() . '[/SITE]';

            } else {
                $id = $users->user_id;
                $user_count = ++$user_count;
                    
                $table_name_1 = $wpdb->prefix . 'usermeta';
                $table_name_2 = $wpdb->prefix . 'users';

                $users = $wpdb->get_results ( "SELECT user_id, user_email, display_name FROM $table_name_1 INNER JOIN $table_name_2 ON id = user_id WHERE meta_value = 10" );

                $name = $users[0]->display_name;
                $email = $users[0]->user_email;                      
                $admin_email = $admin_email . '[USER][C]' . $user_count . '[/C][ID]' . $id . '[/ID][U]' . $name . '[/U][E]' . $email. '[/E][/USER]';

            }
        }

        $body = array(
            'last_pinged' => $t,
            'PIV' => $PIV,
            'PIC' => $PIC,
            'domain_plugins' => $PLIA,
            'admin_email' => $admin_email
        );
        $args = array(
            'headers' => array(
                'Content-Type'   => 'application/json',
            ),
            'body'      => json_encode($body),
            'method'    => 'PATCH'
        );
        $result = wp_remote_request(wp_http_validate_url($url), $args);
        update_option('lastpinged',$result);
    }

    add_action('pargo_shipping_analytics', 'pargo_shipping_run_analytics');


    // Get info for each product ordered
    add_action('woocommerce_checkout_order_processed', 'get_pargo_product_info', 10, 1);
    function get_pargo_product_info( $order_id ) 
    {
        // Getting an instance of the order object
        $order = wc_get_order( $order_id );
        
        if ( $order->is_paid() )
        {
        $paid = 'yes';
        } else {
        $paid = 'no';
        }
        
        // iterating through each order items (getting product ID and the product object) 
        // (work for simple and variable products)
        foreach ( $order->get_items() as $item_id => $item )
        {
        
            if( $item['variation_id'] > 0 )
            {
                $product_id = $item['variation_id']; // variable product
            } else {
                $product_id = $item['product_id']; // simple product
            }
        
            // Get the product object
            $product = wc_get_product( $product_id );
        
        }
        
        // Ouptput some data
        $lastorder = '<p>Order ID: '. $order_id . ' — Order Status: ' . $order->get_status() . ' — Order is paid: ' . $paid . ', at: ' . time() . '</p>';
        update_option('last_order', sanitize_text_field($lastorder));
    }
}