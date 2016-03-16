document.observe("dom:loaded", function(){
    var drop = $("ftp_import_drag_n_drop");
    var input = new Element('input', {type: 'file', multiple: true, style:'display:none', name:'file'});

    drop.show();


    drop.observe('drop', function(e){
        e.stopPropagation();
        e.preventDefault();

        updateDrag(e);

        dropMultipleFilesFtp(e.dataTransfer.files);
    });

    drop.observe('dragover', updateDrag);
    drop.observe('dragenter', updateDrag);
    drop.observe('dragleave', updateDrag);


    drop.observe('click', function(){
        input.click();
    });

    input.observe('change', function(){
        dropMultipleFilesFtp(this.files);
    });

    drop.insert({after: input});
});

function dropMultipleFilesFtp(files)
{
    for (var i = 0; i < files.length; i++)
    {
        submitFileFtp($('preloader'), files[i]);
    }
}


function submitFileFtp(box, file)
{
    showPreloader(box);

    var fd = new FormData;

    var fileInput = $('files_box').down('input[type=file]');

    fd.append(fileInput.name, file ? file : fileInput.files[0]);

    fd.append('form_key', FORM_KEY);
    fd.append('delete_existing_data', $('delete_existing_data').value);
    fd.append('finder_id', $('amfinder_id').value);


    var xhr = new XMLHttpRequest();

    xhr.addEventListener('load', function(e){
        removePreloader(box);
        try {
            var response = e.target.response.evalJSON();
        } catch(e) {
            box.select("#preloader_message")[0].replace("<li id=\"preloader_message\" class=\"error-msg\"><span>Error server response</span></li>");
            return;
        }
        if (response.errors.length > 0)
        {
            box.select("#preloader_message")[0].replace("<li id=\"preloader_message\" class=\"error-msg\"><span>" + response.errors[0] + "</span></li>");
        }
        else
        {
            console.log(box.select("#preloader_messages"));
            box.select("#preloader_message")[0].replace("<li id=\"preloader_message\" class=\"success-msg\"><span>" + response.content + "</span></li>");
            files_list_gridJsObject.reload();
        }
    }, false);

    xhr.addEventListener('error', function(e){
        alert('Invalid file or another error!');
        removePreloader(box);

    }, false);

    xhr.open('POST', $('amfinder_ajax_action').value);
    xhr.send(fd);
}


function showPreloader(box)
{
    var preloader = new Element('div', {class: 'preloader'});
    preloader.appendChild(new Element('img', {src: $('loading-mask').down('img').readAttribute('src')}));

    box.appendChild(preloader);
}

function removePreloader(box)
{
    box.down('.preloader').remove();
}


function updateDrag(e)
{
    e.stopPropagation();
    e.preventDefault();

    if (e.target.tagName == 'DIV')
    {
        if (e.type == 'dragover')
            e.target.addClassName('hover');
        else
            e.target.removeClassName('hover');
    }
}

function amShowErrorsPopup(url)
{
    if ($('browser_window') && typeof(Windows) != 'undefined') {
        Windows.focus('browser_window');
        return;
    }
    var dialogWindow = Dialog.info(null, {
        closable:true,
        resizable:true,
        draggable:true,
        className:'magento',
        windowClassName:'popup-window',
        title:'List Errors',
        top:100,
        width:document.documentElement.clientWidth -100,
        height:400,
        zIndex:1000,
        recenterAuto:false,
        hideEffect:Element.hide,
        showEffect:Element.show,
        id:'browser_window',
        url:url,
        onClose:function (param, el) {
            //alert('onClose');
        }
    });
}

function closePopup() {
    Windows.close('browser_window');
}

var isStoppedImport = false;
function amFinderRunImportFile(url)
{
    $('am_finder_popup').show();
    isStoppedImport = false;


    var onSuccessCallBack = function(response) {
        var data = response.responseText;
        if (!data || !data.isJSON()) {
            alert('System error: ' + data);
            window.location.reload();
        }
        data = data.evalJSON();
        $('am_finder_popup_log').insert('<li>' + data.message + '</li>', {position: 'content'});
        $('am_finder_popup_progress').writeAttribute('style', 'width:' + data.progress + '%');
        $('am_finder_overlay').hide();
        if (!data.isCompleted && !isStoppedImport) {
            setTimeout(function() { amFinderRequestImport(url, onSuccessCallBack); }, 1000);
        }

    };

    amFinderRequestImport(url, onSuccessCallBack);
}

function amFinderRequestImport(url, onSuccessCallBack)
{
    if(isStoppedImport) {
        return;
    }
    $('am_finder_overlay').show();
    new Ajax.Request(url,
        {
            onSuccess: onSuccessCallBack  /**/
        });
}

function amFinderCloseImportPopUp()
{
    $('am_finder_overlay').hide();
    $('am_finder_popup').hide();
    isStoppedImport = true;
    $('am_finder_popup_log').innerHTML = '';
    $('am_finder_popup_progress').writeAttribute('style', 'width:0%');
    files_list_gridJsObject.reload();
    import_history_gridJsObject.reload();
    $('finderTabs_products').addClassName('notloaded');

}