#!/usr/bin/python
from vidi import Vidi

apikey = "your_api_key_goes_here" # you can learn your from your vidi account page
vidi = Vidi(apikey)
print vidi
room = vidi.create_room()
print room
client = room.create_client()
print client
input = client.create_input()
print input
output = client.create_output()
print output
binding = room.create_binding(input, output)
print binding

print vidi.get_rooms()
print vidi.get_room(room.id)
print room.get_clients()
print room.get_client(client.id)
print room.get_bindings()
print room.get_binding(binding.id)
print client.get_inputs()
print client.get_input(input.id)
print client.get_outputs()
print client.get_output(output.id)

print vidi.set_property("propertyname", "propertyvalue", access='public')
print vidi.get_property("propertyname", access='public')
print room.set_property("propertyname", "propertyvalue", access='public')
print room.get_property("propertyname", access='public')
print client.set_property("propertyname", "propertyvalue", access='public')
print client.get_property("propertyname", access='public')
print input.set_property("propertyname", "propertyvalue", access='public')
print input.get_property("propertyname", access='public')
print output.set_property("propertyname", "propertyvalue", access='public')
print output.get_property("propertyname", access='public')
print binding.set_property("propertyname", "propertyvalue", access='public')
print binding.get_property("propertyname", access='public')

# testing if binding object can populate io
binding2 = room.get_binding(binding.id)
print binding2.get_input()
print binding2.get_output()

room.send_message('hello room')
client.send_message('hello client')

desktop = vidi.get_desktop('1')
#desktop.notify('hello desktop')
desktop.ring()

binding.close()
input.close()
output.close()
client.close()
room.close()
vidi.close()

