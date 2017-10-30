<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <style>

        body {
            margin: 0;
            font-family: Arial;
            overflow: hidden;
        }

        a {
            color: #ffffff;
        }

        #info {
            position: absolute;
            top: 0px;
            width: 100%;
            color: #ffffff;
            padding: 5px;
            font-family: Monospace;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            z-index: 1;
        }

        #menu {
            position: absolute;
            bottom: 20px;
            width: 100%;
            text-align: center;
            padding: 0;
            margin: 0;
        }

        button {
            color: rgb(255,255,255);
            background: transparent;
            border: 0px;
            padding: 5px 10px;
            cursor: pointer;
        }
        button:hover {
            background-color: rgba(0,255,255,0.5);
        }
        button:active {
            color: #000000;
            background-color: rgba(0,255,255,1);
        }

        .label {
            text-shadow: -1px 1px 1px rgb(0,0,0);
            margin-left: 25px;
        }


    </style>
</head>
<body>
<script src="three.js"></script>
<script src="TrackballControls.js"></script>
<script src="PDBLoader.js"></script>
<script src="CSS2DRenderer.js"></script>

<div id="container"></div>
<div id="info"><a href="http://threejs.org" target="_blank" rel="noopener">three.js webgl</a> - molecules</div>
<div id="menu"></div>

<script>

    var camera, scene, renderer, labelRenderer;
    var controls;

    var root;

    var MOLECULES = {
        "Water": "water.pdb",
        "Ethanol": "ethanol.pdb",
        "Salt": "salt.pdb",
        "Ammonia": "ammonia.pdb",
        "Methane": "methane.pdb",
        "Methanol": "methanol.pdb",
        "MethylChloride": "MethylChloride.pdb",
        "propane": "propane.pdb",
        "Ethylene": "ethylene.pdb",
        "AluminumOxide": "AluminumOxide.pdb",
    };

    var loader = new THREE.PDBLoader();

    var menu = document.getElementById( 'menu' );

    init();
    animate();

    function init() {

        scene = new THREE.Scene();
        scene.background = new THREE.Color( 0x050505 );

        camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 1, 5000 );
        camera.position.z = 1000;
        scene.add( camera );

        var light = new THREE.DirectionalLight( 0xffffff, 0.8 );
        light.position.set( 1, 1, 1 );
        scene.add( light );

        var light = new THREE.DirectionalLight( 0xffffff, 0.5 );
        light.position.set( -1, -1, 1 );
        scene.add( light );

        root = new THREE.Group();
        scene.add( root );

        //

        renderer = new THREE.WebGLRenderer( { antialias: true } );
        renderer.setPixelRatio( window.devicePixelRatio );
        renderer.setSize( window.innerWidth, window.innerHeight );
        document.getElementById( 'container' ).appendChild( renderer.domElement );

        labelRenderer = new THREE.CSS2DRenderer();
        labelRenderer.setSize( window.innerWidth, window.innerHeight );
        labelRenderer.domElement.style.position = 'absolute';
        labelRenderer.domElement.style.top = '0';
        labelRenderer.domElement.style.pointerEvents = 'none';
        document.getElementById( 'container' ).appendChild( labelRenderer.domElement );

        //

        controls = new THREE.TrackballControls( camera, renderer.domElement );
        controls.minDistance = 500;
        controls.maxDistance = 2000;

        //

        loadMolecule( '../../assets/molecules/ethylene.pdb' );
        createMenu();

        //

        window.addEventListener( 'resize', onWindowResize, false );

    }

    //

    function generateButtonCallback( url ) {

        return function ( event ) {

            loadMolecule( url );

        }

    }

    function createMenu() {

        for ( var m in MOLECULES ) {

            var button = document.createElement( 'button' );
            button.innerHTML = m;
            menu.appendChild( button );

            var url = '../../assets/molecules/' +  MOLECULES[ m ];

            button.addEventListener( 'click', generateButtonCallback( url ), false );

        }

    }

    //

    function loadMolecule( url ) {

        while ( root.children.length > 0 ) {

            var object = root.children[ 0 ];
            object.parent.remove( object );

        }

        loader.load( url, function ( pdb ) {

            console.log("pdb", pdb);

            var geometryAtoms = pdb.geometryAtoms;
            var geometryBonds = pdb.geometryBonds;
            var json = pdb.json;

            var boxGeometry = new THREE.BoxBufferGeometry( 1, 1, 1 );
            var sphereGeometry = new THREE.IcosahedronBufferGeometry( 1, 2 );

            var offset = geometryAtoms.center();
            geometryBonds.translate( offset.x, offset.y, offset.z );

            var positions = geometryAtoms.getAttribute( 'position' );
            var colors = geometryAtoms.getAttribute( 'color' );

            var position = new THREE.Vector3();
            var color = new THREE.Color();

            for ( var i = 0; i < positions.count; i ++ ) {

                position.x = positions.getX( i );
                position.y = positions.getY( i );
                position.z = positions.getZ( i );

                color.r = colors.getX( i );
                color.g = colors.getY( i );
                color.b = colors.getZ( i );

                var material = new THREE.MeshPhongMaterial( { color: color } );

                var object = new THREE.Mesh( sphereGeometry, material );
                object.position.copy( position );
                object.position.multiplyScalar( 75 );
                object.scale.multiplyScalar( 25 );
                root.add( object );

                var atom = json.atoms[ i ];

                var text = document.createElement( 'div' );
                text.className = 'label';
                text.style.color = 'rgb(' + atom[ 3 ][ 0 ] + ',' + atom[ 3 ][ 1 ] + ',' + atom[ 3 ][ 2 ] + ')';
                text.textContent = atom[ 4 ];

                var label = new THREE.CSS2DObject( text );
                label.position.copy( object.position );
                root.add( label );

            }

            positions = geometryBonds.getAttribute( 'position' );

            var start = new THREE.Vector3();
            var end = new THREE.Vector3();

            for ( var i = 0; i < positions.count; i += 2 ) {

                start.x = positions.getX( i );
                start.y = positions.getY( i );
                start.z = positions.getZ( i );

                end.x = positions.getX( i + 1 );
                end.y = positions.getY( i + 1 );
                end.z = positions.getZ( i + 1 );

                start.multiplyScalar( 75 );
                end.multiplyScalar( 75 );

                var object = new THREE.Mesh( boxGeometry, new THREE.MeshPhongMaterial( 0xffffff ) );
                object.position.copy( start );
                object.position.lerp( end, 0.5 );
                object.scale.set( 5, 5, start.distanceTo( end ) );
                object.lookAt( end );
                root.add( object );

            }

            render();

        } );

    }

    //

    function onWindowResize() {

        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();

        renderer.setSize( window.innerWidth, window.innerHeight );
        labelRenderer.setSize( window.innerWidth, window.innerHeight );

        render();

    }

    function animate() {

        requestAnimationFrame( animate );
        controls.update();

        var time = Date.now() * 0.0004;

        root.rotation.x = time;
        root.rotation.y = time * 0.7;

        render();

    }

    function render() {

        renderer.render( scene, camera );
        labelRenderer.render( scene, camera );

    }

</script>
</body>
</html>
