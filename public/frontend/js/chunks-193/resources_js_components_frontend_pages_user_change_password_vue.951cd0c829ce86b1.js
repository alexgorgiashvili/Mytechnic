"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["resources_js_components_frontend_pages_user_change_password_vue"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/pages/user/change_password.vue?vue&type=script&lang=js&":
/*!******************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/pages/user/change_password.vue?vue&type=script&lang=js& ***!
  \******************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _partials_user_sidebar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../partials/user_sidebar */ "./resources/js/components/frontend/partials/user_sidebar.vue");

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: "change_password",
  data: function data() {
    return {
      current: 'change_password',
      loading: false,
      formData: {
        current_password: "",
        new_password: "",
        confirm_password: "",
        is_password_set: this.$store.getters.getUser.is_password_set
      }
    };
  },
  components: {
    user_sidebar: _partials_user_sidebar__WEBPACK_IMPORTED_MODULE_0__["default"]
  },
  methods: {
    changPassword: function changPassword() {
      var _this = this;
      var url = this.getUrl('user/change-password');
      this.loading = true;
      axios.post(url, this.formData).then(function (response) {
        _this.loading = false;
        if (response.data.success) {
          toastr.success(response.data.success, _this.lang.Success + ' !!');
          _this.formData.confirm_password = '';
          _this.formData.new_password = '';
          _this.formData.current_password = '';
          _this.formData.is_password_set = response.data.data.is_password_set;
          _this.$store.dispatch('user', response.data.data);
        } else {
          if (response.data.error) {
            toastr.error(response.data.error, _this.lang.Error + ' !!');
          }
        }
      })["catch"](function (error) {
        _this.loading = false;
        if (error.response.status == 422) {
          _this.errors = error.response.data.errors;
        }
      });
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/shimmer.vue?vue&type=script&lang=js&":
/*!********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/shimmer.vue?vue&type=script&lang=js& ***!
  \********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: "shimmer.vue",
  props: ['height'],
  data: function data() {
    return {
      style: {
        height: this.height + 'px'
      }
    };
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _shimmer__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./shimmer */ "./resources/js/components/frontend/partials/shimmer.vue");

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  name: "user_sidebar",
  props: ['current', 'addresses'],
  data: function data() {
    return {
      loading: false,
      download_url: false,
      show_menu: ''
    };
  },
  mounted: function mounted() {
    this.checkAuth();
  },
  computed: {
    totalReward: function totalReward() {
      return this.$store.getters.getTotalReward;
    },
    modalType: function modalType() {
      return this.$store.getters.getModalType;
    }
  },
  components: {
    shimmer: _shimmer__WEBPACK_IMPORTED_MODULE_0__["default"]
  },
  methods: {
    checkAuth: function checkAuth() {
      var _this = this;
      var url = this.getUrl('home/check-auth');
      axios.get(url).then(function (response) {
        _this.$store.dispatch('user', response.data.user);
        _this.$store.commit('getOrderUrl', response.data.order_urls);
        if (!_this.authUser) {
          _this.$router.push({
            name: 'login'
          });
        } else if (_this.authUser.user_type == 'admin') {
          _this.$router.push({
            name: 'home'
          });
        }
        if (response.data.reward) {
          _this.$store.commit('setTotalReward', response.data.reward);
        }
        if (response.data.download_urls) {
          _this.download_url = true;
        }
      });
    },
    convertReward: function convertReward() {
      var _this2 = this;
      var url = this.getUrl('user/convert-reward');
      var form = {
        amount: this.converted_reward / this.settings.reward_convert_rate,
        reward: this.converted_reward
      };
      if (form.amount > 0 && this.totalReward.rewards >= this.converted_reward && confirm('Are You Sure! You want to Convert ?')) {
        this.loading = true;
        axios.post(url, form).then(function (response) {
          _this2.loading = false;
          if (response.data.error) {
            toastr.error(response.data.error, _this2.lang.Error + ' !!');
          } else {
            toastr.success(response.data.success, _this2.lang.Success + '!!');
            $('#convert_reward').modal('hide');
            _this2.converted_reward = '';
            _this2.$store.dispatch('user', response.data.user);
            _this2.$store.commit('setTotalReward', response.data.reward);
          }
        })["catch"](function (error) {
          _this2.loading = false;
        });
      }
    },
    showMenu: function showMenu() {
      if (this.show_menu == 'displayMenu') {
        this.show_menu = '';
      } else {
        this.show_menu = 'displayMenu';
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/pages/user/change_password.vue?vue&type=template&id=332816da&":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/pages/user/change_password.vue?vue&type=template&id=332816da& ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render),
/* harmony export */   staticRenderFns: () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c("div", {
    staticClass: "sg-page-content"
  }, [_c("section", {
    staticClass: "edit-profile"
  }, [_c("div", {
    staticClass: "container"
  }, [_c("div", {
    staticClass: "row"
  }, [_c("user_sidebar", {
    attrs: {
      current: _vm.current
    }
  }), _vm._v(" "), _c("div", {
    staticClass: "col-lg-9"
  }, [_c("div", {
    staticClass: "edit-profile-box"
  }, [_c("div", {
    staticClass: "title justify-content-between"
  }, [_c("h1", [_vm._v(_vm._s(_vm.lang.change_password))])]), _vm._v(" "), _c("form", {
    on: {
      submit: function submit($event) {
        $event.preventDefault();
        return _vm.changPassword();
      }
    }
  }, [_c("div", {
    staticClass: "sg-card"
  }, [_vm.authUser.is_password_set == 1 ? _c("div", {
    staticClass: "form-group"
  }, [_c("label", [_vm._v(_vm._s(_vm.lang.current_password))]), _vm._v(" "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.formData.current_password,
      expression: "formData.current_password"
    }],
    staticClass: "form-control",
    "class": {
      error_border: _vm.errors.current_password
    },
    attrs: {
      type: "password",
      placeholder: _vm.lang.current_password
    },
    domProps: {
      value: _vm.formData.current_password
    },
    on: {
      input: function input($event) {
        if ($event.target.composing) return;
        _vm.$set(_vm.formData, "current_password", $event.target.value);
      }
    }
  })]) : _vm._e(), _vm._v(" "), _vm.errors.current_password ? _c("span", {
    staticClass: "validation_error"
  }, [_vm._v(_vm._s(_vm.errors.current_password[0]))]) : _vm._e(), _vm._v(" "), _c("div", {
    staticClass: "form-group"
  }, [_c("label", [_vm._v(_vm._s(_vm.lang.new_password))]), _vm._v(" "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.formData.new_password,
      expression: "formData.new_password"
    }],
    staticClass: "form-control",
    "class": {
      error_border: _vm.errors.new_password
    },
    attrs: {
      type: "password",
      placeholder: _vm.lang.new_password
    },
    domProps: {
      value: _vm.formData.new_password
    },
    on: {
      input: function input($event) {
        if ($event.target.composing) return;
        _vm.$set(_vm.formData, "new_password", $event.target.value);
      }
    }
  })]), _vm._v(" "), _vm.errors.new_password ? _c("span", {
    staticClass: "validation_error"
  }, [_vm._v(_vm._s(_vm.errors.new_password[0]))]) : _vm._e(), _vm._v(" "), _c("div", {
    staticClass: "form-group"
  }, [_c("label", [_vm._v(_vm._s(_vm.lang.confirm_new_password))]), _vm._v(" "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.formData.confirm_password,
      expression: "formData.confirm_password"
    }],
    staticClass: "form-control",
    "class": {
      error_border: _vm.errors.confirm_password
    },
    attrs: {
      type: "password",
      placeholder: _vm.lang.confirm_new_password
    },
    domProps: {
      value: _vm.formData.confirm_password
    },
    on: {
      input: function input($event) {
        if ($event.target.composing) return;
        _vm.$set(_vm.formData, "confirm_password", $event.target.value);
      }
    }
  })]), _vm._v(" "), _vm.errors.confirm_password ? _c("span", {
    staticClass: "validation_error"
  }, [_vm._v(_vm._s(_vm.errors.confirm_password[0]))]) : _vm._e(), _vm._v(" "), _vm.loading ? _c("loading_button", {
    attrs: {
      class_name: "btn btn-primary"
    }
  }) : _c("button", {
    staticClass: "btn btn-primary",
    attrs: {
      type: "submit"
    }
  }, [_vm._v(_vm._s(_vm.lang.save_change))])], 1)])])])], 1)])])]);
};
var staticRenderFns = [];
render._withStripped = true;


