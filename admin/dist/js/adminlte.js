/*!
 * AdminLTE v3.1.0 (https://adminlte.io)
 * Copyright 2014-2021 Colorlib <https://colorlib.com>
 * Licensed under MIT (https://github.com/ColorlibHQ/AdminLTE/blob/master/LICENSE)
 */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('jquery')) :
  typeof define === 'function' && define.amd ? define(['exports', 'jquery'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.adminlte = {}, global.jQuery));
}(this, (function (exports, $) { 'use strict';

  function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

  var $__default = /*#__PURE__*/_interopDefaultLegacy($);

  /**
   * --------------------------------------------
   * AdminLTE CardRefresh.js
   * License MIT
   * --------------------------------------------
   */
  /**
   * Constants
   * ====================================================
   */

  var NAME$e = 'CardRefresh';
  var DATA_KEY$e = 'lte.cardrefresh';
  var EVENT_KEY$7 = "." + DATA_KEY$e;
  var JQUERY_NO_CONFLICT$e = $__default['default'].fn[NAME$e];
  var EVENT_LOADED = "loaded" + EVENT_KEY$7;
  var EVENT_OVERLAY_ADDED = "overlay.added" + EVENT_KEY$7;
  var EVENT_OVERLAY_REMOVED = "overlay.removed" + EVENT_KEY$7;
  var CLASS_NAME_CARD$1 = 'card';
  var SELECTOR_CARD$1 = "." + CLASS_NAME_CARD$1;
  var SELECTOR_DATA_REFRESH = '[data-card-widget="card-refresh"]';
  var Default$c = {
    source: '',
    sourceSelector: '',
    params: {},
    trigger: SELECTOR_DATA_REFRESH,
    content: '.card-body',
    loadInContent: true,
    loadOnInit: true,
    responseType: '',
    overlayTemplate: '<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>',
    onLoadStart: function onLoadStart() {},
    onLoadDone: function onLoadDone(response) {
      return response;
    }
  };

  var CardRefresh = /*#__PURE__*/function () {
    function CardRefresh(element, settings) {
      this._element = element;
      this._parent = element.parents(SELECTOR_CARD$1).first();
      this._settings = $__default['default'].extend({}, Default$c, settings);
      this._overlay = $__default['default'](this._settings.overlayTemplate);

      if (element.hasClass(CLASS_NAME_CARD$1)) {
        this._parent = element;
      }

      if (this._settings.source === '') {
        throw new Error('Source url was not defined. Please specify a url in your CardRefresh source option.');
      }
    }

    var _proto = CardRefresh.prototype;

    _proto.load = function load() {
      var _this = this;

      this._addOverlay();

      this._settings.onLoadStart.call($__default['default'](this));

      $__default['default'].get(this._settings.source, this._settings.params, function (response) {
        if (_this._settings.loadInContent) {
          if (_this._settings.sourceSelector !== '') {
            response = $__default['default'](response).find(_this._settings.sourceSelector).html();
          }

          _this._parent.find(_this._settings.content).html(response);
        }

        _this._settings.onLoadDone.call($__default['default'](_this), response);

        _this._removeOverlay();
      }, this._settings.responseType !== '' && this._settings.responseType);
      $__default['default'](this._element).trigger($__default['default'].Event(EVENT_LOADED));
    };

    _proto._addOverlay = function _addOverlay() {
      this._parent.append(this._overlay);

      $__default['default'](this._element).trigger($__default['default'].Event(EVENT_OVERLAY_ADDED));
    };

    _proto._removeOverlay = function _removeOverlay() {
      this._parent.find(this._overlay).remove();

      $__default['default'](this._element).trigger($__default['default'].Event(EVENT_OVERLAY_REMOVED));
    } // Private
    ;

    _proto._init = function _init() {
      var _this2 = this;

      $__default['default'](this).find(this._settings.trigger).on('click', function () {
        _this2.load();
      });

      if (this._settings.loadOnInit) {
        this.load();
      }
    } // Static
    ;

    CardRefresh._jQueryInterface = function _jQueryInterface(config) {
      var data = $__default['default'](this).data(DATA_KEY$e);

      var _options = $__default['default'].extend({}, Default$c, $__default['default'](this).data());

      if (!data) {
        data = new CardRefresh($__default['default'](this), _options);
        $__default['default'](this).data(DATA_KEY$e, typeof config === 'string' ? data : config);
      }

      if (typeof config === 'string' && /load/.test(config)) {
        data[config]();
      } else {
        data._init($__default['default'](this));
      }
    };

    return CardRefresh;
  }();
  /**
   * Data API
   * ====================================================
   */


  $__default['default'](document).on('click', SELECTOR_DATA_REFRESH, function (event) {
    if (event) {
      event.preventDefault();
    }

    CardRefresh._jQueryInterface.call($__default['default'](this), 'load');
  });
  $__default['default'](function () {
    $__default['default'](SELECTOR_DATA_REFRESH).each(function () {
      CardRefresh._jQueryInterface.call($__default['default'](this));
    });
  });
  /**
   * jQuery API
   * ====================================================
   */

  $__default['default'].fn[NAME$e] = CardRefresh._jQueryInterface;
  $__default['default'].fn[NAME$e].Constructor = CardRefresh;

  $__default['default'].fn[NAME$e].noConflict = function () {
    $__default['default'].fn[NAME$e] = JQUERY_NO_CONFLICT$e;
    return CardRefresh._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE CardWidget.js
   * License MIT
   * --------------------------------------------
   */
  /**
   * Constants
   * ====================================================
   */

  var NAME$d = 'CardWidget';
  var DATA_KEY$d = 'lte.cardwidget';
  var EVENT_KEY$6 = "." + DATA_KEY$d;
  var JQUERY_NO_CONFLICT$d = $__default['default'].fn[NAME$d];
  var EVENT_EXPANDED$3 = "expanded" + EVENT_KEY$6;
  var EVENT_COLLAPSED$4 = "collapsed" + EVENT_KEY$6;
  var EVENT_MAXIMIZED = "maximized" + EVENT_KEY$6;
  var EVENT_MINIMIZED = "minimized" + EVENT_KEY$6;
  var EVENT_REMOVED$1 = "removed" + EVENT_KEY$6;
  var CLASS_NAME_CARD = 'card';
  var CLASS_NAME_COLLAPSED$1 = 'collapsed-card';
  var CLASS_NAME_COLLAPSING = 'collapsing-card';
  var CLASS_NAME_EXPANDING = 'expanding-card';
  var CLASS_NAME_WAS_COLLAPSED = 'was-collapsed';
  var CLASS_NAME_MAXIMIZED = 'maximized-card';
  var SELECTOR_DATA_REMOVE = '[data-card-widget="remove"]';
  var SELECTOR_DATA_COLLAPSE = '[data-card-widget="collapse"]';
  var SELECTOR_DATA_MAXIMIZE = '[data-card-widget="maximize"]';
  var SELECTOR_CARD = "." + CLASS_NAME_CARD;
  var SELECTOR_CARD_HEADER = '.card-header';
  var SELECTOR_CARD_BODY = '.card-body';
  var SELECTOR_CARD_FOOTER = '.card-footer';
  var Default$b = {
    animationSpeed: 'normal',
    collapseTrigger: SELECTOR_DATA_COLLAPSE,
    removeTrigger: SELECTOR_DATA_REMOVE,
    maximizeTrigger: SELECTOR_DATA_MAXIMIZE,
    collapseIcon: 'fa-minus',
    expandIcon: 'fa-plus',
    maximizeIcon: 'fa-expand',
    minimizeIcon: 'fa-compress'
  };

  var CardWidget = /*#__PURE__*/function () {
    function CardWidget(element, settings) {
      this._element = element;
      this._parent = element.parents(SELECTOR_CARD).first();

      if (element.hasClass(CLASS_NAME_CARD)) {
        this._parent = element;
      }

      this._settings = $__default['default'].extend({}, Default$b, settings);
    }

    var _proto = CardWidget.prototype;

    _proto.collapse = function collapse() {
      var _this = this;

      this._parent.addClass(CLASS_NAME_COLLAPSING).children(SELECTOR_CARD_BODY + ", " + SELECTOR_CARD_FOOTER).slideUp(this._settings.animationSpeed, function () {
        _this._parent.addClass(CLASS_NAME_COLLAPSED$1).removeClass(CLASS_NAME_COLLAPSING);
      });

      this._parent.find("> " + SELECTOR_CARD_HEADER + " " + this._settings.collapseTrigger + " ." + this._settings.collapseIcon).addClass(this._settings.expandIcon).removeClass(this._settings.collapseIcon);

      this._element.trigger($__default['default'].Event(EVENT_COLLAPSED$4), this._parent);
    };

    _proto.expand = function expand() {
      var _this2 = this;

      this._parent.addClass(CLASS_NAME_EXPANDING).children(SELECTOR_CARD_BODY + ", " + SELECTOR_CARD_FOOTER).slideDown(this._settings.animationSpeed, function () {
        _this2._parent.removeClass(CLASS_NAME_COLLAPSED$1).removeClass(CLASS_NAME_EXPANDING);
      });

      this._parent.find("> " + SELECTOR_CARD_HEADER + " " + this._settings.collapseTrigger + " ." + this._settings.expandIcon).addClass(this._settings.collapseIcon).removeClass(this._settings.expandIcon);

      this._element.trigger($__default['default'].Event(EVENT_EXPANDED$3), this._parent);
    };

    _proto.remove = function remove() {
      this._parent.slideUp();

      this._element.trigger($__default['default'].Event(EVENT_REMOVED$1), this._parent);
    };

    _proto.toggle = function toggle() {
      if (this._parent.hasClass(CLASS_NAME_COLLAPSED$1)) {
        this.expand();
        return;
      }

      this.collapse();
    };

    _proto.maximize = function maximize() {
      this._parent.find(this._settings.maximizeTrigger + " ." + this._settings.maximizeIcon).addClass(this._settings.minimizeIcon).removeClass(this._settings.maximizeIcon);

      this._parent.css({
        height: this._parent.height(),
        width: this._parent.width(),
        transition: 'all .15s'
      }).delay(150).queue(function () {
        var $element = $__default['default'](this);
        $element.addClass(CLASS_NAME_MAXIMIZED);
        $__default['default']('html').addClass(CLASS_NAME_MAXIMIZED);

        if ($element.hasClass(CLASS_NAME_COLLAPSED$1)) {
          $element.addClass(CLASS_NAME_WAS_COLLAPSED);
        }

        $element.dequeue();
      });

      this._element.trigger($__default['default'].Event(EVENT_MAXIMIZED), this._parent);
    };

    _proto.minimize = function minimize() {
      this._parent.find(this._settings.maximizeTrigger + " ." + this._settings.minimizeIcon).addClass(this._settings.maximizeIcon).removeClass(this._settings.minimizeIcon);

      this._parent.css('cssText', "height: " + this._parent[0].style.height + " !important; width: " + this._parent[0].style.width + " !important; transition: all .15s;").delay(10).queue(function () {
        var $element = $__default['default'](this);
        $element.removeClass(CLASS_NAME_MAXIMIZED);
        $__default['default']('html').removeClass(CLASS_NAME_MAXIMIZED);
        $element.css({
          height: 'inherit',
          width: 'inherit'
        });

        if ($element.hasClass(CLASS_NAME_WAS_COLLAPSED)) {
          $element.removeClass(CLASS_NAME_WAS_COLLAPSED);
        }

        $element.dequeue();
      });

      this._element.trigger($__default['default'].Event(EVENT_MINIMIZED), this._parent);
    };

    _proto.toggleMaximize = function toggleMaximize() {
      if (this._parent.hasClass(CLASS_NAME_MAXIMIZED)) {
        this.minimize();
        return;
      }

      this.maximize();
    } // Private
    ;

    _proto._init = function _init(card) {
      var _this3 = this;

      this._parent = card;
      $__default['default'](this).find(this._settings.collapseTrigger).click(function () {
        _this3.toggle();
      });
      $__default['default'](this).find(this._settings.maximizeTrigger).click(function () {
        _this3.toggleMaximize();
      });
      $__default['default'](this).find(this._settings.removeTrigger).click(function () {
        _this3.remove();
      });
    } // Static
    ;

    CardWidget._jQueryInterface = function _jQueryInterface(config) {
      var data = $__default['default'](this).data(DATA_KEY$d);

      var _options = $__default['default'].extend({}, Default$b, $__default['default'](this).data());

      if (!data) {
        data = new CardWidget($__default['default'](this), _options);
        $__default['default'](this).data(DATA_KEY$d, typeof config === 'string' ? data : config);
      }

      if (typeof config === 'string' && /collapse|expand|remove|toggle|maximize|minimize|toggleMaximize/.test(config)) {
        data[config]();
      } else if (typeof config === 'object') {
        data._init($__default['default'](this));
      }
    };

    return CardWidget;
  }();
  /**
   * Data API
   * ====================================================
   */


  $__default['default'](document).on('click', SELECTOR_DATA_COLLAPSE, function (event) {
    if (event) {
      event.preventDefault();
    }

    CardWidget._jQueryInterface.call($__default['default'](this), 'toggle');
  });
  $__default['default'](document).on('click', SELECTOR_DATA_REMOVE, function (event) {
    if (event) {
      event.preventDefault();
    }

    CardWidget._jQueryInterface.call($__default['default'](this), 'remove');
  });
  $__default['default'](document).on('click', SELECTOR_DATA_MAXIMIZE, function (event) {
    if (event) {
      event.preventDefault();
    }

    CardWidget._jQueryInterface.call($__default['default'](this), 'toggleMaximize');
  });
  /**
   * jQuery API
   * ====================================================
   */

  $__default['default'].fn[NAME$d] = CardWidget._jQueryInterface;
  $__default['default'].fn[NAME$d].Constructor = CardWidget;

  $__default['default'].fn[NAME$d].noConflict = function () {
    $__default['default'].fn[NAME$d] = JQUERY_NO_CONFLICT$d;
    return CardWidget._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE ControlSidebar.js
   * License MIT
   * --------------------------------------------
   */
  /**
   * Constants
   * ====================================================
   */

  var NAME$c = 'ControlSidebar';
  var DATA_KEY$c = 'lte.controlsidebar';
  var EVENT_KEY$5 = "." + DATA_KEY$c;
  var JQUERY_NO_CONFLICT$c = $__default['default'].fn[NAME$c];
  var EVENT_COLLAPSED$3 = "collapsed" + EVENT_KEY$5;
  var EVENT_EXPANDED$2 = "expanded" + EVENT_KEY$5;
  var SELECTOR_CONTROL_SIDEBAR = '.control-sidebar';
  var SELECTOR_CONTROL_SIDEBAR_CONTENT$1 = '.control-sidebar-content';
  var SELECTOR_DATA_TOGGLE$4 = '[data-widget="control-sidebar"]';
  var SELECTOR_HEADER$1 = '.main-header';
  var SELECTOR_FOOTER$1 = '.main-footer';
  var CLASS_NAME_CONTROL_SIDEBAR_ANIMATE = 'control-sidebar-animate';
  var CLASS_NAME_CONTROL_SIDEBAR_OPEN$1 = 'control-sidebar-open';
  var CLASS_NAME_CONTROL_SIDEBAR_SLIDE = 'control-sidebar-slide-open';
  var CLASS_NAME_LAYOUT_FIXED$1 = 'layout-fixed';
  var CLASS_NAME_NAVBAR_FIXED = 'layout-navbar-fixed';
  var CLASS_NAME_NAVBAR_SM_FIXED = 'layout-sm-navbar-fixed';
  var CLASS_NAME_NAVBAR_MD_FIXED = 'layout-md-navbar-fixed';
  var CLASS_NAME_NAVBAR_LG_FIXED = 'layout-lg-navbar-fixed';
  var CLASS_NAME_NAVBAR_XL_FIXED = 'layout-xl-navbar-fixed';
  var CLASS_NAME_FOOTER_FIXED = 'layout-footer-fixed';
  var CLASS_NAME_FOOTER_SM_FIXED = 'layout-sm-footer-fixed';
  var CLASS_NAME_FOOTER_MD_FIXED = 'layout-md-footer-fixed';
  var CLASS_NAME_FOOTER_LG_FIXED = 'layout-lg-footer-fixed';
  var CLASS_NAME_FOOTER_XL_FIXED = 'layout-xl-footer-fixed';
  var Default$a = {
    controlsidebarSlide: true,
    scrollbarTheme: 'os-theme-light',
    scrollbarAutoHide: 'l',
    target: SELECTOR_CONTROL_SIDEBAR
  };
  /**
   * Class Definition
   * ====================================================
   */

  var ControlSidebar = /*#__PURE__*/function () {
    function ControlSidebar(element, config) {
      this._element = element;
      this._config = config;
    } // Public


    var _proto = ControlSidebar.prototype;

    _proto.collapse = function collapse() {
      var $body = $__default['default']('body');
      var $html = $__default['default']('html');
      var target = this._config.target; // Show the control sidebar

      if (this._config.controlsidebarSlide) {
        $html.addClass(CLASS_NAME_CONTROL_SIDEBAR_ANIMATE);
        $body.removeClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE).delay(300).queue(function () {
          $__default['default'](target).hide();
          $html.removeClass(CLASS_NAME_CONTROL_SIDEBAR_ANIMATE);
          $__default['default'](this).dequeue();
        });
      } else {
        $body.removeClass(C