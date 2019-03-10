var Cookie = new Class({
    initialize: function() {
        this.prefix = 'SLSKN';
    },

    acceptCookie: function(elem) {
        this.set('accept', 1);
        elem.getParent('.cookie-notice').addClass('d-none');
    },

    getKey: function(key) {
        return this.prefix + '---' + key;
    },

    /**
     * Set a Cookie
     * @param  {string} key    key to set, prepended by prefix
     * @param  {string} value  value to set
     * @param  {string} path   path to set
     * @param  {string} maxAge maxAge in days
     */
    set: function(key, value, path, maxAge) {
        var cookieParts = [
            this.getKey(key) + "=" + value
        ];

        path = path || "/";
        maxAge = maxAge || 365;
        maxAge = maxAge * 86400;

        cookieParts.push('path=' + path);
        cookieParts.push('max-age=' + maxAge);

        document.cookie = cookieParts.join(';');
    },

    get: function(key, def) {
        var check = new RegExp(this.getKey(key) + '=([^;]+)'),
            match = document.cookie.match(check);

        if (match && match[1]) {
            return match[1];
        } else {
            return def;
        }
    }
});
