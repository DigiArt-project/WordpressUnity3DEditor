<?php

function wpunity_create_folder_withmeta($folderType,$currentSlug,$currentID,$parentSlug,$parentID){

    if($folderType == 'scene'){
        //FORMAT: uploads / slug Game / slug Scene / slug Category of Asset (standard) + metas
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $parentSlug;   $file_dir = $upload_dir;//save this for asset folder's meta
        $upload_dir .= "/" . $currentSlug;

        $upload_dir = str_replace('\\','/',$upload_dir);

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755);
        }

        $templatePart = wpunity_getFolderMetaPattern();

        $file_dir = str_replace('\\','/',$file_dir);
        $file_dir .= '/' . $currentSlug .'.meta';//path and 'folder_name'.meta

        $create_file = fopen($file_dir, "w") or die("Unable to open file!");

        $myfile_text = wpunity_replace_foldermeta($templatePart,$currentID);
        fwrite($create_file, $myfile_text);
        fclose($create_file);

        wpunity_create_subfolders_withmeta($currentID,$upload_dir,$templatePart);

    }elseif($folderType == 'scene-nosub'){
        //FORMAT: uploads / slug Game / slug Scene + meta (no subfolders)
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $parentSlug;   $file_dir = $upload_dir;//save this for asset folder's meta
        $upload_dir .= "/" . $currentSlug;

        $upload_dir = str_replace('\\','/',$upload_dir);

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755);
        }

        $templatePart = wpunity_getFolderMetaPattern();

        $file_dir = str_replace('\\','/',$file_dir);
        $file_dir .= '/' . $currentSlug .'.meta';//path and 'folder_name'.meta

        $create_file = fopen($file_dir, "w") or die("Unable to open file!");

        $myfile_text = wpunity_replace_foldermeta($templatePart,$currentID);
        fwrite($create_file, $myfile_text);
        fclose($create_file);
    }elseif($folderType == 'game'){
        //FORMAT: uploads / slug Game !without meta (only the folder)
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $parentSlug;

        $upload_dir = str_replace('\\','/',$upload_dir);

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755);
        }
    }elseif($folderType == 'asset'){
        //FORMAT: uploads / slug Game / slug Scene / slug Category of Asset (standard) / slug Asset
        //get (all) the custom post type with Taxonomy's 'equal' slug (Scene)
        $custom_args = array(
            'name'        => $parentSlug,
            'post_type'   => 'wpunity_scene',
        );
        $my_posts = get_posts($custom_args);
        $sceneID = $my_posts[0]->ID;

        //slug Game (first taxonomy item)
        $terms = wp_get_post_terms( $sceneID, 'wpunity_scene_pgame');
        $gameID = $terms[0]->slug;

        //Category of Asset (standard)
        $categoryAssetID = intval($_POST['wpunity_asset3d_cat'], 10);
        $categoryAssetSlug = ( $categoryAssetID > 0 ) ? get_term( $categoryAssetID, 'wpunity_asset3d_cat' )->slug : NULL;

        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $gameID;
        $upload_dir .= "/" . $parentSlug;
        $upload_dir .= "/" . $categoryAssetSlug; $file_dir = $upload_dir;//save this for asset folder's meta
        $upload_dir .= "/" . $currentSlug;

        $upload_dir = str_replace('\\','/',$upload_dir);

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755);
        }


        $templatePart = wpunity_getFolderMetaPattern();

        //create folder.meta for Asset-folder (meta file has same path as folder)
        $file_dir = str_replace('\\','/',$file_dir);
        $file_dir .= '/' . $currentSlug .'.meta';//path and 'folder_name'.meta

        $create_file = fopen($file_dir, "w") or die("Unable to open file!");

        $myfile_text = wpunity_replace_foldermeta($templatePart,$currentID);
        fwrite($create_file, $myfile_text);
        fclose($create_file);

    }


}

