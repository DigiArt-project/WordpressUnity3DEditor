<?php
if ( get_option('permalink_structure') ) { $perma_structure = true; } else {$perma_structure = false;}
if( $perma_structure){$parameter_pass = '?wpunity_game=';} else{$parameter_pass = '&wpunity_game=';}
if( $perma_structure){$parameter_Scenepass = '?wpunity_scene=';} else {$parameter_Scenepass = '&wpunity_scene=';}
$parameter_assetpass = $perma_structure ? '?wpunity_asset=' : '&wpunity_asset=';

// Load VR_Editor Scripts
function load_vreditor_scripts()
{
    $vthreejs = 119;

    wp_enqueue_script('wpunity_load'.$vthreejs.'_threejs');
    wp_enqueue_script('wpunity_load'.$vthreejs.'_CSS2DRenderer');
    wp_enqueue_script('wpunity_load'.$vthreejs.'_CopyShader');
    wp_enqueue_script('wpunity_load'.$vthreejs.'_FXAAShader');
    wp_enqueue_script('wpunity_load'.$vthreejs.'_EffectComposer');
    wp_enqueue_script('wpunity_load'.$vthreejs.'_RenderPass');
    wp_enqueue_script('wpunity_load'.$vthreejs.'_OutlinePass');
    wp_enqueue_script('wpunity_load'.$vthreejs.'_ShaderPass');
    
    // Fixed at 87 (forked of original 87)
    wp_enqueue_script('wpunity_load87_datgui');
    wp_enqueue_script('wpunity_load87_OBJloader');
    wp_enqueue_script('wpunity_load87_MTLloader');
    wp_enqueue_script('wpunity_load87_OrbitControls');
    wp_enqueue_script('wpunity_load87_TransformControls');
    wp_enqueue_script('wpunity_load87_PointerLockControls');
    
    wp_enqueue_script('wpunity_load87_sceneexporterutils');
    wp_enqueue_script('wpunity_load87_scene_importer_utils');
    wp_enqueue_script('wpunity_load87_sceneexporter');
    
    // Colorpicker for the lights
    wp_enqueue_script('wpunity_jscolorpick');
    
    wp_enqueue_style('wpunity_vr_editor');
    wp_enqueue_style('wpunity_vr_editor_filebrowser');
}
add_action('wp_enqueue_scripts', 'load_vreditor_scripts' );


function load_custom_functions_vreditor(){
    wp_enqueue_script('wpunity_vr_editor_environmentals');
    wp_enqueue_script('wpunity_keyButtons');
    wp_enqueue_script('wpunity_rayCasters');
    wp_enqueue_script('wpunity_auxControlers');
    wp_enqueue_script('wpunity_LoaderMulti');
    wp_enqueue_script('wpunity_movePointerLocker');
    wp_enqueue_script('wpunity_addRemoveOne');
    wp_enqueue_script('wpunity_vr_editor_buttons');
    wp_enqueue_script('wpunity_vr_editor_analytics');
    
}
add_action('wp_enqueue_scripts', 'load_custom_functions_vreditor' );


$project_id    = sanitize_text_field( intval( $_GET['wpunity_game'] ) );
$project_post     = get_post($project_id);
$projectSlug = $project_post->post_name;
$project_type_obj = wpunity_return_project_type($project_id);

$current_scene_id = sanitize_text_field( intval( $_GET['wpunity_scene'] ));
$scene_post = get_post($current_scene_id);
$sceneTitle = $scene_post->post_name;

// For analytics
$project_saved_keys = wpunity_getProjectKeys($project_id, $project_scope);

// if Virtual Lab
if($project_scope === 1) {
    if (!array_key_exists('gioID', $project_saved_keys)) {
        echo "<script type='text/javascript'>alert(\"APP KEY not found." .
            " Please make sure that your user account has been registered correctly, " .
            "and you have loaded the correct page\");</script>";
    }
}

$user_data = get_userdata( get_current_user_id() );
$user_email = $user_data->user_email;


$allProjectsPage = wpunity_getEditpage('allgames');
$newAssetPage = wpunity_getEditpage('asset');
$editscenePage = wpunity_getEditpage('scene');
$editscene2DPage = wpunity_getEditpage('scene2D');
$editsceneExamPage = wpunity_getEditpage('sceneExam');

// for vr_editor
$urlforAssetEdit = esc_url( get_permalink($newAssetPage[0]->ID) . $parameter_pass . $project_id . '&wpunity_scene=' .$current_scene_id . '&wpunity_asset=' );

