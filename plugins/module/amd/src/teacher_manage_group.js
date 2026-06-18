import * as Str from "core/str";
import ModalEvents from "core/modal_events";
import ModalSaveCancel from "core/modal_save_cancel";
import Prefetch from "core/prefetch";
import Fragment from "core/fragment";
import Ajax from "core/ajax";

const showModal = (contextId, groupId) => {
    return ModalSaveCancel.create({
        large: true,
        title: Str.get_string("modal_group_members_title", "mod_gitlab"),
        body: Fragment.loadFragment("mod_gitlab", "manage_group_form", contextId, {groupid: groupId}),
        buttons: {
            save: Str.get_string("modal_group_members_update", "mod_gitlab"),
        },
        show: true,
    }).then((modal) => {
        modal.getRoot().on(ModalEvents.save, (e) => {
            e.preventDefault();
            modal.getRoot().find("form").submit();
        });

        modal.getRoot().on("submit", "form", (e) => {
            e.preventDefault();

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

    const form = modal.getRoot().find("form")[0];
    const formData = new FormData(form);
    const members = formData.getAll("userlist[]").map(Number);

    Ajax.call([{
        methodname: 'mod_gitlab_set_group_members',
        args: {
            members,
            groupid: groupId,
        }
    }]);
};

export const init = ({contextId, groupId}) => {
    Prefetch.prefetchStrings("mod_gitlab", [
        "modal_group_members_title",
        "modal_group_members_update",
    ]);

    const button = document.getElementById(`manage-group-members-${groupId}`);
    if (!button) {
        return;
    }

    button.addEventListener("click", () => showModal(contextId, groupId));
};