/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/shimmer.vue?vue&type=template&id=44ada926&":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/shimmer.vue?vue&type=template&id=44ada926& ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render),
/* harmony export */   staticRenderFns: () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c("img", {
    staticClass: "shimmer",
    style: [_vm.height ? _vm.style : null],
    attrs: {
      src: _vm.getUrl("public/images/default/preview.jpg"),
      alt: "shimmer"
    }
  });
};
var staticRenderFns = [];
render._withStripped = true;


/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=template&id=cdcc10da&":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=template&id=cdcc10da& ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render),
/* harmony export */   staticRenderFns: () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _vm.authUser ? _c("div", {
    staticClass: "col-lg-3"
  }, [_c("div", {
    staticClass: "sidebar-menu"
  }, [_c("ul", {
    staticClass: "global-list"
  }, [_c("li", {
    "class": {
      active: _vm.current === "dashboard"
    }
  }, [_c("router-link", {
    attrs: {
      to: {
        name: "dashboard"
      }
    }
  }, [_c("span", {
    staticClass: "mdi mdi-name mdi-view-dashboard-outline"
  }), _vm._v(" " + _vm._s(_vm.lang.dashboard) + "\n                    ")])], 1), _vm._v(" "), _c("li", {
    "class": {
      active: _vm.current === "addresses"
    }
  }, [_c("router-link", {
    attrs: {
      to: {
        name: "addresses"
      }
    }
  }, [_c("span", {
    staticClass: "mdi mdi-name mdi-map-marker-outline"
  }), _vm._v("\n                        " + _vm._s(_vm.lang.addresses) + "\n                    ")])], 1), _vm._v(" "), _c("li", {
    "class": {
      active: _vm.current === "notification"
    }
  }, [_c("router-link", {
    attrs: {
      to: {
        name: "notification"
      }
    }
  }, [_c("span", {
    staticClass: "mdi mdi-name mdi-bell-outline"
  }), _vm._v("\n                        " + _vm._s(_vm.lang.notification) + "\n                    ")])], 1), _vm._v(" "), _c("li", {
    "class": {
      active: _vm.current === "order_history"
    }
  }, [_c("router-link", {
    attrs: {
      to: {
        name: "order.history"
      }
    }
  }, [_c("span", {
    staticClass: "mdi mdi-name mdi-cart-outline"
  }), _vm._v("\n                        " + _vm._s(_vm.lang.order_history) + "\n                    ")])], 1), _vm._v(" "), _vm.download_url ? _c("li", {
    "class": {
      active: _vm.current === "digital_product_order_history"
    }
  }, [_c("router-link", {
    attrs: {
      to: {
        name: "orders.digital.product"
      }
    }
  }, [_c("span", {
    staticClass: "mdi mdi-name mdi-cart-arrow-down"
  }), _vm._v(" " + _vm._s(_vm.lang.digital_product_order) + "\n                    ")])], 1) : _vm._e(), _vm._v(" "), _vm.settings.coupon_system == 1 ? _c("li", {
    "class": {
      active: _vm.current === "gift_voucher"
    }
  }, [_c("router-link", {
    attrs: {
      to: {
        name: "gift.voucher"
      }
    }
  }, [_c("span", {
    staticClass: "mdi mdi-name mdi-wallet-giftcard"
  }), _vm._v("\n                        " + _vm._s(_vm.lang.gift_voucher) + "\n                    ")])], 1) : _vm._e(), _vm._v(" "), _c("li", {
    "class": {
      active: _vm.current === "change_password"
    }
  }, [_c("router-link", {
    attrs: {
      to: {
        name: "change.password"
      }
    }
  }, [_c("span", {
    staticClass: "mdi mdi-name mdi-lock-outline"
  }), _vm._v("\n                        " + _vm._s(_vm.lang.change_password) + "\n                    ")])], 1), _vm._v(" "), _vm.settings.wallet_system == 1 ? _c("li", {
    "class": {
      active: _vm.current === "wallet_history"
    }
  }, [_c("router-link", {
    attrs: {
      to: {
        name: "wallet.history"
      }
    }
  }, [_c("span", {
    staticClass: "mdi mdi-wallet-outline"
  }), _vm._v("\n                        " + _vm._s(_vm.lang.my_wallet) + "\n                    ")])], 1) : _vm._e(), _vm._v(" "), _vm.addons.includes("reward") ? _c("li", {
    "class": {
      active: _vm.current === "reward_history"
    }
  }, [_c("router-link", {
    attrs: {
      to: {
        name: "reward.history"
      }
    }
  }, [_c("span", {
    staticClass: "mdi mdi-vector-point"
  }), _vm._v(_vm._s(_vm.lang.my_rewards) + "\n                    ")])], 1) : _vm._e(), _vm._v(" "), _vm.addons.includes("affiliate") && _vm.authUser.referral_code ? _c("li", {
    staticClass: "dp-arrow",
    "class": {
      active: _vm.current === "affiliate_system",
      displayMenu: _vm.show_menu === "displayMenu"
    },
    on: {
      click: _vm.showMenu
    }
  }, [_vm._m(0), _vm._v(" "), _c("ul", {
    staticClass: "dashboard-dp-menu"
  }, [_c("li", [_c("router-link", {
    attrs: {
      to: {
        name: "affiliate.system"
      }
    }
  }, [_vm._v("Affiliate System")])], 1), _vm._v(" "), _c("li", [_c("router-link", {
    attrs: {
      to: "/sdfsfd"
    }
  }, [_vm._v("iewww1")])], 1)])]) : _vm._e(), _vm._v(" "), _vm.settings.seller_system == 1 ? _c("li", {
    "class": {
      active: _vm.current === "followed_shop"
    }
  }, [_c("router-link", {
    attrs: {
      to: {
        name: "shop.followed"
      }
    }
  }, [_c("span", {
    staticClass: "mdi mdi-home-heart"
  }), _vm._v(_vm._s(_vm.lang.shop) + "\n                    ")])], 1) : _vm._e()])]), _vm._v(" "), _c("div", {
    staticClass: "modal fade reward",
    attrs: {
      id: "convert_reward",
      tabindex: "-1",
      "aria-labelledby": "exampleModalLabel",
      "aria-hidden": "true"
    }
  }, [_c("div", {
    staticClass: "modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable"
  }, [_c("div", {
    staticClass: "modal-content"
  }, [_c("div", {
    staticClass: "modal-header"
  }, [_c("h5", {
    staticClass: "modal-title"
  }, [_vm._v(_vm._s(_vm.lang.reward_point))]), _vm._v(" "), _vm._m(1)]), _vm._v(" "), _c("div", {
    staticClass: "modal-body reward_modal"
  }, [_c("form", {
    on: {
      submit: function submit($event) {
        $event.preventDefault();
        return _vm.convertReward.apply(null, arguments);
      }
    }
  }, [_c("div", {
    staticClass: "row"
  }, [_c("div", {
    staticClass: "col-lg-12 text-center"
  }, [_c("div", {
    staticClass: "form-group"
  }, [_c("label", {
    attrs: {
      "for": "reward"
    }
  }, [_vm._v(_vm._s(_vm.lang.reward_point) + " ")]), _vm._v(" "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.converted_reward,
      expression: "converted_reward"
    }],
    staticClass: "form-control",
    attrs: {
      type: "text",
      id: "reward",
      placeholder: _vm.lang.enter_point_you_want_convert
    },
    domProps: {
      value: _vm.converted_reward
    },
    on: {
      input: function input($event) {
        if ($event.target.composing) return;
        _vm.converted_reward = $event.target.value;
      }
    }
  })]), _vm._v(" "), _vm.totalReward != null ? _c("div", {
    staticClass: "text-start"
  }, [_c("p", [_vm._v("Available Points to Convert : " + _vm._s(_vm.totalReward.rewards))]), _vm._v(" "), _c("p", [_vm._v(_vm._s(_vm.settings.reward_convert_rate) + _vm._s(_vm.lang.reward_points) + _vm._s(_vm.priceFormat(1)))]), _vm._v(" "), _vm.totalReward.rewards > 0 ? _c("p", [_vm._v(_vm._s(_vm.lang.total_amount_you_will_get) + "\n                                            " + _vm._s(_vm.priceFormat(_vm.converted_reward / _vm.settings.reward_convert_rate)))]) : _vm._e()]) : _vm._e(), _vm._v(" "), _vm.loading ? _c("loading_button", {
    attrs: {
      class_name: "btn btn-primary mt-3"
    }
  }) : _c("button", {
    staticClass: "btn btn-primary mt-3",
    "class": {
      disable_btn: _vm.converted_reward < _vm.settings.reward_convert_rate || _vm.totalReward.rewards < _vm.converted_reward
    },
    attrs: {
      type: "submit"
    }
  }, [_vm._v("\n                                        " + _vm._s(_vm.lang.covert_rewards) + "\n                                    ")])], 1)])])])])])])]) : _vm._e();
};
var staticRenderFns = [function () {
  var _vm = this,
    _c = _vm._self._c;
  return _c("a", {
    attrs: {
      href: "javascript:void(0)"
    }
  }, [_c("span", {
    staticClass: "mdi mdi-vector-point"
  }), _vm._v("Affiliate\n                ")]);
}, function () {
  var _vm = this,
    _c = _vm._self._c;
  return _c("button", {
    staticClass: "close modal_close",
    attrs: {
      type: "button",
      "data-bs-dismiss": "modal",
      "aria-label": "Close"
    }
  }, [_c("span", {
    attrs: {
      "aria-hidden": "true"
    }
  }, [_vm._v("×")])]);
}];
render._withStripped = true;