// Get 'parent-game' taxonomy with the same slug as Game (in order to show scenes that belong here)
$allScenePGame = get_term_by('slug', $projectSlug, 'wpunity_scene_pgame');
$allScenePGameID = $allScenePGame->term_id;

if ($project_type_obj->string === "Chemistry") {
    $analytics_molecule_checklist = wpunity_derive_molecules_checklist();
}

$upload_dir = str_replace('\\','/',wp_upload_dir()['basedir']);

// Ajax for fetching game's assets within asset browser widget at vr_editor // user must be logged in to work, otherwise ajax has no privileges
$pluginpath = str_replace('\\','/', dirname(plugin_dir_url( __DIR__  )) );

// COMPILE Ajax
if(wpunity_getUnity_local_or_remote() != 'remote') {

    // Local compile
	$gameUnityProject_dirpath = $upload_dir . '\\' . $projectSlug . 'Unity';
	$gameUnityProject_urlpath = $pluginpath . '/../../uploads/' . $projectSlug . 'Unity/';

} else {

    // Remote compile
	$ftp_cre = wpunity_get_ftpCredentials();
	$ftp_host = $ftp_cre['address'];

	$gamesFolder = 'COMPILE_UNITY3D_GAMES';

	$gameUnityProject_dirpath = $gamesFolder."/".$projectSlug."Unity";
	$gameUnityProject_urlpath = "http://".$ftp_host."/".$gamesFolder."/".$projectSlug."Unity";
}


$thepath = $pluginpath . '/js_libs/assemble_compile_commands/request_game_assepile.js';
wp_enqueue_script( 'ajax-script_assepile', $thepath, array('jquery') );
wp_localize_script( 'ajax-script_assepile', 'my_ajax_object_assepile',
	array( 'ajax_url' => admin_url( 'admin-ajax.php'),
	       'id' => $project_id,
	       'slug' => $projectSlug,
	       'gameUnityProject_dirpath' => $gameUnityProject_dirpath,
	       'gameUnityProject_urlpath' => $gameUnityProject_urlpath
	)
);

// DELETE SCENE AJAX
wp_enqueue_script( 'ajax-script_deletescene', $pluginpath . '/js_libs/delete_ajaxes/delete_scene.js', array('jquery') );
wp_localize_script( 'ajax-script_deletescene', 'my_ajax_object_deletescene',
	array( 'ajax_url' => admin_url( 'admin-ajax.php'))
);

//FOR SAVING extra keys
wp_enqueue_script( 'ajax-script_savegio', $pluginpath.'/js_libs/save_scene_ajax/wpunity_save_scene_ajax.js', array('jquery') );
wp_localize_script( 'ajax-script_savegio', 'my_ajax_object_savegio',
	array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'project_id' => $project_id )
);

// Asset Browser
wp_enqueue_script( 'ajax-script_filebrowse', $pluginpath.'/js_libs/assetBrowserToolbar.js', array('jquery') );
wp_localize_script( 'ajax-script_filebrowse', 'my_ajax_object_fbrowse', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

// Save scene
wp_enqueue_script( 'ajax-script_savescene', $pluginpath.'/js_libs/save_scene_ajax/wpunity_save_scene_ajax.js', array('jquery') );
wp_localize_script( 'ajax-script_savescene', 'my_ajax_object_savescene',
	array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'scene_id' => $current_scene_id )
);

// Delete Asset
wp_enqueue_script( 'ajax-script_deleteasset', $pluginpath.'/js_libs/delete_ajaxes/delete_asset.js', array('jquery') );
wp_localize_script( 'ajax-script_deleteasset', 'my_ajax_object_deleteasset',
	array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
);


wp_enqueue_media($scene_post->ID);
require_once(ABSPATH . "wp-admin" . '/includes/media.php');

if ($project_scope == 0) {
	$single_lowercase = "tour";
	$single_first = "Tour";
} else if ($project_scope == 1){
	$single_lowercase = "lab";
	$single_first = "Lab";
} else {
	$single_lowercase = "project";
	$single_first = "Project";
}

