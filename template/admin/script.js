jQuery('.table-hover tr').click(function() {
    var href = jQuery(this).attr('data-href');

    if (href) {
        location.href = href;
    }

});

jQuery('.file-preview .image-wrapper .delete-file').click(function(event) {
    event.preventDefault();
    var el = jQuery(this),
        file = el.attr('data-file'),
        postId = el.attr('data-post-id');

    if (confirm('<?php echo $this->t('really.delete'); ?>')) {
        jQuery.ajax('<?php echo $this->webhost; ?>admin/delete-post-media/', {
            'method': 'post',
            'data': {
                file: file,
                post_id: postId
            },
            'success': function(res) {
                console.log(res);
            }
        });
    }
});
