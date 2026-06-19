define(['jquery', 'core/ajax', 'core/templates', 'core/str'], function($, Ajax, Templates, Str) {
    return {
        processResults: function(selector, results) {
            if (!$.isArray(results)) {
                return results;
            }

            return results.map((result) => ({
                value: result.id,
                label: result.label,
            }));
        },
        transport: function(selector, search, success, failure) {
            const courseid = $(selector).attr('courseid');

            Ajax.call([{
                methodname: 'mod_gitlab_reviewer_selector',
                args: {
                    courseid,
                    search,
                },
            }])[0].then(async (results) => {
                const users = await Promise.all(results.map(async (result) => ({
                    ...result,
                    label: await Templates.render('mod_gitlab/reviewer_selector', result),
                })));
                success(users);
            }).fail(failure);
        },
    };
});
