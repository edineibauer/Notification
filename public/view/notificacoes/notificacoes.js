$(async function () {
    $(".badge-notification").remove();

    if (USER.setor === 0 || typeof firebaseConfig === "undefined" || !swRegistration || !swRegistration.pushManager || Notification.permission === "granted")
        $(".btn-notify").remove();
});