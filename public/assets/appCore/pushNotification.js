/**
 * Push Notification Functions
 * @param asyncFunc
 * @param onSuccess
 * @param onFailure
 * @param customTries
 */
function trySomeTimes(asyncFunc, onSuccess, onFailure, customTries) {
    var tries = typeof customTries === "undefined" ? 100 : customTries;
    var interval = setTimeout(function () {
        if (typeof asyncFunc !== "function") {
            onSuccess("Unavailable");
            return;
        }
        asyncFunc()
            .then(function (result) {
                if ((result !== null && result !== "") || tries < 0) {
                    onSuccess(result);
                } else {
                    trySomeTimes(asyncFunc, onSuccess, onFailure, tries - 1);
                }
            })
            .catch(function (e) {
                clearInterval(interval);
                onFailure(e);
            });
    }, 100);
}

function setupOnTokenRefresh() {
    FCM.eventTarget.addEventListener(
        "tokenRefresh",
        function (data) {
            AJAX.post("pushNotificationRegister", {tokenPush: data.detail, code: "FCM"});
        },
        false
    );
}

function setupOnNotification() {
    FCM.eventTarget.addEventListener(
        "notification",
        function (data) {
            console.log(data.detail);
        },
        false
    );
    FCM.getInitialPushPayload()
        .then((payload) => {
            console.log("Initial Payload ", payload);
        })
        .catch((error) => {
            console.log("Initial Payload Error ", error);
        });
}

function registerFCMToken() {
    trySomeTimes(
        FCM.getToken,
        function (token) {
            AJAX.post("pushNotificationRegister", {tokenPush: token, code: "FCM"});
        },
        function (error) {
            console.log("Error on listening for FCM token: " + error);
        }
    );
}

function registerAPNSToken() {
    if (cordova.platformId !== "ios")
        return;

    FCM.getAPNSToken(
        function (token) {
            AJAX.post("pushNotificationRegister", {tokenPush: token, code: "APNS"});
        },
        function (error) {
            console.log("Error on listening for APNS token: " + error);
        }
    );
}

function waitForPermission(callback) {
    FCM.requestPushPermission()
        .then(function (didIt) {
            if (didIt) {
                callback();
            } else {
                console.log("Push permission was not given to this application");
            }
        })
        .catch(function (error) {
            console.log("Error on checking permission: " + error);
        });
}

function setupListeners() {
    waitForPermission(function () {
        registerFCMToken();
        registerAPNSToken();
        setupOnTokenRefresh();
        setupOnNotification();
    });
}

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
    if(window.hasOwnProperty("cordova"))
        document.addEventListener("deviceready", setupListeners, false);
    else
        setupListeners();

    $("body").off("click", "a.notification-title").on("click", "a.notification-title", function () {
        setNotificationOpen($(this).data("id"));
    });

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
                    if($navbarNotify.closest("#core-sidebar").length)
                        $("#core-menu-custom-bottom > .menu-li > [onclick='toggleSidebar()']").append("<span class='badge-notification' id='badge-note'>" + data + "</span>");
                }
            } else {
                $("#badge-note").remove();
            }
        } else {
            $("#badge-note").remove();
        }
    });
})