function DMS() {
    ui.subscribe('initialize', this.onInitialize, this, true);
    ui.subscribe('folderClicked', this.onFolderClicked, this, true);
}

DMS.prototype.folderAdded = false;

DMS.prototype.onDownloadFile = function() {
    window.location = config.downloadURI + "&id=" + ui.currentFile.id;
};

DMS.prototype.onAddFile = function() {
    $('file_folder_id').value = ui.currentFolder.id;
    
    ui.fileDialog.show();
};

DMS.prototype.onDeleteFile = function() {
    if (confirm("Really delete the file " + ui.currentFile.name + "? This operation can not be undone.")) {
        YAHOO.util.Connect.asyncRequest('POST', config.deleteFileURI, null, 'file_id=' + ui.currentFile.id);

        ui.fireEvent('folderClicked', ui.currentFolder);
    }
};

DMS.prototype.onAddFolder = function() {
    $('parent_id').value = ui.currentFolder ? ui.currentFolder.id : 0;
    
    this.folderAdded = true;
    
    ui.folderDialog.form.action = config.addFolderURI;
    ui.folderDialog.setHeader('Add Folder');
    ui.folderDialog.show();
};

DMS.prototype.onEditFolder = function() {
    $('folder_id').value = ui.currentFolder.id;
    $('folder_name').value = ui.currentFolder.label;
    
    this.folderAdded = false;
    
    ui.folderDialog.form.action = config.editFolderURI;    
    ui.folderDialog.setHeader('Edit Folder ' + ui.currentFolder.label);    
    ui.folderDialog.show();
};    

DMS.prototype.onDeleteFolder = function() {    
    if (confirm("Really delete the folder " + ui.currentFolder.label + "? All files in this directory will also be removed.")) {
        YAHOO.util.Connect.asyncRequest('POST', config.deleteFolderURI, null, 'folder_id=' + ui.currentFolder.id);
        
        var p = ui.currentFolder.parent;
        ui.tree.popNode(ui.currentFolder);
        p.refresh();
        p.expand();
        
        ui.fireEvent("folderClicked", p);
    }    
};
 
DMS.prototype.onGetFolders = function(folder, onComplete) {
    var callback = {
        success  : function(response) {
            var results = eval(response.responseText);
            for (var i = 0; i < results.length; i++) {
                var node = results[i];
                
                new DMSFolder({
                    label : node.name,
                    parent : response.argument.parent,
                    id : node.id
                });        
            }
        
            response.argument.onLoadComplete();        
        },
        failure  : function(response) {
            alert("I was unable to retrieve data for the " + response.argument.parent.label + " item. Please try again shortly.");

            response.argument.onLoadComplete();            
        },
        'argument' : {
            "parent" : folder,
            "onLoadComplete" : onComplete
        },
        'timeout' : 5000
    };

    YAHOO.util.Connect.asyncRequest('POST', config.getFoldersURI, callback, 'folder_id=' + folder.id);
};

DMS.prototype.onFolderClicked = function(folder) {
    ui.currentFolder = folder;

    {        
        var callback = {
            success  : function(response) {
                var headers = new Array();
                var tlp = folder;
                while(tlp.parent) {
                    headers[headers.length] = tlp.label;
                    
                    tlp = tlp.parent;
                }
                
                ui.getMainPanel().set('header', headers.reverse().join(' &gt; '));

                if (ui.dataTable) {
                    ui.dataTable.destroy();
                }
                
                dms.onInitializeDataTable();

                var results = eval(response.responseText);   
                for (var i = 0; i < results.length; i++) {
                    results[i].at = new Date(results[i].at * 1000);
                }
                             
                ui.dataTable.addRows(results);
                
                $('dms-files-table').style.display = 'block';                
            },
            failure  : function(arg) {
                alert("Failed to load data for " + ui.currentFolder.label);
            },
            'timeout' : 5000
        };
        
        YAHOO.util.Connect.asyncRequest('POST', config.getFilesURI, callback, 'folder_id=' + folder.id);        
    }
    
    {
        var callback = {
            success  : function(response) {
                var unit = ui.getOperationsPanel();
                
                unit.set('header', 'Operations for ' + folder.label);            
                unit.set('body', response.responseText);
                
                YAHOO.util.Event.onContentReady('image-previewer', function() {
                    $('image-previewer').src = config.downloadURI + '&id=' + ui.currentFile.id;
                });
            },
            failure  : function(arg) {
                alert("Failed to load data for " + ui.currentFolder.label);
            },
            'timeout' : 5000
        };

        YAHOO.util.Connect.asyncRequest('POST', config.getOperationsContentURI, callback, 'folder_id=' + folder.id);
    }            
};

