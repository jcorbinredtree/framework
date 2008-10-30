/**
 * This class defines a YUI tree node.
 *
 * @author Red Tree Systems, LLC
 */
function DMSFolder(obj) {
    if (!obj.id || !obj.label) {
        throw "id and label are required!";
    }
            
    DMSFolder.superclass.constructor.call(this, 
        obj.label,
        obj.parent, 
        obj.expand ? true : false);
                    
    this.id = obj.id;
}

/*
 * extend the TextNode
 */
YAHOO.lang.extend(DMSFolder, YAHOO.widget.TextNode);

/**
 * Stores the user-supplied id for this node
 */
DMSFolder.prototype.id = -1;