function wpunity_create_subfolders_withmeta($sceneID,$upload_dir,$templatePart){

    //FORMAT: uploads / slug Game / slug Scene / slug Category of Asset (standard)
    //Create Subfolders for assets to be uploaded
    $newDir1 = $upload_dir . '/' . 'dynamic3dmodels';
    $newDir2 = $upload_dir . '/' . 'doors';
    $newDir3 = $upload_dir . '/' . 'pois_ImageText';
    $newDir4 = $upload_dir . '/' . 'pois_Video';
    $newDir5 = $upload_dir . '/' . 'static3dmodels';

    if (!is_dir($newDir1)) {mkdir($newDir1, 0755);}
    if (!is_dir($newDir2)) {mkdir($newDir2, 0755);}
    if (!is_dir($newDir3)) {mkdir($newDir3, 0755);}
    if (!is_dir($newDir4)) {mkdir($newDir4, 0755);}
    if (!is_dir($newDir5)) {mkdir($newDir5, 0755);}

    $file1_text = wpunity_replace_foldermeta($templatePart,'a'. $sceneID);
    $file2_text = wpunity_replace_foldermeta($templatePart,'b'. $sceneID);
    $file3_text = wpunity_replace_foldermeta($templatePart,'c'. $sceneID);
    $file4_text = wpunity_replace_foldermeta($templatePart,'d'. $sceneID);
    $file5_text = wpunity_replace_foldermeta($templatePart,'e'. $sceneID);


    $create_file1 = fopen($upload_dir . '/dynamic3dmodels.meta', "w") or die("Unable to open file!");
    fwrite($create_file1, $file1_text);
    fclose($create_file1);

    $create_file2 = fopen($upload_dir . '/doors.meta', "w") or die("Unable to open file!");
    fwrite($create_file2, $file2_text);
    fclose($create_file2);

    $create_file3 = fopen($upload_dir . '/pois_ImageText.meta', "w") or die("Unable to open file!");
    fwrite($create_file3, $file3_text);
    fclose($create_file3);

    $create_file4 = fopen($upload_dir . '/pois_Video.meta', "w") or die("Unable to open file!");
    fwrite($create_file4, $file4_text);
    fclose($create_file4);


    $create_file5 = fopen($upload_dir . '/static3dmodels.meta', "w") or die("Unable to open file!");
    fwrite($create_file5, $file5_text);
    fclose($create_file5);
}

function wpunity_create_unityfile_noAssets($folderType,$sceneSlug,$sceneID,$parentGameSlug,$parentGameID,$yamlTermID){

    if($folderType == 'scene'){
        //FORMAT: uploads / slug Game / slug Scene / slug-Scene.unity (plus .unity.meta file)
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $parentGameSlug;
        $upload_dir .= "/" . $sceneSlug;
        $upload_dir = str_replace('\\','/',$upload_dir);

        $unityfile_dir = $upload_dir . '/' . $sceneSlug .'.unity';//path and 'folder_name'.unity
        $unitycreate_file = fopen($unityfile_dir, "w") or die("Unable to open file!");
        $unityfile_text = wpunity_replace_unityfile($yamlTermID,$sceneID);

        fwrite($unitycreate_file, $unityfile_text);
        fclose($unitycreate_file);

        $unityMetafile_dir = $upload_dir . '/' . $sceneSlug .'.unity.meta';//path and 'folder_name'.unity.meta
        $unityMetacreate_file = fopen($unityMetafile_dir, "w") or die("Unable to open file!");
        $unityMetafile_text = wpunity_replace_unityMetafile($yamlTermID,$sceneID);
        fwrite($unityMetacreate_file, $unityMetafile_text);
        fclose($unityMetacreate_file);
    }

}

