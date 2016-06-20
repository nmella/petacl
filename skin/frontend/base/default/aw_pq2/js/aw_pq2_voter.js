var awPq2VoteManager = Class.create();
awPq2VoteManager.prototype = {
    initialize: function(config) {
        this.config = config;
        this.containerList = $$(this.config.voterContainerSelector);

        this.voterList = [];
        this.initVoterList();
    },
    initVoterList: function() {
        var me = this;
        if (this.containerList.length > 0) {
            this.containerList.each(function(voterContainer) {
                var voterElement = new awPq2Voter(voterContainer, me.config);
                me.voterList.push(voterElement);
            });
        }
    }
};

var awPq2Voter = Class.create();
awPq2Voter.prototype = {
    initialize: function(voterContainer, config) {
        this.lock = false;
        this.config = config;
        this.voterContainer = voterContainer;

        this.likeElement = this.voterContainer.select(this.config.likeSelector).first();
        this.dislikeElement = this.voterContainer.select(this.config.dislikeSelector).first();

        this.valueElement = this.voterContainer.select(this.config.valueSelector).first();
        this.progressElement = this.voterContainer.select(this.config.progressSelector).first();

        this.initObservers();
    },

    initObservers: function() {
        var me = this;

        Event.observe(this.likeElement, 'click', function(e){
            Event.stop(e);
            if (me.likeElement.hasClassName(me.config.disabledClass) || me.lock) {
                return;
            }
            me.like();
        });

        Event.observe(this.dislikeElement, 'click', function(e){
            Event.stop(e);
            if (me.dislikeElement.hasClassName(me.config.disabledClass) || me.lock) {
                return;
            }
            me.dislike();
        });

    },

    like: function() {
        var me = this;
        new Ajax.Request(
            me.likeElement.getAttribute('href'),
            {
                method: 'post',
                parameters: {'value':me.getLikeValue()},
                onCreate : me._onVotingStart.bind(me),
                onComplete: me._onLikeVoteCompleteFn.bind(me)
            }
        );
    },

    dislike: function() {
        var me = this;
        new Ajax.Request(
            me.likeElement.getAttribute('href'),
            {
                method: 'post',
                parameters: {'value':me.getDislikeValue()},
                onCreate : me._onVotingStart.bind(me),
                onComplete: me._onDislikeVoteCompleteFn.bind(me)
            }
        );
    },

    updateValue: function(value) {
        this.valueElement.update(parseInt(this.valueElement.innerHTML) + value);
    },

    getLikeValue: function() {
        if (this.likeElement.hasClassName(this.config.votedLikeClass)) {
            return -1;
        }
        if (this.dislikeElement.hasClassName(this.config.votedDislikeClass)) {
            return 2;
        }
        return 1;
    },

    getDislikeValue: function() {
        if (this.dislikeElement.hasClassName(this.config.votedDislikeClass)) {
            return 1;
        }
        if (this.likeElement.hasClassName(this.config.votedLikeClass)) {
            return -2;
        }
        return -1;
    },

    _onLikeVoteCompleteFn: function(transport) {
        try {
            eval("var json = " + transport.responseText + " || {}");
        } catch(e) {
            return;
        }
        if (json.success) {
            this.updateValue(this.getLikeValue());
            this.dislikeElement.removeClassName(this.config.votedDislikeClass);
            this.likeElement.toggleClassName(this.config.votedLikeClass);
        }
        this.progressElement.hide();
        this.valueElement.show();
        this.lock = false;
    },

    _onDislikeVoteCompleteFn: function(transport) {
        try {
            eval("var json = " + transport.responseText + " || {}");
        } catch(e) {
            return;
        }
        if (json.success) {
            this.updateValue(this.getDislikeValue());
            this.likeElement.removeClassName(this.config.votedLikeClass);
            this.dislikeElement.toggleClassName(this.config.votedDislikeClass);
        }
        this.progressElement.hide();
        this.valueElement.show();
        this.lock = false;
    },

    _onVotingStart: function() {
        this.lock = true;
        this.valueElement.hide();
        this.progressElement.show();
    }
};