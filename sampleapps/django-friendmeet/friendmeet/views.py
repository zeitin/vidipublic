from django.conf import settings
from django.http import HttpResponse
from django.shortcuts import render_to_response
from vidi.vidi import Vidi
from django.utils.safestring import mark_safe

vidi = Vidi(settings.VIDI_APIKEY)

def index(request):
    return render_to_response('index.html')

def create_room(request):
    room = vidi.create_room()
    client = room.create_client()
    input = client.create_input()
    output = client.create_output()

    vidi_init_js = vidi.get_init_js(room, client)
    vidi_screen_localecho = vidi.create_screen(
        localecho=True,
    )
    vidi_screen_remote = vidi.create_screen(
        input=input,
        output=output,
    )

    return render_to_response('room.html', {
        'roomid': room.id,
        'vidi_init_js': mark_safe(vidi_init_js),
        'vidi_screen_localecho': mark_safe(vidi_screen_localecho),
        'vidi_screen_remote': mark_safe(vidi_screen_remote),
    })

def join_room(request):
    roomid = request.REQUEST['roomid']
    room = vidi.get_room(roomid)

    # get the first client (room creator)
    clients = room.get_clients()
    client1 = clients[0]
    input1 = client1.get_inputs()[0]
    output1 = client1.get_outputs()[0]

    # create our client
    client2 = room.create_client()
    input2 = client2.create_input()
    output2 = client2.create_output()

    # bind two clients
    binding1 = room.bind(input1, output2)
    binding2 = room.bind(input2, output1)

    vidi_init_js = vidi.get_init_js(room, client2)
    vidi_screen_localecho = vidi.create_screen(
        localecho=True,
    )
    vidi_screen_remote = vidi.create_screen(
        input=input2,
        output=output2,
    )

    return render_to_response('room.html', {
        'roomid': room.id,
        'vidi_init_js': mark_safe(vidi_init_js),
        'vidi_screen_localecho': mark_safe(vidi_screen_localecho),
        'vidi_screen_remote': mark_safe(vidi_screen_remote),
    })

