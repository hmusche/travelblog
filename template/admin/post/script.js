(function(win, doc) {
    var dom = new Solsken.DOM(),
        textInput = dom.getElement('#element-text'),
        form = dom.getParent('form', textInput),
        statusSelect = dom.getElement('#element-status', form);

    if (textInput && statusSelect) {
        var status = dom.getElements('option', statusSelect)[statusSelect.selectedIndex].value,
            labelElement = dom.getElement('[for=element-text]'),
            label = labelElement.innerHTML,
            hasId = doc.location.href.split('/').indexOf('id') > -1,
            timeout;

        handleUpdate = function(event) {
            clearTimeout(timeout);

            timeout = setTimeout(function () {
                var date = new Date(),
                    data = {
                        form_id: dom.getElement('[name=form_id]').value,
                        text: textInput.value
                    };

                labelElement.innerHTML = label +
                    ' <i class="fas fa-spin fa-sync text-muted"></i>';

                req = new Solsken.Request({
                    success: function(res) {
                        if (res.status && res.status == 'success') {
                            labelElement.innerHTML = label +
                                    ' <span class="small text-success"><?php echo $this->t('saved.at'); ?> ' +
                                    date.toLocaleTimeString() +
                                    ' <i class="fas fa-check green"></i>'+
                                    ' </span>';
                        } else {
                            labelElement.innerHTML = label +
                                ' <span class="small text-danger"><?php echo $this->t('error.saving'); ?></span>';
                        }
                    },
                    error: function(res) {
                        labelElement.innerHTML = label +
                            ' <span class="small text-danger"><?php echo $this->t('error.saving'); ?></span>';
                    }
                });

                req.send(data);
            }, 3000);
        };

        if (status == 'draft' && hasId) {
            textInput.addEventListener('keyup', handleUpdate);
        }
    }
})(window, document);
