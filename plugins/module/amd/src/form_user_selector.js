define(['jquery', 'core/ajax', 'core/str'], function($, Ajax, Str) {
    return {
        processResults: function(selector, results) {
            if (!$.isArray(results)) {
                return results;
            }

            return results.map((result) => ({
                value: "value",
                label: "label",
            }));
        },
        transport: function(selector, search, success, failure) {
            const courseid = $(selector).attr('courseid');
            const groupid = $(selector).attr('groupid');

            Ajax.call([{
                methodname: 'mod_gitlab_form_user_selector',
                args: {
                    courseid,
                    groupid,
                    search,
                },
            }]).then(success).fail(failure);
        },
    };
});
