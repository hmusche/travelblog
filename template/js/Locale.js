var Locale = new Class({
    initialize: function() {
        if (!Intl) {
            return;
        }

        var cookie = new Cookie(),
            timezone = Intl.DateTimeFormat().resolvedOptions().timeZone,
            language = navigator.language;

        if (!cookie.get('locale_settings')) {
            cookie.set('locale_settings', language);
        }

        if (!cookie.get('timezone') || cookie.get('timezone') != timezone) {
            cookie.set('timezone', timezone);
            document.location.reload();
        }

        this.currentTimezone = timezone;
    },

    formatTs: function(format, ts) {
        var date = new Date(ts * 1000),
            keys = {
            d: date.getDate(),
            M: date.getMonth() + 1,
            y: date.getFullYear(),
            h: date.getHours(),
            m: date.getMinutes(),
            s: date.getSeconds()
        };

        return format.replace(/d+|M+|y+|h+|m+|s+/g, function(placeholder) {
            var ret = String(keys[placeholder[0]]).slice(placeholder.length * -1);

            if (placeholder.length > ret.length) {
                ret = new Array(placeholder.length - ret.length + 1).join("0") + ret;
            }

            return ret;
        });
    }
});
