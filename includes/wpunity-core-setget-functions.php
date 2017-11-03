<?php

//GET page by given type (depending the template) - breacrumb and links for front-end
function wpunity_getEditpage($type){
	if($type=='game'){
		$edit_pages = get_pages(array(
			'hierarchical' => 0,
			'parent' => -1,
			'meta_key' => '_wp_page_template',
			'meta_value' => '/templates/edit-wpunity_game.php'
		));
		return $edit_pages;
	}elseif($type=='scene'){
		$edit_pages = get_pages(array(
			'hierarchical' => 0,
			'parent' => -1,
			'meta_key' => '_wp_page_template',
			'meta_value' => '/templates/edit-wpunity_scene.php'
		));
		return $edit_pages;
	}elseif($type=='scene2D'){
		$edit_pages = get_pages(array(
			'hierarchical' => 0,
			'parent' => -1,
			'meta_key' => '_wp_page_template',
			'meta_value' => '/templates/edit-wpunity_scene2D.php'
		));
		return $edit_pages;
	}elseif($type=='allgames'){
		$edit_pages = get_pages(array(
			'hierarchical' => 0,
			'parent' => -1,
			'meta_key' => '_wp_page_template',
			'meta_value' => '/templates/open-wpunity_game.php'
		));
		return $edit_pages;
	}elseif($type=='sceneExam'){
		$edit_pages = get_pages(array(
			'hierarchical' => 0,
			'parent' => -1,
			'meta_key' => '_wp_page_template',
			'meta_value' => '/templates/edit-wpunity_sceneExam.php'
		));
		return $edit_pages;
	}elseif($type=='asset'){
		$edit_pages = get_pages(array(
			'hierarchical' => 0,
			'parent' => -1,
			'meta_key' => '_wp_page_template',
			'meta_value' => '/templates/edit-wpunity_asset3D.php'
		));
		return $edit_pages;
	}else{
		return false;
	}

}

//==========================================================================================================================================

//Get Settings Values (Generic Yaml Patterns

//Get 'Folder.meta Pattern'
function wpunity_getFolderMetaPattern(){
	$yamloptions = get_option( 'yaml_settings' );
	return $yamloptions["wpunity_folder_meta_pat"];;
}

//Get 'each_scene.unity meta pattern'
function wpunity_getSceneUnityMetaPattern(){
	$yamloptions = get_option( 'yaml_settings' );
	return $yamloptions["wpunity_scene_meta_pat"];
}

//Get 'obj.meta Pattern'
function wpunity_getYaml_obj_dotmeta_pattern(){
	$yamloptions = get_option( 'yaml_settings' );
	return $yamloptions["wpunity_obj_meta_pat"];
}

//Get 'jpg.meta Pattern'
function wpunity_getYaml_jpg_dotmeta_pattern(){
	$yamloptions = get_option( 'yaml_settings' );
	return $yamloptions["wpunity_jpg_meta_pat"];
}

//Get 'The jpg sprite meta pattern'
function wpunity_getYaml_jpg_sprite_pattern(){
	$yamloptions = get_option( 'yaml_settings' );
	return $yamloptions["wpunity_jpgsprite_meta_pat"];
}

//Get 'Material (.mat) Pattern'
function wpunity_getYaml_mat_pattern(){
	$yamloptions = get_option( 'yaml_settings' );
	return $yamloptions["wpunity_mat_pat"];
}

//Get 'mat.meta Pattern'
function wpunity_getYaml_mat_dotmeta_pattern(){
	$yamloptions = get_option( 'yaml_settings' );
	return $yamloptions["wpunity_mat_meta_pat"];
}

//==========================================================================================================================================

//TODO check them

function wpunity_fetch_game_assets_action_callback(){

	// Output the directory listing as JSON
	header('Content-type: application/json');

	$response = wpunity_getAllassets_byGameProject($_POST['gameProjectSlug']);

	for ($i=0; $i<count($response); $i++){
		$response[$i][name] = $response[$i][assetName];
		$response[$i][type] = 'file';
		$response[$i][path] = $response[$i][objPath];

		// Find kb size
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
			"items" => $response
		)
	);

	echo $jsonResp;
	wp_die();
}

