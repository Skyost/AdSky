function loaderFadeIn() {
    $('html, body').css('overflow', 'hidden');
    $('#loader').fadeIn('fast');
}

function loaderFadeOut() {
    $('html, body').css('overflow', '');
    $('#loader').fadeOut('slow');
}