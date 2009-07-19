import com.zeitin.vidi.*;

public class Test {

	public static void main(String[] args) {
		
		Vidi vidi = new Vidi();
		
		Room room = vidi.createRoom();
		System.out.println(room);
		
		Client client = room.create_client();
		System.out.println(client);
		
		Input input = client.createInput();
		System.out.println(input);
		
		Output output = client.createOutput();
		System.out.println(output);
		
		Binding binding = room.create_binding(input, output);
		System.out.println(binding);
		
		System.out.println(vidi.getRooms());
		System.out.println(vidi.getRoom(room.getId()));
		System.out.println(room.getClients());
		System.out.println(room.getClient(client.getId()));
		System.out.println(room.getBindings());
		System.out.println(client.getInputs());
		System.out.println(client.getInput(input.getId()));
		System.out.println(client.getOutputs());
		System.out.println(client.getOutput(output.getId()));
		
		Binding binding2 = room.getBinding(binding.getId());
		System.out.println(binding2.getInput());
		System.out.println(binding2.getOutput());
		
		room.sendMessage("hello room");
//		client.send_message("hello client");
		
		Desktop desktop = vidi.getDesktop("1");
		System.out.println(desktop);
		desktop._notify();
		desktop.ring("5");
		
		room.setProperty("name", "ugur", Vidi.PUBLIC);
		room.setProperty("surname", "gurel", Vidi.PUBLIC);
		
		System.out.println(room.getProperty("name", Vidi.PUBLIC));
		
		System.out.println(room.listProperties(Vidi.PUBLIC));
		
		System.out.println();
		System.out.println(vidi.getInitJS(room, client, "dkfh"));
		System.out.println();
		
		
		
		System.out.println(vidi.createScreen(input, output));
		
		binding.close();
		input.close();
		output.close();
		client.close();
		room.close();
//		vidi.close();	
	}

}
