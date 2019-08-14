<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

if ( get_option('permalink_structure') ) { $perma_structure = true; } else {$perma_structure = false;}
if( $perma_structure){$parameter_Scenepass = '?wpunity_scene=';} else{$parameter_Scenepass = '&wpunity_scene=';}
if( $perma_structure){$parameter_pass = '?wpunity_game=';} else{$parameter_pass = '&wpunity_game=';}
$parameter_assetpass = $perma_structure ? '?wpunity_asset=' : '&wpunity_asset=';

if ($project_scope == 0) {
    //	$single_lowercase = "tour";
    //	$single_first = "Tour";
} else if ($project_scope == 1){
    //	$single_lowercase = "lab";
    //	$single_first = "Lab";
} else {
    //	$single_lowercase = "project";
    //	$single_first = "Project";
}

$project_id = get_page_by_path( 'archaeology-joker', OBJECT, 'wpunity_game' )->ID;

//if( isset($_GET['wpunity_asset']) ) {
//	$asset_inserted_id = sanitize_text_field( intval( $_GET['wpunity_asset'] ));
//	$asset_post = get_post($asset_inserted_id);
//	if($asset_post->post_type == 'wpunity_asset3d') {
//		$create_new = 0;
//		$asset_checked_id = $asset_inserted_id;
//	}
//}

$game_post = get_post($project_id);
$gameSlug = $game_post->post_name;

$isAdmin = is_admin() ? 'back' : 'front';
echo '<script>';
echo 'isAdmin="'.$isAdmin.'";'; // This variable is used in the request_game_assemble.js
echo '</script>';

$isUserloggedIn = is_user_logged_in();
$current_user = wp_get_current_user();
$login_username = $current_user->user_login;

if($isUserloggedIn)
    $isUserAdmin = current_user_can('administrator');
else
    $isUserAdmin = false;

$pluginpath = dirname (plugin_dir_url( __DIR__  ));
$pluginpath = str_replace('\\','/',$pluginpath);

//--Uploads/myGameProjectUnity--
$upload_dir = wp_upload_dir()['basedir'];
$upload_dir = str_replace('\\','/',$upload_dir);

// DELETE ASSET AJAX
wp_enqueue_script( 'ajax-script_deleteasset', $pluginpath.'/js_libs/delete_ajaxes/delete_asset.js', array('jquery') );
wp_localize_script( 'ajax-script_deleteasset', 'my_ajax_object_deleteasset',
	array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
);


//Get 'parent-game' taxonomy with the same slug as Game (in order to show scenes that belong here)
//$allScenePGame = get_term_by('slug', $gameSlug, 'wpunity_scene_pgame');
//if ($allScenePGame)
//    $allScenePGameID = $allScenePGame->term_id;


$editgamePage = wpunity_getEditpage('game');
$newAssetPage = wpunity_getEditpage('asset');

//$urlforAssetEdit = esc_url( get_permalink($newAssetPage[0]->ID) . $parameter_pass . $project_id . '&wpunity_scene=' .$scene_id . '&wpunity_asset=' ); // . asset_id

get_header();

?>



<?php

// Display Login name at right
if($isUserloggedIn){ ?>
    <span style="float:right; right:0; font-family: 'Comic Sans MS'; display:inline-table;margin-top:10px">Welcome,
        <a href="https://heliosvr.mklab.iti.gr/account/" style="color:dodgerblue">
              <?php echo $login_username;?>
        </a>
    </span>
<?php } ?>


<?php



$user_id = get_current_user_id();
$user_games_slugs = wpunity_get_user_game_projects($user_id, $isUserAdmin);
$assets = get_games_assets($user_games_slugs);


