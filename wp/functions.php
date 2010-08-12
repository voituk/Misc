<?php


// added by Vadim Voituk
if (!function_exists('is_in_toplevel_category')) {
	/**
	* Check if current post relate to the specified category or any level parent category
	* @param string|array $slug_list - category slug or slugs array
	* @usage Inside The_Loop
	*/
	function is_in_toplevel_category($slug_list) {
		global $post;
		$slug_list = (array)$slug_list;
		//print_r($post);
		foreach(get_the_category() as $cat) {
			if (in_array($cat->slug, $slug_list))
				return true;

			foreach( explode('||',get_category_parents($cat, false, '||', true)) as $s) {
				if (in_array($s, $slug_list))
					return true;
			}
		}
		return false;
	}
}

