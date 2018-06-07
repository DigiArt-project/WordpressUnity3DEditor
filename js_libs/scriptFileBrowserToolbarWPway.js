//  AJAX: FETCH Assets 3d
function wpunity_fetchSceneAssetsAjax(isAdmin, gameProjectSlug, urlforAssetEdit, gameProjectID){

    jQuery.ajax({
        url :  isAdmin == "back" ? 'admin-ajax.php' : my_ajax_object_fbrowse.ajax_url,
        type : 'POST',
        data : {
            'action': 'wpunity_fetch_game_assets_action',
            'gameProjectSlug': gameProjectSlug,
            'gameProjectID': gameProjectID
        },

        success : function(responseRecords) {

            responseRecords = responseRecords.items;

            file_Browsing_By_DB(responseRecords, gameProjectSlug, urlforAssetEdit);
        },
        error : function(xhr, ajaxOptions, thrownError){
            console.log("ERROR 51:" + thrownError);
        }
    });
}

/**
 * Start the browser
 * @param responseData
 */
function file_Browsing_By_DB(responseData, gameProjectSlug, urlforAssetEdit) {

    var filemanager = jQuery('#fileBrowserToolbar'),
        //breadcrumbs = jQuery('.breadcrumbs'),
        fileList = filemanager.find('.data'),
        closeButton = jQuery('.bt_close_file_toolbar');

    // Create drag image BEFORE event is fired - THEN call it inside the event
    function createDragImage() {
        var img = jQuery('<img>');
        img.attr('src', '../wp-content/plugins/wordpressunity3deditor/images/ic_asset.png');
        img.css({
            "top": 0,
            "left": 0,
            "position": "absolute",
            "pointerEvents": "none"
        }).appendTo(document.body);
        setTimeout(function() {
            img.remove();
        });
        return img[0];
    }
    var dragImg = createDragImage();

    render(responseData, gameProjectSlug, urlforAssetEdit );

    // Hiding and showing the search box
    filemanager.find('.search').click(function() {
        var search = jQuery(this);
        search.find('span').hide();
        search.find('input[type=search]').show().focus();
    });

    // Listening for keyboard input on the search field.
    // We are using the "input" event which detects cut and paste
    // in addition to keyboard input.

    filemanager.find('input').on('input', function(e){

        var value = this.value.trim();

        if(value.length) {
            filemanager.addClass('searching');

            // Filter the responseData according to value.trim()
            filteredResponseData = selectByTitleComparizon(responseData, value.trim());
            render(filteredResponseData, gameProjectSlug, urlforAssetEdit);
        } else {
            filemanager.removeClass('searching');
            render(responseData, gameProjectSlug, urlforAssetEdit);
        }

    }).on('keyup', function(e){ // Clicking 'ESC' button triggers focusout and cancels the search
        var search = jQuery(this);
        if(e.keyCode === 27)
            search.trigger('focusout');
    }).focusout(function(e){  // Cancel the search
        var search = jQuery(this);
        if(!search.val().trim().length) {
            //window.location.hash = encodeURIComponent(currentPath);
            search.hide();
            search.parent().find('span').show();
        }
    });


    fileList.on({
        click: function(e) {
            //alert("Drag n drop zip files onto 3D space");

            e.preventDefault();
        },

        dragstart: function(e) {

            e.originalEvent.dataTransfer.setDragImage(dragImg, 32, 32);

            var dragData = {
                "title": e.target.attributes.getNamedItem("data-assetslug").value + "_" + Math.floor(Date.now() / 1000),
                "assetid": e.target.attributes.getNamedItem("data-assetid").value,
                "obj": e.target.attributes.getNamedItem("data-objPath").value,
                "objID": e.target.attributes.getNamedItem("data-objID").value,
                "mtl": e.target.attributes.getNamedItem("data-mtlPath").value,
                "mtlID": e.target.attributes.getNamedItem("data-mtlID").value,
                "diffImages": e.target.attributes.getNamedItem("data-diffImages").value,
                "diffImageIDs": e.target.attributes.getNamedItem("data-diffImageIDs").value,
                "categoryID": e.target.attributes.getNamedItem("data-categoryID").value,
                "categoryName": e.target.attributes.getNamedItem("data-categoryName").value,
                "image1id":e.target.attributes.getNamedItem("data-image1id").value,
                "doorName_source":e.target.attributes.getNamedItem("data-doorName_source").value,
                "doorName_target":e.target.attributes.getNamedItem("data-doorName_target").value,
                "isreward":e.target.attributes.getNamedItem("data-isreward").value,
                "sceneName_target":e.target.attributes.getNamedItem("data-sceneName_target").value,
                "sceneID_target":e.target.attributes.getNamedItem("data-sceneID_target").value,
                "isCloned":e.target.attributes.getNamedItem("data-isCloned").value,
                "isJoker":e.target.attributes.getNamedItem("data-isJoker").value
            };

            var jsonDataDrag = JSON.stringify(dragData);
            e.originalEvent.dataTransfer.setData("text/plain", jsonDataDrag);

        },
        drag: function(e) {
            e.preventDefault();
        },
        dragend: function(e) {
            e.preventDefault();
        }
    });

    // Render the HTML for the file manager
    // Here we make the list
    function render(enlistData, gameProjectSlug, urlforAssetEdit) {

        var i, f, name;

        // Empty the old result and make the new one
        fileList.empty().hide();

        if (!enlistData) {
            filemanager.find('.nothingfound').show();
        } else  {
            filemanager.find('.nothingfound').hide();

            for (i = 0; i < enlistData.length; i++) {

                f = enlistData[i];

                var fileSize = bytesToSize(f.size);

                name = escapeHTML(f.name);

                if(f.categoryName=="Molecule")
                    continue;

                if(!f.objPath)
                    continue;

                var fileType = f.objPath.split('.').pop();

                /*var icon = '<span class="icon file f-'+f.categoryID+'">.'+f.categoryName+'</span>';*/
                var img;
                var imgFileExtension;

                if (fileType.toUpperCase() === 'JPG' || fileType.toUpperCase()==='PNG') {
                    imgFileExtension = '';
                }

                // Check if icon of obj exists  file.obj.png or file.obj.jpg
                else if (fileType.toUpperCase() === 'OBJ') {
                    imgFileExtension = '.jpg';
                }

                f.screenImagePath = f.screenImagePath ? f.screenImagePath : "../wp-content/plugins/wordpressunity3deditor/images/ic_no_sshot.png";


                img = '<span class="mdc-list-item__start-detail CenterContents" style="width:72px; margin-right: 8px;"><img draggable="false" src=' + f.screenImagePath +'><br><span class="mdc-typography--caption mdc-theme--text-secondary-on-light">'+ fileSize +'</span></span>';

                var file = jQuery('<li id="asset-'+ f.assetid + '"  class="mdc-list-item mdc-elevation--z2" style="height: 96px; position: relative;">' +
                    '<a class="mdc-list-item editor-asset-tile-style" style="align-items:baseline; left:0; padding:6px 0 6px 6px; height: 100%; width:100%" href="'+ f.objPath +
                    '" title="Drag the card into the plane" ' +
                    'data-assetslug="'+ f.assetSlug +
                    '" data-assetid="'+ f.assetid +
                    '" data-objPath="'+ f.objPath +
                    '" data-objID="'+ f.objID +
                    '" data-mtlPath="'+ f.mtlPath +
                    '" data-mtlID="'+ f.mtlID +
                    '" data-diffImages="'+ f.diffImages +
                    '" data-diffImageIDs="'+ f.diffImageIDs +
                    '" data-categoryID="'+ f.categoryID +
                    '" data-categoryName="'+ f.categoryName +
                    '" data-image1id="'+ f.image1id +
                    '" data-doorName_source="'+ f.doorName_source +
                    '" data-doorName_target="'+ f.doorName_target +
                    '" data-sceneName_target="'+ f.sceneName_target +
                    '" data-sceneID_target="'+ f.sceneID_target +
                    '" data-sshot-url="'+ f.screenImagePath +
                    '" data-isreward="'+ f.isreward +
                    '" data-isCloned="'+ f.isCloned +
                    '" data-isJoker="'+ f.isJoker +
                    '" >' + img +
                    '<span class="FileListItemName mdc-list-item__text" title="Drag the card into the plane">'+ name +
                    '<span class="mdc-list-item__text__secondary mdc-typography--caption">'+ f.categoryName +'</span></span></a>' +
                    '<span class="FileListItemFooter">' +

                    (f.isJoker==='false'?
                            ('<a draggable="false" ondragstart="return false;" title="Edit asset" id="editAssetBtn-'+ f.assetid +
                                '" onclick="window.location.href=\''+urlforAssetEdit + f.assetid + '&editable=true'  + '\'" class="mdc-button mdc-button--dense">Edit</a>')
                            :
                            ('<a draggable="false" ondragstart="return false;" title="Edit asset" id="editAssetBtn-'+ f.assetid +
                                '" onclick="window.location.href=\''+urlforAssetEdit + f.assetid + '&editable=false' + '\'" class="mdc-button mdc-button--dense">View</a>')
                    )+

                    (f.isJoker==='false'?
                            ('<a draggable="false" ondragstart="return false;" title="Delete asset" href="#" id="deleteAssetBtn-'+ f.assetid
                                + '" onclick="wpunity_deleteAssetAjax('+
                                f.assetid + ', \'' + gameProjectSlug + '\',' + f.isCloned + ')" class="mdc-button mdc-button--dense">Delete</a>') :
                            ''
                    )
                    +

                    '</span>' +
                    '<div id="deleteAssetProgressBar-'+ f.assetid + '" class="progressSlider" style="position: absolute;bottom: 0;display: none;">\n' +
                    '<div class="progressSliderLine"></div>\n' +
                    '<div class="progressSliderSubLine progressIncrease"></div>\n' +
                    '<div class="progressSliderSubLine progressDecrease"></div>\n' +
                    '</div>' +
                    '</li>' );

                file.appendTo(fileList);
            }

            var addNewBtnLink = jQuery('#addNewAssetBtn').attr('href');

            var newAssetBtn = jQuery(
                '<br><a ' +
                'draggable="false" ' +
                'onclick="window.location.href='+ "'" + addNewBtnLink + "'" +'" ' +
                'class="mdc-button mdc-button--raised mdc-button--primary mdc-theme--secondary-bg" ' +
                'data-mdc-auto-init="MDCRipple" ' +
                '>' +
                'Add new' +
                '</a><br>');

            newAssetBtn.appendTo(fileList);

            // Don't delete. Needed to auto init the mdc componented after they have loaded.
            mdc.autoInit(document, () => {});
        }

        // Remove animation
        if(filemanager.hasClass('searching'))
            fileList.removeClass('animated');

        // Show the generated elements
        fileList.animate({'display':'inline-block'});

        // Perform click to open (bug appeared from migrating jquery-1.11 to 3.1.1
        closeButton.click();
    }

    // This function escapes special html characters in names
    function escapeHTML(text) {
        return text.replace(/\&/g,'&amp;').replace(/\</g,'&lt;').replace(/\>/g,'&gt;');
    }

    // Convert file sizes from bytes to human readable units
    function bytesToSize(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes == 0) return '0 Bytes';
        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    }

    function selectByTitleComparizon(input_data, needle){
        var output_data = [];

        input_data.forEach(function(d){
            if (d.assetName.indexOf(needle) !== -1)
                output_data.push(d);
        });
        return output_data;
    }

}