/**
 * Shows the loader.
 */

function loaderFadeIn() {
    $('html, body').css('overflow', 'hidden');
    $('#loader').fadeIn('fast');
}

/**
 * Hides the loader.
 */

function loaderFadeOut() {
    $('#loader').fadeOut('slow');
    $('html, body').css('overflow', '');
}