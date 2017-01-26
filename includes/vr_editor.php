<!-- vr_editor.php -->
<?php

wp_enqueue_style('wpunity_vr_editor');
wp_enqueue_style('wpunity_vr_editor_filebrowser');

// Define current path
$PLUGIN_PATH_VR = plugins_url().'/WordpressUnity3DEditor';
$UPLOAD_DIR = wp_upload_dir()['baseurl'];
$UPLOAD_DIR_C = wp_upload_dir()['basedir'];
$UPLOAD_DIR_C = str_replace('/','\\',$UPLOAD_DIR_C);

// Also available in Javascript side
echo '<script>';
echo 'PLUGIN_PATH_VR="'.$PLUGIN_PATH_VR.'"';
echo '</script>';

echo '<script>';
echo 'UPLOAD_DIR="'.wp_upload_dir()['baseurl'].'"';
echo '</script>';

echo '<script>';
echo 'scenefolder="'.$scenefolder.'"';
echo '</script>';

echo '<script>';
echo 'gamefolder="'.$gamefolder.'"';
echo '</script>';

//$sceneToLoad = $PLUGIN_PATH_VR."/scenes/standard_scene.json";
?>

<!-- Todo: put these js libraries in wp_register -->
<!-- JS libraries -->
<link rel="import" href="<?php echo $PLUGIN_PATH_VR?>/includes/vr_editor_header_js.html">


    <!-- script for scanning files toolbar -->
    <script src="<?php echo $PLUGIN_PATH_VR?>/js_libs/scriptFileBrowserToolbar.js"></script>




<script>
    //  Save Button implemented with Ajax
    $(document).ready(function(){

        var cw = $('#vr_editor_main_div').width();
        $('#vr_editor_main_div').css({'height':cw*2/3+'px'});

        envir.SCREEN_WIDTH = envir.container_3D_all.clientWidth; // 500; //window.innerWidth;
        envir.SCREEN_HEIGHT = envir.container_3D_all.clientHeight; // 500; //window.innerHeight;
        envir.renderer.setSize(envir.SCREEN_WIDTH, envir.SCREEN_HEIGHT);
    });

    // Convert scene to json and put the json in the wordpress field wpunity_scene_json_input
    function textify_scene(){

        document.getElementById('save-scene-button').style.backgroundColor = 'green';

        // Export using a custom variant of the old deprecated class SceneExporter
        var exporter = new THREE.SceneExporter();
        var outputJSON = exporter.parse(envir.scene);

        document.getElementById('wpunity_scene_json_input').value = outputJSON;

        setInterval(function(){document.getElementById('save-scene-button').style.backgroundColor = 'black';},300);
    };


    function removeSavedText(){
        $(".result").html("");
    }

    //========== Drag and drop 3D objects into scene for INSERT  =========================================
    var raycasterDrag = new THREE.Raycaster();

    function drop_handler(ev) {
        var dataDrag =JSON.parse( ev.dataTransfer.getData("text"));

        var path = dataDrag.obj.substring(0, dataDrag.obj.lastIndexOf("/")+1);
        var objFname = dataDrag.obj.substring(dataDrag.obj.lastIndexOf("/")+1);
        var mtlFname = dataDrag.mtl.substring(dataDrag.mtl.lastIndexOf("/")+1);

        var fbxFname = objFname.slice(0,-4) + '.fbx';
        var matFname = objFname.slice(0,-4) + '.mat';
        var guid_fbx = "sys15a";
        var guid_mat = "sys11a";

        addOne(dataDrag.title, path, objFname, mtlFname, fbxFname, matFname, guid_fbx, guid_mat,
            envir.getSteveWorldPosition().x,
            envir.getSteveWorldPosition().y,
            envir.getSteveWorldPosition().z);

        ev.preventDefault();
    }

    function dragover_handler(ev) {
        ev.preventDefault();
    }

    /**
     * Resize div and renderer
     *
     * @param ev
     */
    function resize_handler(ev){

        var cw = $('#vr_editor_main_div').width();
        $('#vr_editor_main_div').css({'height':cw*2/3+'px'});

        envir.SCREEN_WIDTH = envir.container_3D_all.clientWidth; // 500; //window.innerWidth;
        envir.SCREEN_HEIGHT = envir.container_3D_all.clientHeight; // 500; //window.innerHeight;
        envir.ASPECT = envir.SCREEN_WIDTH / envir.SCREEN_HEIGHT;
        envir.renderer.setSize(envir.SCREEN_WIDTH, envir.SCREEN_HEIGHT);
    }


    window.addEventListener('resize', resize_handler, true);

    //===================== End of drag n drop for INSERT ========================================================