function wpunity_create_unityfile_withAssets($folderType,$sceneSlug,$sceneID,$parentGameSlug,$parentGameID,$yamlTermID,$jsonScene){

    if($folderType == 'scene'){
        //FORMAT: uploads / slug Game / slug Scene / slug-Scene.unity
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $parentGameSlug;
        $upload_dir .= "/" . $sceneSlug;
        $upload_dir = str_replace('\\','/',$upload_dir);

        $unityfile_dir = $upload_dir . '/' . $sceneSlug .'.unity';//path and 'folder_name'.unity
        unlink($unityfile_dir);//DELETE old unity file

        $unitycreate_file = fopen($unityfile_dir, "w") or die("Unable to open file!");
        $unityfile_text = wpunity_replace_unityfile_withAssets($yamlTermID,$sceneID,$jsonScene);
        fwrite($unitycreate_file, $unityfile_text);
        fclose($unitycreate_file);

        $unityMetafile_dir = $upload_dir . '/' . $sceneSlug .'.unity.meta';//path and 'folder_name'.unity.meta
        unlink($unityMetafile_dir);//DELETE old unity.meta file

        $unityMetacreate_file = fopen($unityMetafile_dir, "w") or die("Unable to open file!");
        $unityMetaPattern = wpunity_getSceneUnityMetaPattern();
        $unityMetafile_text = wpunity_replace_unityMetafile_withAssets($sceneID,$unityMetaPattern);
        fwrite($unityMetacreate_file, $unityMetafile_text);
        fclose($unityMetacreate_file);
    }elseif($folderType == 'scene-mainmenu'){
        //FORMAT: uploads / slug Game / slug Scene / slug-Scene.unity
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $parentGameSlug;
        $upload_dir .= "/" . $sceneSlug;
        $upload_dir = str_replace('\\','/',$upload_dir);

        $unityfile_dir = $upload_dir . '/' . $sceneSlug .'.unity';//path and 'folder_name'.unity
        unlink($unityfile_dir);//DELETE old unity file

        $unitycreate_file = fopen($unityfile_dir, "w") or die("Unable to open file!");
        $unityfile_text = wpunity_getYaml_main_menu_unity_pattern($yamlTermID);//Get 'The S_MainMenu.unity pattern' by Yaml ID
        //$unityfile_text = wpunity_replace_unityfile_withAssets($yamlTermID,$sceneID,$jsonScene);
        fwrite($unitycreate_file, $unityfile_text);
        fclose($unitycreate_file);

        $unityMetafile_dir = $upload_dir . '/' . $sceneSlug .'.unity.meta';//path and 'folder_name'.unity.meta
        unlink($unityMetafile_dir);//DELETE old unity.meta file

        $unityMetacreate_file = fopen($unityMetafile_dir, "w") or die("Unable to open file!");
        $unityMetaPattern = wpunity_getSceneUnityMetaPattern();
        $unityMetafile_text = wpunity_replace_unityMetafile_withAssets($sceneID,$unityMetaPattern);
        fwrite($unityMetacreate_file, $unityMetafile_text);
        fclose($unityMetacreate_file);
    }elseif($folderType == 'scene-options'){
        //FORMAT: uploads / slug Game / slug Scene / slug-Scene.unity
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $parentGameSlug;
        $upload_dir .= "/" . $sceneSlug;
        $upload_dir = str_replace('\\','/',$upload_dir);

        $unityfile_dir = $upload_dir . '/' . $sceneSlug .'.unity';//path and 'folder_name'.unity
        unlink($unityfile_dir);//DELETE old unity file

        $unitycreate_file = fopen($unityfile_dir, "w") or die("Unable to open file!");
        $unityfile_text = wpunity_getYaml_options_unity_pattern($yamlTermID);//Get 'The S_Options.unity pattern' by Yaml ID
        //$unityfile_text = wpunity_replace_unityfile_withAssets($yamlTermID,$sceneID,$jsonScene);
        fwrite($unitycreate_file, $unityfile_text);
        fclose($unitycreate_file);

        $unityMetafile_dir = $upload_dir . '/' . $sceneSlug .'.unity.meta';//path and 'folder_name'.unity.meta
        unlink($unityMetafile_dir);//DELETE old unity.meta file

        $unityMetacreate_file = fopen($unityMetafile_dir, "w") or die("Unable to open file!");
        $unityMetaPattern = wpunity_getSceneUnityMetaPattern();
        $unityMetafile_text = wpunity_replace_unityMetafile_withAssets($sceneID,$unityMetaPattern);
        fwrite($unityMetacreate_file, $unityMetafile_text);
        fclose($unityMetacreate_file);
    }elseif($folderType == 'scene-credentials'){
        //FORMAT: uploads / slug Game / slug Scene / slug-Scene.unity
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir .= "/" . $parentGameSlug;
        $upload_dir .= "/" . $sceneSlug;
        $upload_dir = str_replace('\\','/',$upload_dir);

        $unityfile_dir = $upload_dir . '/' . $sceneSlug .'.unity';//path and 'folder_name'.unity
        unlink($unityfile_dir);//DELETE old unity file

        $unitycreate_file = fopen($unityfile_dir, "w") or die("Unable to open file!");
        $unityfile_text = wpunity_getYaml_credentials_unity_pattern($yamlTermID);//Get 'The S_Credentials.unity pattern' by Yaml ID
        //$unityfile_text = wpunity_replace_unityfile_withAssets($yamlTermID,$sceneID,$jsonScene);
        fwrite($unitycreate_file, $unityfile_text);
        fclose($unitycreate_file);

        $unityMetafile_dir = $upload_dir . '/' . $sceneSlug .'.unity.meta';//path and 'folder_name'.unity.meta
        unlink($unityMetafile_dir);//DELETE old unity.meta file

        $unityMetacreate_file = fopen($unityMetafile_dir, "w") or die("Unable to open file!");
        $unityMetaPattern = wpunity_getSceneUnityMetaPattern();
        $unityMetafile_text = wpunity_replace_unityMetafile_withAssets($sceneID,$unityMetaPattern);
        fwrite($unityMetacreate_file, $unityMetafile_text);
        fclose($unityMetacreate_file);
    }

}

