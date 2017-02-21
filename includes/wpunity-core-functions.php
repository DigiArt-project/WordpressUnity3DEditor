<?php

// database method
function wpunity_fetch_scene_assets_by_db_action_callback(){ //$sceneID){

    // Output the directory listing as JSON
    header('Content-type: application/json');

    $DS = DIRECTORY_SEPARATOR;

    // if you change this, be sure to change line 440 in scriptFileBrowserToolbarWPway.js
    $dir = '..'.$DS.'wp-content'.$DS.'uploads'.$DS.$_GET['gamefolder'].$DS.$_GET['scenefolder'];

    $response = wpunity_getAllassets_byScene($_GET['sceneID']);

    for ($i=0; $i<count($response); $i++){
        $response[$i][name] =$response[$i][assetName];
        $response[$i][type] ='file';
        $response[$i][path] =$response[$i][objPath];


        $ch = curl_init($response[$i][objPath]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        $dataCurl = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        $response[$i][size] =$size;
    }

    $jsonResp =  json_encode(
            array(
                "name" => $dir,
                "type" => "folder",
                "path" => $dir,
                "items" => $response
            )
    );

    echo $jsonResp;

    wp_die();
}


// OLD DIR METHOD
function wpunity_fetch_scene_assets_by_dir_action_callback(){ //$sceneID){

    // Output the directory listing as JSON
    header('Content-type: application/json');

    $DS = DIRECTORY_SEPARATOR;
    // if you change this, be sure to change line 440 in scriptFileBrowserToolbarWPway.js
    $dir = '..'.$DS.'wp-content'.$DS.'uploads'.$DS.$_GET['gamefolder'].$DS.$_GET['scenefolder'];

    $response = scan($dir);

    $jsonResp =  json_encode(array(
                                    "name" => $dir,
                                   "type" => "folder",
                                    "path" => $dir,
                                    "items" => $response
                                   )
                            );

    echo $jsonResp;

    wp_die();
}

function scan($dir)
{
    $DS = '/'; // Do not change
    $files = array();
    // -- Dir method --
    if (file_exists($dir)) {

        foreach (scandir($dir) as $f) {

            if (!$f || $f[0] == '.') {
                continue; // Ignore hidden files
            }

            if (is_dir($dir . '/' . $f)) {
                // The path is a folder
                $files[] = array(
                    "name" => $f,
                    "type" => "folder",
                    "path" => $dir . $DS . $f,
                    "items" => scan($dir . $DS . $f) // Recursively get the contents of the folder
                );
            } else {
                // It is a file
                $files[] = array(
                    "name" => $f,
                    "type" => "file",
                    "path" => $dir . $DS . $f,
                    "size" => filesize($dir . $DS . $f) // Gets the size of this file
                );
            }
        }

    }

    return $files;
}



function wpunity_getAllassets_byScene($sceneID){

    $allAssets = [];

    $originalScene = get_post($sceneID);
    $sceneSlug = $originalScene->post_name;
    //Get 'Asset's Parent Scene' taxonomy with the same slug
    $sceneTaxonomy = get_term_by('slug', $sceneSlug, 'wpunity_asset3d_pscene');
    $sceneTaxonomyID = $sceneTaxonomy->term_id;

    $queryargs = array(
        'post_type' => 'wpunity_asset3d',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'wpunity_asset3d_pscene',
                'field' => 'id',
                'terms' => $sceneTaxonomyID
            )
        )
    );

    $custom_query = new WP_Query( $queryargs );

    if ( $custom_query->have_posts() ) :
        while ( $custom_query->have_posts() ) :

            $custom_query->the_post();
            $asset_id = get_the_ID();
            $asset_name = get_the_title();

            //ALL DATA WE NEED
            $objID = get_post_meta($asset_id, 'wpunity_asset3d_obj', true); //OBJ ID
            if($objID){$objPath = wp_get_attachment_url( $objID );} //OBJ PATH
            $mtlID = get_post_meta($asset_id, 'wpunity_asset3d_mtl', true); //MTL ID
            if($mtlID){$mtlPath = wp_get_attachment_url( $mtlID );} //MTL PATH
            $difImageID = get_post_meta($asset_id, 'wpunity_asset3d_diffimage', true); //Diffusion Image ID
            if($difImageID){$difImagePath = wp_get_attachment_url( $difImageID );} //Diffusion Image PATH
            $screenImageID = get_post_meta($asset_id, 'wpunity_asset3d_screenimage', true); //Screenshot Image ID
            if($screenImageID){$screenImagePath = wp_get_attachment_url( $screenImageID );} //Screenshot Image PATH

            $categoryAsset = wp_get_post_terms($asset_id, 'wpunity_asset3d_cat');

            $allAssets[] = ['assetName'=>$asset_name,
                            'assetSlug'=>get_post()->post_name,
                            'assetID'=>$asset_id,
                            'categoryName'=>$categoryAsset[0]->name,
                            'categoryID'=>$categoryAsset[0]->term_id,
                            'objID'=>$objID,
                            'objPath'=>$objPath,
                            'mtlID'=>$mtlID,
                            'diffImageID'=>$difImageID,
                            'diffImage'=>$difImagePath,
                            'screenImageID'=>$screenImageID,
                            'screenImagePath'=>$screenImagePath,
                            'mtlPath'=>$mtlPath];

        endwhile;
    endif;

    // Reset postdata
    wp_reset_postdata();

    return $allAssets;

}

