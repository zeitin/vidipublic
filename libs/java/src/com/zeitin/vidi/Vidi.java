package com.zeitin.vidi;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLConnection;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import org.json.simple.JSONValue;

public class Vidi {

	public static final String PRIVATE = "private";
	public static final String PUBLIC = "public";
	private String address;
	private String apikey;
	private String REST_HOST = "http://api.vidi.zeitin.com";
	private String REST_URL = "/vidi/rest";

	public Vidi() {
		this.address = REST_HOST + REST_URL;
		this.apikey = "a1d386c64bceaf644a8e7093d523eafb4aaa3923";
	}

	public String getApikey() {
		return this.apikey;
	}

	public ArrayList<Room> getRooms() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = address + "/rooms";
		parameters.put("apikey", apikey);
		String response = request(url, "GET", parameters);
		String roomIds[] = response.split(" ");
		ArrayList<Room> rooms = new ArrayList<Room>();
		for (int i = 0; i < roomIds.length; i++) {
			rooms.add(new Room(this, roomIds[i]));
		}
		return rooms;
	}

	public Room getRoom(String roomid) {
		return new Room(this, roomid);
	}

	public Room createRoom() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = address + "/rooms/create";
		parameters.put("apikey", apikey);
		String roomId = request(url, "POST", parameters);
		return new Room(this, roomId);
	}

	public Desktop getDesktop(String desktopid) {
		return new Desktop(this, desktopid);
	}

	public void close() {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.address + "/session/destroy";
		parameters.put("apikey", this.apikey);
		String response = request(url, "DELETE", parameters);
		System.out.println("vidi close response: " + response);
	}

	public String getAddress() {
		return address;
	}

	public void setAddress(String address) {
		this.address = address;
	}

	public String request(String requestUrl, String method,
			Map<String, String> parameters) {
		String json = "";
		String data = "";
		Iterator<String> iterator = parameters.keySet().iterator();
		String next;
		if (method.equals("GET") || method.equals("DELETE")) {
			try {
				next = iterator.next();
				data = next + "=" + parameters.get(next);
				while (iterator.hasNext()) {
					next = iterator.next();
					data += "&" + next + "=" + parameters.get(next);
				}
				URL url = new URL(requestUrl + "?" + data);
				HttpURLConnection conn = (HttpURLConnection) url
				.openConnection();
				String encoding = URLEncoder.encode(data, "UTF-8");
				conn.setRequestProperty("Authorization", "Basic " + encoding);
				conn.setRequestMethod(method);
				conn.connect();
				InputStream in = conn.getInputStream();
				BufferedReader reader = new BufferedReader(
						new InputStreamReader(in));
				json = reader.readLine();
				conn.disconnect();
			} catch (IOException ex) {
				ex.printStackTrace();
				System.out.println("made it here");
			}
		} else if (method.equals("POST")) {
			try {
				next = iterator.next();
				data = URLEncoder.encode(next, "UTF-8") + "="
				+ URLEncoder.encode(parameters.get(next), "UTF-8");
				while (iterator.hasNext()) {
					next = iterator.next();
					data += "&" + URLEncoder.encode(next, "UTF-8") + "="
					+ URLEncoder.encode(parameters.get(next), "UTF-8");
				}
				// Send data
				URL url = new URL(requestUrl);
				URLConnection conn = url.openConnection();
				conn.setDoOutput(true);
				OutputStreamWriter wr = new OutputStreamWriter(conn
						.getOutputStream());
				wr.write(data);
				wr.flush();
				// Get the response
				BufferedReader rd = new BufferedReader(new InputStreamReader(
						conn.getInputStream()));
				json = rd.readLine();
				wr.close();
				rd.close();

			} catch (IOException ex) {
				ex.printStackTrace();
				System.out.println("made it here");
			}
		}
		Object obj = JSONValue.parse(json);
		JSONArray array = (JSONArray) obj;
		String response = array.get(0).toString();
		for (int i = 1; i < array.size(); i++) {
			response += " " + array.get(i).toString();
		}
		return response;
	}

	// TODO make access parameter optional
	public String _getProperty(String where, String id, String key,
			String access) {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = address + "/properties/get";
		parameters.put("apikey", this.apikey);
		parameters.put("where", where);
		parameters.put("id", id);
		parameters.put("key", key);
		parameters.put("?access", access);
		String value = request(url, "GET", parameters);
		return value;
	}

	public void _setProperty(String where, String id, String key, String value,
			String access) {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = address + "/properties/set";
		parameters.put("apikey", this.apikey);
		parameters.put("where", where);
		parameters.put("id", id);
		parameters.put("key", key);
		parameters.put("value", value);
		parameters.put("?access", access);
		String response = request(url, "POST", parameters);
		System.out.println(response);
	}

	public ArrayList<String> _listProperties(String where, String id,
			String access) {
		Map<String, String> parameters = new HashMap<String, String>();
		String url = address + "/properties";
		parameters.put("apikey", this.apikey);
		parameters.put("where", where);
		parameters.put("id", id);
		parameters.put("?access", access);
		String response = request(url, "GET", parameters);
		String keys[] = response.split(" ");
		ArrayList<String> properties = new ArrayList<String>();
		for (int i = 0; i < keys.length; i++) {
			properties.add(keys[i]);
		}
		return properties;
	}

	public String getProperty(String key, String access) {
		return _getProperty("apikey", this.apikey, key, access);
	}

	public void setProperty(String key, String value, String access) {
		_setProperty("apikey", this.apikey, key, value, access);
	}

	public ArrayList<String> listProperties(String access) {
		return _listProperties("apikey", this.apikey, access);
	}

	public String getInitJS(Room room, Client client, String callback,
			boolean debug) {
		HashMap<String, String> params = new HashMap<String, String>();
		params.put("clientid", client.getId());
		params.put("roomid", room.getId());
		params.put("debug", String.valueOf(debug));
		if(callback != null) {
			params.put("callback", callback);
		}
		String format = "<script type=\"text/javascript\" src=\"" + REST_HOST
		+ "/vidi/static/vidi.js\"></script>";
		format += "\n<script type=\"text/javascript\">";
		format += "\nvidi.initialize(";
		Object obj = JSONObject.toJSONString(params);
		format += obj.toString() + ");";
		format += "\n</script>";
		return format;
	}

	public String getInitJS(Room room, Client client, String callback) {
		return getInitJS(room, client, callback, false);
	}

	public String getInitJS(Room room, Client client, boolean debug) {
		return getInitJS(room, client, null, debug);
	}

	public String getInitJS(Room room, Client client) {
		return getInitJS(room, client, null, false);
	}

	public String createScreen(Object... args) {
		String divid = "vidi_screen_" + (int) (Math.random() * 10000 + 1);
		HashMap<String, String> params = new HashMap<String, String>();
		if(contain("Input", args) != -1) {	
			Input input = (Input) args[contain("Input", args)];
			params.put("inputid", input.getId());
		}
		if(contain("Output", args) != -1) {	
			Output output = (Output) args[contain("Output", args)];
			params.put("outputid", output.getId());
		}
		String html;
		if(contain("String", args) != -1) {
			html = "<script type=\"text/javascript\">";
			html += "\nvidi.createScreen(";
			html += JSONObject.toJSONString(params).toString() + ");";;
			html += "\n</script>";
		} else {
			html = "<div id=\"" + divid + "\"></div>";
			html += "<script type=\"text/javascript\">";
			html += "\nvidi.createScreen(";
			html += JSONObject.toJSONString(params).toString() + ");";
			html += "\n</script>";
		}
		return html;
	}

	private int contain(String key, Object args[]) {
		for(int i = 0; i < args.length; i++) {
			if(args[i].getClass().getName().equals(key)) {
				return i;
			}
		}
		return -1;
	}
}