/***/ }),

/***/ "./resources/js/components/frontend/pages/user/change_password.vue":
/*!*************************************************************************!*\
  !*** ./resources/js/components/frontend/pages/user/change_password.vue ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _change_password_vue_vue_type_template_id_332816da___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./change_password.vue?vue&type=template&id=332816da& */ "./resources/js/components/frontend/pages/user/change_password.vue?vue&type=template&id=332816da&");
/* harmony import */ var _change_password_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./change_password.vue?vue&type=script&lang=js& */ "./resources/js/components/frontend/pages/user/change_password.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _change_password_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _change_password_vue_vue_type_template_id_332816da___WEBPACK_IMPORTED_MODULE_0__.render,
  _change_password_vue_vue_type_template_id_332816da___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/frontend/pages/user/change_password.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/components/frontend/partials/shimmer.vue":
/*!***************************************************************!*\
  !*** ./resources/js/components/frontend/partials/shimmer.vue ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _shimmer_vue_vue_type_template_id_44ada926___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./shimmer.vue?vue&type=template&id=44ada926& */ "./resources/js/components/frontend/partials/shimmer.vue?vue&type=template&id=44ada926&");
/* harmony import */ var _shimmer_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./shimmer.vue?vue&type=script&lang=js& */ "./resources/js/components/frontend/partials/shimmer.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _shimmer_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _shimmer_vue_vue_type_template_id_44ada926___WEBPACK_IMPORTED_MODULE_0__.render,
  _shimmer_vue_vue_type_template_id_44ada926___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/frontend/partials/shimmer.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/components/frontend/partials/user_sidebar.vue":
