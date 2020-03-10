<?php
/*
	Plugin Name: CSS sprite generator for Woocommerce Brands
	Plugin URI: 
	Description: automatic CSS sprite generator for Woocommerce Brands
	Version: 1.1
	Author: Robbert Langezaal (original by: Creatomatic)
	Author URI: http://www.opicon.eu/
	License: Copyright 2020  Opicon Bv GPLv2
*/

if( !defined("ABSPATH") )
	return false;

if(defined("CSPRITE_VERSION"))
	return false;

define("CSPRITE_VERSION", "pro_1.1");

add_shortcode( 'csprite-brands', 'csprite_brands' );
function csprite_brands()
{
	$html = '';

        $classes = get_option('csprite_classes');
        $columns = 5;
        $count = 0;
        $html .= '<ul class="brand-thumbnails columns-'.$columns.'">';
        foreach ($classes as $classarr)
        {

		$class = $classarr['class'];

                if ($count % $columns == 0)
                        $prefix = 'first';
                else
                        $prefix = '';

		$link = esc_url( get_term_link( $classarr['slug'], 'product_brand' ) );
		$title = esc_attr( $classarr['name'] );

                $html .= '<li class="'.$prefix.'">';
		$html .= '<div class="csprite-wrap">';
                $html .= '<a href="'.$link.'" title="'.$title.'">';
                $html .= '<div class="csprite csprite-scale '.$class.'">&nbsp;</div>';
		$html .= '</a>';
                $html .= '</div>';
                $html .= '</li>';
                $count++;
        }
        $html .= '</ul>';

	return $html;

}

add_action( 'admin_enqueue_scripts', 'csprite_style' );
add_action( 'wp_enqueue_scripts', 'csprite_style' );
function csprite_style()
{
        $csprite_generated = get_option("csprite_generated");

        //$upload_dir = wp_get_upload_dir();
        $upload_dir = get_site_url();
        //$csprite_image_file_url = trailingslashit( $upload_dir['baseurl'] ) . 'wp-content/uploads/csprite/';
        $csprite_image_file_url = trailingslashit( $upload_dir ) . 'wp-content/uploads/csprite/';

        wp_enqueue_style ("csprite", $csprite_image_file_url . "csprite.css", array(), $csprite_generated);
}

include dirname(__FILE__) . "/csprite.php";
include dirname(__FILE__) . "/settings.php";
?>
