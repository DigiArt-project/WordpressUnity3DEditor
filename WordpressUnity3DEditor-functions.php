<?php
/**
 * Plugin Name: WordpressUnity3DEditor
 * Plugin URI: http://yoursite.com
 * Description: functions for VR actions that are added to the main functions.php
 * Author: Dimitrios Ververidis
 * Author URI: http://yoursite.com
 * Version: 0.1.5
 */

/*
 * Change root .htaccess for uploading big data files
 *
 *
php_value upload_max_filesize 256M
php_value post_max_size 512M
php_value max_input_time 2400

// in php you can check their values with

echo ini_get('post_max_size').chr(10);
echo ini_get('max_input_time').chr(10);
--
 */
// Only these variables can change with php
// @ini_set( 'memory_limit', '512M');
@ini_set( 'max_execution_time', '2400' );

// Set scope based on project:
// DigiArt - Virtual Tour: 0
// Envisage - Virtual Lab: 1
// Default - Game Project: any other number
$project_scope = 1;


//===================================== Styles & Scripts ====================================
function wpunity_load_jquery_scripts() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-slider');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-effects-core');
	wp_enqueue_style( 'jquery-ui-css' , plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css' );
	wp_enqueue_style( 'jquery-ui-theme-css' , plugin_dir_url( __FILE__ ) . 'css/jquery-ui.theme.min.css' );
}
add_action('wp_enqueue_scripts', 'wpunity_load_jquery_scripts' );

