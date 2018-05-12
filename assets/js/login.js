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
            data.url = '../api/v1/users/login';
            if($('#form-rememberme').is(':checked')) {
                data.postData.rememberduration = 60 * 60 * 24 * 365.25;
            }
            data.successUrl = '../admin/';
        }
        else {
            data.url = '../api/v1/users/register';
            data.postData.username = $('#form-username').val();
            data.successUrl = '?message=registered';
        }

        $.post(data.url, data.postData, function(response) {
            if(response.error == null) {
                window.location.href = data.successUrl;
                return;
            }

            loaderFadeOut();
            $('#form-message').css('color', '').removeClass('mt-0').text(response.error);
        }, 'json');
        return false;
    });

    $('.form-switch').click(function(event) {
        event.preventDefault();

        $('.visible-login, .visible-register').toggle();
    });

    $('#forgot-password').click(function(event) {
        event.preventDefault();

        showModal('Forgot password ?', '<p>Enter your email address below :</p><input class="form-control" type="email" value="' + $('#form-email').val() + '">', [{
            id: 'forgot-password-ok',
            class: 'btn-primary',
            text: 'OK',
            callback: function() {
                loaderFadeIn();
                $.post('../api/v1/users/' + $('.modal-body input').val() + '/forgot', {}, function(data) {
                    loaderFadeOut();
                    $('.modal-body').html('<p class="mb-0">' + (data.error == null ? data.message : data.error) + '</p>');
                    $('#forgot-password-ok').remove();
                }, 'json');
            }
        }])
    });

    loaderFadeOut();
});