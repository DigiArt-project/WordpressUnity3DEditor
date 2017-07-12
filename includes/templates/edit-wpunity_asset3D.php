<?php

// Three js : for simple rendering
wp_enqueue_script('wpunity_load_threejs');
wp_enqueue_script('wpunity_load_objloader');
wp_enqueue_script('wpunity_load_mtlloader');
wp_enqueue_script('wpunity_load_orbitcontrols');
wp_enqueue_script('wu_3d_view');
wp_enqueue_script('wpunity_asset_editor_scripts');
wp_enqueue_script('flot');


$create_new = 1; //1=NEW ASSET 0=EDIT ASSET
$perma_structure = get_option('permalink_structure') ? true : false;

$parameter_pass = $perma_structure ? '?wpunity_game=' : '&wpunity_game=';
$parameter_scenepass = $perma_structure ? '?wpunity_scene=' : '&wpunity_scene=';

$project_id = sanitize_text_field( intval( $_GET['wpunity_game'] ));
$asset_id = sanitize_text_field( intval( $_GET['wpunity_asset'] ));

$game_post = get_post($project_id);
$gameSlug = $game_post->post_name;
$game_type_obj = wpunity_return_game_type($project_id);

//Get 'parent-game' taxonomy with the same slug as Game
$assetPGame = get_term_by('slug', $gameSlug, 'wpunity_asset3d_pgame');
$assetPGameID = $assetPGame->term_id;
$assetPGameSlug = $assetPGame->post_name;

$scene_post = get_post($asset_id);
if($scene_post->post_type == 'wpunity_asset3d') {$create_new = 0;}

//$scene_post = get_post($scene_id);
//$sceneSlug = $scene_post->post_title;

$editgamePage = wpunity_getEditpage('game');
$allGamesPage = wpunity_getEditpage('allgames');

if(isset($_POST['submitted']) && isset($_POST['post_nonce_field']) && wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')) {

	$assetCatID = $_POST['term_id'];

	$asset_taxonomies = array(
		'wpunity_asset3d_pgame' => array(
			$assetPGameID,
		),
		'wpunity_asset3d_cat' => array(
			$assetCatID,
		)
	);

	$asset_information = array(
		'post_title' => esc_attr(strip_tags($_POST['assetTitle'])),
		'post_content' => esc_attr(strip_tags($_POST['assetDesc'])),
		'post_type' => 'wpunity_asset3d',
		'post_status' => 'publish',
		'tax_input' => $asset_taxonomies,
	);

	$assetCatTerm = get_term_by('id', $assetCatID, 'wpunity_asset3d_cat');
	if($assetCatTerm->slug == 'consumer'){
		//Energy Consumption Cost (in $)
		$safe_cost_values = array( -5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5 );
		$underPowerCost = intval($_POST['underPowerCost']);
		$normalPowerCost = intval($_POST['normalPowerCost']);
		$overPowerCost = intval($_POST['overPowerCost']);
		if ( ! in_array( $underPowerCost, $safe_cost_values, true ) ) {$underPowerCost = '';}
		if ( ! in_array( $normalPowerCost, $safe_cost_values, true ) ) {$normalPowerCost = '';}
		if ( ! in_array( $overPowerCost, $safe_cost_values, true ) ) {$overPowerCost = '';}


		//Energy Consumption
		$safe_cons_values = range(0, 2000, 5);
		$energyConsumptionMinVal = intval($_POST['energyConsumptionMinVal']);
		$energyConsumptionMaxVal = intval($_POST['energyConsumptionMaxVal']);
		$energyConsumptionMeanVal = intval($_POST['energyConsumptionMeanVal']);
		$energyConsumptionVarianceVal = intval($_POST['energyConsumptionVarianceVal']);
		if ( ! in_array( $energyConsumptionMinVal, $safe_cons_values, true ) ) {$energyConsumptionMinVal = '';}
		if ( ! in_array( $energyConsumptionMaxVal, $safe_cons_values, true ) ) {$energyConsumptionMaxVal = '';}
		if ( ! in_array( $energyConsumptionMeanVal, $safe_cons_values, true ) ) {$energyConsumptionMeanVal = '';}
		if ( ! in_array( $energyConsumptionVarianceVal, $safe_cons_values, true ) ) {$energyConsumptionVarianceVal = '';}
	}

	$asset_id = wp_insert_post($asset_information);
	update_post_meta( $asset_id, 'wpunity_asset3d_pathData', $gameSlug );

	$mtlFile = $_FILES['mtlFileInput'];
	$objFile = $_FILES['objFileInput'];
	$textureFile = $_FILES['textureFileInput'];
	$screenShotFile = $_POST['sshotFileInput'];

	if($asset_id) {
		//Upload All files as attachments of asset
		$mtlFile_id = wpunity_upload_Assetimg( $mtlFile, $asset_id, $gameSlug);
		$objFile_id = wpunity_upload_Assetimg( $objFile, $asset_id, $gameSlug);
		$textureFile_id = wpunity_upload_Assetimg( $textureFile, $asset_id, $gameSlug);
		$screenShotFile_id = wpunity_upload_Assetimg64($screenShotFile, $asset_information['post_title'], $asset_id, $gameSlug);

		//Set value of attachment IDs at custom fields
		update_post_meta( $asset_id, 'wpunity_asset3d_mtl', $mtlFile_id );
		update_post_meta( $asset_id, 'wpunity_asset3d_obj', $objFile_id );
		update_post_meta( $asset_id, 'wpunity_asset3d_diffimage', $textureFile_id );
		update_post_meta( $asset_id, 'wpunity_asset3d_screenimage', $screenShotFile_id );


		wp_redirect(esc_url( get_permalink($editgamePage[0]->ID) . $parameter_pass . $project_id ));
		exit;
	}

}

