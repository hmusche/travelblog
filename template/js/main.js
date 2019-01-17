<?php require 'Geo.js'; ?>
<?php require 'Locale.js'; ?>
<?php require 'Form.js'; ?>

document.getElements('.solsken-form').each(function(form) {
    form.store('object', new Form(form));
});
