<?php
namespace Elementor;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Kivi_Portfolio_Widget extends Widget_Base {
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