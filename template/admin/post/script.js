(function(win, doc) {
    var dom = new Solsken.DOM(),
        textInput = dom.getElement('#element-text'),
        form = dom.getParent('form', textInput),
        statusSelect = dom.getElement('#element-status', form);

    if (dom.getElement('.image-wrapper')) {
        var postId = dom.getElement('.image-wrapper').getAttribute('data-post-id'),
            url = '<?php echo $this->webhost; ?>admin/resize-images/';

        new Solsken.Request({
            'url': url,
            'success': function(response) {
                if (response && response.files) {
                    var i, params = [],
                        progress = dom.getElement('.image-preloader'),
                        progressBar = dom.getElement('.progress-bar', progress);

                    for (let [file, sizes] of Object.entries(response.files)) {
                        for (i = 0; i < sizes.length; i++) {
                            params.push({post_id: postId, file: file, size: sizes[i]});
                        }
                    }

                    if (params.length) {
                        dom.removeClass(progress, 'd-none');
                        progress.style.opacity = 1;
                        var done = 0;

                        var resizeImage = function(index) {
                            if (index == params.length) {
                                setTimeout(function() {
                                    progress.style.opacity = 0;
                                }, 2000);
                            } else {
                                new Solsken.Request({
                                    'url': url,
                                    success: function() {
                                        done++;
                                        progressBar.style.width = ((index + 1) / params.length) * 100 + '%';
                                        resizeImage(index + 1);
                                    }
                                }).send(params[index]);
                            }
                        }

                        resizeImage(0);

                    }
                }
            }
        }).send({
            'post_id': postId
        });
    }

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

                var req = new Solsken.Request({
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
