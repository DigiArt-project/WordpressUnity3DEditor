<?php

$gameClass = new GameClass();

class GameClass{
    function __construct(){
        add_action('init', array($this, 'wpunity_games_construct')); //wpunity_game
        add_action('init', array($this, 'wpunity_games_taxcategory')); //wpunity_game_cat
    }

    /**
     * B1.01
     * Create Game
     *
     * Game as custom type 'wpunity_game'
     */
    function wpunity_games_construct(){

        $labels = array(
            'name'               => _x( 'Games', 'post type general name'),
            'singular_name'      => _x( 'Game', 'post type singular name'),
            'menu_name'          => _x( 'Games', 'admin menu'),
            'name_admin_bar'     => _x( 'Game', 'add new on admin bar'),
            'add_new'            => _x( 'Add New', 'add new on menu'),
            'add_new_item'       => __( 'Add New Game'),
            'new_item'           => __( 'New Game'),
            'edit'               => __( 'Edit'),
            'edit_item'          => __( 'Edit Game'),
            'view'               => __( 'View'),
            'view_item'          => __( 'View Game'),
            'all_items'          => __( 'All Games'),
            'search_items'       => __( 'Search Games'),
            'parent_item_colon'  => __( 'Parent Games:'),
            'parent'             => __( 'Parent Game'),
            'not_found'          => __( 'No Games found.'),
            'not_found_in_trash' => __( 'No Games found in Trash.')
        );

        $args = array(
            'labels'                => $labels,
            'description'           => 'A Game consists of several scenes',
            'public'                => true,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'show_in_nav_menus'     => false,
            'menu_position'     => 26,
            'menu_icon'         =>'dashicons-media-interactive',
            'taxonomies'        => array('wpunity_game_cat'),
            'supports'          => array('title','editor','custom-fields'),
            'hierarchical'      => false,
            'has_archive'       => false,
        );

        register_post_type('wpunity_game', $args);

    }

    //==========================================================================================================================================

    /**
     * B1.02
     * Create Game Category
     *
     * Category of Games as custom taxonomy 'wpunity_game_cat'
     */
    function wpunity_games_taxcategory(){

        $labels = array(
            'name'              => _x( 'Game Category', 'taxonomy general name'),
            'singular_name'     => _x( 'Game Category', 'taxonomy singular name'),
            'menu_name'         => _x( 'Game Categories', 'admin menu'),
            'search_items'      => __( 'Search Game Categories'),
            'all_items'         => __( 'All Game Categories'),
            'parent_item'       => __( 'Parent Game Category'),
            'parent_item_colon' => __( 'Parent Game Category:'),
            'edit_item'         => __( 'Edit Game Category'),
            'update_item'       => __( 'Update Game Category'),
            'add_new_item'      => __( 'Add New Game Category'),
            'new_item_name'     => __( 'New Game Category')
        );

        $args = array(
            'description' => 'Category of Game',
            'labels'    => $labels,
            'public'    => false,
            'show_ui'   => true,
            'hierarchical' => true,
            'show_admin_column' => true
        );

        register_taxonomy('wpunity_game_cat', 'wpunity_game', $args);

    }
}

//==========================================================================================================================================

/**
 * B1.03
 * Generate folder with Game's slug
 *
 * Generate a folder in media to store assets named as the permalink of the game
 */

function wpunity_create_folder_game( $new_status, $old_status, $post ){

    $post_type = get_post_type($post);
    $post_slug = $post->post_name;

    if ($post_type == 'wpunity_game') {
        if ( ($new_status == 'publish') && ($old_status != 'publish') ) {
            $media_subfolder_to_generate = $post_slug;
            $upload = wp_upload_dir();
            $upload_dir = $upload['basedir'];
            $upload_dir = $upload_dir . "/" . $media_subfolder_to_generate;

            $upload_dir = str_replace('\\','/',$upload_dir);

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755);
            }
        }else{
            //TODO It's not a new Game so DELETE everything
        }

    }
}

add_action('transition_post_status','wpunity_create_folder_game',10,3);
?>