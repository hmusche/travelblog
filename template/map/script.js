jQuery(document).ready(function() {

    var wrapper = document.getElementById('map'),
        boundaries = eval(wrapper.get('data-boundaries')),
        i, content;

    wrapper.setStyle('height', window.innerHeight - 100 + 'px');

    var geo = new Geo(wrapper);
    geo.setBounds(boundaries);

    window.trgeo = geo;

    for (i = 0; i < markers.length; i++) {
        if (markers[i]['link']) {
            content = '<a href="' + '<?php echo $this->webhost; ?>' + markers[i]['link'] + '">'
                    + markers[i]['heading']
                    + '</a>';
        } else {
            content = '<h6>' + markers[i]['heading'] + '</h6>';
        }

        geo.setMarker([markers[i]['longitude'], markers[i]['latitude']], content);
    }
});
