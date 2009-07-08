
public class Test {

	public static void main(String[] args) {

		String REST_HOST = "http://prod.vidi.zeitin.com:5080";
		String REST_URL = "//vidi//rest";
		String username = "VIBAPP";
		String password = "VIBAPP";
		
		Vidi vidi = new Vidi(username, password, REST_HOST + REST_URL);
		
//		String id = vidi.getApitoken();
//		System.out.println(id);
		
		Room room = vidi.create_room();
		System.out.println(room.getId());
		
//		Client client = room.create_client();
//		System.out.println(client.getId());
		
		
	}

}
