import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;


public class Room {
	
	private String id;
	private Vidi vidi;

	public Room(Vidi vidi, String id) {
		this.id = id;
		this.vidi = vidi;
	}


	public String getId() {
		return id;
	}

	public void setId(String id) {
		this.id = id;
	}
	
	
	public Client create_client() {
		
		String clientId = "";
		
		try{
			URL url = new URL(vidi.getAddress() + "?action=createClientInRoom&apikey=" + vidi.getApikey() + "&roomid=" + id);
			HttpURLConnection conn = (HttpURLConnection) url.openConnection();
			String encoding = new sun.misc.BASE64Encoder().encode("username  password".getBytes());
			conn.setRequestProperty ("Authorization", "Basic " + encoding);
			conn.setRequestMethod("GET");

			conn.connect();
			InputStream in = conn.getInputStream();
			BufferedReader reader = new BufferedReader(new InputStreamReader(in));
			clientId = reader.readLine();
			conn.disconnect();

		}catch(IOException ex)
		{
			ex.printStackTrace();
			System.out.println("made it here");
		}
		
		return new Client(clientId);
	}
	
}
