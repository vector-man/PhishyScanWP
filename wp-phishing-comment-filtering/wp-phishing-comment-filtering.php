<?php
/*
Plugin Name: WP Phishing Comment Filtering
Description: Filter phishing/scam comments using Yotanest API https://rapidapi.com/yottanext-yottanext-default/api/phishyscan-api.
Version:     1.0.0
Author:      Torricelli
Author URI:  https://www.fiverr.com/torricelli
Text Domain: wp-phishing-comment-filtering
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die;

define( 'WP_PHISHING_COMMENT_FILTERING_FILE', __FILE__ );
define( 'WP_PHISHING_COMMENT_FILTERING_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_PHISHING_COMMENT_FILTERING_OPTSGROUP_NAME', 'wp_phishing_comment_filtering_optsgroup' );
define( 'WP_PHISHING_COMMENT_FILTERING_OPTIONS_NAME', 'wp_phishing_comment_filtering_options' );
define( 'WP_PHISHING_COMMENT_FILTERING_SCAN_URL', 'https://phishyscan-api.p.rapidapi.com/scan' );
define( 'WP_PHISHING_COMMENT_FILTERING_BUY_URL', 'https://rapidapi.com/yottanext-yottanext-default/api/phishyscan-api/pricing' );
define( 'WP_PHISHING_COMMENT_FILTERING_VER', '1.0.0' );

if ( ! class_exists( 'WP_Phising_Comment_Filtering' ) ) {
	class WP_Phising_Comment_Filtering {
		public static function get_instance() {
			if ( self::$instance == null ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private $options = null;

		private static $instance = null;

		private function __clone() { }

		public function __wakeup() { }

		private function __construct() {
			// Actions
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'wp_insert_comment', array( $this, 'insert_comment' ), 1000, 2 );
			add_action( 'edit_comment', array( $this, 'edit_comment' ), 1000, 2 );
		}

		public function init() {
			load_plugin_textdomain( 'wp-phishing-comment-filtering', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		public function register_settings() {
			register_setting( WP_PHISHING_COMMENT_FILTERING_OPTSGROUP_NAME, WP_PHISHING_COMMENT_FILTERING_OPTIONS_NAME );
		}

		public function admin_menu() {
			add_menu_page(
				__( 'Phishing CF', 'wp-phishing-comment-filtering' ),
				__( 'Phishing CF', 'wp-phishing-comment-filtering' ),
				'manage_options',
				'wp-phishing-comment-filtering',
				array( $this, 'render_options_page' ),
				'dashicons-filter'
			);
		}

		public function render_options_page() {
			require WP_PHISHING_COMMENT_FILTERING_PLUGIN_PATH . 'options.php';
		}

		public function insert_comment( $comment_id, $comment ) {
			$this->process_comment( $comment_id, $comment );
		}

		public function edit_comment( $comment_id, $comment ) {
			$this->process_comment( $comment_id, $comment );
		}

		private function process_comment( $comment_id, $data ) {
			$data = ( array ) $data;

			$parsed_url = parse_url( WP_PHISHING_COMMENT_FILTERING_SCAN_URL );

			$result = wp_remote_post( WP_PHISHING_COMMENT_FILTERING_SCAN_URL, array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'x-rapidapi-host' => sanitize_text_field( $parsed_url['host'] ),
					'x-rapidapi-key' => sanitize_text_field( $this->get_option( 'api_key' ) )
				),
				'body' => json_encode( array(
					'id' => "comment-$comment_id",
					'address' => $data['comment_author_email'],
					'title' => "Comment $comment_id",
					'message' => $data['comment_content']
				) )
			) );

			if ( is_wp_error( $result ) ) {
				update_comment_meta( $comment_id, 'phishyscan_response', 'Error (1)' );
				return;
			}

			$obj = json_decode( $result['body'] );
			if ( ! is_object( $obj ) || ! property_exists( $obj, 'result' ) ) {
				update_comment_meta( $comment_id, 'phishyscan_response', 'Error (2)' );
				return;
			}

			update_comment_meta( $comment_id, 'phishyscan_response', $obj );

			switch( strtolower( $obj->result ) ) {
				case 'passed' :
					wp_set_comment_status( $comment_id, 'approve' );
					break;
				case 'warn' :
					wp_set_comment_status( $comment_id, 'hold' );
					break;
				default:
					wp_set_comment_status( $comment_id, 'spam' );
			}
		}

		private function get_option( $option_name, $default = '' ) {
			if ( is_null( $this->options ) ) {
				$this->options = ( array ) get_option( WP_PHISHING_COMMENT_FILTERING_OPTIONS_NAME, array() );
			}

			if ( isset( $this->options[$option_name] ) ) {
				return $this->options[$option_name];
			}

			return $default;
		}
	}

	WP_Phising_Comment_Filtering::get_instance();
}