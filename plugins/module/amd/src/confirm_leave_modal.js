import * as Str from "core/str";
import Prefetch from "core/prefetch";
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';

const showModal = async (groupId) => {
    const modal = await ModalFactory.create({
        title: Str.get_string("modal_leave_group_title", "mod_gitlab"),
        body: Str.get_string("modal_leave_group_help", "mod_gitlab"),
        type: ModalFactory.types.DELETE_CANCEL,
        buttons: {
            delete: Str.get_string("modal_leave_confirm", "mod_gitlab"),
        },
    });

    modal.getRoot().on(ModalEvents.delete, () => {
        Ajax.call([{
            methodname: "mod_gitlab_leave_group",
            args: {groupid: groupId}
        }])[0].then(() => window.location.reload());


    });

    modal.show();
};

export const init = ({groupId}) => {
    Prefetch.prefetchStrings("mod_gitlab", [
        "modal_leave_group_title",
        "modal_leave_group_help",
        "modal_leave_confirm",
    ]);

    const button = document.getElementById(`leave-group-${groupId}`);
    if (!button) {
        return;
    }

    button.addEventListener("click", () => showModal(groupId));
};
