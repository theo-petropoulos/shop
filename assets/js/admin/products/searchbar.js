window.search = '';
window.item = '';
window.itemContainer = '';
window.itemBox = '';

$(function(){
    // Open search bar
    $(document).on('click', '.trigger_adm_search', function(){
        item            = $(this).parent().attr('id').split('_')[2]
        itemContainer   = $('#search_' + item + '_container')
        itemBox         = $('#search_' + item + '_box')
        let inputField  = $("#adm_search_input_" + item)

        openSearchBar(itemBox, itemContainer, inputField)
    })

    // Close search bar on button click
    $(document).on('click', '.search_close_btn', function(){
        closeSearchBar(itemBox, itemContainer)
    })
    // Close search bar on clicking outside the div
    $(document).on('click', '.search_item_container', function(e){
        if($(e.target).is('#search_brand_container') || $(e.target).is('#search_product_container'))
            closeSearchBar(itemBox, itemContainer)
    })
    // Close search bar on esc keypress
    $(document).on('keydown', function(e){
        if (e.key === 'Escape')
            closeSearchBar(itemBox, itemContainer)
    })

    // Ajax search
    $(document).on({
        'keyup': function () {
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
                    try{
                        let results = JSON.parse(data);
                        $("#search_results_" + item + " div").remove();

                        $(results).each(function (arrkey, object) {
                            $("#search_results_" + item).prepend(
                                "<div id='" + item + "_" + object['id'] + "_search' class='div_det'>\
                                    <button class='adm_delete_btn'>X</button>\
                                </div>"
                            )

                            for (let key in object) {
                                let array = ['id', 'id_produit']

                                if (table === 'promotions')
                                    array.push('nom_produit')

                                if (!array.includes(key)) {
                                    let value = object[key]

                                    if (key === 'nom_marque') {
                                        key     = 'nom_produit'
                                        value  += ' - ' + object['nom_produit']
                                    }

                                    $("#" + item + "_" + object['id'] + "_search").append(
                                        "<div id='" + object['id'] + "_" + key + "_" + item + "_search' class='" + key + "'>\
                                            <h3>" + key + "</h3>\
                                            <p>" + value + "</p>\
                                        </div>"
                                    )

                                    if (key !== 'nom_produit') {
                                        $("#" + object['id'] + "_" + key + "_" + item + "_search").append(
                                            "<button class='adm_modify_button'>Modifier</button>"
                                        )
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        return false;
                    }

                    if ($("#results_box p").length > 1) {
                        $("#search_input").css({
                            "border-bottom-right-radius": "0",
                            "border-bottom-left-radius": "0"
                        });
                    }
                })
                .fail(function () {
                    console.log('Search failed')
                })
            }
        }
    }, ".adm_search_input")

    // Display Products by Brand
    $(document).on('click', 'a[href="admin_marques_show_products"]', function(e){
        e.preventDefault()
        let brand           = $(this).attr('id').replace('_link', '')
        const productSearch = $('#adm_search_input_produits');
        $('#adm_search_produits .trigger_adm_search').trigger('click')
        productSearch.val(brand).trigger('keyup')
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