DMS.prototype.onInitializeDataTable = function() {
    var columns = [
        { key : 'name', label : 'Name', sortable:true, resizeable:true },
        { key : 'size', label : 'Size', sortable:true, resizeable:true },
        { key : 'mimeType', label : 'Type', sortable:true, resizeable:true },
        { key : 'at', label : 'Added', sortable:true, resizeable:true, formatter:YAHOO.widget.DataTable.formatDate }            
    ];
    
    ui.dataSource = new YAHOO.util.DataSource(null);
    ui.dataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
    ui.dataSource.responseSchema = { 
        fields: ["name","size","mimeType", "at"] 
    };
    
    ui.dataTable = new YAHOO.widget.DataTable("dms-files-table", columns, ui.dataSource, { 
        width: '100%', 
        height: '100%',
        initialLoad: false 
    });
            
    ui.dataTable.subscribe("rowMouseoverEvent", ui.dataTable.onEventHighlightRow);
    ui.dataTable.subscribe("rowMouseoutEvent", ui.dataTable.onEventUnhighlightRow);
    ui.dataTable.subscribe("rowClickEvent", ui.dataTable.onEventSelectRow);
    ui.dataTable.subscribe("rowSelectEvent", function() {
        ui.currentFile = this.getRecordSet().getRecord(this.getSelectedRows()[0])._oData;
        
        var callback = {
            success  : function(response) {
                var unit = ui.inner.getUnitByPosition('bottom');
                
                unit.set('header', 'Operations for ' + ui.currentFile.name);            
                unit.set('body', response.responseText);
                                
                YAHOO.util.Event.onContentReady('image-previewer', function() {
                    $('image-previewer').src = config.downloadURI + '&id=' + ui.currentFile.id;
                });                
            },
            failure  : function() {
                alert("Failed to load data for " + ui.currentFile.label);
            },
            'timeout' : 5000
        };
    
        YAHOO.util.Connect.asyncRequest('POST', config.getOperationsContentURI, callback, 'folder_id=' + ui.currentFolder.id + "&file_id=" + ui.currentFile.id);
    }, ui.dataTable, true);
    
    ui.dataTable.subscribe('rowDblclickEvent', function(){ui.fireEvent('fileSelected', ui.currentFile);});
};

