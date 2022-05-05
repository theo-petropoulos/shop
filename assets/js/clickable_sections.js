$(function() {
    $(document).on('click', '.navto_container', function() {
        let href = $(this).children('a.navto').attr('href')
        window.location.replace(href)
    })
})