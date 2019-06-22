var Gallery = new Class({
    initialize: function(gallery) {
        this.gallery = gallery;
        this.imageContainer = gallery.getElement('.image-wrapper');
        this.galleryWrapper = gallery.getElement('.gallery-wrapper');
        this.controls = gallery.getElement('.gallery-controls');
        this.playvideo = this.controls.getElement('.gallery-playvideo-button');
        this.offset = 0;
        this.currentIndex = 0;
        this.setImages();
        this.cookie = new Cookie();
    },

    setImages: function(currentSize) {
        var self = this,
            sizes = {
                'xl': 1920,
                'lg': 1200,
                'md': 900,
                'sm': 600,
                'xs': 300,
            };

        if (!currentSize) {
            Object.each(sizes, function(pixel, size) {
                if (self.gallery.clientWidth < pixel) {
                    currentSize = size;
                }
            });
        }

        this.images = this.galleryWrapper.getElements('.gallery-image-wrapper');
        this.loadedImages = 0;
        this.maxRatio     = 100;

        this.images.each(function(div) {
            var imgSrc = div.get('data-src').replace('{size}', currentSize),
                subtitle = div.get('data-subtitle'),
                filetype = div.get('data-filetype');

            if (filetype.indexOf('image') === 0) {
                new Element('img', {
                    'src': imgSrc,
                    'events': {
                        'load': function() {
                            self.loadedImages++;

                            div.setStyle('background-image', 'url(' + imgSrc + ')')
                            this.remove();

                            if (subtitle) {
                                new Element('div', {
                                    'class': 'subtitle',
                                    'text' : subtitle
                                }).inject(div);
                            }

                            if ((this.height / this.width) < self.maxRatio) {
                                self.maxRatio = (this.height / this.width);
                            }

                            if (self.loadedImages == self.images.length) {
                                self.initGallery();
                            }
                        }
                    }
                });
            } else if (filetype.indexOf('video') === 0) {
                var vid = new Element('video', {
                    'src': imgSrc,
                    'controls': false
                });

                vid.inject(div);

                vid.addEventListener && vid.addEventListener('ended', function() {
                    self.toggleVideo(true);
                });

                self.loadedImages++;
            }
        });
    },

    initGallery: function() {
        var self = this,
            swipeHintCount = this.cookie.get('swipehint', 0);

        this.setSizes();
        this.initEvents();
        this.toggleEasing(true);

        if (this.gallery.getElement('.loader')) {
            this.gallery.getElement('.loader').remove();
        }

        if (swipeHintCount < 5 && this.images.length > 1) {
            setTimeout(function() {
                self.galleryWrapper.setStyle('margin-left', '-50px');

                setTimeout(function() {
                    self.galleryWrapper.setStyle('margin-left', '0');
                }, 300);

                self.cookie.set('swipehint', ++swipeHintCount);
            }, 500);
        }
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

        if (!this.controls.hasClass('done')) {
            this.gallery.store('events-added', true);

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
                        self.toggleVideo(true);
                        self.showImage(self.currentIndex + 1);
                    } else if (current <= min) {
                        self.toggleVideo(true);
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

            this.controls.addEvent('click', function(e) {
                if (e.target == this || e.target == self.playvideo || e.target.getParent() == self.playvideo) {
                    // center area clicked, check if video and pause/play
                    self.toggleVideo();
                }
            });

            this.controls.getElements('.gallery-left,.gallery-right').addEvent('click', function(e) {
                var direction = this.hasClass('gallery-left');

                self.toggleVideo(true);

                if (direction) {
                    self.showImage(self.currentIndex - 1);
                } else {
                    self.showImage(self.currentIndex + 1);
                }
            });

            document.addEvent('keyup', function(event) {
                switch (event.key) {
                    case 'right':
                        self.toggleVideo(true);
                        self.showImage(self.currentIndex + 1);
                        break;

                    case 'left':
                        self.toggleVideo(true);
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
        }
    },

    toggleFullscreen: function(toggle) {
        this.toggleEasing(false);

        if (typeof toggle == 'undefined') {
            toggle = !this.gallery.hasClass('fullscreen');
        }

        if (toggle) {
            this.gallery.addClass('fullscreen');

            if (!this.fullscreenDone) {
                // set Images to max size for best quality, no matter what the screen size
                this.setImages('xl');
                this.fullscreenDone = true;
            }
        } else {
            this.gallery.removeClass('fullscreen');
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
        this.playvideo.addClass('d-none');

        if (wrapper.getElement('video')) {
            this.playvideo.removeClass('d-none');
        }

        wrapper.addClass('active');

        this.controls.getElements('div').addClass('active');

        if (this.currentIndex === 0) {
            this.controls.getElements('div')[0].removeClass('active');
        }

        if (this.currentIndex == (this.images.length - 1) || wrapper.offsetLeft >= maxOffset) {
            this.controls.getElements('div')[1].removeClass('active');
        }
    },

    toggleVideo: function(forceStop) {
        var vidEl = this.images[this.currentIndex].getElement('video'),
            forceStop = forceStop || false;

        if (vidEl) {
            if (vidEl.retrieve('playing') || forceStop) {
                this.playvideo.removeClass('d-none');
                vidEl.store('playing', false);
                vidEl.pause();
            } else {
                this.playvideo.addClass('d-none');
                vidEl.store('playing', true);
                vidEl.play();
            }
        }
    }
});