DMS.prototype.onInitialize = function() {    
    ui.tree = new YAHOO.widget.TreeView("dms-folder-control", {});
    ui.tree.setDynamicLoad(this.onGetFolders);
    
    onLoadChildren(ui.tree);       
                
    ui.tree.subscribe("labelClick", function(f){ui.fireEvent("folderClicked", f);});
    
    /*
     * this is a really really shitty hack. without this f node bullshit if there are no
     * nodes in the tree already, adding a new top level node will not appear. the "solution" is:
     * 
     * 1.) add a blank label node
     * 2.) draw the tree
     * 3.) remove the node
     * 4.) refresh the tree
     * 5.) battle depression
     */
    var f = new DMSFolder({
        label : '   ',
        id : -1,
        parent : ui.tree.getRoot()
    });
    
    ui.tree.draw();
    
    ui.tree.popNode(f);
    ui.tree.getRoot().refresh();

    {
        ui.waitDialog = new YAHOO.widget.Panel("waitDialog",  
            { 
              width:"240px", 
              fixedcenter:true, 
              close:false, 
              draggable:false, 
              zindex:4,
              modal:true,
              visible:false
            } 
        );

        ui.waitDialog.setHeader("Uploading file...");
        ui.waitDialog.setBody('<img src="view/images/loading.gif" alt = "loading.." />');
        ui.waitDialog.render();
        ui.waitDialog.hide();
    }
    
    ui.folderDialog = new YAHOO.widget.Dialog("folderDialog", {fixedcenter:true,visible:false});
    {
        var buttons = [ 
                        { 
                            text:"Submit",
                            handler : function() {
                                this.submit();  
                            },
                            isDefault:true 
                        },
                        { 
                            text:"Cancel", 
                            handler: function() {
                                this.cancel();
                            }
                        } 
                     ];
                     
        ui.folderDialog.cfg.queueProperty("buttons", buttons);                     
                      
        ui.folderDialog.validate = function() {
            var data = this.getData();
            
            if (data.folder_name == "") {
                alert("Please enter a name for this folder.");
                return false;
            } 
        
            return true;    
        };          
        
        ui.folderDialog.callback = {
            success : function(response) {                
                var data = ui.folderDialog.getData();    
                
                if (dms.folderAdded) {
                    var p = ui.currentFolder ? ui.currentFolder : ui.tree.getRoot();
                    
                    new DMSFolder({
                       label : data.folder_name,
                       id : response.responseText,
                       parent : p
                    });
                    
                    p.refresh();
                    p.expand();
                }
                else {
                    ui.currentFolder.setUpLabel(data.folder_name);
                    ui.currentFolder.parent.refresh();
                    ui.currentFolder.parent.expand();
                }
            }
        };
                      
        ui.folderDialog.render();
        ui.folderDialog.hide();        
    }         
    
    ui.fileDialog = new YAHOO.widget.Dialog("fileDialog", 
        {
            fixedcenter: true,
            constraintoviewport: true,
            visible: false,            
            width: '600px'
        });
    {
        var buttons = [ 
                        { 
                            text:"Submit", 
                            handler: function() {
                                ui.waitDialog.show();    
                                
                                this.submit();
                            }, 
                            isDefault:true 
                        },
                        { 
                            text:"Cancel", 
                            handler: function() {
                                this.cancel();
                            }
                        } 
                      ];     
                      
        ui.fileDialog.callback.upload = function(o) {
            ui.waitDialog.hide();                
            
            var r = o.responseText.replace(/<pre(?:[^>]*?)?>(.+)<\/pre>/i, '$1');
            if (r != 'OK') {
                alert("An error has occurred while attempting to upload this document.\n" + r);
                return;
            }
            
            ui.fireEvent('folderClicked', ui.currentFolder);
        };
                      
        ui.fileDialog.cfg.queueProperty("buttons", buttons);
        ui.fileDialog.render();
        ui.fileDialog.hide();        
    }

    ui.layout = new YAHOO.widget.Layout('dms-panel', {
        height: 635,
        units: [
            { position: 'left', gutter: '0px', width: '200px', resize: true, body: 'dms-folders', minWidth: 150 },
            { position: 'center', gutter: '0px' }
        ]
    });
    
    ui.layout.on('render', function() {
        ui.inner = new YAHOO.widget.Layout(ui.layout.getUnitByPosition('center').get('wrap'), {
            parent: ui.layout,
            units: [
                { position: 'center', gutter: '0px', width: '95%', body: 'dms-main' },
                { position: 'bottom', gutter: '0px', height: '250px', resize: true, body: 'dms-operations', minHeight: 150 }                
            ]        
        });
        
        ui.inner.render();
    });

    ui.layout.render();    
    
    this.onInitializeDataTable();        
}

var config = new Config();
var ui = new UI();
var dms = new DMS();

/*
 * utils
 */
function $(id) { 
    return document.getElementById(id); 
}

