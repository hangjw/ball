<html>
<head>
    <script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
</head>

<body>
<div style="position:absolute;top:50px;left:200px" id="test">
    <img style="width:240px;height:160px;border: 1px solid red" id="testImg" src=""/>
</div>
</body>
<script>
    // socket
    $(document).ready(function () {
        if ("WebSocket" in window == false) {
            alert("您的浏览器不支持 WebSocket!");
        }
        var app_url = "<?php echo request()->getHost(); ?>";
        var ws = new WebSocket("ws://" + app_url + ":5200/api/socket/ball");
        ws.onopen = function () {
        };
        function send(path, data) {
            var sendData = {'path_info': path, 'data': data};
            ws.send(JSON.stringify(sendData));
        }

        ws.onmessage = function (evt) {
            var data = JSON.parse(evt.data);
            if (data.type == 'video') {
                if (data.video) {
                    $('#testImg').attr('src', data.video);
                }
            }
        };
        ws.onclose = function () {
        };
    })
</script>
</html>