$(document).ready(function () {
    $("#btn").click(function () {
        sendAjaxForm('result_form', 'ajax_form', 'api/login.php')
    })
});

function sendAjaxForm(result_form, ajax_form, url) {
    result_form = $('#' + result_form);
    ajax_form = $("#" + ajax_form);
    result_form.html('')
    result_form.removeClass();
    result_form.addClass('alert text_center mb-3')

    $.ajax({
        url: url,
        type: "POST",
        dataType: "html",
        data: ajax_form.serialize(),
        success: function (response) {
            let result = $.parseJSON(response);
            if (result.status === 'success') {
                result_form.html('Регистрация закончена');
                result_form.addClass('alert-success');
                ajax_form.remove();
            } else {
                result_form.html(result.message);
                result_form.addClass('alert-warning');
            }
        },
        error: function () {
            $('#result_form').html('Ошибка. Данные не отправлены.');
        }
    });
}
