var Gallery = new Class({
    initialize: function(gallery) {
        this.imageContainer = gallery.getElement('.image-wrapper');
        this.galleryWrapper = gallery.getElement('.gallery-wrapper');
        this.controls = gallery.getElement('.gallery-controls');

        this.setImages();
    },

    setImages: function() {
        var self = this;

        this.images = [];
        this.currentIndex = 1;
        this.loadedImages = 0;
        this.minHeight = 100000;

        this.imageContainer.getElements('img').each(function(img) {
            var img = new Element('img', {
                    'class': 'gallery-image',
                    'src': img.get('src'),
                    'data-index': self.currentIndex,
                    'styles': {
                        'max-width': window.innerWidth + 'px'
                    },
                    'events': {
                        'load': function() {
                            self.loadedImages++;

                            if (this.clientHeight < self.minHeight) {
                                self.minHeight = this.clientHeight;
                            }

                            if (self.loadedImages == self.images.length) {
                                self.initGallery();
                            }
                        }
                    }
                }),
                wrapper = new Element('div', {
                    'class': 'gallery-image-wrapper'
                });

            self.images.push(img);
            img.inject(wrapper);
            wrapper.inject(self.galleryWrapper);
        });
    },

    initGallery: function() {
        var self = this,
            galleryWidth = 0;

        this.images.each(function(image) {
            image.setStyle('height', self.minHeight);
            galleryWidth = galleryWidth + image.width;
        });

        self.galleryWrapper.setStyle('width', galleryWidth + 'px');

        this.showImage(0);
        this.initEvents();
        this.toggleEasing();
    },

    initEvents: function() {
        var self = this;

        this.controls.getElements('div').addEvent('click', function(e) {
            var direction = this.hasClass('gallery-left');

            if (direction) {
                self.showImage(self.currentIndex - 1);
            } else {
                self.showImage(self.currentIndex + 1);
            }
        });

        this.controls.addClass('done');
    },

    toggleEasing: function() {
        this.galleryWrapper.toggleClass('easing');
    },

    showImage: function(index) {
        if (!this.images[index]) {
            return;
        }

        this.currentIndex = index;

        var wrapper = this.images[index].getParent('.gallery-image-wrapper'),
            offset = wrapper.offsetLeft,
            width = this.images[index].clientWidth,
            viewportWidth = this.galleryWrapper.getParent('.solsken-gallery').clientWidth;

        this.galleryWrapper.setStyle('margin-left', -1 * (offset - (viewportWidth - width) / 2) + 'px');

        this.galleryWrapper.getElements('.gallery-image-wrapper').removeClass('active');
        wrapper.addClass('active');
    }
});
