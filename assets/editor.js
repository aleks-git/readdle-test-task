$(function() {

    var hash = getUrlParameter('id');
    var name = getUrlParameter('name');
    var token = $('meta[name=csrf-token]').attr("content");

    if (hash){
        $.ajax({
            url: 'php-backend/handler.php',
            type: 'post',
            dataType: 'json',
            data: {
                'token': token,
                'action': 'load',
                'hash':hash
            },
            success: function(data){
                var issetError = catchCustomError(data);
                if(issetError)
                    $(location).attr('href', '/');
                else {
                    var metadata = data[0];
                    var content = data[1];
                    runMosaico(name, metadata, content);
                }
            },
            error: function( jqXhr, textStatus, errorThrown ){
                console.log(jqXhr);
                console.log(textStatus);
                console.log(errorThrown);
            }
        });
    }
    else{
        runMosaico(name, undefined, undefined);
    }


    function runMosaico(name, metadata, content){
        var basePath = window.location.href.substr(0, window.location.href.lastIndexOf('/'));
        var plugins = [
            // plugin for integrating save button
            function(viewModel) {
                console.log('PROCESS PLUGIN');

                var saveCmd = {
                    name: 'Save',
                    enabled: ko.observable(true)
                };

                var downloadCmd = {
                    name: 'Download',
                    enabled: ko.observable(true)
                };

                saveCmd.execute = function() {
                    console.log('FIRE SAVE');
                    saveCmd.enabled(false);
                    viewModel.metadata.changed = Date.now();
                    if(typeof viewModel.metadata.key == 'undefined') {
                        var rnd = Math.random().toString(36).substr(2, 7);
                        viewModel.metadata.key = rnd;
                    }

                    $.ajax({
                        url: 'php-backend/handler.php',
                        type: "post",
                        data:{
                            'name':name,
                            'token': token,
                            'action': 'save',
                            'hash':viewModel.metadata.key,
                            'metadata':viewModel.exportMetadata(),
                            'content':viewModel.exportJSON(),
                            'html': viewModel.exportHTML()
                        },
                        success:function(data){
                            var issetError = catchCustomError(data);
                            if(issetError)
                                $(location).attr('href', '/');
                            else viewModel.notifier.success(viewModel.t('Successfully saved.'));
                        },
                        error:function(jqXHR, textStatus, errorMessage){
                            viewModel.notifier.error(viewModel.t('Saving failed.'));
                        }
                    }).always(function() {
                        saveCmd.enabled(true);
                    });
                };

                downloadCmd.execute = function() {
                    var emailProcessorBackend = "/dl/";
                    downloadCmd.enabled(false);
                    viewModel.notifier.info(viewModel.t("Downloading..."));
                    viewModel.exportHTMLtoTextarea('#downloadHtmlTextarea');
                    var postUrl = emailProcessorBackend;
                    document.getElementById('downloadForm').setAttribute("action", postUrl);
                    document.getElementById('downloadForm').submit();
                    downloadCmd.enabled(true);
                };

                viewModel.save = saveCmd;
                viewModel.download = downloadCmd;
            }
        ];

        var ok = Mosaico.start({
            imgProcessorBackend: basePath + '/img/',
            emailProcessorBackend: basePath + '/dl/',
            titleToken: "Email Editor",
            fileuploadConfig: {
                url: basePath + '/mosaico-php/index.php/?path=upload'
            }
        }, basePath + '/dist/templates/versafix-1/template-versafix-1.html', metadata, content, plugins);
        if (!ok) {
            console.log("Missing initialization hash, redirecting to main entrypoint!");
        }
    }


    function catchCustomError(data){
        if (data == 'error_token' || data == 'error_auth' || data == 'error')
            return true;
        else return false;
    }


    function getUrlParameter(sParam) {
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