function wpunity_register_scripts() {
	wp_register_script( 'wpunity_asset_editor_scripts', plugin_dir_url( __FILE__ ).'js_libs/wpunity_asset_editor_scripts.js');

	wp_register_script( 'flot', plugin_dir_url( __FILE__ ) . 'js_libs/jquery.flot.js');
	wp_register_script( 'flot-axis-labels', plugin_dir_url( __FILE__ ) . 'js_libs/jquery.flot.axislabels.js');
	wp_register_script( 'wpunity_scripts', plugin_dir_url( __FILE__ ) . 'js_libs/wpunity_scripts.js');
	wp_register_script( 'wpunity_lightslider', plugin_dir_url( __FILE__ ) . 'js_libs/lightslider.min.js');

    wp_register_script( 'wpunity_html2canvas', plugin_dir_url( __FILE__ ) . 'js_libs/html2canvas.min.js');
    
	//wp_register_script( 'wpunity_compile_request', plugin_dir_url( __FILE__ ) . 'js_libs/assemble_compile_commands/request_game_compile.js',
//	null, null, false);
//
//wp_register_script( 'wpunity_assemble_request', plugin_dir_url( __FILE__ ) . 'js_libs/assemble_compile_commands/request_game_assemble.js',
//	null, null, false);

	wp_register_script( 'wpunity_assepile_request', plugin_dir_url( __FILE__ ) . 'js_libs/assemble_compile_commands/request_game_assepile.js',
		null, null, false);

	wp_register_script( 'wpunity_savescene_request', plugin_dir_url( __FILE__ ) . 'js_libs/save_scene_ajax/wpunity_save_scene_ajax.js',
		null, null, false);

	wp_register_script( 'wpunity_content_interlinking_request', plugin_dir_url( __FILE__ ) . 'js_libs/content_interlinking_commands/content_interlinking.js',
		null, null, false);

	wp_register_script( 'wpunity_segmentation_request', plugin_dir_url( __FILE__ ) . 'js_libs/semantics_commands/segmentation.js',
		null, null, false);

	wp_register_script( 'wpunity_classification_request', plugin_dir_url( __FILE__ ) . 'js_libs/semantics_commands/classification.js',
		null, null, false);


	wp_register_script( 'wpunity_fetch_asset_scenes_request', plugin_dir_url( __FILE__ ) . 'js_libs/scriptFileBrowserToolbarWPway.js',
		null, null, false);




//=========================== THREE js scripts ============================================
//	wp_register_script( 'wpunity_load_threejs', plugin_dir_url( __FILE__ ) . 'js_libs/threejs79/three.js', null, null, false);
//	wp_register_script( 'wpunity_load_objloader', plugin_dir_url( __FILE__ ) . 'js_libs/threejs79/OBJLoader.js', null, null, false);
//	wp_register_script( 'wpunity_load_mtlloader', plugin_dir_url( __FILE__ ) . 'js_libs/threejs79/MTLLoader.js', null, null, false);
// wp_register_script( 'wpunity_load_orbitcontrols', plugin_dir_url( __FILE__ ) . 'js_libs/threejs79/OrbitControls.js', null, null, false);

	wp_register_script( 'wpunity_load87_threejs', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/three.js', null, null, false);
    wp_register_script( 'wpunity_load87_objloader', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/OBJLoader.js', null, null, false);
	wp_register_script( 'wpunity_load87_objloader2', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/OBJLoader2.js', null, null, false);
//    wp_register_script( 'wpunity_load87_objloader2_support', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/LoaderSupport.js', null, null, false);
    wp_register_script( 'wpunity_load87_wwobjloader2', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/WWOBJLoader2.js', null, null, false);
    
    wp_register_script( 'wpunity_load87_mtlloader', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/MTLLoader.js', null, null, false);
    wp_register_script( 'wpunity_load87_orbitcontrols', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/OrbitControls.js', null, null, false);
    wp_register_script( 'wpunity_load87_pdbloader', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/PDBLoader.js', null, null, false);
	

	wp_register_script( 'wpunity_load87_trackballcontrols', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/TrackballControls.js', null, null, false);
    wp_register_script( 'wpunity_CSS2DRenderer', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/CSS2DRenderer.js', null, null, false);

	wp_register_script( 'wpunity_load_sceneexporterutils', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/SceneExporterUtils.js', null, null, false);
	wp_register_script( 'wpunity_load_sceneexporter', plugin_dir_url( __FILE__ ) . 'js_libs/threejs87/SceneExporter.js', null, null, false);

// ToDo: For some reason these can not be enqueued in vr_editor.php, try again when vr_editor is made as js
//wp_register_script( 'wpunity_load_pointerlockcontrols', plugin_dir_url( __FILE__ ) . 'js_libs/threejs79/PointerLockControls.js', null, null, false);
//wp_register_script( 'wpunity_load_transformcontrols', plugin_dir_url( __FILE__ ) . '/js_libs/threejs79/TransformControls.js', null, null, false);
//wp_register_script( 'wpunity_load_datgui'            , plugin_dir_url( __FILE__ ) . 'js_libs/threejs79/dat.gui.js', null, null, false);
//wp_register_script( 'wpunity_load_statsmin', plugin_dir_url( __FILE__ ) . 'js_libs/threejs79/stats.min.js', null, null, false);

	//wp_register_script( 'wu_3d_view', plugin_dir_url( __FILE__ ) . 'js_libs/wu_3d_view.js', null, null, false);

	wp_register_script( 'WU_webw_3d_view', plugin_dir_url( __FILE__ ) . 'js_libs/WU_webw_3d_view.js', null, null, false);

//================================= Scene YAML Templates ===================================
}
add_action('wp_enqueue_scripts', 'wpunity_register_scripts' );

function wpunity_register_styles() {
	wp_register_style( 'wpunity_backend', plugin_dir_url( __FILE__ ) . 'css/wpunity_backend.css' );
	wp_register_style( 'wpunity_vr_editor', plugin_dir_url( __FILE__ ) . 'css/vr_editor_style.css' );
	wp_register_style( 'wpunity_vr_editor_filebrowser', plugin_dir_url( __FILE__ ) . 'css/vr_editor_fileBrowserStyle.css' );
	wp_register_style( 'wpunity_material_stylesheet',  plugin_dir_url( __FILE__ ) . 'node_modules/material-components-web/dist/material-components-web.css' );
	wp_register_script( 'wpunity_material_scripts', plugin_dir_url( __FILE__ ) . 'node_modules/material-components-web/dist/material-components-web.js');
	wp_register_style( 'wpunity_frontend_stylesheet',  plugin_dir_url( __FILE__ ) . 'css/wpunity_frontend.css' );
	wp_register_style( 'wpunity_lightslider_stylesheet',  plugin_dir_url( __FILE__ ) . 'css/lightslider.min.css' );

	// TODO: When ready for production, ignore  node_modules folder and move the 2 material css & js files to another folder.
	// Material & Frontend CSS & Scripts
	wp_enqueue_style('wpunity_material_stylesheet');
	wp_enqueue_script('wpunity_material_scripts');
	wp_enqueue_style( 'wpunity_material_icons', plugin_dir_url( __FILE__ ) . 'css/material-icons/material-icons.css' );
	wp_enqueue_style('wpunity_frontend_stylesheet');
	wp_enqueue_style( 'wpunity_lightslider_stylesheet');
}
add_action('wp_enqueue_scripts', 'wpunity_register_styles' );



require_once ( plugin_dir_path( __FILE__ ) . 'includes/wpunity-users-roles.php');

//===================================== Games ============================================

require_once ( plugin_dir_path( __FILE__ ) . 'includes/wpunity-types-games.php');

require_once ( plugin_dir_path( __FILE__ ) . 'includes/wpunity-types-games-tax.php');

require_once ( plugin_dir_path( __FILE__ ) . 'includes/wpunity-types-games-data.php');


//===================================== Scenes ============================================

include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-types-scenes.php');

include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-types-scenes-tax.php' );

include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-types-scenes-data.php' );

//===================================== Assets ============================================

include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-types-assets.php' );

include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-types-assets-tax.php' );

include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-types-assets-data.php' );

//===================================== Other ============================================

include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-core-functions.php' );

include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-core-setget-functions.php' );

//3.01 Create Initial Asset Categories

include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-page-settings.php' );


include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-page-templates.php' );
register_activation_hook(__FILE__,'wpunity_create_openGamePage');
register_activation_hook(__FILE__,'wpunity_create_editGamePage');
register_activation_hook(__FILE__,'wpunity_create_editScenePage');
register_activation_hook(__FILE__,'wpunity_create_editScene2DPage');
register_activation_hook(__FILE__,'wpunity_create_editSceneExamPage');
register_activation_hook(__FILE__,'wpunity_create_editAsset3D');
include_once( plugin_dir_path( __FILE__ ) . 'includes/templates/edit-wpunity_asset3D-saveData.php' );





// Make the games versions table on activating the plugin
include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-db-table-creations.php' );
register_activation_hook( __FILE__, 'wpunity_db_create_games_versions_table' );

// Add helper functions file
include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-core-helper.php' );


include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-core-project-handler.php' );

include_once( plugin_dir_path( __FILE__ ) . 'includes/default_game_project_settings/wpunity-default-energy-settings.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/default_game_project_settings/wpunity-default-energy-yamls.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/default_game_project_settings/wpunity-default-energy-compile.php' );

include_once( plugin_dir_path( __FILE__ ) . 'includes/default_game_project_settings/wpunity-default-archaeology-yamls.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/default_game_project_settings/wpunity-default-archaeology-settings.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/default_game_project_settings/wpunity-default-archaeology-compile.php' );

include_once( plugin_dir_path( __FILE__ ) . 'includes/default_game_project_settings/wpunity-default-chemistry-settings.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/default_game_project_settings/wpunity-default-chemistry-yamls.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/default_game_project_settings/wpunity-default-chemistry-compile.php' );


// ===================== Obsolete ===================================

/**
 * Allow JSON file type to be uploaded.
 *
 * @param $mime_types
 *
 * @return mixed
 */
function my_myme_types($mime_types){
	$mime_types['json'] = 'text/json';
	$mime_types['obj'] = 'text/plain';
	$mime_types['mp4'] = 'video/mp4';
    $mime_types['ogv'] = 'application/ogg';
    $mime_types['ogg'] = 'application/ogg';
	$mime_types['mtl'] = 'text/plain';
	$mime_types['mat'] = 'text/plain';
	$mime_types['pdb'] = 'text/plain';
	return $mime_types;
}

add_filter('upload_mimes', 'my_myme_types', 1, 1);



//Scripts about Upload button in Metaboxes
add_action('plugins_loaded', function() {
	if($GLOBALS['pagenow']=='post.php') {
		add_action('admin_print_scripts', 'my_admin_scripts');
		add_action('admin_print_styles',  'my_admin_styles');
	}
});

function my_admin_scripts() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
}

//wp_register_script('my-upload', WP_PLUGIN_URL.'/my-script.js', array('jquery','media-upload','thickbox'));
//  wp_enqueue_script('my-upload');
function my_admin_styles()  {
	wp_enqueue_style('thickbox');

}

/**
 *   shorcode to show content inside page with [visitor] Some content for the people just browsing your site. [/visitor]
 */
add_shortcode( 'visitor', 'visitor_check_shortcode' );

function visitor_check_shortcode( $atts, $content = null ) {
    if ( ( !is_user_logged_in() && !is_null( $content ) ) || is_feed() )
        return $content;
    return '';
}

/**
 * On reset password redirect to wpunity-main
 */
function wpse_lost_password_redirect() {
    // Check if have submitted
    $confirm = ( isset($_GET['checkemail'] ) ? $_GET['checkemail'] : '' );

    if( $confirm ) {
        wp_redirect( get_site_url( ).'/wpunity-main.php' );
        exit;
    }
}
add_action('login_headerurl', 'wpse_lost_password_redirect');



// Ajax for fetching game's assets within asset browser widget at vr_editor
add_action( 'wp_ajax_wpunity_fetch_game_assets_action', 'wpunity_fetch_game_assets_action_callback' );


add_action('wp_ajax_wpunity_save_scene_async_action','wpunity_save_scene_async_action_callback');


add_action('wp_ajax_wpunity_save_expid_async_action','wpunity_save_expid_async_action_callback');

// Ajax for saving gio asynchronoysly
add_action('wp_ajax_wpunity_save_gio_async_action','wpunity_save_gio_async_action_callback');

// Ajax for deleting scene
add_action('wp_ajax_wpunity_delete_scene_action','wpunity_delete_scene_frontend_callback');

//$fo = fopen("output_activation.txt","w");
//fwrite($fo, dirname( __FILE__ ) . '/includes/wpunity-core-functions.php');
//fclose($fo);

//include_once dirname( __FILE__ ) . '/includes/wpunity-core-functions.php';

//include_once( plugin_dir_path( __FILE__ ) . 'includes/wpunity-core-functions.php' );
//register_activation_hook( __FILE__ , 'wpunity_createJoker_activation' );






?>