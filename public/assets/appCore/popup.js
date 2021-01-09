var enablePopUpClose = 0;

async function showPopUpModal(note, notification) {
    let tpl = await getTemplates();

    while (typeof tpl.notificationModal !== "string")
        await sleep(10);

    $("#notificationModal").parent().remove();
    $("#app").append(Mustache.render(tpl.notificationModal, note));
    $('#notificationModal').modal('show');

    let $block = $("<div id='blockBtnPopup'></div>").insertAfter("[data-dismiss='modal']");

    enablePopUpClose = 3;
    let awaitpopup = setInterval(function () {
        enablePopUpClose -= .1;
        let mic = parseFloat(enablePopUpClose % 1).toFixed(1) * 10;
        $("[data-dismiss='modal']").html("00:0" + parseInt(enablePopUpClose) + ":" + zeroEsquerda(mic));
        if (enablePopUpClose <= 0) {
            clearInterval(awaitpopup);
            enablePopUpClose = 0;
            $("[data-dismiss='modal']").html("fechar");
            $block.remove();
        }
    }, 100);

    $('#app').off('hidden.bs.modal', "#notificationModal").on('hidden.bs.modal', "#notificationModal", async function () {
        while (enablePopUpClose !== 0)
            await sleep(10);

        window.onpopstate = maestruHistoryBack;
        $("#notificationModal").parent().remove();
        if (typeof notification !== "undefined" && notification.length > 0)
            receivePopUpModal(notification);
    });

    /**
     * On back navigation, close modal
     */
    onHistoryBack(async function () {

        while (enablePopUpClose !== 0)
            await sleep(10);

        $("#notificationModal").parent().remove();
        if (notification.length > 0) {
            setTimeout(function () {
                receivePopUpModal(notification);
            }, 100);
        }
    });
}

async function receivePopUpModal(notification) {
    while ($("#notificationModal").length)
        await sleep(500);

    let showThisPopUp = !0;
    let note = notification.shift();

    if (note.ownerpub === USER.id) {
        showThisPopUp = !isEmpty(await db.exeRead("popup", note.id));
        if (showThisPopUp)
            await db.exeDelete("popup", note.id);
    } else {
        showThisPopUp = isEmpty(await db.exeRead("popup_user", {popup: note.id, ownerpub: USER.id}));
        if (showThisPopUp)
            await db.exeCreate("popup_user", {popup: note.id});
    }

    if (showThisPopUp)
        showPopUpModal(note, notification);
}

if(!inIframe()) {

    /**
     * Overload sistema de notificações
     * Recebimento, show modal
     */
    $(function () {
        sse.add("popup", async (data) => {
            if (USER.setor !== 0 && !isEmpty(data))
                receivePopUpModal(data);
        });
    });
}