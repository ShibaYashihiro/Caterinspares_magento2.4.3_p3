define([
    'uiClass',
    'underscore',
    'jquery',
    'mage/translate'
], function (StatusChecker, _, $) {
    return StatusChecker.extend({
        initialize: function () {
            this._super();

            setTimeout(this.updateStatus.bind(this), 3000);

            return this;
        },
        updateStatus: function () {
            $.ajax({
                url: this.url,
                type: 'GET',
                success: function (data) {
                    if (_.isObject(data)) {
                        _.each(data,function (val, indexerId) {
                            var el = $('input[type="checkbox"][value="' + indexerId + '"]').closest('tr');
                            if (el.length) {
                                var status_class = '',
                                    status_text = '';

                                switch (val.status) {
                                    case 'working':
                                        status_class = 'grid-severity-major';
                                        status_text = $.mage.__('Processing');
                                        break;
                                    case 'valid':
                                        status_class = 'grid-severity-notice';
                                        status_text = $.mage.__('Ready');
                                        break;
                                    case 'invalid':
                                        status_class = 'grid-severity-critical';
                                        status_text = $.mage.__('Reindex required');
                                        break;
                                }
                                el.find('td.col-indexer_status span').attr('class', '').addClass(status_class).text(status_text);
                                el.find('td.col-indexer_updated').text(val.updated_at);
                            }
                        });
                        setTimeout(this.updateStatus.bind(this), 3000);
                    }
                }.bind(this)
            });
        }
    });
});
