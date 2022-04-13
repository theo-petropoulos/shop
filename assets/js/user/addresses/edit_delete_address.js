$(function (){
    // Affiche un pop-up de confirmation pour la suppression d'une adresse
    $(document).on('click', '.delete_address', function(){
        let href = $(this).attr('href')
        Swal.fire({
            title: 'Vous êtes sur le point de supprimer cette adresse',
            text: 'Confirmez-vous cette action ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Supprimer l\'adresse',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Succès',
                    text: 'L\'adresse a bien été supprimée.',
                    icon: 'success'
                }).then (function() {
                    window.location.replace(href)
                })
            }
        })
    })

    // Affiche la modal d'édition d'adresse
    $(document).on('click', '.edit_address', function(){
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
})