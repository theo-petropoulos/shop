$(function() {
    $(document).on('click', '.delete_admin_btn', function (e) {
        e.preventDefault()
        let deleteId    = $(this).attr('id').split('_')[0]
        let href        = $(this).attr('href')
        let isCurrAdmin = deleteId === currAdminId

        Swal.fire({
            title: 'Suppression ' + ( isCurrAdmin ? 'de votre' : 'd\'un' ) + ' compte Administrateur',
            html: 'Vous êtes sur le point de <strong>supprimer</strong> ' + ( isCurrAdmin ? '<strong>votre</strong>' : 'un' ) + ' compte Administrateur. Cette action est <strong>irréversible</strong>.<br><br>Confirmez-vous cette action ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Supprimer',
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