function flashMessage(message, duration, customClass)
{
    $('body').prepend('<div id="custom-flash-message"></div>')

    let customMessage = $('#custom-flash-message')

    customMessage.addClass(customClass).css({
        opacity     : 1,
        visibility  : 'visible'
    }).text(message)

    setTimeout(function() {
        customMessage.animate({
            opacity: 0
        }, 500, function() {
            $(this).removeClass(customClass).empty()
        })
    }, duration)
}

export {flashMessage}