from django.conf import settings
from django.http import HttpResponse
from django.shortcuts import render_to_response
from django.utils.safestring import mark_safe

from vidi import Vidi

def index(request):
    if request.GET.has_key('apikey'):
        apikey = request.GET['apikey']
        demo_apikey = apikey
    else:
        apikey = settings.VIDI_APIKEY
        demo_apikey = apikey
        
         
    return render_to_response('index.html', {
        'apikey':demo_apikey,
        'BASE_URL': settings.BASE_URL,
    })

def create_room(request):
    if request.GET.has_key('apikey'):
        apikey = request.GET['apikey']
        demo_apikey = apikey
    else:
        
        apikey = settings.VIDI_APIKEY
        demo_apikey = apikey
        
    username =request.GET['username']
#request.GET['username']
        #roomid=request.REQUEST['roomid']
        
    # create a vidi session
    vidi = Vidi(apikey)

    # create the room
    room = vidi.create_room()
    
    room.set_property("username",username,"private")
    # create a client and io
    client = room.create_client()
    input = client.create_input()
    output = client.create_output()
    
    # get vidi initialize javascript code
    vidi_init_js = vidi.get_init_js(room, client)

    # get localecho screen html/javascript code
    vidi_screen_localecho = vidi.create_screen(
        input=input,
        localecho=True,
    )
    

    # get remote screen html/javascript code
    vidi_screen_remote = vidi.create_screen(
        camera=False,
        mic=False,
        output=output,
    )

    return render_to_response('room.html', {
        'roomid': room.id,
        'vidi_init_js': mark_safe(vidi_init_js),
        'vidi_screen_localecho': mark_safe(vidi_screen_localecho),
        'vidi_screen_remote': mark_safe(vidi_screen_remote),
        'apikey':demo_apikey,
        'BASE_URL': settings.BASE_URL,
        'username': username, 
        })

def join_room(request):
    if request.GET.has_key('apikey'):
        apikey = request.GET['apikey']
        demo_apikey = apikey
    else:
        apikey = settings.VIDI_APIKEY
        demo_apikey = apikey

    # create a vidi session
    vidi = Vidi(apikey)

    # get the room
    roomid = request.REQUEST['roomid']
    room = vidi.get_room(roomid)

    # get the first client (room creator) and its io
    clients = room.get_clients()
    client1 = clients[0]
    input1 = client1.get_inputs()[0]
    output1 = client1.get_outputs()[0]

    # create the new client (joined room) and io
    client2 = room.create_client()
    input2 = client2.create_input()
    output2 = client2.create_output()

    # bind two clients (cross connection to make them talk with each other)
    binding1 = room.bind(input1, output2)
    binding2 = room.bind(input2, output1)

    # get vidi initialize javascript code
    vidi_init_js = vidi.get_init_js(room, client2)

    # get localecho screen html/javascript code
    vidi_screen_localecho = vidi.create_screen(
        localecho=True,
        input=input2,
    )

    # get remote screen html/javascript code
    vidi_screen_remote = vidi.create_screen(
        camera=False,
        mic=False,
        output=output2,
    )

    return render_to_response('room.html', {
        'roomid': room.id,
        'vidi_init_js': mark_safe(vidi_init_js),
        'vidi_screen_localecho': mark_safe(vidi_screen_localecho),
        'vidi_screen_remote': mark_safe(vidi_screen_remote),
        'apikey':demo_apikey, 
        'BASE_URL': settings.BASE_URL,
    })

