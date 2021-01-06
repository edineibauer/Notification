async function setNotificationOpen(id) {
    db.exeCreate("notifications_report", {id: id, abriu: 1});
}

async function closeNote(id) {
    /**
     * Deleta card de notificação
     */
    let $note = $(".notification-item[rel='" + id + "']");
    $note.addClass("activeRemove");
    setTimeout(function () {
        $note.remove();
    }, 150);

    /**
     * Deleta notification report
     */
    db.exeDelete("notifications_report", id);
}

$(function () {
    /**
     * Notificações pendentes show badge
     */
    sse.add("notificationsBadge", async function (data) {
        if (USER.setor !== 0) {
            if (isNumberPositive(data)) {
                /**
                 * Adiciona badge notification apenas no navbar mobile e se tiver a aba de notificações
                 */
                let $navbarNotify = $("a[href='notificacoes']");
                if ($navbarNotify.length && !$navbarNotify.find("#badge-note").length) {
                    $navbarNotify.append("<span class='badge-notification' id='badge-note'>" + data + "</span>");
                    if ($navbarNotify.closest("#core-sidebar").length)
                        $("#core-menu-custom-bottom > .menu-li > [onclick='toggleSidebar()']").append("<span class='badge-notification' id='badge-note'>" + data + "</span>");
                }
            } else {
                $("#badge-note").remove();
            }
        } else {
            $("#badge-note").remove();
        }
    });

    $("body").off("click", "a.notification-title").on("click", "a.notification-title", function () {
        setNotificationOpen($(this).data("id"));
    });
})