/*!********************************************************************!*\
  !*** ./resources/js/components/frontend/partials/user_sidebar.vue ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _user_sidebar_vue_vue_type_template_id_cdcc10da___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./user_sidebar.vue?vue&type=template&id=cdcc10da& */ "./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=template&id=cdcc10da&");
/* harmony import */ var _user_sidebar_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./user_sidebar.vue?vue&type=script&lang=js& */ "./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=script&lang=js&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _user_sidebar_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _user_sidebar_vue_vue_type_template_id_cdcc10da___WEBPACK_IMPORTED_MODULE_0__.render,
  _user_sidebar_vue_vue_type_template_id_cdcc10da___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/frontend/partials/user_sidebar.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/js/components/frontend/pages/user/change_password.vue?vue&type=script&lang=js&":
/*!**************************************************************************************************!*\
  !*** ./resources/js/components/frontend/pages/user/change_password.vue?vue&type=script&lang=js& ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_change_password_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./change_password.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/pages/user/change_password.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_change_password_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/frontend/partials/shimmer.vue?vue&type=script&lang=js&":
/*!****************************************************************************************!*\
  !*** ./resources/js/components/frontend/partials/shimmer.vue?vue&type=script&lang=js& ***!
  \****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_shimmer_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./shimmer.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/shimmer.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_shimmer_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************!*\
  !*** ./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_user_sidebar_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./user_sidebar.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=script&lang=js&");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_user_sidebar_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/frontend/pages/user/change_password.vue?vue&type=template&id=332816da&":
/*!********************************************************************************************************!*\
  !*** ./resources/js/components/frontend/pages/user/change_password.vue?vue&type=template&id=332816da& ***!
  \********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_change_password_vue_vue_type_template_id_332816da___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_change_password_vue_vue_type_template_id_332816da___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_change_password_vue_vue_type_template_id_332816da___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./change_password.vue?vue&type=template&id=332816da& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/pages/user/change_password.vue?vue&type=template&id=332816da&");


/***/ }),

/***/ "./resources/js/components/frontend/partials/shimmer.vue?vue&type=template&id=44ada926&":
/*!**********************************************************************************************!*\
  !*** ./resources/js/components/frontend/partials/shimmer.vue?vue&type=template&id=44ada926& ***!
  \**********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_shimmer_vue_vue_type_template_id_44ada926___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_shimmer_vue_vue_type_template_id_44ada926___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_shimmer_vue_vue_type_template_id_44ada926___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./shimmer.vue?vue&type=template&id=44ada926& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/shimmer.vue?vue&type=template&id=44ada926&");


/***/ }),

/***/ "./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=template&id=cdcc10da&":
/*!***************************************************************************************************!*\
  !*** ./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=template&id=cdcc10da& ***!
  \***************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_user_sidebar_vue_vue_type_template_id_cdcc10da___WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_user_sidebar_vue_vue_type_template_id_cdcc10da___WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_user_sidebar_vue_vue_type_template_id_cdcc10da___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./user_sidebar.vue?vue&type=template&id=cdcc10da& */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/js/components/frontend/partials/user_sidebar.vue?vue&type=template&id=cdcc10da&");


/***/ })

}]);