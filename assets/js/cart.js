$(function() {
    let counterField    = $('.buy_counter')
    let counter         = counterField.length ? parseInt(counterField.text()) : 1

    $(document).on('click', '.btn-cart, .btn-cart_add', function(e) {
        e.preventDefault()
    })

    // Decrease the product's counter
    $(document).on('click', '.buy_minus:enabled', function() {
        counterField.text(--counter)
        if (counter === 1)
            $(this).prop('disabled', true)

        if (counter < stock)
            $('.buy_plus').prop('disabled', false)
    })

    // Increase the product's counter
    $(document).on('click', '.buy_plus:enabled', function() {
        counterField.text(++counter)
        if (counter > 1)
            $('.buy_minus').prop('disabled', false)

        if (parseInt(counter) === parseInt(stock))
            $(this).prop('disabled', true)
    })

    // Add product to the cart
    $(document).on('click', '.btn-cart_add', function() {
        let id          = $(this).attr('id').split('_')[0]
        let quantity    = counter
        let cart        = Cookies.get('cart') ? JSON.parse(Cookies.get('cart')) : {}

        $.get(
            '/products/' + id + '/' + quantity,
            (res) => {
                if (res === 'true') {
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
                        cart            = JSON.stringify(cart)
                    }

                    Cookies.set('cart', cart, { sameSite: 'strict' })

                    flash('Article ajouté au panier', 2500, 'success')
                }
                else
                    flash('Stock insuffisant', 2500, 'failure')
            }
        )
    })

    $(document).on('click', '.cart_product_remove', function() {
        let id      = $(this).parent().attr('id').split('_')[0]
        let cart    = Cookies.get('cart') ? JSON.parse(Cookies.get('cart')) : {}

        if (!cart)
            return false

        else {
            delete cart[id]
            cart = JSON.stringify(cart)
            Cookies.set('cart', cart, { sameSite: 'strict' })

            flash('Article supprimé du panier', 2500, 'failure')

            $('#cart').load(window.location.href + " #cart > *")
        }
    })

    $(document).on('click', '.cart_minus, .cart_plus', function() {
        let id      = $(this).attr('id').split('_')[0]
        let cart    = Cookies.get('cart') ? JSON.parse(Cookies.get('cart')) : {}

        if (!cart)
            return false

        else {
            if ($(this).hasClass('cart_minus')) {
                cart[id] -= 1
                if (cart[id] === 0) {
                    delete cart[id]
                    flash('Article supprimé du panier', 2000, 'failure')
                }
                else {
                    flash('Article retiré du panier', 2000, 'warning')
                }
            }
            else if ($(this).hasClass('cart_plus')) {
                cart[id] += 1
                flash('Article ajouté au panier', 2000, 'success')
            }

            cart = JSON.stringify(cart)
            Cookies.set('cart', cart, { sameSite: 'strict' })

            $('#cart').load(window.location.href + " #cart > *")
        }
    })
})
