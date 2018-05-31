<?php

function load2DSceneEditorScripts() {
	wp_enqueue_script('wpunity_scripts');
}
add_action('wp_enqueue_scripts', 'load2DSceneEditorScripts' );



if ( get_option('permalink_structure') ) { $perma_structure = true; } else {$perma_structure = false;}
if( $perma_structure){$parameter_Scenepass = '?wpunity_scene=';} else{$parameter_Scenepass = '&wpunity_scene=';}
if( $perma_structure){$parameter_pass = '?wpunity_game=';} else{$parameter_pass = '&wpunity_game=';}

$scene_id = intval( $_GET['wpunity_scene'] );
$scene_id = sanitize_text_field( $scene_id );

$scene_type = sanitize_text_field( $_GET['scene_type'] );

$project_id = intval( $_GET['wpunity_game'] );
$project_id = sanitize_text_field( $project_id );

$game_post = get_post($project_id);
$game_type_obj = wpunity_return_game_type($project_id);

$scene_post = get_post($scene_id);
$sceneSlug = $scene_post->post_title;

$editgamePage = wpunity_getEditpage('game');
$allGamesPage = wpunity_getEditpage('allgames');
$newAssetPage = wpunity_getEditpage('asset');
$editscenePage = wpunity_getEditpage('scene');
$editscene2DPage = wpunity_getEditpage('scene2D');
$editsceneExamPage = wpunity_getEditpage('sceneExam');

$userid = get_current_user_id();
$user_data = get_userdata( $userid );
$user_email = $user_data->user_email;

wp_enqueue_media($scene_post->ID);
require_once(ABSPATH . "wp-admin" . '/includes/media.php');

$scene_title = 'Exam';
$molecules = wpunity_get_all_molecules_of_game($project_id);
$savedMoleculesVal = get_post_meta($scene_id, 'wpunity_input_molecules',true);
$savedMolecules = explode(',', $savedMoleculesVal);

if ($project_scope == 0) {
	$single_first = "Tour";
} else if ($project_scope == 1){
	$single_first = "Lab";
} else {
	$single_first = "Project";
}


$scene_data = wpunity_getFirstSceneID_byProjectID($project_id,'chemistry_games');//first 3D scene id
$edit_scene_page_id = $editscenePage[0]->ID;
$goBackTo_MainLab_link = get_permalink($edit_scene_page_id) . $parameter_Scenepass . $scene_data['id'] . '&wpunity_game=' . $project_id . '&scene_type=' . $scene_data['type'];
$goBackTo_AllProjects_link = esc_url( get_permalink($allGamesPage[0]->ID));

$preSavedStrategies = get_post_meta($scene_id, 'wpunity_exam_strategy', true);

if(isset($_POST['submitted']) && isset($_POST['post_nonce_field']) && wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')) {

    $savedStrategies = $_POST['json-strategies-input'];
    update_post_meta($scene_id, 'wpunity_exam_strategy', $savedStrategies);

    wp_redirect($goBackTo_MainLab_link);
    exit;
}

