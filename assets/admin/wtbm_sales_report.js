(function ($) {

    let reportOpener = null;
    $(document).on('click', '.wtbm_get_daily_report', function (event) {
        event.preventDefault();
        reportOpener = this;
        const reports = {
            daily: '#wtbm_dily_sales_report',
            movie: '#wtbm_dily_movies_performance',
            theater: '#wtbm_daily_theater_performance'
        };
        const report = $(reports[$(this).attr('data-wtbm-report')] || reports.daily);
        $('#wtbm_sales_popup .wtbm_sales_report').hide();
        $('#wtbm_sales_popup .wtbm_sales_popup').addClass('wtbm-daily-dialog');
        report.show();
        const title = report.find('h2').first();
        if (!title.attr('id')) title.attr('id', report.attr('id') + '-title');
        $('#wtbm_sales_popup .wtbm_sales_popup').attr('aria-labelledby', title.attr('id'));
        wtbmSalesOpenPopup(report);
    });

    $(document).on('click', '.wtbm_sales_close-btn, .wtbm_sales_close-btn-inline', function () {
        wtbmSalesClosePopup();
    });
    $(document).on('click', '#wtbm_sales_popup', function (event) {
        if (event.target === this) wtbmSalesClosePopup();
    });
    $(document).on('keydown', function (event) {
        const popup = $('#wtbm_sales_popup');
        if (!popup.is(':visible')) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            wtbmSalesClosePopup();
        } else if (event.key === 'Tab') {
            const controls = popup.find('button, a[href], input, select, textarea, [tabindex="0"]').filter(':visible').filter(':enabled');
            const first = controls.first()[0];
            const last = controls.last()[0];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    let bookingRequest = null;
    let bookingSequence = 0;
    let appliedBookingFilters = {};
    let bookingBusy = false;
    let bookingExportBusy = false;
    let bookingExportReady = true;
    function bookingExportButtons() {
        $('#wtbm_booking_data_download_btn, #wtbm_booking_data_csv_btn').prop('disabled', bookingBusy || bookingExportBusy || !bookingExportReady);
    }
    function bookingFilters() {
        return {movie_id: $('#wtbm_movie_filter').val() || '', theater_id: $('#wtbm_theater_filter').val() || '', show_time: $('#wtbm_showtime_filter').val() || '', show_date: $('#wtbm_booking_date_filter').val() || '', booking_status: $('#wtbm_order_status_filter').val() || ''};
    }
    function bookingFeedback(message, error) { $('#wtbm-booking-feedback').text(message).toggleClass('is-error', !!error); }
    function bookingError(response) { return typeof response?.data === 'string' ? response.data : response?.data?.message || 'Unable to load bookings. Please try again.'; }
    function loadBookings(append = false) {
        const sequence = ++bookingSequence;
        if (bookingRequest) bookingRequest.abort();
        const filters = append ? appliedBookingFilters : bookingFilters();
        const loaded = append ? $('#wtbm_bookings_table_body tr[data-order-id]').map(function () { return Number($(this).attr('data-order-id')); }).get() : [];
        bookingBusy = true; bookingExportReady = false;
        $('#wtbm_bookings_content').attr('aria-busy', 'true');
        $('#wtbm_find_booking, #wtbm_booking_load_more_btn, #wtbm_booking_data_download_btn, #wtbm_booking_data_csv_btn').prop('disabled', true);
        bookingFeedback('Loading bookings…');
        bookingRequest = $.ajax({url: mptrs_admin_ajax.ajax_url, type: 'POST', data: {...filters, action: 'wtbm_filter_bookings', nonce: mptrs_admin_ajax.nonce, loaded_ids: loaded}}).done(response => {
            if (sequence !== bookingSequence) return;
            if (!response.success) { bookingFeedback(bookingError(response), true); return; }
            const body = $('#wtbm_bookings_table_body');
            if (append) body.append(response.data.html);
            else body.html(response.data.html || '<tr class="wtbm-booking-empty"><td colspan="10">No bookings found. Try different filters.</td></tr>');
            appliedBookingFilters = {...filters}; bookingExportReady = true;
            const count = body.find('tr[data-order-id]').length;
            const total = Number(response.data.total_booking);
            $('#wtbm_showing_count').text(count); $('#wtbm_total_booking_count').text(total);
            $('#bookings-count').text(total + ' matching booking' + (total === 1 ? '' : 's'));
            $('#showing-info').text(total ? 'Showing 1–' + count + ' of ' + total + ' bookings' : 'No matching bookings');
            $('#wtbm_booking_load_more_btn').prop('hidden', count >= total);
            bookingFeedback(total + ' booking' + (total === 1 ? '' : 's') + ' found. Exports include all matching results.');
        }).fail((xhr, status) => { if (status !== 'abort' && sequence === bookingSequence) bookingFeedback(bookingError(xhr.responseJSON), true); }).always(() => {
            if (sequence !== bookingSequence) return;
            bookingBusy = false; $('#wtbm_bookings_content').removeAttr('aria-busy');
            $('#wtbm_find_booking, #wtbm_booking_load_more_btn').prop('disabled', false); bookingExportButtons();
        });
    }
    $(document).on('click', '#wtbm_find_booking', event => { event.preventDefault(); loadBookings(); });
    $(document).on('change', '#wtbm_booking_filters input, #wtbm_booking_filters select', () => loadBookings());
    $(document).on('click', '#wtbm_clear_find_booking', event => { event.preventDefault(); $('#wtbm_booking_filters').find('input, select').val(''); loadBookings(); });
    $(document).on('click', '#wtbm_booking_load_more_btn', () => loadBookings(true));
    $(document).on('click', '.wtbm_show_filter', function (event) {
        event.preventDefault(); $(this).attr('aria-expanded', String($(this).attr('aria-expanded') !== 'true'));
        $('#wtbm_booking_filters').stop(true, true).slideToggle(180);
    });
    $(document).on('click', '#wtbm_booking_data_download_btn, #wtbm_booking_data_csv_btn', async function (event) {
        event.preventDefault(); if (bookingBusy || bookingExportBusy || !bookingExportReady) return;
        bookingExportBusy = true; bookingExportButtons();
        const button = $(this); const format = this.id === 'wtbm_booking_data_csv_btn' ? 'csv' : 'pdf'; const label = button.text();
        button.prop('disabled', true).text('Preparing…'); bookingFeedback('Preparing ' + format.toUpperCase() + ' for all matching bookings…');
        try {
            const prepared = await $.ajax({url: mptrs_admin_ajax.ajax_url, type: 'POST', data: {...appliedBookingFilters, action: 'wtbm_prepare_booking_' + format, nonce: mptrs_admin_ajax.nonce, filtered: 1}});
            if (!prepared.success) throw new Error(bookingError(prepared));
            const response = await fetch(prepared.data.download_url, {credentials: 'same-origin'});
            const type = response.headers.get('content-type') || '';
            if (!response.ok || !type.includes(format === 'pdf' ? 'application/pdf' : 'text/csv')) throw new Error('Download failed. Please try again.');
            const blob = await response.blob(); if (!blob.size) throw new Error('The export was empty. Please try again.');
            const url = URL.createObjectURL(blob); const link = document.createElement('a');
            link.href = url; link.download = 'theaterly-bookings.' + format;
            document.body.appendChild(link); link.click(); link.remove(); setTimeout(() => URL.revokeObjectURL(url), 30000);
            bookingFeedback(format.toUpperCase() + ' downloaded (' + prepared.data.count + ' bookings).');
        } catch (error) { bookingFeedback(error.message || bookingError(error.responseJSON), true); }
        finally { bookingExportBusy = false; button.text(label); bookingExportButtons(); }
    });

    function wtbmSalesOpenPopup() {
        $('body').addClass('wtbm-report-modal-open');
        $('#wtbm_sales_popup').stop(true, true).fadeIn(150, function () {
            $(this).find('.wtbm_sales_close-btn').trigger('focus');
        });
    }
    function wtbmSalesClosePopup() {
        $('#wtbm_sales_popup').stop(true, true).fadeOut(150, function () {
            $('body').removeClass('wtbm-report-modal-open');
            if (reportOpener && document.contains(reportOpener)) reportOpener.focus();
        });
    }

    $(document).on('click','.wtbm_delete_booking', function(e) {
        let $this = $(this);
        let bookingId = $(this).closest('tr').attr('data-order-id');

        if (!confirm('Are you sure you want to delete this booking?')) {
            return; // stop if cancelled
        }

        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wtbm_delete_booking',
                booking_id: bookingId,
                nonce: mptrs_admin_ajax.nonce
            },
            success: function(response) {
                $this.closest('tr').fadeOut(200);
            }
        });
    });

    let bookingEditorOpener = null;
    function closeBookingEditor() {
        $('.wtbm_booking_edit_overlay').remove();
        $('body').removeClass('wtbm-booking-modal-open');
        if (bookingEditorOpener && document.contains(bookingEditorOpener)) bookingEditorOpener.focus();
    }
    function updateSeatCount() {
        const ids = $('#wtbm_booking_edit_overlay [name="wtbm_booking_seat_ids"]').val() || '';
        $('#wtbm-edit-seat-count').text(ids.split(',').filter(id => id.trim()).length);
    }
    $(document).on('click', '.wtbm_edit_booking', function (event) {
        event.preventDefault();
        bookingEditorOpener = this;
        const trigger = $(this);
        if (trigger.prop('disabled')) return;
        trigger.prop('disabled', true);
        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wtbm_get_booking_html',
                booking_id: trigger.closest('tr').attr('data-order-id'),
                nonce: mptrs_admin_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    $('.wtbm_booking_edit_overlay').remove();
                    $('body').append(response.data.edit_data).addClass('wtbm-booking-modal-open');
                    $('#wtbm_booking_edit_overlay .wtbm_booking_edit_close_icon').trigger('focus');
                } else {
                    alert(response.data || 'Booking could not be loaded.');
                }
            },
            error: function () { alert('Booking could not be loaded. Please try again.'); },
            complete: function () { trigger.prop('disabled', false); }
        });
    });
    $(document).on('click', '.wtbm_booking_edit_close_icon, .wtbm_booking_edit_close_btn', closeBookingEditor);
    $(document).on('click', '#wtbm_booking_edit_overlay', function (event) {
        if (event.target === this) closeBookingEditor();
    });
    $(document).on('keydown', function (event) {
        const editor = $('#wtbm_booking_edit_overlay');
        if (!editor.length) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeBookingEditor();
        } else if (event.key === 'Tab') {
            const controls = editor.find('button, input:not([type="hidden"]), select, textarea, a[href]').filter(':visible').filter(':enabled');
            const first = controls.first()[0];
            const last = controls.last()[0];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault(); last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault(); first.focus();
            }
        }
    });

    $(document).on('click', '#wtbm_update_booking', function (e) {
        e.preventDefault();

        let $body = $('.wtbm_booking_edit_body');
        const updateLabel = $(this).text();

        let data = {
            action: 'wtbm_update_booking_data',
            booking_id: $body.find('.wtbm_booking_edit_id').val(),
            order_id: $body.find('.wtbm_order_edit_id').val(),

            attendee_name:  $body.find('[name="wtbm_booking_attendee_name"]').val(),
            attendee_phone: $body.find('[name="wtbm_booking_attendee_phone"]').val(),
            attendee_email: $body.find('[name="wtbm_booking_attendee_email"]').val(),

            seat_number: $body.find('[name="wtbm_booking_seat_number"]').val(),
            seat_ids: $body.find('[name="wtbm_booking_seat_ids"]').val(),
            booking_status: $body.find('[name="wtbm_booking_status"]').val(),

            movie_id: $body.find('.wtbm_movie_edit_id').val(),
            theater_id: $body.find('.wtbm_theater_edit_id').val(),
            movie_time: $body.find('.wtbm_movie_time').val(),
            nonce: mptrs_admin_ajax.nonce
        };

        $.ajax({
            url: mptrs_admin_ajax.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#wtbm_update_booking').prop('disabled', true).text('Updating...');
            },
            success: function (response) {
                if (response.success) {
                    if (response.data && response.data.payment_url) { window.location.assign(response.data.payment_url); return; }
                    $('.wtbm_booking_edit_overlay').fadeOut(200, function () {
                        $(this).remove();
                    });
                    location.reload();
                } else {
                    alert(response.data || 'Update failed');
                }
            },
            error: function () {
                alert('Something went wrong. Please try again.');
            },
            complete: function () {
                $('#wtbm_update_booking').prop('disabled', false).text(updateLabel);
            }
        });
    });

    $(document).on('click', '.wtbm_get_available_seat', function () {
        const trigger = $(this);
        const label = trigger.text();
        let parent = $("#wtbm_booking_edit_overlay");
        let theaterId = parent.find('input[name="wtbm_edit_theater_id"]').val();
        let movieTimeSlot = parent.find('input[name="wtbm_movie_time_slot"]').val();
        let movieId = parent.find('input[name="wtbm_edit_movie_id"]').val();
        let movieDate = parent.find('input[name="wtbm_movie_order_date"]').val();
        // let movieDate = '2026-03-12';

        $.ajax({
            url: wtbm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wtbm_get_theater_available_seats',
                theater_id: theaterId,
                activeMovieId: movieId,
                movie_time_slot: movieTimeSlot,
                movie_date: movieDate,
                nonce: wtbm_ajax.nonce,
            },
            beforeSend: function () { trigger.prop('disabled', true).text(trigger.attr('data-loading-label')); },
            complete: function () { trigger.prop('disabled', false).text(label); },
            success: function(response) {
                if (response.success && response.data) {
                    $("#wtbm_available_seats").html(response.data.wtbm_seatMaps);
                    if (!$('#wtbm_available_seats .wtbm_add_seat_name').length) {
                        $('#wtbm_available_seats').text(trigger.attr('data-empty-label'));
                    }
                }else{
                    $('#wtbm_available_seats').text(trigger.attr('data-empty-label'));
                }
            },
            error: function(xhr, status, error) {
                $('#wtbm_available_seats').text(trigger.attr('data-error-label'));
            }
        });

        // console.log( theaterId, movieTimeSlot, movieId );
    });

    $(document).on('click', '.remove-seat', function () {
        const seatEl   = $(this).closest('.wtbm_seat_name');
        const seatNum  = seatEl.data('seat').toString();
        const seatId   = seatEl.data('seat-id').toString();

        const seatNumInput = $('#wtbm_booking_edit_overlay input[name="wtbm_booking_seat_number"]');
        let seatNums = seatNumInput.val().split(',').map(s => s.trim());

        seatNums = seatNums.filter(val => val !== seatNum);
        seatNumInput.val(seatNums.join(', '));

        const seatIdInput = $('#wtbm_booking_edit_overlay input[name="wtbm_booking_seat_ids"]');
        let seatIds = seatIdInput.val().split(',').map(s => s.trim());

        seatIds = seatIds.filter(val => val !== seatId);
        seatIdInput.val(seatIds.join(', '));
        seatEl.remove();
        updateSeatCount();
    });

    $(document).on('click', '.wtbm_add_seat_name', function () {

        const seatEl  = $(this);
        const seatNum = String(seatEl.data('seat')).trim();
        const seatId  = String(seatEl.data('seat-id')).trim();

        const seatNumInput = $('#wtbm_booking_edit_overlay input[name="wtbm_booking_seat_number"]');
        let seatNums = seatNumInput.val()
            ? seatNumInput.val().split(',').map(s => s.trim())
            : [];

        const seatIdInput = $('#wtbm_booking_edit_overlay input[name="wtbm_booking_seat_ids"]');
        let seatIds = seatIdInput.val()
            ? seatIdInput.val().split(',').map(s => s.trim())
            : [];
        if (seatIds.includes(seatId)) return;

        if (!seatNums.includes(seatNum)) {
            seatNums.push(seatNum);
        }

        seatNumInput.val(seatNums.join(', '));

        if (!seatIds.includes(seatId)) {
            seatIds.push(seatId);
        }

        seatIdInput.val(seatIds.join(', '));
        seatEl.fadeOut(200);
        const selectedSeat = $('<span>', {class: 'wtbm_seat_name'}).attr({'data-seat-id': seatId, 'data-seat': seatNum}).text(seatNum);
        const removeButton = $('<button>', {type: 'button', class: 'remove-seat'}).attr('aria-label', $('#wtbm-edit-selected-seats').attr('data-remove-label') + ' ' + seatNum).text('×');
        selectedSeat.append(removeButton);
        $('#wtbm-edit-selected-seats').append(selectedSeat);
        updateSeatCount();
    });



}(jQuery));