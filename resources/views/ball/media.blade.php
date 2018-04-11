<html>
<head>
    <script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
</head>

<body>
<div style="position:absolute">
    <video id="video"  width="240" height="160" controls/>
    <canvas id="output" style="display:none"></canvas>
</div>
</div>
<div style="position:absolute;top:50px;left:800px" id="test">
    <img style="width:240px;height:160px;border: 1px solid red" id="testImg" src=""/>

</div>
</body>
<script>
    // socket
    $(document).ready(function () {
        var back = document.getElementById('output');
        var video = document.getElementById("video");
        var success = function(stream){
            console.log(window.URL.createObjectURL(stream));
            console.log((stream));
            video.src = window.URL.createObjectURL(stream);
        }
        navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
        navigator.getUserMedia({video:true, audio:false}, success, console.log);
        function draw(){
            try{
                back.getContext('2d').drawImage(video,0,0, back.width, back.height);
            }catch(e){
                if (e.name == "NS_ERROR_NOT_AVAILABLE") {
                    return setTimeout(draw, 30);
                } else {
                    throw e;
                }
            }
            if(video.src){
                var videoData = back.toDataURL("image/jpeg", 0.5);
                send('/api/socket/ball/video', {
                    'data': videoData
                });
            }
            setTimeout(draw, 30);
        }
        if ("WebSocket" in window == false) {
            alert("您的浏览器不支持 WebSocket!");
        }
        var name;
        var app_url = "<?php echo request()->getHost(); ?>";
        var ws = new WebSocket("ws://" + app_url + ":5200/api/socket/ball");
        ws.onopen = function () {
            draw();
        };

        function send(path, data) {
            var sendData = {'path_info': path, 'data': data};
            ws.send(JSON.stringify(sendData));
        }

        ws.onmessage = function (evt) {
            var data = JSON.parse(evt.data);
            if (data.type == 'video') {
                console.log(data.video)
                $('#testImg').attr('src', data.video);
            }
        };
        ws.onclose = function () {
        };
    })
</script>
</html>