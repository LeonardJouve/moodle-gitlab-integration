import * as Str from "core/str";
import Prefetch from "core/prefetch";
import ModalSaveCancel from "core/modal_save_cancel";
import ModalEvents from "core/modal_events";
import Ajax from "core/ajax";

const showModal = async (moduleId) => {
    const modal = await ModalSaveCancel.create({
        large: true,
        title: Str.get_string("modal_release_solution_title", "mod_gitlab"),
        body: Str.get_string("modal_release_solution_description", "mod_gitlab"),
        buttons: {
            save: Str.get_string("modal_release_solution_confirm", "mod_gitlab"),
        },
        show: true,
    });

    modal.getRoot().on(ModalEvents.save, (e) => {
        e.preventDefault();
        modal.getRoot().find("form").submit();
    });

    modal.getRoot().on("submit", "form", (e) => {
        e.preventDefault();

        submitForm(modal, moduleId);
    });

    modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
    });

    modal.show();
};

const submitForm = (modal, moduleId) => {
    modal.hide();
    modal.destroy();
    
    Ajax.call([{
        methodname: "mod_gitlab_release_solution",
        args: {moduleid: moduleId}
    }])[0].then(() => window.location.reload());
};

export const init = ({moduleId}) => {
    Prefetch.prefetchStrings("mod_gitlab", [
        "modal_release_solution_title",
        "modal_release_solution_description",
        "modal_release_solution_confirm",
    ]);

    const button = document.getElementById("release-solution");
    if (!button) {
        return;
    }

    button.addEventListener("click", () => showModal(moduleId));
};
