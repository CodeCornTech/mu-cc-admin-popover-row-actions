// @ts-nocheck
/* global jQuery, CC_APRA_NS */
(function ($, NS) {
  if (!NS) return;

  function buildPopover($td, opts) {
    if (!$td || !$td.length) return null;
    if ($td.data('ccPopover')) return $td.data('ccPopover');

    // trigger preferito : link con classe .wp_ar_id_td , poi fallback badge
    var $trigger = $td.find(opts.selectors.triggerLink).first();
    if (!$trigger.length) return null;

    $trigger.addClass('cc-popover-trigger')
            .attr({ 'aria-haspopup': 'true', 'aria-expanded': 'false' });

    // clona row-actions della cella
    var $rowActions = $td.children(opts.selectors.rowActions).first().clone(true, true);
    if (!$rowActions.length) return null;

    // opzionale : nascondi Modifica rapida
    if (opts.hideQuickEdit) {
      $rowActions.find('button.editinline').closest('span.inline').remove();
      $rowActions.find('a.editinline').closest('span').remove();
      $rowActions.find('span.inline:empty').remove();
    }

    // costruzione popover
    var $pop = $('<div class="cc-popover" role="dialog" aria-label="Azioni riga"></div>');
    var $wrap = $('<div class="cc-actions"></div>');
    $wrap.append($rowActions.find('a, button'));
    $pop.append($wrap);

    // mount
    $td.css('position','relative').append($pop);
    $td.data('ccPopover', $pop);
    return $pop;
  }

  function togglePopover($td, force, opts) {
    var $pop = buildPopover($td, opts);
    if (!$pop) return;
    var open = force !== undefined ? !!force : $pop.attr('data-open') !== 'true';
    $pop.attr('data-open', open ? 'true' : 'false');

    var $trigger = $td.find('.cc-popover-trigger').first();
    $trigger.attr('aria-expanded', open ? 'true' : 'false');

    if (open) {
      if (window.matchMedia('(min-width: 783px)').matches) {
        var rect = $trigger[0].getBoundingClientRect();
        var tdRect = $td[0].getBoundingClientRect();
        var left = rect.left - tdRect.left;
        $pop.css({ left: Math.max(left, 0) + 'px', top: ($trigger.outerHeight() + 6) + 'px' });
      }
      setTimeout(function () {
        var el = $pop.find('a,button').get(0);
        if (el) el.focus({ preventScroll: true });
      }, 0);
    }
  }

  // Public API nel namespace
  NS.core = {
    buildPopover: buildPopover,
    togglePopover: togglePopover
  };

})(jQuery, window.CC_APRA_NS);