function wpunity_getAllscenes_unityfiles_byGame($gameID){

    $allUnityScenes = [];

    $originalGame = get_post($gameID);
    $gameSlug = $originalGame->post_name;
    //Get 'Asset's Parent Scene' taxonomy with the same slug
    $gameTaxonomy = get_term_by('slug', $gameSlug, 'wpunity_asset3d_pgame');
    $gameTaxonomyID = $gameTaxonomy->term_id;

    $queryargs = array(
        'post_type' => 'wpunity_scene',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'wpunity_asset3d_pgame',
                'field' => 'id',
                'terms' => $gameTaxonomyID
            )
        )
    );

    $custom_query = new WP_Query( $queryargs );

    if ( $custom_query->have_posts() ) :
        while ( $custom_query->have_posts() ) :

            $custom_query->the_post();
            //$scene_id = get_the_ID();
            //$scene_name = get_the_title();

            $sceneSlug = the_post()->post_name;
            $sceneUnityFile = $sceneSlug . '.unity';
            $upload = wp_upload_dir();
            $upload_dir = $upload['basedir'];
            $upload_dir .= "/" . $gameSlug . "/" . $sceneUnityFile;
            $unityFile_dir = str_replace('\\','/',$upload_dir);

            $allUnityScenes[] = ['sceneUnityPath'=>$unityFile_dir];


        endwhile;
    endif;

    // Reset postdata
    wp_reset_postdata();

    return $allUnityScenes;

}




function wpunity_getTemplateID_forAsset($asset_id){

    $parentSceneterms = wp_get_post_terms( $asset_id, 'wpunity_asset3d_pscene');

    $parentSceneSlug = $parentSceneterms[0]->slug;
    $custom_args = array(
        'name'        => $parentSceneSlug,
        'post_type'   => 'wpunity_scene',
    );
    $my_posts = get_posts($custom_args);
    $sceneID = $my_posts[0]->ID;


    $parentGameterms = wp_get_post_terms( $sceneID, 'wpunity_scene_pgame');
    $gameSlug = $parentGameterms[0]->slug;
    $custom_args = array(
        'name'        => $gameSlug,
        'post_type'   => 'wpunity_game',
    );
    $my_posts = get_posts($custom_args);
    $gameID = $my_posts[0]->ID;

    $parentTempterms = wp_get_post_terms( $sceneID, 'wpunity_game_cat');
    $tempSlug = $parentTempterms[0]->slug;
    $custom_args = array(
        'name'        => $tempSlug,
        'post_type'   => 'wpunity_yamltemp',
    );
    $my_posts = get_posts($custom_args);
    $tempID = $my_posts[0]->ID;

    return $tempID;
}

//==========================================================================================================================================

function wpunity_disable_imgthumbs_assets( $image_sizes ){

    // extra sizes
    $slider_image_sizes = array(  );
    // for ex: $slider_image_sizes = array( 'thumbnail', 'medium' );

    // instead of unset sizes, return your custom size (nothing)
    if( isset($_REQUEST['post_id']) && 'wpunity_asset3d' === get_post_type( $_REQUEST['post_id'] ) )
        return $slider_image_sizes;

    return $image_sizes;
}

