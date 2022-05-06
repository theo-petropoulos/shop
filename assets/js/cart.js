$(function() {
    let counterField    = $('#buy_counter')
    let counter         = parseInt(counterField.text())

    // Decrease the product's counter
    $(document).on('click', '#buy_minus:enabled', function() {
        counterField.text(--counter)
        if (counter === 1)
            $(this).prop('disabled', true)
    })

    // Increase the product's counter
    $(document).on('click', '#buy_plus:enabled', function() {
        counterField.text(++counter)
        if (counter > 1)
            $('#buy_minus').prop('disabled', false)
    })

    $(document).on('click', '.btn-cart_add', function() {
        let id          = $(this).attr('id').split('_')[0]
        let quantity    = counter
        let cart        = Cookies.get('cart') ? JSON.parse(Cookies.get('cart')) : {}

        if (!cart) {
            cart[id]    = quantity
            cart        = JSON.stringify(cart)
        }
        else {
            let idFound = 0;
            for (let idInCart in cart) {
                if (idInCart === id) {
                    cart[idInCart] += quantity
                    idFound++
                }
            }

            if (!idFound) {
                cart[id] = quantity
            }
console.log(cart)
            cart            = JSON.stringify(cart)
        }

        Cookies.set('cart', cart, { sameSite: 'strict' })
    })
})
