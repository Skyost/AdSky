$(document).ready(function() {
    $('.visible-register').toggle();

    $('form').on('submit', function(event) {
        event.preventDefault();
        loaderFadeIn();

        let data = {
            postData: {
                email: $('#form-email').val(),
                password: $('#form-password').val()
            }
        };
        if($('.visible-login').is(':visible')) {
            data.url = 'api/user/login.php';
            if($('#form-rememberme').is(':checked')) {
                data.postData.rememberduration = 60 * 60 * 24 * 365.25;
            }
        }
        else {
            data.url = 'api/user/register.php';
            data.postData.username = $('#form-username').val();
        }

        $.post(data.url, data.postData, function(data) {
            if(data.error == null) {
                window.location.href = 'admin.php';
                return;
            }

            loaderFadeOut();
            $('#form-message').removeClass('mt-0').text(data.error);
        }, 'json');
        return false;
    });

    $('.form-switch').click(function(event) {
        event.preventDefault();

        $('.visible-login, .visible-register').toggle();
    });

    loaderFadeOut();
});