function wpunity_create_default_scenes_for_game($gameSlug,$gameTitle,$gameID){
    //Get
    $allScenePGame = get_term_by('slug', $gameSlug, 'wpunity_scene_pgame');
    $allScenePGameID = $allScenePGame->term_id;

    $mainmenuSceneYAML = get_term_by('slug', 'mainmenu-yaml', 'wpunity_scene_yaml'); //Yaml Tax for Main Menu
    $mainmenuSceneYAMLID = $mainmenuSceneYAML->term_id;
    $firstSceneYAML = get_term_by('slug', 'wonderaround-yaml', 'wpunity_scene_yaml'); //Yaml Tax for First Scene
    $firstSceneYAMLID = $firstSceneYAML->term_id;
    $optionsSceneYAML = get_term_by('slug', 'options-yaml', 'wpunity_scene_yaml'); //Yaml Tax for Options Scene
    $optionsSceneYAMLID = $optionsSceneYAML->term_id;
    $credentialsSceneYAML = get_term_by('slug', 'credentials-yaml', 'wpunity_scene_yaml'); //Yaml Tax for Credentials Scene
    $credentialsSceneYAMLID = $credentialsSceneYAML->term_id;


    $mainmenuSceneTitle = 'Main Menu for ' . $gameTitle; //Title for Main Menu
    $mainmenuSceneSlug = 's_mainmenu_' . $gameSlug; //Slug for Main Menu
    $firstSceneTitle = 'First Scene for ' . $gameTitle; //Title for First Menu
    $firstSceneSlug = 's1_' . $gameSlug; //Slug for First Menu
    $optionsSceneTitle = 'Options Scene for ' . $gameTitle; //Title for Options Menu
    $optionsSceneSlug = 's_options_' . $gameSlug; //Slug for Options Menu
    $credentialsSceneTitle = 'Credentials Scene for ' . $gameTitle; //Title for Credentials Menu
    $credentialsSceneSlug = 's_credentials_' . $gameSlug; //Slug for Credentials Menu

    // Create Main Menu Scene Data
    $mainmenuSceneData = array(
        'post_title'    => $mainmenuSceneTitle,
        'post_name' => $mainmenuSceneSlug,
        'post_type' => 'wpunity_scene',
        'post_status'   => 'publish',
        'tax_input'    => array(
            'wpunity_scene_pgame'     => array( $allScenePGameID ),
            'wpunity_scene_yaml'     => array( $mainmenuSceneYAMLID ),
        ),
    );

    // Create First Scene Data
    $firstSceneData = array(
        'post_title'    => $firstSceneTitle,
        'post_name' => $firstSceneSlug,
        'post_type' => 'wpunity_scene',
        'post_status'   => 'publish',
        'tax_input'    => array(
            'wpunity_scene_pgame'     => array( $allScenePGameID ),
            'wpunity_scene_yaml'     => array( $firstSceneYAMLID ),
        ),
    );

    // Create Options Scene Data
    $optionsSceneData = array(
        'post_title'    => $optionsSceneTitle,
        'post_name' => $optionsSceneSlug,
        'post_type' => 'wpunity_scene',
        'post_status'   => 'publish',
        'tax_input'    => array(
            'wpunity_scene_pgame'     => array( $allScenePGameID ),
            'wpunity_scene_yaml'     => array( $optionsSceneYAMLID ),
        ),
    );

    // Create Credentials Scene Data
    $credentialsSceneData = array(
        'post_title'    => $credentialsSceneTitle,
        'post_name' => $credentialsSceneSlug,
        'post_type' => 'wpunity_scene',
        'post_status'   => 'publish',
        'tax_input'    => array(
            'wpunity_scene_pgame'     => array( $allScenePGameID ),
            'wpunity_scene_yaml'     => array( $credentialsSceneYAMLID ),
        ),
    );

    // Insert posts 1-1 into the database with subfolders and files needed
    $mainmenuSceneID = wp_insert_post( $mainmenuSceneData );
    wp_insert_term($mainmenuSceneTitle,'wpunity_asset3d_pscene',array('slug'=>$mainmenuSceneSlug,'description'=>'Scene assignment of Asset 3D'));
    wpunity_create_folder_withmeta('scene-nosub',$mainmenuSceneSlug,$mainmenuSceneID,$gameSlug,$gameID);
    //Create .unity file for the "Scene" (Main Menu)
    wpunity_create_unityfile_withAssets('scene-mainmenu',$mainmenuSceneSlug,$mainmenuSceneID,$gameSlug,$gameID,$mainmenuSceneYAMLID,'');
    //Create a parent scene tax category for the assets3d


    $firstSceneID = wp_insert_post( $firstSceneData );
    wp_insert_term($firstSceneTitle,'wpunity_asset3d_pscene',array('slug'=>$firstSceneSlug,'description'=>'Scene assignment of Asset 3D'));
    wpunity_create_folder_withmeta('scene',$firstSceneSlug,$firstSceneID,$gameSlug,$gameID);
    //Create .unity file for the "Scene" (First Scene)
    wpunity_create_unityfile_noAssets('scene',$firstSceneSlug,$firstSceneID,$gameSlug,$gameID,$firstSceneYAMLID);
    //Create a parent scene tax category for the assets3d


    $optionsSceneID = wp_insert_post( $optionsSceneData );
    wp_insert_term($optionsSceneTitle,'wpunity_asset3d_pscene',array('slug'=>$optionsSceneSlug,'description'=>'Scene assignment of Asset 3D'));
    wpunity_create_folder_withmeta('scene-nosub',$optionsSceneSlug,$optionsSceneID,$gameSlug,$gameID);
    //Create .unity file for the "Scene" (Options Scene)
    wpunity_create_unityfile_withAssets('scene-options',$optionsSceneSlug,$optionsSceneID,$gameSlug,$gameID,$optionsSceneYAMLID,'');
    //Create a parent scene tax category for the assets3d


    $credentialsSceneID = wp_insert_post( $credentialsSceneData );
    wp_insert_term($credentialsSceneTitle,'wpunity_asset3d_pscene',array('slug'=>$credentialsSceneSlug,'description'=>'Scene assignment of Asset 3D'));
    wpunity_create_folder_withmeta('scene-nosub',$credentialsSceneSlug,$credentialsSceneID,$gameSlug,$gameID);
    //Create .unity file for the "Scene" (Main Menu)
    wpunity_create_unityfile_withAssets('scene-credentials',$credentialsSceneSlug,$credentialsSceneID,$gameSlug,$gameID,$credentialsSceneYAMLID,'');
    //Create a parent scene tax category for the assets3d

}

