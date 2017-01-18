<?php
/**
 * ?3.01
 * Create metabox with Custom Fields for Game
 *
 * ($wpunity_databox3)
 */

$DS = DIRECTORY_SEPARATOR ;

//============= Compile button resources ================
// load css/wpunity_backend.css
wp_enqueue_style('wpunity_backend');

// load request_game.js script from js_libs
wp_enqueue_script( 'wpunity_compiler_request',
    plugins_url('wordpressunity3deditor/js_libs/compiler_commands/request_game.js'),
    null, null, false);

// Some parameters to pass in the request_game.js  ajax
wp_localize_script('wpunity_compiler_request', 'phpvars',
    array('pluginsUrl' => plugins_url(),
          'PHP_OS'     => PHP_OS,
          'game_dirpath'=> realpath(dirname(__FILE__).'/..').$DS.'test_compiler'.$DS.'game_windows', //'C:\xampp\htdocs\digiart-project_Jan17\wp-content\plugins\wordpressunity3deditor\test_compiler\game_windows'));
          'game_urlpath'=> plugins_url( 'wordpressunity3deditor' ).'/test_compiler/game_windows'
));
///=============================================

//This imc_prefix will be added before all of our custom fields
$wpunity_prefix = 'wpunity_game_';

//All information about our meta box
$wpunity_databox3 = array(
    'id' => 'wpunity-games-databox',
    'page' => 'wpunity_game',
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            'name' => 'Latitude',
            'desc' => 'Game\'s Latitude',
            'id' => $wpunity_prefix . 'lat',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'Longitude',
            'desc' => 'Game\'s Longitude',
            'id' => $wpunity_prefix . 'lng',
            'type' => 'text',
            'std' => ''
        )
    )
);

//==========================================================================================================================================

/**
 * B3.02
 * Add and Show the metabox with Custom Field for Game
 *
 * ($wpunity_databox3)
 */

function wpunity_games_databox_add() {
    global $wpunity_databox3;
    add_meta_box($wpunity_databox3['id'], 'Game Data', 'wpunity_games_databox_show', $wpunity_databox3['page'], $wpunity_databox3['context'], $wpunity_databox3['priority']);

}

function wpunity_games_compilerbox_add(){
    add_meta_box('wpunity-games-compiler-box', 'Game Compiler', 'wpunity_games_compiler_show', 'wpunity_game', 'side', 'low');
}


add_action('admin_menu', 'wpunity_games_databox_add');
add_action('admin_menu', 'wpunity_games_compilerbox_add');


function wpunity_games_databox_show(){
    global $wpunity_databox3, $post;
    // Use nonce for verification
    echo '<input type="hidden" name="wpunity_games_databox_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
    echo '<table class="form-table" id="wpunity-custom-fields-table">';
    foreach ($wpunity_databox3['fields'] as $field) {
        // get current post meta data
        $meta = get_post_meta($post->ID, $field['id'], true);
        echo '<tr>',
        '<th style="width:20%"><label for="', esc_attr($field['id']), '">', esc_html($field['name']), '</label></th>',
        '<td>';


        switch ($field['type']) {
            case 'text':
                echo '<input type="text" name="', esc_attr($field['id']), '" id="', esc_attr($field['id']), '" value="', esc_attr($meta ? $meta : $field['std']), '" size="30" style="width:97%" />', '<br />', esc_html($field['desc']);
                break;
            case 'numeric':
                echo '<input type="number" name="', esc_attr($field['id']), '" id="', esc_attr($field['id']), '" value="', esc_attr($meta ? $meta : $field['std']), '" size="30" style="width:97%" />', '<br />', esc_html($field['desc']);
                break;
            case 'textarea':
                echo '<textarea name="', esc_attr($field['id']), '" id="', esc_attr($field['id']), '" cols="60" rows="4" style="width:97%">', esc_attr($meta ? $meta : $field['std']), '</textarea>', '<br />', esc_html($field['desc']);
                break;
            case 'select':
                echo '<select name="', esc_attr($field['id']), '" id="', esc_attr($field['id']), '">';
                foreach ($field['options'] as $option) {
                    echo '<option ', $meta == $option ? ' selected="selected"' : '', '>', esc_html($option), '</option>';
                }
                echo '</select>';
                break;
            case 'checkbox':
                echo '<input type="checkbox" name="', esc_attr($field['id']), '" id="', esc_attr($field['id']), '"', $meta ? ' checked="checked"' : '', ' />';
                break;

        }
        echo     '</td><td>',
        '</td></tr>';
    }
    echo '</table>';

}

