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
<video id="video" width="480" height="320" controls>

    <div id="map" style="position:relative;margin:auto;width:1000px;height:600px;border: 1px solid red">
    </div>
</body>
<script>


    // socket
    $(document).ready(function () {
        if ("WebSocket" in window == false) {
            alert("您的浏览器不支持 WebSocket!");
        }
        var name;
        var app_url = "<?php echo request()->getHost(); ?>";
        var ws = new WebSocket("ws://" + app_url + ":5200/api/socket/ball");
        ws.onopen = function () {
        };

        function send(path, data) {
//            '/api/socket/ball/move'
            var sendData = {'path_info': path, 'data': data};
            console.log(JSON.stringify(sendData));
            ws.send(JSON.stringify(sendData));
        }

        ws.onmessage = function (evt) {
            var data = JSON.parse(evt.data);
            console.log(evt.data);
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
            } else {
                //如果是一个ICE的候选，则将其加入到PeerConnection中，否则设定对方的session描述为传递过来的描述
                if (json.event === "__ice_candidate") {
                    pc.addIceCandidate(new RTCIceCandidate(data.candidate));
                } else {
                    pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
                }
            }
        };
        ws.onclose = function () {
            console.log("连接已关闭...");
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


        //使用Google的stun服务器
        var iceServer = {
            "iceServers": [{
                "url": "stun:stun.l.google.com:19302"
            }]
        };
        var getUserMedia = (navigator.getUserMedia ||
            navigator.webkitGetUserMedia ||
            navigator.mozGetUserMedia ||
            navigator.msGetUserMedia);

        var PeerConnection = (window.PeerConnection ||
            window.webkitPeerConnection00 ||
            window.webkitRTCPeerConnection ||
            window.mozRTCPeerConnection);
        var pc = new PeerConnection(iceServer);
        //发送ICE候选到其他客户端
        pc.onicecandidate = function (event) {
            alert(1);
            alert(event.candidate);
            ws.send(JSON.stringify({
                "event": "__ice_candidate",
                "data": {
                    "candidate": event.candidate
                }
            }));
        };
        var video = document.getElementById('video');
        ;
        //如果检测到媒体流连接到本地，将其绑定到一个video标签上输出
        pc.onaddstream = function (event) {
            video.srcObject = stream;
        };
        getUserMedia.call(navigator, {
            "audio": true,
            "video": true
        }, function (stream) {
            //发送offer和answer的函数，发送本地session描述
            var sendOfferFn = function (desc) {
                    pc.setLocalDescription(desc);
                    alert(2);
                    alert(desc);
                    ws.send(JSON.stringify({
                        "event": "__offer",
                        "data": {
                            "sdp": desc
                        }
                    }));
                },
                sendAnswerFn = function (desc) {
                    pc.setLocalDescription(desc);
                    ws.send(JSON.stringify({
                        "event": "__answer",
                        "data": {
                            "sdp": desc
                        }
                    }));
                };
            //绑定本地媒体流到video标签用于输出
            video.srcObject = stream;
//            myselfVideoElement.src = URL.createObjectURL(stream);
            //向PeerConnection中加入需要发送的流
            pc.addStream(stream);
            //如果是发送方则发送一个offer信令，否则发送一个answer信令
//            if(isCaller){
//                pc.createOffer(sendOfferFn);
//            } else {
            pc.createAnswer(sendAnswerFn);
//            }
        }, function (error) {
            //处理媒体流创建失败错误
        });

    })
</script>
</html>