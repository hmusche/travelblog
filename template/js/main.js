<?php require 'vendor/hmusche/solsken/js/Cookie.js'; ?>
<?php require 'Geo.js'; ?>
<?php require 'Locale.js'; ?>
<?php require 'Form.js'; ?>
<?php require 'Gallery.js'; ?>

window.Solsken = window.Solsken || {};

window.Solsken.locale = new Locale();

document.getElements('.cookie-accept').each(function(elem) {
    elem.addEvent('click', function(e) {
        var cookie = new Solsken.Cookie;

        cookie.acceptCookie(this);
    });
});

document.getElements('#translate_content').each(function(checkbox) {
    checkbox.addEvent('change', function(event) {
        var cookie = new Solsken.Cookie;

        cookie.set('post_translate', this.get('checked'));

        document.location.reload();
    });
});

document.getElements('.solsken-form').each(function(form) {
    form.store('object', new Form(form));
});

document.getElements('.solsken-gallery').each(function(gallery) {
    gallery.store('gallery', new Gallery(gallery));
});
