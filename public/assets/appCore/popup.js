async function showPopUpModal(note, notification) {
    let tpl = await getTemplates();
    $("#notificationModal").parent().remove();
    $("#app").append(Mustache.render(tpl.notificationModal, note));
    $('#notificationModal').modal('show');

    $('#app').off('hidden.bs.modal', "#notificationModal").on('hidden.bs.modal', "#notificationModal", async function () {
        window.onpopstate = maestruHistoryBack;
        enableAutoPopUp = !0;
        $("#notificationModal").parent().remove();
        if (typeof notification !== "undefined" && notification.length > 0)
            receivePopUpModal(notification);
    });

    /**
     * On back navigation, close modal
     */
    onHistoryBack(function () {
        enableAutoPopUp = !0;
        $("#notificationModal").parent().remove();
        if (notification.length > 0) {
            setTimeout(function () {
                receivePopUpModal(notification);
            }, 100);
        }
    });
}

async function receivePopUpModal(notification) {
    while(!enableAutoPopUp)
        await sleep(500);

    enableAutoPopUp = !1;
    let showThisPopUp = !0;
    let note = notification.shift();

    if(note.ownerpub === USER.id) {
        showThisPopUp = !isEmpty(await db.exeRead("popup", note.id));
        if(showThisPopUp)
            await db.exeDelete("popup", note.id);
    } else {
        showThisPopUp = isEmpty(await db.exeRead("popup_user", {popup: note.id, ownerpub: USER.id}));
        if(showThisPopUp)
            await db.exeCreate("popup_user", {popup: note.id});
    }

    if(showThisPopUp)
        showPopUpModal(note, notification);
    else
        enableAutoPopUp = !0;
}

/**
 * Overload sistema de notificações
 * Recebimento, show modal
 */
var enableAutoPopUp = !0;
sse.add("popup", async (data) => {
    if (USER.setor !== 0 && !isEmpty(data))
        receivePopUpModal(data);
});