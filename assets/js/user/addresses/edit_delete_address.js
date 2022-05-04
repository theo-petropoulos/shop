$(function() {
    // Affiche un pop-up de confirmation pour la suppression d'une adresse
    $(document).on('click', '.delete_address', function() {
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
})