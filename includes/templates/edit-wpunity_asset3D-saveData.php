<?php

function wpunity_create_asset_frontend($assetPGameID,$assetCatID,$assetTitleForm,$assetDescForm,$gameSlug){
    $asset_taxonomies = array(
        'wpunity_asset3d_pgame' => array(
            $assetPGameID,
        ),
        'wpunity_asset3d_cat' => array(
            $assetCatID,
        )
    );

    $asset_information = array(
        'post_title' => $assetTitleForm,
        'post_content' => $assetDescForm,
        'post_type' => 'wpunity_asset3d',
        'post_status' => 'publish',
        'tax_input' => $asset_taxonomies,
    );

    $asset_id = wp_insert_post($asset_information);
    update_post_meta($asset_id, 'wpunity_asset3d_pathData', $gameSlug);

    if($asset_id){return $asset_id;}else{return 0;}
}

function wpunity_update_asset_frontend($asset_inserted_id,$assetTitleForm,$assetDescForm){
    $asset_new_info = array(
        'ID' => $asset_inserted_id,
        'post_title' => $assetTitleForm,
        'post_content' => $assetDescForm,
    );

    wp_update_post($asset_new_info);
    return 1;
}

function wpunity_create_asset_consumerExtra_frontend($asset_newID){
    //Energy Consumption
    $safe_cons_values = range(0, 2000, 5);
    $safe_cons_values2 = range(0, 1000, 5);

    $energyConsumptionMinValForm = intval($_POST['energyConsumptionMinVal']);
    $energyConsumptionMaxValForm = intval($_POST['energyConsumptionMaxVal']);
    $energyConsumptionMeanValForm = intval($_POST['energyConsumptionMeanVal']);
    $energyConsumptionVarianceValForm = intval($_POST['energyConsumptionVarianceVal']);

    if (!in_array($energyConsumptionMinValForm, $safe_cons_values, true)) {$energyConsumptionMinValForm = '';}
    if (!in_array($energyConsumptionMaxValForm, $safe_cons_values, true)) {$energyConsumptionMaxValForm = '';}
    if (!in_array($energyConsumptionMeanValForm, $safe_cons_values, true)) {$energyConsumptionMeanValForm = '';}
    if (!in_array($energyConsumptionVarianceValForm, $safe_cons_values2, true)) {$energyConsumptionVarianceValForm = '';}

    $energyConsumption = array('min' => $energyConsumptionMinValForm, 'max' => $energyConsumptionMaxValForm, 'mean' => $energyConsumptionMeanValForm, 'var' => $energyConsumptionVarianceValForm);

    update_post_meta($asset_newID, 'wpunity_energyConsumption', $energyConsumption);
}

