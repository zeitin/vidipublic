
public class Test {

	public static void main(String[] args) {

		//String REST_HOST = "http://prod.vidi.zeitin.com";
		String REST_HOST = "http://192.168.199.69";
		String REST_URL = "/vidi/rest";
		
		Vidi vidi = new Vidi(REST_HOST + REST_URL);
		
		Room room = vidi.create_room();
		System.out.println(room);
		
		Client client = room.create_client();
		System.out.println(client);
		
		Input input = client.create_Input();
		System.out.println(input);
		
		Output output = client.create_Output();
		System.out.println(output);
		
		Binding binding = room.create_binding(input, output);
		System.out.println(binding);
		
		System.out.println(vidi.get_rooms());
		System.out.println(vidi.get_room(room.getId()));
		System.out.println(room.get_clients());
		System.out.println(room.get_client(client.getId()));
		System.out.println(room.get_bindings());
		System.out.println(client.get_inputs());
		System.out.println(client.get_input(input.getId()));
		System.out.println(client.get_outputs());
		System.out.println(client.get_output(output.getId()));
		
		Binding binding2 = room.get_binding(binding.getId());
		System.out.println(binding2.getInput());
		System.out.println(binding2.getOutput());
		
		room.send_message("hello room");
//		client.send_message("hello client");
		
		Desktop desktop = vidi.get_desktop("1");
		System.out.println(desktop);
		desktop._notify();
		desktop.ring("5");
		
		
		room.setProperty("name", "ugur", Vidi.PUBLIC);
		room.setProperty("surname", "gurel", Vidi.PUBLIC);
		
		System.out.println(room.getProperty("name", Vidi.PUBLIC));
		
		System.out.println(room.listProperties(Vidi.PUBLIC));
		
		
		binding.close();
		input.close();
		output.close();
		client.close();
		room.close();
//		vidi.close();	
	}

}
