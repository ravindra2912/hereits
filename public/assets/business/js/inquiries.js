/**
 * Business Inquiries Module JavaScript
 */

$(function () {
    'use strict';

    const config = window.InquiriesConfig || {};
    let activeSystemStatus = '';

    // Initialize DataTables
    const inquiriesTable = $('#inquiries-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: config.routes.index,
            data: function (d) {
                d.system_status = activeSystemStatus;
                d.business_status = $('#filter_business_status').val();
                d.inquiry_type = $('#filter_inquiry_type').val();
                d.assigned_to = $('#filter_assigned_to').val();
            }
        },
        columns: [
            { data: 'id_date', name: 'id', width: '90px' },
            { data: 'type_and_source', name: 'inquiry_type' },
            { data: 'customer_info', name: 'name' },
            { data: 'system_status_badge', name: 'system_status', className: 'text-center' },
            { data: 'business_status_badge', name: 'business_status', className: 'text-center' },
            { data: 'assigned_user', name: 'assigned_to', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
        ],
        order: [[0, 'desc']],
        pageLength: 15,
        language: {
            emptyTable: 'No inquiries found matching criteria.',
            processing: '<div class="spinner-border text-primary spinner-border-sm me-2"></div>Loading inquiries...'
        }
    });

    // Filter tab clicks
    $('.filter-status-tab').on('click', function (e) {
        e.preventDefault();
        $('.filter-status-tab').removeClass('active');
        $(this).addClass('active');
        activeSystemStatus = $(this).data('status') || '';
        inquiriesTable.ajax.reload();
    });

    // Dropdown filters change
    $('#filter_business_status, #filter_inquiry_type, #filter_assigned_to').on('change', function () {
        inquiriesTable.ajax.reload();
    });

    $('#btn_reset_filters').on('click', function () {
        $('#filter_business_status').val('');
        $('#filter_inquiry_type').val('');
        $('#filter_assigned_to').val('');
        $('.filter-status-tab').removeClass('active');
        $('.filter-status-tab[data-status=""]').addClass('active');
        activeSystemStatus = '';
        inquiriesTable.ajax.reload();
    });

    // Helper: update live credit balance pill
    function updateCreditDisplay(newBalance) {
        if (typeof newBalance !== 'undefined' && newBalance !== null) {
            $('.available-credit-amount').text(parseFloat(newBalance).toFixed(2));
            config.availableCredit = parseFloat(newBalance);
        }
    }

    // 1. View Details Modal
    $(document).on('click', '.btn-view-inquiry', function () {
        const id = $(this).data('id');
        const url = config.routes.show.replace(':id', id);

        $.get(url, function (res) {
            if (res.status === 'success') {
                const data = res.data;
                renderViewDetailsModal(data);
                $('#viewInquiryModal').modal('show');
            }
        }).fail(function (xhr) {
            showToast('error', xhr.responseJSON?.message || 'Failed to load inquiry details.');
        });
    });

    function renderViewDetailsModal(d) {
        $('#view_inquiry_id_text').text('#' + d.id);
        $('#view_inquiry_created_at').text(d.created_at || 'N/A');

        // Customer Details Card
        if (d.is_accepted) {
            $('#view_customer_locked_notice').addClass('d-none');
            $('#view_customer_unlocked_section').removeClass('d-none');
            $('#view_customer_name').text(d.name || 'N/A');
            $('#view_customer_contact').text(d.contact || 'N/A');
            $('#view_customer_contact_call').attr('href', 'tel:' + d.contact);

            const cleanPhone = (d.contact || '').replace(/[^0-9]/g, '');
            $('#view_customer_contact_wa').attr('href', 'https://wa.me/91' + cleanPhone);

            if (d.email) {
                $('#view_customer_email_row').removeClass('d-none');
                $('#view_customer_email').text(d.email);
                $('#view_customer_email_link').attr('href', 'mailto:' + d.email);
            } else {
                $('#view_customer_email_row').addClass('d-none');
            }

            $('#view_accepted_at_badge').removeClass('d-none').text('Accepted on: ' + (d.accepted_at || ''));
        } else {
            $('#view_customer_unlocked_section').addClass('d-none');
            $('#view_customer_locked_notice').removeClass('d-none');
            $('#view_locked_masked_name').text(d.name || 'Protected');
            $('#view_locked_masked_contact').text(d.contact || 'Protected');
            $('#view_accepted_at_badge').addClass('d-none');

            // Set ID on accept button inside modal
            $('#btn_modal_accept_inquiry').data('id', d.id).data('type', d.inquiry_type).data('charge', d.inquiry_charge || 1.0);
            $('#btn_modal_reject_inquiry').data('id', d.id);
            $('#btn_modal_not_fulfilled_inquiry').data('id', d.id);

            if (d.inquiry_charge) {
                $('#view_locked_charge_text').text(parseFloat(d.inquiry_charge).toFixed(2) + ' credit(s)');
            }
        }

        // Status badges
        $('#view_system_status').text(d.system_status.replace('_', ' ').toUpperCase());
        $('#view_business_status').text(d.is_accepted ? d.business_status.replace('_', ' ').toUpperCase() : 'PENDING ACCEPTANCE');
        $('#view_assigned_staff').text(d.assigned_user || 'Unassigned');

        // Next follow up
        if (d.next_follow_up_formatted) {
            $('#view_follow_up_row').removeClass('d-none');
            $('#view_follow_up_text').text(d.next_follow_up_formatted);
        } else {
            $('#view_follow_up_row').addClass('d-none');
        }

        // Message
        $('#view_inquiry_message').text(d.message || 'No additional description provided by customer.');

        // Sources list
        const sourcesContainer = $('#view_inquiry_sources_list').empty();
        if (d.sources && d.sources.length > 0) {
            d.sources.forEach(function (s) {
                const iconClass = s.source_type === 'product' ? 'bi-box-seam text-info' : 'bi-tools text-primary';
                const bgClass = s.source_type === 'product' ? 'bg-info bg-opacity-10' : 'bg-primary bg-opacity-10';
                const priceFormatted = s.price ? 'Rs. ' + parseFloat(s.price).toFixed(2) : 'Price on request';

                sourcesContainer.append(`
                    <div class="inquiry-source-card d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="inquiry-source-icon ${bgClass}">
                                <i class="bi ${iconClass}"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">${escapeHtml(s.name)}</div>
                                <div class="small text-muted text-capitalize">${escapeHtml(s.source_type)}</div>
                            </div>
                        </div>
                        <div class="fw-bold text-dark">${priceFormatted}</div>
                    </div>
                `);
            });
        } else {
            sourcesContainer.html('<div class="text-muted small fst-italic">Direct inquiry (Contact Us).</div>');
        }

        // Footer buttons logic
        if (d.system_status === 'pending') {
            $('#view_modal_pending_actions').removeClass('d-none');
            $('#view_modal_accepted_actions').addClass('d-none');
        } else if (d.system_status === 'accepted') {
            $('#view_modal_pending_actions').addClass('d-none');
            $('#view_modal_accepted_actions').removeClass('d-none');
            $('#btn_modal_quick_status').data('id', d.id).data('status', d.business_status);
            $('#btn_modal_quick_assign').data('id', d.id).data('assigned', d.assigned_to || '');
            $('#btn_modal_quick_logs').data('id', d.id);
        } else {
            $('#view_modal_pending_actions').addClass('d-none');
            $('#view_modal_accepted_actions').addClass('d-none');
        }
    }

    // 2. Accept Inquiry Modal & Action
    $(document).on('click', '.btn-accept-inquiry, #btn_modal_accept_inquiry', function () {
        const id = $(this).data('id');
        const inquiryType = $(this).data('type') || 'contact_us';
        let charge = $(this).data('charge');
        if (typeof charge === 'undefined' || charge === null || charge === '') {
            charge = (config.inquiryCharges && config.inquiryCharges[inquiryType]) ? config.inquiryCharges[inquiryType] : 1.0;
        }

        $('#accept_inquiry_id').val(id);
        $('#accept_inquiry_remarks').val('');

        const available = parseFloat(config.availableCredit || 0);
        const required = parseFloat(charge);

        $('#accept_required_credits').text(required.toFixed(2));
        $('#accept_available_credits').text(available.toFixed(2));

        if (available < required) {
            $('#accept_insufficient_warning').removeClass('d-none');
            $('#btn_confirm_accept').prop('disabled', true);
        } else {
            $('#accept_insufficient_warning').addClass('d-none');
            $('#btn_confirm_accept').prop('disabled', false);
        }

        if ($('#viewInquiryModal').hasClass('show')) {
            $('#viewInquiryModal').modal('hide');
        }
        $('#acceptInquiryModal').modal('show');
    });

    $('#form_accept_inquiry').on('submit', function (e) {
        e.preventDefault();
        const id = $('#accept_inquiry_id').val();
        const url = config.routes.accept.replace(':id', id);
        const submitBtn = $('#btn_confirm_accept');

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Accepting...');

        $.post(url, $(this).serialize(), function (res) {
            if (res.status === 'success') {
                showToast('success', res.message);
                updateCreditDisplay(res.available_credit);
                $('#acceptInquiryModal').modal('hide');
                inquiriesTable.ajax.reload(null, false);
            }
        }).fail(function (xhr) {
            showToast('error', xhr.responseJSON?.message || 'Failed to accept inquiry.');
        }).always(function () {
            submitBtn.prop('disabled', false).html('<i class="bi bi-unlock-fill me-1"></i>Confirm & Deduct Credit');
        });
    });

    // 3. Reject Inquiry Modal & Action
    $(document).on('click', '.btn-reject-inquiry, #btn_modal_reject_inquiry', function () {
        const id = $(this).data('id');
        $('#reject_inquiry_id').val(id);
        $('#reject_inquiry_remarks').val('');

        if ($('#viewInquiryModal').hasClass('show')) {
            $('#viewInquiryModal').modal('hide');
        }
        $('#rejectInquiryModal').modal('show');
    });

    $('#form_reject_inquiry').on('submit', function (e) {
        e.preventDefault();
        const id = $('#reject_inquiry_id').val();
        const url = config.routes.reject.replace(':id', id);
        const submitBtn = $('#btn_confirm_reject');

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Rejecting...');

        $.post(url, $(this).serialize(), function (res) {
            if (res.status === 'success') {
                showToast('success', res.message);
                $('#rejectInquiryModal').modal('hide');
                inquiriesTable.ajax.reload(null, false);
            }
        }).fail(function (xhr) {
            showToast('error', xhr.responseJSON?.message || 'Failed to reject inquiry.');
        }).always(function () {
            submitBtn.prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>Reject Inquiry');
        });
    });

    // 4. Not Fulfilled Modal & Action
    $(document).on('click', '.btn-not-fulfilled-inquiry, #btn_modal_not_fulfilled_inquiry', function () {
        const id = $(this).data('id');
        $('#not_fulfilled_inquiry_id').val(id);
        $('#not_fulfilled_inquiry_remarks').val('');

        if ($('#viewInquiryModal').hasClass('show')) {
            $('#viewInquiryModal').modal('hide');
        }
        $('#notFulfilledInquiryModal').modal('show');
    });

    $('#form_not_fulfilled_inquiry').on('submit', function (e) {
        e.preventDefault();
        const id = $('#not_fulfilled_inquiry_id').val();
        const url = config.routes.notFulfilled.replace(':id', id);
        const submitBtn = $('#btn_confirm_not_fulfilled');

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.post(url, $(this).serialize(), function (res) {
            if (res.status === 'success') {
                showToast('success', res.message);
                $('#notFulfilledInquiryModal').modal('hide');
                inquiriesTable.ajax.reload(null, false);
            }
        }).fail(function (xhr) {
            showToast('error', xhr.responseJSON?.message || 'Failed to update inquiry.');
        }).always(function () {
            submitBtn.prop('disabled', false).html('<i class="bi bi-slash-circle me-1"></i>Mark Not Fulfilled');
        });
    });

    // 5. Update Business Sales Status Modal & Action
    $(document).on('click', '.btn-status-inquiry, #btn_modal_quick_status', function () {
        const id = $(this).data('id');
        const currentStatus = $(this).data('status') || 'new';

        $('#status_inquiry_id').val(id);
        $('#status_inquiry_select').val(currentStatus);
        $('#status_inquiry_next_follow_up').val('');
        $('#status_inquiry_remarks').val('');

        toggleFollowUpField(currentStatus);

        if ($('#viewInquiryModal').hasClass('show')) {
            $('#viewInquiryModal').modal('hide');
        }
        $('#statusInquiryModal').modal('show');
    });

    $('#status_inquiry_select').on('change', function () {
        toggleFollowUpField($(this).val());
    });

    function toggleFollowUpField(status) {
        if (status === 'follow_up' || status === 'scheduled') {
            $('#status_follow_up_container').removeClass('d-none');
        } else {
            $('#status_follow_up_container').addClass('d-none');
        }
    }

    $('#form_status_inquiry').on('submit', function (e) {
        e.preventDefault();
        const id = $('#status_inquiry_id').val();
        const url = config.routes.businessStatus.replace(':id', id);
        const submitBtn = $('#btn_confirm_status');

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');

        $.post(url, $(this).serialize(), function (res) {
            if (res.status === 'success') {
                showToast('success', res.message);
                $('#statusInquiryModal').modal('hide');
                inquiriesTable.ajax.reload(null, false);
            }
        }).fail(function (xhr) {
            showToast('error', xhr.responseJSON?.message || 'Failed to update status.');
        }).always(function () {
            submitBtn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Update Status');
        });
    });

    // 6. Assign Staff Modal & Action
    $(document).on('click', '.btn-assign-inquiry, #btn_modal_quick_assign', function () {
        const id = $(this).data('id');
        const assigned = $(this).data('assigned') || '';

        $('#assign_inquiry_id').val(id);
        $('#assign_inquiry_staff_select').val(assigned);
        $('#assign_inquiry_remarks').val('');

        if ($('#viewInquiryModal').hasClass('show')) {
            $('#viewInquiryModal').modal('hide');
        }
        $('#assignInquiryModal').modal('show');
    });

    $('#form_assign_inquiry').on('submit', function (e) {
        e.preventDefault();
        const id = $('#assign_inquiry_id').val();
        const url = config.routes.assign.replace(':id', id);
        const submitBtn = $('#btn_confirm_assign');

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Assigning...');

        $.post(url, $(this).serialize(), function (res) {
            if (res.status === 'success') {
                showToast('success', res.message);
                $('#assignInquiryModal').modal('hide');
                inquiriesTable.ajax.reload(null, false);
            }
        }).fail(function (xhr) {
            showToast('error', xhr.responseJSON?.message || 'Failed to assign staff.');
        }).always(function () {
            submitBtn.prop('disabled', false).html('<i class="bi bi-person-check me-1"></i>Save Assignment');
        });
    });

    // 7. Timeline / Logs & Notes Modal
    $(document).on('click', '.btn-logs-inquiry, #btn_modal_quick_logs', function () {
        const id = $(this).data('id');
        $('#timeline_inquiry_id').val(id);
        $('#note_inquiry_remarks').val('');

        loadTimelineLogs(id);

        if ($('#viewInquiryModal').hasClass('show')) {
            $('#viewInquiryModal').modal('hide');
        }
        $('#timelineInquiryModal').modal('show');
    });

    function loadTimelineLogs(id) {
        const url = config.routes.logs.replace(':id', id);
        const container = $('#timeline_logs_container').html('<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Loading history...</div>');

        $.get(url, function (res) {
            if (res.status === 'success') {
                const logs = res.data;
                container.empty();

                if (!logs || logs.length === 0) {
                    container.html('<div class="text-muted text-center py-4">No activity history recorded yet.</div>');
                    return;
                }

                logs.forEach(function (log) {
                    const actionTitle = log.action.replace(/_/g, ' ').toUpperCase();
                    let changeText = '';
                    if (log.old_value || log.new_value) {
                        changeText = `<div class="small text-muted mt-1">Status: <span class="fw-medium">${escapeHtml(log.old_value || 'None')}</span> &rarr; <span class="fw-bold text-dark">${escapeHtml(log.new_value || 'None')}</span></div>`;
                    }

                    const remarksHtml = log.remarks ? `<div class="mt-1 small text-dark">${escapeHtml(log.remarks)}</div>` : '';

                    container.append(`
                        <div class="inquiry-timeline-item">
                            <div class="inquiry-timeline-dot dot-${escapeHtml(log.action)}"></div>
                            <div class="inquiry-timeline-content">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                    <span class="badge bg-light text-dark border px-2 py-0.5 fw-bold">${escapeHtml(actionTitle)}</span>
                                    <span class="small text-muted">${escapeHtml(log.created_at || '')}</span>
                                </div>
                                ${changeText}
                                ${remarksHtml}
                                <div class="small text-muted mt-1"><i class="bi bi-person me-1"></i>By: ${escapeHtml(log.user_name || 'System')}</div>
                            </div>
                        </div>
                    `);
                });
            }
        }).fail(function () {
            container.html('<div class="text-danger text-center py-3">Failed to load timeline.</div>');
        });
    }

    $('#form_add_note').on('submit', function (e) {
        e.preventDefault();
        const id = $('#timeline_inquiry_id').val();
        const url = config.routes.addNote.replace(':id', id);
        const submitBtn = $('#btn_save_note');

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.post(url, $(this).serialize(), function (res) {
            if (res.status === 'success') {
                showToast('success', res.message);
                $('#note_inquiry_remarks').val('');
                loadTimelineLogs(id);
            }
        }).fail(function (xhr) {
            showToast('error', xhr.responseJSON?.message || 'Failed to add note.');
        }).always(function () {
            submitBtn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Add Note');
        });
    });

    // Toast helper
    function showToast(type, message) {
        if (typeof toastr !== 'undefined') {
            if (type === 'success') toastr.success(message);
            else if (type === 'error') toastr.error(message);
            else toastr.info(message);
        } else {
            alert(message);
        }
    }

    // Escape HTML helper
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});