get_header(); ?>

    <div class="EditPageHeader">
        <h1 class="mdc-typography--display1 mdc-theme--text-primary-on-light"><?php echo $game_post->post_title; ?></h1>
    </div>

    <span class="mdc-typography--caption">
        <i class="material-icons mdc-theme--text-icon-on-background AlignIconToBottom" title="Add category title & icon"><?php echo $game_type_obj->icon; ?> </i>&nbsp;<?php echo $game_type_obj->string; ?></span>

    <hr class="mdc-list-divider">

    <ul class="EditPageBreadcrumb">
        <li><a class="mdc-typography--caption mdc-theme--primary" href="<?php echo esc_url( get_permalink($allGamesPage[0]->ID)); ?>" title="Go back to Project selection">Home</a></li>
        <li><i class="material-icons EditPageBreadcrumbArr mdc-theme--text-hint-on-background">arrow_drop_up</i></li>
        <li><a class="mdc-typography--caption mdc-theme--primary" href="<?php echo esc_url( get_permalink($editgamePage[0]->ID) . $parameter_pass . $project_id ); ?>" title="Go back to Project editor">Project Editor</a></li>
        <li><i class="material-icons EditPageBreadcrumbArr mdc-theme--text-hint-on-background">arrow_drop_up</i></li>
        <li class="mdc-typography--caption"><span class="EditPageBreadcrumbSelected">3D Asset Creator</span></li>
    </ul>

    <h2 class="mdc-typography--headline mdc-theme--text-primary-on-light"><span>Create a new 3D asset</span></h2>

    <form name="3dAssetForm" id="3dAssetForm" method="POST" enctype="multipart/form-data">

        <div class="mdc-layout-grid">

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12">

                <div id="category-select" class="mdc-select" role="listbox" tabindex="0" style="min-width: 100%;">
                    <i class="material-icons mdc-theme--text-icon-on-light">web_asset</i>&nbsp; <span id="currently-selected" class="mdc-select__selected-text mdc-typography--subheading2">Select a category</span>
                    <div class="mdc-simple-menu mdc-select__menu">
                        <ul class="mdc-list mdc-simple-menu__items">

                            <li class="mdc-list-item mdc-theme--text-primary-on-light" role="option" id="categories" aria-disabled="true" style="pointer-events: none;">
                                Select a category
                            </li>
							<?php
							$myGameType = 1;
							$all_game_types = get_the_terms( $project_id, 'wpunity_game_type' );
							$game_type_slug = $all_game_types[0]->slug;
							if($game_type_slug == 'energy_games'){$myGameType=2;}
							$args = array(
								'hide_empty' => false,
								'meta_query' => array(
									array(
										'key'       => 'wpunity_assetcat_gamecat',
										'value'     => $myGameType,
										'compare'   => '='
									)
								));
							$cat_terms = get_terms('wpunity_asset3d_cat', $args);
							foreach ( $cat_terms as $term ) { ?>

                                <li class="mdc-list-item" role="option" data-cat-desc="<?php echo $term->description; ?>" data-cat-slug="<?php echo $term->slug; ?>" id="<?php echo $term->term_id?>" tabindex="0">
									<?php echo $term->name; ?>
                                </li>

							<?php } ?>

                        </ul>
                    </div>
                </div>

            </div>
            <input id="termIdInput" type="hidden" name="term_id" value="">

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12">
                <span class="mdc-typography--subheading2" id="categoryDescription"></span>
            </div>
        </div>

        <div class="mdc-layout-grid">

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-5">

                <h3 id="physicsTitle" class="mdc-typography--title">Information</h3>

                <div class="mdc-textfield FullWidth mdc-form-field" data-mdc-auto-init="MDCTextfield">
                    <input id="assetTitle" type="text" class="mdc-textfield__input mdc-theme--text-primary-on-light FullWidth" name="assetTitle"
                           aria-controls="title-validation-msg" required minlength="6" maxlength="25" style="box-shadow: none; border-color:transparent;">
                    <label for="assetTitle" class="mdc-textfield__label">
                        Enter a title for your asset
                </div>
                <p class="mdc-textfield-helptext  mdc-textfield-helptext--validation-msg"
                   id="title-validation-msg">
                    Between 6 - 25 characters
                </p>

                <div id="assetDescription" class="mdc-textfield mdc-textfield--multiline" data-mdc-auto-init="MDCTextfield">
                    <textarea id="multi-line" class="mdc-textfield__input" rows="6" cols="40" style="box-shadow: none;" name="assetDesc" form="3dAssetForm"></textarea>
                    <label for="multi-line" class="mdc-textfield__label">Add a description</label>
                </div>

                <!-- FALLBACK: Use this if you cannot validate the above on submit -->
                <!--<select title="I am a title" class="mdc-select" required>
                    <option value="" default selected>Pick a food</option>
                    <option value="grains">Bread, Cereal, Rice, and Pasta</option>
                    <option value="vegetables">Vegetables</option>
                    <optgroup label="Fruits">
                        <option value="apple">Apple</option>
                        <option value="oranges">Orange</option>
                        <option value="banana">Banana</option>
                    </optgroup>
                    <option value="dairy">Milk, Yogurt, and Cheese</option>
                    <option value="meat">Meat, Poultry, Fish, Dry Beans, Eggs, and Nuts</option>
                    <option value="fats">Fats, Oils, and Sweets</option>
                </select>-->

                <hr class="WhiteSpaceSeparator">

                <div id="doorDetailsPanel">
                    <h3 class="mdc-typography--title">Door options</h3>

                    <div class="mdc-layout-grid">

                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">

                            <div id="next-scene-select" class="mdc-select" role="listbox" tabindex="0" style="min-width: 100%;">
                                <span id="currently-selected" class="mdc-select__selected-text mdc-typography--subheading2">Next scene</span>
                                <div class="mdc-simple-menu mdc-select__menu" style="left: 48px; top: 0; transform-origin: center 8px 0; transform: scale(0, 0);">
                                    <ul class="mdc-list mdc-simple-menu__items" style="transform: scale(1, 1);">
                                        <li class="mdc-list-item" role="option" id="scenes" aria-disabled="true">
                                            Next scene
                                        </li>

                                        <li class="mdc-list-item" role="option" id="" tabindex="0">
                                            Dummy
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                            <div id="entry-point-select" class="mdc-select" role="listbox" tabindex="0" style="min-width: 100%;">
                                <span id="currently-selected" class="mdc-select__selected-text mdc-typography--subheading2">Entry point</span>
                                <div class="mdc-simple-menu mdc-select__menu">
                                    <ul class="mdc-list mdc-simple-menu__items">
                                        <li class="mdc-list-item" role="option" id="entryPoints" aria-disabled="true">
                                            Entry point
                                        </li>

                                        <li class="mdc-list-item" role="option" id="" tabindex="0">
                                            Dummy
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>

                        <input id="nextSceneInput" type="hidden" name="next_scete_id" value="" disabled>
                        <input id="entryPointInput" type="hidden" name="entry_point_id" value="" disabled>
                    </div>
                </div>

                <div id="poiImgDetailsPanel" style="display: none;">
                    <h3 class="mdc-typography--title">Image POI Details</h3>

                    <div id="poiImgDetailsWrapper">
                        <a id="poiAddFieldBtn" class="mdc-button mdc-button--primary mdc-theme--primary" data-mdc-auto-init="MDCRipple">
                            <i class="material-icons mdc-theme--primary ButtonIcon">add</i> Add Field
                        </a>

                        <hr class="WhiteSpaceSeparator">
                    </div>
                </div>

                <div id="poiVideoDetailsPanel" style="display: none;">
                    <h3 class="mdc-typography--title">Video POI Details</h3>

                    <div id="videoFileInputContainer" class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <label for="videoFileInput"> Select a video</label>
                        <input class="FullWidth" type="file" name="videoFileInput" value="" id="videoFileInput" accept="video/mp4" disabled=""/>
                    </div>
                </div>
            </div>
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-1"></div>
            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">

                <h3 class="mdc-typography--title">Object Properties</h3>

                <ul class="RadioButtonList">
                    <li class="mdc-form-field">
                        <div class="mdc-radio">
                            <input class="mdc-radio__native-control" type="radio" id="fbxRadio"  name="objectTypeRadio" value="fbx">
                            <div class="mdc-radio__background">
                                <div class="mdc-radio__outer-circle"></div>
                                <div class="mdc-radio__inner-circle"></div>
                            </div>
                        </div>
                        <label id="fbxRadio-label" for="fbxRadio" style="margin-bottom: 0;">FBX file</label>
                    </li>
                    <li class="mdc-form-field">
                        <div class="mdc-radio">
                            <input class="mdc-radio__native-control" type="radio" id="mtlRadio" checked="" name="objectTypeRadio" value="mtl">
                            <div class="mdc-radio__background">
                                <div class="mdc-radio__outer-circle"></div>
                                <div class="mdc-radio__inner-circle"></div>
                            </div>
                        </div>
                        <label id="mtlRadio-label" for="mtlRadio" style="margin-bottom: 0;">MTL & OBJ files</label>
                    </li>
                </ul>

                <div class="mdc-layout-grid">

                    <div id="fbxFileInputContainer" class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12" style="display: none;">
                        <label for="fbxFileInput"> Select an FBX file</label>
                        <input class="FullWidth" type="file" name="fbxFileInput" value="" id="fbxFileInput"/>
                    </div>

                    <div id="mtlFileInputContainer" class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <label for="mtlFileInput"> Select an MTL file</label>
                        <input class="FullWidth" type="file" name="mtlFileInput" value="" id="mtlFileInput" accept=".mtl"/>
                    </div>

                    <div id="objFileInputContainer" class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <label  for="objFileInput" > Select an OBJ file</label>
                        <input class="FullWidth" type="file" name="objFileInput" value="" id="objFileInput" accept=".obj"/>
                    </div>
                </div>

                <h3 class="mdc-typography--title" id="objectPreviewTitle" style="display: none;">Object Preview</h3>
                <div id="assetPreviewContainer"></div>

                <div class="mdc-layout-grid">

                    <div id="textureFileInputContainer" class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <label for="textureFileInput"> Select a texture</label><br>
                        <img id="texturePreviewImg" style="width:100px; height:100px" src="<?php echo plugins_url( '../images/ic_texture.png', dirname(__FILE__)  ); ?>">
                        <input class="FullWidth" type="file" name="textureFileInput" value="" id="textureFileInput" accept="image/jpeg"/>
                    </div>

                    <div id="sshotFileInputContainer" class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <label for="sshotFileInput"> Screenshot</label><br>
                        <img id="sshotPreviewImg" style="width:100px; height:100px" src="<?php echo plugins_url( '../images/ic_sshot.png', dirname(__FILE__)  ); ?>">
                        <input class="FullWidth" type="hidden" name="sshotFileInput" value="" id="sshotFileInput" accept="image/jpeg"/>

                        <a style="display: none;" id="createModelScreenshotBtn" type="button" class="mdc-button mdc-button--primary mdc-theme--primary" data-mdc-auto-init="MDCRipple">Create screenshot</a>
                    </div>

                </div>

            </div>

        </div>

        <div id="terrainPanel" class="mdc-layout-grid" style="display: none;">

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">

                <h3 class="mdc-typography--title">Physics</h3>

                <label for="wind-speed-range-label" class="mdc-typography--subheading2">Wind Speed Range:</label>
                <input class="mdc-textfield mdc-textfield__input mdc-theme--accent" type="text" id="wind-speed-range-label" readonly style="box-shadow: none; border-color:transparent; font-weight:bold;">
                <div id="wind-speed-range"></div>
                <input type="hidden" id="physicsWindMinVal" value="" disabled>
                <input type="hidden" id="physicsWindMaxVal" value="" disabled>

                <hr class="WhiteSpaceSeparator">

                <label for="wind-mean-slider-label" class="mdc-typography--subheading2">Wind Speed Mean:</label>
                <input class="mdc-textfield mdc-textfield__input mdc-theme--accent" type="text" id="wind-mean-slider-label" readonly style="box-shadow: none; border-color:transparent; font-weight:bold;">
                <div id="wind-mean-slider"></div>
                <input type="hidden" id="physicsWindMeanVal" value="" disabled>

                <hr class="WhiteSpaceSeparator">

                <label for="wind-variance-slider-label" class="mdc-typography--subheading2">Wind Variance:</label>
                <input class="mdc-textfield mdc-textfield__input mdc-theme--accent" type="text" id="wind-variance-slider-label" readonly style="box-shadow: none; border-color:transparent; font-weight:bold;">
                <div id="wind-variance-slider"></div>
                <input type="hidden" id="physicsWindVarianceVal" value="" disabled="">

            </div>

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">

                <h3 class="mdc-typography--title">Construction Penalties (in $)</h3>

                <div class="mdc-layout-grid">

                    <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <div class="mdc-textfield mdc-textfield--dense FullWidth mdc-form-field" data-mdc-auto-init="MDCTextfield">
                            <input title="Access cost penalty" id="accessCostPenalty" type="number" class="mdc-textfield__input mdc-theme--text-primary-on-light FullWidth" name="accessCostPenalty"
                                   aria-controls="accessCostPenalty-validation-msg" value="0" required min="0" max="10" minlength="1" maxlength="2" style="box-shadow: none; border-color:transparent;" disabled="">
                            <label for="accessCostPenalty" class="mdc-textfield__label">
                                Access Cost
                        </div>
                    </div>
                    <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <div class="mdc-textfield mdc-textfield--dense FullWidth mdc-form-field" data-mdc-auto-init="MDCTextfield">
                            <input title="Archaeological site proximity penalty" id="archProximityPenalty" type="number" class="mdc-textfield__input mdc-theme--text-primary-on-light FullWidth" name="archProximityPenalty"
                                   aria-controls="archProximityPenalty-validation-msg" value="0" required min="0" max="10" minlength="1" maxlength="2" style="box-shadow: none; border-color:transparent;" disabled="">
                            <label for="archProximityPenalty" class="mdc-textfield__label">
                                Arch. site proximity
                        </div>
                    </div>

                    <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <div class="mdc-textfield mdc-textfield--dense FullWidth mdc-form-field" data-mdc-auto-init="MDCTextfield">
                            <input title="Natural reserve proximity penalty" id="naturalReserveProximityPenalty" type="number" class="mdc-textfield__input mdc-theme--text-primary-on-light FullWidth" name="naturalReserveProximityPenalty"
                                   aria-controls="naturalReserveProximityPenalty-validation-msg" value="0" required min="0" max="10" minlength="1" maxlength="2" style="box-shadow: none; border-color:transparent;" disabled="">
                            <label for="naturalReserveProximityPenalty" class="mdc-textfield__label">
                                Natural reserve proximity
                        </div>
                    </div>
                    <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <div class="mdc-textfield mdc-textfield--dense FullWidth mdc-form-field" data-mdc-auto-init="MDCTextfield">
                            <input title="Distance from High Voltage lines penalty" id="hiVoltLineDistancePenalty" type="number" class="mdc-textfield__input mdc-theme--text-primary-on-light FullWidth" name="hiVoltLineDistancePenalty"
                                   aria-controls="hiVoltLineDistancePenalty-validation-msg" value="0" required min="0" max="10" minlength="1" maxlength="2" style="box-shadow: none; border-color:transparent;" disabled="">
                            <label for="hiVoltLineDistancePenalty" class="mdc-textfield__label">
                                Hi-Voltage line distance
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div id="consumerPanel" class="mdc-layout-grid" style="display: none;">

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">

                <h3 class="mdc-typography--title">Energy Consumption</h3>

                <label for="energy-consumption-range-label" class="mdc-typography--subheading2">Energy Consumption Range:</label>
                <input class="mdc-textfield mdc-textfield__input mdc-theme--accent" type="text" id="energy-consumption-range-label" readonly style="box-shadow: none; border-color:transparent; font-weight:bold;">
                <div id="energy-consumption-range"></div>
                <input type="hidden" id="energyConsumptionMinVal" name="energyConsumptionMinVal" value="" disabled>
                <input type="hidden" id="energyConsumptionMaxVal" name="energyConsumptionMaxVal" value="" disabled>

                <hr class="WhiteSpaceSeparator">

                <label for="energy-consumption-mean-slider-label" class="mdc-typography--subheading2">Energy Consumption Mean:</label>
                <input class="mdc-textfield mdc-textfield__input mdc-theme--accent" type="text" id="energy-consumption-mean-slider-label" readonly style="box-shadow: none; border-color:transparent; font-weight:bold;">
                <div id="energy-consumption-mean-slider"></div>
                <input type="hidden" id="energyConsumptionMeanVal" name="energyConsumptionMeanVal" value="" disabled>

                <hr class="WhiteSpaceSeparator">

                <label for="energy-consumption-variance-slider-label" class="mdc-typography--subheading2">Energy Consumption Variance:</label>
                <input class="mdc-textfield mdc-textfield__input mdc-theme--accent" type="text" id="energy-consumption-variance-slider-label" readonly style="box-shadow: none; border-color:transparent; font-weight:bold;">
                <div id="energy-consumption-variance-slider"></div>
                <input type="hidden" id="energyConsumptionVarianceVal" name="energyConsumptionVarianceVal" value="" disabled="">

            </div>

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">

                <h3 class="mdc-typography--title">Energy Consumption Cost (in $)</h3>

                <div class="mdc-layout-grid">

                    <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <div class="mdc-textfield mdc-textfield--dense FullWidth mdc-form-field" data-mdc-auto-init="MDCTextfield">
                            <input title="Underpower cost" id="underPowerCost" type="number" class="mdc-textfield__input mdc-theme--text-primary-on-light FullWidth" name="underPowerCost"
                                   aria-controls="underPowerCost-validation-msg" value="0.5" required min="-5" max="5" minlength="1" maxlength="1" style="box-shadow: none; border-color:transparent;" disabled="">
                            <label for="underPowerCost" class="mdc-textfield__label">
                                Underpowered consumption cost
                        </div>
                    </div>

                    <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <div class="mdc-textfield mdc-textfield--dense FullWidth mdc-form-field" data-mdc-auto-init="MDCTextfield">
                            <input title="Normal power cost" id="normalPowerCost" type="number" class="mdc-textfield__input mdc-theme--text-primary-on-light FullWidth" name="normalPowerCost"
                                   aria-controls="normalPowerCost-validation-msg" value="1" required min="-5" max="5" minlength="1" maxlength="1" style="box-shadow: none; border-color:transparent;" disabled="">
                            <label for="normalPowerCost" class="mdc-textfield__label">
                                Normal power consumption cost
                        </div>
                    </div>

                    <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">
                        <div class="mdc-textfield mdc-textfield--dense FullWidth mdc-form-field" data-mdc-auto-init="MDCTextfield">
                            <input title="Overpower cost" id="overPowerCost" type="number" class="mdc-textfield__input mdc-theme--text-primary-on-light FullWidth" name="overPowerCost"
                                   aria-controls="overPowerCost-validation-msg" value="0" required min="-5" max="5" minlength="1" maxlength="1" style="box-shadow: none; border-color:transparent;" disabled="">
                            <label for="overPowerCost" class="mdc-textfield__label">
                                Overpowered consumption cost
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div id="producerPanel" class="mdc-layout-grid">

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-12">

                <h3 class="mdc-typography--title">Power Production Chart</h3>

                <div class="PlotContainerStyle">
                    <div id="producer-chart" class="ProducerChartStyle"></div>
                </div>

                <div id="powerProductionValuesGroup" class="PowerProductionGroupStyle">
                    <span>0</span>
                    <span>0</span>
                    <span>0</span>
                    <span>0.5</span>
                    <span>0.5</span>
                    <span>0.5</span>
                    <span>1</span>
                    <span>1</span>
                    <span>1</span>
                    <span>1.5</span>
                    <span>1.5</span>

                    <span>2</span>
                    <span>2</span>
                    <span>2</span>
                    <span>3</span>
                    <span>3</span>
                    <span>3</span>
                    <span>3.5</span>
                    <span>3.5</span>
                    <span>4</span>
                    <span>4</span>
                    <span>4</span>

                    <span>4</span>
                    <span>5</span>
                    <span>5.5</span>
                    <span>5.5</span>
                    <span>6</span>
                    <span>6</span>
                </div>

                <input type="hidden" id="producerPowerProductionVal" value="" disabled>
            </div>

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">

                <h3 class="mdc-typography--title">Producer Options</h3>

                <label for="producer-turbine-size-slider-label" class="mdc-typography--subheading2">Size:</label>
                <input class="mdc-textfield mdc-textfield__input mdc-theme--accent" type="text" id="producer-turbine-size-slider-label" readonly style="box-shadow: none; border-color:transparent; font-weight:bold;">
                <div id="producer-turbine-size-slider"></div>
                <input type="hidden" id="producerTurbineSizeVal" value="" disabled>

                <hr class="WhiteSpaceSeparator">

                <label for="producer-damage-coeff-slider-label" class="mdc-typography--subheading2">Damage Coefficient:</label>
                <input class="mdc-textfield mdc-textfield__input mdc-theme--accent" type="text" id="producer-damage-coeff-slider-label" readonly style="box-shadow: none; border-color:transparent; font-weight:bold;">
                <div id="producer-damage-coeff-slider"></div>
                <input type="hidden" id="producerDmgCoeffVal" value="" disabled>

            </div>

            <div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-6">

                <h3 class="mdc-typography--title">Producer Costs</h3>

                <label for="producer-cost-slider-label" class="mdc-typography--subheading2">Producer Cost:</label>
                <input class="mdc-textfield mdc-textfield__input mdc-theme--accent" type="text" id="producer-cost-slider-label" readonly style="box-shadow: none; border-color:transparent; font-weight:bold;">
                <div id="producer-cost-slider"></div>
                <input type="hidden" id="producerCostVal" value="" disabled>

                <hr class="WhiteSpaceSeparator">

                <label for="producer-repair-cost-slider-label" class="mdc-typography--subheading2">Producer Repaid Cost:</label>
                <input class="mdc-textfield mdc-textfield__input mdc-theme--accent" type="text" id="producer-repair-cost-slider-label" readonly style="box-shadow: none; border-color:transparent; font-weight:bold;">
                <div id="producer-repair-cost-slider"></div>
                <input type="hidden" id="producerRepairCostVal" value="" disabled>


            </div>
        </div>


        <hr class="WhiteSpaceSeparator">

		<?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
        <input type="hidden" name="submitted" id="submitted" value="true" />

        <button id="formSubmitBtn" style="float: right; margin-bottom: 24px; width: 100%; height: 48px; display: none;" class="mdc-button mdc-elevation--z2 mdc-button--raised mdc-button--primary" data-mdc-auto-init="MDCRipple" type="submit">
            Create asset
        </button>


    </form>

    <script type="text/javascript">
        'use strict';

        var mdc = window.mdc;
        mdc.autoInit();

        wpunity_reset_panels();

        var fbxInputContainer = jQuery('#fbxFileInputContainer');
        var fbxInput = jQuery('#fbxFileInput');
        var mtlInputContainer = jQuery('#mtlFileInputContainer');
        var mtlInput = jQuery('#mtlFileInput');
        var objInputContainer = jQuery('#objFileInputContainer');
        var objInput = jQuery('#objFileInput');
        var textureInputContainer = jQuery('#textureFileInputContainer');
        var textureInput = jQuery('#textureFileInput');
        var texturePreviewDefaultImg = document.getElementById("texturePreviewImg").src;
        var sshotInput = jQuery('#sshotFileInput');
        var sshotPreviewDefaultImg = document.getElementById("sshotPreviewImg").src;
        var createScreenshotBtn = jQuery("#createModelScreenshotBtn");

        var mtlFileContent = '';
        var objFileContent = '';
        var textureFileContent = '';
        var fbxFileContent = '';
        var previewRenderer;

        createScreenshotBtn.click(function() {
            wpunity_create_model_sshot(previewRenderer);
        });

        // Flot options
        var plotOptions = {
            xaxis: {
                min: 0,
                max: 27,
                ticks: 27
            },
            yaxis: {
                min: -0.5,
                max: 6.5
            },
            tooltip: true,
            
            series: {
                color: "#ff4081",
                lines: {
                    show: true
                },
                points: {
                    show: true
                },

                shadowSize: 0

            },
            grid: { hoverable: true }

        };
        var plotData = [{ data: [0,0] }];
        for (var i=0; i < 28; i++) {
            plotData[0].data[i] = [i, 5];
        }

        (function() {
            var MDCSelect = mdc.select.MDCSelect;
            var categoryDropdown = document.getElementById('category-select');
            var nextSceneDropdown = document.getElementById('next-scene-select');
            var entryPointDropdown = document.getElementById('entry-point-select');

            var categorySelect = MDCSelect.attachTo(categoryDropdown);
            var nextSceneSelect = MDCSelect.attachTo(nextSceneDropdown);
            var entryPointSelect = MDCSelect.attachTo(entryPointDropdown);

            nextSceneDropdown.addEventListener('MDCSelect:change', function() {
                jQuery("#nextSceneInput").attr( "value", nextSceneSelect.selectedOptions[0].getAttribute("id") );
            });

            entryPointDropdown.addEventListener('MDCSelect:change', function() {
                jQuery("#entryPointInput").attr( "value", entryPointSelect.selectedOptions[0].getAttribute("id") );
            });

            categoryDropdown.addEventListener('MDCSelect:change', function() {
                var item = categorySelect.selectedOptions[0];
                var index = categorySelect.selectedIndex;

                jQuery("#formSubmitBtn").show();

                wpunity_reset_panels();

                var descText = document.getElementById('categoryDescription');
                descText.innerHTML = categorySelect.selectedOptions[0].getAttribute("data-cat-desc");

                jQuery("#termIdInput").attr( "value", categorySelect.selectedOptions[0].getAttribute("id") );

                var cat = categorySelect.selectedOptions[0].getAttribute("data-cat-slug");

                switch(cat) {
                    // Archaeology cases
                    case 'doors':

                        jQuery("#doorDetailsPanel").show();

                        jQuery("#nextSceneInput").removeAttr("disabled");
                        jQuery("#entryPointInput").removeAttr("disabled");

                        break;
                    case 'dynamic3dmodels':


                        break;
                    case 'pois_imagetext':

                        jQuery("#assetDescription").hide();
                        jQuery("#poiImgDetailsPanel").show();

                        break;
                    case 'pois_video':

                        jQuery("#poiVideoDetailsPanel").show();
                        jQuery("#videoFileInput").removeAttr("disabled");

                        break;

                    // Energy cases
                    case 'terrain':
                        jQuery("#terrainPanel").show();
                        jQuery("#physicsWindMinVal").removeAttr("disabled");
                        jQuery("#physicsWindMaxVal").removeAttr("disabled");
                        jQuery("#physicsWindMeanVal").removeAttr("disabled");
                        jQuery("#physicsWindVarianceVal").removeAttr("disabled");

                        jQuery("#accessCostPenalty").removeAttr("disabled");
                        jQuery("#archProximityPenalty").removeAttr("disabled");
                        jQuery("#naturalReserveProximityPenalty").removeAttr("disabled");
                        jQuery("#hiVoltLineDistancePenalty").removeAttr("disabled");

                        break;
                    case 'consumer':
                        jQuery("#consumerPanel").show();
                        jQuery("#energyConsumptionMinVal").removeAttr("disabled");
                        jQuery("#energyConsumptionMaxVal").removeAttr("disabled");
                        jQuery("#energyConsumptionMeanVal").removeAttr("disabled");
                        jQuery("#energyConsumptionVarianceVal").removeAttr("disabled");

                        jQuery("#overPowerCost").removeAttr("disabled");
                        jQuery("#normalPowerCost").removeAttr("disabled");
                        jQuery("#underPowerCost").removeAttr("disabled");


                        break;
                    case 'producer':
                        jQuery("#producerPanel").show();
                        jQuery("#producerAirSpeedVal").removeAttr("disabled");
                        jQuery("#producerPowerProductionVal").removeAttr("disabled");
                        jQuery("#producerTurbineSizeVal").removeAttr("disabled");
                        jQuery("#producerDmgCoeffVal").removeAttr("disabled");
                        jQuery("#producerCostVal").removeAttr("disabled");
                        jQuery("#producerRepairCostVal").removeAttr("disabled");

                        jQuery("#producer-chart").plot(plotData, plotOptions).data("plot");

                        break;
                    default:

                }
                console.log(cat, index);
            });
        })();

        fbxInput.change(function() {
            document.getElementById("assetPreviewContainer").innerHTML = "";

            if (wpunity_extract_file_extension(fbxInput.val()) === 'fbx') {

            } else {
                document.getElementById("fbxFileInput").value = "";
            }
        });

        mtlInput.click(function() {
            document.getElementById("mtlFileInput").value = "";
            wpunity_read_file('', 'mtl', wpunity_load_file_callback);
            wpunity_reset_sshot_field();
        });
        mtlInput.change(function() {
            document.getElementById("assetPreviewContainer").innerHTML = "";

            if (wpunity_extract_file_extension(mtlInput.val()) === 'mtl') {
                wpunity_read_file(document.getElementById('mtlFileInput').files[0], 'mtl', wpunity_load_file_callback);
            }
        });

        objInput.click(function() {
            document.getElementById("objFileInput").value = "";
            wpunity_read_file('', 'obj', wpunity_load_file_callback);
            wpunity_reset_sshot_field();
        });
        objInput.change(function() {
            document.getElementById("assetPreviewContainer").innerHTML = "";

            if (wpunity_extract_file_extension(objInput.val()) === 'obj') {
                wpunity_read_file(document.getElementById('objFileInput').files[0], 'obj', wpunity_load_file_callback);
            }
        });

        textureInput.click(function() {
            document.getElementById("textureFileInput").value = "";
            jQuery("#texturePreviewImg").attr('src', texturePreviewDefaultImg);
            textureFileContent = '';
            document.getElementById("assetPreviewContainer").innerHTML = "";
            previewRenderer = wu_3d_view_main('before', '', mtlFileContent, objFileContent, '', document.getElementById('assetTitle').value, 'assetPreviewContainer');
        });
        textureInput.change(function() {
            document.getElementById("assetPreviewContainer").innerHTML = "";

            if (wpunity_extract_file_extension(textureInput.val()) === 'jpg') {
                wpunity_read_file(document.getElementById('textureFileInput').files[0], 'texture', wpunity_load_file_callback);
            }
        });

        function wpunity_create_model_sshot(renderer) {
            document.getElementById("sshotPreviewImg").src = renderer.domElement.toDataURL("image/jpeg");
            document.getElementById("sshotFileInput").value = renderer.domElement.toDataURL("image/jpeg");
        }

        function wpunity_reset_sshot_field() {
            document.getElementById("sshotPreviewImg").src = sshotPreviewDefaultImg;
            document.getElementById("sshotFileInput").value = "";
            createScreenshotBtn.hide();
            jQuery("#objectPreviewTitle").hide();
        }

        jQuery( function() {

            // FBX / MTL Toggles
            jQuery( "input[name=objectTypeRadio]" ).click(function() {

                var objectType = jQuery('input[name=objectTypeRadio]:checked').val();

                if (objectType === 'fbx') {
                    wpunity_clear_asset_files();
                    fbxInputContainer.show();
                    mtlInputContainer.hide();
                    objInputContainer.hide();
                }
                else if (objectType === 'mtl') {
                    wpunity_clear_asset_files();
                    fbxInputContainer.hide();
                    mtlInputContainer.show();
                    objInputContainer.show();
                }
            });


            // Sliders
            var windSpeedRangeSlider = wpunity_create_slider_component("#wind-speed-range", true, {min: 0, max: 40, values:[0, 40], valIds:["#physicsWindMinVal", "#physicsWindMaxVal" ], units:"m/s"});
            var windMeanSlider = wpunity_create_slider_component("#wind-mean-slider", false, {min: 0, max: 40, value: 14, valId:"#physicsWindMeanVal", units:"m/s"});
            var windVarianceSlider = wpunity_create_slider_component("#wind-variance-slider", false, {min: 1, max: 100, value: 30, valId:"#physicsWindVarianceVal", units:""});

            var energyConsumptionRangeSlider = wpunity_create_slider_component("#energy-consumption-range", true, {min: 0, max: 2000, values:[50, 150], valIds:["#energyConsumptionMinVal", "#energyConsumptionMaxVal" ], step: 5, units:"kW"});
            var energyConsumptionMeanSlider = wpunity_create_slider_component("#energy-consumption-mean-slider", false, {min: 0, max: 2000, value: 100, valId:"#energyConsumptionMeanVal", step: 5, units:"kW"});
            var energyConsumptionVarianceSlider = wpunity_create_slider_component("#energy-consumption-variance-slider", false, {min: 5, max: 1000, value: 50, valId:"#energyConsumptionVarianceVal", step: 5, units:""});


            var index = 0;
            jQuery( "#powerProductionValuesGroup > span" ).each(function() {
                // read initial values from markup and remove that
                var value = parseInt( jQuery( this ).text(), 10 );

                jQuery( this ).empty().slider({
                    value: value,
                    min: 0,
                    max: 6,
                    range: "min",
                    animate: true,
                    step: 0.5,
                    orientation: "vertical",
                    sliderId: index,
                    stop: function( event, ui ) {

                        var val = jQuery( this ).slider("option", "value");
                        plotData[0].data[jQuery( this ).slider("option", "sliderId")] = [jQuery( this ).slider("option", "sliderId"), val];
                        jQuery("#producer-chart").plot(plotData, plotOptions).data("plot");

                        jQuery("#producerPowerProductionVal").attr( "value", JSON.stringify(plotData[0].data) );

                    },
                    create: function( event, ui ) {

                        var val = jQuery( this ).slider("option", "value");
                        plotData[0].data[jQuery( this ).slider("option", "sliderId")] = [jQuery( this ).slider("option", "sliderId"), val];
                        jQuery("#producer-chart").plot(plotData, plotOptions).data("plot");

                        jQuery("#producerPowerProductionVal").attr( "value", JSON.stringify(plotData[0].data) );

                    }
                });

                jQuery( this ).attr("id", "power-production-value-"+index);
                index++;
            });

            /*var producerAirSpeedSlider = wpunity_create_slider_component("#producer-air-speed-slider", false, {min: 0, max: 27, value: 5, valId:"#producerAirSpeedVal", step: 1, units:"m/sec"});
             var producerPowerProductionSlider = wpunity_create_slider_component("#producer-power-production-slider", false, {min: 0, max: 6, value: 1, valId:"#producerPowerProductionVal", step: 1, units:"MW"});*/
            var producerTurbineSizeSlider = wpunity_create_slider_component("#producer-turbine-size-slider", false, {min: 3, max: 250, value: 90, valId:"#producerTurbineSizeVal", step: 1, units:"m"});
            var producerDmgCoeffSlider = wpunity_create_slider_component("#producer-damage-coeff-slider", false, {min: 0.001, max: 0.02, value: 0.005, valId:"#producerDmgCoeffVal", step: 0.001, units:"Probability / sec"});
            var producerCostSlider = wpunity_create_slider_component("#producer-cost-slider", false, {min: 1, max: 10, value: 3, valId:"#producerCostVal", step: 1, units:"$"});
            var producerRepairCostSlider = wpunity_create_slider_component("#producer-repair-cost-slider", false, {min: 0.5, max: 5, value: 1, valId:"#producerRepairCostVal", step: 0.5, units:"$"});

            // POI Image panels - Add/remove POI inputs
            var poiMaxFields      = 3; // max input boxes allowed
            var poiImgDetailsWrapper         = jQuery("#poiImgDetailsWrapper"); // Fields wrapper
            var addPoiFieldBtn      = jQuery("#poiAddFieldBtn"); // Add button ID
            var i = 0; // Initial text box count

            addPoiFieldBtn.click(function(e){ // On add input button click
                e.preventDefault();
                if(i < poiMaxFields) { // Max input box allowed
                    i++; // Text box increment
                    poiImgDetailsWrapper.append(
                        '<div class="mdc-layout-grid">'+
                        '<div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-10">' +
                        '<input type="file" name="poi-input-file-'+i+'" class="FullWidth" value="" accept="image/jpeg"/>' +
                        '<div class="mdc-textfield mdc-form-field FullWidth " data-mdc-auto-init="MDCTextfield">' +
                        '<input id="poi-input-text-'+i+'" type="text" class="mdc-textfield__input mdc-theme--text-primary-on-light FullWidth" name="poi-input-text-'+i+'" ' +
                        'aria-controls="poi-input-text-validation-msg" minlength="6" maxlength="25" style="box-shadow: none; border-color:transparent;">' +
                        '<label for="poi-input-text-'+i+'" class="mdc-textfield__label">Enter an image description' +
                        '</div>' +
                        '<p class="mdc-textfield-helptext  mdc-textfield-helptext--validation-msg" id="title-validation-msg">Between 6 - 25 characters</p></div>' +
                        '<div class="mdc-layout-grid__cell mdc-layout-grid__cell--span-2"><a href="#" class="remove_field"><i title="Delete field" style="font-size: 36px" class="material-icons">clear</i></a></div></div>'
                    ); // Add input box
                }
                // Run autoInit with noop to suppress warnings.
                mdc.autoInit(document, () => {});
            });

            poiImgDetailsWrapper.on("click",".remove_field", function(e) { // User click on remove text
                e.preventDefault();
                jQuery(this).parent('div').parent('div').remove(); i--;
            })
        } );
    </script>
<?php  get_footer(); ?>