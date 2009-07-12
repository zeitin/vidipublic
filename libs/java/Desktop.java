import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;


public class Desktop {

	private Vidi vidi;
	private String id;

	public Desktop(Vidi vidi, String id) {

		this.vidi = vidi;
		this.id = id;
	}

	public Vidi getVidi() {
		return vidi;
	}

	public void setVidi(Vidi vidi) {
		this.vidi = vidi;
	}

	public String getId() {
		return id;
	}

	public void setId(String id) {
		this.id = id;
	}

	public String toString() {
		return "Vidi desktop object: " + this.id;
	}

	public void ring(String message) {

		Map<String, String> parameters = new HashMap<String, String>();
		String url = vidi.getAddress() + "/desktop/ring";
		parameters.put("apikey", vidi.getApikey());
		parameters.put("desktopid", id);
		parameters.put("message", message);
		String response = vidi.request(url, "POST", parameters);

		System.out.println("desktop ring response: " + response);
	}

	public void _notify() {

		Map<String, String> parameters = new HashMap<String, String>();
		String url = vidi.getAddress() + "/desktop/notify";
		parameters.put("apikey", vidi.getApikey());
		parameters.put("desktopid", id);
		parameters.put("message", "");
		String response = vidi.request(url, "POST", parameters);

		System.out.println("desktop _notify response: " + response);
	}
	
	public String getProperty(String key, String access) {

		return this.vidi._getProperty("desktopid", this.id, key, access);
	}

	public void setProperty(String key, String value, String access) {

		this.vidi._setProperty("desktopid", this.id, key, value, access);
	}
	
	public ArrayList<String> listProperties(String access) {

		return this.vidi._listProperties("desktopid", this.id, access);
	}
}
