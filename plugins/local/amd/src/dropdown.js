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

import {getString} from "core/str";
import Prefetch from "core/prefetch";
import {call} from "core/ajax";

export const init = () => {
    // Prefetch the string.
    Prefetch.prefetchStrings("gitlab", [
        "pluginname",
        "form_token_apply",
        "form_token_loading",
    ]);

    loadJSContent();
};

const loadJSContent = async () => {
    const pluginName = await getString("gitlab", "pluginname");
    console.log("Plugin name:", pluginName);

    const tokenField = document.querySelector("input[name='customfield_gitlab_token']");
    const dropdown = document.querySelector("select[name='customfield_gitlab_groups']");

    if (!tokenField || !dropdown) {
        console.error("element not found", tokenField, dropdown);
        return;
    }

    dropdown.disabled = true;

    const button = document.createElement("button");
    button.className = "btn btn-secondary";
    const apply = await getString("gitlab", "form_token_apply");
    button.textContent = apply;

    tokenField.parentNode.insertBefore(button, tokenField.nextSibling);

    button.addEventListener("click", async () => {
        const token = tokenField.value;
        if (!token) {
            return;
        }

        button.disabled = true;
        button.textContent = await getString("gitlab", "form_token_loading");

        call([{
            methodname: "local_gitlab_get_groups",
            args: {token: token}
        }])[0].then((items) => {
            dropdown.innerHTML = "";
            items.forEach((item) => {
                const option = document.createElement("option");
                option.value = item.value;
                option.textContent = item.label;
                dropdown.appendChild(option);
            });

            dropdown.disabled = false;
        }).catch(() => {
            console.error("unable retrieve gitlab groups");
        }).finally(() => {
            button.disabled = false;
            button.textContent = apply;
        });
    });
};
