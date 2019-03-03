jQuery(document).ready(function() {

    var wrapper = document.getElementById('map'),
        boundaries = eval(wrapper.get('data-boundaries')),
        geo = new Geo(wrapper);

        geo.setBounds(boundaries);

        var getPosts = function() {
            jQuery.ajax({
                'url': '<?php echo $this->webhost; ?>map/posts',
                'data': {
                    'bounds': boundaries
                },
                success: function(result) {
                    console.log(result);
                }
            });
        }

        getPosts();

    window.trgeo = geo;
});
