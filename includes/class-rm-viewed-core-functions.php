<?php
/**
 * Recent - Most Viewed Products - Core Functions
 *
 * @version 1.0.0
 * @package RecentMostViewedProducts\classes
 */

if ( ! class_exists( 'RM_Viewed_Core_Functions' ) ) :

    /**
     * RM_Viewed_Core_Functions class.
     */
    class RM_Viewed_Core_Functions {
        
        /**
         * RM_Viewed_Core_Functions Constructor
         */
        public function __construct() {
            $this->rm_viewed_setup_actions();
        }
        
        /**
         * Setting up Hooks
         */
        public function rm_viewed_setup_actions() {
            //Main plugin hooks
            register_activation_hook( RM_VIEWED_PLUGIN_DIR_URL, array( $this, 'rm_viewed_activate' ) );
            register_deactivation_hook( RM_VIEWED_PLUGIN_DIR_URL, array( $this, 'rm_viewed_deactivate' ) );
            

            // Register recent and most view widgets
            add_action( 'widgets_init', array( $this, 'rm_viewed_register_widgets' ) );

            // Register ajax callbacks
            add_action( 'wp_ajax_rm_viewed_recent_product_call', array( $this,'rm_viewed_update_recent_product_call' ) );
            add_action( 'wp_ajax_nopriv_rm_viewed_recent_product_call', array( $this,'rm_viewed_update_recent_product_call' ) );

            add_action( 'wp_ajax_rm_viewed_most_product_call', array( $this, 'rm_viewed_update_most_product_call' ) );
            add_action( 'wp_ajax_nopriv_rm_viewed_most_product_call', array( $this, 'rm_viewed_update_most_product_call' ) );
        }
        
        /**
         * Activate callback
         */
        public function rm_viewed_activate() {
            if ( ! $this->rm_viewed_is_plugin_activate( 'woocommerce/woocommerce.php' ) ) {
            	$this->rm_viewed_deactivate();
                wp_die( "Please Install and Activate WooCommerce plugin, without that this plugin can't work" );
            }
        }
        
        /**
         * Deactivate callback
         */
        public function rm_viewed_deactivate() {
            require_once ABSPATH . 'wp-admin/includes/plugin.php' ;
            deactivate_plugins( plugin_basename( __FILE__ ) );
            if ( isset( $_GET['activate'] ) ) {
                unset( $_GET['activate'] );
            }
        }

        /**
         * Check for plugin is activate or not
         *
         * @param string $plugin plugin file name
         * @return boolean 
         */
        public function rm_viewed_is_plugin_activate( $plugin ){
        	return ( in_array( $plugin, apply_filters( 'rm_viewed_active_plugins', get_option( 'active_plugins' ) ) ) );
        }

        /**
         * Register widget
         */
        public function rm_viewed_register_widgets() {

            if ( apply_filters( 'rm_viewed_enable_recent_and_most_view_products', true ) ) {
                include_once RM_VIEWED_PLUGIN_DIR_PATH . '/includes/class-rm-viewed-widgets.php';
                include_once RM_VIEWED_PLUGIN_DIR_PATH . '/includes/class-rm-viewed-frontend.php';
                register_widget( 'RM_Viewed_Widgets' );
            }

        }

        /**
         * Update recent viewed products info
         *
         */
        public function rm_viewed_update_recent_product_call() {      

            $product_id         = $_POST['product_id'];            
            $user_id            = get_current_user_id();
            $userdata           = '';
            $userdata           = ( ! empty( $user_id ) && $user_id != 0 ) ? $user_id : $this->rm_viewed_get_client_ip() ;
            $data               = array(
                                    'userdata'    => $userdata,
                                    'product_ids' => array(),
                                );
            $current_post_meta  = get_transient( 'rm_viewed_recent_view_'.$userdata );
            if ( ! empty( $current_post_meta ) && $current_post_meta['userdata'] == $userdata ) {
                if ( ( $key = array_search( $product_id, $current_post_meta['product_ids'] ) ) !== false ) {
                    unset( $current_post_meta['product_ids'][ $key ] );
                }
                $data['product_ids'] = array_merge( array( $product_id ), $current_post_meta['product_ids'] );
                set_transient( 'rm_viewed_recent_view_'.$userdata, $data );                
            } else {
                $data['product_ids'][] = $product_id;     
                set_transient( 'rm_viewed_recent_view_'.$userdata, $data );
            }   

            return;        

        }

        /**
         * Update most viewed products info
         * 
         */
        function rm_viewed_update_most_product_call() {
            
            $product_id        = $_POST['product_id'];         
            $data              = array( );
            $current_post_meta = get_transient( 'rm_viewed_most_view_products' );
            
            if ( ! empty( $current_post_meta ) ) {
                $flag = false;
                foreach ( $current_post_meta as $post_data ) {                    
                    if( $post_data['product_id'] == $product_id ){                            
                        $count  = $post_data['count'] + 1;
                        $data[] = array( 'product_id' => $product_id, 'count' => $count );
                        $flag   = true;
                    } else {
                        $data[] = array( 'product_id' => $post_data['product_id'], 'count' => $post_data['count'] );
                    }  
                    set_transient( 'rm_viewed_most_view_products', $data );                     
                }
                if( ! $flag ) {
                    $data = array_merge( array( array( 'product_id' => $product_id, 'count' => 1 ) ), $current_post_meta );
                    set_transient( 'rm_viewed_most_view_products', $data );  
                }                  
            } else {
                $data[] = array( 'product_id' => $product_id, 'count' => 1 );            
                set_transient( 'rm_viewed_most_view_products', $data );     
            }            
               
            return;
        }

        /**
         * Get client's IP address
         */
        public function rm_viewed_get_client_ip() {
            $ipaddress = '';
            if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) )
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            elseif( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            elseif( isset( $_SERVER['HTTP_X_FORWARDED'] ) )
                $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
            elseif( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) )
                $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
            elseif( isset( $_SERVER['HTTP_FORWARDED'] ) )
                $ipaddress = $_SERVER['HTTP_FORWARDED'];
            elseif( isset( $_SERVER['REMOTE_ADDR'] ) )
                $ipaddress = $_SERVER['REMOTE_ADDR'];
            else
                $ipaddress = '';
            return $ipaddress;
        }
    
    }

endif;

return new RM_Viewed_Core_Functions();