?>
<div id="page-wrapper" style="display:inline-block">

    <!-- Display assets Grid-->
    <div style="width:70%;float:left;padding-top:5px;padding-left:5px;" class="mdc-layout-grid">
    <span class="mdc-typography--display1 mdc-theme--text-primary-on-background" style="display:inline-table;margin-bottom:20px;">Shared <?php echo $isUserloggedIn?" and private": ""; ?> Assets</span>
    
    <a href="#" class="helpButton" onclick="alert('Login to a) add a Shared Asset or b) to create a Project and add your private Assets there')">?</a>
    <div class="mdc-layout-grid__inner">
        <!-- Card to add asset -->
        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-2" style="position:relative;background: orangered">
            <a href="<?php echo $isUserloggedIn ?
                esc_url( get_permalink($newAssetPage[0]->ID) . $parameter_pass . $project_id ) : wp_login_url();
            ?>">
            
            <i class="addAssetCardIcon material-icons" style="<?php if(!$isUserloggedIn){?> filter:invert(30%) <?php }?>">add_circle</i>
            <span class="addAssetCardWords" style="<?php if(!$isUserloggedIn){?> filter:invert(30%) <?php }?>">Shared Asset</span>
            </a>
        </div>
        
        <!-- Each Asset -->
        <?php foreach ($assets as $asset) {    ?>

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-2" style="position:relative">

                <div class="asset-shared-thumbnail mdc-card mdc-theme--background" id="<?php echo $asset['assetid']; ?>">
    
                        <?php $pGameId= get_page_by_path($asset['assetParentGameSlug'], OBJECT, 'wpunity_game')->ID; ?>
                        
                        <!-- Edit url -->
                        <a class="editasseturl" href="<?php echo home_url().'/wpunity-3d-asset-creator/?wpunity_game='.$pGameId.
                            '&wpunity_scene=&wpunity_asset='.$asset['assetid'].'#English'; ?>">
                            <?php if ($asset['screenImagePath']){ ?>
                                <img src="<?php echo $asset['screenImagePath']; ?>" class="asset-shared-thumbnail">
                            <?php } else { ?>
                                <div style="min-height: 226px;" class="DisplayBlock mdc-theme--secondary-bg CenterContents">
                                    <i style="font-size: 64px; padding-top: 80px;" class="material-icons mdc-theme--text-icon-on-background">insert_photo</i>
                                </div>
                            <?php } ?>
                        </a>
                        
                        <!-- Title -->
                        <h1 class="assetsListCardTitle mdc-card__title mdc-typography--title" style="">
                            <a class="mdc-theme--secondary"
                               href="<?php echo home_url().'/wpunity-3d-asset-creator/?wpunity_game='.$pGameId.
                                   '&wpunity_scene=&wpunity_asset='.$asset['assetid'].'#English';
                               ?>"><?php echo $asset['assetName'];?></a>
                        </h1>

                        <!-- Author -->
                        <p class="sharedAssetsUsername mdc-typography--caption">
                            <img style="width:20px;height:20px;border-radius: 50%;vertical-align:middle" src="<?php echo get_avatar_url($asset['author_id']);?>">
                            <a href="<?php echo home_url().'/user/'.$asset['author_username']; ?>"
                               style="color:white; mix-blend-mode: difference;">
                                <?php echo $asset['author_displayname']; ?>
                            </a>
                        </p>


                        <!-- Category -->
<!--                        <p class="assetsListCardCategory mdc-card__title mdc-typography--body1">-->
<!--                            --><?php //echo $asset['categoryName'];?>
<!--                        </p>-->
    
                        <!-- DELETE BUTTON -->
                        <?php
                        // For joker assets, If the user is not administrator he should not be able to delete or edit them.
                        if( $isUserAdmin || ($user_id == $asset['author_id'])) {  ?>
                            <a id="deleteAssetBtn" data-mdc-auto-init="MDCRipple" title="Delete asset"
                               class="deleteAssetListButton mdc-button mdc-button--compact mdc-card__action"
                               onclick="wpunity_deleteAssetAjax(<?php echo $asset['assetid'];?>,'<?php echo $gameSlug ?>',<?php echo $asset['isCloned'];?>)"
                               >DELETE</a>
                        <?php } ?>
    
    
                        <?php if ($asset['isJoker']=='true') { ?>
                            <!--                        <span class="sharedAssetsIndicator mdc-typography--subheading1" style="background: rgba(144,238,144,0.3);">Shared</span>-->
                        <?php } else { ?>
                            <span class="sharedAssetsIndicator mdc-typography--subheading1"
                                  style="background: rgba(250,250,210,0.3);">
                            <?php echo "Personal @ ". $asset['assetParentGame']; ?></span>
                        <?php } ?>

                </div>
            </div>
        <?php } ?>

    </div>
    
    
</div>

<div style="width:30%;float:right; padding-right:5px;">
   <?php get_sidebar(); ?>
</div>

</div>


<!--  No Assets Empty Repo-->
<?php if ( !$assets ) :  ?>
    <hr class="WhiteSpaceSeparator">
    <div class="CenterContents">
        <i class="material-icons mdc-theme--text-icon-on-light" style="font-size: 96px;" aria-hidden="true" title="No assets available">
            insert_photo
        </i>
        <h3 class="mdc-typography--headline">No Assets available</h3>
        <hr class="WhiteSpaceSeparator">
    </div>
<?php endif; ?>
<!--                     -->

<script type="text/javascript">

    var mdc = window.mdc;
    mdc.autoInit();
    
    var helpDialog = document.querySelector('#help-dialog');
    if (helpDialog) {
        helpDialog = new mdc.dialog.MDCDialog(helpDialog);
        jQuery( "#helpButton" ).click(function() {
            helpDialog.show();
        });
    }
    
    var deleteDialog = document.querySelector('#delete-dialog');
    if (deleteDialog) {
        deleteDialog = new mdc.dialog.MDCDialog(deleteDialog);
        deleteDialog.focusTrap_.deactivate();
    }
</script>
<?php get_footer(); ?>