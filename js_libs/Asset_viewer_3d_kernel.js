/* 3D viewer for all types of files in assetEditor */
class Asset_viewer_3d_kernel {

    // asset_viewer_3d_kernel.scene.children :
        // 0: root (pdb, audio, fbx, GLB, OBJ)
        // 1,2,3,4: Directional light 1,2,3, 4: Ambient light

    // PDB is the same loader for url and client side
    // OBJ, FBX, and GLB call first an xhr loader in editor_scripts.js and then the streaming version here


    // noinspection DuplicatedCode
    setZeroVars(){
        this.nObj = 0;
        this.nFbx = 0;
        this.nMtl = 0;
        this.nJpg = 0;
        this.nPng = 0;
        this.nPdb = 0;
        this.nGif = 0;
        this.nGlb = 0;
        this.FbxBuffer = '';
    }


    constructor(canvasToBindTo, back_3d_color, audioElement) {

        this.setZeroVars()
        this.back_3d_color = back_3d_color;

        this.FbxBuffer = '';
        this.GlbBuffer = '';

        this.path_url = null;
        this.mtl_file_name = this.obj_file_name = this.pdb_file_name = this.fbx_file_name = this.glb_file_name;

        this.canvas = canvasToBindTo;
        this.scene = new THREE.Scene();

        this.stats = new Stats();
        document.getElementById( 'wrapper_3d_inner' ).appendChild( this.stats.dom );
        this.stats.dom.style.removeProperty("left");

        this.renderer = new THREE.WebGLRenderer({
            canvas: this.canvas,
            antialias: true,
            logarithmicDepthBuffer: true
        });


        this.camera = null;
        this.listener = null;

        this.audioElement = audioElement;


        this.aspectRatio = 1;
        this.recalcAspectRatio();

        this.cameraDefaults = {
            posCamera: new THREE.Vector3(0.0, 175.0, 500.0),
            posCameraTarget: new THREE.Vector3(0, 0, 0),
            near: 0.01,
            far: 10000,
            fov: 45
        };

        // Set up camera
        this.camera = new THREE.PerspectiveCamera(this.cameraDefaults.fov,
            this.aspectRatio, this.cameraDefaults.near, this.cameraDefaults.far);

        this.cameraTarget = this.cameraDefaults.posCameraTarget;

        // Trackball controls
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);


        this.controls.zoomSpeed = 1.02;
        //this.controls.dynamicDampingFactor = 0.3;
        //this.controls.dynamicDampingFactor = 0;
        //this.controls.staticMoving = true;

        // For the animation
        this.clock = new THREE.Clock();
        this.mixers = [];

        // Scene.children[0] is root: Here all chemistry 3D and 2D labels items are stored
        let root = new THREE.Group();
        root.name = "root";
        this.scene.add( root );


        // - OBJ Specific - Setup loader
        try {
            this.wwObjLoader2 = new THREE.OBJLoader2.WWOBJLoader2();
            this.wwObjLoader2.setCrossOrigin('anonymous');
        } catch (e) {
            console.log("ERROR WW15", "Web Workers for OBJ not found")
        }

