<?php 
/*
 * Plugin Name: WODTogether Whiteboards
 * Plugin URI: http://wodtogether.com
 * Description: Automatic embedding of WODTogether whiteboards into blog posts. Other widgets too
 * Version: 3.3.1
 * Author: WODTogether, Inc.
 * Author URI: http://wodtogether.com
 */

if (!function_exists('add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}


/**
 * WODTogether Settings
 */
add_action('admin_menu', 'wodtogether_admin_add_page');
function wodtogether_admin_add_page() {
	add_options_page('WODTogether Plugin Page', 'WODTogether', 'manage_options', 'plugin', 'wodtogether_options_page');
}

function wodtogether_options_page() {
	echo "<div>
	<h2>WODTogether Plugin Settings</h2>
	For help visit: <a href=\"http://wodtogether.uservoice.com/knowledgebase/articles/71819-wordpress-plugin\">http://wodtogether.uservoice.com</a>
	<form action=\"options.php\" method=\"post\">";
	
	settings_fields('wodtogether_options');
	do_settings_sections('wodtogether'); 
	
	echo "<input class='button-primary' name=\"Submit\" type=\"submit\" value=\"Save Changes\" /></form></div>";
}

add_action('admin_init', 'wodtogether_admin_init');
function wodtogether_admin_init(){
	register_setting( 'wodtogether_options', 'wodtogether_options', 'wodtogether_options_validate' );
	add_settings_section('wodtogether_main', 'Main Settings', 'wodtogether_section_text', 'wodtogether');
	
	add_settings_field('wodtogether_gym_id', 'Gym ID', 'wodtogether_setting_gym_id', 'wodtogether', 'wodtogether_main');
	add_settings_field('wodtogether_program_id', 'Program ID', 'wodtogether_setting_program_id', 'wodtogether', 'wodtogether_main');
	add_settings_field('wodtogether_date_offset', 'Whiteboard Date Setting', 'wodtogether_setting_date_offset', 'wodtogether', 'wodtogether_main');
	add_settings_field('wodtogether_required_category', 'Required Category', 'wodtogether_setting_required_category', 'wodtogether', 'wodtogether_main');
	add_settings_field('wodtogether_static_page', 'Static Page (Today\'s WOD)', 'wodtogether_setting_static_page', 'wodtogether', 'wodtogether_main');
	add_settings_field('wodtogether_inline_results', 'Allow Inline Results and Comments', 'wodtogether_setting_inline_results', 'wodtogether', 'wodtogether_main');
}

function wodtogether_section_text() {
	echo '<p>WODTogether Integration Settings</p>';
}

function wodtogether_setting_gym_id() {
	$options = get_option('wodtogether_options');
	echo "<input id='wodtogether_gym_id' name='wodtogether_options[gym_id]' size='20' type='text' value='{$options['gym_id']}' />
	<p><strong>Note:</strong> Find your gym ID by going to <em>My Gym &raquo; Recent Activity</em> on <a href=\"http://wodtogether.com\">WODTogether</a>. The URL (address bar) will look like: http://wodtogether.com/gym/view/id/<strong>####</strong> -- copy that number, it's your gym id.</p>";
}
function wodtogether_setting_program_id() {
	$options = get_option('wodtogether_options');
	echo "<input id='wodtogether_program_id' name='wodtogether_options[program_id]' size='20' type='text' value='{$options['program_id']}' />
	<p><strong>Note:</strong> Provide a program ID to show only that program's whiteboards. Blank (default) will show whiteboards from all of your gym's programs.</p>";
}
function wodtogether_setting_date_offset() {
	$options = get_option('wodtogether_options');

	$selected = (isset($options['date_offset']) && $options['date_offset']) ? ' selected' : '';
	echo "<select style='width:200px' id='wodtogether_date_offset' name='wodtogether_options[date_offset]'><option value='0'>Same Day as Blog Post</option><option value='1' {$selected}>Day After Blog Post</option></select>
	<p><strong>Note:</strong> If you publish your workouts the day or night before, use \"Day After Blog Post\"</p>";
}
function wodtogether_setting_required_category() {
	$options = get_option('wodtogether_options');
	echo "<select id='wodtogether_required_category' name='wodtogether_options[required_category]'><option value=''>Embed Whiteboards on All Blog Posts </option>";
	
	$blog_categories = get_categories(array('hide_empty' => 0));
	
	foreach($blog_categories as $category) {
		$selected = ($options['required_category'] == $category->term_id) ? ' selected' : '';
		echo "<option value=\"{$category->term_id}\"{$selected}>{$category->name}</option>";
	}
	
	echo "</select>";
}
function wodtogether_setting_static_page() {
	$options = get_option('wodtogether_options');
	echo "<select id='wodtogether_static_page' name='wodtogether_options[static_page]'><option value=''>None </option>";
	
	$blog_pages = get_pages();
	
	foreach($blog_pages as $page) {
		$selected = ($options['static_page'] == $page->ID) ? ' selected' : '';
		echo "<option value=\"{$page->ID}\"{$selected}>{$page->post_title}</option>";
	}
	
	echo "</select>
	<p><strong>Note:</strong> If you want to show Today's WOD on a static Page (instead of/in addition to on each blog post), select a page in the dropdown.</p>";
}
function wodtogether_setting_inline_results() {
	$options = get_option('wodtogether_options');
	
	$checked = (isset($options['inline_results']) && $options['inline_results']) ? ' checked' : '';
	echo "<input id='wodtogether_inline_results' name='wodtogether_options[inline_results]' {$checked} type='checkbox' value='1' />";
}


function wodtogether_options_validate($input) {
	if (!is_numeric($input['gym_id'])) {
		$input['gym_id'] = 2958; // default to main site
	}
	
	if (!is_numeric($input['program_id'])) {
		$input['program_id'] = ''; // default to main site
	}
	
	if (!is_numeric($input['date_offset'])) {
		$input['date_offset'] = 0; // default to same day
	}
	
	if (!is_numeric($input['inline_results'])) {
		$input['inline_results'] = 0; // default to off
	}
	
	$input['required_category'] = trim($input['required_category']);
	
	if (!is_numeric($input['static_page'])) {
		$input['static_page'] = 0; // default to main site
	}
	
	return $input;
}
/**
 * END WODTogether Settings
 */
 
// count how many whiteboards have been loaded for this page
$whiteboarded = 0;
$wodtogetherSettings = get_option('wodtogether_options');

wp_register_style('wodtogether-whiteboards', WP_PLUGIN_URL . '/wodtogether-whiteboards/wodtogether-whiteboards.css');
wp_enqueue_style('wodtogether-whiteboards');

wp_enqueue_script('wodtogether-whiteboards',
	WP_PLUGIN_URL . '/wodtogether-whiteboards/wodtogether-whiteboards.js',
	array('jquery')
);

add_filter( 'page_template', 'wodtogether_page_template' );
function wodtogether_page_template( $page_template )
{
	global $wodtogetherSettings;
	
	$pageID = get_the_ID();
    if ( is_page( $pageID ) && $wodtogetherSettings['static_page'] == $pageID) {
        $page_template = dirname( __FILE__ ) . '/wod.php';
    }
    return $page_template;
}

add_filter('the_content', 'showWhiteboard');

/**
 * Show whiteboard on each post, or page if it matches the static page
 */
function showWhiteboard($input, $todays_wods=false)
{
	global $whiteboarded;
	global $wodtogetherSettings;
	global $post;

	if (is_page($post->ID) && (!isset($wodtogetherSettings['static_page']) || $post->ID != $wodtogetherSettings['static_page'])) {
		return $input;
	}

	// force todays_wods for static page
	if (is_page($post->ID) && isset($wodtogetherSettings['static_page']) && $post->ID == $wodtogetherSettings['static_page']) {
		$todays_wods = true;
	}

	if (!is_page( $wodtogetherSettings['static_page'] ) && isset($wodtogetherSettings['required_category']) && $wodtogetherSettings['required_category']) {
		$post_categories = get_the_category();
		$has_required_category = false;
		foreach($post_categories as $post_category) {
			if ($post_category->term_id == $wodtogetherSettings['required_category']) {
				$has_required_category = true;
			}
		}
		
		if (!$has_required_category) {
			return $input;
		}
	}
	
	$gym_id =(!isset($wodtogetherSettings['gym_id']) || !$wodtogetherSettings['gym_id'] || !is_numeric($wodtogetherSettings['gym_id'])) ? 2958 : $wodtogetherSettings['gym_id']; 
	$program_id = (!isset($wodtogetherSettings['program_id']) || !$wodtogetherSettings['program_id'] || !is_numeric($wodtogetherSettings['program_id'])) ? '' : $wodtogetherSettings['program_id']; 
	$inline_results = (isset($wodtogetherSettings['inline_results']) && $wodtogetherSettings['inline_results']) ? 'true' : 'false';
	
	$date_offset = (isset($wodtogetherSettings['date_offset']) && $wodtogetherSettings['date_offset']) ? 86400 : 0;	
	
	$datestr = ($todays_wods) ? 'today' : $GLOBALS['post']->post_date;
	$date = date('Y-m-d', strtotime($datestr) + $date_offset);
	
	$whiteboardsHtml = "<div id=\"wb_{$whiteboarded}\" class=\"wodtogether_whiteboard_wods wodtogether_wb\" data-gid=\"{$gym_id}\" data-pids=\"{$program_id}\" data-date=\"{$date}\" data-inline_results=\"{$inline_results}\"></div>";
	
	$whiteboarded++;
	return $input . $whiteboardsHtml;
}
