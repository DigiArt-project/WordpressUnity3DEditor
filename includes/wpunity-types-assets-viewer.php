<?php

function wpunity_asset_viewer($curr_path,$textmtl,$url_obj,$post_title){
    ?>
        <!-- START 3D -->
        <script src="<?php echo plugins_url() ?>/WordpressUnity3DEditor/js_libs/threejs79/three.js"></script>
        <script src="<?php echo plugins_url() ?>/WordpressUnity3DEditor/js_libs/threejs79/OBJLoader.js"></script>
        <script src="<?php echo plugins_url() ?>/WordpressUnity3DEditor/js_libs/threejs79/MTLLoader.js"></script>
        <script src="<?php echo plugins_url() ?>/WordpressUnity3DEditor/js_libs/threejs79/OrbitControls.js"></script>

        <script>
            function onWindowResize() {
                windowW = container3d_previewer.clientWidth;
                windowH = Math.round(windowW*2/3);

                camera.aspect = windowW / windowH;
                camera.updateProjectionMatrix();
                renderer.setSize(windowW, windowH);

                container3d_previewer.style.height = renderer.domElement.height + "px";
                container3d_previewer.style.width = renderer.domElement.width + "px";
            }

            function animate() {
                requestAnimationFrame(animate);
                //scene.rotation.y += 0.01;
                // No need to render continuously because there are no changes. Render only when OrbitControls change
                render();
            }
            function render() {
                renderer.render(scene, camera);
            }
        </script>

        <script>
            container3d_previewer = document.getElementById('vr-preview');
            windowW = container3d_previewer.clientWidth; // Do not remove -14. I do not know why reduction is needed.
            windowH = Math.round(windowW * 2/3);

            var camera, scene, renderer;

            camera = new THREE.PerspectiveCamera( 45, windowW / windowH, 1, 2000);

            camera.position.z = 10;
            camera.position.x = 0;
            camera.position.y = 10;

            camera.lookAt(new THREE.Vector3(0, 0, 0));

            // scene
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0xffffff);

            var directionalLight = new THREE.DirectionalLight(0xffffff);
            directionalLight.position.set(0, 30, 0);
            scene.add(directionalLight);

            // ------------ Total Manager -----------------
            var manager = new THREE.LoadingManager();
            manager.onProgress = function (item, loaded, total) {
                //console.log( item, loaded, total );
            };

            var mtlLoader = new THREE.MTLLoader();

            var curr_path = '<?php echo $curr_path?>';
            mtlLoader.setPath(curr_path);

            var textmtl = <?php echo json_encode($textmtl)?>;
            if (textmtl != '')
                mtlLoader.loadfromtext(textmtl, function (materials) {
                    materials.preload();

                    var objLoader = new THREE.OBJLoader(manager);
                    objLoader.setMaterials(materials);

                    objLoader.load('<?php echo $url_obj?>',

                        // OnObjLoad
                        function (object) {
                            // This makes the obj double sided (put it in a dat.gui button better)
                            object.traverse(function (node) {

                                //if (node.material)
                                //    node.material.side = THREE.DoubleSide;

                                if (node instanceof THREE.Mesh)
                                    node.isDigiArt3DMesh = true;
                            });


                            object.name = '<?php echo $post_title ?>';

                            scene.add(object);
                        },

                        //onObjProgressLoad
                        function (xhr) {

                            if (xhr.lengthComputable) {

                                var percentComplete = xhr.loaded / xhr.total * 100;

//                                        var bar = $("#progressbar").get(0).offsetWidth;
//                                        bar = Math.floor(bar * xhr.loaded / xhr.total);
//
//                                        $("#bar").get(0).style.width = bar + "px";
//                                        var downloadedBytes = "Downloaded: " + xhr.loaded + " / " + xhr.total + ' bytes';
//
//                                        $(".result").get(0).innerHTML = downloadedBytes;
//                                        //            console.log(Math.round(percentComplete, 2) + '% downloaded');

//                                        if (xhr.loaded == xhr.total) {
//                                            $("#message").get(0).innerHTML = "Completed";
//                                            //$("#message").get(0).style.display = "none";
//                                            //$("#progressbar").get(0).style.display = "none";
//                                        }


                            }
                        },

                        //onObjErrorLoad
                        function (xhr) {
                        }
                    );

                });

            //

            var mycanvas3d = document.getElementById("canvas3d");
            renderer = new THREE.WebGLRenderer({canvas: mycanvas3d});

            //container3d_previewer.height = canvas.height - 10;

            //renderer.setPixelRatio( window.devicePixelRatio );
            renderer.setSize(windowW, windowH);
            container3d_previewer.appendChild(renderer.domElement);


            //container3d_previewer.clientHeight = renderer.domElement.height;

            container3d_previewer.style.height = renderer.domElement.height + "px";
            container3d_previewer.style.width = renderer.domElement.width + "px";

            //console.log( renderer.domElement.height, container3d_previewer.clientHeight );


            // Orbit controls
            controls = new THREE.OrbitControls(camera, renderer.domElement);
            controls.addEventListener('change', render); // add this only if there is no animation loop (requestAnimationFrame)
            controls.enableDamping = true;
            controls.dampingFactor = 0.25;
            controls.enableZoom = true;

            // Resize callback
            window.addEventListener('resize', onWindowResize, false);

            // start rendering
            animate();


        </script>

    <?php
}
?>