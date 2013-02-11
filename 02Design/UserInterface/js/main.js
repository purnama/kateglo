function resetHomeContainer() {

    var height = $(window).height();

    if (height > 150) {
        var newHeight = (height - 400) / 2;
        if (newHeight > 150) {
            $('#home-container').attr("style", "margin-top: " + newHeight + "px;");
        } else {
            $('#home-container').removeAttr("style");
        }
    } else {
        $('#home-container').removeAttr("style");
    }
}

function resetNavbarAffixTop() {
    var width = $('#wrap-container').width();
    var menu = $('#navbar-affix-top');
    menu.attr("style", "width: " + width + "px;");

    $('#navbar-affix-top').affix({
        offset:{
            top:$('#navbar-affix-top').position().top - 42,
            left:$('#navbar-affix-top').position().left
        }
    });
    $('#navbar-affix-top').on('affixed', function () {
        $('#affix-title strong').html($('#entry-title').html());
    });
    $('#navbar-affix-top').on('unaffixed', function () {
        $('#affix-title strong').html('');
    });

    var elem = $('#' + window.location.hash.replace('#', ''));
    if(elem) {
        console.log(elem[0]);
        elem[0].scrollIntoView();
        scrollBy(0, -100);
    }

    $(window).scrollspy({wrap:$('.wrap')[0]});

    $('#navbar-affix-scrollspy .nav li a').click(function (event) {
        event.preventDefault();
        var parent = $(this).parent();
        $.each(parent.siblings(), function () {
            $(this).removeClass('active');
        });
        parent.addClass('active');
        console.log($($(this).attr('href'))[0]);
        $($(this).attr('href'))[0].scrollIntoView();
        scrollBy(0, -100);
    });

}
