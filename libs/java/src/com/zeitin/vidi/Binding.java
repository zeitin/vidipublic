package com.zeitin.vidi;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

public class Binding {

	private Room room;
	private String id;
	private Input input;
	private Output output;

	public Binding(Room room, String id, Input input, Output output) {
		this.room = room;
		this.id = id;
		this.input = input;
		this.output = output;
	}

	public String getId() {
		return id;
	}

	public void setId(String id) {
		this.id = id;
	}

	public Input getInput() {
		return input;
	}

	public void setInput(Input input) {
		this.input = input;
	}

	public Output getOutput() {
		return output;
	}

	public void setOutput(Output output) {
		this.output = output;
	}

	public Room getRoom() {
		return room;
	}

	public void setRoom(Room room) {
		this.room = room;
	}

	public String toString() {
		return "Vidi binding object: " + this.id;
	}

	public void close() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.room.getVidi().getAddress() + "/bindings/destroy";
		parameters.put("apikey", this.room.getVidi().getApikey());
		parameters.put("bindingid", id);
		String response = this.room.getVidi().request(url, "DELETE", parameters);
		System.out.println("binding close response: " + response);
	}
	
	public String getProperty(String key, String access) {
		return this.room.getVidi()._getProperty("bindingid", this.id, key, access);
	}

	public void setProperty(String key, String value, String access) {
		this.room.getVidi()._setProperty("bindingid", this.id, key, value, access);
	}
	
	public ArrayList<String> listProperties(String access) {
		return this.room.getVidi()._listProperties("bindingid", this.id, access);
	}
}
