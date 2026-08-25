$(function(){
    //DOM読み込み後の表示状態調整
    if($('#file_only_dispname').val() != ''){
        $('#file_only_del').css('display', 'inline');
    }
    else{
        $('#file_only_del').css('display', 'none');
    }
    // //Ajax送信開始時のアクション
    // $(document).ajaxSend(function(){
    //     $("#overlay").fadeIn(300);
    // });
    
    // //Ajax送信終了時のアクション
    // $(document).ajaxStop(function(){
    //     setTimeout(function(){
    //         $("#overlay").fadeOut(300);
    //     },500);
    // });
    
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
                beforeSend: function() {
                    $('#file_only_progress').val(0);
                },
                dataType: 'json',
                xhr: function() {
                    XHR = $.ajaxSettings.xhr();
                    if (XHR.upload)
                    {
                        $('#file_only_progress_div').css('display', 'block');
                        XHR.upload.addEventListener('progress', function(e)
                        {
                            var uploaded = parseInt(e.loaded / e.total * 100);
                            $('#file_only_progress').val(uploaded);
                        }, false);
                    }
                    return XHR;
                }
            }).done(function(data, textStatus, jqXHR){
                if(data.status != 'success'){
                    alert(data.error);
                }
                else{
                    $file.next().val(data.file_name);
                    $file.next().next().val($file_name);
                }
                $file.val('');
                $file.next().next().next().next().css('display', 'inline');
            }).fail(function(jqXHR, textStatus, errorThrown){
                alert('送信に失敗しました。' + textStatus + ':' + errorThrown);
            }).always(()=> {
                $('#file_only_progress_div').css('display', 'none');
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
