$(function() {
    let counter = typeof counter === 'undefined' ? 0 : counter

    $(document).on('click', '#load_products_link', function(e) {
        e.preventDefault()

        let href = $(this).attr('href')

        $.post(
            href,
            {
                counter
            },
            (res) => {
                let products    = JSON.parse(res)
                let addDiv      = $('#load_products')
                for (let key in products) {
                    if (products[key] !== 'catalogsEnd') {
                        let product = JSON.parse(products[key])
                        addDiv.before(
                            '<div id="' + product['id'] + '_product" class="product_container">\n' +
                            '<a href="' + productHref.replace('idPlaceholder', product['id']) + '">\n' +
                            '<img src="' + imagePath.replace('authorIdPlaceholder', product['author']['id']).replace('imageNamePlaceholder', product['images'][0]['name']) +'">\n' +
                            '</a>\n' +
                            '</div>'
                        )
                    }
                    else {
                        addDiv.remove()
                    }
                }
            }
        )

        counter++
    })
})