</script>

<!-- All go here -->
<div id="vr_editor_main_div" ondrop="drop_handler(event);" ondragover="dragover_handler(event);">

    <!-- Controlling 3d items transition-rotation-scale (trs) -->
    <div id="dat-gui-container"></div>

    <!-- The button to start walking in the 3d environment -->
    <div id="blocker">
        <div id="instructions">
            <div style="font-size: 1vw; display:block;">Walk in

                <div style="font-size: 0.5vw;">(W,A,S,D,Q,E keys= Move, MOUSE = Look around)</div>
            </div>

        </div>
    </div>

    <!-- --------- Make form to submit user changes ----- -->
    <div id="infophp">
        <div id="progress">
            <span id="scene_loading_message">Downloading ...</span>
            <div id="progressbar">
                <div id="scene_loading_bar"></div>
            </div>
        </div>

        <div id="save-scene-button" class="bt_textify" onclick="textify_scene()">Textify configuration</div>

        <div class="result"></div>
        <div id="result_download"></div>
    </div>

    <!--  FileBrowserToolbar  -->
    <div class="filemanager" id="fileBrowserToolbar" >

        <div class="search">
            <input type="search" placeholder="Find a file.." />
        </div>

        <div class="breadcrumbs"></div>

<!--            <div class="nothingfound">-->
<!--                <div class="nofiles"></div>-->
<!--                <span>Nothing found</span>-->
<!--            </div>-->


        <ul class="data" id="filesList" >



        </ul>

        <div class="bt_close_file_toolbar">
            Close
        </div>

    </div>

    <!-- Full screen bar button -->
    <div id="scene-vr-editor-fullscreen-bar" name="scene-vr-editor-fullscreen-bar">
        <div id="scene-vr-editor-fullscreen-bt" name="scene-vr-editor-fullscreen-bt" onclick="envir.makeFullScreen()">
            &boxVH;
        </div>
    </div>

    <!-- Interface for Picking two overlapping objects -->
    <div id="popUpDiv">
        <select id="popupSelect">
        </select>
    </div>

</div>


