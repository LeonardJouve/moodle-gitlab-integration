import * as Str from "core/str";
import Prefetch from "core/prefetch";
import ModalFactory from "core/modal_factory";
import ModalEvents from "core/modal_events";
import Ajax from "core/ajax";
import Fragment from "core/fragment";

const showModal = async (contextId, groupId, name) => {
    const modal = await ModalFactory.create({
        title: Str.get_string("modal_delete_group_title", "mod_gitlab"),
        body: Fragment.loadFragment("mod_gitlab", "confirm_delete_form", contextId, {name}),
        type: ModalFactory.types.DELETE_CANCEL,
    });

    modal.getRoot().on(ModalEvents.delete, (e) => {
        e.preventDefault();
        modal.getRoot().find("form").submit();
    });

    modal.getRoot().on("submit", "form", (e) => {
        e.preventDefault();

        submitForm(modal, groupId, name);
    });

    modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
    });

    modal.show();
};

const displayError = async (modal) => {
    const message = await Str.get_string('modal_delete_group_mismatch', 'mod_gitlab');
    const form = modal.getRoot().find("form");

    if (form.find(".gitlab-confirm-error").length === 0) {
        form.append(`<div class="alert alert-danger gitlab-confirm-error" role="alert">${message}</div>`);
    }
};

function removeError(modal) {
    modal.getRoot().find(".gitlab-confirm-error").remove();
};

const submitForm = (modal, groupId, name) => {
    removeError(modal);
    
    const form = modal.getRoot().find("form")[0];
    const formData = new FormData(form);
    const confirm = formData.get("confirmationname");

    if (confirm !== name) {
        displayError(modal);
        return;
    }

    modal.hide();
    modal.destroy();
    
    Ajax.call([{
        methodname: "mod_gitlab_delete_group",
        args: {groupid: groupId}
    }])[0].then(() => window.location.reload());
};

export const init = ({contextId, groupId, name}) => {
    Prefetch.prefetchStrings("mod_gitlab", [
        "modal_delete_group_title",
        "modal_delete_group_mismatch",
    ]);

    const button = document.getElementById(`delete-group-${groupId}`);
    if (!button) {
        return;
    }

    button.addEventListener("click", () => showModal(contextId, groupId, name));
};
