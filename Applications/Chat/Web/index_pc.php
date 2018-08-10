<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title>在线聊天</title>
    <link href="/src/css/bootstrap.min.css" rel="stylesheet">
    <link href="/src/css/jquery-sinaEmotion-2.1.0.min.css" rel="stylesheet">
    <link href="/src/css/weui-v1.1.2.min.css" rel="stylesheet">
    <link href="/src/css/style.css" rel="stylesheet">
    <script type="text/javascript" src="/src/js/swfobject.js"></script>
    <script type="text/javascript" src="/src/js/web_socket.js"></script>
    <script type="text/javascript" src="/src/js/jquery.min.js"></script>
    <script type="text/javascript" src="/src/js/jquery-sinaEmotion-2.1.0.min.js"></script>
    <script src="/src/js/babel-6.26.min.js"></script>
    <script>let isPcClient = true;</script>
    <script type="text/javascript" src="/dist/bundle.js"></script>
    <script type="text/javascript" src="/src/js/index.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="row clearfix">
        <div class="col-md-8 room-info">
          <div class="pull-right">
            显示名称：<span class="room-user-name"></span><span class="link" id="rename">修改</span>
          </div>
          <div>
            当前群组：<span class="room-name">格致</span>
          </div>
        </div>

        <div class="col-md-8 column">
          <div class="thumbnail">
            <div class="caption" id="dialog"></div>
          </div>
          <form>
            <textarea class="textarea thumbnail" id="msg"></textarea>
            <div class="say-btn">
              <a href="#" class="face pull-left" title="表情"><img src="/src/img/emotion.png"/></a>
              <span class="enter-tip">Enter快捷发表</span>
              <input type="button" class="btn btn-info" id="submit-msg" value="发表" />
            </div>
          </form>
        </div>
        <div class="col-md-4 column">
          <div class="thumbnail">
            <div class="caption" id="userlist">
              <h4>在线人数(<span class="online-count"></span>)</h4>
              <ul></ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>