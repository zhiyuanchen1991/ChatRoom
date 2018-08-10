<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title>在线聊天</title>
    <link href="/src/css/jquery-sinaEmotion-2.1.0.min.css" rel="stylesheet">
    <link href="/src/css/weui-v1.1.2.min.css" rel="stylesheet">
    <link href="/src/css/style.css" rel="stylesheet">
    <script type="text/javascript" src="/src/js/swfobject.js"></script>
    <script type="text/javascript" src="/src/js/web_socket.js"></script>
    <script type="text/javascript" src="/src/js/jquery.min.js"></script>
    <script type="text/javascript" src="/src/js/jquery-sinaEmotion-2.1.0.min.js"></script>
    <script src="/src/js/babel-6.26.min.js"></script>
    <script>let isPcClient = false;</script>
    <script type="text/javascript" src="/dist/bundle.js"></script>
    <script type="text/javascript" src="/src/js/index.js"></script>
  </head>
  <body>
    <div class="container mobile-container flex">
      <div class="weui-flex msg-show-area">
        <div class="weui-flex__item">
            <div id="dialog"></div>
        </div>
      </div>

      <div class="weui-flex msg-send-area">
        <div class="weui-flex__item">
          <textarea class="textarea thumbnail" id="msg" rows=1></textarea>
        </div>
        <div class="">
          <a href="#" class="face" title="表情"><img src="/src/img/emotion.png"/></a>
        </div>
        <div>
          <input type="button" class="btn btn-info" id="submit-msg" value="发表" />
        </div>
      </div>
    </div>
  </body>
</html>