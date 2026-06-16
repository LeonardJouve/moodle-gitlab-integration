import * as Str from "core/str";
import Config from "core/config";
import ModalEvents from "core/modal_events";
import jQuery from "jquery";
import ModalSaveCancel from "core/modal_save_cancel";
import Prefetch from "core/prefetch";
import Fragment from "core/fragment";

const showModal = (contextId, groupId) => {
    return ModalSaveCancel.create({
        large: true,
        title: Str.get_string("modal_manage_group_title", "mod_gitlab"),
        body: Fragment.loadFragment("mod_gitlab", "manage_group_form", contextId, {}),
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

    jQuery.ajax(`${Config.wwwroot}/gitlab/ajax.php?id=${contextId}&groupid=${groupId}&action=test`, {
        type: "GET",
        processData: false,
        contentType: "application/json",
    }).then(console.log);
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
