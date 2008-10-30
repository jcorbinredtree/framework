/* manager */

function CMSDragManager() {
	
}

CMSDragManager.activeDrag = null;

CMSDragManager.drags = [];

CMSDragManager._getTargetAtPoint = function(drag, pt) {
	var targets = drag.getTargets();
	for (var i = 0; i < targets.length; i++) {
		var target = targets[i];
		var r = target.getRegion();
		
		if (r.contains(pt)) {
			return target;
		}
	}	
	
	return null;
};

CMSDragManager.register = function(drag) {
	CMSDragManager.drags[CMSDragManager.drags.length] = drag;
};

CMSDragManager.unregister = function(drag) {
	var newarray = [];
	
	for (var i = 0; i < CMSDragManager.drags.length; i++) {
		var o = CMSDragManager.drags[i];
		
		if (o != drag) {
			newarray[newarray.length] = o;
		}
	}
	
	CMSDragManager.drags = newarray;
};

CMSDragManager.onMouseMove = function(e) {
	if (!CMSDragManager.activeDrag) {
		return;
	}
	
	var x = YAHOO.util.Event.getPageX(e);
	var y = YAHOO.util.Event.getPageY(e);
	var pt = new YAHOO.util.Point(x, y);
	var region = CMSDragManager.activeDrag.getFrameRegion();	
		
	if (region.contains(pt)) {
		var target = CMSDragManager._getTargetAtPoint(CMSDragManager.activeDrag, pt);
		if (target) {
			CMSDragManager.activeDrag.onDragOver(e, target);
		}
	}
	
	CMSDragManager.activeDrag.onDrag(e, x, y);
};

CMSDragManager.onMouseDown = function(e) {
	var x = YAHOO.util.Event.getPageX(e);
	var y = YAHOO.util.Event.getPageY(e);
	var pt = new YAHOO.util.Point(x, y);
	
	for (var i = 0; i < CMSDragManager.drags.length; i++) {
		var drag = CMSDragManager.drags[i];		
		var region = drag.getRegion();
		
		if (region.contains(pt)) {
			CMSDragManager.activeDrag = drag;
			CMSDragManager.activeDrag.onStartDrag(e);
			return;
		}
	}
};

CMSDragManager.onMouseUp = function(e) {
	if (!CMSDragManager.activeDrag) {
		return;
	}
	
	var x = YAHOO.util.Event.getPageX(e);
	var y = YAHOO.util.Event.getPageY(e);
	var pt = new YAHOO.util.Point(x, y);
	var target = CMSDragManager._getTargetAtPoint(CMSDragManager.activeDrag, pt);
	if (target) {
		CMSDragManager.activeDrag.onDragDrop(e, target);
	}
	else {
		CMSDragManager.activeDrag.onAbortDrag(e);
	}
	
	CMSDragManager.activeDrag = null;
};

YAHOO.util.Event.on(document, 'mousemove', CMSDragManager.onMouseMove);
YAHOO.util.Event.on(document, 'mousedown', CMSDragManager.onMouseDown);
YAHOO.util.Event.on(document, 'mouseup', CMSDragManager.onMouseUp);

/* drag */

function CMSDrag(el, targetFrame) {
	this._el = el;
	this.targetFrame = targetFrame;
	
	CMSDragManager.register(this);
}

CMSDrag.prototype._el = null;
CMSDrag.prototype._proxy = null;

CMSDrag.prototype._region = null;
CMSDrag.prototype._frameRegion = null;

CMSDrag.prototype.targetFrame = null;
CMSDrag.prototype._targets = [];

CMSDrag.prototype.getEl = function() {
	return this._el;	
};

CMSDrag.prototype.getTargets = function() {
	return this._targets;	
};

CMSDrag.prototype.addTarget = function(t) {
	this._targets[this._targets.length] = t;	
};

CMSDrag.prototype.getProxyEl = function() {
	return this._proxy;	
};

CMSDrag.prototype.getFrameRegion = function() {
	if (this._frameRegion) {
		return this._frameRegion;
	}
	
	return this._frameRegion = YAHOO.util.Region.getRegion(this.targetFrame);
};

CMSDrag.prototype.getRegion = function() {
	if (this._region) {
		return this._region;
	}
	
	return this._region = YAHOO.util.Region.getRegion(this._el);
};

CMSDrag.prototype.onStartDrag = function(e) {
	this._proxy = document.createElement('div');
	this._proxy.innerHTML = this._el.innerHTML;
	
	YAHOO.util.Dom.setStyle(this._proxy, 'opacity', '.75');
	YAHOO.util.Dom.setStyle(this._proxy, 'position', 'absolute');
	YAHOO.util.Dom.setStyle(this._proxy, 'border', '2px dashed blue');
	
	document.body.appendChild(this._proxy);
	
	YAHOO.util.Dom.setStyle(this._proxy, 'cursor', 'move');	
};

CMSDrag.prototype.onDrag = function(e, x, y) {	
	YAHOO.util.Dom.setStyle(this._proxy, 'top', (y - 10) + 'px');
	YAHOO.util.Dom.setStyle(this._proxy, 'left', (x - 10) + 'px');
};

CMSDrag.prototype.onAbortDrag = function(e) {
	document.body.removeChild(this._proxy);	
};

CMSDrag.prototype.onDragOver = function(e, target) {
};

CMSDrag.prototype.onDragDrop = function(e, target) {
	YAHOO.util.Dom.setStyle(target.getEl(), 'border', '1px dashed red');
};

/* target */

function CMSDragTarget(el) {
	this._el = el;
}

CMSDragTarget.prototype._el = null;
CMSDragTarget.prototype._region = null;

CMSDragTarget.prototype.getEl = function() {
	return this._el;
};

CMSDragTarget.prototype.getRegion = function() {
	if (this._region) {
		return this._region;
	}
	
	return this._region = YAHOO.util.Region.getRegion(this._el);	
};