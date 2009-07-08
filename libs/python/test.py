#!/usr/bin/python
from vidi import Vidi

vidi = Vidi('VIBAPP', 'VIBAPP')
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

