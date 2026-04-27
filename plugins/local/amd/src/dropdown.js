// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_gitlab
 * @category    string
 * @copyright   2026 Léonard Jouve leonard.jouve@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';
import Prefetch from 'core/prefetch';

export const init = () => {
    // Prefetch the string.
    Prefetch.prefetchString('gitlab', 'pluginname');

    loadJSContent();
};

/**
 * Add content using JavaScript.
 */
const loadJSContent = async() => {
    const pluginName = await getString('gitlab', 'pluginname');
    console.log("Plugin name:", pluginName);

    // const tokenField = $('input[name="customfield_gitlab_token"]');
    // const dropdown = $('select[name="customfield_gitlab_groups"]');

    // if (!tokenField.length || !dropdown.length) {
    //     return;
    // }

    // // Initial state
    // dropdown.prop('disabled', true);

    // // Create Apply button
    // const button = $('<button type="button" class="btn btn-secondary">Apply</button>');
    // tokenField.after(button);

    // button.on('click', function() {

    //     const token = tokenField.val();

    //     if (!token) {
    //         return;
    //     }

    //     button.prop('disabled', true).text('Loading...');

    //     Ajax.call([{
    //         methodname: 'local_gitlab_get_groups',
    //         args: {
    //             token: token
    //         }
    //     }])[0].done(function(response) {

    //         dropdown.empty();

    //         response.forEach(function(item) {
    //             dropdown.append(
    //                 $('<option></option>')
    //                     .val(item.value)
    //                     .text(item.label)
    //             );
    //         });

    //         dropdown.prop('disabled', false);

    //     }).fail(function() {
    //         alert('Failed to load GitLab groups');
    //     }).always(function() {
    //         button.prop('disabled', false).text('Apply');
    //     });

    // });
};
