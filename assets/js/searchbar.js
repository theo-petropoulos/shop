window.search           = '';
window.globalSearchBar  = $("#search_bar")
window.globalSubBar     = $("#user_search_input_global")
window.itemContainer    = $("#search_global_container")
window.itemBox          = $("#search_global_box")
window.resultsBox       = $("#search_results_global")

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
    $(document).on('focus', '#search_bar', function() {
        openSearchBar(itemBox, itemContainer, globalSubBar)
    })

    // Close search bar on button click
    $(document).on('click', '.search_close_btn', function() {
        closeSearchBar(itemBox, itemContainer)
    })
    // Close search bar on clicking outside the div
    $(document).on('click', '.search_item_container', function(e) {
        if($(e.target).is("#search_global_container"))
            closeSearchBar(itemBox, itemContainer)
    })
    // Close search bar on esc keypress
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape')
            closeSearchBar(itemBox, itemContainer)
    })
console.log('toto')
    // Ajax search
    $(document).on({
        'keyup': function() {
            let search      = globalSubBar.val()
            globalSearchBar.val(globalSubBar.val())

            if (search.length > 0) {
                $.post(
                    globalSearchPath,
                    {
                        search
                    },
                    (res) => {
                        console.log(res)
                    })
                .done(function (data, status) {
                    try {
                        let results = JSON.parse(data);
                        $("#search_results_global .div_det").remove();

                        $(results).each(function (arrkey, object) {
                            resultsBox.prepend(
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
    }, "#user_search_input_global")
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