<!--    Start 3D    -->
<script>
    // all 3d dom
    var container_3D_all = document.getElementById( 'vr_editor_main_div' );

    // Selected object name
    var selected_object_name = '';

    // Add gui to gui container_3D_all
    var datguiContainer = document.getElementById('dat-gui-container');
    datguiContainer.appendChild(gui.domElement);

    // camera, scene, renderer, lights, stats, floor, browse_controls are all children of CaveEnvironmentals instance
    var envir = new vr_editor_environmentals(container_3D_all);

    // Controls with axes (Transform, Rotate, Scale)
    var transform_controls = new THREE.TransformControls( envir.cameraOrbit, envir.renderer.domElement );
    transform_controls.name = 'myTransformControls';
    transform_controls.addEventListener( 'change', checkForRecycle );
    envir.addCubeToControls(transform_controls);

    // When Dat.Gui changes update php, javascript vars and transform_controls
    controllerDatGuiOnChange();

    // Is Recycle Bin deployed
    var isRecycleBinDeployed = false;

    /* The items in the recycle bin */
    var delArchive = [];

    // Load all 3D including Steve
    var loaderMulti;

    // id of animation frame is used for canceling animation when dat-gui changes
    var id_animation_frame;

    var resources3D  = [];// This holds all the resources to load. Generated in Parse JSON

    //====================== Load Manager =======================================================
    // Make progress bar visible
    $("#progress").get(0).style.display = "block";

    var manager = new THREE.LoadingManager();

    manager.onProgress = function ( item, loaded, total ) {
        console.log( item, loaded, total );
    };

    // When all are finished loading place them in the correct position
    manager.onLoad = function (){

        var objItem;
        var trs_tmp;
        var name;

        for (name in resources3D ) {

            trs_tmp = resources3D[name]['trs'];

            objItem = envir.scene.getObjectByName(name);

            objItem.position.set( trs_tmp['translation'][0], trs_tmp['translation'][1], trs_tmp['translation'][2]);
            objItem.rotation.set( trs_tmp['rotation'][0], trs_tmp['rotation'][1], trs_tmp['rotation'][2]);
            objItem.scale.set( trs_tmp['scale'], trs_tmp['scale'], trs_tmp['scale']);
        }

        // place controls to last inserted obj
        if (objItem) {
            transform_controls.attach(objItem);
            envir.scene.add(transform_controls);

            transform_controls.object.position.set(trs_tmp['translation'][0], trs_tmp['translation'][1], trs_tmp['translation'][2]);
            transform_controls.object.rotation.set(trs_tmp['rotation'][0], trs_tmp['rotation'][1], trs_tmp['rotation'][2]);
            transform_controls.object.scale.set(trs_tmp['scale'], trs_tmp['scale'], trs_tmp['scale']);

            selected_object_name = name;
        }
    }
</script>

<!-- Load Scene - javascript var resources3D[] -->
<?php require( "vr_editor_ParseJSON.php" );
$formRes = new ParseJSON($UPLOAD_DIR);
$formRes->init($sceneToLoad);
?>

<script>

    loaderMulti = new LoaderMulti();


    loaderMulti.load(manager, resources3D);

    //=================== End of loading ============================================
    //--- initiate PointerLockControls ---------------
    initPointerLock();

    //--------------------------- UPDATERS ---------------------------------------------------------------------
    // ANIMATE
    function animate()
    {
        // 60fps
        id_animation_frame = requestAnimationFrame( animate );

        // XX fps (avoid due to dat-gui unable to intercept rendering (limited scope of id_animation_frame)
//        setTimeout( function() {
//            id_animation_frame = requestAnimationFrame( animate );
//        }, 1000 / 25 );


        // Render it
        envir.renderer.render( envir.scene, avatarControlsEnabled ? envir.cameraAvatar : envir.cameraOrbit);

        // Update it
        update();


    }

    // UPDATE
    function update()
    {
        // Only for orbit ?
        //if (avatarControlsEnabled == false)
        envir.orbitControls.update();

        updatePointerLockControls();

        transform_controls.update();// update the axis controls based on the browse controls
        envir.stats.update();

        // light is from camera towards object
        envir.lightOrbit.position.copy(envir.orbitControls.object.position);
        envir.lightAvatar.position.copy(envir.avatarControls.getObject().position);
        envir.lightAvatar.position.y += 1.8;

        // Now update the translation and rotation input texts
        if (transform_controls.object != null){

            for (var i in gui.__controllers)
                gui.__controllers[i].updateDisplay();

            updatePositionsPhpAndJavsFromControlsAxes();
        }
    }


    // Select event listener
    $("#vr_editor_main_div").get(0).addEventListener( 'mousedown', onMouseDown );

    animate();

</script>

<!-- Change dat GUI style: Override the inside js style -->

<link rel="stylesheet" type="text/css" href="<?php echo $PLUGIN_PATH_VR?>/css/dat-gui.css">