function wpunity_create_asset_terrainExtra_frontend($asset_newID){
    $underPowerIncomeValForm = floatval($_POST['underPowerIncomeVal']);
    $correctPowerIncomeValForm = floatval($_POST['correctPowerIncomeVal']);
    $overPowerIncomeValForm = floatval($_POST['overPowerIncomeVal']);
    $accessCostPenaltyForm = intval($_POST['accessCostPenalty']);
    $archProximityPenaltyForm = intval($_POST['archProximityPenalty']);
    $naturalReserveProximityPenaltyForm = intval($_POST['naturalReserveProximityPenalty']);
    $hiVoltLineDistancePenaltyForm = intval($_POST['hiVoltLineDistancePenalty']);
    $physicsWindMinValForm = intval($_POST['physicsWindMinVal']);
    $physicsWindMaxValForm = intval($_POST['physicsWindMaxVal']);
    $physicsWindMeanValForm = intval($_POST['physicsWindMeanVal']);
    $physicsWindVarianceValForm = intval($_POST['physicsWindVarianceVal']);

    //Income (in $)
    $safe_income_values = range(-5, 5, 0.5);
    if (!in_array($underPowerIncomeValForm, $safe_income_values, true)) {$underPowerIncomeValForm = '';}
    if (!in_array($correctPowerIncomeValForm, $safe_income_values, true)) {$correctPowerIncomeValForm = '';}
    if (!in_array($overPowerIncomeValForm, $safe_income_values, true)) {$overPowerIncomeValForm = '';}

    $energyConsumptionIncome = array('under' => $underPowerIncomeValForm, 'correct' => $correctPowerIncomeValForm, 'over' => $overPowerIncomeValForm);

    //Construction Penalties (in $)
    $safe_penalty_values = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
    if (!in_array($accessCostPenaltyForm, $safe_penalty_values, true)) {$accessCostPenaltyForm = '';}
    if (!in_array($archProximityPenaltyForm, $safe_penalty_values, true)) {$archProximityPenaltyForm = '';}
    if (!in_array($naturalReserveProximityPenaltyForm, $safe_penalty_values, true)) {$naturalReserveProximityPenaltyForm = '';}
    if (!in_array($hiVoltLineDistancePenaltyForm, $safe_penalty_values, true)) {$hiVoltLineDistancePenaltyForm = '';}

    $constructionPenalties = array('access' => $accessCostPenaltyForm, 'arch' => $archProximityPenaltyForm, 'natural' => $naturalReserveProximityPenaltyForm, 'hiVolt' => $hiVoltLineDistancePenaltyForm);

    //Physics
    $safe_physics_values = range(0, 40, 1);
    $safe_physics_values2 = range(1, 100, 1);//for Wind Variance
    if (!in_array($physicsWindMinValForm, $safe_physics_values, true)) {$physicsWindMinValForm = '';}
    if (!in_array($physicsWindMaxValForm, $safe_physics_values, true)) {$physicsWindMaxValForm = '';}
    if (!in_array($physicsWindMeanValForm, $safe_physics_values, true)) {$physicsWindMeanValForm = '';}
    if (!in_array($physicsWindVarianceValForm, $safe_physics_values2, true)) {$physicsWindVarianceValForm = '';}

    $physicsValues = array('min' => $physicsWindMinValForm, 'max' => $physicsWindMaxValForm, 'mean' => $physicsWindMeanValForm, 'variance' => $physicsWindVarianceValForm);

    update_post_meta($asset_newID, 'wpunity_energyConsumptionIncome', $energyConsumptionIncome);
    update_post_meta($asset_newID, 'wpunity_physicsValues', $physicsValues);
    update_post_meta($asset_newID, 'wpunity_constructionPenalties', $constructionPenalties);
}

function wpunity_create_asset_producerExtra_frontend($asset_newID){
    $producerTurbineSizeValForm = intval($_POST['producerTurbineSizeVal']);
    $producerDmgCoeffValForm = floatval($_POST['producerDmgCoeffVal']);
    $producerCostValForm = intval($_POST['producerCostVal']);
    $producerRepairCostValForm = floatval($_POST['producerRepairCostVal']);
    $producerClassValForm = $_POST['producerClassVal'];
    $producerWindSpeedClassValForm = floatval($_POST['producerWindSpeedClassVal']);
    $producerMaxPowerValForm = floatval($_POST['producerMaxPowerVal']);
    $producerPowerProductionValForm = $_POST['producerPowerProductionVal'];

    //Producer Options-Costs
    $safe_opt_val = range(3, 250, 1);
    $safe_opt_dmg = range(0.001, 0.02, 0.001);
    $safe_opt_cost = range(1, 10, 1);
    $safe_opt_repaid = range(0.5, 5, 0.5);
    if (!in_array($producerTurbineSizeValForm, $safe_opt_val, true)) {$producerTurbineSizeValForm = '';}
    if (!in_array($producerDmgCoeffValForm, $safe_opt_dmg, true)) {$producerDmgCoeffValForm = '';}
    if (!in_array($producerCostValForm, $safe_opt_cost, true)) {$producerCostValForm = '';}
    if (!in_array($producerRepairCostValForm, $safe_opt_repaid, true)) {$producerRepairCostValForm = '';}

    $producerOptCosts = array('size' => $producerTurbineSizeValForm, 'dmg' => $producerDmgCoeffValForm, 'cost' => $producerCostValForm, 'repaid' => $producerRepairCostValForm);
    $producerOptGen = array('class' => $producerClassValForm, 'speed' => $producerWindSpeedClassValForm, 'power' => $producerMaxPowerValForm);

    update_post_meta($asset_newID, 'wpunity_producerPowerProductionVal', $producerPowerProductionValForm);
    update_post_meta($asset_newID, 'wpunity_producerOptCosts', $producerOptCosts);
    update_post_meta($asset_newID, 'wpunity_producerOptGen', $producerOptGen);
}

