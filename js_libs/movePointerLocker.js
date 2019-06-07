// For detecting collisions while moving
// Info at http://www.html5rocks.com/en/tutorials/pointerlock/intro/

var avatarControlsEnabled = false;

var moveForward = false;
var moveBackward = false;
var moveLeft = false;
var moveRight = false;
var moveUp = false;
var moveDown = false;

//var canJump = false;

var prevTime = performance.now();
var velocity = new THREE.Vector3();
var torgue = new THREE.Vector3();

/**
 *   initialize Pointer Lock
 *
 */
function initPointerLock() {

    var firstPersonBlocker = document.getElementById('firstPersonBlocker');
    var firstPersonBlockerBtn = document.getElementById('firstPersonBlockerBtn');

    var havePointerLock = 'pointerLockElement' in document || 'mozPointerLockElement' in document || 'webkitPointerLockElement' in document;

    avatarControlsEnabled = false;
    envir.avatarControls.enabled = false;

    if (havePointerLock) {

        var element = document.body;

        var pointerlockchange = function (event) {



            if (document.pointerLockElement === element || document.mozPointerLockElement === element || document.webkitPointerLockElement === element) {
                //------------ AVATAR ---------------------------
                avatarControlsEnabled = true;

                envir.avatarControls.enabled = true;
                envir.orbitControls.enabled = false;

                firstPersonBlocker.style.display = 'none';



                // When in AVATAR the avatarControls position is the orbit controls target
                envir.avatarControls.getObject().position = envir.orbitControls.target;

                envir.avatarControls.getObject().children[0].position = envir.orbitControls.target;
                envir.avatarControls.getObject().children[1].position = envir.orbitControls.target;

                //console.log("A:" , envir.avatarControls.getObject().rotation._y);

                //console.log("AVATAR", envir.avatarControls.getObject().rotation.y);
                //envir.avatarControls.getObject().position.y = 1.8;

                //shd("envir.avatarControls.getObject().position.x", envir.avatarControls.getObject().position.x);
                //shd("envir.avatarControls.getObject().position.z", envir.avatarControls.getObject().position.z);

                jQuery( "#toggleUIBtn" ).trigger( "click" );

            } else {
                // ------------- ORBIT --------------------------
                avatarControlsEnabled = false;
                envir.avatarControls.enabled = false;
                envir.orbitControls.enabled = true;

                //if (envir.avatarControls.getObject().rotation.y < - Math.PI /2)
                //    envir.avatarControls.getObject().rotation.y += 0.85;

                // envir.cameraOrbit.getObjectByName("recycleBin").visible=true;
                firstPersonBlocker.style.display = '-webkit-box';
                firstPersonBlocker.style.display = '-moz-box';
                firstPersonBlocker.style.display = 'box';
                firstPersonBlockerBtn.style.display = '';
                jQuery('#thirdPersonBlocker')[0].style.display = '';

                envir.thirdPersonView = false;

                envir.scene.getObjectByName("SteveOld").visible = false;
                envir.scene.getObjectByName("Steve").visible = true;

                jQuery( "#toggleUIBtn" ).trigger( "click" );

                // ToDo: Zoom
                envir.orbitControls.reset();
                findSceneDimensions();
                envir.updateCameraGivenSceneLimits();

            }
        };

        var pointerlockerror = function (event) {
            firstPersonBlockerBtn.style.display = '';

            jQuery('#thirdPersonBlocker')[0].style.display = "";
        };

        // Hook pointer lock state change events
        document.addEventListener('pointerlockchange', pointerlockchange, false);
//        document.addEventListener('mozpointerlockchange', pointerlockchange, false);
//        document.addEventListener('webkitpointerlockchange', pointerlockchange, false);

        document.addEventListener('pointerlockerror', pointerlockerror, false);
//        document.addEventListener('mozpointerlockerror', pointerlockerror, false);
//        document.addEventListener('webkitpointerlockerror', pointerlockerror, false);

    } else {
        firstPersonBlockerBtn.innerHTML = 'Your browser doesn\'t seem to support Pointer Lock API';
    }
}

/**
 *
 * Update Steve when Steve walks by key presses
 *
 */
