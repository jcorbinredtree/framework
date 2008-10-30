YAHOO.util.Event.on(document, 'mousemove', function(e){
    parent.CMSDragManager.onMouseMove(e);
    return;
});

YAHOO.util.Event.on(document, 'mouseup', function(e){
    parent.CMSDragManager.onMouseUp(e);
    return;
});

YAHOO.util.Event.on(document, 'mousedown', function(e){
    parent.CMSDragManager.onMouseDown(e);
    return;
});