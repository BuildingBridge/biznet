(function (exports) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { keys.push.apply(keys, Object.getOwnPropertySymbols(object)); } if (enumerableOnly) keys = keys.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }
	var FormType = Object.freeze({
	  none: 'none',
	  like: 'like',
	  welcome: 'welcome',
	  offline: 'offline',
	  history: 'history'
	});
	var VoteType = Object.freeze({
	  none: 'none',
	  like: 'like',
	  dislike: 'dislike'
	});
	BX.Vue.cloneComponent('bx-imopenlines-message', 'bx-messenger-message', {
	  methods: {
	    checkFormShow: function checkFormShow() {
	      if (!this.message.params || !this.message.params.IMOL_FORM) {
	        return true;
	      }

	      if (this.message.params.IMOL_FORM === 'welcome') {
	        if (!this.widget.dialog.sessionClose && !this.widget.user.name && !this.widget.user.lastName && !this.widget.user.email && !this.widget.user.phone) {
	          this.$root.$emit('requestShowForm', {
	            type: FormType.welcome,
	            delayed: true
	          });
	        }
	      } else if (this.message.params.IMOL_FORM === 'offline') {
	        if (!this.widget.dialog.sessionClose && !this.widget.user.email) {
	          this.$root.$emit('requestShowForm', {
	            type: FormType.offline,
	            delayed: true
	          });
	        }
	      } else if (this.message.params.IMOL_FORM === 'history-delay') {
	        if (parseInt(this.message.params.IMOL_VOTE) === this.widget.dialog.sessionId && this.widget.dialog.userVote === VoteType.none) {
	          this.$root.$emit('requestShowForm', {
	            type: FormType.like,
	            delayed: true
	          });
	        }
	      }
	    }
	  },
	  created: function created() {
	    this.checkFormShow();
	  },
	  computed: _objectSpread({
	    dialogNumber: function dialogNumber() {
	      if (!this.message.params) {
	        return false;
	      }

	      if (!this.message.params.IMOL_SID) {
	        return false;
	      }

	      return this.localize.IMOL_MESSAGE_DIALOG_ID.replace('#ID#', this.message.params.IMOL_SID);
	    },
	    localize: function localize() {
	      return Object.freeze(Object.assign({}, this.parentLocalize, BX.Vue.getFilteredPhrases('IMOL_MESSAGE_', this.$root.$bitrixMessages)));
	    },
	    showMessage: function showMessage() {
	      if (!this.message.params) {
	        return true;
	      }

	      if (this.message.params.IMOL_FORM && this.message.params.IMOL_FORM === 'history-delay' // TODO change after release to vote
	      ) {
	          return false;
	        }

	      return true;
	    }
	  }, BX.Vuex.mapState({
	    widget: function widget(state) {
	      return state.widget;
	    }
	  })),
	  template: "\n\t\t<div v-if=\"showMessage\" class=\"bx-imopenlines-message\">\n\t\t\t<div v-if=\"dialogNumber\" class=\"bx-imopenlines-message-dialog-number\">{{dialogNumber}}</div>\n\t\t\t#PARENT_TEMPLATE#\n\t\t</div>\n\t"
	});

}((this.window = this.window || {})));
//# sourceMappingURL=message.bundle.js.map