function wpunity_create_asset_poisITExtra_frontend($asset_newID){
    $asset_featured_imageForm =  $_FILES['poi-img-featured-image'];
    $attachment_id = wpunity_upload_img_vid( $asset_featured_imageForm, $asset_newID);
    set_post_thumbnail( $asset_newID, $attachment_id );
}

function wpunity_create_asset_poisVideoExtra_frontend($asset_newID){
    $asset_featured_imageForm =  $_FILES['poi-video-featured-image'];
    $asset_videoForm = $_FILES['videoFileInput'];

    $attachment_id = wpunity_upload_img_vid( $asset_featured_imageForm, $asset_newID);
    set_post_thumbnail( $asset_newID, $attachment_id );
    
    $attachment_video_id = wpunity_upload_img_vid( $asset_videoForm, $asset_newID);
    update_post_meta( $asset_newID, 'wpunity_asset3d_video', $attachment_video_id );
}

function wpunity_create_asset_moleculeExtra_frontend($asset_newID){
    $moleculeChemicalType = $_POST['moleculeChemicalType'];
    $moleculeFunctionalGroupInput = $_POST['moleculeFunctionalGroupInput'];
    $moleculeFluidViscosity = floatval($_POST['molecule-fluid-viscosity-slider-label']);
    $moleculeFluidColorVal = $_POST['moleculeFluidColorVal'];

    update_post_meta($asset_newID, 'wpunity_molecule_ChemicalTypeVal', $moleculeChemicalType);
    update_post_meta($asset_newID, 'wpunity_molecule_FunctionalGroupVal', $moleculeFunctionalGroupInput);
    update_post_meta($asset_newID, 'wpunity_molecule_FluidViscosityVal', $moleculeFluidViscosity);
    update_post_meta($asset_newID, 'wpunity_molecule_FluidColorVal', $moleculeFluidColorVal);

}