function wpunity_getAllassets_byGameProject($gameProjectSlug){

	$allAssets = [];

	$queryargs = array(
		'post_type' => 'wpunity_asset3d',
		'posts_per_page' => -1,
		'tax_query' => array(
			array(
				'taxonomy' => 'wpunity_asset3d_pgame',
				'field' => 'slug',
				'terms' => $gameProjectSlug
			)
		)
	);

	$custom_query = new WP_Query( $queryargs );


	if ( $custom_query->have_posts() ) :
		while ( $custom_query->have_posts() ) :

			$custom_query->the_post();
			$asset_id = get_the_ID();
			$asset_name = get_the_title();

			// ALL DATA WE NEED
			$objID = get_post_meta($asset_id, 'wpunity_asset3d_obj', true); // OBJ ID
			$objPath = $objID ? wp_get_attachment_url( $objID ) : '';                   // OBJ PATH

			$mtlID = get_post_meta($asset_id, 'wpunity_asset3d_mtl', true); // MTL ID
			$mtlPath = $mtlID ? wp_get_attachment_url( $mtlID ) : '';                   // MTL PATH

			$difImageIDs = get_post_meta($asset_id, 'wpunity_asset3d_diffimage', false);  // Diffusion Image ID

            $difImagePaths = [];

            foreach ($difImageIDs as $diffid)
                $difImagePaths[] = wp_get_attachment_url( $diffid );                // Diffusion Image PATH

			$screenImageID = get_post_meta($asset_id, 'wpunity_asset3d_screenimage', true); // Screenshot Image ID
			$screenImagePath = $screenImageID ? wp_get_attachment_url( $screenImageID ) : '';           // Screenshot Image PATH

			$image1id = get_post_meta($asset_id, 'wpunity_asset3d_image1', true);

			$categoryAsset = wp_get_post_terms($asset_id, 'wpunity_asset3d_cat');

			$allAssets[] = [
				'assetName'=>$asset_name,
				'assetSlug'=>get_post()->post_name,
				'assetid'=>$asset_id,
				'categoryName'=>$categoryAsset[0]->name,
				'categoryID'=>$categoryAsset[0]->term_id,
				'objID'=>$objID,
				'objPath'=>$objPath,
				'mtlID'=>$mtlID,
				'diffImageIDs'=>$difImageIDs,
				'diffImages'=>$difImagePaths,
				'screenImageID'=>$screenImageID,
				'screenImagePath'=>$screenImagePath,
				'mtlPath'=>$mtlPath,
				'image1id'=>$image1id,
                'doorName_source'=>'', //$doorName_source,   the asset does not save door but the json
                'doorName_target'=>'', //$doorName_target,
                'sceneName_target'=>'' //$sceneName_target
			];

		endwhile;
	endif;



	// Reset postdata
	wp_reset_postdata();

	return $allAssets;
}

// jimver : check this
function wpunity_getAllscenes_unityfiles_byGame($gameID){

	$allUnityScenes = [];

	$originalGame = get_post($gameID);
	$gameSlug = $originalGame->post_name;
	//Get 'Asset's Parent Scene' taxonomy with the same slug
	$gameTaxonomy = get_term_by('slug', $gameSlug, 'wpunity_scene_pgame');
	$gameTaxonomyID = $gameTaxonomy->term_id;

	$queryargs = array(
		'post_type' => 'wpunity_scene',
		'posts_per_page' => -1,
		'orderby'   => 'ID',
		'order' => 'ASC',
		'tax_query' => array(
			array(
				'taxonomy' => 'wpunity_scene_pgame',
				'field' => 'id',
				'terms' => $gameTaxonomyID
			)
		)
	);

	$custom_query = new WP_Query( $queryargs );

	if ( $custom_query->have_posts() ) :
		while ( $custom_query->have_posts() ) :
			$custom_query->the_post();
			$scene_id = get_the_ID();
			$sceneSlug = get_post_field( 'post_name', $scene_id );
			$allUnityScenes[] = ['sceneUnityPath'=>"Assets/".$sceneSlug."/".$sceneSlug.".unity"];
		endwhile;
	endif;

	// Reset postdata
	wp_reset_postdata();

	return $allUnityScenes;

}

//==========================================================================================================================================

?>