(function(){var B=YAHOO.widget.DataTable,A=YAHOO.util.Dom;B.prototype._setColumnWidth=function(I,D,J){I=this.getColumn(I);if(I){J=J||"hidden";if(!B._bStylesheetFallback){var N;if(!B._elStylesheet){N=document.createElement("style");N.type="text/css";B._elStylesheet=document.getElementsByTagName("head").item(0).appendChild(N)}if(B._elStylesheet){N=B._elStylesheet;var M=".yui-dt-col-"+I.getId();var K=B._oStylesheetRules[M];if(!K){if(N.styleSheet&&N.styleSheet.addRule){N.styleSheet.addRule(M,"overflow:"+J);N.styleSheet.addRule(M,"width:"+D);K=N.styleSheet.rules[N.styleSheet.rules.length-1]}else{if(N.sheet&&N.sheet.insertRule){N.sheet.insertRule(M+" {overflow:"+J+";width:"+D+";}",N.sheet.cssRules.length);K=N.sheet.cssRules[N.sheet.cssRules.length-1]}else{B._bStylesheetFallback=true}}B._oStylesheetRules[M]=K}else{K.style.overflow=J;K.style.width=D}return }B._bStylesheetFallback=true}if(B._bStylesheetFallback){if(D=="auto"){D=""}var C=this._elTbody?this._elTbody.rows.length:0;if(!this._aFallbackColResizer[C]){var H,G,F;var L=["var colIdx=oColumn.getKeyIndex();","oColumn.getThEl().firstChild.style.width="];for(H=C-1,G=2;H>=0;--H){L[G++]="this._elTbody.rows[";L[G++]=H;L[G++]="].cells[colIdx].firstChild.style.width=";L[G++]="this._elTbody.rows[";L[G++]=H;L[G++]="].cells[colIdx].style.width="}L[G]="sWidth;";L[G+1]="oColumn.getThEl().firstChild.style.overflow=";for(H=C-1,F=G+2;H>=0;--H){L[F++]="this._elTbody.rows[";L[F++]=H;L[F++]="].cells[colIdx].firstChild.style.overflow=";L[F++]="this._elTbody.rows[";L[F++]=H;L[F++]="].cells[colIdx].style.overflow="}L[F]="sOverflow;";this._aFallbackColResizer[C]=new Function("oColumn","sWidth","sOverflow",L.join(""))}var E=this._aFallbackColResizer[C];if(E){E.call(this,I,D,J);return }}}else{}};B.prototype._syncColWidths=function(){var J=this.get("scrollable");if(this._elTbody.rows.length>0){var M=this._oColumnSet.keys,C=this.getFirstTrEl();if(M&&C&&(C.cells.length===M.length)){var O=false;if(J&&(YAHOO.env.ua.gecko||YAHOO.env.ua.opera)){O=true;if(this.get("width")){this._elTheadContainer.style.width="";this._elTbodyContainer.style.width=""}else{this._elContainer.style.width=""}}var I,L,F=C.cells.length;for(I=0;I<F;I++){L=M[I];if(!L.width){this._setColumnWidth(L,"auto","visible")}}for(I=0;I<F;I++){L=M[I];var H=0;var E="hidden";if(!L.width){var G=L.getThEl();var K=C.cells[I];if(J){var N=(G.offsetWidth>K.offsetWidth)?G.firstChild:K.firstChild;if(G.offsetWidth!==K.offsetWidth||N.offsetWidth<L.minWidth){H=Math.max(0,L.minWidth,N.offsetWidth-(parseInt(A.getStyle(N,"paddingLeft"),10)|0)-(parseInt(A.getStyle(N,"paddingRight"),10)|0))}}else{if(K.offsetWidth<L.minWidth){E=K.offsetWidth?"visible":"hidden";H=Math.max(0,L.minWidth,K.offsetWidth-(parseInt(A.getStyle(K,"paddingLeft"),10)|0)-(parseInt(A.getStyle(K,"paddingRight"),10)|0))}}}else{H=L.width}if(L.hidden){L._nLastWidth=H;this._setColumnWidth(L,"1px","hidden")}else{if(H){this._setColumnWidth(L,H+"px",E)}}}if(O){var D=this.get("width");this._elTheadContainer.style.width=D;this._elTbodyContainer.style.width=D}}}this._syncScrollPadding()}})();
(function(){var A=YAHOO.util,B=YAHOO.env.ua,E=A.Event,C=A.Dom,D=YAHOO.widget.DataTable;D.prototype._initTheadEls=function(){var X,V,T,Z,I,M;if(!this._elThead){Z=this._elThead=document.createElement("thead");I=this._elA11yThead=document.createElement("thead");M=[Z,I];E.addListener(Z,"focus",this._onTheadFocus,this);E.addListener(Z,"keydown",this._onTheadKeydown,this);E.addListener(Z,"mouseover",this._onTableMouseover,this);E.addListener(Z,"mouseout",this._onTableMouseout,this);E.addListener(Z,"mousedown",this._onTableMousedown,this);E.addListener(Z,"mouseup",this._onTableMouseup,this);E.addListener(Z,"click",this._onTheadClick,this);E.addListener(Z.parentNode,"dblclick",this._onTableDblclick,this);this._elTheadContainer.firstChild.appendChild(I);this._elTbodyContainer.firstChild.appendChild(Z)}else{Z=this._elThead;I=this._elA11yThead;M=[Z,I];for(X=0;X<M.length;X++){for(V=M[X].rows.length-1;V>-1;V--){E.purgeElement(M[X].rows[V],true);M[X].removeChild(M[X].rows[V])}}}var N,d=this._oColumnSet;var H=d.tree;var L,P;for(T=0;T<M.length;T++){for(X=0;X<H.length;X++){var U=M[T].appendChild(document.createElement("tr"));P=(T===1)?this._sId+"-hdrow"+X+"-a11y":this._sId+"-hdrow"+X;U.id=P;for(V=0;V<H[X].length;V++){N=H[X][V];L=U.appendChild(document.createElement("th"));if(T===0){N._elTh=L}P=(T===1)?this._sId+"-th"+N.getId()+"-a11y":this._sId+"-th"+N.getId();L.id=P;L.yuiCellIndex=V;this._initThEl(L,N,X,V,(T===1))}if(T===0){if(X===0){C.addClass(U,D.CLASS_FIRST)}if(X===(H.length-1)){C.addClass(U,D.CLASS_LAST)}}}if(T===0){var R=d.headers[0];var J=d.headers[d.headers.length-1];for(X=0;X<R.length;X++){C.addClass(C.get(this._sId+"-th"+R[X]),D.CLASS_FIRST)}for(X=0;X<J.length;X++){C.addClass(C.get(this._sId+"-th"+J[X]),D.CLASS_LAST)}var Q=(A.DD)?true:false;var c=false;if(this._oConfigs.draggableColumns){for(X=0;X<this._oColumnSet.tree[0].length;X++){N=this._oColumnSet.tree[0][X];if(Q){L=N.getThEl();C.addClass(L,D.CLASS_DRAGGABLE);var O=D._initColumnDragTargetEl();N._dd=new YAHOO.widget.ColumnDD(this,N,L,O)}else{c=true}}}for(X=0;X<this._oColumnSet.keys.length;X++){N=this._oColumnSet.keys[X];if(N.resizeable){if(Q){L=N.getThEl();C.addClass(L,D.CLASS_RESIZEABLE);var G=L.firstChild;var F=G.appendChild(document.createElement("div"));F.id=this._sId+"-colresizer"+N.getId();N._elResizer=F;C.addClass(F,D.CLASS_RESIZER);var e=D._initColumnResizerProxyEl();N._ddResizer=new YAHOO.util.ColumnResizer(this,N,L,F.id,e);var W=function(f){E.stopPropagation(f)};E.addListener(F,"click",W)}else{c=true}}}if(c){}}else{}}for(var a=0,Y=this._oColumnSet.keys.length;a<Y;a++){if(this._oColumnSet.keys[a].hidden){var b=this._oColumnSet.keys[a];var S=b.getThEl();b._nLastWidth=S.offsetWidth-(parseInt(C.getStyle(S,"paddingLeft"),10)|0)-(parseInt(C.getStyle(S,"paddingRight"),10)|0);this._setColumnWidth(b.getKeyIndex(),"1px")}}if(B.webkit&&B.webkit<420){var K=this;setTimeout(function(){K._elThead.style.display=""},0);this._elThead.style.display="none"}}})();              

YAHOO.util.Event.onDOMReady(function(){ui.fireEvent("initialize");});