<?php require 'Cookie.js'; ?>
<?php require 'Geo.js'; ?>
<?php require 'Locale.js'; ?>
<?php require 'Form.js'; ?>
<?php require 'Gallery.js'; ?>

window.Solsken = window.Solsken || {};

window.Solsken.locale = new Locale();

document.getElements('.solsken-form').each(function(form) {
    form.store('object', new Form(form));
});

document.getElements('.solsken-gallery').each(function(gallery) {
    gallery.store('gallery', new Gallery(gallery));
});
