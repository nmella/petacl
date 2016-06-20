var awPq2Sorter = Class.create();
awPq2Sorter.prototype = {
    initialize: function(config) {
        this.config = config;
        this.sortBlock = $$(config.sortBlockSelector).first();
        this.sortby = this.sortBlock.select(config.sortbySelector).first();
        this.dir = this.sortBlock.select(config.dirSelector).first();
        this.dirImage = this.dir.select('img').first();
        this.updateContainer = $$(config.updateContainerSelector).first();
        this.sortUrl = config.sortUrl;
        this.sortByValue = config.sortByValue;
        this.sortDirValue = config.sortDirValue;
        this.sortDirValueTarget = config.sortDirValueTarget;
        this.product = config.product;
        this.images = config.dirImagesUrl;
        this.overlayCssClass = config.overlayCssClass;
        this.overlayImage = config.overlayImage;

        this.disabled = false;
        this.initObservers();
    },
    initObservers: function() {
        var me = this;

        Event.observe(this.sortby, 'change', function(e) {
            if (!me.disabled) {
                me.sortByValue = me.sortby.value;
                me._dispatch();
            }
        });
        Event.observe(this.dir, 'click', function(e) {
            if (!me.disabled) {
                me.sortDirValue = me.sortDirValueTarget;
                me.dirImage.src = me.images[me.sortDirValue];
                me._dispatch();
            }
        });
    },
    _dispatch: function() {
        var me = this;
        new Ajax.Request(
            me.sortUrl,
            {
                method: 'get',
                parameters: {
                    'orderby': me.sortByValue,
                    'dir': me.sortDirValue,
                    'product': me.product
                },
                onCreate : me._onStartRequest.bind(me),
                onComplete: me._onCompleteRequest.bind(me)
            }
        );
    },
    _onStartRequest: function(response) {
        this._disable();
        this._showOverlay();
    },
    _onCompleteRequest: function(response) {
        try {
            eval("var json = " + response.responseText + " || {}");
        } catch(e) {
            return;
        }
        this.updateContainer.innerHTML = json.block;
        this._evalScripts(this.updateContainer.innerHTML);
        this._hideOverlay();
        this._enable();
    },
    _evalScripts: function(html) {
        var scripts = html.extractScripts();
        scripts.each(function(script){
            try {
                //FIX CDATA comment
                script = script.replace('//<![CDATA[', '').replace('//]]>', '');
                script = script.replace('/*<![CDATA[*/', '').replace('/*]]>*/', '');
                eval(script.replace(/var /gi, ""));
            } catch(e){
                if(window.console) {
                    console.log(e.message);
                }
            }
        });
    },
    _showOverlay: function() {
        this.overlayContainer = this.updateContainer.select(this.config.overlayContainerSelector).first();
        this.overlay = this._createOverlay();
        this.overlayContainer.insert({
            top: this.overlay
        });
    },
    _hideOverlay: function() {
        if (this.overlay) {
            this.overlay.remove();
        }
    },
    _disable: function() {
        this.disabled = true;
        this.sortby.disabled = true;
    },
    _enable: function() {
        this.disabled = false;
        this.sortby.disabled = false;
    },
    _createOverlay: function() {
        var overlay = new Element('div');
        $(overlay).addClassName(this.overlayCssClass);
        overlay.setStyle({
            backgroundColor: 'rgb(251, 250, 246)',
            opacity: '0.5',
            width: this.overlayContainer.getWidth() + "px",
            height: this.overlayContainer.getHeight() + "px",
            backgroundImage: "url(" + this.overlayImage + ")",
            backgroundSize: "48px 48px"
        });
        return overlay;
    }
};