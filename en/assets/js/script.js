/*---------------------------------------------------------*/
/* アンカースクロール　                                         */
/*---------------------------------------------------------*/
$('a[href^="#"]').click(function() {
    let header = $(".header").innerHeight();
    let speed = 1000;
    let id = $(this).attr("href");
    let target = $("#" == id ? "html" : id);
    let position = $(target).offset().top - header;
    $("html, body").animate(
        {
        scrollTop: position
        },
        speed
    );
    return false;
});


/*---------------------------------------------------------*/
/* メニュー開閉                                               */
/*---------------------------------------------------------*/
/* PCサブメニュー開閉 */
$('.gnavi__item').hover(
    function() {
        if(checkDevice() == 'pc'){
            $(this).addClass('hover');
        }
    },
    function() {
        if(checkDevice() == 'pc'){
            $(this).removeClass('hover');
        }
    }
);
$('.gnavi__item.has_submenu').hover(
function() {
    if(checkDevice() == 'pc'){
        $(this).addClass('show_submenu');
    }
},
function() {
    if(checkDevice() == 'pc'){
        $(this).removeClass('show_submenu');
    }
}
);


/* SPメニュー開閉 */
$('.header__gnavi-btn').on('click', function() {
if (checkDevice() == 'sp') {
    if($('.gnavi').outerHeight() > 0) {
        $('.gnavi').css('height', '');
    }else{
        $('.gnavi').css('height', 'calc(100vh - ' + $('.header').outerHeight() + 'px)');
    }
}
});

/* SPサブメニュー開閉 */
$('.gnavi-submenu').on('click', function() {
if (checkDevice() == 'sp') {
    let this_height = $(this).outerHeight();
    let target_height = $(this).find('.gnavi-submenu__inner').outerHeight();

    if (this_height < target_height) {
        $(this).addClass('open_sp_submenu');
        $(this).css('height', target_height + 'px');
    }else{
        $(this).removeClass('open_sp_submenu');
        $(this).css('height', '');
    }
    console.log(this_height);
    console.log(target_height);
}
});

// リサイズでメニュー閉じる
$(window).resize(function(){
$('.gnavi').css('height', '');
$('.gnavi-submenu').removeClass('open_sp_submenu');
$('.gnavi-submenu').css('height', '');
});

//現在のwindowサイズからSP/PC判定
function checkDevice() {
if(window.matchMedia("(max-width: 1299px)").matches) {
    return 'sp';
}else{
    return 'pc';
}
}


/*---------------------------------------------------------*/
/* スライダー　slick                                          */
/*---------------------------------------------------------*/
$('.posts-type2__sp-list').slick({
    infinite: true,
    autoplay: true,
    autoplaySpeed: 3000,
    slidesToShow: 1,
    slidesToScroll: 1,
    dots: false,
    arrows: true,
    centerMode: true,
    centerPadding: '18%',
    prevArrow : '<div class="posts-type2__sp-arrow prev"></div>"',
    nextArrow : '<div class="posts-type2__sp-arrow next"></div>"',
});

$('.history-slide__list').slick({

    infinite: true,
    slidesToShow: 3,
    slidesToScroll: 3,
    centerMode: true,
    centerPadding: '16.1%',
    autoplay: true,
    autoplaySpeed: 0,　//隣あう画像のスライドするまでの間隔時間
    speed: 10000,
    arrows: false,
    pauseOnFocus: false,
    pauseOnHover: false,
    adaptiveHeight: true,
    cssEase: 'linear',//開始から終了まで一定に変化する
    responsive: [{
        breakpoint: 769,
        settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
        }
    }]
});

$(function () {
    $(".top-mainview__slide")
    // 最初のスライドに"add-animation"のclassを付ける(data-slick-index="0"が最初のスライドを指す)
        .on("init", function () {
            $('.top-mainview__slide .slick-slide[data-slick-index="0"]').addClass("add-animation");
        })
        // 通常のオプション
        .slick({
            autoplay: true, // 自動再生ON
            fade: true, // フェードON
            arrows: false, // 矢印OFF
            speed: 2000, // スライド、フェードアニメーションの速度2000ミリ秒
            autoplaySpeed: 4000, // 自動再生速度4000ミリ秒
            pauseOnFocus: false, // フォーカスで一時停止OFF
            pauseOnHover: false, // マウスホバーで一時停止OFF
        })
        .on({
            // スライドが移動する前に発生するイベント
            beforeChange: function (event, slick, currentSlide, nextSlide) {
                // 表示されているスライドに"add-animation"のclassをつける
                $(".top-mainview__slide .slick-slide", this).eq(nextSlide).addClass("add-animation");
                // あとで"add-animation"のclassを消すための"remove-animation"classを付ける
                $(".top-mainview__slide .slick-slide", this).eq(currentSlide).addClass("remove-animation");
            },
            // スライドが移動した後に発生するイベント
            afterChange: function () {
                // 表示していないスライドはアニメーションのclassを外す
                $(".remove-animation", this).removeClass(
                "remove-animation add-animation"
                );
            },
        });
});


/*---------------------------------------------------------*/
/* スライダー　slick                                          */
/*---------------------------------------------------------*/
$('.basepage-tenant__map-link').on('click', function() {
    window.open($(this).attr('href'), "", "width=1130,height=603");
    console.log($(this).attr('href'));
    return false;
});