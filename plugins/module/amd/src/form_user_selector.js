define(['jquery', 'core/ajax', 'core/templates', 'core/str'], function($, Ajax, Templates, Str) {
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
            }])[0].then(async (results) => {
                const users = await Promise.all(results.map((result) => Templates.render('mod_gitlab/form_user_selector', result)));
                success(users);
            }).fail(failure);
        },
    };
});
