<?php

$asset3dClass = new Asset3DClass();

class Asset3DClass{
    public $asset_path = '';
    public $asset_path_url = '';
    public $asset_subdir = '';

    function __construct(){

        add_action('init', array($this, 'wpunity_assets_construct')); //wpunity_asset3d
        add_action('init', array($this, 'wpunity_assets_taxcategory')); //wpunity_asset3d_cat
        add_action('init', array($this, 'wpunity_assets_taxpscene')); //wpunity_asset3d_pscene


//        add_action("save_post", array($this, 'save_data_to_db_and_media'), 10, 3);
//        add_action('admin_footer', array($this, 'checktoradio'));
//        add_filter('get_sample_permalink', array($this, 'disable_permalink'));

        // TODO: use wp_handle_upload() to overwrite uploaded files

        // TODO: Stathis help me. DISABLE UPDATE BUTTON AND DISPLAY ADMIN NOTICES
    }

    /**
     * D1.01
     * Create Asset3D
     *
     * Asset3D as custom type 'wpunity_asset3d'
     */
    function wpunity_assets_construct()
    {

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
            'menu_icon' => 'dashicons-visibility',
            'taxonomies' => array('wpunity_asset3d_cat', 'wpunity_asset3d_pscene'),
            'supports' => array('title', 'editor', 'custom-fields'),
            'hierarchical' => false,
            'has_archive' => false,
        );

        register_post_type('wpunity_asset3d', $args);
    }

    //==========================================================================================================================================

    /**
     * D1.02
     * Create Asset Category
     *
     * Category of 3D asset as custom taxonomy
     */
    function wpunity_assets_taxcategory()
    {

        $labels = array(
            'name' => _x('Asset Category', 'taxonomy general name'),
            'singular_name' => _x('Asset Category', 'taxonomy singular name'),
            'menu_name' => _x('Asset Categories', 'admin menu'),
            'search_items' => __('Search Asset Categories'),
            'all_items' => __('All Asset Categories'),
            'parent_item' => __('Parent Asset Category'),
            'parent_item_colon' => __('Parent Asset Category:'),
            'edit_item' => __('Edit Asset Category'),
            'update_item' => __('Update Asset Category'),
            'add_new_item' => __('Add New Asset Category'),
            'new_item_name' => __('New Asset Category')
        );

        $args = array(
            'description' => 'Category of 3D asset',
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'hierarchical' => true,
            'show_admin_column' => true
        );

        register_taxonomy('wpunity_asset3d_cat', 'wpunity_asset3d', $args);

    }

    //==========================================================================================================================================

    /**
     * D1.03
     * Create Asset Scene
     *
     * Select To Which Scenes it belongs to (as custom taxonomy)
     */
    function wpunity_assets_taxpscene()
    {

        // 2. Select To Which Scenes it belongs to
        $labels = array(
            'name' => _x('Asset Scene', 'taxonomy general name'),
            'singular_name' => _x('Asset Scene', 'taxonomy singular name'),
            'menu_name' => _x('Asset Scenes', 'admin menu'),
            'search_items' => __('Search Asset Scenes'),
            'all_items' => __('All Asset Scenes'),
            'parent_item' => __('Parent Asset Scene'),
            'parent_item_colon' => __('Parent Asset Scene:'),
            'edit_item' => __('Edit Asset Scene'),
            'update_item' => __('Update Asset Scene'),
            'add_new_item' => __('Add New Asset Scene'),
            'new_item_name' => __('New Asset Scene')
        );

        $args = array(
            'description' => 'Scene assignment of Asset 3D',
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'hierarchical' => true,
            'show_admin_column' => true
        );

        register_taxonomy('wpunity_asset3d_pscene', 'wpunity_asset3d', $args);
    }

    //==========================================================================================================================================

}

//==========================================================================================================================================

/**
 * ??
 * Generate folder and Taxonomy (for scenes) with Game's slug/name
 *
 * Generate a folder in media to store assets named as the permalink of the game
 * Generate taxonomy with for Scene usage (wpunity_scene_pgame)
 */


//==========================================================================================================================================

    function custom_modify_upload_dir( $param ){
        $param['path'] = $this->asset_path; // $param['path'] . $mydir;
        $param['url']  = $this->asset_path_url; //$param['url'] . $mydir;
        $param['subdir'] = $this->asset_subdir;
//        error_log("path={$param['path']}");
//        error_log("url={$param['url']}");
//        error_log("subdir={$param['subdir']}");
//        error_log("basedir={$param['basedir']}");
//        error_log("baseurl={$param['baseurl']}");
//        error_log("error={$param['error']}");
        return $param;
    }

    /**
     * Upload each file internal code
     *
     * @param $post_id
     * @param $file
     * @param $supported_types
     * @param $meta_name
     */
    function uploader_wrapper($post_id, $asset3d_category_slug, $post_slug, $file, $supported_types, $meta_name){

        // Start uploading
        if(!empty($file['name'])) {

            // extension of the file
            $ext = basename($file['name']);

            // Get the file type of the upload
            $arr_file_type = wp_check_filetype($ext);

            //print_r($arr_file_type);
            $uploaded_type = $arr_file_type['type'];

            // Check if the type is supported. If not, throw an error.
            if(in_array($uploaded_type, $supported_types) || empty($uploaded_type)) {

                // set directory
                add_filter('upload_dir', array(&$this,'custom_modify_upload_dir'));


                //add_filter('upload_dir', array(&$this,'awesome_wallpaper_dir'));

                // Use the WordPress API to upload the file
                $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));

                if(isset($upload['error']) && $upload['error'] != 0) {
                    wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
                } else {
                    add_post_meta($post_id, $meta_name, $upload);
                    update_post_meta($post_id, $meta_name, $upload);
                } // end if/else

            } else {
                wp_die("The file type that you've uploaded is: ". $file['name'] . " " .$uploaded_type);
            } // end if/else

        } // end if

    }





    function generate_asset3d_folder($asset3d_category_Folder,$asset3d_Folder){
        global $post;

        // Get the scene that this asset3d belongs to
        $scene_Folder = get_the_terms($post, 'asset3d_scene_assignment')[0]->slug;

        // Get the game that the scene of the above scene
        $post_scene = get_posts( array('post_type' => 'scene', 'post_slug' => $scene_Folder) )[0];
        $game_Folder = get_the_terms($post_scene, 'scene_category')[0]->slug;

        // Generate new folder for the new asset
        $new_item_subfolder_path = $game_Folder.'/'.$scene_Folder.'/'.$asset3d_category_Folder.'/'.$asset3d_Folder;

        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = str_replace('\\','/',$upload_dir) . "/" . $new_item_subfolder_path;

        // Create dir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Set uploading dir (for temporary use)
        $this->asset_path = $upload_dir;
        $this->asset_path_url = get_site_url().'/wp-content/uploads/'.$new_item_subfolder_path;
        $this->asset_subdir = '/'.$new_item_subfolder_path;
    }

?>