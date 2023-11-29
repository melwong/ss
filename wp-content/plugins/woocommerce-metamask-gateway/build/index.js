!(function (e) {
  var t = {};
  function n(r) {
    if (t[r]) return t[r].exports;
    var o = (t[r] = { i: r, l: !1, exports: {} });
    return e[r].call(o.exports, o, o.exports, n), (o.l = !0), o.exports;
  }
  (n.m = e),
    (n.c = t),
    (n.d = function (e, t, r) {
      n.o(e, t) || Object.defineProperty(e, t, { enumerable: !0, get: r });
    }),
    (n.r = function (e) {
      "undefined" != typeof Symbol &&
        Symbol.toStringTag &&
        Object.defineProperty(e, Symbol.toStringTag, { value: "Module" }),
        Object.defineProperty(e, "__esModule", { value: !0 });
    }),
    (n.t = function (e, t) {
      if ((1 & t && (e = n(e)), 8 & t)) return e;
      if (4 & t && "object" == typeof e && e && e.__esModule) return e;
      var r = Object.create(null);
      if (
        (n.r(r),
        Object.defineProperty(r, "default", { enumerable: !0, value: e }),
        2 & t && "string" != typeof e)
      )
        for (var o in e)
          n.d(
            r,
            o,
            function (t) {
              return e[t];
            }.bind(null, o)
          );
      return r;
    }),
    (n.n = function (e) {
      var t =
        e && e.__esModule
          ? function () {
              return e.default;
            }
          : function () {
              return e;
            };
      return n.d(t, "a", t), t;
    }),
    (n.o = function (e, t) {
      return Object.prototype.hasOwnProperty.call(e, t);
    }),
    (n.p = ""),
    n((n.s = 5));
})([
  function (e, t) {
    e.exports = window.wp.element;
  },
  function (e, t) {
    e.exports = window.wp.htmlEntities;
  },
  function (e, t) {
    e.exports = window.wp.i18n;
  },
  function (e, t) {
    e.exports = window.wc.wcBlocksRegistry;
  },
  function (e, t) {
    e.exports = window.wc.wcSettings;
  },
  function (e, t, n) {
    "use strict";
    n.r(t);
    var r,
      o,
      i = n(0),
      u = n(1),
      a = n(2),
      l = n(3),
      c = n(4),
      f = function () {
        var e = Object(c.getSetting)("metamask_data", null);
        if (!e)
          throw new Error("MetaMask initialization data is not available");
        return e;
      },
      d = function () {
        var e;
        return Object(u.decodeEntities)(
          (null === (e = f()) || void 0 === e ? void 0 : e.description) || ""
        );
      };
    Object(l.registerPaymentMethod)({
      name: "metamask",
      label: Object(i.createElement)(function () {
        var e, t;
        return Object(i.createElement)("img", {
          src: null === (e = f()) || void 0 === e ? void 0 : e.logo_url,
          alt: null === (t = f()) || void 0 === t ? void 0 : t.title,
        });
      }, null),
      ariaLabel: Object(a.__)(
        "MetaMask payment method",
        "woocommerce-gateway-metamask"
      ),
      canMakePayment: function () {
        return !0;
      },
      content: Object(i.createElement)(d, null),
      edit: Object(i.createElement)(d, null),
      supports: {
        features:
          null !==
            (r = null === (o = f()) || void 0 === o ? void 0 : o.supports) &&
          void 0 !== r
            ? r
            : [],
      },
    });
  },
]);
