<?php
/**
 * Plugin Name: JWT Token Generator
 * Plugin URI: https://aliishaq.site
 * Description: A simple WordPress plugin to generate JWT tokens for users.
 * Version: 1.0
 * Author: Ali Ishaq
 * Author URI: https://aliishaq.site
 */

// Ensure that WordPress has loaded Composer's autoload file
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';  // Correct path to vendor/autoload.php
use Firebase\JWT\JWT;

// Secret key for signing the JWT (ensure you keep it private)
define('JWT_SECRET_KEY', '5e3f5(  YOUR SECRET KEY  )50f');

// Function to generate JWT for WordPress user
function generate_jwt_token_for_wp_user($user) {
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600;  // Token valid for 1 hour
    $issuer = get_site_url(); // Issuer of the token (your site URL)

    // Payload with user information
    $payload = array(
        "iat" => $issuedAt,  // Issued At
        "exp" => $expirationTime,  // Expiration Time
        "iss" => $issuer,  // Issuer
        "data" => array(
            "user_id" => $user->ID,
            "username" => $user->user_login,
            "email" => $user->user_email
        )
    );

    // Encode the token
    // Encode the token with the HS256 algorithm
    $jwt = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');

    return $jwt;
}

// Function to handle JWT token request through REST API
function jwt_token_endpoint() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        $user = wp_get_current_user(); // Get the current logged-in user
        $jwt_token = generate_jwt_token_for_wp_user($user); // Generate the JWT token
        
        // Return the token as JSON response
        wp_send_json_success(array('token' => $jwt_token));
    } else {
        // If the user is not logged in, return an error
        wp_send_json_error(array('message' => 'User is not logged in.'));
    }
}

// Register the custom REST API endpoint
function register_jwt_token_api() {
    register_rest_route('jwt-auth/v1', '/token', array(
        'methods' => 'GET',
        'callback' => 'jwt_token_endpoint',
        'permission_callback' => '__return_true', // No permission callback
    ));
}

// Hook into WordPress to register the API endpoint
add_action('rest_api_init', 'register_jwt_token_api');