function wpunity_replace_unityfile_withAssets( $yamlID, $sceneID, $jsonScene ){

    $tempFirstPersonPart = wpunity_getYaml_wonder_around_unity_pattern($yamlID);
    $templatePart_sop = wpunity_getYaml_static_object_pattern($yamlID);

    $unity_file_contents = "";

    $sceneJsonARR = json_decode($jsonScene, TRUE);//->objects->floor_1487753970

    $curr_fid = 30;

    //if ($sceneJsonARR['objects']) {}
    foreach ($sceneJsonARR['objects'] as $key => $value ) {

        if ($key == 'avatarYawObject') {

            $curr_fid++;

            // Change avatar position and rotation
            $unity_file_contents .= str_replace([
                '___[player_name]___',
                '___[player_fid]___',
                '___[player_position_x]___',
                '___[player_position_y]___',
                '___[player_position_z]___',
                '___[player_rotation_x]___',
                '___[player_rotation_y]___',
                '___[player_rotation_z]___'
            ],
                [
                    'Player',
                    $curr_fid,
                    $value['position'][0],
                    $value['position'][1],
                    $value['position'][2],
                    $value['rotation'][0],
                    $value['rotation'][1],
                    $value['rotation'][2]
                ],
                $tempFirstPersonPart);
        } else {

            if ($value['categoryName'] == 'Static 3D models'){

                $unity_file_contents .= str_replace(
                    [
                        "___[sop_name]___",
                        "___[sop_fid]___", // +1
                        "___[sop_prefab_fid]___", // +1
                        "___[sop_meshcol_fid]___", // +1
                        "___[sop_guid]___", // from obj meta
                        "___[sop_material_guid]___", // from mat meta
                        "___[sop_pos_x]___",
                        "___[sop_pos_y]___",
                        "___[sop_pos_z]___",
                        "___[sop_rot_x]___",
                        "___[sop_rot_y]___",
                        "___[sop_rot_z]___",
                        "___[sop_scale_x]___",
                        "___[sop_scale_y]___",
                        "___[sop_scale_z]___"],
                    [
                        $key,
                        $curr_fid++,
                        $curr_fid++,
                        $curr_fid++,
                        wpunity_create_guids('obj', $value['fnObjID']),
                        wpunity_create_guids('mat', $value['fnMtlID']), // ToDO: here we need the fnMatID // ToDO: We need to support multiple mat
                        //rotation
                        $value['position'][0], $value['position'][1], $value['position'][2],
                        // position
                        $value['rotation'][0], $value['rotation'][1], $value['rotation'][2],
                        // scale
                        $value['scale'][0]   , $value['scale'][1]   , $value['scale'][2]
                    ]
                    , $templatePart_sop);

            } else if ($value['categoryName'] == 'Points of Interest (Image-Text)'){


            } else if ($value['categoryName'] == 'Points of Interest (Video)'){


            } else if ($value['categoryName'] == 'Dynamic 3D models'){


            } else if ($value['categoryName'] == 'Doors'){

            }


        }
    }


    return $unity_file_contents;
}

