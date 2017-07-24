<?php

$asset3dClass = new Asset3DClass();

class Asset3DClass{
    public $asset_path = '';
    public $asset_path_url = '';
    public $asset_subdir = '';

    function __construct(){
        add_action('init', array($this, 'wpunity_assets_construct')); //wpunity_asset3d 'ASSETS 3D'
        add_action('init', array($this, 'wpunity_assets_taxcategory')); //wpunity_asset3d_cat 'ASSET TYPES'
        add_action('init', array($this, 'wpunity_assets_taxpgame')); //wpunity_asset3d_pgame 'ASSET GAMES'
    }

    // Create Asset3D as custom type 'wpunity_asset3d'
    function wpunity_assets_construct(){
        $labels = array(
            'name' => _x('Assets 3D', 'post type general name'),
            'singular_name' => _x('Asset 3D', 'post type singular name'),
            'menu_name' => _x('Assets 3D', 'admin menu'),
            'name_admin_bar' => _x('Asset 3D', 'add new on admin bar'),
            'add_new' => _x('Add New', 'add new on menu'),
            'add_new_item' => __('Add New Asset 3D'),
            'new_item' => __('New Asset 3D'),
            'edit' => __('Edit'),
            'edit_item' => __('Edit Asset 3D'),
            'view' => __('View'),
            'view_item' => __('View Asset 3D'),
            'all_items' => __('All Assets 3D'),
            'search_items' => __('Search Assets 3D'),
            'parent_item_colon' => __('Parent Assets 3D:'),
            'parent' => __('Parent Asset 3D'),
            'not_found' => __('No Assets 3D found.'),
            'not_found_in_trash' => __('No Assets 3D found in Trash.')
        );
        $args = array(
            'labels' => $labels,
            'description' => 'Displays Assets 3D',
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_in_nav_menus' => false,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-editor-textcolor',
            'taxonomies' => array('wpunity_asset3d_cat', 'wpunity_asset3d_pgame'),
            'supports' => array('title', 'editor', 'custom-fields'),
            'hierarchical' => false,
            'has_archive' => false,
        );
        register_post_type('wpunity_asset3d', $args);
    }

    //==========================================================================================================================================

    // Create Asset Category as custom taxonomy
    function wpunity_assets_taxcategory(){
        $labels = array(
            'name' => _x('Asset Type', 'taxonomy general name'),
            'singular_name' => _x('Asset Type', 'taxonomy singular name'),
            'menu_name' => _x('Asset Types', 'admin menu'),
            'search_items' => __('Search Asset Types'),
            'all_items' => __('All Asset Types'),
            'parent_item' => __('Parent Asset Type'),
            'parent_item_colon' => __('Parent Asset Type:'),
            'edit_item' => __('Edit Asset Type'),
            'update_item' => __('Update Asset Type'),
            'add_new_item' => __('Add New Asset Type'),
            'new_item_name' => __('New Asset Type')
        );
        $args = array(
            'description' => 'Type (Category) of 3D asset',
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'hierarchical' => false,
            'show_admin_column' => true
        );
        register_taxonomy('wpunity_asset3d_cat', 'wpunity_asset3d', $args);
    }

    //==========================================================================================================================================

    // Create Asset Game as custom taxonomy
    function wpunity_assets_taxpgame(){
        $labels = array(
            'name' => _x('Asset Game', 'taxonomy general name'),
            'singular_name' => _x('Asset Game', 'taxonomy singular name'),
            'menu_name' => _x('Asset Games', 'admin menu'),
            'search_items' => __('Search Asset Games'),
            'all_items' => __('All Asset Games'),
            'parent_item' => __('Parent Asset Game'),
            'parent_item_colon' => __('Parent Asset Game:'),
            'edit_item' => __('Edit Asset Game'),
            'update_item' => __('Update Asset Game'),
            'add_new_item' => __('Add New Asset Game'),
            'new_item_name' => __('New Asset Game')
        );
        $args = array(
            'description' => 'Game assignment of Asset 3D',
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'hierarchical' => false,
            'show_admin_column' => true
        );
        register_taxonomy('wpunity_asset3d_pgame', 'wpunity_asset3d', $args);
    }

}

//==========================================================================================================================================

//Create PathData for each asset as custom field in order to upload files at pathdata/Models folder
function wpunity_create_pathdata_asset( $post_id ){

    $post_type = get_post_type($post_id);

    if ($post_type == 'wpunity_asset3d') {
        $post = get_post($post_id);
        //FORMAT: uploads / slug Game / Models / ...

        $parentGameID = intval($_POST['wpunity_asset3d_pgame'], 10);
        $parentGameSlug = ( $parentGameID > 0 ) ? get_term( $parentGameID, 'wpunity_asset3d_pgame' )->slug : NULL;

        $upload_dirpath = $parentGameSlug;

        update_post_meta($post_id,'wpunity_asset3d_pathData',$upload_dirpath);
    }
}

add_action('save_post','wpunity_create_pathdata_asset',10,3);

//==========================================================================================================================================

?>