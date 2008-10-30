(function() {
    // Load plugin specific language pack
    tinymce.PluginManager.requireLangPack('redtree_browser');

    tinymce.create('tinymce.plugins.RedtreeBrowser', {
        getInfo: function() {
            return {
                longname: 'RedTree Systems Browser',
                author: 'Joshua T Corbin',
                authorurl: 'http://www.redtreesystems.com',
                infourl: 'http://www.redtreesystems.com',
                version: '1.0'
            };
        },

        init: function(ed, plugin_url) {
            ed.settings.file_browser_callback = function (field_name, url, type, win) {
                tinymce.plugins.RedtreeBrowser.openBrowser(plugin_url, field_name, url, type, win);
            };
            
            ed.onBeforeSetContent.add(function(e, o) {
                o.content = o.content.replace(/[{]dms[:](\d+)[}]/g, plugin_url + '/controller.php?task=download&id=$1&__dms__=$1');
            });
        }
    });

    tinymce.extend( tinymce.plugins.RedtreeBrowser, {
        openBrowser: function(plugin_url, field_name, url, type, win) {
            tinyMCE.activeEditor.windowManager.open({
                file: plugin_url + '/browser_dialog.php?type=' + type,
                width: 700,
                height: 650,
                resizable: 'yes',
                inline: 'yes',
                close_previous: 'no'
            }, {
                plugin_url: plugin_url,
                field_name: field_name,
                initial_url: url,
                browse_type: type,
                calling_window: win
            });
        }
    });

    tinymce.PluginManager.add('redtree_browser', tinymce.plugins.RedtreeBrowser);

})();
