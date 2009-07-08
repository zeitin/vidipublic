import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;


public class Vidi {

	private String username;
	private String password;
	private String address;
	private String apikey;
	private ArrayList<Room> rooms;

	public Vidi(String password, String username, String address) {

		this.password = password;
		this.username = username;
		this.address = address;	

		/*try{
			URL url = new URL(address + "?action=login&username=" + this.username + "&password=" + this.password);
			HttpURLConnection conn = (HttpURLConnection) url.openConnection();
			String encoding = new sun.misc.BASE64Encoder().encode("username  password".getBytes());
			conn.setRequestProperty ("Authorization", "Basic " + encoding);
			conn.setRequestMethod("GET");

			conn.connect();
			InputStream in = conn.getInputStream();
			BufferedReader reader = new BufferedReader(new InputStreamReader(in));
			this.apitoken = reader.readLine();

			conn.disconnect();

		}catch(IOException ex)
		{
			ex.printStackTrace();
			System.out.println("made it here");
		} */

		this.apikey = "a1d386c64bceaf644a8e7093d523eafb4aaa3923"; // loginden gelicek
		this.rooms = new ArrayList<Room>();
	}

	public String getApikey() {

		return this.apikey;
	}

	public ArrayList<Room> get_rooms() {

		return this.rooms;
	}

	public Room get_room(String roomid) {

		for(int i = 0; i < rooms.size(); i++) {

			if(rooms.get(i).getId().equals(roomid))
				return rooms.get(i);
		}

		return null;
	}

	public Room create_room() {

		/*String roomId = "";

		try{
			URL url = new URL(address + "?action=createRoom&apikey=" + this.apikey);
			HttpURLConnection conn = (HttpURLConnection) url.openConnection();
			String encoding = new sun.misc.BASE64Encoder().encode("username  password".getBytes());
			conn.setRequestProperty ("Authorization", "Basic " + encoding);
			conn.setRequestMethod("GET");

			conn.connect();
			InputStream in = conn.getInputStream();
			BufferedReader reader = new BufferedReader(new InputStreamReader(in));
			roomId = reader.readLine();

			conn.disconnect();

		}catch(IOException ex)
		{
			ex.printStackTrace();
			System.out.println("made it here");
		}

		return new Room(this, roomId); */
		
		Map<String, String> parameters = new HashMap<String, String>();
		String url = address + "?action=createRoom";
		parameters.put("apikey", apikey);
		String id = request(url, "GET", parameters);
		
		return new Room(this, id);

	}

	public String get_desktop(String desktopid) {

		return null;

	}

	public void close() {

	}

	public String _getattr_(String name) {

		return null;
	}

	public String _repr_() {

		return null;
	}

	public String getAddress() {
		return address;
	}

	public void setAddress(String address) {
		this.address = address;
	}

	public String request(String requestUrl, String method, Map<String, String> parameters) { 

		String id = "";
		String dataStr = "";
		Iterator<String> iterator = parameters.keySet().iterator();
		String temp;


		if(method.equals("GET")) {

			try{

				while(iterator.hasNext())
				{
					temp = iterator.next();
					dataStr = dataStr + "&" + temp + "=" + parameters.get(temp);
				}
				
				URL url = new URL(requestUrl + dataStr);
				HttpURLConnection conn = (HttpURLConnection) url.openConnection();
				String encoding = URLEncoder.encode(dataStr, "UTF-8");
				conn.setRequestProperty ("Authorization", "Basic " + encoding);
				conn.setRequestMethod(method);

				conn.connect();
				InputStream in = conn.getInputStream();
				BufferedReader reader = new BufferedReader(new InputStreamReader(in));
				id = reader.readLine();

				conn.disconnect();

			}catch(IOException ex)
			{
				ex.printStackTrace();
				System.out.println("made it here");
			}
		}
		else if(method.equals("POST")) {
		
			try{

				while(iterator.hasNext())
				{
					temp = iterator.next();
					dataStr = dataStr + "&" + temp + "=" + parameters.get(temp);
				}
				
				URL url = new URL(requestUrl);
				HttpURLConnection conn = (HttpURLConnection) url.openConnection();
				String encoding = URLEncoder.encode(dataStr, "UTF-8");
				conn.setRequestProperty ("Authorization", "Basic " + encoding);
				conn.setRequestMethod(method);

				conn.connect();
				InputStream in = conn.getInputStream();
				BufferedReader reader = new BufferedReader(new InputStreamReader(in));
				id = reader.readLine();

				conn.disconnect();

			}catch(IOException ex)
			{
				ex.printStackTrace();
				System.out.println("made it here");
			}
		}
		else if(method.equals("DELETE")) {
			
		}

		return id;
	}
}
