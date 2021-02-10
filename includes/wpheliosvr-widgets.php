<?php

// Load Scripts
function loadAsset3Dfunctions() {
    // Load single asset kernel
    // Three js : for simple rendering
    wp_enqueue_script('wpunity_scripts');
    
    // For fbx binary
    wp_enqueue_script('wpunity_inflate'); // for binary fbx
    
    // 1. Three js library
    wp_enqueue_script('wpunity_load124_threejs');
    wp_enqueue_script('wpunity_load124_statjs');
    
    // 2. Obj loader simple; For loading an uploaded obj
    wp_enqueue_script('wpunity_load87_OBJloader');
    
    // 3. Obj loader 2: For preview loading
    wp_enqueue_script('wpunity_load87_OBJloader2');
    wp_enqueue_script('wpunity_load87_WWOBJloader2');
    
    // 4. Mtl loader
    wp_enqueue_script('wpunity_load87_MTLloader');
    
    // 5. Pdb loader for molecules
    wp_enqueue_script('wpunity_load87_PDBloader');
    
    // 6. Fbx loader
    wp_enqueue_script('wpunity_load119_FBXloader');
    
    // 7. Trackball controls
    wp_enqueue_script('wpunity_load124_TrackballControls');
    wp_enqueue_script('wpunity_load119_OrbitControls');
    
    // 8. GLTF Loader
    wp_enqueue_script('wpunity_load119_GLTFLoader');
    wp_enqueue_script('wpunity_load119_DRACOLoader');
    wp_enqueue_script('wpunity_load119_DDSLoader');
    wp_enqueue_script('wpunity_load119_KTXLoader');
    
    // For the PDB files to annotate molecules in 3D
    wp_enqueue_script('wpunity_load119_CSS2DRenderer');
    
    // Load single asset
    wp_enqueue_script('Asset_viewer_3d_kernel');
}
add_action('wp_enqueue_scripts', 'loadAsset3Dfunctions' );

// Creating the widget
class wpheliosvr_3d_widget extends WP_Widget {
    
    function __construct() {
        parent::__construct(

            // Base ID of your widget
            'wpheliosvr_3d_widget',

            // Widget name will appear in UI
            __('HeliosVR 3D Widget', 'wpheliosvr_3d_widget_domain'),

            // Widget description
            array( 'description' => __( 'A widget to place 3D models', 'wpheliosvr_widget_domain' ), )
        );
    }
    
    
    // Widget Backend
    public function form( $instance ) {
        
        $title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __( 'New title', 'wpheliosvr_3d_widget_domain');
        $asset_id =  isset( $instance[ 'asset_id' ] ) ? $instance[ 'asset_id' ] : __( 'Insert asset id', 'wpheliosvr_3d_widget_domain' );
        $cameraPositionX = isset( $instance[ 'cameraPositionX' ] ) ?  $instance[ 'cameraPositionX' ] : 0;
        $cameraPositionY = isset( $instance[ 'cameraPositionY' ] ) ?  $instance[ 'cameraPositionY' ] : 0;
        $cameraPositionZ = isset( $instance[ 'cameraPositionZ' ] ) ?  $instance[ 'cameraPositionZ' ] : 0;
        $canvasWidth = isset( $instance[ 'canvasWidth' ] )? $instance[ 'canvasWidth' ] : '300px';
        $canvasHeight = isset( $instance[ 'canvasHeight' ] )? $instance[ 'canvasHeight' ] : '200px';
    
        $canvasBackgroundColor = isset( $instance[ 'canvasBackgroundColor' ] )? $instance[ 'canvasBackgroundColor' ] : 'transparent';
        
        $enableZoom = isset( $instance[ 'enableZoom' ] )? $instance[ 'enableZoom' ] : 'true';
    
        $enablePan = isset( $instance[ 'enableZoom' ] )? $instance[ 'enablePan' ] : 'false';
        
        
        
        
        
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">
                <?php _e( 'Title:' ); ?>
            </label>
            
            <input class="widefat"
                   id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>"
                   type="text"
                   value="<?php echo esc_attr( $title ); ?>"
            />
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id( 'asset_id' ); ?>">
                <?php _e( 'Asset id:' ); ?>
            </label>
            

            <select
                class="widefat"
                name="<?php echo $this->get_field_name( 'asset_id' ); ?>"
                id="<?php echo $this->get_field_id( 'asset_id' ); ?>"
            >
            
            <?php
                // Get all assets
                $assets = get_assets([]);
                
                // Iterate for the drop down
                for ($i=0;$i<count($assets);$i++){
                    
                    echo '<option value="'.
                        $assets[$i]['assetid'].'" '.
                        (esc_attr( $asset_id )==$assets[$i]['assetid']?'selected':'').
                        '>'.
                        $assets[$i]['assetName'].
                        '</option>';
                    
                }
            ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'cameraPositionX' ); ?>">
                <?php _e( 'camera Position X:' ); ?>
            </label>

