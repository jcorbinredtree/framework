function UI() {
    /*
     * dms events
     * --------------------------------------------------
     */
    this.createEvent('initialize');
    this.createEvent('folderClicked');
    
    this.createEvent('fileSelected');
}

/*
 * add event provider functionality to UI
 */
YAHOO.augment(UI, YAHOO.util.EventProvider);

UI.prototype.currentFile = null;
UI.prototype.currentFolder = null;
        
UI.prototype.tree = null;        
UI.prototype.inner = null;
UI.prototype.layout = null;        
UI.prototype.dataSource = null;
UI.prototype.dataTable = null; 

UI.prototype.fileDialog = null;
UI.prototype.folderDialog = null;
UI.prototype.waitDialog = null; 

UI.prototype.getOperationsPanel = function() {
    return this.inner.getUnitByPosition('bottom');    
};

UI.prototype.getMainPanel = function() {
    return this.inner.getUnitByPosition('center');
};