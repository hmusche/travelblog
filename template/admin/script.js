jQuery('.table-hover tr').click(function() {
    var href = jQuery(this).attr('data-href');

    if (href) {
        location.href = href;
    }

});