if(isset($_POST['submitted2']) && isset($_POST['post_nonce_field2']) && wp_verify_nonce($_POST['post_nonce_field2'], 'post_nonce')) {
	$expID = $_POST['exp-id'];
	update_post_meta( $project_id, 'wpunity_project_expID', $expID);

	$loadMainSceneLink = get_permalink($editscenePage[0]->ID) . $parameter_Scenepass . $scene_id . '&wpunity_game=' . $project_id . '&scene_type=' . 'scene';
	wp_redirect( $loadMainSceneLink );
	exit;
}


// NEW SCENE
if(isset($_POST['submitted']) && isset($_POST['post_nonce_field']) && wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')) {

	$newSceneType = $_POST['sceneTypeRadio'];

	$sceneMetaType = 'scene';//default 'scene' MetaType (3js)
	$game_type_chosen_slug = '';

	$default_json = '';
	$thegameType = wp_get_post_terms($project_id, 'wpunity_game_type');
	if($thegameType[0]->slug == 'archaeology_games'){
	    
	    $newscene_yaml_tax = get_term_by('slug', 'wonderaround-yaml', 'wpunity_scene_yaml');
	    
	    $game_type_chosen_slug = 'archaeology_games';
	    $default_json = wpunity_getDefaultJSONscene('archaeology');
	    
	} elseif($thegameType[0]->slug == 'energy_games'){
	    
	    $newscene_yaml_tax = get_term_by('slug', 'educational-energy', 'wpunity_scene_yaml');
	    $game_type_chosen_slug = 'energy_games';
	    $default_json = wpunity_getDefaultJSONscene('energy');
	
	}elseif($thegameType[0]->slug == 'chemistry_games'){
	 
		$game_type_chosen_slug = 'chemistry_games';
		
		$default_json = wpunity_getDefaultJSONscene('chemistry');
		
		if($newSceneType == 'lab'){
		
		    $newscene_yaml_tax = get_term_by('slug', 'wonderaround-lab-yaml', 'wpunity_scene_yaml');
		
		} elseif($newSceneType == '2d'){
		
		    $newscene_yaml_tax = get_term_by('slug', 'exam2d-chem-yaml', 'wpunity_scene_yaml');
		    $sceneMetaType = 'sceneExam2d';
		
		} elseif($newSceneType == '3d'){
		
		    $newscene_yaml_tax = get_term_by('slug', 'exam3d-chem-yaml', 'wpunity_scene_yaml');
		    $sceneMetaType = 'sceneExam3d';
		}
	}

	$scene_taxonomies = array(
		'wpunity_scene_pgame' => array(
			$allScenePGameID,
		),
		'wpunity_scene_yaml' => array(
			$newscene_yaml_tax->term_id,
		)
	);

	$scene_metas = array(
		'wpunity_scene_default' => 0,
        'wpunity_scene_caption' => esc_attr(strip_tags($_POST['scene-caption']))
	);

	//REGIONAL SCENE EXTRA TYPE FOR ENERGY GAMES
	$isRegional = 0;//default value
	if($thegameType[0]->slug == 'energy_games'){
		if($_POST['regionalSceneCheckbox'] == 'on'){$isRegional = 1;}
		$scene_metas['wpunity_isRegional']= $isRegional;
		$scene_metas['wpunity_scene_environment'] = 'fields';
	}

	//Add the final MetaType of the Scene
	$scene_metas['wpunity_scene_metatype']= $sceneMetaType;

	$scene_information = array(
		'post_title' => esc_attr(strip_tags($_POST['scene-title'])),
		'post_content' => $default_json,
		'post_type' => 'wpunity_scene',
		'post_status' => 'publish',
		'tax_input' => $scene_taxonomies,
		'meta_input' => $scene_metas,
	);

	$scene_id = wp_insert_post($scene_information);

	if($scene_id){
		if($sceneMetaType == 'sceneExam2d' || $sceneMetaType == 'sceneExam3d'){$edit_scene_page_id = $editsceneExamPage[0]->ID;}
		else{$edit_scene_page_id = $editscenePage[0]->ID;}
		$loadMainSceneLink = get_permalink($edit_scene_page_id) . $parameter_Scenepass . $scene_id . '&wpunity_game=' . $project_id . '&scene_type=' . $sceneMetaType;
		wp_redirect( $loadMainSceneLink );
		exit;
	}
}

$goBackTo_AllProjects_link = esc_url( get_permalink($allProjectsPage[0]->ID));

