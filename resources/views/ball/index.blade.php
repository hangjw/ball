<html>
<head>
    <script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>

    <style>
        .userBall {
            position: absolute;
            border-radius: 50%;
            border: 1px solid red;
        }
    </style>
</head>

<body>
<div style="position:absolute">
    <video id="video"  width="240" height="160" controls/>
    <canvas id="output" style="display:none"></canvas>
</div>
<div style="position:absolute;left:400px">
    <video id="videoCli"  width="240" height="160" controls/>
    <canvas id="output" style="display:none"></canvas>
</div>
<div id="map" style="position:relative;margin:auto;width:1000px;height:600px;border: 1px solid red">

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
//            '/api/socket/ball/move'
            var sendData = {'path_info': path, 'data': data};
            ws.send(JSON.stringify(sendData));
        }

        ws.onmessage = function (evt) {
            var data = JSON.parse(evt.data);
            if (data.type == 'start') {
                for (var i in data.user) {
                    var user = data.user[i];
                    var id = user.id;
                    if ($('#' + id).length == 0) {
                        var html = '' +
                            '<div class="userBall" style="left:' + user.position.x + 'px;top:' + user.position.y + 'px;width:' + user.size.width + initStyle.unit + ';height:' + user.size.height + initStyle.unit + '" id="' + id + '">' +
                            '<span style="white-space:nowrap;">' + user.name + '</span>' +
                            '</div>' +
                            '';
                        $('#map').append(html);
                    }
                }

            } else if (data.type == 'move') {
                $('#' + data.moveId).gameMove({
                    'x': data.moveTo.x,
                    'y': data.moveTo.y
                }, info.speed);
            } else if (data.type == 'close') {
                var close_id = data.close_id;
                $('#' + close_id).remove();
            } else if (data.type == 'video') {
                console.log(data.video)
                $('#testImg').attr('src', data.video);
            }
        };
        ws.onclose = function () {
//            console.log("连接已关闭...");
        };

        while (!name) {
            name = window.prompt("欢迎，请在此输入您的姓名。", "");
        }
        send('/api/socket/ball/setName', {
            'name': name
        });

        // 继承
        $.fn.extend({
            gameMove: function (direction, speed) {
                var now = {
                    'left': parseInt($(this).css('left')),
                    'top': parseInt($(this).css('top'))
                };
                var base = {
                    'left': direction.x - parseInt($(this).css('width')) / 2,
                    'top': direction.y - parseInt($(this).css('height')) / 2
                };
                var moveX = base.left - now.left;
                var moveY = base.top - now.top;
                var total = Math.sqrt(moveX * moveX + moveY * moveY);

                $(this).stop().animate({
                    left: base.left,
                    top: base.top
                }, total / speed * 1000);
            }
        });
        $.extend({
            positionTrans: function (direction, nowOffset) {
                return {
                    'x': direction.x - nowOffset.x,
                    'y': direction.y - nowOffset.y
                };
            }
        })

        $("html").mouseup(function (event) {
            info.offset = {
                'x': $('#map').offset().left + event.screenX - event.pageX,
                'y': $('#map').offset().top + event.screenY - event.pageY
            };
            send('/api/socket/ball/move', $.positionTrans({
                'x': event.screenX,
                'y': event.screenY
            }, info.offset));
        });

        // 初始化
        var initStyle = {
            'unit': 'px'
        };
        var info = {
            'speed': 100,  // 一秒移动的unit
            'offset': null
        };

    })
</script>
</html>