<?php
/**
 * 
 *
 * @package  
 * @version  
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UMP_Admin_Settings_Page', false ) ) :

	
	class UMP_Admin_Settings_Page {

		
		private static $settings = array();

		
		private static $errors = array();

		
		private static $messages = array();

		public function __construct() {
			//print_r($_GET);
			$page = sanitize_text_field($_GET['page']);
			switch($page){
				case "ump-settings":
				echo '<div class="wrap">';

				include_once dirname( __FILE__ ) . '/view/settings-left-section.php';
				
				echo '</div>';
				echo '<div class="clear"></div>';
				break;

				default:
				include_once dirname( __FILE__ ) . '/view/settings-left-section.php';
				break;

			}
			

	}

		

	}

endif;

return new UMP_Admin_Settings_Page();