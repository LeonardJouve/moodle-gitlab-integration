import * as Str from "core/str";
import ModalEvents from "core/modal_events";
import ModalSaveCancel from "core/modal_save_cancel";
import Prefetch from "core/prefetch";
import Fragment from "core/fragment";
import Ajax from "core/ajax";

const showModal = (contextId, groupId) => {
    return ModalSaveCancel.create({
        large: true,
        title: Str.get_string("modal_manage_group_title", "mod_gitlab"),
        body: Fragment.loadFragment("mod_gitlab", "manage_group_form", contextId, {groupid: groupId}),
        buttons: {
            save: Str.get_string("modal_manage_group_title", "mod_gitlab"),
        },
        show: true,
    }).then((modal) => {
        modal.getRoot().on(ModalEvents.save, (e) => {
            e.preventDefault();
            modal.getRoot().find("form").submit();
        });

        modal.getRoot().on("submit", "form", (e) => {
            e.preventDefault();

            console.log(e);

            submitFormAjax(modal, contextId, groupId);
        });

        modal.getRoot().on(ModalEvents.hidden, () => {
            modal.destroy();
        });
    });
};

const submitFormAjax = (modal, contextId, groupId) => {
    modal.hide();
    modal.destroy();

    console.log(modal.getRoot().find('form').serialize());

    const form = modal.getRoot().find('form');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const userlist = formData.getAll('userlist[]');
    console.log(data, userlist);

    const data = {};
    form.serializeArray().forEach(item => {
        if (data[item.name]) {
            // handle multiple values (like userlist[])
            if (!Array.isArray(data[item.name])) {
                data[item.name] = [data[item.name]];
            }
            data[item.name].push(item.value);
        } else {
            data[item.name] = item.value;
        }
    });

    console.log(data);

    Ajax.call([{
        methodname: 'mod_gitlab_test',
        args: {id: 123}
    }])[0].then(console.log);
};

export const init = ({contextId, groupId}) => {
    Prefetch.prefetchStrings("mod_gitlab", [
        "modal_manage_group_title",
    ]);

    const button = document.getElementById(`manage-group-members-${groupId}`);
    if (!button) {
        return;
    }

    button.addEventListener("click", () => showModal(contextId, groupId));
};
