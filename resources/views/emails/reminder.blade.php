<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>

        <style>
            html, body {
                height: 100%;
                font-family: 'Roboto','Helvetica Neue','Microsoft Yahei','Hiragino Sans GB','Microsoft Sans Serif','WenQuanYi Micro Hei',sans-serif;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
            }

            .container {
                vertical-align: middle;
            }

            .content {
                margin: 8px;
                display: inline-block;
            }

            .title {
                font-size: 16px;
            }

            .code {
                margin: 8px;
                font-size: 24px;
                font-weight: 500;
            }

        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">你向邮箱{{$email}}请求的验证码为</div>
                <div class="code">{{$code}}</div>
                <div>请尽快使用，如果不是你本人操作请无视</div>
            </div>
        </div>
    </body>
</html>
