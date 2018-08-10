import User from "./user.js";
import "../css/app.css"

let config = require("./config.json");

let user = new User,
	loginUser = user.get();

/**
 * 如果浏览器不支持websocket，会使用这个flash自动模拟websocket协议，此过程对开发者透明
 * 开启flash的websocket debug
 **/
let WEB_SOCKET_SWF_LOCATION = "/swf/WebSocketMain.swf",
	WEB_SOCKET_DEBUG = true;

/**
 * 可能与多个单人对话，因此建立多连接
 *
 * target为当前目标用户
 *   client_uid: 当前对话的用户client_uid
 *   client_name: 当前对话的用户名称
 *   active_client: 活跃的对话
 **/
let ws,
	chat = {
		client_uid: '',
		client_name: ''
	},
	client_list = {};

/**
 * 连接服务端
 **/
let connect = () => {
	// 创建websocket
	ws = new WebSocket("ws://" + document.domain + ":" + config.server_port);
	// 当socket连接打开时，输入用户名
	ws.onopen = onopen;
	// 当有消息时根据消息类型显示不同信息
	ws.onmessage = onmessage;

	ws.onclose = function() {
		connect();
	};

	ws.onerror = function() {
	};
}

// 连接建立时发送登录信息
let onopen = () => {
	let loginInfo = {
		type: "login",
		room: 1
	},
	name = "";

    if (user.check()) {
    	user.keepAlive();
    	loginInfo.client_uid = loginUser.client_uid;
    	loginInfo.client_name = loginUser.client_name;
		$(".room-user-name").text(loginUser.client_name);
    } else {
	    name = show_prompt();
	    loginInfo.client_name = name.replace(/"/g, '\\"');
	}

	send(loginInfo);
}

// 服务端发来消息时
let onmessage = (e) => {
    let data = JSON.parse(e.data);
    switch (data['type']) {
        // 服务端ping客户端
        case 'ping':
            send({"type":"pong"});
            break;;
        // 登录 更新用户列表
        case 'login':
            //{"type":"login","client_id":xxx,"client_name":"xxx","client_list":"[...]","time":"xxx"}
            // 如果已经在当前用户列表中，则不再提示
            chat.client_uid = data.client_uid;
            chat.client_name = data.client_name;
            if (client_list[data['client_uid']]) {
            	break;
            }

            if (data['client_list']) {
                client_list = data['client_list'];
            }
            else {
                client_list[data['client_uid']] = data['client_name'];
            }

            flush_client_list();
            if ('self' == data.scope) {
				user.login(data['client_uid'], data['client_name']);
				broadcast('您加入了群聊，开始畅所欲言吧。', data['time']);
				$(".room-user-name").text(data.client_name);
			}
			else if ('all' == data.scope) {
	            broadcast('【' + data['client_name'] + '】加入了群聊。', data['time']);
			}

            break;
        // 发言
        case 'msg':
            // {"type":"msg","from_client_id":xxx,"to_client_id":"all/client_id","content":"xxx","time":"xxx"}
            msg(data['from_client_uid'], data['from_client_name'], data['content'], data['time']);
            break;
        // 用户退出 更新用户列表
        case 'logout':
            //{"type":"logout","client_id":xxx,"time":"xxx"}
			if (data['from_client_uid']) {
            	broadcast('【' + data['from_client_name'] + '】退出了群聊。', data['time']);
            	delete client_list[data['from_client_uid']];
            	flush_client_list();
            }
            break;

        // 改名
        case 'rename':
        	broadcast(data['content'], data['time']);
        	client_list = data['client_list'];
        	flush_client_list();

        	if (user.get('client_uid') == data.client_uid) {
        		user.set('client_name', data.client_name);
        		chat.client_name = data.client_name;
        		$(".room-user-name").text(chat.client_name);
        	}

        	break;
    }

    // 消息区域滚动条到底部
	var scrollHeight = $('#dialog').prop("scrollHeight");
    $('#dialog').scrollTop(scrollHeight, 200);
}

// 输入姓名
let show_prompt = () => {
    let name = prompt('输入你的真实姓名：', '');
    if (null == name || ! $.trim(name)) {
        show_prompt();
    }

    if (! verify_name(name)) {
    	alert("提示：姓名不能超过5个字符，且不能包含特殊符号");
    	show_prompt();
    }

    return name;
}

// 改名
let rename = () => {
	let name = prompt('修改你的真实姓名：', '');
	name = $.trim(name);
	if (name) {
		if (! verify_name(name)) {
	    	return alert("提示：姓名不能超过5个字符，且不能包含特殊符号");
		}

		send({
			type: "rename",
			client_uid: chat.client_uid,
			client_name: name
		});
	}
}

let view = () => {
	let $roomUserList = $("#room-user-list");
	if ($roomUserList.is(":visible")) {
		$roomUserList.hide();
	} else {
		$roomUserList.show();
	}
}

let verify_name = (name) => {
	return ! /[\.\-\{\}\$\\]/.test(name) && name.length < 5;
}

// 提交对话
let submit_msg = (msg) => {
	send({
		type: "msg",
		to_client_uid: 'all',
		to_client_name: '',
		content: msg
	});
}

// 刷新用户列表框
let flush_client_list = () => {
	let client_name_list = Object.values(client_list),
		user_list = '';

	client_name_list.sort(function compareFunction(param1, param2) {
        return param1.localeCompare(param2, 'zh');
    });

    let sort_client_name = {},
    	client_name;
    for (let client_uid in client_list) {
    	client_name = client_list[client_uid];
    	if (null == sort_client_name[client_name]) {
    		sort_client_name[client_name] = [];
    	}

    	sort_client_name[client_name].push(client_uid);
    }

	client_name_list.forEach(function (value, index) {
		sort_client_name[value].forEach(function (uid) {
			user_list += '<li>' + value + '</li>';
		});
	});

	if (isPcClient) {
		$("#userlist > h4 > .online-count").html(client_name_list.length);
		$("#userlist > ul").html(user_list);
	} else {
		$("#room-user-list > ul").html(user_list);
	}

	document.title = "格致基因(" + client_name_list.length + "人在线）";
}

// 发言
let msg = (from_client_uid, from_client_name, content, time) => {
	let selfStyle = "";
	if (from_client_uid == loginUser.client_uid) {
		selfStyle = " msg-item-self";
		from_client_name = "我";
	}

	content = '<div class="msg-item' + selfStyle + '">'
			+ '    <div class="msg-user">'
			+ '	       <span class="msg-user-name">' + from_client_name + '</span>'
			+ '		   <span class="msg-time">' + time + '</span>'
			+ '    </div>'
			+ '    <div class="msg-content">' + content + '</div>'
			+ '</div>';

	$("#dialog").append(content).parseEmotion();
}

// 广播
let broadcast = (content, time) => {
	content = time + " " + content;
	$("#dialog").append('<div class="broadcast-item">' + content + '</div>');
}

let send = (data) => {
	ws.send(JSON.stringify(data));
}

let parse_emotion = (content) => {
    return content;
}

/**
 * 页面加载完毕后，开始建立连接
 **/
$(function () {

	connect('all');

	// 禁止刷新
	$(document).on("keydown keypress keyup", function (e) {
		let keyCode = e.keyCode || e.which;
		if (keyCode == 116) {
			return false;
		}
	});

	$("#submit-msg").on("click", function () {
		let $msg = $("#msg"),
			msg = $.trim($msg.val());

		if (! msg) {
			return $msg.val("").focus();
		}

		if (msg.length > 500) {
			return alert("您输入的内容太长");
		}

		submit_msg(msg);

		$msg.val("");
		if (isPcClient) {
			$msg.focus();
		}

		return false;
	});

	$("#client_list").on("change", function () {
		chat.client_uid = $(this).val();
		chat.client_name = $(this).children("option:selected").text();
		chat.client_list[chat.client_uid] = 1;
	});

	$("#msg").on("keydown keypress keyup", function (e) {
	    let keyCode = e.keyCode || e.which;
	    if (keyCode == 13) {
	        $("#submit-msg").click();
	        return ;
	    }
	});

	$("#rename").on("click", function () {
		rename();
	});

	$("#view").on("click", function () {
		view();
	});

	$(".face").click(function () {
		if (isPcClient) {
			let top = $("#sinaEmotion").css("top");
			top = parseInt(top) - 315;
			$("#sinaEmotion").css({
				top: top + "px"
			});
		}
	});

	if (! isPcClient) {
		document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
			WeixinJSBridge.call('hideToolbar');
		});
	}

});