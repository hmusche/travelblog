jQuery(document).ready(function() {

    var wrapper = document.getElementById('map'),
        boundaries = eval(wrapper.get('data-boundaries')),
        posts = eval(wrapper.get('data-posts')),
        i, content;

    wrapper.setStyle('height', window.innerHeight - 100 + 'px');

    var geo = new Geo(wrapper);
    geo.setBounds(boundaries);

    window.trgeo = geo;

    for (i = 0; i < posts.length; i++) {
        if (posts[i]['link']) {
            content = '<a href="' + '<?php echo $this->webhost; ?>' + posts[i]['link'] + '">'
                    + posts[i]['heading']
                    + '</a>';
        } else {
            content = '<h6>' + posts[i]['heading'] + '</h6>';
        }

        geo.setMarker([posts[i]['longitude'], posts[i]['latitude']], content);
    }
});
