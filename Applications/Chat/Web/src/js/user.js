
export default class User
{
	constructor()
	{
		this.field = "user";
		this.getData();
	}

	/**
	 * 用户保活校验
	 *
	 * 7天之内，保持登录状态
	 */
	check()
	{
		if (null == this.data) {
			return false;
		}

		if (null == this.data.heartbeat) {
			return false;
		}

		return new Date().getTime() - this.data.heartbeat <= 7 * 24 * 3600 * 1000;
	}

	keepAlive()
	{
		this.set("heartbeat", new Date().getTime());
	}

	login(client_uid, client_name)
	{
		this.set({
			client_uid: client_uid,
			client_name: client_name,
			heartbeat: new Date().getTime()
		});
	}

	rename(value)
	{
		this.set("client_name", value);
	}

	set(key, value)
	{
		let user;
		if (typeof key == "object") {
			user = key;
		}
		else if (typeof key == "string") {
			user = this.get();
			if (null != user) {
				user[key] = value;
			}
		}

		if (null != user) {
			user = JSON.stringify(user);
			window.localStorage.setItem(this.field, user);
		}
	}

	get(key)
	{
		if (null == this.data) {
			return null;
		}

		return null == key ? this.data
						   : (this.data[key] || null);
	}

	/**
	 * data, object
	 *
	 * client_uid: string
	 * client_name: string
	 * heartbeat: int
	 */
	getData()
	{
		this.data = window.localStorage.getItem(this.field) || null;
		if (null != this.data) {
			this.data = JSON.parse(this.data);
		}
	}

}
