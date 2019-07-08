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

        var setLocation = function() {
            var input;

            Object.forEach(geo.getLocation(), function(value, key) {
                input = geoGroup.getElement('input[data-geo=' + key + ']');

                if (input) {
                    input.set({'value' : value});
                }
            });
        }

        var geo = new Geo(mapWrapper);

        geo.setDragMarker();
        geo.setLocation(location);
        geo.setCallback('markerDragEnd', setLocation);
        geo.setCallback('load', setLocation);

        window.trgeo = geo;
    }
});

jQuery('.table-hover tr').click(function() {
    var href = jQuery(this).attr('data-href');

    if (href) {
        location.href = href;
    }

});

var changeTimeout;

jQuery('.file-preview .image-wrapper .meta-data').change(function(event) {
    var el = jQuery(this).parents('.image-wrapper'),
        file = el.attr('data-file'),
        postId = el.attr('data-post-id'),
        sort = el.find('.sort-order').val(),
        subtitle = el.find('.media-subtitle').val();

    event.preventDefault();

    clearTimeout(changeTimeout);

    changeTimeout = setTimeout(function() {
        jQuery.ajax('<?php echo $this->webhost; ?>admin/post-media-meta/', {
            'method': 'post',
            'data': {
                file: file,
                post_id: postId,
                sort: sort,
                subtitle: subtitle
            },
            success: function(res) {

            }
        });
    }, 300);
});

document.getElements('.btn.sort').addEvent('click', function(e) {
    e.preventDefault();

    var wrapper = this.getParent('.image-wrapper'),
        previous = wrapper.getPrevious(),
        next = wrapper.getNext(),
        replacement, where,
        data = {
            post_id: wrapper.get('data-post-id'),
            file: wrapper.get('data-file'),
            current: wrapper.get('data-sort')
        };

    if (this.hasClass('sort-up') && previous) {
        data.new = previous.get('data-sort');
        replacement = previous;
        where = 'after';
    } else if (this.hasClass('sort-down') && next) {
        data.new = next.get('data-sort');
        replacement = next;
        where = 'before';
    } else {
        return;
    }

    jQuery.ajax('<?php echo $this->webhost; ?>admin/sort-post-media/', {
        'method': 'post',
        'data': data,
        'success': function(response) {

            if (response.status == 'success') {
                wrapper.grab(replacement, where);

                wrapper.set('data-sort', data.new);
                replacement.set('data-sort', data.current);
            }
        }
    })
});

jQuery('.file-preview .image-wrapper .delete-file').click(function(event) {
    event.preventDefault();
    var el = this.getParent('.image-wrapper'),
        file = el.get('data-file'),
        postId = el.get('data-post-id');

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