function updatePointerLockControls(){

    //if ( avatarControlsEnabled ) {

    var collisionResults = [];

    // Todo: Get these two out of the loop
    var Steve = envir.scene.getObjectByName("Steve");
    var steveShieldMesh = envir.scene.getObjectByName("SteveShieldMesh") ;

    var time = performance.now();
    var delta = ( time - prevTime ) / 1000;

    // Reductors of velocity
    velocity.x -= velocity.x * 2.0 * delta;
    velocity.y -= velocity.y * 2.0 * delta;
    velocity.z -= velocity.z * 2.0 * delta;

    // Reductor of rotation along Y
    torgue.y = torgue.y * 0.7; // * delta;

    //velocity.y -= 9.8 * 100.0 * delta; // 100.0 = mass
    if (avatarControlsEnabled) {
        if (moveForward) velocity.z -= 0.3 * delta;
        if (moveBackward) velocity.z += 0.3 * delta;
        if (moveLeft) velocity.x -= 0.3 * delta;
        if (moveRight) velocity.x += 0.3 * delta;
    }
    else {
        if (moveForward) velocity.z -= 0.3 * delta;
        if (moveBackward) velocity.z += 0.3 * delta;
        if (moveLeft) torgue.y += 0.3 * delta;
        if (moveRight) torgue.y -= 0.3 * delta;
    }


    if ( moveUp ) velocity.y -= 0.3 * delta;
    if ( moveDown ) velocity.y += 0.3 * delta;

    //if ( isOnObject === true ) {
    //    velocity.z = Math.max( 0, velocity.z );
    //    canJump = true;
    //}

    // Move avatar
    envir.avatarControls.getObject().translateX( velocity.x );
    envir.avatarControls.getObject().translateY( velocity.y );
    envir.avatarControls.getObject().translateZ( velocity.z );

    // if (torgue.y != 0)
    //     console.log(torgue.y);



    if (!avatarControlsEnabled)
        envir.avatarControls.getObject().rotation.y += torgue.y;



    moveUp = false;
    moveDown = false;

    prevTime = time;


}



// TODO: RAYCASTING SIGNIFICANTLY DETERIORATES RENDERING SPEED

//for (var vertexIndex = 0; vertexIndex < 1; vertexIndex++) //cubeRayShield.geometry.vertices.length
//{
//    var localVertex = cubeRayShield.geometry.vertices[vertexIndex].clone();
//    var globalVertex = localVertex.applyProjection(cubeRayShield.matrixWorld);
//
//
//    var steveWorldPosition = Steve.position.clone().applyProjection(Steve.matrixWorld);
//
//    var directionVector = globalVertex.sub( steveWorldPosition  );
//
//    var dirVecNorm = directionVector.clone().normalize();
//
//    // Visualize Raycaster with a line
//    //    var geometryL = new THREE.Geometry();
//    //    var geometryL = new THREE.Geometry();
//    //    geometryL.vertices.push(steveWorldPosition,
//    //        steveWorldPosition.clone().add(dirVecNorm)
//    //    );
//    //    console.log(Steve.position.clone(), Steve.position.clone().add(dirVecNorm));
//    //    envir.scene.add(new THREE.Line(geometryL, new THREE.LineBasicMaterial({color: 0x0000ff})));
//
//    var raycaster = new THREE.Raycaster( steveWorldPosition, dirVecNorm, 1, 10);
//    var actMesh = getActiveMeshes();
//    var collisionResults = raycaster.intersectObjects( actMesh, true );
//}


// Collider test: Make everything touched red
//for ( var i = 0; i < collisionResults.length; i++ )
//    collisionResults[ i ].object.material.color.set( 0xff0000 );


//        var isOnObject = collisionResults.length > 0; // && collisionResults[0].distance < directionVector.length();




// When the avatar moves then move the camera to follow him

//if (!avatarControlsEnabled) {
//    //envir.orbitControls.object.translateX(velocity.x * delta);
//    //envir.orbitControls.object.translateY(velocity.y * delta);
//    //envir.orbitControls.object.translateZ(velocity.z * delta);
//
//
//}
//
//


// Update the camera Y not to be so low
//if ( moveForward )
//    envir.orbitControls.object.translateY( - velocity.y * delta );

//if ( envir.avatarControls.getObject().position.y < 1.80 ) {
//    velocity.y = 0;
//    envir.avatarControls.getObject().position.y = 0;
//    envir.avatarControls.getObject().children[1].position.y=0;
//    envir.avatarControls.getObject().children[0].position.y=0;
//
//    canJump = true;
//}