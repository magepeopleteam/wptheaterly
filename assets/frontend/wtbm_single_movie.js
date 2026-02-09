jQuery(document).ready(function ($) {


    $('.wtbm_show_movie_wrapper').each(function () {

        let columns = $(this).data('columns');

        if (!columns || columns < 1) {
            columns = 3; // fallback
        }

        $(this)
            .find('.wtbm_show_movie_list')
            .css('--wtbm-columns', columns);

    });

    let wrapper = $('.wtbm_show_movie_wrapper');
    let columns = wrapper.data('columns') || 3;
    let itemsToShow = 20;
    let currentCount = 0;

    $('.wtbm_show_movie_list').css('--wtbm-columns', columns);

    function showItems(filter = 'all') {
        let items = $('.wtbm_show_movie_card');

        if (filter !== 'all') {
            items = items.filter('[data-genre="' + filter + '"]');
        }

        items.hide();
        items.slice(0, currentCount + itemsToShow).fadeIn();

        currentCount += itemsToShow;

        if (currentCount >= items.length) {
            $('.wtbm_show_movie_loadmore').hide();
        } else {
            $('.wtbm_show_movie_loadmore').show();
        }
    }

    showItems();

    // Load More
    $('.wtbm_show_movie_loadmore').on('click', function () {
        showItems($('.wtbm_show_movie_filter li.active').data('filter'));
    });

    // Filter
    $('.wtbm_show_movie_filter li').on('click', function () {
        $('.wtbm_show_movie_filter li').removeClass('active');
        $(this).addClass('active');
        currentCount = 0;
        showItems($(this).data('filter'));
    });

    // Grid / List Toggle
    $('.wtbm_show_movie_toggle').on('click', function () {

        $(".wtbm_show_movie_toggle").removeClass('active');
        $(this).addClass('active');
        let view = $(this).data('view');
        $('.wtbm_show_movie_list')
            .removeClass('grid-view list-view')
            .addClass(view + '-view');
    });

});