function wpunity_replace_unityfile($templateID,$sceneID){

    echo "-------------------------------------->4";

    $tempOcclusionPart = get_post_meta( $templateID, 'wpunity_yamltemp_scene_ocs', true );
    $tempRenderPart = get_post_meta( $templateID, 'wpunity_yamltemp_scene_rs', true );
    $tempLightMapPart = get_post_meta( $templateID, 'wpunity_yamltemp_scene_lms', true );
    $tempNavMeshPart = get_post_meta( $templateID, 'wpunity_yamltemp_scene_nms', true );
    $tempLightPart = get_post_meta( $templateID, 'wpunity_yamltemp_scene_light', true );

    $unity_file_contents = $tempOcclusionPart . $tempRenderPart . $tempLightMapPart . $tempNavMeshPart . $tempLightPart;
    return $unity_file_contents;

}

function wpunity_replace_unityMetafile($templateID,$sceneID){

    return '';

}

function wpunity_replace_unityMetafile_withAssets( $sceneID,$unityMetaPattern ){
    $unix_time = time();
    $guid_id = wpunity_create_guids('unity',$sceneID);

    $file_content_return = str_replace("___[scene_unity_guid]___",$guid_id,$unityMetaPattern);
    $file_content_return = str_replace("___[unx_time_created]___",$unix_time,$file_content_return);

    return $file_content_return;
}

