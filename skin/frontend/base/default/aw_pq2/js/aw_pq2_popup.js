var awPq2Popup = Class.create();
awPq2Popup.prototype = {
    initialize: function(config) {
        this.overlay = $$(config.overlaySelector).first();
        this.popup = $$(config.popupSelector).first();
        this.closeButton = $$(config.closeButtonSelector).first();
        this.resizePopup();
        this.initObservers();
    },

    initObservers: function() {
        var me = this;
        Event.observe(this.overlay, 'click', me.hidePopup.bind(me));
        Event.observe(this.closeButton, 'click', me.hidePopup.bind(me));

        Event.observe(window, 'resize', me.resizePopup.bind(me));
        Event.observe(window, 'scroll', me.resizePopup.bind(me));
    },

    hidePopup: function() {
        this.popup.hide();
        this.overlay.hide();
    },

    resizePopup: function() {
        var xy = this._collectPos(this.popup);
        if (xy[0] < 50) {
            xy[0] = 50;
        }
        if (xy[1] < 50) {
            xy[1] = 50;
        }

        var left = xy[0];
        var top = xy[1];

        var isIOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false );
        if (isIOS) {
            this.popup.setStyle({'position': 'absolute'});
            left += window.pageXOffset?window.pageXOffset:0;
            top += window.pageYOffset?window.pageYOffset:0;
        }

        this.popup.setStyle({
            'left': left + 'px',
            'top': top + 'px'
        });
    },

    _collectPos: function(el) {
        var x, y;

        var elWidth = el.getWidth();
        var docWidth = window.innerWidth;
        x = docWidth/2 - elWidth/2;

        var elHeight = el.getHeight();
        var docHeight = window.innerHeight;
        y = docHeight/2 - elHeight/2;

        return [x, y];
    }
};