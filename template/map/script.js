jQuery(document).ready(function() {

    var wrapper = document.getElementById('map'),
        boundaries = eval(wrapper.get('data-boundaries')),
        i, content;

    wrapper.setStyle('height', window.innerHeight - 100 + 'px');

    var geo = new Geo(wrapper);
    geo.setBounds(boundaries);

    window.trgeo = geo;

    var coordinates = [],
        route = {
        id: 'route',
        type: 'line',
        source: {
            type: 'geojson',
            data: {
                type: 'Feature',
                properties: {},
                geometry: {
                    type: "LineString",
                    coordinates: []
                }
            }
        },
        "layout": {
            "line-join": "round",
            "line-cap": "round"
        },
        "paint": {
            "line-color": "#888",
            "line-width": 8
        }
    };

    for (i = 0; i < markers.length; i++) {
        if (markers[i]['link']) {
            content = '<a href="' + '<?php echo $this->webhost; ?>' + markers[i]['link'] + '">'
                    + markers[i]['heading']
                    + '</a>';
        } else {
            content = '<h6>' + markers[i]['heading'] + '</h6>';
        }

        markers[i].content = content;
        coordinates.push([markers[i]['longitude'], markers[i]['latitude']]);
    }

    route.source.data.geometry.coordinates = coordinates;

    // @todo: add Route layer
    //geo.addLayer(route);

    markers.sort(function(a, b) {
        if (Number.parseFloat(a.latitude) < Number.parseFloat(b.latitude)) {
            return 1;
        }

        return -1;
    });

    for (i = 0; i < markers.length; i++) {
        geo.setMarker([markers[i]['longitude'], markers[i]['latitude']], markers[i]['content']);
    }
});
