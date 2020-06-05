// For detecting collisions while moving
// Info at http://www.html5rocks.com/en/tutorials/pointerlock/intro/

var avatarControlsEnabled = false;

// Initialize
function initPointerLock() {

    var firstPersonBlocker = document.getElementById('firstPersonBlocker');
    var firstPersonBlockerBtn = document.getElementById('firstPersonBlockerBtn');

    var havePointerLock = 'pointerLockElement' in document || 'mozPointerLockElement' in document || 'webkitPointerLockElement' in document;

    avatarControlsEnabled = false;
    envir.avatarControls.enabled = false;

    if (havePointerLock) {
        var element = document.body;

        // var pointerlockchange = function (event) {
        //
        //     if (document.pointerLockElement === element || document.mozPointerLockElement === element || document.webkitPointerLockElement === element) {
        //         //------------ AVATAR ---------------------------
        //         avatarControlsEnabled = true;
        //
        //         envir.avatarControls.enabled = true;
        //         envir.orbitControls.enabled = false;
        //
        //         firstPersonBlocker.style.display = 'none';
        //
        //         // When in AVATAR the avatarControls position is the orbit controls target
        //         envir.avatarControls.getObject().position = envir.orbitControls.target;
        //
        //         envir.avatarControls.getObject().children[0].position = envir.orbitControls.target;
        //         envir.avatarControls.getObject().children[1].position = envir.orbitControls.target;
        //
        //         isComposerOn = false;
        //         transform_controls.visible  = false;
        //         // if in 3rd person view then show the cameraobject
        //         envir.getSteveFrustum().visible = envir.thirdPersonView && avatarControlsEnabled;
        //
        //     } else {
        //         // ------------- ORBIT --------------------------
        //         avatarControlsEnabled = false;
        //         envir.avatarControls.enabled = false;
        //         envir.orbitControls.enabled = true;
        //
        //         firstPersonBlocker.style.display = '-webkit-box';
        //         firstPersonBlocker.style.display = '-moz-box';
        //         firstPersonBlocker.style.display = 'box';
        //         firstPersonBlockerBtn.style.display = '';
        //         jQuery('#thirdPersonBlockerBtn')[0].style.display = '';
        //
        //         envir.thirdPersonView = false;
        //
        //         envir.scene.getObjectByName("SteveOld").visible = false;
        //         envir.scene.getObjectByName("Steve").visible = true;
        //
        //         isComposerOn = true;
        //         transform_controls.visible  = true;
        //         envir.getSteveFrustum().visible = true;
        //
        //         // ToDo: Zoom
        //         envir.orbitControls.reset();
        //         findSceneDimensions();
        //         envir.updateCameraGivenSceneLimits();
        //     }
        // };
        //
        // var pointerlockerror = function (event) {
        //     firstPersonBlockerBtn.style.display = '';
        //     jQuery('#thirdPersonBlockerBtn')[0].style.display = "";
        // };

        // Hook pointer lock state change events
        // document.addEventListener('pointerlockchange', pointerlockchange, false);
        // document.addEventListener('pointerlockerror', pointerlockerror, false);

    } else {
        firstPersonBlockerBtn.innerHTML = 'Your browser doesn\'t seem to support Pointer Lock API';
    }
}


function firstPersonViewWithoutLock(){

    if (!avatarControlsEnabled) {

        // ----------- First person view ----------------------
        avatarControlsEnabled = true;

        // Mouse controls Avatar viewing
        envir.avatarControls.enabled = false;

        // Mouse controls orbit
        envir.orbitControls.enabled = false;

        // The avatarControls position is the orbit controls target
        envir.avatarControls.getObject().position = envir.orbitControls.target;

        envir.avatarControls.getObject().children[0].position = envir.orbitControls.target;
        envir.avatarControls.getObject().children[1].position = envir.orbitControls.target;

        transform_controls.visible = false;

        // Glow effect change camera
        envir.composer = [];
        envir.setComposer();

        envir.isComposerOn = true;

        // if in 3rd person view then show the cameraobject
        envir.getSteveFrustum().visible = envir.thirdPersonView && avatarControlsEnabled;
    }else{
        // ------------- ORBIT --------------------------
        avatarControlsEnabled = false;

        envir.avatarControls.enabled = false;

        envir.orbitControls.enabled = true;

        firstPersonBlocker.style.display = '-webkit-box';
        firstPersonBlocker.style.display = '-moz-box';
        firstPersonBlocker.style.display = 'box';

        // firstPersonBlockerBtn.style.display = '';
        // jQuery('#thirdPersonBlockerBtn')[0].style.display = '';

        envir.thirdPersonView = false;

        envir.scene.getObjectByName("SteveOld").visible = false;
        envir.scene.getObjectByName("Steve").visible = true;


        envir.composer = [];
        envir.setComposer();

        envir.isComposerOn = true;

        transform_controls.visible  = true;
        envir.getSteveFrustum().visible = true;

        // ToDo: Zoom
        envir.orbitControls.reset();

        findSceneDimensions();
        envir.updateCameraGivenSceneLimits();

    }
}
