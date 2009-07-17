package com.zeitin.vidi;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

public class Output {

	private Room room;
	private String id;

	public Output(Room room, String id) {
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
		return this.id;
	}

	public void setId(String id) {
		this.id = id;
	}
	
	public String toString() {
		return "Vidi output object: " + this.id;
	}

	public void close() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.room.getVidi().getAddress() + "/outputs/destroy";
		parameters.put("apikey", this.room.getVidi().getApikey());
		parameters.put("outputid", id);
		String response = this.room.getVidi().request(url, "DELETE", parameters);
		System.out.println("output close response: " + response);
	}
	
	public String getProperty(String key, String access) {
		return this.room.getVidi()._getProperty("outputid", this.id, key, access);
	}

	public void setProperty(String key, String value, String access) {
		this.room.getVidi()._setProperty("outputid", this.id, key, value, access);
	}
	
	public ArrayList<String> listProperties(String access) {
		return this.room.getVidi()._listProperties("outputid", this.id, access);
	}
}
