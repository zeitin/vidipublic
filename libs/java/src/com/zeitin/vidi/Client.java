package com.zeitin.vidi;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

public class Client {

	private Room room;
	private String id;


	public Client(Room room, String id) {
		this.id = id;
		this.room = room;
	}

	public String getId() {
		return id;
	}

	public void setId(String id) {
		this.id = id;
	}

	public Room getRoom() {
		return room;
	}

	public void setRoom(Room room) {
		this.room = room;
	}

	public Input createInput() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.room.getVidi().getAddress() + "/inputs/create";
		parameters.put("apikey", this.room.getVidi().getApikey());
		parameters.put("clientid", this.id);
		String inputId = this.room.getVidi().request(url, "POST", parameters);
		return new Input(this.room, inputId);
	}

	public Output createOutput() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.room.getVidi().getAddress() + "/outputs/create";
		parameters.put("apikey", this.room.getVidi().getApikey());
		parameters.put("clientid", this.id);
		String outputId = this.room.getVidi().request(url, "POST", parameters);
		return new Output(this.room, outputId);
	}

	public String toString() {
		return "Vidi client object: " + this.id;
	}

	public ArrayList<Input> getInputs() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.room.getVidi().getAddress() + "/bindings/io";
		parameters.put("apikey", this.room.getVidi().getApikey());
		ArrayList<Binding> bindings = this.room.getBindings();
		ArrayList<Input> inputs = new ArrayList<Input>();
		String response;
		for(int i = 0; i < bindings.size(); i++) {
			parameters.put("bindingid", bindings.get(i).getId());
			response = this.room.getVidi().request(url, "GET", parameters);
			inputs.add(new Input(this.room, response.split(" ")[0]));
		}
		return inputs;
	}

	public ArrayList<Output> getOutputs() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.room.getVidi().getAddress() + "/bindings/io";
		parameters.put("apikey", this.room.getVidi().getApikey());
		ArrayList<Binding> bindings = this.room.getBindings();
		ArrayList<Output> outputs = new ArrayList<Output>();
		String response;
		for(int i = 0; i < bindings.size(); i++) {
			parameters.put("bindingid", bindings.get(i).getId());
			response = this.room.getVidi().request(url, "GET", parameters);
			outputs.add(new Output(this.room, response.split(" ")[1]));
		}
		return outputs;
	}

	public Input getInput(String id) {
		return new Input(this.room, id);
	}

	public Output getOutput(String id) {
		return new Output(this.room, id);
	}

	public void sendMessage(String message) {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.room.getVidi().getAddress() + "/clients/send_message";
		parameters.put("apikey", this.room.getVidi().getApikey());
		parameters.put("clientid", id);
		parameters.put("message", message);
		String response = this.room.getVidi().request(url, "POST", parameters);
		System.out.println(response);	
	}

	public void close() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.room.getVidi().getAddress() + "/clients/destroy";
		parameters.put("apikey", this.room.getVidi().getApikey());
		parameters.put("clientid", id);
		String response = this.room.getVidi().request(url, "DELETE", parameters);
		System.out.println("client close response: " + response);
	}
	
	public String getProperty(String key, String access) {
		return this.room.getVidi()._getProperty("clientid", this.id, key, access);
	}

	public void setProperty(String key, String value, String access) {
		this.room.getVidi()._setProperty("clientid", this.id, key, value, access);
	}
	
	public ArrayList<String> listProperties(String access) {
		return this.room.getVidi()._listProperties("clientid", this.id, access);
	}
}
