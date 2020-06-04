<?php
class ParseJSON {

    function __construct($relativepath)  {
        $this->relativepath = $relativepath;
    }

    public function init($sceneToLoad) {
        
        
        
        
        $sceneToLoad = htmlspecialchars_decode($sceneToLoad);
        $content_JSON = json_decode($sceneToLoad);
        $json_objects = $content_JSON->objects;
    
        // For light target
        $target_position_x = 0;
        $target_position_y = 0;
        $target_position_z = 0;
    
        $light_color_r = 1;
        $light_color_g = 1;
        $light_color_b = 1;
    
        $lightintensity = 1;
        
        
        foreach ($json_objects as $key=>$value) {

            $name = $key;

            if ($name == 'avatarYawObject') {
                $path = '';
                $obj = '';
                $mtl = '';
                $categoryName = 'avatarYawObject';
    
                $r_x = $value->rotation[0];
                $r_y = $value->rotation[1];
                $r_z = 0;
    
                
                
                $isLight = "false";
            } elseif ( strpos($name, 'lightSun') !== false ){
                
                $path = '';
                $obj = '';
                $mtl = '';
    
                $r_x = $value->rotation[0];
                $r_y = $value->rotation[1];
                $r_z = $value->rotation[2];
                
                $target_position_x = $value->targetposition[0];
                $target_position_y = $value->targetposition[1];
                $target_position_z = $value->targetposition[2];
                
                $light_color_r = $value->lightcolor[0];
                $light_color_g =$value->lightcolor[1];
                $light_color_b =$value->lightcolor[2];

                $categoryName = 'lightSun';
                $isLight = "true";
                $lightintensity = $value->lightintensity;
                
            } else {
                $path =$this->relativepath . $value->fnPath;
                $assetid=$value->assetid;
                $obj = $value->fnObj;
                $objID = $value->fnObjID;
                $mtl = $value->fnMtl;
                $mtlID = $value->fnMtlID;
                $diffImage = $value->diffImage;
                $diffImageID = $value->diffImageID;
                $categoryName = $value->categoryName;
                $categoryID = $value->categoryID;
                $image1id = $value->image1id;

                $doorName_source = $value->doorName_source;
                $doorName_target = $value->doorName_target;
                $sceneName_target = $value->sceneName_target;
                $sceneID_target = $value->sceneID_target;

                $archaeology_penalty = $value->archaeology_penalty;
                $hv_penalty = $value->hv_penalty;
                $natural_penalty = $value->natural_penalty;

                $isreward = $value->isreward;
                $isCloned = $value->isCloned;
                $isJoker = $value->isJoker;

                $r_x = $value->rotation[0];
                $r_y = $value->rotation[1];
                $r_z = $value->rotation[2];
    
                $isLight = "false";
            }

            $t_x = $value->position[0];
            $t_y = $value->position[1];
            $t_z = $value->position[2];

            $scale = $value->scale[0];

            echo '<script>';
            echo 'var selected_object_trs={"translation":['.$t_x.','.$t_y.','.$t_z.'],"rotation":['.
                $r_x .','.$r_y .','.$r_z .'],'.'"scale":'.$scale.'};';

            
            
            echo 'resources3D["'.$name.'"]= {'.
                                            '"path":"'.$path.
                                            '","assetid":"'.$assetid.
                                            '","obj":"'.$obj.
                                            '","objID":"'.$objID.
                                            '","mtl":"'.$mtl.
                                            '","mtlID":"'.$mtlID.
                                            '","diffImage":"'.$diffImage.
                                            '","diffImageID":"'.$diffImageID.
                                            '","categoryName":"'.$categoryName.
                                            '","categoryID":"'.$categoryID.
                                            '","image1id":"'.$image1id.
                                            '","doorName_source":"'.$doorName_source.
                                            '","doorName_target":"'.$doorName_target.
                                            '","sceneName_target":"'.$sceneName_target.
                                            '","sceneID_target":"'.$sceneID_target.
                                            '","archaeology_penalty":"'.$archaeology_penalty.
                                            '","hv_penalty":"'.$hv_penalty.
                                            '","natural_penalty":"'.$natural_penalty.
                                            '","isreward":"'.$isreward.
                                            '","isCloned":"'.$isCloned.
                                            '","isJoker":"'.$isJoker.
                                            '","isLight":"'.$isLight.
                                            '","lightintensity":"'.$lightintensity.
                                            '","lightcolor":['.$light_color_r.','.$light_color_g.','.$light_color_b.']'.
                                            ',"targetposition":['.$target_position_x.','.$target_position_y.','.$target_position_z.']'.
                                            ',"trs":selected_object_trs};';
            
           
            
            echo '</script>';
        }
        
        return true;
    }
}
?>
