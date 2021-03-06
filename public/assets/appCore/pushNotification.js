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
            if(typeof data.detail !== "undefined" && typeof data.detail.title === "string")
                toast(data.detail.title + "<br>" + data.detail.body, 8000, "toast-success");
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

$(function () {
    if(window.hasOwnProperty("cordova") && !inIframe())
        document.addEventListener("deviceready", setupListeners, false);
})