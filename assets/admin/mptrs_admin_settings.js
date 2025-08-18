function mptrs_load_sortable_datepicker(parent, item) {
    if (parent.find('.item_insert_before').length > 0) {
        jQuery(item).insertBefore(parent.find('.item_insert_before').first()).promise().done(function () {
            parent.find('.sortable_area').sortable({
                handle: jQuery(this).find('.sortable_button')
            });
            mptrs_load_date_picker(parent);
        });
    } else {
        parent.find('.item_insert').first().append(item).promise().done(function () {
            parent.find('.sortable_area').sortable({
                handle: jQuery(this).find('.sortable_button')
            });
            mptrs_load_date_picker(parent);
        });
    }
    return true;
}
(function ($) {
    "use strict";
    $(document).ready(function () {
        //=========Short able==============//
        $(document).find('.mptrs_area .sortable_area').sortable({
            handle: $(this).find('.sortable_button')
        });
    });
    //=========upload image==============//
    $(document).on('click', '.mptrs_area .add_single_image', function () {
        let parent = $(this);
        parent.find('.single_image_item').remove();
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html = '<div class="single_image_item" data-image-id="' + attachment_id + '"><span class="fas fa-times circleIcon_xs remove_single_image"></span>';
            html += '<img src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += '</div>';
            parent.append(html);
            parent.find('input').val(attachment_id);
            parent.find('button').slideUp('fast');
        }
        wp.media.editor.open($(this));
        return false;
    });
    $(document).on('click', '.mptrs_area .remove_single_image', function (e) {
        e.stopPropagation();
        let parent = $(this).closest('.add_single_image');
        $(this).closest('.single_image_item').remove();
        parent.find('input').val('');
        parent.find('button').slideDown('fast');
    });
    $(document).on('click', '.mptrs_area .remove_multi_image', function () {
        let parent = $(this).closest('.multi_image_area');
        let current_parent = $(this).closest('.multi_image_item');
        let img_id = current_parent.data('image-id');
        current_parent.remove();
        let all_img_ids = parent.find('.multi_image_value').val();
        all_img_ids = all_img_ids.replace(',' + img_id, '')
        all_img_ids = all_img_ids.replace(img_id + ',', '')
        all_img_ids = all_img_ids.replace(img_id, '')
        parent.find('.multi_image_value').val(all_img_ids);
    });
    $(document).on('click', '.mptrs_area .add_multi_image', function () {
        let parent = $(this).closest('.multi_image_area');
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html = '<div class="multi_image_item" data-image-id="' + attachment_id + '"><span class="fas fa-times circleIcon_xs remove_multi_image"></span>';
            html += '<img src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += '</div>';
            parent.find('.mptrs_multi_image').append(html);
            let value = parent.find('.multi_image_value').val();
            value = value ? value + ',' + attachment_id : attachment_id;
            parent.find('.multi_image_value').val(value);
        }
        wp.media.editor.open($(this));
        return false;
    });
    //=========Remove Setting Item ==============//
    $(document).on('click', '.mptrs_area .item_remove', function (e) {
        e.preventDefault();
        if (confirm('Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
            $(this).closest('.mp_remove_area').slideUp(250).remove();
            return true;
        } else {
            return false;
        }
    });
    //=========Add Setting Item==============//
    $(document).on('click', '.mptrs_area .add_item', function () {
        let parent = $(this).closest('.settings_area');
        let item = $(this).next($('.hidden_content')).find(' .hidden_item').html();
        if (!item || item === "undefined" || item === " ") {
            item = parent.find('.hidden_content').first().find('.hidden_item').html();
        }
        mptrs_load_sortable_datepicker(parent, item);
        parent.find('.item_insert').find('.add_select2').select2({});
        return true;
    });
}(jQuery));
(function ($) {
    "use strict";
    //=================select icon=========================//
    $(document).on('click', '.mptrs_area .add_icon_image_area button.icon_add', function () {
        let target_popup = $('.add_icon_popup');
        target_popup.find('.iconItem').click(function () {
            let parent = $('[data-active-popup]').closest('.add_icon_image_area');
            let icon_class = $(this).data('icon-class');
            if (icon_class) {
                parent.find('input[type="hidden"]').val(icon_class);
                parent.find('.add_icon_image_button_area').slideUp('fast');
                parent.find('.image_item').slideUp('fast');
                parent.find('.icon_item').slideDown('fast');
                parent.find('[data-add-icon]').removeAttr('class').addClass(icon_class);
                target_popup.find('.iconItem').removeClass('active');
                target_popup.find('.popupClose').trigger('click');
            }
        });
        target_popup.find('[data-icon-menu]').click(function () {
            if (!$(this).hasClass('active')) {
                let target = $(this);
                let tabsTarget = target.data('icon-menu');
                target_popup.find('[data-icon-menu]').removeClass('active');
                target.addClass('active');
                target_popup.find('[data-icon-list]').each(function () {
                    let targetItem = $(this).data('icon-list');
                    if (tabsTarget === 'all_item' || targetItem === tabsTarget) {
                        $(this).slideDown(250);
                    } else {
                        $(this).slideUp(250);
                    }
                });
            }
            return false;
        });
        target_popup.find('.popupClose').click(function () {
            target_popup.find('[data-icon-menu="all_item"]').trigger('click');
            target_popup.find('.iconItem').removeClass('active');
        });
    });
    $(document).on('click', '.mptrs_area .add_icon_image_area .icon_remove', function () {
        let parent = $(this).closest('.add_icon_image_area');
        parent.find('input[type="hidden"]').val('');
        parent.find('[data-add-icon]').removeAttr('class');
        parent.find('.icon_item').slideUp('fast');
        parent.find('.add_icon_image_button_area').slideDown('fast');
    });
    //=================select Single image=========================//
    $(document).on('click', '.mptrs_area button.mp_image_add', function () {
        let $this = $(this);
        let parent = $this.closest('.add_icon_image_area');
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            parent.find('input[type="hidden"]').val(attachment_id);
            parent.find('.icon_item').slideUp('fast');
            parent.find('img').attr('src', attachment_url);
            parent.find('.image_item').slideDown('fast');
            parent.find('.add_icon_image_button_area').slideUp('fast');
        }
        wp.media.editor.open($this);
        return false;
    });
    $(document).on('click', '.mptrs_area .add_icon_image_area .image_remove', function () {
        let parent = $(this).closest('.add_icon_image_area');
        parent.find('input[type="hidden"]').val('');
        parent.find('img').attr('src', '');
        parent.find('.image_item').slideUp('fast');
        parent.find('.add_icon_image_button_area').slideDown('fast');
    });
}(jQuery));