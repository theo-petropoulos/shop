$(function() {
    window.is_search    = null
    window.field        = null
    window.id           = null

    // Display an input field on edit
    $(document).on('click', '.adm_modify_button', function() {
        if ($(this).parents('.search_results_box').length)
            is_search = 1

        let container       = $(this).parent('div')
        let id_container    = container.attr('id').split('_')
        let value           = $(this).prev('p').length ? $(this).prev('p').text() : ( $(this).text() === 'Désactiver' ? 0 : 1 )

        id                  = id_container[0]
        field               = id_container[1]

        window['prevHTML_' + field + '_' + id] = $(this).parents('div').html()

        if (container.hasClass('active')) {
            let entity = $(this).closest('details').attr('id').split('_')[0]
            ajaxPost(entity, id, field, value, $(this))
        }
        else if (!container.hasClass('brand_name')) {
            if (field === 'startingDate' || field === 'endingDate') {
                let valueToDate = value.split('/')
                let valueYear   = valueToDate[2]
                let valueMonth  = valueToDate[1]
                let valueDay    = valueToDate[0]
                value       = valueYear + '-' + valueMonth + '-' + valueDay

                container.html(
                    '<input type="date" name="' + field + '" value="' + value + '" required>\
                    <span>\
                        <button class="adm_modify_submit">Valider</button>\
                        <button class="adm_modify_cancel">Annuler</button>\
                    </span>'
                )
            }
            else
                container.html(
                    '<input type="text" name="' + field + '" value="' + value + '" required>\
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
                (res) => {
                    brands = JSON.parse(res)
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

    // Cancel the edit
    $(document).on('click', '.adm_modify_cancel', function() {
        $(this).parents('div').first().html(window['prevHTML_' + field + '_' + id])
    })

    // Submit the edit
    $(document).on('click', '.adm_modify_submit', function(e){
        let proceed = 1

        let div     = "#" + $(this).parents('div').first().attr('id')
        let id_div  = $(this).closest('div').attr('id').split('_')
        let id      = id_div[0]
        let field   = id_div[1]

        let value   = $(this).closest('div').find('input').val() ?? $(this).closest('div').find('select').val()
        let entity  = $(this).closest('details').length ?
            $(this).closest('details').attr('id').split('_')[0] :
            $(this).parents('div').first().attr('id').split('_')[2]

        if (field === 'endingDate' || field === 'startingDate') {
            let d = new Date(value)

            if (isNaN(d.getTime()))
                proceed = 0
            else {
                if (field === 'endingDate') {
                    let cmp         = $('#' + id + '_startingDate_discount')
                    let cmpvalue    = cmp.find('p').text() ?
                        cmp.find('p').text() :
                        $('#' + id + '_startingDate_discount_search').find('p').text()

                    let cmpDate     = getStandardDate(cmpvalue);

                    if (cmpDate > d)
                        proceed = 0
                }
                else if (field === 'startingDate') {
                    let cmp         = $('#' + id + '_endingDate_discount')
                    let cmpvalue    = cmp.find('p').text() ?
                        cmp.find('p').text() :
                        $('#' + id + '_endingDate_discount_search').find('p').text()

                    let cmpDate     = getStandardDate(cmpvalue);

                    if (cmpDate < d)
                        proceed = 0
                }
            }
        }

        if (proceed) {
            $.post(
                postEdit,
                {
                    entity,
                    id,
                    field,
                    value
                },
                (res)=>{
                    console.log(res)
                }
            )
            .done(() => {
                if (is_search) {
                    let parent = $(this).parents('div').first()
                    parent.html(window['prevHTML_' + field + '_' + id])
                    parent.find('p').html(value)
                }
                else {
                    if (parseInt(id) !== id) {
                        let parent = '#' + $(this).closest('div').attr('id')
                        console.log(parent)
                        $(parent).load(" " + parent + " > *")
                        /*parent.html(window['prevHTML_' + field + '_' + id])
                        parent.find('p').html(value)
                        parent.attr('id', value + '_name')*/
                    }
                    else
                        $(div).load(" " + div + " > *")
                }
            })
        }
        else {
            $(div).find('p').last().remove()
            $(div).append('<p>La date est invalide.</p>')
        }
    })
})

function getStandardDate(strDate)
{
    let strToDate   = strDate.split('/')
    let stdYear     = strToDate[2]
    let stdMonth    = strToDate[1] - 1
    let stdDay      = strToDate[0]

    return new Date(stdYear, stdMonth, stdDay);
}

function ajaxPost(entity, id, field, value, btn)
{
    $.post(
        postEdit,
        {
            entity,
            id,
            field,
            value
        },
        (res) => {
            // console.log(res)
        }
    )
    .done(() => {
        if (is_search) {
            let parent = btn.parents('div').first()
            parent.html(window['prevHTML_' + field + '_' + id])
            parent.find('p').html(value)
        }
        else {
            let parent = '#' + btn.closest('div').attr('id')
            $(parent).load(" " + parent + " > *")
        }
    })
}