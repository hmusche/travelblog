jQuery(document).ready(function() {
    var geoGroup = document.getElement('.group-geo');

    if (geoGroup) {
        var location = geoGroup.getElements('input[data-geo]').get('value');
        geoGroup.getElements('.form-group').addClass('d-none');

        if (location && location[0].length === 0) {
            location = [];
        }

        var mapWrapper = new Element('div', {
            'class': 'geo-map',
        });

        mapWrapper.inject(geoGroup);

        var geo = new Geo(mapWrapper, location);

        var setLocation = function() {
            var input;

            Object.forEach(geo.getLocation(), function(value, key) {
                input = geoGroup.getElement('input[data-geo=' + key + ']');

                if (input) {
                    input.set({'value' : value});
                }
            });
        }

        geo.setCallback('markerDragEnd', setLocation);

        window.trgeo = geo;
    }
});

jQuery('.table-hover tr').click(function() {
    var href = jQuery(this).attr('data-href');

    if (href) {
        location.href = href;
    }

});

jQuery('.file-preview .image-wrapper .sort-order').change(function(event) {
    var el = jQuery(this).parents('.image-wrapper'),
        file = el.attr('data-file'),
        postId = el.attr('data-post-id'),
        sort = jQuery(this).val();

        console.log(el);

    event.preventDefault();

    jQuery.ajax('<?php echo $this->webhost; ?>admin/sort-post-media/', {
        'method': 'post',
        'data': {
            file: file,
            post_id: postId,
            sort: sort
        },
        success: function(res) {

        }
    })
});

jQuery('.file-preview .image-wrapper .delete-file').click(function(event) {
    event.preventDefault();
    var el = jQuery(this).parent('.image-wrapper'),
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
                if (res.status == 'success') {
                    el.remove();
                }
            }
        });
    }
});
