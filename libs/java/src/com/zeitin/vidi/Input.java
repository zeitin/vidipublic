package com.zeitin.vidi;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

public class Input {

	private Room room;
	private String id;

	public Input(Room room, String id) {
		this.room = room;
		this.id = id;
	}

	public Room getRoom() {
		return room;
	}

	public void setRoom(Room room) {
		this.room = room;
	}

	public String getId() {
		return id;
	}

	public void setId(String id) {
		this.id = id;
	}

	public String toString() {
		return "Vidi input object: " + this.id;
	}

	public void close() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.room.getVidi().getAddress() + "/inputs/destroy";
		parameters.put("apikey", this.room.getVidi().getApikey());
		parameters.put("inputid", id);
		String response = this.room.getVidi().request(url, "DELETE", parameters);
		System.out.println("input close response: " + response);
	}
	
	public void playVideo(String videofile) {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.room.getVidi().getAddress() + "/clients/create";
		parameters.put("apikey", this.room.getVidi().getApikey());
		parameters.put("inputid", id);
		parameters.put("videofile", videofile);
		String response = this.room.getVidi().request(url, "POST", parameters);
		System.out.println(response);
	}
	
	public String getProperty(String key, String access) {
		return this.room.getVidi()._getProperty("inputid", this.id, key, access);
	}

	public void setProperty(String key, String value, String access) {
		this.room.getVidi()._setProperty("inputid", this.id, key, value, access);
	}
	
	public ArrayList<String> listProperties(String access) {
		return this.room.getVidi()._listProperties("inputid", this.id, access);
	}
}
