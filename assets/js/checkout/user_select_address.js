$(document).on('click', '.select_checkout_address', function () {
    $('.select_checkout_address').removeClass('address-selected').css({
        background: 'initial'
    }).text('Sélectionner')

    $(this).addClass('address-selected').css({
        background: 'rgb(230, 255, 175)'
    }).text('Sélectionné')
})

$(document).on('submit', '#checkout_form_stripe', function (e) {
    if (!$('.select_checkout_address.address-selected').length) {
        e.preventDefault()
        flash('Vous devez sélectionner une adresse', 2500, 'warning')
    }
    else {
        let address = $('.address-selected').attr('id').split('_')[0]
        let input   = $('<input>').attr('type', 'hidden').attr('name', 'address').val(address)
        $(this).append(input)
    }
})