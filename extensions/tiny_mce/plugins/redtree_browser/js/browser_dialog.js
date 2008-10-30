ui.subscribe('fileSelected', function(file) {
    function arg(x) {
        return editor.windowManager.params[x];
    }
    
    var tinyMCE = window.parent.tinymce;
    var editor = tinyMCE.EditorManager.activeEditor;
        
    var win = arg('calling_window');
    var input = win.document.getElementById(arg('field_name'));

    input.value = '{dms:' + file.id + '}';

    if (win.ImageDialog) {
        /*
         * use the preview url for the image dialog
         */
        input.value = config.downloadURI + '&id=' + file.id + '&__dms__=' + file.id;
        
        if (win.ImageDialog.getImageData) {
            win.ImageDialog.getImageData();
        }
        
        if (win.ImageDialog.showPreviewImage) {
            win.ImageDialog.showPreviewImage(input.value);
        }
    }
    
    var p = win.document.getElementById('prev');
    if (p && win.generatePreview) {
        /*
         * use the preview url for the media dialog
         */
        input.value = config.downloadURI + '&id=' + file.id + '&__dms__=' + file.id;
        win.generatePreview('');
    }

    editor.windowManager.close(window, arg('mce_window_id'));
});
