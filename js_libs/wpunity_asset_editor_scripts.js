/**
 * Created by tpapazoglou on 11/7/2017.
 * Modified by dverver on 18/10/2017: Multiple jpgs as textures. fReader called once not twice for the same file.
 * dverver 02/04/2018
 */
'use strict';

var mtlFileContent = '';
var objFileContent = '';
var fbxFileContent = '';
var pdbFileContent = '';

var nObj = 0;
var nMtl = 0;
var nJpg = 0;
var nPng = 0;
var nPdb = 0;

//jQuery('#3dAssetForm').remove

function wpunity_read_file(howtoread, file, type, callback, canvas, filename) {
    var content = '';
    var reader = new FileReader();

    if (file) {

        if (howtoread === 'Url')
            reader.readAsDataURL(file);
        else if (howtoread === 'Text')
            reader.readAsText(file);
        else if (howtoread === 'ArrayBuffer')
            reader.readAsArrayBuffer(file);

        // Closure to capture the file information.
        reader.onload = (function(reader) {
            return function() {
                content = reader.result;
                callback(content, type, canvas, filename);
            };
        })(reader);
    } else {
        callback(content, type, canvas, filename);
    }
}

// Callback is fired when obj & mtl inputs have files. Preview is loaded automatically.
// We can expand this for 'fbx' files too.
function wpunity_load_file_callback(content, type, canvas, filename) {

    switch (type) {
        case 'fbx':
            fbxFileContent = content ? content : '';
            break;

        case 'mtl':
            mtlFileContent = content ? content : '';

            // Replace quotes because they create a bug in input form
            mtlFileContent = mtlFileContent.replace(/'/g, "");

            document.getElementById('mtlFileInput').value = mtlFileContent;
            checkerCompleteReading( canvas);
            break;

        case 'obj':
            // Obj as ArrayBuffer (needed for ObjLoader2 and webworkers)
            objFileContent = content ? content : '';

            // Obj as text (needed for ObjLoader in 3D editor)
            var dec = new TextDecoder();

            document.getElementById('objFileInput').value = dec.decode(objFileContent);

            checkerCompleteReading(canvas);

            break;

        case 'pdb':
            pdbFileContent = content ? content : '';

            console.log("loaded pdb file", pdbFileContent);

            // set the input value
            document.getElementById('pdbFileInput').value = pdbFileContent;

            canvas.loadMolecule(content);

            break;
    }


    if (content) {
        if(type === 'texture') {

            /*jQuery("#texturePreviewImg").attr('src', '').attr('src', content);*/

            jQuery('#3dAssetForm').append('<input type="hidden" name="textureFileInput['+filename+']" id="textureFileInput" value="' + content + '" />');

            //var textureFileInput = document.getElementsByName('textureFileInput[]');
            // textureFileInput[0] = document.createElement("INPUT");
            // textureFileInput[filename].setAttribute("type", "hidden");
            //textureFileInput[filename].value = content;

            checkerCompleteReading(canvas);
        }

        if ((objFileContent && mtlFileContent) || pdbFileContent) {

        } else {
            wpunity_reset_sshot_field();
        }

    } else {
        document.getElementById("assetPreviewContainer").innerHTML = "";
    }

}

function wpunity_extract_file_extension(fn) {
    return fn ? fn.split('.').pop().toLowerCase() : '';
}


function wpunity_clear_asset_files(wu_webw_3d_view) {

    if (wu_webw_3d_view.renderer) {
        wu_webw_3d_view.clearAllAssets();
    }

    document.getElementById("fbxFileInput").value = "";
    document.getElementById("mtlFileInput").value = "";
    document.getElementById("objFileInput").value = "";
    document.getElementById("pdbFileInput").value = "";

    while ( jQuery("[id^=textureFileInput]").length > 0) {
         jQuery("[id^=textureFileInput]")[0].remove();
   }

    document.getElementById("fileUploadInput").value = "";

    document.getElementById("sshotFileInput").value = "";
    /*jQuery("#texturePreviewImg").attr('src', texturePreviewDefaultImg);*/
    jQuery("#sshotPreviewImg").attr('src', sshotPreviewDefaultImg);
    jQuery("#objectPreviewTitle").hide();

    objFileContent = '';
    fbxFileContent = '';
    mtlFileContent = '';
    pdbFileContent = '';

    nObj = 0;
    nMtl = 0;
    nJpg = 0;
    nPng = 0;
    nPdb = 0;
}

function wpunity_reset_panels(wu_webw_3d_view) {

    // Clear all
    wpunity_clear_asset_files(wu_webw_3d_view);

    if (jQuery("ProducerPlotTooltip")) {
        jQuery("div.ProducerPlotTooltip").remove();
    }

    jQuery("#assetDescription").show();
    jQuery("#doorDetailsPanel").hide();
    jQuery("#terrainPanel").hide();
    jQuery("#consumerPanel").hide();
    jQuery("#producerPanel").hide();
    //jQuery("#poiImgDetailsPanel").hide();
    //jQuery("#poiVideoDetailsPanel").hide();
    jQuery("#objectPreviewTitle").hide();
    //jQuery("#moleculeOptionsPanel").hide();
    jQuery("#moleculeFluidPanel").hide();
    jQuery("#chemistryBoxOptionsPanel").hide();
}


function loadAssetPreviewer(wu_webw_3d_view_local, multipleFilesInputElem) {

    // Load from selected files
    var _handleFileSelect = function ( event  ) {

        document.getElementById('asset_sourceID').value ="";

        // copy because clear asset files in the following clears the total input fields also
        var files = {... event.target.files};

        // Clear the previously loaded
        wpunity_clear_asset_files(wu_webw_3d_view_local);

        //  Read each file and put the string content in an input dom
        for ( var i = 0, file; file = files[ i ]; i++) {

            if ( file.name.indexOf( '\.pdb' ) > 0 ) {
                nPdb = 1;
                wpunity_read_file('Text' , file, 'pdb', wpunity_load_file_callback, wu_webw_3d_view_local);
            }

            if ( file.name.indexOf( '\.obj' ) > 0 ) {
                nObj = 1;
                wpunity_read_file('ArrayBuffer' , file, 'obj', wpunity_load_file_callback, wu_webw_3d_view_local);
            }
            if ( file.name.indexOf( '\.mtl' ) > 0 ) {
                nMtl = 1;
                wpunity_read_file('Text', file, 'mtl', wpunity_load_file_callback, wu_webw_3d_view_local );
            }
            if ( file.name.indexOf( '\.jpg' ) > 0 ) {
                nJpg ++;

                wpunity_read_file('Url', file, 'texture', wpunity_load_file_callback, wu_webw_3d_view_local, file.name);
            }
            if ( file.name.indexOf( '\.png' ) > 0 ) {
                nPng ++;

                wpunity_read_file('Url', file, 'texture', wpunity_load_file_callback, wu_webw_3d_view_local, file.name);
            }
        }

    };
    multipleFilesInputElem.addEventListener( 'change' , _handleFileSelect, false );

    // Start rendering if even nothing is loaded
    var resizeWindow = function () {
        wu_webw_3d_view_local.resizeDisplayGL();
    };

    window.addEventListener( 'resize', resizeWindow, false );

    var render = function () {
        requestAnimationFrame( render );
        wu_webw_3d_view_local.render();
    };

    wu_webw_3d_view_local.initGL();
    wu_webw_3d_view_local.resizeDisplayGL();
    wu_webw_3d_view_local.initPostGL();

    // kick render loop
    render();

    // for existing 3D models
    if (typeof path_url != "undefined")
        loader_asset_exists(path_url, mtl_file_name, obj_file_name, null);

    if (typeof pdb_file_name != "undefined")
        loader_asset_exists(null, null, null, pdb_file_name);
}

/**
 * Reading from text files on client side
 * @param canvas
 */
function checkerCompleteReading(canvas){

    if (nObj==1 && objFileContent!=='' ){

        // Add loader
        jQuery('#previewProgressSlider').show();

        // Make the definition with the obj
        var uint8Array = new Uint8Array( objFileContent );

        var objectDefinition = {
            name: 'userObj',
            objAsArrayBuffer: uint8Array,
            pathTexture: "",
            mtlAsString: null
        };

        if (nMtl == 0) {
            // Start without MTL
            wu_webw_3d_view.loadFilesUser(objectDefinition);
        } else {
            if (mtlFileContent!==''){

                objectDefinition.mtlAsString = mtlFileContent;

                if (nJpg==0 && nPng==0){
                    // Start without Textures
                    wu_webw_3d_view.loadFilesUser(objectDefinition);

                } else {

                    if ((nPng>0 && nPng === jQuery("input[id='textureFileInput']").length) || ( nJpg>0 && nJpg === jQuery("input[id='textureFileInput']").length) ) {

                        // Get textureFileInput array with jQuery
                        var textFil = jQuery("input[id='textureFileInput']");

                        // Store here the raw image textures
                        objectDefinition.pathTexture = [];

                        for (var k = 0; k < textFil.length; k++){
                            var myname = textFil[k].name;

                            // do some text processing on the names to remove textureFileInput[ and ] from name
                            myname = myname.replace('textureFileInput[','');
                            myname = myname.replace(']','');

                            objectDefinition.pathTexture[myname] = textFil[k].value;
                        }

                        // Start with textures
                        canvas.loadFilesUser(objectDefinition);
                    }
                }
            }
        }
    }
}


/**
 * Reading from url in server side
 * @param pathUrl
 * @param mtlFilename
 * @param objFilename
 */
function loader_asset_exists(pathUrl, mtlFilename, objFilename, pdbFileContent){

    if (wu_webw_3d_view.scene != null) {
        if (wu_webw_3d_view.renderer)
             wu_webw_3d_view.clearAllAssets();
    }

    if (pdbFileContent) {
        wu_webw_3d_view.loadMolecule(pdbFileContent);
        return;
    }

    //--------------- load all from url (in edit asset) --------------
    if (pathUrl  && objFilename ) { // this means that 3D model exists for this asset

        var manager = new THREE.LoadingManager();
        var mtlLoader = new THREE.MTLLoader();

        //var mtl_url = "bfcff4ceba79910cfed496e0b19d2ac3_materialTurbine1.txt";
        //var obj_file_name = "f74d834f96148080b5822a409a4299ff_objTurbine1.txt";
        //var pathUrl = "http://127.0.0.1:8080/digiart-project_Jan17/wp-content/uploads/Models/";

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
                    var sphere = wu_webw_3d_view.computeSceneBoundingSphereAll ( object) ;

                    // translate object to the center
                    object.traverse( function (object) {
                        if (object instanceof THREE.Mesh) {
                            object.geometry.translate(- sphere[0].x, - sphere[0].y, - sphere[0].z) ;
                        }
                    });

                    // Add to pivot
                    wu_webw_3d_view.pivot.add(object);

                    // Find new zoom
                    var totalradius = sphere[1];
                    wu_webw_3d_view.controls.minDistance = 0.001*totalradius;
                    wu_webw_3d_view.controls.maxDistance = 3*totalradius;
                },
                //onObjProgressLoad
                function (xhr) {
                    if (xhr.lengthComputable) {
                    }
                },
                //onObjErrorLoad
                function (xhr) {
                    console.log("Error 351");
                }
            );
        });
    }

}



