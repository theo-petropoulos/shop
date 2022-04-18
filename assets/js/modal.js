// Affiche la modal d'édition d'adresse
$(document).on('click', '.edit_address, .add_btn', function(){
    $.post(
        $(this).attr('href')
    ).done(function (data){
        let mainModalContainer = $('#main_modal_container')
        if ($(mainModalContainer).length) $(mainModalContainer).remove()
        $('body').append(data)
    })
})

// Retire la modal d'édition d'adresse
$(document).on('click', '#main_modal_container, .close-modal', function(e){
    if (e.target === document.getElementById('main_modal_container') || e.target === document.getElementById('close_main_modal'))
        $("#main_modal_container").remove()
})
$(document).on('keydown', function(e){
    if (e.key === 'Escape')
        $('#main_modal_container').remove()
})