            <input class="widefat"
                   id="<?php echo $this->get_field_id( 'cameraPositionX' ); ?>"
                   name="<?php echo $this->get_field_name( 'cameraPositionX' ); ?>"
                   type="text"
                   value="<?php echo esc_attr( $cameraPositionX ); ?>"
            />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'cameraPositionY' ); ?>">
                <?php _e( 'Camera Position Y:' ); ?>
            </label>

            <input class="widefat"
                   id="<?php echo $this->get_field_id( 'cameraPositionY' ); ?>"
                   name="<?php echo $this->get_field_name( 'cameraPositionY' ); ?>"
                   type="text"
                   value="<?php echo esc_attr( $cameraPositionY ); ?>"
            />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'cameraPositionZ' ); ?>">
                <?php _e( 'Camera Position Z:' ); ?>
            </label>

            <input class="widefat"
                   id="<?php echo $this->get_field_id( 'cameraPositionZ' ); ?>"
                   name="<?php echo $this->get_field_name( 'cameraPositionZ' ); ?>"
                   type="text"
                   value="<?php echo esc_attr( $cameraPositionZ ); ?>"
            />
        </p>


        <p>
            <label for="<?php echo $this->get_field_id( 'canvasWidth' ); ?>">
                <?php _e( 'Canvas width, e.g. 200px:' ); ?>
            </label>

            <input class="widefat"
                   id="<?php echo $this->get_field_id( 'canvasWidth' ); ?>"
                   name="<?php echo $this->get_field_name( 'canvasWidth' ); ?>"
                   type="text"
                   value="<?php echo esc_attr( $canvasWidth ); ?>"
            />
        </p>


        <p>
            <label for="<?php echo $this->get_field_id( 'canvasHeight' ); ?>">
                <?php _e( 'Canvas height:' ); ?>
            </label>

            <input class="widefat"
                   id="<?php echo $this->get_field_id( 'canvasHeight' ); ?>"
                   name="<?php echo $this->get_field_name( 'canvasHeight' ); ?>"
                   type="text"
                   value="<?php echo esc_attr( $canvasHeight ); ?>"
            />
        </p>


        
        

        <p>
            <label for="<?php echo $this->get_field_id( 'canvasBackgroundColor' ); ?>">
                <?php _e( 'Canvas Background Color. Examples: basic names (yellow), transparent, or rbg(0,10,100):' ); ?>
            </label>

            <input class="widefat"
                   id="<?php echo $this->get_field_id( 'canvasBackgroundColor' ); ?>"
                   name="<?php echo $this->get_field_name( 'canvasBackgroundColor' ); ?>"
                   type="text"
                   value="<?php echo esc_attr( $canvasBackgroundColor ); ?>"
            />
        </p>

        
        <p>
            <label for="<?php echo $this->get_field_id( 'enableZoom' ); ?>">
                <?php _e( 'Enable Zoom:' ); ?>
            </label>

            <input class="widefat"
                   id="<?php echo $this->get_field_id( 'enableZoom' ); ?>"
                   name="<?php echo $this->get_field_name( 'enableZoom' ); ?>"
                   type="text"
                   value="<?php echo esc_attr( $enableZoom ); ?>"
            />
        </p>
        

        <p>
            <label for="<?php echo $this->get_field_id( 'enablePan' ); ?>">
                <?php _e( 'Enable pan:' ); ?>
            </label>

            <input class="widefat"
                   id="<?php echo $this->get_field_id( 'enablePan' ); ?>"
                   name="<?php echo $this->get_field_name( 'enablePan' ); ?>"
                   type="text"
                   value="<?php echo esc_attr( $enablePan ); ?>"
            />
        </p>

        <?php
    }
    
    
    // Creating widget front-end
    public function widget( $args, $instance ) {

        $title = apply_filters( 'widget_title', $instance['title'] );
        $asset_id = apply_filters( 'widget_asset_id', $instance['asset_id'] );
        $cameraPositionX = apply_filters( 'widget_cameraPositionX', $instance['cameraPositionX'] );
        $cameraPositionY = apply_filters( 'widget_cameraPositionY', $instance['cameraPositionY'] );
        $cameraPositionZ = apply_filters( 'widget_cameraPositionZ', $instance['cameraPositionZ'] );
    
        $canvasWidth = apply_filters( 'widget_canvasWidth', $instance['canvasWidth'] );
        $canvasHeight = apply_filters( 'widget_canvasHeight', $instance['canvasHeight'] );
    
        $canvasBackgroundColor = apply_filters( 'widget_canvasBackgroundColor', $instance['canvasBackgroundColor'] );
        $enablePan = apply_filters( 'widget_enablePan', $instance['enablePan'] );
        $enableZoom = apply_filters( 'widget_enableZoom', $instance['enableZoom'] );

        
        
        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        
        
        // The data
        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];
    
