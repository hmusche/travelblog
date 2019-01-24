var Gallery = new Class({
    initialize: function(gallery) {
        this.imageContainer = gallery.getElement('.image-wrapper');
        this.galleryWrapper = gallery.getElement('.gallery-wrapper');
        this.controls = gallery.getElement('.gallery-controls');
        this.offset = 0;
        this.setImages();
    },

    setImages: function() {
        var self = this;

        this.images = this.galleryWrapper.getElements('img');
        this.currentIndex = 0;
        this.loadedImages = 0;

        this.images.each(function(img) {
            if (!img.get('src')) {
                img.addEvent('load', function() {
                    self.loadedImages++;

                    if (self.loadedImages == self.images.length) {
                        self.initGallery();
                    }
                });

                img.set('src', img.get('data-src'));
            }
        });
    },

    setSizes: function() {
        var self = this,
            galleryWidth = 0,
            minHeight = 10000,
            maxWidth = self.galleryWrapper.getParent().clientWidth;

        this.toggleEasing(false);

        /**
         * Set all images width to maximum of gallery wrapper
         */
        this.images.each(function(image) {
            image.setStyle('height', 'auto');
            image.setStyle('max-width', maxWidth + 'px');
            image.getParent('div').setStyle('width', maxWidth + 'px');
        });

        /**
         * Get the minimum height from the image with the lowest height
         */
        this.images.each(function(image) {
            if (image.clientHeight < minHeight) {
                minHeight = image.clientHeight;
            }
        });


        /**
         * Set that height to every image, and set gallery width to that
         */
        this.images.each(function(image) {
            image.setStyle('height', minHeight + 'px');

            galleryWidth = galleryWidth + image.getParent('div').clientWidth;
        });
        galleryWidth = maxWidth * (this.images.length + 1);
        self.galleryWrapper.setStyle('width', galleryWidth + 10 + 'px');
        self.galleryWrapper.getParent().setStyle('height', minHeight + 'px');

        this.toggleEasing(true);
        this.showImage();
    },

    initGallery: function() {
        this.setSizes();
        this.initEvents();
        this.toggleEasing(true);
    },

    initEvents: function() {
        var self = this,
            current, initial, max, min;

        this.galleryWrapper.getParent('.solsken-gallery').addEvents({
            'touchstart': function(event) {
                var elem = this;

                self.toggleEasing(false);

                current = max = min = 0;

                initial = event.client.x;

                this.store('was_touched', true);

                timeout = setTimeout(function() {
                    elem.eliminate('was_touched');
                }, 400);
            },
            'touchmove': function(event) {
                current = initial - event.client.x;

                if (max < current) {
                    max = current;
                }

                if (min > current) {
                    min = current;
                }

                if (self.currentIndex == 0 && current < 0) {
                    current = 0;
                    initial = event.client.x;
                } else if (self.currentIndex == (self.images.length - 1) && current > 0) {
                    current = 0;
                    initial = event.client.x;
                }

                self.galleryWrapper.setStyle('transform', 'translate(' + (-1 * (self.offset + current)) + 'px)');
            },
            'touchend': function(event) {
                self.toggleEasing(true);

                if (Math.abs(current) < 20) {
                    return;
                }

                if (current >= max) {
                    self.showImage(self.currentIndex + 1);
                } else if (current <= min) {
                    self.showImage(self.currentIndex - 1);
                } else {
                    self.showImage();
                }
            },
            mouseover: function() {
                if (!this.retrieve('was_touched')) {
                    self.controls.addClass('has-pointer');
                }
            }
        });

        this.controls.getElements('div').addEvent('click', function(e) {
            var direction = this.hasClass('gallery-left');

            if (direction) {
                self.showImage(self.currentIndex - 1);
            } else {
                self.showImage(self.currentIndex + 1);
            }
        });

        window.addEvent('resize', function() {
            self.setSizes();
        })

        this.controls.addClass('done');
    },

    toggleEasing: function(on) {
        if (on) {
            this.galleryWrapper.addClass('easing');
        } else {
            this.galleryWrapper.removeClass('easing');
        }

    },

    showImage: function(index) {
        if (typeof index == 'undefined') {
            index = this.currentIndex;
        }

        if (!this.images[index]) {
            return;
        }

        var wrapper = this.images[index].getParent('.gallery-image-wrapper'),
            maxOffset = this.galleryWrapper.clientWidth - this.galleryWrapper.getParent('.solsken-gallery').clientWidth;

        if (wrapper.offsetLeft >= maxOffset) {
            this.offset = maxOffset;
        } else {
            this.offset = wrapper.offsetLeft;
        }

        this.currentIndex = index;

        this.galleryWrapper.setStyle('transform', 'translate(' + (-1 * this.offset) + 'px)');
        this.galleryWrapper.getElements('.gallery-image-wrapper').removeClass('active');
        wrapper.addClass('active');

        this.controls.getElements('div').addClass('active');

        if (this.currentIndex === 0) {
            this.controls.getElements('div')[0].removeClass('active');
        }

        if (this.currentIndex == (this.images.length - 1) || wrapper.offsetLeft >= maxOffset) {
            this.controls.getElements('div')[1].removeClass('active');
        }
    }
});
