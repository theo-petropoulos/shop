$(function() {
    // Display the current hovered image as the main background
    $(document).on('mouseenter', '.author_container', function() {
        let imagePath = $(this).css('background-image')
        $('#authors').css({
            'background-image': imagePath
        })
    })

    // Display the current hovered article's image as the left section background
    $(document).on('mouseenter', '.author_product_container', function() {
        let imagePath = $(this).find('.author_product_image').attr('src')
        // console.log(imagePath)
        $('#left_section').css({
            'background-image': 'url("' + imagePath + '")'
        })
    })

    // Remove the main background on mouseleave
    $(document).on('mouseleave', '#authors', function() {
        $('#authors').css({
            'background-image': 'initial'
        })
    })
})