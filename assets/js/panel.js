const BASE_TITLE = $('title').text();
const ENTITY_MAP = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
    '/': '&#x2F;',
    '`': '&#x60;',
    '=': '&#x3D;'
};
const REQUESTED = [];
const ADS_DATA_HANDLER = function(data, i) {
    switch(i) {
        case 0:
            return ['id', data['id'], 'none'];
        case 1:
            return ['title', data['title'], 'text'];
        case 2:
            return ['message', data['message'], 'text'];
        case 3:
            return ['type', null, 'select', ['Title', 'Chat']];
        case 4:
            return ['interval', data['interval'], 'number'];
        case 5:
            return ['expiration', formatDate(new Date(data['expiration'] * 1000)), 'date'];
        case 6:
            return ['username', data['username'], 'none'];
    }
};

let updateChecked = false;

$(document).ready(function() {
    $('#nav-drawer').click(function(event) {
        event.preventDefault();
        $('#sidenav').toggleClass('d-none');
    });

    $('[data-show]').click(function(event) {
        event.preventDefault();
        showFragment($(this).attr('data-show'));
    });

    $('#nav-logout').click(function(event) {
        event.preventDefault();
        loaderFadeIn();
        $.getJSON('../api/user/logout', function() {
            window.location.href = '../login/';
        });
    });

    $('#btn-update').click(function(event) {
        event.preventDefault();

        let newEmail = $('#form-user-email').val();
        let changeEmail = newEmail && newEmail != USER_DATA.email;
        if(changeEmail && newEmail != $('#form-user-email-confirm').val()) {
            showError('profile', 'Invalid email confirmation.');
            return;
        }

        let newPassword = $('#form-user-password').val();
        if(newPassword && newPassword != $('#form-user-password-confirm').val()) {
            showError('profile', 'Invalid password confirmation.');
            return;
        }

        if(!changeEmail && !newPassword) {
            showError('profile', 'No change applied to your current profile.');
            return;
        }

        let data = {'oldpassword': $('#form-user-current-password').val()};
        if(changeEmail) {
            data.email = newEmail;
        }
        if(newPassword) {
            data.password = newPassword;
        }

        defaultPostRequest('../api/user/update', data, 'profile', function() {
            if(changeEmail) {
                window.location.href = '../login/?message=updated';
                return;
            }
            goToOrReload('?message=profile_updated#profile');
        })
    });

    $('#btn-create').click(function(event) {
        event.preventDefault();
        let type = $('#form-ad-type').val();
        defaultPostRequest('../api/ad/pay', {
            type: type,
            title: $('#form-ad-title').val(),
            message: $('#form-ad-message').val(),
            interval: $('#form-ad-day').val(),
            duration: type == 0 ? $('#form-ad-duration').val() : -1,
            expiration: new Date($('#form-ad-expiration').val()).getTime() / 1000
        }, 'create', function(data) {
            goToOrReload(data.object);
        });
    });

    let select = $('#fragment-create select');
    select.change(function() {
        let data;
        if(this.value == 0) {
            data = TITLE_SETTINGS;
            $('#form-ad-duration').removeAttr('disabled');
        }
        else {
            data = CHAT_SETTINGS;
            $('#form-ad-duration').attr('disabled', 'disabled');
        }

        printLimitations(data);
    });
    select.change();

    $('#content .table').on('click', '.fa-edit', function() {
        let row = $(this).parent().parent();
        row.find('td[data-editable=true] input, td[data-editable=true] select').removeAttr('disabled');
        $(this).addClass('fa-check').removeClass('fa-edit');
        $(this).parent().find('.fa-trash-alt').addClass('fa-ban').removeClass('fa-trash-alt');
    });

    $('#content .table').on('click', '.fa-ban', function() {
        let row = $(this).parent().parent();
        row.find('input, select').attr('disabled', 'disabled');
        $(this).addClass('fa-trash-alt').removeClass('fa-ban');
        $(this).parent().find('.fa-check').addClass('fa-edit').removeClass('fa-check');
    });

    $('#fragment-list .table').on('click', '.fa-sync-alt', function() {
        let today = new Date();
        let date = new Date(Date.UTC(today.getUTCFullYear(), today.getUTCMonth(), today.getUTCDate(), 0, 0, 0)).getTime() / 1000;

        let row = $(this).parent().parent().children();
        let type = row.eq(3).attr('data-type');

        let expiration = (row.eq(5).attr('data-expiration') - date) / (60 * 60 * 24);
        let min = null;
        let max = null;
        if(type == 0) {
            min = expiration >= TITLE_SETTINGS[8] ? 1 : TITLE_SETTINGS[8] - expiration;
            max = TITLE_SETTINGS[9] - expiration;
        }
        else {
            min = expiration >= CHAT_SETTINGS[6] ? 1 : CHAT_SETTINGS[6] - expiration;
            max = CHAT_SETTINGS[7] - expiration;
        }

        if(max <= min || row.eq(5).attr('data-expiration') <= date) {
            showModal('Cannot renew ad', '<p class="mb-0">Cannot renew this ad because the max expiration date is already reached. Please come back on another day.</p>');
            return;
        }

        showModal('Renew ad', '<p>Please enter how many days you want to add to the selected ad :</p><input class="form-control" type="number" min="' + min + '" max="' + max + '" value="' + min + '"/>', [{
            id: 'button-ok',
            class: 'btn-primary',
            text: 'OK',
            callback: function() {
                closeModal();
                defaultPostRequest('../api/ad/renew', {
                    id: row.eq(0).attr('data-id'),
                    days: $('#modal input').val()
                }, 'list', function(data) {
                    goToOrReload(data.object);
                });
            }
        }]);
    });

    $('#fragment-list .table').on('click', '.fa-trash-alt', function() {
        let row = $(this).parent().parent().children();
        defaultPostRequest('../api/ad/delete', {id: row.eq(0).attr('data-id')}, 'list', null);
    });

    let inputs = $('#form-ad-type, #form-ad-expiration');
    inputs.on('change', function() {
        let cost = $('#form-ad-type').val() == 0 ? TITLE_SETTINGS : CHAT_SETTINGS;
        cost = cost[cost.length - 1];

        let today = new Date();
        today.setHours(0, 0, 0, 0);

        let expiration = new Date($('#form-ad-expiration').val());
        expiration.setHours(0, 0, 0, 0);

        $('#ad-cost').text(cost * ((expiration.getTime() - today.getTime()) / (1000 * 60 * 60 * 24)));
    });
    inputs.change();

    loaderFadeOut();
    showFragment(window.location.hash ? window.location.hash.substring(1) : 'home');
});

