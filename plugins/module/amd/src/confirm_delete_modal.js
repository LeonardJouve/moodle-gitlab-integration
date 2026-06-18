import * as Str from "core/str";
import Prefetch from "core/prefetch";
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';

const showModal = async (groupId) => {
    const modal = await ModalFactory.create({
        title: Str.get_string("modal_delete_group_title", "mod_gitlab"),
        body: Str.get_string("modal_delete_group_help", "mod_gitlab"),
        type: ModalFactory.types.DELETE_CANCEL,
    });

    modal.getRoot().on(ModalEvents.delete, () => {
        Ajax.call([{
            methodname: "mod_gitlab_delete_group",
            args: {groupid: groupId}
        }])[0].then(() => window.location.reload());


    });

    modal.show();
};

export const init = ({groupId}) => {
    Prefetch.prefetchStrings("mod_gitlab", [
        "modal_delete_group_title",
        "modal_delete_group_help",
    ]);

    const button = document.getElementById(`delete-group-${groupId}`);
    if (!button) {
        return;
    }

    button.addEventListener("click", () => showModal(groupId));
};
