$(function(){
    //カテゴリー選択時のアクション
    $('body').on('change', '#category', function() {
        var $formData = new FormData();
        $formData.append('category', $(this).val());
        $formData.append('lang', 'en');
        $.ajax({
            url: "/func/getnews.php",
            type: "POST",
            data: $formData,
            processData: false,
            contentType: false,
            async: true,
            dataType: 'json'
        }).done(function(data, textStatus, jqXHR){
            $('.posts-type3__list').empty();
            $base = '<li class="posts-type3__item"><a href="/en//news/detail.php?id=%serial%"%target% class="posts-type3__link"><div class="posts-type3__category">%category%</div><div class="posts-type3__date">%post_date%</div><div class="posts-type3__title">%title%</div></a></li>';
            $.each(data,function(key,item){
                $li = $base;
                $li = $li.replace("%serial%", item.serial);
                $li = $li.replace("%target%", item.target);
                $li = $li.replace("%category%", item.category);
                $li = $li.replace("%post_date%", item.post_date);
                $li = $li.replace("%title%", item.title);
                $('.posts-type3__list').append($li);
            });
        }).fail(function(jqXHR, textStatus, errorThrown){
            alert('送信に失敗しました。' + textStatus + ':' + errorThrown);
        }).always(()=> {
        });
    });
});
