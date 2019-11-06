<?php
if (!isset($_SESSION))
{
    session_start();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>GIF Creator 1.0</title>
        <style>
            body, html
            {
                height: 100%;
                margin: 0;
            }

            .bg
            {
                position: relative;
                background-image: url("bg.jpg");
                height: 100%; 
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
                display: flex;
                justify-content: center;
                align-items: center;
                resize: both;
                overflow: auto;
            }
            
            .center
            {
                position:absolute;
                margin-left:auto;
                margin-right:auto;
                display:block;
                background:white;
                padding:10px;
                border-style: solid;
                font-family: "Lucida Console", Monospace;
            }

            #frames
            {
                position:absolute;
                height: 100%;
                width: 100%;
                margin: 0;
                padding: 0;
                top: 0px;
                left: 0px;
                z-index: 1;
            }
        </style>
        <script src="jquery.js"></script>
        <script>
$(function()
{
    $("html").on(
    {
        'dragover dragenter drop': function(e)
        { 
            e.preventDefault();
            e.stopPropagation();
        }
    });

    $("#frames").on(
    {
        'dragover dragenter': function(e)
        { 
            e.preventDefault();
            e.stopPropagation();
        },

        'drop': function(e)
        {
            var dataTransfer =  e.originalEvent.dataTransfer;
            e.preventDefault();
            e.stopPropagation();

            var data = new FormData();
            var files = dataTransfer.files;
            $.ajax({
                url: 'api/initprocess',
                type: 'post',
                data: data,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) { OnProcessDone(response, files); }
            });
        }
    });
});

function OnProcessDone(response, files)
{
    if(response.Success)
    {
        $("#message").text("Uploading files...");
        var data = new FormData();
        for (var i = 0; i < files.length; i++)
        {
            data.append('frames[]', files[i]);
        }
        $.ajax({
            url: 'api/upload',
            type: 'post',
            data: data,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) { OnUploadDone(response); }
        });
    }
}

function OnUploadDone(response)
{
    if(response.Success)
    {
        $("#message").text("Generating animation. Please wait...");
        var data = new FormData();
        $.ajax({
            url: 'api/generategif',
            type: 'post',
            data: data,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) { OnGenerateDone(response); }
        });
    }
}

function OnGenerateDone(response)
{
    if(response.Success)
    {
        $("#message").text("GIF is ready ");
        $('<a>',
        {
            text: response.URL,
            href: response.URL,
            title: ""
        }).appendTo($("#message"));
        $("#frames").remove();
    }
}
            </script>
    </head>
    <body>
        <div class="bg">
            <div class="center">
                <p id="message">To create a GIF animation. Just drop files anywhere on the page.</p>
            </div>
        </div>
        <div id="frames"></div>            
    </body>
</html>