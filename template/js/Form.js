var Form = new Class({
    initialize: function(form) {
        this.form = form;

        this.setDateFields();
    },

    setDateFields: function() {
        var inputs = this.form.getElements('input[data-type=date]'),
            locale = new Locale();

        inputs.each(function(input) {
            var updateTs = function() {
                    var date = new Date(dateInput.get('value') + " " + timeInput.get('value'));

                    input.set('value', Math.round(date.getTime() / 1000));
                },
                ts = input.get('value'),
                dateInput = new Element('input', {
                    'class': input.get('class'),
                    'type': 'date',
                    'events': {
                        change: updateTs
                    }
                }),
                timeInput = new Element('input', {
                    'class': input.get('class'),
                    'type': 'time',
                    'events': {
                        change: updateTs
                    }
                });

            if (ts) {
                dateInput.set('value', locale.formatTs('yyyy-MM-dd', ts));
                timeInput.set('value', locale.formatTs('hh:mm', ts));
            }

            input.addClass('d-none');
            dateInput.inject(input.getParent('.form-group'));
            timeInput.inject(input.getParent('.form-group'));
        });
    },


});
