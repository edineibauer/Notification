async function noteFuncao(note) {
    for(let n of note) {
        if(isNumberPositive(n.notificacao) && n.recebeu != 1)
            db.exeUpdate("notifications_report", {id: n.id, recebeu: 1});

        n.data_de_envio = moment(n.data_de_envio).calendar();
    }

    return note;
}