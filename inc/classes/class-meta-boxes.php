<?php
/**
 * Register Meta Boxes
 * This class adds a meta box to hide the page title in posts.
 * @package Aquila
 */

namespace AQUILA_THEME\Inc;

use AQUILA_THEME\Inc\Traits\Singleton;

/**
 * Class Meta_Boxes
 */
class Meta_Boxes {

    use Singleton;

    protected function __construct() {
        // Load class hooks.
        $this->setup_hooks();
    }

    protected function setup_hooks() {
        // Register actions.
        add_action( 'add_meta_boxes', [ $this, 'add_custom_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_post_meta_data' ] );
    }

    /**
     * Add custom meta box.
     *
     * @return void
     */
    public function add_custom_meta_box() {
        $screens = [ 'post' ];

        foreach ( $screens as $screen ) {
            add_meta_box(
                'hide-page-title',         // ID of the meta box.
                __( 'Hide page title', 'aquila' ),  // Title of the meta box.
                [ $this, 'custom_meta_box_html' ], // Callback function to render the meta box.
                $screen,                    // Post type to display the meta box.
                'side'                      // Context where the box will appear.
            );
        }
    }

    /**
     * Meta box HTML content.
     *
     * @param object $post Post object.
     * @return void
     */
    public function custom_meta_box_html( $post ) {
        $value = get_post_meta( $post->ID, '_hide_page_title', true );

        wp_nonce_field( plugin_basename(__FILE__), 'hide_title_meta_box_nonce_name' );
        ?>
        <label for="aquila-field"><?php esc_html_e( 'Hide the page title', 'aquila' ); ?></label>
        <select name="aquila_hide_title_field" id="aquila-field" class="postbox">
            <option value=""><?php esc_html_e( 'Select', 'aquila' ); ?></option>
            <option value="yes" <?php selected( $value, 'yes' ); ?>>
                <?php esc_html_e( 'Yes', 'aquila' ); ?>
            </option>
            <option value="no" <?php selected( $value, 'no' ); ?>>
                <?php esc_html_e( 'No', 'aquila' ); ?>
            </option>
        </select>
        <?php
    }

    /**
     * Save post meta into the database when the post is saved.
     *
     * @param integer $post_id Post ID.
     * @return void
     */
    public function save_post_meta_data( $post_id ) {
        // Check if the user can edit the post.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Verify nonce for security.
        if ( ! isset( $_POST['hide_title_meta_box_nonce_name'] ) || 
             ! wp_verify_nonce( $_POST['hide_title_meta_box_nonce_name'], plugin_basename(__FILE__) ) ) {
            return;
        }

        // Update post meta if the field exists.
        if ( array_key_exists( 'aquila_hide_title_field', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_hide_page_title',
                $_POST['aquila_hide_title_field']
            );
        }
    }
}
