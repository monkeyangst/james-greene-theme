<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function understrap_remove_scripts() {
    wp_dequeue_style( 'understrap-styles' );
    wp_deregister_style( 'understrap-styles' );

    wp_dequeue_script( 'understrap-scripts' );
    wp_deregister_script( 'understrap-scripts' );

    // Removes the parent themes stylesheet and scripts from inc/enqueue.php
}
add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {

	// Get the theme data
	$the_theme = wp_get_theme();
    wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . '/css/child-theme.min.css', array(), $the_theme->get( 'Version' ) );
    wp_enqueue_script( 'jquery');
    wp_enqueue_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . '/js/child-theme.min.js', array(), $the_theme->get( 'Version' ), true );
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}

function add_child_theme_textdomain() {
    load_child_theme_textdomain( 'understrap-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'add_child_theme_textdomain' );


/*
The following are my BH's additions.
*/

function jg_enqueue_fonts() {
    wp_enqueue_style( 'jamesgreene-google-fonts', 'https://fonts.googleapis.com/css?family=Raleway:300,400,500,700&display=swap');

}
add_action( 'wp_enqueue_scripts', 'jg_enqueue_fonts' );


function jg_gallery_shortcode() {
    ?>
    <ul class="products">
        <?php
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => 12
                );
            $posts = get_posts($args);
            foreach ($posts as $post) {
                echo '<h2>' . $post->post_title . 'Flooble</h2>';
                $meta = get_post_meta($post->ID, '_thumbnail_id', $single=TRUE);
                $flooble = wp_get_attachment_metadata($meta);
                echo '<pre>';
                var_dump($flooble);
                echo '</pre>';
            }
        ?>
    </ul><!--/.products-->
    <?php
    
    }
    
    //add_shortcode('jg-test', 'jg_gallery_shortcode');
    
    
    
    //add_action('jp_carousel_enqueue_assets', 'flooble_test');
    
    add_action( 'woocommerce_product_options_inventory_product_data', 'jg_product_option_group' );
    
     
    function jg_product_option_group() { ?>
    <div class="options_group">
        <?php
            woocommerce_wp_text_input( 
                array(
                    'id'        =>  'jg_product_date',
                    'value'     =>  get_post_meta( get_the_ID(), 'jg_product_date', true),
                    'label'     =>  'Completion Year',
                    'desc_tip'  => true,
                    'description'   => 'The year this work was completed'
                )
            );
        ?>
    </div>
    
    <?php 
    }
    
    add_action( 'woocommerce_process_product_meta', 'jg_product_save_fields', 10, 2 );
    function jg_product_save_fields( $id, $post ){
     
        //if( !empty( $_POST['super_product'] ) ) {
            update_post_meta( $id, 'jg_product_date', $_POST['jg_product_date'] );
        //} else {
        //	delete_post_meta( $id, 'super_product' );
        //}
     
    }
    
    // See if I can mess with the WooCommerce notices.
    function mess_with_woocommerce_notice($message) {
        $message .= '<h1>Flooble</h1>';
        return $message;
    }
    //add_filter('wc_add_to_cart_message_html', 'mess_with_woocommerce_notice');
    
    
    // Replace default WooCommerce empty-cart message
    function jg_cart_is_empty() {
        echo '<p class="cart-empty jg-woocommerce-info"><i class="fa fa-shopping-cart"></i> ' . wp_kses_post( apply_filters( 'wc_empty_cart_message', __( 'Your cart is currently empty.', 'woocommerce' ) ) ) . '</p>';}
    
    add_action('woocommerce_cart_is_empty', 'jg_cart_is_empty');
    remove_action('woocommerce_cart_is_empty', 'wc_empty_cart_message');
    
    function jp_custom_exif_info( $js ) {
        // Populate metadata in images with WooCommerce links
        // Kludgy, using EXIF data for purposes other than intended
    
        //echo '<pre>'; var_dump($js); echo '</pre>';
        $args = array(
            'post_type' => 'attachment',
            'posts_per_page' => 12
            );
        $posts = get_posts($args);
        foreach ($posts as $post) {
            $meta = get_post_meta($post->ID);
            // echo '<pre>';
            // echo $post->post_title.'<hr>';
            // var_dump($meta);
            // echo '</pre>';
            $piece = $post->post_title;
            $medium = $meta['_medium_text'][0];
            if (empty($medium)) $medium = "";
            $size = $meta['_size_text'][0];
            if (empty($size)) $size = "";
            $year = $meta['_year_text'][0];
            if (empty($year)) $year = "";
    
            $attach_meta = wp_get_attachment_metadata($post->ID);
            //echo '<pre>'; var_dump($attach_meta); echo '</pre>';
    
            $attach_meta['image_meta']['piece'] = htmlentities($piece);
            $attach_meta['image_meta']['medium'] = htmlentities($medium);
            $attach_meta['image_meta']['year'] = htmlentities($year);
            $attach_meta['image_meta']['size'] = htmlentities($size);
            $attach_meta['image_meta']['floo'] = '';
    
            wp_update_attachment_metadata($post->ID, $attach_meta);
    
        }
    
    
        // Overwrite with a new list
        $js['meta_data'] = array( 'year', 'size', 'medium' );
    
        // Add translation for tag
        $js['piece'] = __( 'Piece', 'jetpack' );
        $js['year'] = __( 'Year', 'jetpack' );
        $js['size'] = __( 'Size', 'jetpack' );
        $js['medium'] = __( 'Medium', 'jetpack' );
    
    
        return $js;
    } 
    add_filter( 'jp_carousel_localize_strings', 'jp_custom_exif_info');
    
    function tweakjp_rm_comments_att( $open, $post_id ) {
        $post = get_post( $post_id );
        if( $post->post_type == 'attachment' ) {
            return false;
        }
        return $open;
    }
    add_filter( 'comments_open', 'tweakjp_rm_comments_att', 10 , 2 );
    
    /**
    * Disable out of stock variations
    * https://github.com/woocommerce/woocommerce/blob/826af31e1e3b6e8e5fc3c1004cc517c5c5ec25b1/includes/class-wc-product-variation.php
    * @return Boolean
    */
    function wcbv_variation_is_active( $active, $variation ) {
        if( ! $variation->is_in_stock() ) {
            return false;
        }
        return $active;
    }
    add_filter( 'woocommerce_variation_is_active', 'wcbv_variation_is_active', 10, 2 );
    
    
    
    function flooble_test($data) {
        echo '<h1>Flooble</h1>';
    }
    
    
    
    /**
    * Add custom media metadata fields
    *
    * Be sure to sanitize your data before saving it
    * http://codex.wordpress.org/Data_Validation
    *
    * @param $form_fields An array of fields included in the attachment form
    * @param $post The attachment record in the database
    * @return $form_fields The final array of form fields to use
    */
    function add_image_attachment_fields_to_edit( $form_fields, $post ) {
        
        // Remove the "Description" field, we're not using it
        unset( $form_fields['post_content'] ); 
        
        // Add description text (helps) to the "Title" field
        $form_fields['post_title']['helps'] = 'Use a descriptive title for the image. This will make it easy to find the image in the future and will improve SEO.';
            
        // Re-order the "Caption" field by removing it and re-adding it later
        $form_fields['post_excerpt']['helps'] = 'Describe the significants of the image pertaining to the site.';
        $caption_field = $form_fields['post_excerpt'];
        unset($form_fields['post_excerpt']);
        
        // Re-order the "File URL" field
        $image_url_field = $form_fields['image_url'];
        unset($form_fields['image_url']);
        
        // Add Caption before Credit field 
        $form_fields['post_excerpt'] = $caption_field;
        
        // Add a Year field
        $form_fields["year_text"] = array(
            "label" => __("Year"),
            "input" => "text", // this is default if "input" is omitted
            "value" => esc_attr( get_post_meta($post->ID, "_year_text", true) ),
            "helps" => __("The year in which this piece was completed."),
        );
        
        // Add a Medium field
        $form_fields["medium_text"] = array(
            "label" => __("Medium"),
            "input" => "text", // this is default if "input" is omitted
            "value" => esc_attr( get_post_meta($post->ID, "_medium_text", true) ),
            "helps" => __("The medium in which this piece was originally created."),
        );
        
        // Add a Size field
        $form_fields["size_text"] = array(
            "label" => __("Size"),
            "input" => "text", // this is default if "input" is omitted
            "value" => esc_attr( get_post_meta($post->ID, "_size_text", true) ),
            "helps" => __("The size of the original piece."),
        );
        
        // Add Caption before Size field 
        $form_fields['image_url'] = $image_url_field;
        
        return $form_fields;
    }
    add_filter("attachment_fields_to_edit", "add_image_attachment_fields_to_edit", null, 2);
    
    /**
    * Save custom media metadata fields
    *
    * Be sure to validate your data before saving it
    * http://codex.wordpress.org/Data_Validation
    *
    * @param $post The $post data for the attachment
    * @param $attachment The $attachment part of the form $_POST ($_POST[attachments][postID])
    * @return $post
    */
    function add_image_attachment_fields_to_save( $post, $attachment ) {
        if ( isset( $attachment['medium_text'] ) )
            update_post_meta( $post['ID'], '_medium_text', esc_attr($attachment['medium_text']) );
            
        if ( isset( $attachment['size_text'] ) )
            update_post_meta( $post['ID'], '_size_text', esc_attr($attachment['size_text']) );
    
        if ( isset( $attachment['year_text'] ) )
            update_post_meta( $post['ID'], '_year_text', esc_attr($attachment['year_text']) );
    
        return $post;
    }
    add_filter("attachment_fields_to_save", "add_image_attachment_fields_to_save", null , 2);