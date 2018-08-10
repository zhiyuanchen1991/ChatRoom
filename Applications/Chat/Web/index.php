<?php
  /*移动端判断*/
  function isMobile()
  {
      // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
      if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
      {
          return true;
      }
      // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
      if (isset ($_SERVER['HTTP_VIA']))
      {
          // 找不到为flase,否则为true
          return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
      }
      // 脑残法，判断手机发送的客户端标志,兼容性有待提高
      if (isset ($_SERVER['HTTP_USER_AGENT']))
      {
          $clientkeywords = array ('nokia',
              'sony',
              'ericsson',
              'mot',
              'samsung',
              'htc',
              'sgh',
              'lg',
              'sharp',
              'sie-',
              'philips',
              'panasonic',
              'alcatel',
              'lenovo',
              'iphone',
              'ipod',
              'blackberry',
              'meizu',
              'android',
              'netfront',
              'symbian',
              'ucweb',
              'windowsce',
              'palm',
              'operamini',
              'operamobi',
              'openwave',
              'nexusone',
              'cldc',
              'midp',
              'wap',
              'mobile'
              );
          // 从HTTP_USER_AGENT中查找手机浏览器的关键字
          if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
          {
              return true;
          }
      }
      // 协议法，因为有可能不准确，放到最后判断
      if (isset ($_SERVER['HTTP_ACCEPT']))
      {
          // 如果只支持wml并且不支持html那一定是移动设备
          // 如果支持wml和html但是wml在html之前则是移动设备
          if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
          {
              return true;
          }
      }
      return false;
  }
?>

<?php
if (isMobile()) {
?>

<!-- 移动端聊天室 -->
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title>格致</title>
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
      <div class="weui-flex room-top">
        <div class="weui-flex__item room-online-area">
          <span class="link" id="view"><img src="/src/img/user.png" class="icon"/></span>
        </div>
        <div class="weui-flex__item room-user-area">
          <span class="room-user-name"></span><span class="link" id="rename"><img src="/src/img/setting.png"/></span>
        </div>

        <div id="room-user-list">
          <ul>
           <li>陈志远</li>
           <li>周杰伦</li>
           <li>习近平</li>
          </ul>
        </div>

      </div>

      <div class="weui-flex msg-show-area">
        <div class="weui-flex__item">
            <div id="dialog"></div>
        </div>
      </div>

      <div class="weui-flex msg-send-area">
        <div class="weui-flex__item">
          <textarea class="textarea thumbnail weui-textarea" id="msg" rows=1></textarea>
        </div>
        <div class="msg-btn-area">
          <input type="button" class="weui-btn btn-info" id="submit-msg" value="发表" />
        </div>
      </div>
    </div>

  </body>
</html>

<?php
} else {
?>

<!-- PC端聊天室 -->
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title>格致基因</title>
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
            当前群组：<span class="room-name">格致基因</span>
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


<?php
}
?>