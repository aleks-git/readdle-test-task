$(document).ready(function(){
    var token = $('meta[name=csrf-token]').attr("content");

    /*Show tepmplates list*/
    if($('#saved').length > 0){
        var typePage = $('#saved').attr('data-type');

        $.ajax({
            url: 'php-backend/handler.php',
            type: 'post',
            data: {
                'type': typePage,
                'token': token,
                'action': 'list'
            },
            success: function(data){
                var issetError = catchCustomError(data);
                if(issetError) $(location).attr('href', '/');
                else $('#saved').append(data);
            },
            error: function( jqXhr, textStatus, errorThrown ){
                console.log('Database Error');
            }
        });
    }


    /*New Template*/
    $('button.new').on('click',function(e){
        e.preventDefault();
        var name = prompt("Template Name", "");

        if (name !== null && name != "") {
            var redirectWindow = window.open('/editor.php?name='+name, '_blank');
            redirectWindow.location;
        }
        else if(name == "")
            showErrorInformer('You should set template name. Try once again.');
    });


    /*Rename Template*/
    $('body').on('click','.rename',function(e){
        e.preventDefault();
        var rename = prompt("Rename Template", "");
        if (rename !== null && rename != "") {

            var parentElem = $(this).parent().parent().parent().parent();
            var dataid = $(this).data('id');
            $.ajax({
                url: "php-backend/handler.php",
                type: "post",
                data: {
                    'action': 'rename',
                    'token': token,
                    'name': rename,
                    'hash': dataid
                },
                success:function(data){
                    var issetError = catchCustomError(data);
                    if(issetError) $(location).attr('href', '/');
                    else if(data){
                        parentElem.find('.template-name').html(rename);
                        parentElem.find('.template-name, ul.dropdown-menu a.edit').attr('href', 'editor.php?id='+dataid+'&name='+rename);
                        showSuccessInformer('Template was renamed.');
                    }
                    else showErrorInformer('Something wrong. Try again later.');
                },
                error:function(jqXHR, textStatus, errorMessage){
                    showErrorInformer('Something wrong. Try again later.');
                }
            });
        }
        else if(rename == "")
            showErrorInformer('You should set template name. Try once again.');
    });


    /*Delete Template*/
    $('body').on('click','.delete',function(e){
        e.preventDefault();
        if (confirm('Are you sure you want to delete this template?')) {
            var parentElem = $(this).parent().parent().parent().parent();
            var dataid = $(this).data('id');

            $.ajax({
                url: "php-backend/handler.php",
                type: "post",
                data: {
                    'action': 'delete',
                    'token': token,
                    'hash': dataid
                },
                success:function(data){
                    var issetError = catchCustomError(data);
                    if(issetError) $(location).attr('href', '/');
                    else if(data) {
                        parentElem.remove();
                        showSuccessInformer('Template was deleted.');
                    }
                    else showErrorInformer('Something wrong. Try again later.');
                },
                error:function(jqXHR, textStatus, errorMessage){
                    showErrorInformer('Something wrong. Try again later.');
                }
            });
        }
    });

    /*Duplicate Template*/
    $('body').on('click','.duplicate',function(e){
        e.preventDefault();
        var parentElem = $(this).parent().parent().parent().parent();
        var dataid = $(this).data('id');
        $.ajax({
            url: "php-backend/handler.php",
            type: "post",
            dataType: 'json',
            data:{
                'action': 'duplicate',
                'token': token,
                'hash': dataid
            },
            success:function(data){
                var issetError = catchCustomError(data);
                if(issetError) $(location).attr('href', '/');
                else if(data && data.hash){
                    var newName = data.name;
                    var newHash = data.hash;
                    var copyBlock = parentElem.clone();
                    copyBlock.find('.template-name').html(newName);
                    copyBlock.find('ul.dropdown-menu a').attr('data-id', newHash);
                    copyBlock.find('ul.dropdown-menu a.preview').attr('href', '/?page=preview&id='+newHash);
                    copyBlock.find('.template-name, ul.dropdown-menu a.edit').attr('href', 'editor.php?id='+newHash+'&name='+newName);

                    $('#saved').append(copyBlock);
                    showSuccessInformer('Template was duplicated.');
                }
                else showErrorInformer('Something wrong. Try again later.');
            },
            error:function(jqXHR, textStatus, errorMessage){
                showErrorInformer('Something wrong. Try again later.');
            }
        });
    });


    /*Send email*/
    $('body').on('click','.send_email',function(){
        var recipient = prompt("Enter a recipient", "");

        if (recipient !== null && recipient != "") {
            var dataid = $(this).data('id');
            $.ajax({
                url: "php-backend/handler.php",
                type: "post",
                data:{
                    'action': 'sendEmail',
                    'token': token,
                    'hash': dataid,
                    'recipient': recipient
                },
                success:function(data){
                    var issetError = catchCustomError(data);
                    if(issetError) $(location).attr('href', '/');
                    else if(data == 'success') {
                        showSuccessInformer('Test email was sent.');
                    }
                    else showErrorInformer('Something wrong. Try again later.');
                },
                error:function(jqXHR, textStatus, errorMessage){
                    showErrorInformer('Something wrong. Try again later.');
                }
            });
        }
        else if(recipient == "") showErrorInformer('You should write your email address...');
    });


    if($('#previewBlock').length > 0){
        var hash = getUrlParameters('id');
        if(hash){
            $.ajax({
                url: "php-backend/handler.php",
                type: 'post',
                data: {
                    'action': 'preview',
                    'token': token,
                    'hash': hash
                },
                success: function(data){
                    var issetError = catchCustomError(data);
                    if(issetError) $(location).attr('href', '/');
                    else if(data) $("html").html(data);
                },
                error: function( jqXhr, textStatus, errorThrown ){
                    console.log('Preview load error');
                }
            });
        }
    }


    /*Show success informer*/
    function showSuccessInformer(text){
        $(".alert.alert-success span").text(text);
        $(".alert.alert-success").removeClass('fade');
        setTimeout(function() { $(".alert.alert-success").addClass('fade'); }, 4000);
    }


    /*Show error informer*/
    function showErrorInformer(text){
        $(".alert.alert-danger span").text(text);
        $(".alert.alert-danger").removeClass('fade');
        setTimeout(function() { $(".alert.alert-danger").addClass('fade'); }, 4000);
    }


    function catchCustomError(data){
        if (data == 'error_token' || data == 'error_auth' || data == 'error')
            return true;
        else return false;
    }


    function getUrlParameters(sParam){
        var params = window.location.search.replace('?','').split('&')
            .reduce(function(p,e){
                    var a = e.split('=');
                    p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
                    return p;
                }, {}
            );

        return params[sParam];
    }

});