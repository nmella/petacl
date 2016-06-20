var PQ2AjaxForm = Class.create();
PQ2AjaxForm.prototype = {
    initialize: function () {
        this.dialogWindowId = 'aw-pq2-form-content';
    },
    open: function (url, parameters, title, callback, width) {
        if (this.dialogWindow) {
            this.closeDialogWindow(this.dialogWindow);
        }
        new Ajax.Request(url, {
            parameters: parameters,
            onComplete: function (transport) {
                if (transport.responseText) {
                    this.openDialogWindow(transport.responseText, title, width);
                }
                if (callback) {
                    eval(callback);
                }
                
                var me = this;
                Event.observe(window, 'resize', function(){
                    var height = window.document.documentElement.clientHeight;
                    var width = window.document.documentElement.clientWidth;
                    if(me.dialogWindow.width <= width && me.dialogWindow.height <= height){
                        me.dialogWindow.showCenter();
                    }
                });
            }.bind(this)
        });
    },
    openDialogWindow: function (content, title, width) {
        if ($(this.dialogWindowId) && typeof(Windows) != 'undefined') {
            Windows.focus(this.dialogWindowId);
            return;
        }
        this.overlayShowEffectOptions = Windows.overlayShowEffectOptions;
        this.overlayHideEffectOptions = Windows.overlayHideEffectOptions;
        Windows.overlayShowEffectOptions = {duration: 0};
        Windows.overlayHideEffectOptions = {duration: 0};
        this.dialogWindow = Dialog.info(content, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: title,
            width: width,
            //height:270,
            zIndex: 104,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: this.dialogWindowId,
            onClose: this.closeDialogWindow.bind(this),
            top: 200,
            left: (window.document.documentElement.clientWidth - width) / 2
        });

        content.evalScripts.bind(content).defer();
    },
    closeDialogWindow: function (window) {
        if (!window) {
            window = this.dialogWindow;
        }
        if (window) {
            window.close();
            Windows.overlayShowEffectOptions = this.overlayShowEffectOptions;
            Windows.overlayHideEffectOptions = this.overlayHideEffectOptions;
        }
    }
};