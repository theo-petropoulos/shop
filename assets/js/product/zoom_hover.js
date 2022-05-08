/**
 * Code by https://codepen.io/elevadorstudio with a bit of rearrangement
 */
$(function() {
    let refSizing = $('.product_image_hidden')

    resizeContainer(refSizing)

    $(window, 'body', 'main', '#product_image_container').resize(function() {
        resizeContainer(refSizing)
    })

    $(".product_image_resizer")
        // tile mouse actions
        .on("mouseover", function() {
            $(this)
                .children(".product_image")
                .css({
                    transform: "scale(1.7)",
                    transition: "all 0.3s ease-out"
                })
        })
        .on("mouseout", function() {
            $(this)
                .children(".product_image")
                .css({
                    transform: "scale(1)",
                    transition: "all 0.3s ease-out"
                })
        })
        .on("mousemove", function(e) {
            $(this)
                .children(".product_image")
                .css({
                    "transform-origin":
                        ((e.pageX - $(this).offset().left) / $(this).width()) * 100 +
                        "% " +
                        ((e.pageY - $(this).offset().top) / $(this).height()) * 100 +
                        "%"
                })
        })
})

function resizeContainer(refSizing)
{
    $('.product_image_resizer').css({
        width: refSizing.width(),
        height: refSizing.height()
    })
}