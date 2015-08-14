<?php
/*
	Plugin Name: Mt8 FC2 Import Comments Adjuster
	Plugin URI: https://github.com/mt8/mt8-fc2-import-comments-adjuster
	Description: "Mt8 Import Comments Adjuster" is a plugin to adjust comments of importing export data of the FC2 blog.
	Author: mt8.biz
	Version: 1.0
	Author URI: http://mt8.biz
	Domain Path: /languages
	Text Domain: mt8-fc2-import-comments-adjuster
*/

$mt8_fc2_ica = new Mt8_FC2_Import_Comments_Adjuster();
$mt8_fc2_ica->register_hooks();

class Mt8_FC2_Import_Comments_Adjuster {
	
	const TEXT_DOMAIN = 'mt8-fc2-import-comments-adjuster';
	
	const SECRET_COMMENT_PATTERN = '/^SECRET: [1]/';
	const CLEAN_COMMENT_PATTERN = '/^SECRET: [0-1]\nPASS: [a-f0-9]{32}\n/';
	
	public function __construct() {
		
	}
	
	public function register_hooks() {
		
		add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ) );
		
	}
	
	public function plugins_loaded() {

		load_plugin_textdomain( self::TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
		if ( $this->is_active_movabletype_importer() ) {

			add_action( 'wp_insert_comment', array( &$this, 'wp_insert_comment' ), 10, 2 );

		} else {

			add_action( 'admin_notices', array( &$this, 'admin_notices' ) );

		}
		
	}
	
	public function admin_notices() {
		
	?>
    <div class="error">
        <ul>
            <li><?php _e( 'Mt8 FC2 Import Comments Adjuster is enabled , but not effective. It requires Movable Type and TypePad Importer in order to work.', self::TEXT_DOMAIN );?></li>
        </ul>
    </div>
	<?php
	
	}
	
	public function wp_insert_comment( $id, $comment ) {

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			return;
		}
		
		if ( $this->is_secret_comment( $comment->comment_content ) ) {
			$comment->comment_approved = '0';
		}

		$comment->comment_content = $this->adjust_comment_content( $comment->comment_content );

		$update_args = array(
			'comment_ID'       => $id,
			'comment_content'  => $comment->comment_content,
			'comment_approved' => $comment->comment_approved,
		);
		
		wp_update_comment( $update_args );
		
	}
	
	public function is_active_movabletype_importer() {
		
		return ( in_array( 'movabletype-importer/movabletype-importer.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) );
		
	}
	
	public function is_secret_comment( $comment_content ) {
		
		return ( 1 === preg_match( self::SECRET_COMMENT_PATTERN, $comment_content ) );
		
	}
	
	public function adjust_comment_content( $comment_content ) {
		
		return preg_replace( self::CLEAN_COMMENT_PATTERN, '', $comment_content );
		
	}

}