$(document).on('fragmentChanged', function(event, fragment) {
    if(fragment == 'home' && USER_DATA.type == 0 && !updateChecked) {
        loaderFadeIn();
        $.get('../api/update/check', function(data) {
            updateChecked = true;
            loaderFadeOut();

            if(data.object == null) {
                return;
            }

            showUpdateMessage(data.object);
        }, 'json');

        return;
    }

    if(fragment == 'list') {
        makeRequest('list', {
            'url': '../api/ad/list',
            'data': {username: USER_DATA.username}
        }, {
            'buttons': '<i class="fas fa-sync-alt"></i> <i class="fas fa-trash-alt"></i>',
            'handlingLength': 6,
            'dataHandler': ADS_DATA_HANDLER
        }, true);
    }
});

/**
 * Shows an update message according to the specified object.
 *
 * @param object The object (must contain a version and a download link).
 */

function showUpdateMessage(object) {
    let message = '<h4 class="alert-heading">Update available !</h4>';
    message += '<p>An update (' + object.version + ') seems to be available to download. ';
    message += 'You have two choices : you click on the button on the bottom of this alert to install it (Bêta).</p>';
    message += '<hr>';
    message += '<p>Or you can download it manually by clicking <a href="' + object.download + '">here</a>. ';
    message += 'Don\'t forget to check the <em>MIGRATION.md</em> file if required.</p>';
    message += '<strong id="update-trigger" class="float-right text-uppercase" style="cursor: pointer;">Update</strong>';

    $('#fragment-home h1').after('<div class="alert alert-info clearfix" role="alert">' + message + '</div>');
    $('#update-trigger').click(function() {
        defaultPostRequest('../api/update/update', {}, 'home', function() {
            goToOrReload('?message=adsky_updated#home');
        });
    });
}

