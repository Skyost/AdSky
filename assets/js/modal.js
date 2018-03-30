function showModal(title, message, buttons) {
    $('#modal h5').html(title);
    $('#modal .modal-body').html(message);

    let parent = $('#modal .modal-footer');
    let html = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';

    if(buttons != null) {
        for(let i = 0, n = buttons.length; i < n; i++) {
            let button = buttons[i];
            html += '<button id="' + button.id + '" type="button" class="btn ' + button.class + '">' + button.text + '</button>';
            if (button.callback == null) {
                continue;
            }
            parent.on('click', '#' + button.id, button.callback);
        }
        parent.html(html);
    }

    $('#modal').modal();
}

function closeModal() {
    $('#modal').modal('hide');
}