from django.conf import settings
from django.http import HttpResponse
from django.shortcuts import render_to_response
from vidi.vidi import Vidi

def index(request):
    return render_to_response('index.html')

def create_room(request):
    vidi = Vidi(settings.VIDI_APIKEY)
    room = vidi.create_room()
    return render_to_response('room.html')

def join_room(request):
    return render_to_response('room.html')

