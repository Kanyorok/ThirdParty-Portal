window.notyf = new Notyf();
window.isDirty = false;
window.addEventListener('beforeunload', function (event) {
    if (window.isDirty) {
        const confirmationMessage = 'You have unsaved changes. Are you sure you want to leave?';
        // Set the returnValue property to trigger the confirmation dialog
        event.returnValue = confirmationMessage; // For most browsers
        return confirmationMessage; // For some older browsers
    }
});

async function saveForm(form, saveBtn, redirect = false, reset = false, popMessage = true, isEdit = false) {
    const btnContent = saveBtn.html();
    saveBtn.prop('disable', true).addClass('disabled').prop('type', 'button').html('<i class="fas fa-spinner fa-spin"></i> please wait');
    $(".form-control").removeClass('is-invalid');
    $('.error').addClass('d-none');
    window.isDirty = true;
    try {
        return await $.ajax({
            url: form.attr('action'),
            type: 'post',
            dataType: 'json',
            data: form.serialize(),
            success: function (data) {
                if (redirect && data.route !== '') {
                    window.setTimeout(function () {
                        window.location.replace(data.route);
                    }, 3000)
                }
                if (reset) {
                    form.trigger("reset");
                }
                if (popMessage) {
                    nSuccess(data.message);
                }

                return data;
            }/*,
            error: function () {

                return false;
            }*/, complete() {
                saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);
                window.isDirty = false;
            }
        });
    } catch (e) {
        formRequest(e, popMessage, isEdit);
        return false;
    }
}

function printContent(elem, title) {
    var mywindow = window.open('', 'PRINT');

    mywindow.document.write('<html lang="en"><head><title>' + title + '</title>');
    mywindow.document.write('<style>');
    const table_style = document.getElementById("print_style").innerHTML;
    mywindow.document.write(table_style);
    mywindow.document.write('</style>');
    mywindow.document.write('</head><body >');
    mywindow.document.write(document.getElementById(elem).innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    mywindow.close();

    return true;
}

function codeNotify(r) {
    switch (r) {
        case 0:
            nError("Check your internet connection, Connection Lost");
            break;
        case 403:
            nError("Sorry, you don't have the permissions required, Authorization");
            break;
        case 401:
            nError("you have to be logged in to do this.");
            break;
        case 419:
            nWarning("Please login again !, Session Expired");
            window.location.reload();
            break;
        case 404:
            nError("Page has moved permanently");
            break;
        case 412:
            nWarning(" Some Condition failed");
            break;
        case 422:
            nWarning("Form Submitted contains some errors");
            break;
        case 409:
            nSuccess("this is already done, Already Set");
            break;
        case 500:
            nError("Sorry our servers encountered an error");
            break;
        case 503:
            nError("Changing things up to make things way better, Server Maintenance Underway");
            break;
        case 400:

            break;
        default:
            nError("Something went wrong, Please reload , Unknown Error")
    }
}

function formRequest(request, popMessage = true, edit = false) {
    if (popMessage) {
        codeNotify(request.status);
    }
    if (request.status === 422 && request.responseJSON.errors) {
        $.each(request.responseJSON.errors, function (key, value) {
            if (edit) {
                setInvalid('e_' + key,value[0])
            } else {
                setInvalid(key,value[0])
            }
        });
    } else if (request.status === 400 && request.responseJSON.message && popMessage) {
        nWarning(request.responseJSON.message);
    }
}

function setInvalid(element_id, message) {
    $('#' + element_id).addClass('is-invalid');
    $('#' + element_id + '_error').removeClass('d-none').html(message);
}

function clearInvalid(element_id) {
    $('#' + element_id).removeClass('is-invalid');
    $('#' + element_id + '_error').addClass('d-none').html('..');
}

function nSuccess(msg) {
    return window.notyf.open({
        type: "success",
        message: msg,
        duration: 3000,
        ripple: true,
        dismissible: true,
        position: {
            x: "right",
            y: "bottom"
        }
    });
}

function nError(msg) {
    return window.notyf.open({
        type: "error",
        message: msg,
        duration: 3000,
        ripple: true,
        dismissible: true,
        position: {
            x: "right",
            y: "bottom"
        }
    });
}

function nWarning(msg) {
    return window.notyf.open({
        background: "orange",
        message: msg,
        icon: '<i class="fas fa-exclamation-triangle"></i>',
        duration: 3000,
        ripple: true,
        dismissible: true,
        position: {
            x: "right",
            y: "bottom"
        }
    });
}

window.getDocumentUrl = function () {
    let current = document.URL;
    return current.replace('#', '');
}

function updateFields(fields) {
    Object.entries(fields).forEach(([key, value]) => {
        $('.set-' + key).html(value);
    });
}
