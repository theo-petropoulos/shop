$(function() {
    // Ouvre la modal
    $(document).on('click', '.edit_address, .add_btn, .open-modal', function(e) {
        e.preventDefault()
        $.post(
            $(this).attr('href')
        ).done(function(data) {
            let mainModalContainer = $('#main_modal_container')
            if ($(mainModalContainer).length) $(mainModalContainer).remove()
            $('body').append(data)
        })
    })

    // Retire la modal
    $(document).on('click', '#main_modal_container, .close-modal', function(e) {
        if (e.target === document.getElementById('main_modal_container') || e.target === document.getElementById('close_main_modal'))
            $("#main_modal_container").remove()
    })
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape')
            $('#main_modal_container').remove()
    })

    // Récupère les erreurs via JsonResponse
    $(document).on('submit', '#main_modal form', function(e) {
        /*e.preventDefault()
        let form = $(this)
        console.log(form, form.serialize())
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize()
        }).done(function(res) {
            $.each($('.sorted-error'), function() {
                $(this).remove()
            })

            let sortedErrors = isParsableJSON(res)
            console.log(sortedErrors);
            if (sortedErrors && sortedErrors['origin'] === 'form') {
                $.each(sortedErrors, function (key, message) {
                    $("#" + key).parent().after("<div class='sorted-error'>" + message + "</div>")
                })
                return false;
            }
        })*/
    })
})

function isParsableJSON(str) {
    try {
        return JSON.parse(str);
    } catch (e) {
        return false;
    }
}