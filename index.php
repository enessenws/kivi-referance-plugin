<?php
/**
 * Plugin Name: Kivi Portfolio References
 * Description: Custom Portfolio References Plugin for WordPress
 * Version: 1.0.0
 * Author: Your Name
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type: Portfolio
function kivi_register_portfolio_post_type() {
    $labels = array(
        'name'               => __('Portfolio', 'kivi'),
        'singular_name'      => __('Portfolio Item', 'kivi'),
        'menu_name'          => __('Portfolio', 'kivi'),
        'add_new'            => __('Add New', 'kivi'),
        'add_new_item'       => __('Add New Portfolio Item', 'kivi'),
        'edit_item'          => __('Edit Portfolio Item', 'kivi'),
        'new_item'           => __('New Portfolio Item', 'kivi'),
        'view_item'          => __('View Portfolio Item', 'kivi'),
        'all_items'          => __('All Portfolio Items', 'kivi'),
        'search_items'       => __('Search Portfolio', 'kivi'),
        'not_found'          => __('No Portfolio Items Found', 'kivi'),
        'not_found_in_trash' => __('No Portfolio Items Found in Trash', 'kivi'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => array('title', 'editor', 'thumbnail'),
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'portfolio'),
        'show_in_rest'       => true,
    );
    
    register_post_type('kivi_portfolio', $args);
}
add_action('init', 'kivi_register_portfolio_post_type');

// Register Custom Taxonomies for Portfolio
function kivi_register_portfolio_taxonomies() {
    register_taxonomy(
        'kivi_portfolio_category',
        'kivi_portfolio',
        array(
            'label' => __('Portfolio Categories', 'kivi'),
            'rewrite' => array('slug' => 'portfolio-category'),
            'hierarchical' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
        )
    );
    
    register_taxonomy(
        'kivi_portfolio_skills',
        'kivi_portfolio',
        array(
            'label' => __('Portfolio Skills', 'kivi'),
            'rewrite' => array('slug' => 'portfolio-skills'),
            'hierarchical' => false,
            'show_admin_column' => true,
            'show_in_rest' => true,
        )
    );
}
add_action('init', 'kivi_register_portfolio_taxonomies');

// Add Meta Boxes
function kivi_add_portfolio_meta_boxes() {
    add_meta_box(
        'kivi_portfolio_details',
        __('Portfolio Details', 'kivi'),
        'kivi_portfolio_meta_box_callback',
        'kivi_portfolio',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'kivi_add_portfolio_meta_boxes');

function kivi_portfolio_meta_box_callback($post) {
    wp_nonce_field('kivi_portfolio_save_meta', 'kivi_portfolio_meta_nonce');
    $portfolio_link = get_post_meta($post->ID, '_kivi_portfolio_link', true);
    $client_name = get_post_meta($post->ID, '_kivi_client_name', true);
    $client_url = get_post_meta($post->ID, '_kivi_client_url', true);
    $standort = get_post_meta($post->ID, '_kivi_standort', true);
    ?>
    <p>
        <label for="kivi_portfolio_link">Portfolio Link:</label>
        <input type="text" id="kivi_portfolio_link" name="kivi_portfolio_link" value="<?php echo esc_attr($portfolio_link); ?>" size="50" />
    </p>
    <p>
        <label for="kivi_client_name">Client Name:</label>
        <input type="text" id="kivi_client_name" name="kivi_client_name" value="<?php echo esc_attr($client_name); ?>" size="50" />
    </p>
    <p>
        <label for="kivi_client_url">Client URL:</label>
        <input type="text" id="kivi_client_url" name="kivi_client_url" value="<?php echo esc_attr($client_url); ?>" size="50" />
    </p>
    <p>
        <label for="kivi_standort">Standort:</label>
        <input type="text" id="kivi_standort" name="kivi_standort" value="<?php echo esc_attr($standort); ?>" size="50" />
    </p>
    <?php
}

// Save Meta Box Data
function kivi_save_portfolio_meta($post_id) {
    if (!isset($_POST['kivi_portfolio_meta_nonce']) || !wp_verify_nonce($_POST['kivi_portfolio_meta_nonce'], 'kivi_portfolio_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['kivi_portfolio_link'])) {
        update_post_meta($post_id, '_kivi_portfolio_link', sanitize_text_field($_POST['kivi_portfolio_link']));
    }
    if (isset($_POST['kivi_client_name'])) {
        update_post_meta($post_id, '_kivi_client_name', sanitize_text_field($_POST['kivi_client_name']));
    }
    if (isset($_POST['kivi_client_url'])) {
        update_post_meta($post_id, '_kivi_client_url', esc_url($_POST['kivi_client_url']));
    }
    if (isset($_POST['kivi_standort'])) {
        update_post_meta($post_id, '_kivi_standort', sanitize_text_field($_POST['kivi_standort']));
    }
}
add_action('save_post', 'kivi_save_portfolio_meta');
