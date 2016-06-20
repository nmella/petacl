var awPq2ItemManager = Class.create();
awPq2ItemManager.prototype = {
    initialize: function (config) {
        this.config = config;
        this.addQuestionButtonList = $$(config.addQuestionButtonSelector);
        this.questionForm = $$(config.questionFormSelector).first();
        this.questionItemContainerList = $$(config.questionItemContainerSelector);
        this.nextQuestionsButton = $$(config.nextQuestionsSelector).first();
        this.questionItemHiddenClass = config.questionItemHiddenClass;
        this.questionPageSize = config.questionPageSize;
        this.itemList = [];

        this.initObservers();
        this.initItemList();
        this.processNextQuestions();
    },
    initObservers: function () {
        var me = this;
        if (this.addQuestionButtonList.length > 0 && this.questionForm) {
            this.addQuestionButtonList.each(function (addQuestionButton) {
                Event.observe(addQuestionButton, 'click', function (e) {
                    me.toggleAddQuestionForm(addQuestionButton);
                });
            });
        }
        Event.observe(this.nextQuestionsButton, 'click', me.processNextQuestions.bind(me));
    },
    initItemList: function () {
        var me = this;
        if (this.questionItemContainerList.length > 0) {
            this.questionItemContainerList.each(function (questionContainerItem) {
                var itemElement = new awPq2Item(questionContainerItem, me.config);
                me.itemList.push(itemElement);
            });
        }
    },
    toggleAddQuestionForm: function (addQuestionButton) {
        var textContainer = addQuestionButton.select(
            this.config.addQuestionButtonTextContainerSelector
        ).first();
        if (textContainer) {
            var text = textContainer.innerHTML.trim();
            if (text === this.config.addQuestionButtonText.inactive) {
                addQuestionButton.setAttribute('title', this.config.addQuestionButtonText.active);
                textContainer.update(this.config.addQuestionButtonText.active);
            } else {
                addQuestionButton.setAttribute('title', this.config.addQuestionButtonText.inactive);
                textContainer.update(this.config.addQuestionButtonText.inactive);
            }
        }
        if (this.questionForm) {
            this.questionForm.toggle();
        }
    },
    processNextQuestions: function () {
        this.showNextQuestionsPage();
        this.checkNextQuestionsButton();
    },
    showNextQuestionsPage: function () {
        var me = this;
        var nextQuestionsList = $$('.' + this.questionItemHiddenClass).splice(0, this.questionPageSize);
        nextQuestionsList.each(function (nextQuestionItem) {
            nextQuestionItem.removeClassName(me.questionItemHiddenClass);
        })
    },
    checkNextQuestionsButton: function () {
        if ($$('.' + this.questionItemHiddenClass).length > 0) {
            this.nextQuestionsButton.show();
            return;
        }
        this.nextQuestionsButton.hide();
    }
};

var awPq2Item = Class.create();
awPq2Item.prototype = {
    initialize: function (questionContainerItem, config) {
        this.config = config;
        this.questionContainerItem = questionContainerItem;
        this.questionContentElement = this.questionContainerItem.select(this.config.questionContentSelector).first();
        this.questionExpandElement = this.questionContainerItem.select(this.config.questionExpandSelector).first();
        this.answerListBlock = this.questionContainerItem.select(this.config.answerListContainerSelector).first();
        this.addAnswerButton = this.questionContainerItem.select(this.config.addAnswerButtonSelector).first();
        this.nextAnswersButton = this.questionContainerItem.select(this.config.nextAnswersSelector).first();
        this.answerForm = this.questionContainerItem.select(this.config.answerFormSelector).first();
        this.answerItemHiddenClass = config.answerItemHiddenClass;
        this.answerPageSize = config.answerPageSize;

        this.questionExpandStatus = {
            activeClassName: config.questionExpandStatus.activeClassName,
            inactiveClassName: config.questionExpandStatus.inactiveClassName
        };

        this.initExpand();
        this.initObservers();
        this.processNextAnswers();
    },

    initExpand: function () {
        if (Object.isUndefined(Storage)) {
            return;
        }
        var questionId = this.questionExpandElement.getAttribute('data-question-id');
        var expandStatus = (localStorage.getItem('aw-pq2-expand') || "{}").evalJSON();
        if (Object.isUndefined(expandStatus[questionId]) || !expandStatus[questionId]) {
            return;
        }
        this.toggleAnswerList();
    },

    initObservers: function () {
        var me = this;
        Event.observe(this.questionContentElement, 'click', function (e) {
            var target = e.target || e.srcElement;
            if (target.tagName.toUpperCase() === 'A') {
                return;
            }
            me.toggleAnswerList();
        });
        Event.observe(this.questionContentElement, 'mousedown', function (e) {
            Event.stop(e);
        });
        Event.observe(this.questionExpandElement, 'click', me.toggleAnswerList.bind(me));
        if (this.addAnswerButton) {
            Event.observe(this.addAnswerButton, 'click', me.toggleAddAnswerForm.bind(me));
        }
        Event.observe(this.nextAnswersButton, 'click', me.processNextAnswers.bind(me));
    },

    toggleAnswerList: function () {
        this.toggleExpandElementClass();
        this.toggleAnswerListBlock();
        if (!Object.isUndefined(Storage)) {
            var isActive = this.questionExpandElement.hasClassName(this.questionExpandStatus.inactiveClassName);
            var questionId = this.questionExpandElement.getAttribute('data-question-id');
            var expandStatus = (localStorage.getItem('aw-pq2-expand') || "{}").evalJSON();
            expandStatus[questionId] = isActive;
            localStorage.setItem('aw-pq2-expand', Object.toJSON(expandStatus));
        }
    },

    toggleExpandElementClass: function () {
        if (!this.questionExpandElement) {
            return;
        }
        this.questionExpandElement.toggleClassName(this.questionExpandStatus.activeClassName);
        this.questionExpandElement.toggleClassName(this.questionExpandStatus.inactiveClassName);
    },

    toggleAnswerListBlock: function () {
        if (!this.answerListBlock) {
            return;
        }
        this.answerListBlock.toggle();
    },

    toggleAddAnswerForm: function () {
        var textContainer = this.addAnswerButton.select(
            this.config.addAnswerButtonTextContainerSelector
        ).first();
        if (textContainer) {
            var text = textContainer.innerHTML.trim();
            if (text === this.config.addAnswerButtonText.inactive) {
                this.addAnswerButton.setAttribute('title', this.config.addAnswerButtonText.active);
                textContainer.update(this.config.addAnswerButtonText.active);
            } else {
                this.addAnswerButton.setAttribute('title', this.config.addAnswerButtonText.inactive);
                textContainer.update(this.config.addAnswerButtonText.inactive);
            }
        }
        if (this.answerForm) {
            this.answerForm.toggle();
        }
    },

    processNextAnswers: function () {
        this.showNextAnswersPage();
        this.checkNextAnswersButton();
    },

    showNextAnswersPage: function () {
        var me = this;
        var nextAnswerList = this.answerListBlock.select('.' + this.answerItemHiddenClass).splice(0, this.answerPageSize);
        nextAnswerList.each(function (nextAnswerItem) {
            nextAnswerItem.removeClassName(me.answerItemHiddenClass);
        })
    },

    checkNextAnswersButton: function () {
        if (this.answerListBlock.select('.' + this.answerItemHiddenClass).length > 0) {
            this.nextAnswersButton.show();
            return;
        }
        this.nextAnswersButton.hide();
    }
};