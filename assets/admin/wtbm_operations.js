(function ($) {
    'use strict';
    const config = window.wtbmOperations;
    if (!config) return;
    const request = data => $.ajax({url: config.ajax, method: 'POST', data: Object.assign({nonce: config.nonce}, data)});
    const errorText = xhr => (xhr.responseJSON && xhr.responseJSON.data) || config.labels.error;
    function paymentFields(group) {
        const method = group.find('.wtbm-payment-method').val();
        group.find('.wtbm-tender-field').prop('hidden', method !== 'wtbm_cash');
        group.find('.wtbm-reference-field').prop('hidden', !['wtbm_card', 'wtbm_manual'].includes(method));
        group.find('.wtbm-tender-field input').prop('required', method === 'wtbm_cash');
        group.find('.wtbm-reference-field input').prop('required', ['wtbm_card', 'wtbm_manual'].includes(method));
        group.find('.wtbm-payment-help').text(method === 'wtbm_cash' ? config.labels.cash : method === 'wtbm_unpaid' ? config.labels.unpaid : config.labels.reference);
    }
    $(document).on('change', '.wtbm-payment-method', function () { paymentFields($(this).closest('.wtbm-payment-fields')); });
    $(function () { $('.wtbm-payment-fields').each(function () { paymentFields($(this)); }); });
    $(document).on('input', '#wtbm-history-search', function () {
        const term = this.value.trim().toLowerCase(); let visible = 0;
        $('.wtbm-history-card tbody tr').each(function () { const match = $(this).text().toLowerCase().includes(term); $(this).prop('hidden', !match); if (match) visible++; });
        $('.wtbm-history-no-match').prop('hidden', visible > 0);
    });
    $(document).on('submit', '#wtbm-history-retention', function (event) {
        event.preventDefault();
        const form = $(this), feedback = $('#wtbm-history-feedback');
        const button = form.find('button').prop('disabled', true);
        feedback.text('Saving cleanup settings…');
        request({action: 'wtbm_history', verb: 'save', days: form.find('[name=days]').val(), time: form.find('[name=time]').val(), enabled: form.find('[name=enabled]').is(':checked') ? 1 : 0}).done(response => {
            feedback.text(response.success ? response.data.message : response.data);
            if (response.success) $('#wtbm-history-next').text(response.data.next);
        }).fail(xhr => feedback.text(errorText(xhr))).always(() => button.prop('disabled', false));
    });
    $(document).on('click', '#wtbm-history-clear', async function () {
        const button = $(this).prop('disabled', true), feedback = $('#wtbm-history-feedback');
        feedback.text('Checking entries to clear…');
        try {
            const preview = await request({action: 'wtbm_history', verb: 'preview', range: $('#wtbm-history-range').val()});
            if (!preview.success) throw new Error(preview.data);
            if (!preview.data.count) { feedback.text('No history entries match this range.'); return; }
            if (!window.confirm('Permanently clear ' + preview.data.count + ' history entries? This cannot be undone. Bookings, payments and check-in records will be kept.')) { feedback.text('Clearing cancelled.'); return; }
            const cleared = await request({action: 'wtbm_history', verb: 'clear', token: preview.data.token});
            if (!cleared.success) throw new Error(cleared.data);
            $('.wtbm-history-card tr[data-event-id]').each(function () {
                if (Number($(this).attr('data-event-id')) <= cleared.data.max && (!cleared.data.cutoff || $(this).attr('data-created') < cleared.data.cutoff)) $(this).remove();
            });
            feedback.text(cleared.data.count + ' history entries cleared.');
            $('#wtbm-history-search').trigger('input');
            if (!$('.wtbm-history-card tr[data-event-id]').length) $('.wtbm-history-card tbody').html('<tr><td colspan="4" class="wtbm-history-empty">No recent activity. New events will appear here.</td></tr>');
        } catch (error) { feedback.text(error.message || errorText(error)); }
        finally { button.prop('disabled', false); }
    });
    function filters() {
        return {from: $('#wtbm-report-from').val(), to: $('#wtbm-report-to').val(), basis: $('#wtbm-report-basis').val(), movie: $('#wtbm-report-movie').val(), theater: $('#wtbm-report-theater').val(), status: $('#wtbm-report-status').val()};
    }
    $(document).on('click', '#wtbm-generate-report, [data-wtbm-report="payment"]', function (event) {
        event.preventDefault();
        const button = $(this).prop('disabled', true);
        const output = $('#wtbm-period-report').text(config.labels.loading);
        request(Object.assign({action: 'wtbm_operations_report'}, filters())).done(response => {
            if (response.success) { output.html(response.data.html); output[0].scrollIntoView({behavior: 'smooth', block: 'start'}); }
            else output.text(response.data);
        }).fail(xhr => output.text(errorText(xhr))).always(() => button.prop('disabled', false));
    });
    $(document).on('click', '.wtbm-report-export', function (event) {
        event.preventDefault();
        const url = new URL(config.exportUrl);
        Object.entries(filters()).forEach(([key, value]) => url.searchParams.set(key, value || ''));
        url.searchParams.set('format', $(this).attr('data-format'));
        window.open(url.toString(), '_blank', 'noopener');
    });
    let camera = null, scanning = false, timer = null;
    function stopCamera() {
        scanning = false;
        if (timer) clearTimeout(timer);
        if (camera) camera.getTracks().forEach(track => track.stop());
        camera = null;
        $('#wtbm-stop-scanner').prop('disabled', true);
        $('#wtbm-start-scanner').prop('disabled', false);
        const video = document.getElementById('wtbm-scanner-video');
        if (video) { video.srcObject = null; video.hidden = true; }
    }
    function admit() {
        const button = $('#wtbm-admit').prop('disabled', true);
        const output = $('#wtbm-admit-result').text(config.labels.loading).removeClass('wtbm-ops-error wtbm-ops-success');
        return request({action: 'wtbm_admit_ticket', token: $('#wtbm-admit-token').val(), theater: $('#wtbm-admit-theater').val(), show_date: $('#wtbm-admit-date').val()}).done(response => {
            output.toggleClass('wtbm-ops-success', response.success).toggleClass('wtbm-ops-error', !response.success).text(response.success ? response.data.message + ' ' + response.data.movie + ' · ' + response.data.time + ' · ' + (response.data.seat_names || response.data.seats).join(', ') : response.data);
        }).fail(xhr => output.addClass('wtbm-ops-error').text(errorText(xhr))).always(() => button.prop('disabled', false));
    }
    $(document).on('click', '#wtbm-admit', admit);
    $(document).on('click', '#wtbm-stop-scanner', stopCamera);
    $(document).on('click', '#wtbm-start-scanner', async function () {
        stopCamera();
        try {
            if (!navigator.mediaDevices) throw new Error(config.labels.camera);
            if (!window.jsQR) await $.getScript(config.scannerUrl);
            camera = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'environment'}, audio: false});
            const video = document.getElementById('wtbm-scanner-video');
            video.srcObject = camera; video.hidden = false; await video.play(); scanning = true; $('#wtbm-stop-scanner').prop('disabled', false); $('#wtbm-start-scanner').prop('disabled', true);
            const canvas = document.createElement('canvas'), context = canvas.getContext('2d', {willReadFrequently: true});
            function frame() {
                if (!scanning) return;
                if (video.readyState >= 2) {
                    canvas.width = Math.min(640, video.videoWidth); canvas.height = Math.round(canvas.width * video.videoHeight / video.videoWidth);
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const raw = context.getImageData(0, 0, canvas.width, canvas.height);
                    const code = window.jsQR(raw.data, raw.width, raw.height);
                    if (code) { $('#wtbm-admit-token').val(code.data); stopCamera(); admit(); return; }
                }
                timer = setTimeout(frame, 180);
            }
            frame();
        } catch (error) { stopCamera(); $('#wtbm-admit-result').addClass('wtbm-ops-error').text(config.labels.camera); }
    });
    window.addEventListener('pagehide', stopCamera);
    document.addEventListener('visibilitychange', () => { if (document.hidden) stopCamera(); });
    // Reschedule forms display human-readable seat names; IDs remain in hidden fields.
    function loadChange(form) {
        const panel = form.find('.wtbm-change-seats');
        const payload = {action: 'wtbm_change_options', nonce: config.bookingNonce, movie: form.find('[name=movie]').val(), theater: form.find('[name=theater]').val(), date: form.find('[name=date]').val(), booking: form.find('[name=booking]').val(), order_key: form.find('[name=order_key]').val(), time: form.find('[name=time]').val()};
        panel.text(config.labels.loading);
        request(payload).done(response => {
            if (!response.success) { panel.text(response.data); return; }
            const times = form.find('[name=time]'), previous = times.val();
            times.empty(); response.data.times.forEach(time => times.append($('<option>').val(time).text(time))); times.val(response.data.selected_time || previous);
            panel.empty();
            response.data.seats.forEach(seat => panel.append($('<label>').append($('<input>', {type: 'checkbox', class: 'wtbm-change-seat'}).val(seat.id), $('<span>').text(seat.name))));
            form.find('[name=seat_ids]').val('');
            form.find('.wtbm-change-help').text(response.data.message || '');
        }).fail(xhr => panel.text(errorText(xhr)));
    }
    $(document).on('change', '.wtbm-ticket-form [name=movie], .wtbm-ticket-form [name=theater], .wtbm-ticket-form [name=date], .wtbm-ticket-form [name=time]', function () { loadChange($(this).closest('form')); });
    $(document).on('change', '.wtbm-change-seat', function () { const form = $(this).closest('form'); form.find('[name=seat_ids]').val(form.find('.wtbm-change-seat:checked').map(function () { return this.value; }).get().join(',')); });
    $(document).on('click', '.wtbm-reschedule summary', function () { const details = $(this).closest('details'); if (!details.attr('data-loaded')) { details.attr('data-loaded', '1'); loadChange($(this).closest('form')); } });
    // Customer dashboard actions use the same booking services as the box office.
    function filterTickets() {
        const filter = $('.wtbm-ticket-toolbar [data-ticket-filter].is-active').attr('data-ticket-filter') || 'all';
        const term = ($('.wtbm-ticket-search').val() || '').trim().toLowerCase();
        let visible = 0;
        $('.wtbm-customer-ticket').each(function () { const card = $(this); const match = (filter === 'all' || card.attr('data-ticket-state') === filter) && card.text().toLowerCase().includes(term); card.prop('hidden', !match); if (match) visible++; });
        $('.wtbm-ticket-empty').prop('hidden', visible > 0);
    }
    $(document).on('click', '[data-ticket-filter]', function () { $('[data-ticket-filter]').removeClass('is-active').attr('aria-pressed', 'false'); $(this).addClass('is-active').attr('aria-pressed', 'true'); filterTickets(); });
    $(document).on('input', '.wtbm-ticket-search', filterTickets);
    $(document).on('click', '.wtbm-show-entry-qr', function () {
        const button = $(this), card = button.closest('.wtbm-customer-ticket'), form = card.find('.wtbm-ticket-form'), qr = card.find('.wtbm-entry-qr');
        if (qr.children().length) { qr.prop('hidden', !qr.prop('hidden')); return; }
        button.prop('disabled', true);
        request({action: 'wtbm_ticket_qr', nonce: form.find('[name=nonce]').val(), booking: button.attr('data-booking'), order_key: form.find('[name=order_key]').val()}).done(response => {
            if (response.success) { qr.empty().append($('<img>', {src: response.data.image, alt: 'Ticket entry QR'}), $('<figcaption>').text('Show this QR at admission. It covers all active seats in this booking.'), $('<code>').text(response.data.code)).prop('hidden', false); }
            else card.find('.wtbm-ticket-feedback').text(response.data);
        }).fail(xhr => card.find('.wtbm-ticket-feedback').text(errorText(xhr))).always(() => button.prop('disabled', false));
    });
    $(document).on('submit', '.wtbm-ticket-form', function (event) {
        event.preventDefault();
        const form = $(this), card = form.closest('.wtbm-customer-ticket'), submitter = event.originalEvent && event.originalEvent.submitter;
        if (!submitter) return;
        const verb = submitter.value, feedback = form.find('.wtbm-ticket-feedback').removeClass('wtbm-ops-error wtbm-ops-success');
        if (verb === 'cancel' && !form.find('[name="seats[]"]:checked').length) { feedback.addClass('wtbm-ops-error').text('Choose the seats you want to cancel.'); return; }
        if (verb === 'reschedule' && !form.find('[name=seat_ids]').val()) { feedback.addClass('wtbm-ops-error').text('Choose available seats for the new show.'); return; }
        const values = form.serializeArray(); values.push({name: 'verb', value: verb});
        const buttons = form.find('button').prop('disabled', true);
        feedback.text(config.labels.loading);
        $.ajax({url: config.ajax, method: 'POST', data: values}).done(response => {
            if (!response.success) { feedback.addClass('wtbm-ops-error').text(response.data); return; }
            feedback.addClass('wtbm-ops-success').text(response.data.message || 'Ticket updated.');
            if (response.data.payment_url) { window.location.assign(response.data.payment_url); return; }
            if (verb !== 'resend') { const url = new URL(window.location.href); url.searchParams.set('order_id', card.attr('data-order')); const key = form.find('[name=order_key]').val(); if (key) url.searchParams.set('order_key', key); window.location.assign(url.toString()); }
        }).fail(xhr => feedback.addClass('wtbm-ops-error').text(errorText(xhr))).always(() => buttons.prop('disabled', false));
    });
    let pricingPreviewRequest = null;
    function refreshPricingDates() {
        const cards = $('.wtbm_booking_date_date_card');
        if (!cards.length) return;
        if (pricingPreviewRequest) pricingPreviewRequest.abort();
        pricingPreviewRequest = request({action: 'wtbm_pricing_dates', nonce: config.bookingNonce, dates: cards.map(function () { return $(this).attr('data-date'); }).get(), theater: $('#wtbm_summeryTheaterId').val(), time: $('#wtbm_summeryTime').val(), seats: $('#wtbm_summaryQuantity').text() || 0}).done(response => {
            if (!response.success) return;
            cards.each(function () { $(this).find('.wtbm-date-pricing').html(response.data[$(this).attr('data-date')] || ''); });
        });
    }
    $(refreshPricingDates);
    $(document).on('wtbm:pricing-reset', function () { clearTimeout(holdDelay); ++holdSequence; });
    $(document).ajaxComplete(function (event, xhr, settings) {
        const action = typeof settings.data === 'string' ? new URLSearchParams(settings.data).get('action') : (settings.data || {}).action;
        if (['wtbm_get_movies_data_by_date', 'wtbm_get_theater_show_time_data', 'wtbm_get_single_theater_show_time_data', 'wtbm_get_theater_seat_map_data', 'wtbm_hold_seats'].includes(action)) refreshPricingDates();
    });
    function showPricing(data) {
        const pricing = data.pricing;
        let panel = $('.wtbm-pricing-breakdown');
        if (!panel.length) panel = $('<div>', {class: 'wtbm-pricing-breakdown', role: 'status'}).insertAfter($('#wtbm_summaryTotal').closest('.wtbm_registrationSummaryItem'));
        panel.empty();
        if (!pricing || !pricing.rules.length) { panel.hide(); return; }
        panel.show().append($('<div>', {class: 'wtbm-pricing-line'}).append($('<span>').text(config.labels.basePrice), $('<strong>').html(data.formatted_base)));
        const discount = pricing.percent < 0;
        panel.append($('<div>', {class: 'wtbm-pricing-line ' + (discount ? 'is-discount' : 'is-increase')}).append($('<span>').text((discount ? '↓ ' + config.labels.discount : '↑ ' + config.labels.increase) + ' (' + Math.abs(pricing.percent) + '%)'), $('<strong>').html((discount ? '−' : '+') + data.formatted_adjustment)));
        panel.append($('<small>').text(pricing.rules.map(rule => rule.name).join(' · ')));
    }
    $(document).on('click', '.wtbm_booking_date_date_card, .wtbm_booking_movie_card, .wtbm_mappedSeat', function () { $('.wtbm-pricing-breakdown').empty().hide(); });
    let holdDelay = null, holdSequence = 0;
    $(document).on('click', '.wtbm_mappedSeat', function () {
        clearTimeout(holdDelay);
        const sequence = ++holdSequence;
        holdDelay = setTimeout(() => {
            const ids = ($('#wtbm_summerySeatIds').val() || '').split(',').map(id => id.trim()).filter(Boolean);
            let status = $('.wtbm-hold-status');
            if (!status.length) status = $('<div>', {class: 'wtbm-hold-status', role: 'status'}).insertAfter($('#wtbm_summaryTotal').closest('.wtbm_registrationSummaryItem'));
            if (!ids.length) { $('.wtbm-pricing-breakdown').empty().hide(); refreshPricingDates(); status.empty(); request({action: 'wtbm_release_hold', nonce: config.bookingNonce}); return; }
            status.show().text(config.labels.loading);
            request({action: 'wtbm_hold_seats', nonce: config.bookingNonce, movie_id: $('#wtbm_summeryMovieId').val(), theater_id: $('#wtbm_summeryTheaterId').val(), booking_date: $('#wtbm_summeryDate').val(), booking_time: $('#wtbm_summeryTime').val(), booked_seat_ids: JSON.stringify(ids)}).done(response => {
                if (sequence !== holdSequence) return;
                if (response.success) { status.text(config.labels.hold + ' ' + new Date(response.data.expires_at * 1000).toLocaleTimeString()); $('#wtbm_summaryTotal').html(response.data.formatted_price).siblings('.wtbm_currency').hide(); $('#wtbm_summeryTotalAmount').val(response.data.price); showPricing(response.data); }
                else status.text(response.data);
            }).fail(xhr => { if (sequence === holdSequence) status.text(errorText(xhr)); });
        }, 450);
    });
})(jQuery);
