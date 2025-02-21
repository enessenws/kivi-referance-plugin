<?php
/**
 * Plugin Name: Kivi Portfolio References
 * Description: Custom Portfolio References Plugin for WordPress with Elementor support, lazy loading, and filtering
 * Version: 1.3.1
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
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
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
}
add_action('init', 'kivi_register_portfolio_taxonomies');

// Elementor Widget for Portfolio
function kivi_register_elementor_widgets($widgets_manager) {
    require_once plugin_dir_path(__FILE__) . 'widgets/class-kivi-portfolio-widget.php';
    if (class_exists('Kivi_Portfolio_Widget')) {
        error_log('Kivi_Portfolio_Widget class found.');
        $widgets_manager->register(new Kivi_Portfolio_Widget());
    } else {
        error_log('Kivi_Portfolio_Widget class not found.');
    }
}
add_action('elementor/widgets/register', 'kivi_register_elementor_widgets');

// Check Elementor before registering widget
if (class_exists('\Elementor\Widget_Base')) {
    class Kivi_Portfolio_Widget extends \Elementor\Widget_Base {
        public function get_name() {
            return 'kivi_portfolio_widget';
        }

        public function get_title() {
            return __('Kivi Portfolio', 'kivi');
        }

        public function get_icon() {
            return 'eicon-gallery-grid';
        }

        public function get_categories() {
            return ['general'];
        }

        protected function render() {
            // Check if the shortcode exists before rendering
            if (shortcode_exists('kivi_portfolio')) {
                echo do_shortcode('[kivi_portfolio]');
            } else {
                echo '<p>' . __('Portfolio shortcode not found.', 'kivi') . '</p>';
            }
        }
    }
}

// Shortcode for Portfolio Grid with Lazy Load and Filtering
function kivi_portfolio_shortcode($atts) {
    ob_start();
    $atts = shortcode_atts(array(
        'design' => 'option1', // Default design option
    ), $atts);
    ?>
    <div id="kivi-portfolio-filter">
        <button class="filter-btn" data-filter="all">All</button>
        <?php
        $categories = get_terms('kivi_portfolio_category');
        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $category) {
                echo '<button class="filter-btn" data-filter="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</button>';
            }
        }
        ?>
    </div>
    <div id="kivi-portfolio-grid" class="kivi-portfolio-grid <?php echo esc_attr($atts['design']); ?>"></div>
    <button id="load-more">Load More</button>
    <script>
        let page = 1;
        function loadPortfolioItems(filter = 'all') {
            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>?action=kivi_load_more&page=' + page + '&filter=' + filter)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('kivi-portfolio-grid').innerHTML += data;
                    page++;
                });
        }
        document.getElementById('load-more').addEventListener('click', function() {
            const filter = document.querySelector('.filter-btn.active') ? document.querySelector('.filter-btn.active').dataset.filter : 'all';
            loadPortfolioItems(filter);
        });
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                page = 1; // Reset page
                document.getElementById('kivi-portfolio-grid').innerHTML = ''; // Clear grid
                loadPortfolioItems(this.dataset.filter);
            });
        });
        loadPortfolioItems();
    </script>
    <style>
        .kivi-portfolio-grid.option1 .portfolio-item {
            width: 48%;
            display: inline-block;
            margin: 1%;
        }
        .kivi-portfolio-grid.option1 .portfolio-item.large {
            width: 100%;
        }
        .kivi-portfolio-grid.option2 .portfolio-item {
            width: 30%;
            display: inline-block;
            margin: 1.5%;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('kivi_portfolio', 'kivi_portfolio_shortcode');

// AJAX Load More Function
function kivi_ajax_load_more() {
    $paged = isset($_GET['page']) ? intval($_GET['page']) + 1 : 1;
    $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';
    $args = array('post_type' => 'kivi_portfolio', 'paged' => $paged);
    if ($filter !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'kivi_portfolio_category',
                'field' => 'slug',
                'terms' => $filter,
            ),
        );
    }
    $query = new WP_Query($args);
    if (is_wp_error($query)) {
        echo '<p>' . __('Error in query.', 'kivi') . '</p>';
        wp_die();
    }
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            ?>
            <div class="portfolio-item <?php echo ($paged == 1 && $query->current_post == 0) ? 'large' : ''; ?>">
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail('medium'); ?>
                    <h3><?php the_title(); ?></h3>
                </a>
            </div>
            <?php
        endwhile;
    } else {
        echo '<p>No more portfolio items to load.</p>';
    }
    wp_die();
}
add_action('wp_ajax_kivi_load_more', 'kivi_ajax_load_more');
add_action('wp_ajax_nopriv_kivi_load_more', 'kivi_ajax_load_more');
