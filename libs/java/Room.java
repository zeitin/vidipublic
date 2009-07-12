import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

public class Room {

	private String id;
	private Vidi vidi;

	public Room(Vidi vidi, String roomid) {
		this.id = roomid;
		this.vidi = vidi;
	}

	public String getId() {
		return id;
	}

	public void setId(String id) {
		this.id = id;
	}

	public Client create_client() {

		Map<String, String> parameters = new HashMap<String, String>();
		String url = vidi.getAddress() + "/clients/create";
		parameters.put("apikey", vidi.getApikey());
		parameters.put("roomid", id);
		String clientId = vidi.request(url, "POST", parameters);

		return new Client(this, clientId);
	}

	public Vidi getVidi() {

		return this.vidi;
	}

	public Binding create_binding(Input input, Output output) {

		Map<String, String> parameters = new HashMap<String, String>();
		String url = vidi.getAddress() + "/bindings/create";
		parameters.put("apikey", vidi.getApikey());
		parameters.put("inputid", input.getId());
		parameters.put("outputid", output.getId());
		String bindingId = vidi.request(url, "POST", parameters);

		return new Binding(this, bindingId, input, output);
	}

	public String toString() {

		return "Vidi room object: " + this.id;
	}

	public ArrayList<Client> get_clients() {

		Map<String, String> parameters = new HashMap<String, String>();
		String url = vidi.getAddress() + "/clients";
		parameters.put("apikey", vidi.getApikey());
		parameters.put("roomid", this.id);
		String response = vidi.request(url, "GET", parameters);

		String clientIds[] = response.split(" ");

		ArrayList<Client> clients = new ArrayList<Client>();

		for(int i = 0; i < clientIds.length; i++) {

			clients.add(new Client(this, clientIds[i]));
		}

		return clients;
	}

	public Client get_client(String id) {

		return new Client(this, id);
	}

	public ArrayList<Binding> get_bindings() {

		Map<String, String> parameters = new HashMap<String, String>();
		String url = vidi.getAddress() + "/bindings";
		parameters.put("apikey", vidi.getApikey());
		parameters.put("roomid", this.id);
		String response = vidi.request(url, "GET", parameters);

		String bindingIds[] = response.split(" ");

		ArrayList<Binding> bindings = new ArrayList<Binding>();


		for(int i = 0; i < bindingIds.length; i++) {

			parameters = new HashMap<String, String>();
			url += "/io";
			parameters.put("apikey", vidi.getApikey());
			parameters.put("bindingid", bindingIds[i]);
			response = vidi.request(url, "GET", parameters);
			bindings.add(new Binding(this, bindingIds[i], new Input(this, response.split(" ")[0]), new Output(this, response.split(" ")[0])));
		}

		return bindings;
	}

	public Binding get_binding(String id) {

		Map<String, String> parameters = new HashMap<String, String>();
		String url = vidi.getAddress() + "/bindings/io";
		parameters.put("apikey", vidi.getApikey());
		parameters.put("bindingid", id);
		String response = vidi.request(url, "GET", parameters);

		return new Binding(this, id, new Input(this, response.split(" ")[0]), new Output(this, response.split(" ")[1]));
	}

	public void send_message(String message) {

		Map<String, String> parameters = new HashMap<String, String>();
		String url = vidi.getAddress() + "/rooms/send_message";
		parameters.put("apikey", vidi.getApikey());
		parameters.put("roomid", id);
		parameters.put("message", message);
		String response = vidi.request(url, "POST", parameters);

		System.out.println("room send_message response: " + response);
	}

	public void close() {

		Map<String, String> parameters = new HashMap<String, String>();
		String url = this.vidi.getAddress() + "/rooms/destroy";
		parameters.put("apikey", this.vidi.getApikey());
		parameters.put("roomid", id);
		String response = this.vidi.request(url, "DELETE", parameters);

		System.out.println("room close response: " + response);
	}

	public String getProperty(String key, String access) {

		return this.vidi._getProperty("roomid", this.id, key, access);
	}

	public void setProperty(String key, String value, String access) {

		this.vidi._setProperty("roomid", this.id, key, value, access);
	}

	public ArrayList<String> listProperties(String access) {

		return this.vidi._listProperties("roomid", this.id, access);
	}
}
