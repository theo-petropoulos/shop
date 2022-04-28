$(function(){
    window.is_search = null
    window.item = null
    window.id = null

    $(document).on('click', '.adm_modify_button', function() {
        if($(this).parents('.search_results_box').length)
            is_search = 1

        let container       = $(this).parent('div')
        let id_container    = container.attr('id').split('_')
        let value           = $(this).prev('p').text()

        id                  = id_container[0]
        item                = id_container[1]

        window['prevHTML_' + item + '_' + id] = $(this).parents('div').html()

        if (!container.hasClass('brand_name')) {
            container.html(
                '<input type="text" name="' + item + '" value="' + value + '" required>\
                <span>\
                    <button class="adm_modify_submit">Valider</button>\
                    <button class="adm_modify_cancel">Annuler</button>\
                </span>'
            )
        }
        else {
            let brands = null

            $.get(
                fetchBrands,
                (res)=>{
                    brands = JSON.parse(res)
                    console.log(res)
                }
            )
            .done(() => {
                container.html(
                    '<select id="select_brands_modify" name="id_brand"></select>\
                    <span>\
                        <button class="adm_modify_submit">Valider</button>\
                        <button class="adm_modify_cancel">Annuler</button>\
                    </span>'
                )
                for (let brand of brands) {
                    $('#select_brands_modify').append(
                        '<option value="' + brand['id'] + '">' + brand['name'] + '</option>'
                    )
                }
            })
        }
    })

    /**
     * Cancel the modification
     */
    $(document).on('click', '.adm_modify_cancel', function() {
        /*if (is_search) {*/
        $(this).parents('div').first().html(window['prevHTML_' + item + '_' + id])
       /* }
        else {
            let container       = $(this).parents('div')
            let id_container    = "#" + container.attr('id')
            $(id_container).load(" " + id_container + " > *")
        }*/
    })
})