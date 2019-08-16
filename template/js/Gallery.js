var Gallery = new Class({
    initialize: function(gallery) {
        this.gallery = gallery;
        this.imageContainer = gallery.getElement('.image-wrapper');
        this.galleryWrapper = gallery.getElement('.gallery-wrapper');
        this.controls = gallery.getElement('.gallery-controls');
        this.playvideo = this.controls.getElement('.gallery-playvideo-button');
        this.offset = 0;
        this.currentIndex = 0;
        this.maxRatio     = 3;
        this.getFullscreenPrefixes();
        this.setImages();
        this.cookie = new Cookie();
    },

    setImages: function(currentSize) {
        var i = 0,
            self = this,
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

        self.setSizes(true);

        this.images.each(function(div) {
            var imgSrc = div.get('data-src').replace('{size}', currentSize),
                subtitle = div.get('data-subtitle'),
                filetype = div.get('data-filetype');

            if (!self.controls.hasClass('done')) {
                new Element('div', {
                    'class': 'gallery-bullet',
                    'data-index': i
                }).inject(self.controls.getElement('.gallery-bullets'));

                i++;
            }

            if (filetype.indexOf('image') === 0) {
                div.getElement('.loader') && div.getElement('.loader').removeClass('d-none');

                new Element('img', {
                    'src': imgSrc,
                    'events': {
                        'load': function() {
                            self.loadedImages++;

                            div.setStyle('background-image', 'url(' + imgSrc + ')')
                            div.getElement('.loader') && div.getElement('.loader').remove();
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

                            self.setSizes();
                        }
                    }
                });
            } else if (filetype.indexOf('video') === 0 && !div.hasClass('video-done')) {
                var vid = new Element('video', {
                    'src': imgSrc,
                    'controls': false
                });

                var progressWrapper = new Element('div', {
                    'class': 'progress-wrapper'
                });

                var progress = new Element('div', {
                    'class': 'progress'
                });

                progress.inject(progressWrapper);

                if (vid.addEventListener) {
                    vid.addEventListener('canplay', function() {
                        div.getElement('.loader') && div.getElement('.loader').addClass('d-none');

                        if (!vid.retrieve('playing')) {
                            self.playvideo.removeClass('d-none');
                        }
                    });

                    vid.addEventListener('waiting', function() {
                        div.getElement('.loader') && div.getElement('.loader').removeClass('d-none');

                    });

                    vid.addEventListener('playing', function() {
                        div.getElement('.loader') && div.getElement('.loader').addClass('d-none');
                    });

                    vid.addEventListener('ended', function() {
                        self.toggleVideo(true);
                    });

                    vid.addEventListener('timeupdate', function(event) {
                        if (this.duration && this.currentTime) {
                            var ratio = this.currentTime / this.duration * 100,
                                progress = this.getPrevious().getElement('.progress');

                            progress.setStyle('width', ratio + '%');
                        }

                    });
                }

                self.loadedImages++;
                progressWrapper.inject(div);
                vid.inject(div);

                if (subtitle) {
                    new Element('div', {
                        'class': 'subtitle',
                        'text' : subtitle
                    }).inject(div);
                }

                div.addClass('video-done');
            }

        });

        this.initEvents();
        this.toggleEasing(true);
    },

    setSizes: function(initial) {
        var self = this,
            width = this.gallery.clientWidth,
            height = width * this.maxRatio;

        if (initial) {
            // On initial run, set ratio to 2:1
            height = width * 0.5;
        }

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
            var touchCount = 0,
                isPinch = false;

            this.gallery.store('events-added', true);

            this.gallery.addEvents({
                'touchstart': function(event) {
                    var elem = this;

                    touchCount++;

                    isPinch = (touchCount !== 1);

                    /**
                     * If more than one touch occurred, the user apparently tries to zoom
                     */
                    if (touchCount > 1) {
                        return;
                    }

                    self.toggleEasing(false);

                    current = max = min = 0;
                    initial = event.client.x;

                    /**
                     * Set a touch flag for 400ms, because mouseover event is fired as well in some occasions
                     */
                    this.store('was_touched', true);

                    timeout = setTimeout(function() {
                        elem.eliminate('was_touched');
                    }, 400);
                },
                'touchmove': function(event) {
                    /**
                     * If more than one touch occurred, the user apparently tries to zoom
                     */
                    if (touchCount > 1) {
                        self.showImage();
                        return;
                    }

                    if (!isPinch) {
                        event && event.preventDefault();

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
                    }
                },
                'touchend': function(event) {
                    self.toggleEasing(true);

                    touchCount--;

                    if (touchCount > 0) {
                        return;
                    }

                    if (Math.abs(current) < 20) {
                        self.showImage();
                        return;
                    }

                    if (!isPinch) {
                        if (current >= max) {
                            self.toggleVideo(true);
                            self.showImage(self.currentIndex + 1);
                        } else if (current <= min) {
                            self.toggleVideo(true);
                            self.showImage(self.currentIndex - 1);
                        } else {
                            self.showImage();
                        }
                    }

                    isPinch = (touchCount === 0);
                },
                'contextmenu': function() {
                    // in case user taps to long
                    if (touchCount > 0) {
                        self.controls.removeClass('has-pointer');
                    }

                    return false;
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

            this.controls.getElements('.gallery-bullet').addEvent('click', function(event) {
                var index = Number.toInt(this.get('data-index'));

                self.showImage(index);
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

            if (self.fullscreenAPI) {
                document.addEventListener && document.addEventListener(self.fullscreenAPI.event, function() {
                    if (self.gallery.hasClass('fullscreen') && !document[self.fullscreenAPI.fullscreenElement]) {
                        self.toggleFullscreen();
                    }
                })
            }
                                                                                                                                                                                                                                                    ;
            this.controls.addClass('done');
        }
    },

    getFullscreenPrefixes: function() {
        var i, self = this,
            fullscreenMethodNames = [
                'webkitRequestFullscreen',
                'mozRequestFullScreen',
                'msRequestFullscreen',
                'requestFullscreen'
            ],
            exitMethodNames = [
                'webkitExitFullscreen',
                'mozCancelFullScreen',
                'msExitFullscreen',
                'exitFullscreen'
            ],
            eventNames = [
                'webkitfullscreenchange',
                'mozfullscreenchange',
                'MSFullscreenChange',
                'fullscreenchange'
            ],
            fullscreenElements = [
                'webkitFullscreenElement',
                'mozFullScreenElement',
                'msFullscreenElement',
                'fullscreenElement'
            ];

        for (i = 0;  i < fullscreenMethodNames.length; i++) {
            var check = fullscreenMethodNames[i];

            if (self.gallery[check]) {
                self.fullscreenAPI = {
                    'requestFullscreen': check,
                    'exitFullscreen': exitMethodNames[i],
                    'event': eventNames[i],
                    'fullscreenElement': fullscreenElements[i]
                };

                return true;
            }
        }

        return false;
    },

    toggleFullscreen: function(toggle) {
        this.toggleEasing(false);

        var self = this;

        if (typeof toggle == 'undefined') {
            toggle = !this.gallery.hasClass('fullscreen');
        }

        if (toggle) {
            this.gallery.addClass('fullscreen');

            if (self.fullscreenAPI) {
                this.gallery[self.fullscreenAPI.requestFullscreen]();
            }

            if (!this.fullscreenDone) {
                // set Images to max size for best quality, no matter what the screen size
                this.setImages('xl');
                this.fullscreenDone = true;
            }
        } else if (this.gallery.hasClass('fullscreen')) {
            this.gallery.removeClass('fullscreen');

            if (self.fullscreenAPI && document[self.fullscreenAPI.fullscreenElement]) {
                document[self.fullscreenAPI.exitFullscreen]();
            }
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
        var bullets = this.controls.getElements('.gallery-bullet');

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
        this.playvideo.removeClass('is-video');

        if (wrapper.getElement('video')) {
            this.playvideo.addClass('is-video');
        }

        wrapper.addClass('active');

        if (bullets.length) {
            bullets.removeClass('active');
            bullets[index].addClass('active');
        }

        this.controls.getChildren('div').addClass('active');

        if (this.currentIndex === 0) {
            this.controls.getElement('.gallery-left').removeClass('active');
        }

        if (this.currentIndex == (this.images.length - 1) || wrapper.offsetLeft >= maxOffset) {
            this.controls.getElement('.gallery-right').removeClass('active');
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
