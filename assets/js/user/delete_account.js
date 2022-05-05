$(function() {
    // Affiche un pop-up de confirmation pour la suppression d'une adresse
    $(document).on('click', '.delete_account', function(e) {
        e.preventDefault()
        let href = $(this).attr('href')
        Swal.fire({
            title: 'Supprimer votre compte',
            html: "Vous êtes sur le point de <strong>supprimer</strong> votre compte. Cette action est <strong>irréversible</strong>.<br><br>Afin de conserver une traçabilité de vos achats, vos adresses seront conservées dans notre base de données <strong>pour une durée de 2 ans</strong>. Vous pouvez vous opposer à ce traitement en envoyant un e-mail à l\'adresse <a href='mailto:dpo@shop.com'>dpo@shop.com</a>.<br><br>Confirmez-vous cette action ?",
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
                    text: 'Le compte a bien été supprimé.',
                    icon: 'success'
                }).then (function() {
                    window.location.replace(href)
                })
            }
        })
    })
})