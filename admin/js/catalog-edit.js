$(function(){
    //DOM読み込み後の表示状態調整
   $('input[name*=dispname]').each(function(){
        if($(this).val() != ''){
            $(this).siblings('.bt-kaijo').css('display', 'inline');
        }
        else{
            $(this).siblings('.bt-kaijo').css('display', 'none');
        }
    });
    //Ajax送信開始時のアクション
    $(document).ajaxSend(function(){
        $("#overlay").fadeIn(300);
    });
    
    //Ajax送信終了時のアクション
    $(document).ajaxStop(function(){
        setTimeout(function(){
            $("#overlay").fadeOut(300);
        },500);
    });
    
    //ファイル選択クリック時のアクション
    $('body').on('click', '.bt-sentaku', function(){
        // *_file[]のクリックをシミュレート
        $(this).prev().prev().prev().trigger('click');
    });
    
    //ファイル選択完了時のアクション
    $('body').on('change', 'input[type=file]', function() {
        var $file = $(this);
        if($(this).prop('files').length != 0)
        {
            var $file_name = $(this).prop('files')[0].name;
            var $file_data = $(this).prop('files')[0];
            var $formData = new FormData();
            $formData.append('file', $file_data);
            $.ajax({
                url: "../lib/upload.php",
                type: "POST",
                data: $formData,
                processData: false,
                contentType: false,
                async: true,
                dataType: 'json'
            }).done(function(data, textStatus, jqXHR){
                if(data.status != 'success'){
                    alert(data.error);
                }
                else{
                    $file.next().val(data.file_name);
                    $file.next().next().val($file_name);
                    $file.next().next().next().next().css('display', 'inline');
                }
                $file.val('');
            }).fail(function(jqXHR, textStatus, errorThrown){
                alert('送信に失敗しました。' + textStatus + ':' + errorThrown);
            }).always(()=> {
            });
        }
        else
        {
                $(this).next().val('');
                $(this).next().next().val('');
                $(this).val('');
                alert('キャンセルしました');
        }
    });
    
    //消クリック時のアクション
    $('body').on('click', '.bt-kaijo', function(){
        if(!confirm('ファイル選択を消去します。よろしいですか？'))
            return false;
        $(this).prev().prev().prev().val('');
        $(this).prev().prev().val('');
        $(this).css('display', 'none');
        return false;
    });
});