get_header(); ?>

    <style>
        .panel { display: none; }
        .panel.active { display: block; }
        .navigation-top {display:none;}
        .mdc-tab { min-width: 0; }
        .custom-header { display:none; }
        .main-navigation a { padding: 0.2em 1em; font-size:9pt !important;}
        .site-branding {display:none;}
        #content {padding:0px;}
        
        
    </style>

<?php if ( !is_user_logged_in() ) { ?>

    <div class="DisplayBlock CenterContents">
        <i style="font-size: 64px; padding-top: 80px;" class="material-icons mdc-theme--text-icon-on-background">account_circle</i>
        <p class="mdc-typography--title"> Please <a class="mdc-theme--secondary" href="<?php echo wp_login_url( get_permalink() ); ?>">login</a> to use platform</p>
        <p class="mdc-typography--title"> Or <a class="mdc-theme--secondary" href="<?php echo wp_registration_url(); ?>">register</a> if you don't have an account</p>
    </div>

    <hr class="WhiteSpaceSeparator">

<?php } else { ?>


    <!-- START PAGE -->
    <div class="EditPageHeader">
        
        <!-- ADD NEW ASSET FROM JOKER PROJECT -->
        <a id="addNewAssetBtn" style="visibility: hidden;" class="HeaderButtonStyle mdc-button mdc-button--raised mdc-button--primary" data-mdc-auto-init="MDCRipple" href="<?php echo esc_url( get_permalink($newAssetPage[0]->ID) . $parameter_pass . $project_id . '&wpunity_scene=' .  $current_scene_id); ?>">
            Add a new 3D asset
        </a>
        
    </div>

    <span class="mdc-typography--caption" style="font-size:16pt">
    

    </span>


    <div class="mdc-toolbar hidable" style="display:block;position:fixed;z-index:1000;">
        
        <div class="" style="width:90%">
            <div class="mdc-toolbar__section mdc-toolbar__section--shrink-to-fit mdc-toolbar__section--align-start"
                    style="width:80%; vertical-align: middle;line-height:1.8em">
                
                <div id="gameInfoBreadcrump" class="mdc-textfield mdc-theme--text-primary-on-dark mdc-form-field" data-mdc-auto-init="MDCTextfield"
                     style="height:30px; margin:0; font-size: 14px; vertical-align: middle;display:block" >

                    <div id="gameClassGameName" style="float:left;max-width:50%;line-height:1.8em">
                        <a title="Back" href="<?php echo $goBackTo_AllProjects_link; ?>"> <i class="material-icons mdc-theme--text-primary-on-dark"
                                                                                             style="font-size: 20px; vertical-align: middle;" >arrow_back</i> </a>
    
                        <i class="material-icons mdc-theme--text-icon-on-dark"
                           style="font-size: 16px; vertical-align: middle;margin-bottom:3px;"
                           title="<?php echo $project_type_obj->string; ?>"><?php echo $project_type_obj->icon; ?> </i>&nbsp;<?php
        
                        //        if ($project_type_obj->string === "Archaeology")
                        //            echo "Museum";
                        //        else
                            echo $project_type_obj->string;
                        //echo $project_type_obj->string;
                        ?>
                        <i class="material-icons mdc-theme--text-icon-on-dark" title="" style="font-size:20px;vertical-align:middle">chevron_right</i>&nbsp;
                        <?php
                            echo $project_post->post_title;
                        ?>
                        <i class="material-icons mdc-theme--text-icon-on-dark" title="" style="font-size:20px;vertical-align:middle">chevron_right</i>

                    </div>
                    
                    <input title="Scene title" placeholder="Scene title" value="<?php echo $scene_post->post_title; ?>" id="sceneTitleInput" name="sceneTitleInput" type="text" class="mdc-textfield__input mdc-theme--text-primary-on-dark"
                           aria-controls="title-validation-msg" minlength="3" required
                           style="display:block; float:left; width:35%; margin-left:0px; padding:0px; border: none; font-size:14px; border-bottom: 1px solid rgba(255, 255, 255, 0.3); box-shadow: none; border-radius: 0;">
                    
                    <p class="mdc-textfield-helptext mdc-textfield-helptext--validation-msg"
                       style="height:25px;max-width:10%;display:block;float:left;"
                       id="title-validation-msg">
                        Must be at least 3 characters long
                    </p>
                    <div class="mdc-textfield__bottom-line"></div>
                </div>

<!--                <div class="mdc-toolbar__section" style="display:block;float:left">-->
<!--                    <nav id="dynamic-tab-bar" class="mdc-tab-bar--indicator-secondary" style="text-transform: uppercase" role="tablist">-->
<!--                        <a role="tab" aria-controls="panel-1" class="mdc-tab mdc-tab-active mdc-tab--active" href="#panel-1" >Editor</a>-->
<!--                        --><?php //if ( $project_type_obj->string === "Energy" || $project_type_obj->string === "Chemistry" ) { ?>
<!---->
<!--                            <a role="tab" aria-controls="panel-2" class="mdc-tab" href="#panel-2" onclick="">Analytics</a>-->
<!--        -->
<!--                            --><?php //if($project_saved_keys['expID'] != ''){ ?>
<!--                                <a role="tab" aria-controls="panel-3" class="mdc-tab" href="#panel-3">at-risk prediction</a>-->
<!--                            --><?php //} ?>
<!--        -->
<!--        -->
<!--                            --><?php //if($project_type_obj->string === "Chemistry"){ ?>
<!--                                <a role="tab" aria-controls="panel-4" class="mdc-tab" href="#panel-4">Content adaptation</a>-->
<!--                            --><?php //} ?>
<!--    -->
<!--                        --><?php //} ?>
<!---->
<!--                        <span class="mdc-tab-bar__indicator"></span>-->
<!--                    </nav>-->
<!--                </div>-->
            </div>

            <!--Set tab buttons-->
<!--            <div class="mdc-toolbar__section" style="display:block;max-width:10%">-->
<!--                <nav id="dynamic-tab-bar" class="mdc-tab-bar mdc-tab-bar--indicator-secondary" role="tablist">-->
<!--                -->
<!--                </nav>-->
<!--            </div>-->

            <div id="save-scene-elements">
               <a id="undo-scene-button" title="Undo last change"><i class="material-icons">undo</i></a>
               <a id="save-scene-button" title="Save all changes you made to the current scene">All changes saved</a>
               <a id="redo-scene-button" title="Redo last change"><i class="material-icons">redo</i></a>
            </div>
            
            
<!--                </div>-->
<!--            </div>-->
            
            
            <a id="compileGameBtn" class="mdc-button mdc-button--raised mdc-theme--text-primary-on-dark mdc-theme--secondary-bg w3-display-right" data-mdc-auto-init="MDCRipple"
               title="When you are finished compile the <?php echo $single_lowercase; ?> into a standalone binary">
                COMPILE
<!--                --><?php //echo $single_lowercase; ?>
            </a>
            

        </div>
    </div>

    <div class="panels">
        <div class="panel active" id="panel-1" role="tabpanel" aria-hidden="false">

            <div class="mdc-layout-grid" style="padding:0px;">
                <div class="mdc-layout-grid__inner">
                    <div class="mdc-layout-grid__cell--span-12">
                        <div id="scene-vr-editor">
							<?php

							// vr_editor loads the $sceneToLoad
							require( plugin_dir_path( __DIR__ ) .  '/vr_editor.php' );
//                            require( plugin_dir_path( __DIR__ ) .  '/vr_editor_scenes_wrapper.php' );
							?>
                        </div>
                    </div>

                    
                    
                    <!-- Scene Options Dialog-->
                    <aside id="options-dialog"
                           class="mdc-dialog"
                           role="alertdialog"
                           style="z-index: 1000;"
                           aria-labelledby="Scene options dialog"
                           aria-describedby="Set the settings of the scene" data-mdc-auto-init="MDCDialog">
                        <div class="mdc-dialog__surface">
                            <header class="mdc-dialog__header">
                                <h2 id="options-dialog-title" class="mdc-dialog__header__title">
                                    Scene options
                                </h2>
                            </header>
                            <section id="options-dialog-description" class="mdc-dialog__body">

                                <div class="mdc-layout-grid">
                                    <div class="mdc-layout-grid__inner">
                                        <div class="mdc-layout-grid__cell--span-6">

                                            <h2 class="mdc-typography--title">Description</h2>

                                            <div class="mdc-textfield mdc-textfield--textarea" data-mdc-auto-init="MDCTextfield" style="border: 1px solid rgba(0, 0, 0, 0.3);">
                                            <textarea id="sceneCaptionInput" name="sceneCaptionInput" class="mdc-textfield__input"
                                                      rows="10" cols="40" style="box-shadow: none; "
                                                      type="text" form="3dAssetForm"><?php echo get_post_meta($current_scene_id, 'wpunity_scene_caption', true); ?></textarea>
                                                <label for="sceneCaptionInput" class="mdc-textfield__label" style="background: none;">Add a description</label>

                                            </div>
                            
                                        </div>

                                        <div class="mdc-layout-grid__cell--span-6">

                                            <h2 class="mdc-typography--title">Screenshot</h2>
                                            <br>
                                            <div class="CenterContents">

												<?php $screenshotImgUrl = get_the_post_thumbnail_url( $current_scene_id );

												if($screenshotImgUrl=='') {
													echo '<script type="application/javascript">is_scene_icon_manually_selected=false</script>';
												}else{
													echo '<script type="application/javascript">is_scene_icon_manually_selected=true</script>';
												}

												if ($screenshotImgUrl) {

													$dataScreenshot = file_get_contents($screenshotImgUrl);
													$dataScreenshotbase64 = 'data:image/jpeg;base64,' . base64_encode($dataScreenshot);
													?>

                                                    <div id="featureImgContainer" class="ImageContainer">
                                                        <img id="wpunity_scene_sshot" name="wpunity_scene_sshot" src="<?php echo $dataScreenshotbase64;?>">
                                                    </div>

												<?php } else { ?>
                                                    <div id="featureImgContainer">
                                                        <img style="width: 160px;" id="wpunity_scene_sshot" name="wpunity_scene_sshot" src="<?php echo plugins_url( '../images/ic_sshot.png', dirname(__FILE__)  ); ?>">
                                                    </div>
												<?php } ?>


                                                <input type="file"
                                                       style="margin: auto;"
                                                       name="wpunity_scene_sshot_manual_select"
                                                       title="Featured image"
                                                       value=""
                                                       id="wpunity_scene_sshot_manual_select"
                                                       accept="image/x-png,image/gif,image/jpeg" >

                                                <div class="CenterContents">

                                                    <p class="mdc-typography--subheading1"> <b>or</b> </p>
                                                    <!-- Clear selected image and take screenshot from 3D canvas-->
                                                    <a title="Capture screenshot from 3D editor"
                                                       id="clear-image-button" class="mdc-button mdc-button--primary mdc-button--raised">Take a screenshot</a>

                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="mdc-layout-grid">
                                    <div class="mdc-layout-grid__inner">

                                    </div>
                                </div>

                            </section>

                            <footer class="mdc-dialog__footer">
                                <a class=" mdc-button mdc-button--primary mdc-dialog__footer__button mdc-dialog__footer__button--accept mdc-button--raised" id="sceneDialogOKBtn">OK</a>
                            </footer>
                        </div>
                        <div class="mdc-dialog__backdrop"></div>
                    </aside>

                </div>
            </div>

            <textarea title="wpunity_scene_json_input" id="wpunity_scene_json_input" style="visibility:hidden; width:0; height:0; display: none;"
                      name="wpunity_scene_json_input"> <?php echo get_post_meta( $current_scene_id, 'wpunity_scene_json_input', true ); ?></textarea>


            <!--Add information for Wind Energy games-->
			<?php if($project_type_obj->string === "Energy") { ?>
                <div class="mdc-layout-grid">
                    <div class="mdc-layout-grid__inner mdc-theme--text-primary-on-light">

                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-4">
                            <h2 class="mdc-typography--title">Average wind speed</h2>
                            <p class="mdc-typography--subheading2">Mountains: 10 m/s</p>
                            <p class="mdc-typography--subheading2">Fields: 8.5 m/s</p>
                            <p class="mdc-typography--subheading2">Seashore: 7.5 m/s</p>
                        </div>

                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-4">
                            <h2 class="mdc-typography--title">Access cost</h2>
                            <p class="mdc-typography--subheading2">Mountains: 3 $</p>
                            <p class="mdc-typography--subheading2">Fields: 2 $</p>
                            <p class="mdc-typography--subheading2">Seashore: 1 $</p>
                        </div>

                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-4">
                            <h2 class="mdc-typography--title">Turbine Types</h2>
                            <p class="mdc-typography--subheading2">Mountains ( Wind class I ): A, B, C</p>
                            <p class="mdc-typography--subheading2">Fields ( Wind class II ): D, E, F</p>
                            <p class="mdc-typography--subheading2">Seashore ( Wind class III ): G, H, I</p>
                        </div>

                    </div>
                </div>
			<?php } ?>


		<?php if ( $project_type_obj->string === "Energy" || $project_type_obj->string === "Chemistry" ) {  ?>

            <div class="panel" id="panel-2" role="tabpanel" aria-hidden="true">

                <div id="analyticsIframeFallback" class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12">

                </div>

                <div id="analyticsIframeContainer" style="position: relative; overflow: hidden; padding-top: 150%; display: none;">
                    <iframe id="analyticsIframeContent" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"></iframe>
                </div>
            </div>

			<?php if($project_saved_keys['expID'] != ''){ ?>

                <div class="panel" id="panel-3" role="tabpanel" aria-hidden="true">
                    <div id="atRiskIframeContainer" style="position: relative; overflow: hidden; padding-top: 180%;">
                        <iframe id="atRiskIframeContent" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"></iframe>
                    </div>
                </div>

			<?php } ?>

			<?php if($project_type_obj->string === "Chemistry"){ ?>
                <div class="panel" id="panel-4" role="tabpanel" aria-hidden="true">
                    <div style="position: relative; overflow: hidden; padding-top: 100%;">
                        <iframe id="ddaIframeContent" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"></iframe>
                    </div>
                </div>
			<?php } ?>

		<?php } ?>


        <!--Compile Dialog-->
        <aside id="compile-dialog"
               class="mdc-dialog"
               role="alertdialog"
               style="z-index: 1000;"
               data-game-slug="<?php echo $projectSlug; ?>"
               data-project-id="<?php echo $project_id; ?>"
               aria-labelledby="my-mdc-dialog-label"
               aria-describedby="my-mdc-dialog-description" data-mdc-auto-init="MDCDialog">
            <div class="mdc-dialog__surface">

                <header class="mdc-dialog__header">
                    <h2 class="mdc-dialog__header__title">
                        Compile <?php echo $single_lowercase; ?>
                    </h2>
                </header>

                <section class="mdc-dialog__body">

                    <h3 class="mdc-typography--subheading2"> Platform </h3>

                    <div id="platform-select" class="mdc-select" role="listbox" tabindex="0" style="min-width: 40%;">
                        <span id="currently-selected" class="mdc-select__selected-text mdc-typography--subheading2">Select a platform</span>
                        <div class="mdc-simple-menu mdc-select__menu" style="position: initial; max-height: none; ">
                            <ul class="mdc-list mdc-simple-menu__items">
                                <li class="mdc-list-item mdc-theme--text-hint-on-light" role="option" id="platforms" aria-disabled="true" style="pointer-events: none;" tabindex="-1">
                                    Select a platform
                                </li>
                                <li class="mdc-list-item mdc-theme--text-primary-on-background" role="option" id="platform-windows" tabindex="0">
                                    Windows
                                </li>
                                <li class="mdc-list-item mdc-theme--text-primary-on-background" role="option" id="platform-linux" tabindex="0">
                                    Linux
                                </li>
                                <li class="mdc-list-item mdc-theme--text-primary-on-background" role="option" id="platform-mac" tabindex="0">
                                    Mac OS
                                </li>
                                <li class="mdc-list-item mdc-theme--text-primary-on-background" role="option" id="platform-web" tabindex="0">
                                    Web
                                </li>
                                <li class="mdc-list-item mdc-theme--text-primary-on-background" role="option" id="platform-android" tabindex="0">
                                    Android
                                </li>

                            </ul>
                        </div>
                    </div>
                    <input id="platformInput" type="hidden">


                    <div class="mdc-typography--caption mdc-theme--text-primary-on-background" style="float: right;"> <i title="Memory Usage" class="material-icons AlignIconToBottom">memory</i> <span  id="unityTaskMemValue">0</span> KB </div>

                    <hr class="WhiteSpaceSeparator">

                    <h2 id="compileProgressTitle" style="display: none" class="CenterContents mdc-typography--headline">
                        Step: 1/4
                    </h2>

                    <div class="progressSlider" id="compileProgressDeterminate" style="display: none;">
                        <div class="progressSliderLine"></div>
                        <div class="progressSliderSubLineDeterminate" id="progressSliderSubLineDeterminateValue"></div>
                    </div>

                    <div class="progressSlider" id="compileProgressSlider" style="display: none;">
                        <div class="progressSliderLine"></div>
                        <div class="progressSliderSubLine progressIncrease"></div>
                        <div class="progressSliderSubLine progressDecrease"></div>
                    </div>


                    <div id="compilationProgressText" class="CenterContents mdc-typography--title"></div>

                    <div class="CenterContents">
                        <a class="mdc-typography--title" href="" id="wpunity-ziplink" style="display:none;"> <i style="vertical-align: text-bottom" class="material-icons">file_download</i> Download Zip</a>
                        <a class="mdc-typography--title" href="" id="wpunity-weblink" style="display:none;margin-left:30px" target="_blank">Web link</a>
                    </div>

                </section>

                <footer class="mdc-dialog__footer">
                    <a id="compileCancelBtn" class="mdc-button mdc-dialog__footer__button--cancel mdc-dialog__footer__button">Cancel</a>
                    <a id="compileProceedBtn" type="button" class="mdc-button mdc-button--primary mdc-dialog__footer__button mdc-button--raised LinkDisabled">Proceed</a>
                </footer>
            </div>
            <div class="mdc-dialog__backdrop"></div>
        </aside>

    </div>

    <script type="text/javascript">

        var mdc = window.mdc;
        var MDCSelect = mdc.select.MDCSelect;
        
        mdc.autoInit();

        loadButtonActions();
        
        // Delete scene dialogue
        var deleteDialog = new mdc.dialog.MDCDialog(document.querySelector('#delete-dialog'));
        deleteDialog.focusTrap_.deactivate();
        
        // Compile dialogue
        var compileDialog = new mdc.dialog.MDCDialog(document.querySelector('#compile-dialog'));
        compileDialog.focusTrap_.deactivate();

        // Project Analytics
        var project_id = <?php echo $project_id; ?>;
        var project_keys = [];
        project_keys = <?php echo json_encode(wpunity_getProjectKeys($project_id, $project_scope)); ?>;
        var scene_id = <?php echo $current_scene_id; ?>;
        var game_type = "<?php echo strtolower($project_type_obj->string);?>";
        var user_email = "<?php echo $user_email; ?>";
        var current_user_id = "<?php echo get_current_user_id();?>";
        var energy_stats = <?php echo json_encode(wpunity_windEnergy_scene_stats($current_scene_id)); ?>;
        loadAnalyticsTab(project_id, scene_id, project_keys, game_type, user_email, current_user_id, energy_stats);
        

        // REM: HERE: SCREENSHOT OF SCENE
        function readURL(input) {

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    jQuery('#wpunity_scene_sshot').attr('src', e.target.result);
                    is_scene_icon_manually_selected = true;
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        jQuery("#wpunity_scene_sshot_manual_select").change(function() {
            readURL(this);
        });

        jQuery("#clear-image-button").click(function() {
            takeScreenshot();
            is_scene_icon_manually_selected = false;
        });

        
        // DELETE SCENE DIALOGUE
        jQuery("#deleteSceneDialogDeleteBtn").click(function (e) {
            jQuery('#delete-scene-dialog-progress-bar').show();
            jQuery( "#deleteSceneDialogDeleteBtn" ).addClass( "LinkDisabled" );
            jQuery( "#deleteSceneDialogCancelBtn" ).addClass( "LinkDisabled" );
            wpunity_deleteSceneAjax(deleteDialog.id, url_scene_redirect);
        });

        jQuery("#deleteSceneDialogCancelBtn").click(function (e) {

            jQuery('#delete-scene-dialog-progress-bar').hide();
            deleteDialog.close();
        });

        function deleteScene(id) {

            var dialogTitle = document.getElementById("delete-dialog-title");
            var dialogDescription = document.getElementById("delete-dialog-description");
            var sceneTitle = document.getElementById(id+"-title").textContent.trim();

            dialogTitle.innerHTML = "<b>Delete " + sceneTitle+"?</b>";
            dialogDescription.innerHTML = "Are you sure you want to delete your scene '" +sceneTitle + "'? There is no Undo functionality once you delete it.";
            deleteDialog.id = id;
            deleteDialog.show();
        }

        function hideCompileProgressSlider() {
            jQuery( "#compileProgressSlider" ).hide();
            jQuery( "#compileProgressTitle" ).hide();
            jQuery( "#compileProgressDeterminate" ).hide();
            jQuery( "#platform-select" ).removeClass( "mdc-select--disabled" ).attr( "aria-disabled","false" );

            jQuery( "#compileProceedBtn" ).removeClass( "LinkDisabled" );
            jQuery( "#compileCancelBtn" ).removeClass( "LinkDisabled" );
        }
    </script>

<?php } ?>
<?php get_footer(); ?>