get_header(); ?>

    <style>
        .panel { display: none; }
        .panel.active { display: block; }
        .mdc-tab { min-width: 0; }
    </style>

    <div class="PageHeaderStyle">
        <h1 class="mdc-typography--display1 mdc-theme--text-primary-on-light">
            <a title="Back" href="<?php echo $goBackTo_MainLab_link; ?>"> <i class="material-icons" style="font-size: 36px; vertical-align: top;" >arrow_back</i> </a>
			<?php echo $game_post->post_title; ?>
        </h1>

    </div>

    <span class="mdc-typography--caption">
        <i class="material-icons mdc-theme--text-icon-on-background AlignIconToBottom" title="Add category title & icon"><?php echo $game_type_obj->icon; ?> </i>&nbsp;<?php echo $game_type_obj->string; ?></span>

    <hr class="mdc-list-divider">

    <ul class="EditPageBreadcrumb">
        <li><a class="mdc-typography--caption mdc-theme--primary" href="<?php echo $goBackTo_AllProjects_link; ?>" title="Go back to Project selection">Home</a></li>
        <li><i class="material-icons EditPageBreadcrumbArr mdc-theme--text-hint-on-background">arrow_drop_up</i></li>
        <li><a class="mdc-typography--caption mdc-theme--primary" href="<?php echo $goBackTo_MainLab_link; ?>" title="Go back to Project editor"><?php echo $single_first; ?> Editor</a></li>
        <li><i class="material-icons EditPageBreadcrumbArr mdc-theme--text-hint-on-background">arrow_drop_up</i></li>
        <li class="mdc-typography--caption"><span class="EditPageBreadcrumbSelected"><?php echo $scene_title; ?> Editor</span></li>

    </ul>



    <div class="mdc-toolbar">
        <div class="mdc-toolbar__row" style="min-height: 0;">
            <div class="mdc-toolbar__section mdc-toolbar__section--shrink-to-fit mdc-toolbar__section--align-start">
                <span class="mdc-toolbar__title"> <?php echo $sceneSlug; ?> </span>
            </div>

            <!--Set tab buttons-->
            <div class="mdc-toolbar__section mdc-toolbar__section--align-start" style="justify-content: flex-end">
                <nav id="dynamic-tab-bar" class="mdc-tab-bar mdc-tab-bar--indicator-secondary" role="tablist">
                    <a role="tab" aria-controls="panel-1" class="mdc-tab mdc-tab-active mdc-tab--active" href="#panel-1" >Build Strategy</a>
                    <a role="tab" aria-controls="panel-2" class="mdc-tab" href="#panel-2">Select Molecules</a>
                    <span class="mdc-tab-bar__indicator"></span>
                </nav>
            </div>

        </div>
    </div>

    <div class="panels">
        <div class="panel active" id="panel-1" role="tabpanel" aria-hidden="false">

            <div class="mdc-layout-grid">

                <form name="edit_exam_scene_form" action="" id="edit_exam_scene_form" method="POST" enctype="multipart/form-data">
                    <div class="mdc-layout-grid__inner">

                        <div class="mdc-layout-grid__cell--span-12">

                            <h2 class="mdc-typography--title">Build strategy</h2>
                            <span style="font-style: italic;" class="mdc-typography--subheading2 mdc-theme--text-secondary-on-light">
                            Select molecules to create a strategy. The active molecules order dictates the sequence of appearance in the Unity game. You can create more than one strategies.
                            </span>

							<?php $molecules = wpunity_get_all_molecules_of_game($project_id); ?>

                            <div class="WhiteSpaceSeparator"></div>

                            <div class="mdc-layout-grid__inner">

                                <div class="mdc-layout-grid__cell--span-12">

                                    <h2 class="mdc-typography--title">Available molecules to use in a strategy</h2>

                                    <ul id="sortable2" class="connectedSortable mdc-layout-grid__inner" style="min-height: 110px; border: 4px solid rgba(63,81,181, .23); background-color: rgba(0,0,0,.23);">
										<?php foreach ($molecules as $molecule) { ?>
											<?php if (!in_array($molecule['moleculeID'], $savedMolecules)) { ?>

                                                <li class="mdc-layout-grid__cell mdc-layout-grid__cell--span-2">

                                                    <div class="mdc-card mdc-theme--background molecule" id="<?php echo $molecule['moleculeID'];?>" data-molec-type="<?php echo $molecule['moleculeType']; ?>">
                                                        <div style="min-height: 110px; min-width: 100%; max-height: 110px; text-align: center; overflow: hidden; position: relative; ">

															<?php if ($molecule['moleculeImage']){ ?>
                                                                <img width="495" height="330" src="<?php echo $molecule['moleculeImage']; ?>" class="attachment-post-thumbnail size-post-thumbnail wp-post-image">
															<?php } else { ?>
                                                                <div style="min-height: 110px;" class="DisplayBlock mdc-theme--secondary-bg CenterContents">
                                                                    <i style="font-size: 48px; padding-top: 30px;" class="material-icons mdc-theme--text-icon-on-background">insert_photo</i>
                                                                </div>
															<?php } ?>
                                                        </div>

                                                        <div class="mdc-card__primary">
                                                            <p class="mdc-card__title mdc-typography--subheading2" style=" white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
																<?php echo $molecule['moleculeName'];?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </li>
											<?php  } ?>

										<?php }?>

                                    </ul>
                                </div>

                                <div class="mdc-layout-grid__cell--span-12">

                                    <h2 class="mdc-typography--title">Active molecules</h2>

                                    <ul id="sortable1" class="connectedSortable mdc-theme--secondary-light-bg mdc-layout-grid__inner" style="min-height: 110px; border: 4px solid rgba(63,81,181, .23);">
										<?php foreach ($molecules as $molecule) { ?>
											<?php if (in_array($molecule['moleculeID'], $savedMolecules)) { ?>

                                                <li class="mdc-layout-grid__cell mdc-layout-grid__cell--span-2">

                                                    <div class="mdc-card mdc-theme--background molecule" id="<?php echo $molecule['moleculeID'];?>" data-molec-type="<?php echo $molecule['moleculeType']; ?>">
                                                        <div style="min-height: 110px; min-width: 100%; max-height: 110px; text-align: center; overflow: hidden; position: relative; ">

															<?php if ($molecule['moleculeImage']){ ?>
                                                                <img width="495" height="330" src="<?php echo $molecule['moleculeImage']; ?>" class="attachment-post-thumbnail size-post-thumbnail wp-post-image">
															<?php } else { ?>
                                                                <div style="min-height: 110px;" class="DisplayBlock mdc-theme--secondary-bg CenterContents">
                                                                    <i style="font-size: 48px; padding-top: 30px;" class="material-icons mdc-theme--text-icon-on-background">insert_photo</i>
                                                                </div>
															<?php } ?>
                                                        </div>

                                                        <div class="mdc-card__primary">
                                                            <p class="mdc-card__title mdc-typography--subheading2" style=" white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
																<?php echo $molecule['moleculeName'];?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </li>
											<?php  } ?>

										<?php }?>
                                    </ul>
                                </div>

                                <div class="mdc-layout-grid__cell--span-12">
                                    <a id="add-strategy-btn" class="mdc-button mdc-button--raised mdc-button--primary mdc-theme--secondary-bg" data-mdc-auto-init="MDCRipple">Add strategy</a>
                                </div>

                                <div class="mdc-layout-grid__cell--span-12">
                                    <h2 class="mdc-typography--title">Saved strategies</h2>
                                    <ul id="saved-strategies"></ul>
                                </div>

                            </div>

                        </div>


                        <input type="hidden" name="active-molecules-input" id="active-molecules-input" value="[]" />

                        <input title="strategyJson" id="molecule-json-field" name="molecule-json-field" type="hidden">

                        <input type="hidden" name="json-strategies-input" id="json-strategies-input" value="[]" />

                        <div class="mdc-layout-grid__cell--span-12">

                            <hr class="WhiteSpaceSeparator">

							<?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                            <input type="hidden" name="submitted" id="submitted" value="true" />

                            <button style="margin-bottom: 24px; width: 100%; height: 48px;" class="mdc-button mdc-elevation--z2 mdc-button--raised" data-mdc-auto-init="MDCRipple" type="submit">
                                Save strategies
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="panel" id="panel-2" role="tabpanel" aria-hidden="true">
            <form name="create_new_strategy_form" action="" id="create_new_strategy_form" method="POST" enctype="multipart/form-data">
                <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                <div class="mdc-layout-grid">

                    <h3 class="mdc-typography--subheading2"> Choose the molecules that will be available for use in the exams </h3>

                    <div class="mdc-layout-grid__inner" id="avail-molecules-list">

                        <!--Stathi load all molecules here with a Foreach-->
                        <?php if ($game_type_obj->string === "Chemistry") {

                            $molecules = wpunity_get_all_molecules_of_game($project_id);
                            foreach ($molecules as $molecule) { ?>

                                <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-3 mdc-form-field">
                                    <div class="mdc-form-field">
                                        <div class="mdc-checkbox">
                                            <input name="<?php echo $molecule['moleculeID'];?>Checkbox" type="checkbox" value="<?php echo $molecule['moleculeID'];?>" id="<?php echo $molecule['moleculeID'];?>-checkbox" class="mdc-checkbox__native-control MoleculeCheckbox">
                                            <div class="mdc-checkbox__background">
                                                <svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
                                                    <path class="mdc-checkbox__checkmark__path" fill="none" stroke="white" d="M1.73,12.91 8.1,19.28 22.79,4.59"></path>
                                                </svg>
                                                <div class="mdc-checkbox__mixedmark"></div>
                                            </div>
                                        </div>
                                        <label class="CursorPointer" for="<?php echo $molecule['moleculeID'];?>-checkbox" style="padding: 0; margin: 0;"><?php echo $molecule['moleculeName'];?></label>
                                    </div>
                                </div>

                            <?php } ?>
                        <?php } ?>

                        <input id="availableMoleculesInput" type="hidden" value="[]">

                    </div>

                </div>

                <div class="mdc-layout-grid">
                    <div class="mdc-layout-grid__inner">
                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12">
                            <button style="margin-bottom: 24px; width: 100%; height: 48px;" class="mdc-button mdc-elevation--z2 mdc-button--raised" data-mdc-auto-init="MDCRipple" type="submit">
                                Submit changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <script type="text/javascript">

        var examTitle = "<?php echo $game_post->post_title; ?>";

        var mdc = window.mdc;
        mdc.autoInit();

        var dynamicTabBar = window.dynamicTabBar = new mdc.tabs.MDCTabBar(document.querySelector('#dynamic-tab-bar'));
        var dots = document.querySelector('.dots');
        var panels = document.querySelector('.panels');

        dynamicTabBar.preventDefaultOnClick = true;

        dynamicTabBar.listen('MDCTabBar:change', function (t) {
            var dynamicTabBar = t.detail;
            var nthChildIndex = dynamicTabBar.activeTabIndex;

            updatePanel(nthChildIndex);
        });


        function updatePanel(index) {
            var activePanel = panels.querySelector('.panel.active');
            if (activePanel) {
                activePanel.classList.remove('active');
            }
            var newActivePanel = panels.querySelector('.panel:nth-child(' + (index + 1) + ')');
            if (newActivePanel) {
                newActivePanel.classList.add('active');
            }
        }

        function deleteStrategy(id) {
            jQuery('#'+id).remove();
        }

        jQuery("#add-strategy-btn").click(function() {

            var savedStrategiesList = jQuery( "#saved-strategies" );

            var new_id1 = makeid();
            var new_id2 = makeid();

            var strategy = jQuery("#molecule-json-field").val();

            if (strategy.length > 2) {
                var strategyId = examTitle+""+new_id1+"strat"+new_id2;
                savedStrategiesList.append( '<li class="mdc-list-item" id='+strategyId+'><span class="mdc-list-item__text">'+ strategy+ '</span>&nbsp;<a onclick="deleteStrategy('+"'"+strategyId+"'"+')" class="mdc-list-item CursorPointer" aria-label="Delete game" title="Delete project"><i class="material-icons mdc-list-item__end-detail" aria-hidden="true" title="Delete">delete</i></a></li>');
            }

            var json = {};
            jQuery( savedStrategiesList.children() ).each(function( index ) {

                var id = jQuery( this ).attr('id');
                var val = jQuery( "span", this ).text();
                val = JSON.parse(val);
                json[id] = val;

            });

            jQuery("#json-strategies-input").val(JSON.stringify(json));
        });

        jQuery( function() {
            jQuery( "#sortable1, #sortable2" ).sortable({
                connectWith: ".connectedSortable",
                change: function(event, ui) {
                    ui.placeholder.css({visibility: 'visible', background : 'rgba(255, 64, 129, .54)'});

                },
                receive: function(event, ui) {

                    var arr = [];
                    var typeArr = [];
                    jQuery('div','#sortable1').each(function(){

                        if (jQuery(this).attr('id')) {
                            arr.push(jQuery(this).attr('id'));
                            typeArr.push(jQuery(this).attr('data-molec-type'));
                        }
                    });

                    jQuery("#active-molecules-input").val(arr);
                    jQuery('#molecule-json-field').val(JSON.stringify(typeArr));
                },
                create: function(event, ui) {

                    var arr = [];
                    var typeArr = [];
                    jQuery('div','#sortable1').each(function(){

                        if (jQuery(this).attr('id')) {
                            arr.push(jQuery(this).attr('id'));
                            typeArr.push(jQuery(this).attr('data-molec-type'));
                        }
                    });

                    arr.reverse();
                    typeArr.reverse();

                    jQuery("#active-molecules-input").val(arr);
                    jQuery('#molecule-json-field').val(JSON.stringify(typeArr));
                },
                sort: function(event, ui) {

                    var arr = [];
                    var typeArr = [];
                    jQuery('div','#sortable1').each(function(){

                        if (jQuery(this).attr('id')) {
                            arr.push(jQuery(this).attr('id'));
                            typeArr.push(jQuery(this).attr('data-molec-type'));
                        }
                    });

                    arr.reverse();
                    typeArr.reverse();

                    jQuery("#active-molecules-input").val(arr);
                    jQuery('#molecule-json-field').val(JSON.stringify(typeArr));
                }

            }).disableSelection();

        } );


        jQuery("#copy-output-btn").click(function() {
            var copyText = document.getElementById("molecule-json-field");
            copyText.select();
            document.execCommand("Copy");
            alert("Strategy copied: " + copyText.value);
        });

        jQuery( ".MoleculeCheckbox" ).click(function() {
            var molecIds = jQuery("#avail-molecules-list input:checkbox:checked").map(function(){
                return jQuery(this).val();
            }).get();
            jQuery( "#availableMoleculesInput" ).val(JSON.stringify(molecIds));
        });

        function makeid() {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

            for (var i = 0; i < 5; i++)
                text += possible.charAt(Math.floor(Math.random() * possible.length));

            return text;
        }

    </script>

<?php get_footer(); ?>