add_filter( 'intermediate_image_sizes', 'wpunity_disable_imgthumbs_assets', 999 );

//==========================================================================================================================================

function force_post_title_init(){
    wp_enqueue_script('jquery');
}

function force_post_title(){
    global $post;
    $post_type = get_post_type($post->ID);
    if($post_type == 'wpunity_asset3d' || $post_type == 'wpunity_scene' || $post_type == 'wpunity_game' || $post_type == 'wpunity_yamltemp') {
        echo "<script type='text/javascript'>\n";
        echo "
            jQuery('#publish').click(function(){
            var testervar = jQuery('[id^=\"titlediv\"]')
            .find('#title');
            if (testervar.val().length < 1)
            {
                jQuery('[id^=\"titlediv\"]').css('background', '#F96');
                setTimeout(\"jQuery('#ajax-loading').css('visibility', 'hidden');\", 100);
                alert('TITLE is required');
                setTimeout(\"jQuery('#publish').removeClass('button-primary-disabled');\", 100);
                return false;
            }
  	    });
        ";
        echo "</script>\n";
    }
}

add_action('admin_init', 'force_post_title_init');
add_action('edit_form_advanced', 'force_post_title');

//==========================================================================================================================================

function wpunity_change_publish_button( $translation, $text ) {
    global $post;
    $post_type = get_post_type($post->ID);
    if($post_type == 'wpunity_asset3d' || $post_type == 'wpunity_scene' || $post_type == 'wpunity_game' || $post_type == 'wpunity_yamltemp') {
        if ($text == 'Publish')
            return 'Create';
        if ($text == 'Update')
            return 'Save';
    }

    return $translation;
}

add_filter( 'gettext', 'wpunity_change_publish_button', 10, 2 );

//==========================================================================================================================================

function wpunity_upload_dir_forAssets( $args ) {

    // Get the current post_id
    $id = ( isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : '' );

    if( $id ) {

        $pathofPost = get_post_meta($id,'wpunity_asset3d_pathData',true);
        // Set the new path depends on current post_type
        $newdir = '/' . $pathofPost;

        $args['path']    = str_replace( $args['subdir'], '', $args['path'] ); //remove default subdir
        $args['url']     = str_replace( $args['subdir'], '', $args['url'] );
        $args['subdir']  = $newdir;
        $args['path']   .= $newdir;
        $args['url']    .= $newdir;
    }
    return $args;
}

add_filter( 'upload_dir', 'wpunity_upload_dir_forAssets' );

//==========================================================================================================================================

function wpunity_aftertitle_info($post) {

    $post_type = get_post_type($post->ID);
    if($post_type == 'wpunity_game'){
        $gameSlug = $post->post_name;
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $gameSlug;
        $upload_dir = str_replace('\\','/',$upload_dir);
        echo '<b>Slug:</b> ' . $gameSlug;
        echo '<br/><b>Upload Folder:</b>' . $upload_dir;
    }
    elseif($post_type == 'wpunity_scene'){
        $sceneSlug = $post->post_name;
        $terms = wp_get_post_terms( $post->ID, 'wpunity_scene_pgame');
        $gameSlug = $terms[0]->slug;
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $gameSlug . "/" . $sceneSlug;
        $upload_dir = str_replace('\\','/',$upload_dir);
        echo '<b>Slug:</b> ' . $sceneSlug;
        echo '<br/><b>Upload Folder:</b>' . $upload_dir;
    }
    elseif($post_type == 'wpunity_asset3d'){
        $assetSlug = $post->post_name;
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $pathofPost = get_post_meta($post->ID,'wpunity_asset3d_pathData',true);
        $upload_dir .= "/" . $pathofPost;
        $upload_dir = str_replace('\\','/',$upload_dir);
        echo '<b>Slug:</b> ' . $assetSlug;
        echo '<br/><b>Upload Folder:</b>' . $upload_dir;
    }

}


add_action( 'edit_form_after_title', 'wpunity_aftertitle_info' );

//==========================================================================================================================================

/**
 * 1.01
 * Overwrite Uploads
 *
 * Upload files with the same namew, without uploading copy with unique filename
 *
 */

function wpunity_overwrite_uploads( $name ){
    $args = array(
        'numberposts'   => -1,
        'post_type'     => 'attachment',
        'meta_query' => array(
            array(
                'key' => '_wp_attached_file',
                'value' => $name,
                'compare' => 'LIKE'
            )
        )
    );
    $attachments_to_remove = get_posts( $args );

    foreach( $attachments_to_remove as $attach )
        wp_delete_attachment( $attach->ID, true );

    return $name;
}

add_filter( 'sanitize_file_name', 'wpunity_overwrite_uploads', 10, 1 );

//==========================================================================================================================================

// ---- AJAX COMPILE 1: compile game, i.e. make a bat file and run it
function wpunity_compile_action_callback() {

    $DS = DIRECTORY_SEPARATOR;
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

        // TEST PHASE
        //$game_dirpath = realpath(dirname(__FILE__).'/..').$DS.'test_compiler'.$DS.'game_windows'; //$_GET['game_dirpath'];

        // REAL
        $game_dirpath = $_POST['dirpath']; //  realpath(dirname(__FILE__).'/..').$DS.'games_assemble'.$DS.'dune';

        // 1 : Generate bat
        $myfile = fopen($game_dirpath.$DS."starter_artificial.bat", "w") or die("Unable to open file!");
        $txt = '"C:\Program Files\Unity\Editor\Unity.exe" -quit -batchmode -logFile '.$game_dirpath.'\stdout.log -projectPath '. $game_dirpath .' -buildWindowsPlayer "builds\mygame.exe"';
        fwrite($myfile, $txt);
        fclose($myfile);

        // 2: run bat
        $output = shell_exec('start /b '.$game_dirpath.$DS.'starter_artificial.bat /c');




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

//---- AJAX COMPILE 2: read compile stdout.log file and return content.
function wpunity_monitor_compiling_action_callback(){
    $DS = DIRECTORY_SEPARATOR;

    // TEST
    //$game_dirpath = realpath(dirname(__FILE__).'/..').$DS.'test_compiler'.$DS.'game_windows';

    // Real
    $game_dirpath = $_POST['dirpath']; //realpath(dirname(__FILE__).'/..').$DS.'games_assemble'.$DS.'dune';

    $fs = file_get_contents($game_dirpath.$DS."stdout.log");

    echo $fs;

    wp_die();
}

//---- AJAX COMPILE 3: Zip the builds folder
function wpunity_game_zip_action_callback(){

    $DS = DIRECTORY_SEPARATOR;

    // TEST
    //$game_dirpath = realpath(dirname(__FILE__).'/..').$DS.'test_compiler'.$DS.'game_windows';

    // Real
    $game_dirpath = $_POST['dirpath']; //realpath(dirname(__FILE__).'/..').$DS.'games_assemble'.$DS.'dune';

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

function wpunity_fetch_description_action_callback(){

    if ($_POST['externalSource']=='Wikipedia'){
        $url = 'https://'.$_POST['lang'].'.wikipedia.org/w/api.php?action=query&format=json&exlimit=3&prop=extracts&'.$_POST['fulltext'].'titles='.$_POST['titles'];
    } else {
        $url = 'https://www.europeana.eu/api/v2/search.json?wskey=8mfU6ZgfW&query='.$_POST['titles'];//.'&qf=LANGUAGE:'.$_POST['lang'];
    }

    $content = file_get_contents($url);
    echo $content;

    wp_die();
}


function wpunity_fetch_image_action_callback(){

    if ($_POST['externalSource_image']=='Wikipedia'){
        $url = 'https://'.$_POST['lang_image'].'.wikipedia.org/w/api.php?action=query&prop=imageinfo&format=json&iiprop=url&generator=images&titles='.$_POST['titles_image'];
    } else {
        $url = 'https://www.europeana.eu/api/v2/search.json?wskey=8mfU6ZgfW&query='.$_POST['titles_image'];//.'&qf=LANGUAGE:'.$_POST['lang_image'];
    }


    $content = file_get_contents($url);
    echo $content;

    wp_die();
}


function wpunity_fetch_video_action_callback(){

    if ($_POST['externalSource_video']=='Wikipedia'){
        $url = 'https://'.$_POST['lang_video'].'.wikipedia.org/w/api.php?action=query&format=json&prop=videoinfo&viprop=derivatives&titles=File:'.$_POST['titles_video'].'.ogv';
    } else {
        $url = 'https://www.europeana.eu/api/v2/search.json?wskey=8mfU6ZgfW&query='.$_POST['titles_image'];//.'&qf=LANGUAGE:'.$_POST['lang_image'];
    }

    $content = file_get_contents($url);
    echo $content;

    wp_die();
}


// ---- AJAX SEMANTICS 1: run segmentation ----------
function wpunity_segment_obj_action_callback() {

    $DS = DIRECTORY_SEPARATOR;
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

        $curr_folder = wp_upload_dir()['basedir'].$DS.$_POST['path'];
        $curr_folder = str_replace('/','\\',$curr_folder); // full path

        $batfile = wp_upload_dir()['basedir'].$DS.$_POST['path']."segment.bat";


        $batfile = str_replace('/','\\',$batfile); // full path

        $fnameobj = basename($_POST['obj']);

        $fnameobj = $curr_folder.$fnameobj;


        // 1 : Generate bat
        $myfile = fopen($batfile, "w") or die("Unable to open file!");


        $outputpath = wp_upload_dir()['basedir'].$DS.$_POST['path'];
        $outputpath = str_replace('/','\\',$outputpath); // full path

        $exefile = untrailingslashit(plugin_dir_path(__FILE__)).'\..\semantics\segment3D\pclTesting.exe';
        $exefile = str_replace("/", "\\", $exefile);

        $iter = $_POST['iter'];
        $minDist = $_POST['minDist'];
        $maxDist = $_POST['maxDist'];
        $minPoints = $_POST['minPoints'];
        $maxPoints = $_POST['maxPoints'];
        //$exefile.' '.$fnameobj.' '.$iter.' 0.01 0.2 100 25000 1 '.$outputpath.PHP_EOL.

        $txt = '@echo off'.PHP_EOL.
                $exefile.' '.$fnameobj.' '.$iter.' '.$minDist.' '.$maxDist.' '.$minPoints.' '.$maxPoints.' 1 '.$outputpath.PHP_EOL.
               'del "*.pcd"'.PHP_EOL.
               'del "barycenters.txt"';

        fwrite($myfile, $txt);
        fclose($myfile);

        shell_exec('del "'.$outputpath.'log.txt"');
        shell_exec('del "'.$outputpath.'cloud_cluster*.obj"');
        shell_exec('del "'.$outputpath.'cloud_plane*.obj"');

        // 2: run bat
        $output = shell_exec($batfile);
        echo $output;

    } else { // LINUX SERVER // TODO

//        $game_dirpath = realpath(dirname(__FILE__).'/..').$DS.'test_compiler'.$DS.'game_linux'; //$_GET['game_dirpath'];
//
//        // 1 : Generate sh
//        $myfile = fopen($game_dirpath.$DS."starter_artificial.sh", "w") or print("Unable to open file!");
//        $txt = "#/bin/bash"."\n".
//            "projectPath=`pwd`"."\n".
//            "xvfb-run --auto-servernum --server-args='-screen 0 1024x768x24:32' /opt/Unity/Editor/Unity -batchmode -nographics -logfile stdout.log -force-opengl -quit -projectPath ${projectPath} -buildWindowsPlayer 'builds/myg3.exe'";
//        fwrite($myfile, $txt);
//        fclose($myfile);
//
//        // 2: run sh (nohup     '/dev ...' ensures that it is asynchronous called)
//        $output = shell_exec('nohup sh starter_artificial.sh'.'> /dev/null 2>/dev/null &');
    }

    wp_die();
}

//---- AJAX COMPILE 2: read compile stdout.log file and return content.
function wpunity_monitor_segment_obj_action_callback(){

    $DS = DIRECTORY_SEPARATOR;

    $fs = file_get_contents(pathinfo($_POST['obj'], PATHINFO_DIRNAME ).'/log.txt');
    echo $fs;

    wp_die();
}


//---- AJAX COMPILE 3: Enlist the split objs -------------
function wpunity_enlist_splitted_objs_action_callback(){

    $DS = DIRECTORY_SEPARATOR;
    $path = wp_upload_dir()['basedir'].$DS.$_POST['path'];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file)
    {
        // Skip directories (they would be added automatically)
        if (!$file->isDir() and pathinfo($file,PATHINFO_EXTENSION)=='obj')
        {

            echo "<a href='".wp_upload_dir()['baseurl']."/".$_POST['path'].basename($file)."' >".basename($file)."</a><br />";
        }
    }

    wp_die();
}



// ---- AJAX ASSEMBLE 1: Assemble game
function wpunity_assemble_action_callback() {

    $DS = DIRECTORY_SEPARATOR;

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

//        echo $_POST['source'];
//        echo $_POST['target'];

        // Check if target folder exist from a previous assemble
        $target_exists = file_exists ( $_POST['target'] );
        echo '1. Target Folder exists? '.($target_exists?'true':'false');

        // if exists then remove the whole game target folder
        if ($target_exists) {
            $res_del = shell_exec('rmdir ' . $_POST['target'] . ' /s /q');
            echo '<br />2. Delete target folder: '. (file_exists($_POST['target']) ? 'Error 4' : 'Success');
        }

        shell_exec('mkdir ' . $_POST['target']);
        echo '<br />3. Create target folder: '.(file_exists ( $_POST['target'] )?'Success':'Error 5');

        // Copy the pre-written windows game libraries
        $xcopy_command = 'xcopy /s /Q '.$_POST['game_libraries_path'].$DS.'\windows '.$_POST['target'];
        $res_copy = shell_exec($xcopy_command);

        echo '<br />4. Copy unity3d libraries: '.$res_copy;

        //------ Modify /ProjectSettings/EditorBuildSettings.asset to include all scenes ---
        // replace
        $needle_str ='  m_Scenes: []'.chr(10);
        // with
        $target_str= '  m_Scenes:'.chr(10).
                     '  - enabled: 1'.chr(10).
                     '    path: Assets/S4/S4.unity'.chr(10);

        //  Possible bug is the LF character in the end of lines
        echo '<br />5. Include Scenes in EditorBuildSettings.asset: ';

        $path_eba = $_POST['target']."/ProjectSettings/EditorBuildSettings.asset";

        // first read
        $fhandle = fopen($path_eba, "r");
        $fcontents = fread($fhandle, filesize($path_eba));
        fclose($fhandle);

        // then write
        $fhandle = fopen($path_eba, "w");
        $fcontents = str_replace($needle_str, $target_str, $fcontents);
        fwrite($fhandle, $fcontents);
        fclose($fhandle);

        echo '<pre style="font-size:8pt">'.$fcontents.'</pre>';


        // Copy source assets to target assets
        $xcopy_assets_command = 'xcopy /s /Q '.$_POST['source'].' '.$_POST['target'].$DS.'\Assets';
        $res_copy_assets = shell_exec($xcopy_assets_command);

        echo '<br />6. Copy Game Instance Assets to target Assets: '.$res_copy_assets;

        // Game Scene not ready yet : take S4 from test_scene/




    } else { // LINUX SERVER

//        $game_dirpath = realpath(dirname(__FILE__).'/..').$DS.'test_compiler'.$DS.'game_linux'; //$_GET['game_dirpath'];
//
//        // 1 : Generate sh
//        $myfile = fopen($game_dirpath.$DS."starter_artificial.sh", "w") or print("Unable to open file!");
//        $txt = "#/bin/bash"."\n".
//            "projectPath=`pwd`"."\n".
//            "xvfb-run --auto-servernum --server-args='-screen 0 1024x768x24:32' /opt/Unity/Editor/Unity -batchmode -nographics -logfile stdout.log -force-opengl -quit -projectPath ${projectPath} -buildWindowsPlayer 'builds/myg3.exe'";
//        fwrite($myfile, $txt);
//        fclose($myfile);
//
//        // 2: run sh (nohup     '/dev ...' ensures that it is asynchronous called)
//        $output = shell_exec('nohup sh starter_artificial.sh'.'> /dev/null 2>/dev/null &');
    }

    echo '<br /><br /> Finished assemble';

    wp_die();
}