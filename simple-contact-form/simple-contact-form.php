<?php

/** 
 * Plugin Name: Simple Contact Form
 * Description: It's a simple contact form by Usman
 * Author: Muhammed Usman Qureshi 
 * Author URI: https://thepresence.in 
 * Version: 1.0.0
 * Text Domain: Simple Contact Form
 */

// Ensuring Security Measures.
if (!defined('ABSPATH')) {
    exit;
}

// Class Definition
class SimpleContactForm
{

    // Consruct Method Of Class  (SimpleContactForm)
    public function __construct()
    {
        // Create Custom Post Type
        add_action('init', array($this, 'create_custom_post_type'));

        // Add Assets (JS, CSS, ETC.)
        add_action('wp_enqueue_scripts', array($this, 'load_assests'));

        // Add Shortcode
        add_shortcode('contact-form', array($this, 'load_shortcode'));

        // Load Javascript (jQuery)
        add_action('wp_footer', array($this, 'load_scripts'));

        // Adding Specific Hook For registering Rest API
        add_action('rest_api_init', array($this, 'register_rest_api'));
    }

    // Method To Create Custom Post Type.
    public function create_custom_post_type()
    {
        $args = array(
            'public' => true,
            'has_archive' => true,
            // 'supports' => array('title'), // Original
            'supports' => array('title', 'editor', 'thumbnail'), // Usman Edited
            // 'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes', 'post-formats', 'custom-fields'), // Usman Edited for Testing Purpose Only
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability' => 'manage_options',
            'labels' => array(
                'name' => 'Contact Form',
                'signature_name' => 'Contact Form Entry',
            ),
            'menu_icon' => 'dashicons-media-text',
        );

        register_post_type('simple_contact_form', $args);
    }

    // Method To Load Assests (CSS, JS)
    public function load_assests()
    {
        // CSS
        wp_enqueue_style(
            'simple-contact-form',
            plugin_dir_url(__FILE__) . 'css/simple-contact-form.css',
            array(), // For Bootstrap
            1,
            'all'
        );
        // JS
        wp_enqueue_script(
            'simple-contact-form',
            plugin_dir_url(__FILE__) . 'js/simple-contact-form.js',
            array('jquery'),
            1,
            true
        );
    }

    // Method To Load Shortcode.
    public function load_shortcode()
    { ?>

        <!-- Bootstrap Starts -->
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <!-- Required meta tags -->
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

            <title>My Shortcode</title>
        </head>

        <body>

            <!-- HTML PART FOR DISPLAYING THE CONTACT FORM STARTS -->
            <div class="container">
                <div class="card mt-5 justify-content-center">
                    <div class="card-header">
                        <h2>Send Us An E-mail</h2>
                        <p>Please Fill The Form Given Below</p>
                    </div>

                    <div class="card-body">
                        <div class="simple-contact-form">
                            <form id="simple-contact-form__form">

                                <div class="form-group mb-3">
                                    <input type="text" name="name" placeholder="Enter Your Name Here" class="w-100 mb-3">
                                </div>
                                <div class="form-group" >
                                    <input type="email" name="email" placeholder="Enter Your Email Here" class="w-100 mb-3">
                                </div>
                                <div class="form-group mb-3 w-100">
                                    <input type="tel" name="phone" placeholder="Enter Your Phone No. Here" class="w-100">
                                </div>
                                <div class="form-group mb-3 w-100">
                                    <textarea name="message" placeholder="Enter Your Message Here" class="w-100"></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <button type="submit" class="btn btn-success btn-block w-100">Send Message</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
           
            <!-- Script Starts -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
            <!-- Script Ends -->

        </body>

        </html>

    <?php
    }

    // Method To Load Javascript (jQuery)
    public function load_scripts()
    { ?>
        <script>
           var nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';

            (function($) {
                $('#simple-contact-form__form').submit(function(event) {
                    event.preventDefault();

                    var form = $(this).serialize();
                    // console.log(form);

                    $.ajax({
                        method: 'post',
                        url: '<?php echo get_rest_url(null, 'simple-contact-form/v1/send-email'); ?>',
                        headers: {
                            'X-WP-Nonce': nonce
                        },
                        data: form
                    })
                });
            })(jQuery)
        </script>
<?php
    }

    // Methos For Registering Rest API
    public function register_rest_api()
    {
        register_rest_route('simple-contact-form/v1', 'send-email', array(

            'methods' => 'POST',
            'callback' => array($this, 'handle_contact_form'),

        ));
    }

    /* Original */
    public function handle_contact_form($data)
    {
        // echo 'This End Point Is Working.';
        $headers = $data->get_headers();
        $params = $data->get_params();

        // echo json_encode($headers);
        // $nonce = $headers['X_WP_Nonce'][0]; // Not Sure
        $nonce = $headers['x_wp_nonce'][0]; // Not Sure

        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            // echo 'This Nonce Is Correct';
            return new WP_REST_Response('Mesage Not Sent', 422);
        }


        $post_id = wp_insert_post([
            'post_type' => 'simple_contact_form',
            'post_title' => 'Contact Enquiry',
            'post_editor' => 'Contact Enquiry', // Usman
            'post_thumbnail' => 'Contact Enquiry', // Usman
            'post_status' => 'publish'
        ]);

        if($post_id){
            return new WP_REST_Response('Thank You For Your Email', 200);
        }
    }
    /* Original */


}

// Instantiating The Class.
new SimpleContactForm();
