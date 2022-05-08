$(function() {
    $(document).on('click', '.btn-suggestions_nav', function() {
        let container   = $(this).parent()
        let suggestions = container.children('.suggestion_container')

        if ($(this).hasClass('suggestions-left')) {
            suggestions.css({
                left: '-=50%'
            })
            suggestions.last().prependTo(container)

            suggestions.animate({
                left: '0%'
            }, 300)
        }
        else if ($(this).hasClass('suggestions-right')) {
            suggestions.animate({
                left: '-=50%'
            }, {
                duration: 300,
                complete: function() {
                    suggestions.first().appendTo(container)
                    suggestions.css({
                        left: '0%'
                    })
                }
            })
        }
    })
})