// for the Energy Turbines
function wpunity_create_slider_component(elemId, range, options) {

    if (range) {

        jQuery( elemId ).slider({
            range: range,
            min: options.min,
            max: options.max,
            values: [ options.values[0], options.values[1] ],
            create: function() {
                jQuery( options.valIds[0] ).val(options.values[0]);
                jQuery( options.valIds[1] ).val(options.values[1]);
            },
            slide: function( event, ui ) {
                jQuery( elemId+"-label" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] + " " +options.units);

            },
            stop: function( event, ui ) {
                jQuery( options.valIds[0] ).val(ui.values[ 0 ]);
                jQuery( options.valIds[1] ).val(ui.values[ 1 ]);
            }

        });
        jQuery( elemId+"-label" ).val( jQuery( elemId ).slider( "values", 0 ) +
            " - " + jQuery( elemId ).slider( "values", 1 ) + " " + options.units );

    } else {

        if (options.inputText) {

            jQuery( elemId ).slider({
                min: options.min,
                max: options.max,
                value: options.value,
                create: function() {
                    jQuery( options.valId ).val(options.value);
                },
                slide: function( event, ui ) {
                    jQuery( elemId+"-label" ).val( ui.value );

                },
                stop: function( event, ui ) {
                    jQuery( options.valId ).val(ui.value);
                }
            });
            jQuery( elemId+"-label" ).val( jQuery( elemId ).slider( "option", "value" ));


            jQuery(elemId+"-label").change(function () {
                var value = this.value;
                jQuery( elemId ).slider("value", parseInt(value));

            });

            jQuery(elemId+"-label").on('input', function() {
                var value = this.value;
                jQuery( elemId ).slider("value", parseInt(value));
            });


        } else {

            jQuery( elemId ).slider({
                min: options.min,
                max: options.max,
                value: options.value,
                create: function() {
                    jQuery( options.valId ).val(options.value);
                },
                slide: function( event, ui ) {

                    jQuery( elemId+"-label" ).val( ui.value + " " +options.units);
                },
                stop: function( event, ui ) {
                    jQuery( options.valId ).val(ui.value);
                }
            });
            jQuery( elemId+"-label" ).val( jQuery( elemId ).slider( "option", "value" ) + " " + options.units );

        }
    }

    if (options.step) {
        jQuery( elemId ).slider({step: options.step});
    }

    return jQuery( elemId ).slider;
}