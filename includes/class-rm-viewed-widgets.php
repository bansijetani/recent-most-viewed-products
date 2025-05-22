<?php
/**
 * Recent - Most Viewed Products - Recent and Most Viewed Widget
 *
 * @version 1.0.0
 * @package RecentMostViewedProducts\classes
 */

if ( ! class_exists( 'RM_Viewed_Widgets' ) ) :

    /**
     * RM_Viewed_Widgets class.
     */
    class RM_Viewed_Widgets extends WC_Widget {
        
        /**
         * RM_Viewed_Widgets Constructor
         */
        public function __construct() {
            /**
             * Register widget with WordPress.
             */
            $this->widget_cssclass    = 'woocommerce widget_recently_and_most_viewed_products';
            $this->widget_description = __( "Display a list of a customer's recently and most viewed products.", 'recent-most-viewed-products' );
            $this->widget_id          = 'rm_viewed_recently_and_most_viewed_products';
            $this->widget_name        = __( 'Recently - Most Viewed Products', 'recent-most-viewed-products' );
            $this->settings           = array(
                'title'  => array(
                    'type'  => 'text',
                    'std'   => __( 'Recently - Most Viewed Products', 'recent-most-viewed-products' ),
                    'label' => __( 'Title', 'recent-most-viewed-products' ),
                ),
                'number' => array(
                    'type'  => 'number',
                    'step'  => 1,
                    'min'   => 1,
                    'max'   => 15,
                    'std'   => 10,
                    'label' => __( 'Number of products to show', 'recent-most-viewed-products' ),
                ),
                'type'        => array(
                    'type'    => 'select',
                    'std'     => 'recent_viewed',
                    'label'   => __( 'Type', 'recent-most-viewed-products' ),
                    'options' => array(
                        'recent_viewed' => __( 'Recently viewed products', 'recent-most-viewed-products' ),
                        'most_viewed'   => __( 'Most viewed products', 'recent-most-viewed-products' ),
                    ),
                ),
            );

            parent::__construct();
        }

        /**
         * Query the products and return them
         *
         * @param array $args     Arguments.
         * @param array $instance Widget instance.
         *
         * @return WP_Query
         */
        public function rm_viewed_get_products( $args, $instance ) {

            $title              = ! empty( $instance['title'] ) ? absint( $instance['title'] ) : $this->settings['title']['std'];
            $number             = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : $this->settings['number']['std'];
            $type               = ! empty( $instance['type'] ) ? sanitize_title( $instance['type'] ) : $this->settings['type']['std'];

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

            $posts_per_page  = ( empty( $number ) ) ? 1 : $number ;  
            
            $args = array(
                'posts_per_page' => $posts_per_page,
                'post_status'    => array( 'publish', 'private' ),
                'post_type'      => 'product',
                'post__in'       => $product_ids,
                'orderby'        => 'post__in',                                       
            );

            return new WP_Query( apply_filters( 'rm_viewed_products_widget_query_args', $args ) );
        }


        /**
         * Output widget
         *
         * @param array $args     Arguments
         * @param array $instance Widget instance
         */
        public function widget( $args, $instance ) {

            $products = $this->rm_viewed_get_products( $args, $instance );

            if ( empty( $products ) ) {
                return;
            }
            
            ob_start();

            $this->widget_start( $args, $instance );

            echo wp_kses_post( apply_filters( 'rm_viewed_before_widget_product_list', '<ul class="product_list_widget">' ) );

            $template_args = array(
                'widget_id' => isset( $args['widget_id'] ) ? $args['widget_id'] : $this->widget_id,
            );

            while ( $products->have_posts() ) {
                $products->the_post();
                wc_get_template( 'content-widget-product.php', $template_args );    
            }

            echo wp_kses_post( apply_filters( 'rm_viewed_after_widget_product_list', '</ul>' ) );

            $this->widget_end( $args );

            $content = ob_get_clean();
            wp_reset_postdata();
            echo $content;
        }       
        
    }

endif;

return new RM_Viewed_Widgets();