//        echo $cameraPositionX.' '.$cameraPositionY.' '.$cameraPositionZ;

//        if ( ! empty( $asset_id ) )
//            echo $asset_id;
        
        // -----  Step 2 : Get  urls from id ---------
    
        // Get post
        $asset_post    = get_post($asset_id);
    
        // Get post meta
        $assetpostMeta = get_post_meta($asset_id);
    
        // Background color in canvas
        $back_3d_color = $assetpostMeta['wpunity_asset3d_back_3d_color'][0];
    
        $asset_3d_files = get_3D_model_files($assetpostMeta, $asset_id);
        
        // audio file
        $audioID = get_post_meta($asset_id, 'wpunity_asset3d_audio', true);
        $attachment_audio_file = get_post( $audioID )->guid;
        ?>

        <div id="wrapper_3d_inner" class="">

            <!--   Progress bar -->
            <div id="previewProgressSliderDiv" class="CenterContents"
                 style="display: none; z-index:2; width:100%; top:0"
            >
                <h6 id="previewProgressLabelDiv" class="mdc-theme--text-primary-on-light mdc-typography--subheading1">
                    Preview of 3D Model</h6>
                <div class="progressSliderDiv">
                    <div id="previewProgressSliderLineDiv" class="progressSliderSubLineDiv" style="width: 0;">...</div>
                </div>
            </div>
            
            <!-- LabelRenderer of Canvas -->
            <div id="divCanvasLabels" style=""></div>

            <!-- 3D Canvas -->
            <canvas id="divCanvas"
                    style="background: transparent; position: absolute; top:0; height: <?php echo $canvasHeight;?>; width:<?php echo $canvasWidth;?>;" >3D canvas</canvas>

            <a href="#" class="animationButtonDiv" id="animButtonDiv" onclick="asset_viewer_3d_kernel.playStopAnimation();">Animation 1</a>


        </div>
    
        <?php
            if(strpos($attachment_audio_file, "mp3" )!==false ||
               strpos($attachment_audio_file, "wav" )!==false) {
            ?>
            
            <audio controls loop preload="auto" id ='audioFile'>
                <source src="<?php echo $attachment_audio_file;?>" type="audio/mp3">
                <source src="<?php echo $attachment_audio_file;?>" type="audio/wav">
                Your browser does not support the audio tag.
            </audio>
        <?php } ?>

        <script>
            path_url     = "<?php echo $asset_3d_files['path'].'/'; ?>";
            mtl_file_name= "<?php echo $asset_3d_files['mtl']; ?>";
            obj_file_name= "<?php echo $asset_3d_files['obj']; ?>";
            pdb_file_name= "<?php echo $asset_3d_files['pdb']; ?>";
            glb_file_name= "<?php echo $asset_3d_files['glb'];?>";
            fbx_file_name= "<?php echo $asset_3d_files['fbx'];    ?>";
            
            cameraPositionX= "<?php echo $cameraPositionX; ?>";
            cameraPositionY= "<?php echo $cameraPositionY; ?>";
            cameraPositionZ= "<?php echo $cameraPositionZ; ?>";
            
            canvasBackgroundColor = "<?php echo $canvasBackgroundColor;?>";
            enableZoom = "<?php echo $enableZoom?>" === 'true';
            enablePan = "<?php echo $enablePan?>" === 'true';
            
            textures_fbx_string_connected = "<?php echo $asset_3d_files['texturesFbx']; ?>";
            back_3d_color = "<?php echo $back_3d_color; ?>";
            let audio_file = document.getElementById( 'audioFile' );
        
            console.log("canvasBackgroundColor", canvasBackgroundColor);
            console.log("enableZoom", enableZoom);
            console.log("enablePan", enablePan);
            
        
            var asset_viewer_3d_kernel = new Asset_viewer_3d_kernel(
                document.getElementById( 'divCanvas' ),
                document.getElementById( 'divCanvasLabels' ),
                document.getElementById( 'animButtonDiv' ),
                document.getElementById('previewProgressLabelDiv'),
                document.getElementById('previewProgressSliderLineDiv'),
                canvasBackgroundColor,
                audio_file,
                path_url, // OBJ textures path
                mtl_file_name,
                obj_file_name,
                pdb_file_name,
                fbx_file_name,
                glb_file_name,
                textures_fbx_string_connected,
                false,
                canvasBackgroundColor === 'transparent' ? true : false,
                !enablePan, // lock
                enableZoom, // enableZoom
                cameraPositionX,cameraPositionY,cameraPositionZ);
        
        </script>
        
        <?php
        
        
        
        // This is where you run the code and display the output
        //echo __( 'HeliosVR 3D Widget', 'wpheliosvr_3d_widget_domain' );
        echo $args['after_widget'];
    }



    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : 'No title';
        
        $instance['asset_id'] = ( ! empty( $new_instance['asset_id'] ) ) ? strip_tags( $new_instance['asset_id'] ) : '';
        
        $instance['cameraPositionX'] =  !empty($new_instance['cameraPositionX']) ?
                                               strip_tags($new_instance['cameraPositionX']) : '0';
        
        $instance['cameraPositionY'] = ( ! empty( $new_instance['cameraPositionY'] ) ) ?
                                               strip_tags( $new_instance['cameraPositionY'] ) : '0';
        
        $instance['cameraPositionZ'] = ( ! empty( $new_instance['cameraPositionZ'] ) ) ?
                                               strip_tags( $new_instance['cameraPositionZ'] ) : '0';
    
        $instance['canvasWidth'] = ( ! empty( $new_instance['canvasWidth'] ) ) ?
            strip_tags( $new_instance['canvasWidth'] ) : '300px';
    
        $instance['canvasHeight'] = ( ! empty( $new_instance['canvasHeight'] ) ) ?
            strip_tags( $new_instance['canvasHeight'] ) : '200px';
    
        $instance['canvasBackgroundColor'] = ( ! empty( $new_instance['canvasBackgroundColor'] ) ) ?
            strip_tags( $new_instance['canvasBackgroundColor'] ) : 'transparent';
    
        $instance['enableZoom'] = ( ! empty( $new_instance['enableZoom'] ) ) ?
            strip_tags( $new_instance['enableZoom'] ) : 'true';
    
        $instance['enablePan'] = ( ! empty( $new_instance['enablePan'] ) ) ?
            strip_tags( $new_instance['enablePan'] ) : 'false';
        
        
        
        
        return $instance;
    }
}


// Register and load the widget
function wpheliosvr_load_widget() {
    register_widget( 'wpheliosvr_3d_widget' );
}
add_action( 'widgets_init', 'wpheliosvr_load_widget' );
