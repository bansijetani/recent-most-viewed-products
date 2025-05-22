<?php
/**
 * Recent - Most Viewed Products - Frontend
 *
 * @version 1.0.0
 * @package RecentMostViewedProducts\classes
 */

if ( ! class_exists( 'RM_Viewed_Frontend' ) ) :

    /**
     * RM_Viewed_Frontend class.
     */
    class RM_Viewed_Frontend {
        
        /**
         * RM_Viewed_Frontend Constructor
         */
        public function __construct() {
        	$this->rm_viewed_setup_actions();
        }

        /**
         * Setting up Hooks
         */
        public function rm_viewed_setup_actions() {
            add_action( 'wp_enqueue_scripts', array( $this, 'rm_viewed_enqueue_scripts' ) );

            // Add shortcode 
            add_shortcode( 'rm_viewed_products', array( $this, 'rm_viewed_show_products' ) );
        }

        /**
         *  Enqueue script for ajax calling
         */
        public function rm_viewed_enqueue_scripts() {

			global $post;			
			
			if ( is_null( $post ) || $post->post_type != 'product' || ! is_product() ) {
				return;
			}

            $p_id = $post->ID;

        	// Localize the script with new data			
			wp_enqueue_script( 'custom_js', plugin_dir_url( __DIR__ ) . '/assets/js/frontend.js', array(), RM_VIEWED_PLUGIN_VER , true );
			$js_array = array(
				'product_id'		=> $p_id,
				'rm_viewed_ajaxurl' => admin_url('admin-ajax.php'),
			);

			wp_add_inline_script( 'custom_js', 'const wp_obj_ajax = ' . json_encode($js_array), 'before' );
        }

        /**
         * Showing products through shortcode
         * 
         * @param array $atts Shortcode attributes
         */
        public function rm_viewed_show_products( $atts ) {   

            $atts = extract( shortcode_atts( array(
                'number_of_products_in_row' => '',
                'type'                      => '', 
                'posts_per_page'            => '',
                'title'                     => '',
                ), 
                $atts 
            ) );

            $rm_core_func       = new RM_Viewed_Core_Functions();
            $user_id            = get_current_user_id();
            $userdata           = ( ! empty( $user_id ) && $user_id != 0 ) ? $user_id : $rm_core_func->rm_viewed_get_client_ip() ;
            $get_transient_data = array();

            if ( $type == 'recent_viewed' ) {
                $get_transient_data = get_transient( 'rm_viewed_recent_view_'.$userdata);
                $product_ids        = ( ! empty( $get_transient_data ) && $get_transient_data['product_ids'] ) ? $get_transient_data['product_ids'] : array();
            }

            if ( $type == 'most_viewed' ) {
                $get_transient_data = get_transient( 'rm_viewed_most_view_products');
                $most_viewed_data   = ( ! empty( $get_transient_data ) ) ? $get_transient_data : array();

                $col_count          = array_column( $most_viewed_data, 'count' );
                array_multisort( $col_count, SORT_DESC, $most_viewed_data );
                $product_ids        = wp_list_pluck( $most_viewed_data, 'product_id' ); 

            }
           
            if ( empty( $get_transient_data ) ) {
                return;
            }

            $posts_per_page  = ( empty( $posts_per_page ) ) ? 1 : $posts_per_page ;  
            
            $args = array(
                'posts_per_page' => $posts_per_page,
                'post_status'    => array( 'publish', 'private' ),
                'post_type'      => 'product',
                'post__in'       => $product_ids,
                'orderby'        => 'post__in',                                       
            );

            $products = new WP_Query( apply_filters( 'rm_viewed_products_shortcode_query_args', $args ) );

            if ( empty( $products ) ) {
                return;
            }
            
            ob_start();
            
            if ( $products->have_posts() ) : ?>
                <div class="woocommerce">   
                    <div class="rm_viewed_products">
                        <?php if ( $title ) { ?>
                        <h2> <?php echo $title; ?> </h2>
                        <?php } ?>
                        <?php woocommerce_product_loop_start(); ?>

                            <?php while ( $products->have_posts() ) : $products->the_post(); ?>
                                    
                                <?php wc_get_template_part( 'content', 'product' ); ?>

                            <?php endwhile; ?>

                        <?php woocommerce_product_loop_end(); ?>
                    </div>
                </div>
            <?php  
            endif;
            $content = ob_get_clean();
            wp_reset_postdata();
            return $content;
        }       

    }

endif;

return new RM_Viewed_Frontend();