        this.boundRender = this.render.bind( this );
        this.initGL();
    }

    // Add OrbitControl listeners to render on demand
    addControlEventListeners(){

        this.controls.addEventListener('change', this.boundRender);

        // this.controls.dispatchEvent( { type: 'change' } );
        window.addEventListener('resize', this.boundRender);
    }

    // Remove OrbitControl listeners to render on demand. (this is useful for continuous animation)
    removeControlEventListeners(){

        this.controls.removeEventListener('change', this.boundRender);

        window.removeEventListener('resize', this.boundRender);
    }



    // Play or Stop animation
    playStopAnimation(){

        if (!this.action.isRunning()) {

            this.removeControlEventListeners();
            this.startAutoLoopRendering();

            // Play the audio
            if (document.getElementById("audioFile")) {
                document.getElementById("audioFile").play();
            }

            // Play the animation
            this.action.paused = false;
            this.action.play();

        } else {

            this.stopAutoLoopRendering();
            this.addControlEventListeners();

            if (document.getElementById("audioFile")) {
                document.getElementById("audioFile").pause();
            }
            this.action.paused = true;

        }
    }

    // Start Renderer amd label Renderer
    render() {

        this.resizeDisplayGL();
        if (!this.renderer.autoClear)
            this.renderer.clear();

        this.stats.update();
        this.renderer.render(this.scene, this.camera);
        this.labelRenderer.render(this.scene, this.camera);

        // Animation
        if ( this.mixers.length > 0 ) {
            this.mixers[ 0 ].update( this.clock.getDelta() );
        }


    }

    // Render only when OrbitControls change of window is resized
    kickRendererOnDemand() {
        this.render();
        this.addControlEventListeners();
    }

    // Start auto loop (when animation)
    startAutoLoopRendering(){
        // continuous rendering
        let scope = this;

        let looprender = function(){
            scope.idRequestFrame = requestAnimationFrame(looprender);
            scope.boundRender();
        }

        looprender();
    }

    // Stop auto loop rendering (when animation)
    stopAutoLoopRendering(){
        cancelAnimationFrame(this.idRequestFrame);
    }

    /**
     * Reading from  files on client side for OBJ, FBX, and GLB
     */
    checkerCompleteReading( whocalls ){

        console.log("checkerCompleteReading by", whocalls)

        if ((this.nObj === 1 && this.objFileContent !== '') ||
            (this.nFbx === 1 && this.FbxBuffer !== '') || (this.nGlb === 1 && this.GlbBuffer !== '') ){

            // Show progress slider
            //jQuery('#previewProgressSlider').show();

            // Make the definition with the obj
            if (this.nObj === 1){

                let objFileContent = document.getElementById('objFileInput').value;
                let mtlFileContent = document.getElementById('mtlFileInput').value;


                let encoder = new TextEncoder();
                let uint8Array = encoder.encode(objFileContent);

                let objectDefinition = {
                    name: this.nObj === 1 ? 'userObj':'userFbx',
                    objAsArrayBuffer: uint8Array,
                    pathTexture: "",
                    mtlAsString: null
                };

                if (this.nMtl === 0) {
                    // Start without MTL
                    this.loadObjStream(objectDefinition);

                } else {
                    if (mtlFileContent!==''){

                        objectDefinition.mtlAsString = mtlFileContent;

                        if (this.nJpg===0 && this.nPng===0 ){
                            // Start without Textures
                            this.loadObjStream(objectDefinition);

                        } else {
                            // Else check if textures have been loaded
                            let nTexturesLength = jQuery("input[id='textureFileInput']").length;

                            if ((this.nPng>0 && this.nPng === nTexturesLength)
                                || ( this.nJpg>0 && this.nJpg === nTexturesLength) ) {

                                // Get textureFileInput array with jQuery
                                let textFil = jQuery("input[id='textureFileInput']");

                                // Store here the raw image textures
                                objectDefinition.pathTexture = [];

                                for (let k = 0; k < textFil.length; k++){
                                    let myname = textFil[k].name;

                                    // do some text processing on the names to remove textureFileInput[ and ] from name
                                    myname = myname.replace('textureFileInput[','');
                                    myname = myname.replace(']','');

                                    objectDefinition.pathTexture[myname] = textFil[k].value;
                                }

                                // Start with textures
                                console.log("start textures");

                                this.loadObjStream(objectDefinition);
                            }
                        }
                    }
                }
            } else if (this.nFbx === 1){

                // Get all fields
                let texturesStreams = jQuery("input[id='textureFileInput']");
                let nTexturesLoaded = texturesStreams.length;

                if ( nTexturesLoaded < this.nJpg || nTexturesLoaded < this.nPng || nTexturesLoaded < this.nGif){
                    console.log("Not all textures loaded yet");
                    return;
                }

                if ( nTexturesLoaded === 0 )
                    texturesStreams = '';

                console.log("Ignite reading fbx");

                console.log("texturesStreams", texturesStreams);

                this.loadFbxStream(this.FbxBuffer, texturesStreams);

            } else if (this.nGlb === 1){
                console.log("Ignite reading glb");

                this.loadGlbStream(this.GlbBuffer);

            }

        }
    }

    // Initialize Scene
    initGL() {

        this.scene.background = new THREE.Color(this.back_3d_color);

        // - Label renderer -
        this.labelRenderer = new THREE.CSS2DRenderer();
        this.labelRenderer.domElement.style.position = 'absolute';
        this.labelRenderer.domElement.style.top = '0';
        this.labelRenderer.domElement.style.fontSize = "25pt";
        this.labelRenderer.domElement.style.textShadow = "-1px -1px #000, 1px -1px #000, -1px 1px  #000, 1px 1px #000";
        this.labelRenderer.domElement.style.pointerEvents = 'none';

        // add label renderer
        document.getElementById("previewCanvasLabels").appendChild(this.labelRenderer.domElement);


        // Add audio listener to the camera
        if (this.audioElement!=null) {
            this.listener = new THREE.AudioListener();
            this.camera.add(this.listener);
            this.positionalAudio = new THREE.PositionalAudio(this.listener);

            this.positionalAudio.name = "audio1";
            this.positionalAudio.setMediaElementSource(this.audioElement);
            this.positionalAudio.setRefDistance(200);
            this.positionalAudio.setDirectionalCone(330, 230, 0.01);

            // This adds the audio element to loaded 3D object
            this.scene.getChildByName('root').add(this.positionalAudio);

        } else {
            console.log("ERROR Audio 111", "No Audio Element is found");
        }

        this.resetCamera();

        // Light
        let ambientLight = new THREE.AmbientLight(0x404040,2);
        let directionalLight1 = new THREE.DirectionalLight(0xA0A050);
        let directionalLight2 = new THREE.DirectionalLight(0x909050);
        let directionalLight3 = new THREE.DirectionalLight(0xA0A050);

        directionalLight1.position.set(-1000,  -550,  1000);
        directionalLight2.position.set( 1000,   550, -1000);
        directionalLight3.position.set(    0,   550,     0);

        // Scene.children[1],[2],[3],[4] are lights
        this.scene.add(directionalLight1);
        this.scene.add(directionalLight2);
        this.scene.add(directionalLight3);
        this.scene.add(ambientLight);

        // ---- OBJ asynch loader ---------

        let scope = this;

        // Function for OBJ: Function to load materials
        let materialsLoaded = function (materials) {
            for (let k in materials) {
                if(materials.hasOwnProperty(k)) {
                    materials[k].transparent = true;
                    materials[k].alphaTest = 0.1;
                }
            }
        };

        // Function for OBJ: Report for meshes
        let meshLoaded = function (name, bufferGeometry, material) {};

        // Function on Completed Loading
        let completedLoading = function () {
            console.log('Loading complete for OBJ WW!');

            document.getElementById('previewProgressSliderLine').style.width = '0';
            document.getElementById('previewProgressLabel').innerHTML = "";

            scope.zoomer(scope.scene.getChildByName('root'));

            console.log("THE SCOPE:", scope);
            scope.kickRendererOnDemand();

            //scope.controls.target(scope.scene.getChildByName('root'));

            // // Auto create screenshot;
            // setTimeout(function(){
            //     jQuery("#button_qrcode").click(); // close qr code
            //     jQuery("#createModelScreenshotBtn").click();
            // },1000);
        };

        try {
            this.wwObjLoader2.registerCallbackProgress(this._reportProgress);
            this.wwObjLoader2.registerCallbackCompletedLoading(completedLoading);
            this.wwObjLoader2.registerCallbackMaterialsLoaded(materialsLoaded);
            this.wwObjLoader2.registerCallbackMeshLoaded(meshLoaded);
        } catch (e){
            console.log("Can load OBJ", "ERROR O151");
        }

        return true;
    }

    // Clear Previous model
    clearAllAssets() {

        console.log("CLEARING");

        this.setZeroVars();

        // Hide animation button
        document.getElementById("animButton1").style.display = "none";

        // Clear animations
        this.mixers = [];

        // Clear any GLB, FBX, PDB or OBJ
        this.scene.getObjectByName('root').clear(); // remove all children of root
    }

    /* OBJ Loader */
    loadObjStream(objDef) {

        let prepData = new THREE.OBJLoader2.WWOBJLoader2.PrepDataArrayBuffer(
            objDef.name,
            objDef.objAsArrayBuffer,
            objDef.pathTexture,  // URL if already uploaded or array of raw images if in preview (client side)
            objDef.mtlAsString
        );

        prepData.setSceneGraphBaseNode(this.scene.getChildByName('root'));
        prepData.setStreamMeshes(true);
        this.wwObjLoader2.prepareRun(prepData);
        this.wwObjLoader2.run();
    }


    /* FBX loader */
    loadFbxStream(fbxBuffer, texturesStreams) {

        // Clear Previous
        this.clearAllAssets();

        let fbxLoader = new THREE.FBXLoader();

        let fbxObject = fbxLoader.parseStream(fbxBuffer, texturesStreams);

        // With animation
        if ( fbxObject.animations.length > 0 ) {

            fbxObject.mixer = new THREE.AnimationMixer( fbxObject );

            this.mixers.push( fbxObject.mixer );

            this.action = fbxObject.mixer.clipAction( fbxObject.animations[0] );

            // Display button to start animation inside the Asset 3D previewer
            document.getElementById("animButton1").style.display = "inline-block";

            // No-animation
        } else {
            document.getElementById("animButton1").style.display = "none";
        }

        // FBX is added to root
        this.scene.getChildByName("root").add(fbxObject);

        // let centerRadius = this.computeSceneBoundingSphereAll(this.scene.getChildByName('root'));
        //
        // const geometryBall = new THREE.SphereGeometry( centerRadius[1], 32, 32 );
        // const materialBall = new THREE.MeshBasicMaterial( {color: 0xffff00, wireframe: true} );
        // const sphereBall = new THREE.Mesh( geometryBall, materialBall );
        // sphereBall.position.copy( centerRadius[0] );
        // sphereBall.name = "Center Ball"
        // this.scene.getChildByName('root').add( sphereBall );
        //

        this.zoomer(this.scene.getChildByName('root'));

        let scope = this;
        setTimeout(function(){scope.kickRendererOnDemand();} , 1);


        //scope.controls.target = scope.scene.getChildByName('root');

        //jQuery('#previewProgressSlider')[0].style.visibility = "hidden";

        // setTimeout(function(){
        //     jQuery("#button_qrcode").click(); // close qr code
        //     jQuery("#createModelScreenshotBtn").click();
        // },1000);
    }

    /* GLB loader */
    loadGlbStream(GlbBuffer) {
        let scope = this;

        // Clear Previous
        this.clearAllAssets();

        let manager = new THREE.LoadingManager();
        manager.onProgress = function( item, loaded, total ) {};

        let glbLoader = new THREE.GLTFLoader( manager );

        // Load a glTF resource
        glbLoader.load(
            GlbBuffer,
            // called when the resource is loaded
            function ( gltf ) {

                //scene.add( gltf.scene );

                // gltf.animations; // Array<THREE.AnimationClip>
                // gltf.scene; // THREE.Group
                // gltf.scenes; // Array<THREE.Group>
                // gltf.cameras; // Array<THREE.Camera>
                // gltf.asset; // Object

                if ( gltf.animations.length > 0) {

                    let glbMixer = new THREE.AnimationMixer( gltf.scene );
                    scope.mixers.push( glbMixer );
                    scope.action = glbMixer.clipAction( gltf.animations[0] );

                    // Display button to start animation inside the Asset 3D previewer
                    document.getElementById("animButton1").style.display = "inline-block";

                } else {

                    // Display button to start animation inside the Asset 3D previewer
                    document.getElementById("animButton1").style.display = "none";

                }

                scope.scene.getChildByName('root').add( gltf.scene );




                scope.zoomer(scope.scene.getChildByName('root'));

                setTimeout(function(){scope.kickRendererOnDemand();} , 1);


            },
            '',
            // called when loading has errors
            function ( error ) {

                console.log( 'An error happened' );

            }
        );

    }

    /* Molecule loader */
    loadMolecule(url_or_text_pdb, calledFromWhere) {

        // Clear Previous
        this.clearAllAssets();

        let loader = new THREE.PDBLoader();

        let scope = this;

        // Load new
        loader.load(url_or_text_pdb, function (pdb) {

            let geometryAtoms = pdb.geometryAtoms;
            let geometryBonds = pdb.geometryBonds;
            let positionsBonds = geometryBonds.getAttribute('position');

            let positions = geometryAtoms.attributes.position.array;
            let colors = geometryAtoms.getAttribute('color').array;

            let json = pdb.json;

            let sphereGeometry = new THREE.IcosahedronBufferGeometry(0.4, 4);

            for (let i = 0; i < positions.length ; i += 3) {

                // Atom position and color
                let atomPosition = new THREE.Vector3(positions[i], positions[i + 1], positions[ i + 2 ]);
                let atomColor    = new THREE.Color  (   colors[i], colors   [i + 1], colors   [ i + 2 ]);

                let material = new THREE.MeshPhongMaterial({color: atomColor, flatShading: false});
                let atomObject = new THREE.Mesh(sphereGeometry, material);

                let atomName = json.atoms[i/3][4];

                atomObject.name = "AtomBall:" + atomName;
                atomObject.position.copy(atomPosition);

                // atomObject.position.multiplyScalar(75);
                // atomObject.scale.multiplyScalar(25);

                scope.scene.getChildByName('root').add(atomObject);


                // Make the Atom Text CSS2D label
                let atomLabelCSS = document.createElement('div');
                atomLabelCSS.className = 'label';
                atomLabelCSS.style.color = 'rgb(' + atomColor.r* 255 + ',' + atomColor.g* 255+ ',' + atomColor.b * 255 + ')';
                atomLabelCSS.textContent =  atomName;

                let atomlabelObj = new THREE.CSS2DObject(atomLabelCSS);
                atomlabelObj.name = "Label:" + atomLabelCSS.textContent;
                atomlabelObj.position.copy( atomObject.position );

                // console.log("label",label);
                // const axesHelper = new THREE.AxesHelper( 55 );
                // scope.scene.getChildByName('root').add( axesHelper );
                scope.scene.getChildByName('root').add(atomlabelObj);
            }

            // Make the bonds

            positionsBonds.count = positionsBonds.array.length;

            let colorsStart = geometryBonds.getAttribute('colorStart');
            let colorsEnd = geometryBonds.getAttribute('colorEnd');

            for (let i = 0; i < positionsBonds.count; i += 6) {



                let start = new THREE.Vector3( positionsBonds.array[i], positionsBonds.array[i+1], positionsBonds.array[i+2]);
                let end   = new THREE.Vector3( positionsBonds.array[i+3],positionsBonds.array[i+4],positionsBonds.array[i+5]);

                let HALF_PI = + Math.PI * .5;
                let distance = start.distanceTo(end);

                let cylinder = new THREE.CylinderGeometry(0.2, 0.2, distance/2, 10, 4, false);

                let orientation = new THREE.Matrix4();//a new orientation matrix to offset pivot
                let offsetRotation = new THREE.Matrix4();//a matrix to fix pivot rotation
                let offsetPosition = new THREE.Matrix4();//a matrix to fix pivot position

                orientation.lookAt( start, end, new THREE.Vector3(0,1,0));//look at destination
                offsetRotation.makeRotationX(HALF_PI);//rotate 90 degs on X
                orientation.multiply(offsetRotation);//combine orientation with rotation transformations
                cylinder.applyMatrix4(orientation);



                let bond1 = new THREE.Mesh(cylinder, new THREE.MeshPhongMaterial({
                    color: new THREE.Color(colorsStart[i/6][0], colorsStart[i/6][1], colorsStart[i/6][2]),
                    flatShading: false,
                }));

                bond1.name = "Bond:" + i;
                bond1.position.copy(start);
                bond1.position.lerp(end, 0.25);

                scope.scene.getChildByName('root').add(bond1);


                let bond2 = new THREE.Mesh(cylinder, new THREE.MeshPhongMaterial({
                    color: new THREE.Color(colorsEnd[i/6][0], colorsEnd[i/6][1], colorsEnd[i/6][2]),
                    flatShading: false,
                }));

                bond2.name = "Bond:" + (i+1);
                bond2.position.copy(start);
                bond2.position.lerp(end, 0.75);
                scope.scene.getChildByName('root').add(bond2);


            }

            // zoom to molecule
            scope.zoomer(scope.scene.getChildByName('root'));

            // kick renderer
            scope.kickRendererOnDemand();
        });
    }

    /*  Auto zoom on obj with multiple meshes */
    computeSceneBoundingSphereAll(myGroupObj)
    {
        let sceneBSCenter = new THREE.Vector3(0,0,0);
        let sceneBSRadius = 0;
        let nObjects = 0;

        myGroupObj.traverse( function (object)
        {
            if (object instanceof THREE.Mesh)
            {

                //console.log(object.position);
                sceneBSCenter.add( object.position );
                nObjects ++;
            }
        } );

        // console.log(nObjects, sceneBSCenter);
        sceneBSCenter.divideScalar(nObjects);

        myGroupObj.traverse( function (object)
        {
            if (object instanceof THREE.Mesh)
            {
                object.geometry.computeBoundingSphere();

                // Object radius
                let radius = object.geometry.boundingSphere.radius;

//                console.log(object.name + " " + radius, object.position);


                if (radius) {
                     sceneBSRadius = Math.max(sceneBSRadius, radius + object.position.length());
                }
            }
        } );

        return [sceneBSCenter, sceneBSRadius];
    }


    //-------------------- loading from saved data --------------------------------------
    loader_asset_exists( pathUrl = null, mtlFilename = null,
                                 objFilename= null, pdbFileContent = null,
                                 fbxFilename = null, glbFilename = null) {

        if (this.scene != null) {
            if (this.renderer)
                this.clearAllAssets();
        }

        // PDB
        if (pdbFileContent) {
            console.log("Loading from existing resource","PDB");

            this.loadMolecule(pdbFileContent, "loader_asset_exists");

            // GLB
        } else if (glbFilename){
            console.log("Loading from existing resource","GLB");
            //console.log("glbFilename", glbFilename);

            // Instantiate a loader
            const loader = new THREE.GLTFLoader();

            let scope = this;

            // Load a glTF resource
            loader.load(
                // resource URL
                glbFilename,
                // called when the resource is loaded
                function ( gltf ) {

                    if (gltf.animations.length>0) {

                        let glbmixer = new THREE.AnimationMixer(gltf.scene);
                        scope.mixers.push(glbmixer);
                        scope.action = glbmixer.clipAction(gltf.animations[0]);

                        // Display button to start animation inside the Asset 3D previewer
                        document.getElementById("animButton1").style.display = "inline-block";

                    } else {

                        // Display button to start animation inside the Asset 3D previewer
                        document.getElementById("animButton1").style.display = "none";

                    }

                    // Add to root
                    scope.scene.getChildByName('root').add(gltf.scene);
                    scope.zoomer(scope.scene.getChildByName('root'));
                    scope.kickRendererOnDemand();

                    //jQuery('#previewProgressSlider')[0].style.visibility = "hidden";

                },
                // called while loading is progressing
                function ( xhr ) {

                    console.log( ( xhr.loaded / xhr.total * 100 ) + '% loaded' );

                },
                // called when loading has errors
                function ( error ) {

                    console.log( 'An error happened', error );

                }
            );

            // OBJ load
        } else if (pathUrl) {

            console.log("Loading from existing resource","OBJ");

            let scope = this;

            let manager = new THREE.LoadingManager();

            if (objFilename) { // this means that 3D model exists for this asset

                var mtlLoader = new THREE.MTLLoader();

                mtlLoader.setPath(pathUrl);

                mtlLoader.load(mtlFilename, function (materials) {
                    materials.preload();

                    var objLoader = new THREE.OBJLoader(manager);
                    objLoader.setMaterials(materials);
                    objLoader.setPath(pathUrl);

                    objLoader.load(objFilename, 'after',
                        // OnObjLoad
                        function (object) {

                            // Find bounding sphere
                            var sphere = scope.computeSceneBoundingSphereAll(object);

                            // translate object to the center
                            object.traverse(function (object) {
                                if (object instanceof THREE.Mesh) {
                                    object.geometry.translate(-sphere[0].x, -sphere[0].y, -sphere[0].z);
                                }
                            });

                            // Add to pivot
                            scope.scene.getChildByName('root').add(object);

                            // Find new zoom
                            var totalradius = sphere[1];
                            scope.controls.minDistance = 0.001 * totalradius;
                            scope.controls.maxDistance = 3 * totalradius;

                            scope.zoomer(scope.scene.getChildByName('root'));
                            scope.kickRendererOnDemand();
                            //jQuery('#previewProgressSlider')[0].style.visibility = "hidden";
                        },
                        //onObjProgressLoad
                        function (xhr) {

                            //console.log(xhr);
                            document.getElementById('previewProgressLabel').innerHTML = Math.round(xhr.loaded / 1000) + "KB";
                            // if (xhr.lengthComputable) {
                            //
                            // }
                        },
                        //onObjErrorLoad
                        function (xhr) {
                            console.log("Error 351");
                        }
                    );
                });


            } else if (fbxFilename){

                console.log("Loading from existing resource","FBX");

                // split texture string into each texture
                let url_files = textures_fbx_string_connected.split('|');

                if (url_files[0].includes('.jpg')){
                    this.nJpg = url_files.length;
                } else if (url_files[0].includes('.png')){
                    this.nJpg = url_files.length;
                } else if (url_files[0].includes('.gif')){
                    this.nJpg = url_files.length;
                }

                // Add the fbx also
                url_files.push(pathUrl + fbxFilename);

                for (let i=0; i < url_files.length; i++) {

                    let xhr = new XMLHttpRequest();
                    let basename = '';

                    let url = url_files[i];//.replace('http:', 'https:');


                    if( url.includes(".txt") ) {

                        // We want the basename and the extension for naming the file object
                        basename = url.replace('.txt', '.fbx');
                        basename = new String(basename).substring(basename.lastIndexOf('/') + 1);

                        // Set xhr to get the url as text
                        xhr.open('GET', url, true);
                        //xhr.responseType = 'text';
                        xhr.responseType = 'arraybuffer';

                    } else if (url.includes("texture") ) {

                        basename = new String(url).substring(url.lastIndexOf('/') + 1);

                        let file_extension = basename.split('.').pop();

                        let i_first_underscore = basename.indexOf('_');
                        let i_last_underscore = basename.lastIndexOf('_');
                        basename = basename.substring(i_first_underscore+1, i_last_underscore);
                        basename = basename.replace('texture_','');

                        basename = basename  + "." + file_extension;
                        xhr.open('GET', url, true);
                        xhr.responseType = 'blob';
                    }

                    let scope = this;

                    xhr.onload = function (e) {
                        if (this.status === 200) {
                            let file = new File([this.response], basename);
                            file_reader_cortex(file, scope);
                        }
                    };

                    xhr.send();
                }

            } else {
                console.log("WARNING", "UNKNOWN 155");
            }
        }

    }



    // ----------------- Auxiliary ---------------------------

    /* Zoom to object */
    zoomer(towhatObj){ // FBX or OBJ


        let sphere = this.computeSceneBoundingSphereAll( towhatObj );

        // translate object to the center
        // towhatObj.traverse( function (object) {
        //     if (object instanceof THREE.Mesh) {
        //         //object.position.add(new THREE.Vector3(-sphere[0].x, -sphere[0].y, -sphere[0].z));
        //         //object.geometry.translate( - sphere[0].x, - sphere[0].y, - sphere[0].z) ;
        //     }
        // });

        // let centerRadius = scope.computeSceneBoundingSphereAll(scope.scene.getChildByName('root'));
        // console.log("Estimated center", centerRadius[0]);
        // console.log("Estimated radius", centerRadius[1]);
        //
        // const geometryBall = new THREE.SphereGeometry( centerRadius[1], 32, 32 );
        // const materialBall = new THREE.MeshBasicMaterial( {color: 0xffff00, wireframe: true} );
        // const sphereBall = new THREE.Mesh( geometryBall, materialBall );
        // sphereBall.position.copy( centerRadius[0] );
        // sphereBall.name = "Center Ball"
        // scope.scene.getChildByName('root').add( sphereBall );



        let totalRadius = sphere[1];
        this.controls.minDistance = 0.001*totalRadius;
        this.controls.maxDistance = 13*totalRadius;
        this.resizeDisplayGL();
        this.controls.update();
    }

    // Resize Renderer and Label Renderer
    resizeDisplayGL() {

        // This is needed for TrackballControls
        //this.controls.handleResize();

        this.recalcAspectRatio();
        this.renderer.setSize(this.canvas.offsetWidth, this.canvas.offsetHeight, false);
        this.labelRenderer.setSize(this.canvas.offsetWidth, this.canvas.offsetHeight, false);

        this.updateCamera();
    }

    // Recalculate canvas aspect ratio
    recalcAspectRatio() {
        this.aspectRatio = ( this.canvas.offsetHeight === 0 ) ? 1 : this.canvas.offsetWidth / this.canvas.offsetHeight;
    }

    // Reset Camera
    resetCamera() {
        this.camera.position.copy(this.cameraDefaults.posCamera);
        this.cameraTarget.copy(this.cameraDefaults.posCameraTarget);
        this.updateCamera();
    }

    // Update camera aspect
    updateCamera() {
        this.camera.aspect = this.aspectRatio;
        this.camera.lookAt(this.cameraTarget);
        this.camera.updateProjectionMatrix();
    }

    // Report in console
    _reportProgress(text) {
        console.log('Progress: ' + text);
    }

    //function autoScreenshot() {
        // setTimeout(function(){
        //     jQuery("#button_qrcode").click(); // close qr code
        //     jQuery("#createModelScreenshotBtn").click();
        // },1000);
    //}


    // alterShading() {
    //
    //     var scope = this;
    //     scope.flatShading = !scope.flatShading;
    //     console.log(scope.flatShading ? 'Enabling flat shading' : 'Enabling smooth shading');
    //
    //     scope.traversalFunction = function (material) {
    //         material.flatShading = scope.flatShading;
    //         material.needsUpdate = true;
    //     };
    //     var scopeTraverse = function (object3d) {
    //         scope.traverseScene(object3d);
    //     };
    //     scope.pivot.traverse(scopeTraverse);
    // }
    //
    // alterDouble() {
    //
    //     var scope = this;
    //     scope.doubleSide = !scope.doubleSide;
    //     console.log(scope.doubleSide ? 'Enabling DoubleSide materials' : 'Enabling FrontSide materials');
    //
    //     scope.traversalFunction = function (material) {
    //         material.side = scope.doubleSide ? THREE.DoubleSide : THREE.FrontSide;
    //     };
    //
    //     var scopeTraverse = function (object3d) {
    //         scope.traverseScene(object3d);
    //     };
    //     scope.pivot.traverse(scopeTraverse);
    // }


    // traverseScene(object3d) {
    //     if (object3d.material instanceof THREE.MultiMaterial) {
    //         var materials = object3d.material.materials;
    //         for (var name in materials) {
    //             if (materials.hasOwnProperty(name)) this.traversalFunction(materials[name]);
    //         }
    //     } else if (object3d.material) {
    //         this.traversalFunction(object3d.material);
    //     }
    // }

}





