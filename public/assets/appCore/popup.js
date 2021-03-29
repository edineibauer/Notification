var enablePopUpClose = 0, enablePopUpShow = !0;

async function showPopUpModal(note) {

    /**
     * Obtém template do popup, ou aguarda até ter disponível
     * */
    let tpl = await getTemplates();
    while (typeof tpl.notificationModal !== "string") {
        await sleep(50);
        tpl = await getTemplates();
    }

    localStorage.removeItem('popupToShow');
    $("#notificationModal").parent().remove();
    $("#app").append(Mustache.render(tpl.notificationModal, note));
    $('#notificationModal').modal('show');

    let $block = $("<div id='blockBtnPopup'></div>").insertAfter("[data-dismiss='modal']");

    /**
     * Coloca time para poder fechar o popup
     * */
    enablePopUpClose = 2;
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

    /**
     * Se fechar o modal
     * */
    $('#app').off('hidden.bs.modal', "#notificationModal").on('hidden.bs.modal', "#notificationModal", async function () {
        closePopup();
    });

    /**
     * On back navigation, close modal too
     */
    onHistoryBack(closePopup);
}

async function closePopup() {
    while (enablePopUpClose !== 0)
        await sleep(10);

    enablePopUpShow = !0;
    window.onpopstate = maestruHistoryBack;
    $("#notificationModal").parent().remove();
}

$(async function () {
    while (typeof USER.setor === "undefined")
        await sleep(300);

    if (!inIframe() && typeof USER === "object" && typeof USER.setor !== "undefined" && USER.setor !== 0) {

        /**
         * Overload sistema de notificações
         * Recebimento, show modal
         */
        sse.add("popup", async (data) => {
            if (!isEmpty(data)) {

                /**
                 * Adiciona poppu no armazenamento local para garantir que irá mantê-lo até ser exibido (reload)
                 * */
                localStorage.popupToShow = JSON.stringify(data);

                while (!enablePopUpShow)
                    await sleep(500);

                enablePopUpShow = !1;
                showPopUpModal(data);

            } else if (localStorage.popupToShow) {
                /**
                 * Caso tenha um popup pendente para mostrar
                 * */
                while (!enablePopUpShow)
                    await sleep(500);

                enablePopUpShow = !1;
                showPopUpModal(JSON.parse(localStorage.popupToShow));
            }
        });
    }
});