function wpunity_create_asset_3DFilesExtra_frontend($asset_newID, $assetTitleForm, $gameSlug){

    $textureNamesIn = [];
    $tContent = [];

    // Texture
    if (isset($_POST['textureFileInput'])) {
        
        foreach (array_keys($_POST['textureFileInput']) as $texture) {
            $tname = str_replace(['.jpg','.png'], '', $texture);
            $tContent[$tname] = $_POST['textureFileInput'][$texture];
            $textureNamesIn[] = $tname;
        }

        $textureNamesOut = [];
        
        for ($i = 0; $i < count($tContent); $i++) {
            
            $textureFile_id = wpunity_upload_Assetimg64(
                $tContent[$textureNamesIn[$i]], 'texture_' . $textureNamesIn[$i] . '_' . $assetTitleForm,
                $asset_newID, $gameSlug);

            $textureFile_filename = basename(get_attached_file($textureFile_id));

            $textureNamesOut[] = $textureFile_filename;

            add_post_meta($asset_newID, 'wpunity_asset3d_diffimage', $textureFile_id);
            
            //update_post_meta($asset_newID, 'wpunity_asset3d_diffimage', $textureFile_id);
        }
    }
   
   
    $mtl_content = $_POST['mtlFileInput'];
    $obj_content = $_POST['objFileInput'];
    
//    $fl = fopen("output_png.txt","w");
    
    // MTL : Open mtl file and replace jpg filename
    if($_POST['mtlFileInput'] && isset($_POST['textureFileInput'])) {
        if(strlen($_POST['mtlFileInput']) > 0) {
            
            for ($k = 0; $k < count($textureNamesIn); $k++) {
//                fwrite($fl , $textureNamesIn[$k]);
    
                $isJPG = false;
                $isPNG = false;
                
                $imageContentLine = substr($tContent[$textureNamesIn[$k]], 0, 20);
                
                if ( strpos($imageContentLine, "jpeg") )
                    $isJPG = true;
                
                if (strpos($imageContentLine, "png"))
                    $isPNG = true;
                
                if ($isJPG)
                    $mtl_content = preg_replace("/.*\bmap_Kd\b.*\b" . $textureNamesIn[$k] . ".jpg\b/ui",
                        "map_Kd " . $textureNamesOut[$k], $mtl_content);
                
                if($isPNG)
                    $mtl_content = preg_replace("/.*\bmap_Kd\b.*\b" . $textureNamesIn[$k] . ".png\b/ui",
                        "map_Kd " . $textureNamesOut[$k], $mtl_content);
    
            }
        }
    }
    
//    fclose($fl);

    // OBJ and MTL
    if (isset($_POST['mtlFileInput']) && isset($_POST['objFileInput'])) {
        if (strlen($_POST['mtlFileInput'])>0 && strlen($_POST['objFileInput'])>0) {
            $mtlFile_id = wpunity_upload_AssetText($mtl_content, 'material' . $assetTitleForm, $asset_newID, $gameSlug);
            $mtlFile_filename = basename(get_attached_file($mtlFile_id));
        
            // OBJ
            $mtlFile_filename_notxt = substr($mtlFile_filename, 0, -4);
            $mtlFile_filename_withMTLext = $mtlFile_filename_notxt . '.mtl';

//    $obj_content = preg_replace("/.*\b" . 'mtllib' . "\b.*\n/ui", "mtllib " . $mtlFile_filename_withMTLext . "\n", $obj_content);
        
            // Search for replace only in the first 500 characters to avoid memory issues
            $obj_contentfirst = preg_replace("/.*\b" . 'mtllib' . "\b.*\n/ui", "mtllib " . $mtlFile_filename_withMTLext . "\n", substr($obj_content, 0, 500));
        
            $obj_content = substr_replace($obj_content, $obj_contentfirst, 0, 500);
        
            $objFile_id = wpunity_upload_AssetText($obj_content, 'obj' . $assetTitleForm, $asset_newID, $gameSlug);
        
            // Set value of attachment IDs at custom fields
            update_post_meta($asset_newID, 'wpunity_asset3d_mtl', $mtlFile_id);
            update_post_meta($asset_newID, 'wpunity_asset3d_obj', $objFile_id);
            //update_post_meta($asset_newID, 'wpunity_asset3d_diffimage', $textureFile_id);
        }
    }
    
    
    // PDB
    if (isset($_POST['pdbFileInput']))
        if (strlen($_POST['pdbFileInput'])>0) {
            $pdbFile_id = wpunity_upload_AssetText($_POST['pdbFileInput'], 'material' . $assetTitleForm, $asset_newID, $gameSlug);
            update_post_meta($asset_newID, 'wpunity_asset3d_pdb', $pdbFile_id);
        }
    
    // SCREENSHOT
    if (isset($_POST['sshotFileInput']) ) {
        if (strlen($_POST['sshotFileInput'])>0) {
            $screenShotFile_id = wpunity_upload_Assetimg64($_POST['sshotFileInput'], $assetTitleForm, $asset_newID, $gameSlug);
            update_post_meta($asset_newID, 'wpunity_asset3d_screenimage', $screenShotFile_id);
        }
    }

}

function wpunity_copy_3Dfiles($asset_newID, $asset_sourceID){
    
    // Get the source post
    $assetpostMeta = get_post_meta($asset_sourceID);
    
    if ($assetpostMeta['wpunity_asset3d_pdb'][0])
        update_post_meta($asset_newID, 'wpunity_asset3d_pdb', $assetpostMeta['wpunity_asset3d_pdb'][0]);
    
    if ($assetpostMeta['wpunity_asset3d_mtl'][0])
        update_post_meta($asset_newID, 'wpunity_asset3d_mtl', $assetpostMeta['wpunity_asset3d_mtl'][0]);
    
    if($assetpostMeta['wpunity_asset3d_obj'][0])
        update_post_meta($asset_newID, 'wpunity_asset3d_obj', $assetpostMeta['wpunity_asset3d_obj'][0]);
    
    if($assetpostMeta['wpunity_asset3d_screenimage'][0])
        update_post_meta($asset_newID, 'wpunity_asset3d_screenimage', $assetpostMeta['wpunity_asset3d_screenimage'][0]);
    
    if (count($assetpostMeta['wpunity_asset3d_diffimage']) > 0) {
        delete_post_meta($asset_newID, 'wpunity_asset3d_diffimage');
        for ($m = 0; $m < count($assetpostMeta['wpunity_asset3d_diffimage']); $m++)
            add_post_meta($asset_newID, 'wpunity_asset3d_diffimage', $assetpostMeta['wpunity_asset3d_diffimage'][$m]);
    }
}
?>