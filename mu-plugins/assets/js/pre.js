// @ts-nocheck
/* global jQuery, CC_APRA */
(function ($) {
  // Singleton guard + noop se mancano dipendenze
  if (!window || !document || !$.fn) return;

  // Namespace globale per coordinare i moduli
  window.CC_APRA_NS = window.CC_APRA_NS || {
    version: '1.0.0',
    mounted: false,
    options: CC_APRA || {},
    // utility
    inArray: function (v, arr) { return Array.isArray(arr) && arr.indexOf(v) !== -1; },
  };
})(jQuery);