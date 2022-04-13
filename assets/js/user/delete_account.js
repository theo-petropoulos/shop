$(function (){
    // Affiche un pop-up de confirmation pour la suppression d'une adresse
    $(document).on('click', '.delete_account', function(e){
        e.preventDefault()
        let href = $(this).attr('href')
        Swal.fire({
            title: 'Supprimer votre compte',
            html: "Vous êtes sur le point de supprimer votre compte. Cette action est irréversible.<br><br>Afin de conserver un historique de votre activité, votre adresse mail sera conservée dans notre base de données pour une durée de 2 ans. Vous pouvez vous opposer à ce traitement en envoyant un e-mail à l\'adresse dpo@shop.com.<br><br>Confirmez-vous cette action ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Supprimer mon compte',
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