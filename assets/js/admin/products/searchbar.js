window.search           = '';
window.item             = '';
window.itemContainer    = '';
window.itemBox          = '';

$(function() {
    // Dictionary
    let trans       = {
        'author'        : 'Auteur',
        'author_name'   : 'Auteur',
        'product'       : 'Produit',
        'product_name'  : 'Produit',
        'Produit'       : 'Produit',
        'price'         : 'Prix',
        'discount_name' : 'Promotion',
        'percentage'    : 'Pourcentage',
        'startingDate'  : 'Date de début',
        'endingDate'    : 'Date de fin',
        'description'   : 'Description',
        'stock'         : 'Stock',
        'active'        : 'Statut',
        'status'        : 'Statut',
        'true'          : 'Désactiver',
        'false'         : 'Activer'
    }

    // Open search bar
    $(document).on('click', '.trigger_adm_search', function() {
        item            = $(this).parent().attr('id').split('_')[2]
        itemContainer   = $('#search_' + item + '_container')
        itemBox         = $('#search_' + item + '_box')
        let inputField  = $("#adm_search_input_" + item)

        openSearchBar(itemBox, itemContainer, inputField)
    })

    // Close search bar on button click
    $(document).on('click', '.search_close_btn', function() {
        closeSearchBar(itemBox, itemContainer)
    })
    // Close search bar on clicking outside the div
    $(document).on('click', '.search_item_container', function(e) {
        if($(e.target).is('#search_author_container') || $(e.target).is('#search_product_container'))
            closeSearchBar(itemBox, itemContainer)
    })
    // Close search bar on esc keypress
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape')
            closeSearchBar(itemBox, itemContainer)
    })

    // Ajax search
    $(document).on({
        'keyup': function() {
            let search      = $('#adm_search_input_' + item).val()
            table           = item

            if (search.length > 0) {
                $.post(
                    searchPath,
                    {
                        search,
                        table
                    },
                    (res) => {
                        // console.log(res)
                    })
                .done(function (data, status) {
                    try {
                        let results = JSON.parse(data);
                        $("#search_results_" + item + " div").remove();

                        $(results).each(function (arrkey, object) {
                            $("#search_results_" + item).prepend(
                                "<div id='" + item + "_" + object['id'] + "_search' class='div_det'>\
                                </div>"
                            )

                            for (let key in object) {
                                let array = ['id', 'product_id']

                                if (table === 'discount')
                                    array.push('product_name')

                                if (!array.includes(key)) {
                                    let value = object[key]

                                    if (key === 'author_name' && table === 'discount') {
                                        key     = 'Produit'
                                        value  += ' - ' + object['product_name']
                                    }

                                    if (table === 'discount') {
                                        if (key === 'author_name') {
                                            key         = 'Produit'
                                            value      += ' - ' + object['product_name']
                                        }
                                        if (key === 'startingDate' || key === 'endingDate') {
                                            let date    = new Date(value.date)
                                            value       = date.toLocaleDateString("fr-FR")
                                        }
                                    }

                                    if (key !== 'active') {
                                        $("#" + item + "_" + object['id'] + "_search").append(
                                            "<div id='" + object['id'] + "_" + key + "_" + item + "_search' class='" + key + "'>\
                                                <h3>" + trans[key] + "</h3>\
                                                <p>" + value + "</p>\
                                            </div>"
                                        )

                                        if (key !== 'product_name' || table !== 'discount') {
                                            $("#" + object['id'] + "_" + key + "_" + item + "_search").append(
                                                "<button class='adm_modify_button'>Modifier</button>"
                                            )
                                        }
                                    }
                                    else {
                                        $("#" + item + "_" + object['id'] + "_search").append(
                                            "<div id='" + object['id'] + "_" + key + "_" + item + "_search' class='" + key + "'>\
                                                <h3>" + trans[key] + "</h3>\
                                                <button class='adm_modify_button'>" + trans[value] + "</button>\
                                            </div>"
                                        )
                                    }
                                }
                            }
                        });
                    } catch(e) {
                        return false;
                    }

                    if ($("#results_box p").length > 1) {
                        $("#search_input").css({
                            "border-bottom-right-radius": "0",
                            "border-bottom-left-radius": "0"
                        });
                    }
                })
                .fail(function() {
                    console.log('Search failed')
                })
            }
        }
    }, ".adm_search_input")

    // Display Products by Author
    $(document).on('click', '.show_products_by_author', function(e) {
        e.preventDefault()
        let author           = $(this).parent().find('.name p').text()
        const productSearch = $('#adm_search_input_product');
        $('#adm_search_product .trigger_adm_search').trigger('click')
        productSearch.val(author).trigger('keyup')
    })
})

function openSearchBar(box, container, field)
{
    box.animate({
        bottom: "0"
    }, 600)
    container.css({
        "visibility": "visible"
    })
    field.focus()
}

function closeSearchBar(box, container)
{
    box.animate({
        bottom: "-100%"
    }, 600, function(){
        container.css({
            "visibility":"hidden"
        })
    })
}