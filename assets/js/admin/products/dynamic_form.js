$(function() {
    // Display an alert and prevent Form submit in case of wrong selection of Dates
    $(document).on('change', '#add_discount_endingDate, #add_discount_startingDate', function () {
        let startingDate    = $('#add_discount_startingDate')
        let endingDate      = $('#add_discount_endingDate')
        let submitButton    = $('#add_item_submit')
        let dateAlert       = $('#alert_discount_date')

        dateAlert.remove()

        if (Date.parse(startingDate.val()) > Date.parse(endingDate.val())) {
            endingDate.after('<p class="red-text" id="alert_discount_date">La date de fin ne peut pas être inférieure à la date de début.</p>')
            submitButton.prop('disabled', true).css({
                'pointer-events': 'none'
            })
            endingDate.css('background', 'orange')
        } else {
            dateAlert.remove()
            submitButton.prop('disabled', false).css({
                'pointer-events': 'initial'
            })
            endingDate.css('background', 'initial')
        }
    })

    // Display a selection of Products depending on Brand's selection
    $(document).on('change', '#add_discount_brand', function () {
        let brand           = $(this).val()
        let productInput    = $('#add_discount_product')
        let options         = '<option value=""></option>'

        if (brand) {
            $.post(
                fetchPath,
                {
                    brand
                },
                (res) => {
                    // console.log(res)
                }
            )
            .done(function (data) {
                let products    = JSON.parse(data)
                options         = '<option value="999999">Tous les produits</option>'

                if (products.constructor.name === "Object") {
                    for (let name in products)
                        options += '<option value="' + products[name] + '">' + name + '</option>'
                    productInput.prop('disabled', false).html(options)
                } else
                    productInput.prop('disabled', true).html(options)
            })
        } else
            productInput.prop('disabled', true).html(options)
    })
})