/**
 * Sends a POST request, shows the error if an error is returned and call the "href" callable otherwise.
 *
 * @param url The URL.
 * @param postData The POST parameters.
 * @param fragment Target fragment (where to show errors).
 * @param href Will be called if there is no error.
 */

function defaultPostRequest(url, postData, fragment, href) {
    loaderFadeIn();
    $.post(url, postData, function(data) {
        if(data.error != null) {
            loaderFadeOut();

            showError(fragment, data.error);
            return;
        }

        if(href == null) {
            location.reload();
            return;
        }

        href(data);
    }, 'json');
}

/**
 * Prints limitations on the screen according to the specified data array.
 *
 * @param data The data array (see views/admin/content.twig).
 */

function printLimitations(data) {
    let now = new Date();
    now.setHours(0, 0, 0, 0);
    now = now.getTime();
    let items = $('#fragment-create .form-group');
    for(let itemIndex = 0, limitationIndex = 0, length = items.length; itemIndex < length; itemIndex++) {
        let item = $(items[itemIndex]);
        let input = item.find('input');
        if(input.attr('disabled')) {
            continue;
        }

        let limitations = item.find('.ad-limitation');
        if(limitations.length == 0) {
            continue;
        }

        let type = input.attr('type');
        $.each(limitations, function() {
            let limitation = data[limitationIndex++];

            if(type == 'number') {
                if(limitationIndex % 2 === 1) {
                    input.attr('min', limitation);
                    input.val(limitation);
                }
                else {
                    input.attr('max', limitation);
                }

            }

            else if(type == 'date') {
                limitation = formatDate(new Date(now + limitation * 1000 * 60 * 60 * 24));

                if(limitationIndex % 2 === 1) {
                    input.attr('min', limitation);
                    input.val(limitation);
                }
                else {
                    input.attr('max', limitation);
                }

            }

            else if(type == 'text' && limitationIndex % 2 === 0) {
                input.attr('maxlength', limitation);
            }

            $(this).text(limitation);
        });
    }
}

/**
 * Shows a fragment.
 *
 * @param fragment The fragment's name.
 */

function showFragment(fragment) {
    if(!($('#fragment-' + fragment).length)) {
        fragment = 'home';
    }

    $('#sidenav a.active').removeClass('active');
    $('[id^="fragment-"]').css('display', '');

    let element = $('#fragment-' + fragment);
    element.css('display', 'block');

    let currentMenu = $('[data-show="' + fragment + '"]');
    currentMenu.addClass('active');

    document.title = BASE_TITLE + ' - ' + element.find('h1').text();
    window.location.hash = fragment;

    $('html').scrollTop(0);

    $(document).trigger('fragmentChanged', fragment);
}

/**
 * Displays an error.
 *
 * @param fragment The target fragment.
 * @param error The error to display.
 */

function showError(fragment, error) {
    $('html').scrollTop(0);
    $('#fragment-' + fragment + ' .alert-danger').css('display', '').find('p').text(error);
}

/**
 * Makes a request towards an API file that returns pages (api/ad/list for example).
 *
 * @param fragment The target fragment.
 * @param postData The POST data : must contain a "url" and POST parameters ("data").
 * @param printData The print data : must contain "buttons", how many data you want to handle ("handlingLength") and a callable "dataHandler"
 * @param firstRequest Whether or no this is the first request.
 */