//==========================================================================================================================================

// 32 chars Hex (identifier for the resource)
function wpunity_create_guids($objTypeSTR, $objID, $extra_id_material=null){

    switch ($objTypeSTR) {
        case 'unity':  $objType = "1"; break;
        case 'folder': $objType = "2"; break;
        case 'obj': $objType = "3"; break;
        case 'mat': $objType = "4".$extra_id_material; break; // an obj can have two or more mat
        case 'jpg': $objType = "5".$extra_id_material; break; // an obj can have multiple textures jpg
    }

    return str_pad($objType, 3, "0", STR_PAD_LEFT) . str_pad($objID, 29, "0", STR_PAD_LEFT);
}



// 10 chars Decimal (identifier for the GameObject) (e.g. dino1, dino2 have different fid but share the same guid)
function wpunity_create_fids($id){
    return str_pad($id, 10, "0", STR_PAD_LEFT);
}


function wpunity_replace_foldermeta($file_content,$folderID){
    $unix_time = time();
    $guid_id = wpunity_create_guids('folder',$folderID);

    $file_content_return = str_replace("___[folder_guid]___",$guid_id,$file_content);
    $file_content_return = str_replace("___[unx_time_created]___",$unix_time,$file_content_return);

    return $file_content_return;
}

function wpunity_replace_objmeta($file_content,$objID){
    $unix_time = time();
    $guid_id = wpunity_create_guids('obj',$objID);

    $file_content_return = str_replace("___[obj_guid]___",$guid_id,$file_content);
    $file_content_return = str_replace("___[unx_time_created]___",$unix_time,$file_content_return);

    return $file_content_return;
}

function wpunity_replace_jpgmeta($file_content,$objID){
    $unix_time = time();
    $guid_id = wpunity_create_guids('jpg',$objID);

    $file_content_return = str_replace("___[jpg_guid]___",$guid_id,$file_content);
    $file_content_return = str_replace("___[unx_time_created]___",$unix_time,$file_content_return);

    return $file_content_return;
}

function wpunity_replace_mat($file_content, $objID){
//    $unix_time = time();
//    $guid_id = 'c0000000000' . $objID;
//
//    $file_content_return = str_replace("___[jpg_guid]___",$guid_id,$file_content);
//    $file_content_return = str_replace("___[unx_time_created]___",$unix_time,$file_content_return);
//
//    return $file_content_return;
    return $file_content;
}

function wpunity_replace_matmeta($file_content,$objID){
//    $unix_time = time();
//    $guid_id = 'c0000000000' . $objID;
//
//    $file_content_return = str_replace("___[jpg_guid]___",$guid_id,$file_content);
//    $file_content_return = str_replace("___[unx_time_created]___",$unix_time,$file_content_return);
//
//    return $file_content_return;
    return $file_content;
}

//==========================================================================================================================================



?>