//==========================================================================================================================================

/**
 * B3.03
 * Save data from this metabox with Custom Field for Game
 *
 * ($wpunity_databox3)
 */

function wpunity_games_databox_save($post_id) {
    global $wpunity_databox3;
    // verify nonce
    if (!wp_verify_nonce($_POST['wpunity_games_databox_nonce'], basename(__FILE__))) {
        return $post_id;
    }
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }
    foreach ($wpunity_databox3['fields'] as $field) {
        $old = get_post_meta($post_id, $field['id'], true);
        $new = $_POST[$field['id']];
        if ($new && $new != $old) {
            update_post_meta($post_id, $field['id'], $new);
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old);
        }
    }
}

add_action('save_post', 'wpunity_games_databox_save');

//================================= Compiling related =================================================================

function wpunity_games_compiler_show(){
    echo '<div id="wpunity_compileButton" onclick="wpunity_compileAjax()">Compile</div>';
    echo '<div id="wpunity_compile_report1"></div>';
    echo '<div id="wpunity_compile_report2"></div>';
    echo '<div id="wpunity_zipgame_report"></div>';

    echo '<br /><br />Analytic report of compile:<br />';
    echo '<div id="wpunity_compile_game_stdoutlog_report" style="font-size: x-small"></div>';

}

add_action( 'wp_ajax_wpunity_compile_action', 'wpunity_compile_action_callback' );
add_action( 'wp_ajax_wpunity_monitor_compiling_action', 'wpunity_monitor_compiling_action_callback' );
add_action( 'wp_ajax_wpunity_game_zip_action', 'wpunity_game_zip_action_callback' );

// compile game, i.e. make a bat file and run it
function wpunity_compile_action_callback() {

    $DS = DIRECTORY_SEPARATOR;
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

        $game_dirpath = realpath(dirname(__FILE__).'/..').$DS.'test_compiler'.$DS.'game_windows'; //$_GET['game_dirpath'];

        // 1 : Generate bat
        $myfile = fopen($game_dirpath.$DS."starter_artificial.bat", "w") or die("Unable to open file!");
        $txt = '"C:\Program Files\Unity\Editor\Unity.exe" -quit -batchmode -logFile '.$game_dirpath.'\stdout.log -projectPath '. $game_dirpath .' -buildWindowsPlayer "builds\mygame.exe"';
        fwrite($myfile, $txt);
        fclose($myfile);

        // 2: run bat
        $output = shell_exec('start /b starter_artificial.bat /c');

    } else { // LINUX SERVER

        $game_dirpath = realpath(dirname(__FILE__).'/..').$DS.'test_compiler'.$DS.'game_linux'; //$_GET['game_dirpath'];

        // 1 : Generate sh
        $myfile = fopen($game_dirpath.$DS."starter_artificial.sh", "w") or print("Unable to open file!");
        $txt = "#/bin/bash"."\n".
               "projectPath=`pwd`"."\n".
               "xvfb-run --auto-servernum --server-args='-screen 0 1024x768x24:32' /opt/Unity/Editor/Unity -batchmode -nographics -logfile stdout.log -force-opengl -quit -projectPath ${projectPath} -buildWindowsPlayer 'builds/myg3.exe'";
        fwrite($myfile, $txt);
        fclose($myfile);

        // 2: run sh (nohup     '/dev ...' ensures that it is asynchronous called)
        $output = shell_exec('nohup sh starter_artificial.sh'.'> /dev/null 2>/dev/null &');
    }

    wp_die();
}

// read compile stdout.log file and return content.
function wpunity_monitor_compiling_action_callback(){
    $DS = DIRECTORY_SEPARATOR;
    $game_dirpath = realpath(dirname(__FILE__).'/..').$DS.'test_compiler'.$DS.'game_windows';
    $fs = file_get_contents($game_dirpath.$DS."stdout.log");
    echo $fs;

    wp_die();
}

// Zip the builds folder
function wpunity_game_zip_action_callback(){

    $DS = DIRECTORY_SEPARATOR;
    $game_dirpath = realpath(dirname(__FILE__).'/..').$DS.'test_compiler'.$DS.'game_windows';

    $rootPath = realpath($game_dirpath).'/builds';
    $zip_file = realpath($game_dirpath).'/game.zip';

    // Initialize archive object
    $zip = new ZipArchive();
    $resZip = $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    if ($resZip===TRUE) {

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
        echo 'Zip successfully finished';
        wp_die();
    } else {
        echo 'Failed to zip, code:'.$resZip;
        wp_die();
    }
}


?>