function makeRequest(fragment, postData, printData, firstRequest) {
    if(firstRequest && REQUESTED.filter(function(element) { return element == fragment; }).length > 0) {
        return;
    }

    let paginator = $('#fragment-' + fragment + ' .paginator');
    let page = paginator.find('.current-page');

    if(postData.data == null) {
        postData.data = {};
    }
    postData.data.page = page.text();

    loaderFadeIn();
    $.post(postData.url, postData.data, function(data) {
        if(data.object != null) {
            print(fragment, data.object.data, printData.buttons, printData.handlingLength, printData.dataHandler);

            if(data.object.hasPrevious) {
                paginator.find('.btn-previous').removeAttr('disabled')
            }
            else {
                paginator.find('.btn-previous').attr('disabled', 'disabled')
            }

            if(data.object.hasNext) {
                paginator.find('.btn-next').removeAttr('disabled')
            }
            else {
                paginator.find('.btn-next').attr('disabled', 'disabled')
            }
        }

        if(firstRequest) {
            REQUESTED.push(fragment);

            paginator.find('.btn-previous').click(function(event) {
                event.preventDefault();

                if(this.hasAttribute('disabled')) {
                    return;
                }

                page.text(parseInt(page.text()) - 1);

                makeRequest(fragment, postData, printData, false);
            });
            paginator.find('.btn-next').click(function(event) {
                event.preventDefault();

                if(this.hasAttribute('disabled')) {
                    return;
                }

                page.text(parseInt(page.text()) + 1);

                makeRequest(fragment, postData, printData, false);
            });

            if(data.object != null) {
                paginator.find('.max-page').text(data.object.maxPage)
            }
        }

        loaderFadeOut();
    });
}

/**
 * Prints returned data to the target fragment.
 *
 * @param fragment The fragment.
 * @param data The data.
 * @param buttons The buttons.
 * @param dataHandlingLength How many data you want to handle.
 * @param dataHandler The data handler.
 */

function print(fragment, data, buttons, dataHandlingLength, dataHandler) {
    if(data == null || data.length == 0) {
        return;
    }

    let html = '';
    for(let dataIndex = 0, dataLength = data.length; dataIndex < dataLength; dataIndex++) {
        html += '<tr>';
        for(let dataHandlingIndex = 0; dataHandlingIndex < dataHandlingLength; dataHandlingIndex++) {
            let tag = 'td';
            let attr = dataHandler(data[dataIndex], dataHandlingIndex);
            let value =  escapeHTML(data[dataIndex][attr[0]]);

            let isNone = attr[2] == 'none';
            html += '<' + tag + ' data-' + attr[0].replace('_', '-') + '="' + value + '"' + (isNone ? '' : 'data-editable="true"') + '>';
            if(attr[2] == 'select') {
                html += '<select class="form-control" disabled="disabled">';

                let options = attr[3];
                for(let optionIndex = 0, optionsLength = options.length; optionIndex < optionsLength; optionIndex++) {
                    html += '<option value="' + optionIndex + '"' + (optionIndex == value ? ' selected' : '') + '>' + options[optionIndex] + '</option>';
                }

                html += '</select>';
            }
            else {
                html += '<input class="form-control" type="' + (isNone ? 'text' : attr[2]) + '" value="' + escapeHTML(attr[1]) + '" disabled="disabled"/>';
            }

            html += '</' + tag + '>';
        }
        html += '<td class="buttons">' + buttons + '</td></tr>';
    }
    $('#fragment-' + fragment + ' table tbody').html(html);
}

/**
 * Formats a date.
 *
 * @param date The date.
 *
 * @returns {string} The formatted date.
 */

function formatDate(date) {
    let month = '' + (date.getMonth() + 1);
    let day = '' + date.getDate();

    return date.getFullYear() + '-' + (month.length === 1 ? '0' : '') + month + '-' + (day.length === 1 ? '0' : '') + day;
}

/**
 * Goes to the specified link or, if we're already visiting this link, refreshes the page.
 *
 * @param href The link.
 */

function goToOrReload(href) {
    if(window.location.href.endsWith(href)) {
        window.location.reload();
        return;
    }

    window.location.href = href;
}

/**
 * Escapes an HTML String.
 *
 * @param string The String.
 *
 * @returns {string} The escaped HTML String.
 */

function escapeHTML(string) {
    return String(string).replace(/[&<>"'`=\/]/g, function(s) {
        return ENTITY_MAP[s];
    });
}