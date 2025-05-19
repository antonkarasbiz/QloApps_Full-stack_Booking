/*
* Copyright since 2010 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright Since 2010 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

$(document).ready(function() {
    $(document).on('focusout', '#email', function() {
        var email = $.trim($('#email').val());
        if (email != '') {
            $('.customer_email_msg').hide();
            $.ajax({
                url: customer_controller_url,
                method: 'POST',
                dataType: 'json',
				data: {
                    ajax : 1,
                    id_customer: id_customer,
                    email: email,
                    action: 'verifyCustomerEmail'
                },
                success: function(response) {
                    if (!response.status) {
                        if ($('#email').closest('.input-group').parent().find('.customer_email_msg').length) {
                            $('.customer_email_msg').text(response.msg);
                        } else {
                            $('#email').closest('.input-group').parent().append('<p class="text-danger customer_email_msg">'+ response.msg+'</p>');
                        }

                        $('.customer_email_msg').show();
                    } else {
                        $('.customer_email_msg').hide();
                    }
                }
			});
        }
    });

    $(document).on('click', '.edit-customer-guest-detail', function(e) {
        e.preventDefault();
        GuestModal.show($(this).data('id_customer_guest_detail'));
    });
    $(document).on('click', '.delete-customer-guest-detail', function(e) {
        e.preventDefault();
        if (confirm(confirmTxt)) {
            GuestModal.deleteGuest($(this).data('id_customer_guest_detail'));
        }
    });

    const GuestModal = {
        show: function(id_customer_guest_detail) {
            $("#page-loader").show();
            $('#customer-guest-modal').remove();
            $.ajax({
                url: customer_controller_url,
                method: 'POST',
                dataType: 'json',
                data: {
                    ajax : 1,
                    id_customer_guest_detail: id_customer_guest_detail,
                    action: 'InitGuestModal'
                },
                beforeSend: function() {
                    $("#page-loader").show();
                },
                success: function(result) {
                    if (result.hasError == 0 && result.modalHtml) {
                        $('#footer').next('.bootstrap').append(result.modalHtml);
                        $('#customer-guest-modal').modal('show');
                    } else {
                        showErrorMessage(txtSomeErr);
                    }
                },
                complete: function() {
                    $("#page-loader").hide();
                }
            });
        },
        close: function() {
            $('#customer-guest-modal').modal('hide');
        },
        submit: function() {
            $("#page-loader").show();
            GuestModal.hideErrors();
            $.ajax({
                headers: {
                    "cache-control": "no-cache"
                },
                url: customer_controller_url,
                method: 'POST',
                dataType: 'json',
                cache: false,
                data: $('#customer-guest-details-form').serialize()+'&ajax=true&action=updateGuestDetails',
                success: function(result) {
                    if (result.hasError == true) {
                        GuestModal.showErrors(result.errorsHtml);
                    } else {
                        if (result.msg) {
                            showSuccessMessage(result.msg);
                        }
                        let tr = $('<tr>');
                        tr.append('<td>' + result.data.email + '</td>')
                        .append('<td>' + result.data.firstname + '</td>')
                        .append('<td>' + result.data.lastname + '</td>')
                        .append('<td>' + result.data.phone + '</td>')
                        .append('<td><a class="edit-customer-guest-detail" data-id_customer_guest_detail="'+result.data.id+'"><i class="icon-pencil"></i></a></td>')
                        .append('<td><a class="delete-customer-guest-detail" data-id_customer_guest_detail="'+result.data.id+'"><i class="icon-trash"></i></a></td>');
                        $('.customer_guest_detail_'+result.data.id).html($(tr).html());
                    }
                },
                complete: function() {
                    $("#page-loader").hide();
                }
            });
        },
        deleteGuest: function(id_customer_guest_detail) {
            $("#page-loader").show();
            GuestModal.hideErrors();
            $.ajax({
                headers: {
                    "cache-control": "no-cache"
                },
                url: customer_controller_url,
                method: 'POST',
                dataType: 'json',
                cache: false,
                data: {
                    ajax : 1,
                    id_customer_guest_detail: id_customer_guest_detail,
                    action: 'DeleteGuest'
                },
                success: function(result) {
                    if (result.hasError == true) {
                        showErrorMessage(result.msg);
                    } else if (result.msg) {
                        showSuccessMessage(result.msg);
                        $('.customer_guest_detail_'+id_customer_guest_detail).remove();
                        if ($('.customer_guest_details').length == 0) {
                            $('.customer-guests').closest('.panel').hide();
                        } else {
                            $('.customer-guests-count').text(parseInt($('.customer_guest_details').length));
                        }
                    }
                },
                complete: function() {
                    $("#page-loader").hide();
                }
            });
        },
        showErrors: function(errorsHtml) {
            $('#customer-guest-modal .errors-wrap').html(errorsHtml);
            $('#customer-guest-modal .errors-wrap').show(200);
        },
        hideErrors: function(cb) {
            $('#customer-guest-modal .errors-wrap').hide(200);
            $('#customer-guest-modal .errors-wrap').html('');
        }
    };
    $(document).on('click', '.submitGuestInfoInfo', function(e) {
        e.preventDefault();
        GuestModal.submit();
    });
});
