var Gallery = new Class({
    initialize: function(gallery) {
        this.gallery = gallery;
        this.imageContainer = gallery.getElement('.image-wrapper');
        this.galleryWrapper = gallery.getElement('.gallery-wrapper');
        this.controls = gallery.getElement('.gallery-controls');
        this.offset = 0;
        this.setImages();
    },

    setImages: function() {
        var self = this;

        this.images = this.galleryWrapper.getElements('.gallery-image-wrapper');
        this.currentIndex = 0;
        this.loadedImages = 0;
        this.maxRatio     = 100;

        this.images.each(function(div) {
            if (!div.retrieve('image-loaded')) {
                new Element('img', {
                    'src': div.get('data-src'),
                    'events': {
                        'load': function() {
                            self.loadedImages++;

                            div.setStyle('background-image', 'url(' + div.get('data-src') + ')')
                            this.remove();

                            if ((this.height / this.width) < self.maxRatio) {
                                self.maxRatio = (this.height / this.width);
                            }

                            if (self.loadedImages == self.images.length) {
                                self.initGallery();
                            }
                        }
                    }
                });
            }
        });
    },

    initGallery: function() {
        this.setSizes();
        this.initEvents();
        this.toggleEasing(true);
    },

    setSizes: function() {
        var self = this,
            width = this.gallery.clientWidth,
            height = width * this.maxRatio;

        if (this.gallery.hasClass('fullscreen')) {
            height = this.gallery.clientHeight;
        }

        this.gallery.setStyle('height', height + 'px');
        this.toggleEasing(false);

        /**
         * Set all images width to maximum of gallery wrapper
         */
        this.images.each(function(image) {
            image.setStyle('height', height + 'px');
            image.setStyle('width', width + 'px');
        });

        self.galleryWrapper.setStyle('width', width * (this.images.length + 1) + 'px');

        this.showImage();
        this.toggleEasing(true);
    },

    initEvents: function() {
        var self = this,
            current, initial, max, min;

        this.gallery.addEvents({
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

        this.controls.getElements('.gallery-fullscreen-button').addEvent('click', function() {
            self.toggleFullscreen();
        });

        this.controls.getElements('.gallery-left,.gallery-right').addEvent('click', function(e) {
            var direction = this.hasClass('gallery-left');

            if (direction) {
                self.showImage(self.currentIndex - 1);
            } else {
                self.showImage(self.currentIndex + 1);
            }
        });

        document.addEvent('keyup', function(event) {
            switch (event.key) {
                case 'right':
                    self.showImage(self.currentIndex + 1);
                    break;

                case 'left':
                    self.showImage(self.currentIndex - 1);
                    break;

                case 'f':
                    self.toggleFullscreen();
                    break;

                case 'esc':
                    self.toggleFullscreen(false);
                    break;
            }
        });

        window.addEvent('resize', function() {
            self.setSizes();
        });
                                                                                                                                                                                                                                                ;
        this.controls.addClass('done');
    },

    toggleFullscreen: function(toggle) {
        this.toggleEasing(false);

        if (typeof toggle == 'undefined') {
            toggle = !this.gallery.hasClass('fullscreen');
        }

        if (toggle) {
            this.gallery.addClass('fullscreen');
            /*
            this.controls.getElements('.gallery-fullscreen-button>i')
                         .removeClass('fa-arrows-alt')
                         .addClass('fa-compress-arrows-alt');
                         */
        } else {
            this.gallery.removeClass('fullscreen');
            /*
            this.controls.getElements('.gallery-fullscreen-button>i')
                         .removeClass('fa-compress-arrows-alt')
                         .addClass('fa-arrows-alt');
                         */
        }

        this.setSizes();
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

        var wrapper = this.images[index],
            maxOffset = this.galleryWrapper.clientWidth - this.gallery.clientWidth;

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
