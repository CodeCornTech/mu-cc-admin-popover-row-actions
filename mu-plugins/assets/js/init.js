// @ts-nocheck
/* global jQuery, CC_APRA_NS */
(function ($, NS) {
  if (!NS || NS.mounted) return;

  $(function () {
    var opts = $.extend(true, {
      enabled: true,
      onHover: true,
      hideQuickEdit: true,
      selectors: {
        table: '.wp-list-table.table-view-list',
        primaryCell: 'td.column-primary',
        triggerLink: 'a.wp_ar_id_td, .wp_ar-badge',
        rowActions: '.row-actions'
      }
    }, NS.options || {});

    if (!opts.enabled) return;

    var $table = $(opts.selectors.table);
    if (!$table.length) return;

    // prebuild esistenti
    $table.find(opts.selectors.primaryCell).each(function () {
      NS.core.buildPopover($(this), opts);
    });

    // toggle su click
    $table.on('click', opts.selectors.primaryCell + ' .cc-popover-trigger', function (e) {
      e.preventDefault(); e.stopPropagation();
      NS.core.togglePopover($(this).closest('td'), undefined, opts);
    });

    // on hover opzionale
    if (opts.onHover === true) {
      $table.on('mouseenter', opts.selectors.primaryCell + ' .cc-popover-trigger', function () {
        NS.core.togglePopover($(this).closest('td'), true, opts);
      }).on('mouseleave', 'tr', function () {
        var $td = $(this).find(opts.selectors.primaryCell).first();
        if ($td.length) NS.core.togglePopover($td, false, opts);
      });
    }

    // chiudi su click fuori
    $(document).on('click', function (e) {
      if ($(e.target).closest('.cc-popover, .cc-popover-trigger').length) return;
      $('.cc-popover[data-open="true"]').each(function () {
        var $pop = $(this);
        $pop.attr('data-open', 'false');
        $pop.closest('td').find('.cc-popover-trigger').attr('aria-expanded', 'false');
      });
    });

    // chiudi su ESC
    $(document).on('keydown', function (e) {
      if (e.key === 'Escape') {
        $('.cc-popover[data-open="true"]').each(function () {
          var $pop = $(this);
          $pop.attr('data-open', 'false');
          $pop.closest('td').find('.cc-popover-trigger').attr('aria-expanded', 'false').focus();
        });
      }
    });

    NS.mounted = true;
  });

})(jQuery, window.CC_APRA_NS);