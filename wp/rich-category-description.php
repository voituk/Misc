<?php
/*
Plugin Name: Rich Category Description
Plugin URI: http://voituk.kiev.ua/wordpress-plugins/
Description: This plugin adds rich-text editing capability to the categories and tags management
Author: Vadim Voituk
Version: 0.1
Author URI: http://voituk.kiev.ua/
*/



// Using this instead of namespace
class WP_Rich_Category_Description {
	
	public static function init() {
				
		add_action('admin_head', array('WP_Rich_Category_Description', 'admin_head') );
		add_action('edit_term', array('WP_Rich_Category_Description', 'edit_term'));
	}
	
	public static function admin_head() {
		if ($_REQUEST['action'] == 'edit') {
			$elements = 'description';
			$selector = 'form#addtag #submit, form#addtag';
		} else {
			$elements = 'tag-description';
			$selector = 'form#edittag #submit, form#edittag';
		}
		
		wp_tiny_mce(true, array(
			'mode'     => 'exact',
			'elements' => $elements,
			'width'    => '100%', 
			'theme_advanced_buttons1' => 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,link,unlink,|,undo,redo,|,removeformat', 
		));
			
		
		
	echo <<<SCRIPT
		<script type="text/javascript">
		jQuery(function() {
			
			jQuery('$selector').submit(function(){
				tinyMCE.getInstanceById("tag-description").save()	
			})
			
		} )
		</script>
SCRIPT;
	}
	
	public static function edit_term() {
		global $tag_ID;
		wp_update_term($tag_ID, 'description', $_POST['description']);
	} 

}

function wprcd_admin_init() {
	if (!is_admin() || basename($_SERVER['PHP_SELF']) != 'edit-tags.php' || $_REQUEST['taxonomy']!='category')
		return;
	
	remove_all_filters( 'pre_term_description' );
	
	WP_Rich_Category_Description::init();
}
add_action('admin_init', 'wprcd_admin_init');



?>
