import httplib, urllib
import simplejson as json

REST_HOST = 'prod.vidi.zeitin.com'
REST_URL = '/vidi/rest/'

class Vidi(object):
    def __init__(self, apikey):
        self.id = self.apikey

    def get_rooms(self):
        roomids = request('rooms', 'GET', {
            'apikey': self.apikey,
        })
        return [Room(self, roomid) for roomid in roomids]

    def get_room(self, roomid):
        return Room(self, roomid)

    def create_room(self):
        roomid = request('rooms/create', 'POST', {
            'apikey': self.apikey,
        })
        return Room(self, roomid)

    def get_desktop(self, desktopid):
        return Desktop(self, desktopid)

    def close(self):
        request('session/destroy', 'DELETE', {
            'apikey': self.apikey,
        })

    # GET style rest api
    def __getattr__(self, name):
        def wrapper(**kwargs):
            return request(name, 'GET', kwargs)
        return wrapper

    def __repr__(self):
        return '<Vidi Session Object (apikey: %s)>' % self.apikey

class Room(object):
    def __init__(self, vidi, roomid):
        self.vidi = vidi
        self.roomid = roomid
        self.id = roomid

    def get_clients(self):
        clientids = request('clients', 'GET', {
            'apikey': self.vidi.apikey,
            'roomid': self.roomid,
        })
        return [Client(self.vidi, self, clientid) for clientid in clientids]

    def get_client(self, clientid):
        return Client(self.vidi, self, clientid)

    def get_bindings(self):
        bindingids = request('bindings', 'GET', {
            'apikey': self.vidi.apikey,
            'roomid': self.roomid,
        })
        return [Binding(self.vidi, self, None, None, bindingid) for bindingid in bindingids]

    def get_binding(self, bindingid):
        return Binding(self.vidi, self, None, None, bindingid)

    def create_client(self):
        clientid = request('clients/create', 'POST', {
            'apikey': self.vidi.apikey,
            'roomid': self.roomid,
        })
        return Client(self.vidi, self, clientid)

    def create_binding(self, input, output):
        bindingid = request('bindings/create', 'POST', {
            'apikey': self.vidi.apikey,
            'inputid': input.inputid,
            'outputid': output.outputid,
        })
        return Binding(self.vidi, self, input, output, bindingid)

    def send_message(self, message):
        request('rooms/send_message', 'POST', {
            'apikey': self.vidi.apikey,
            'roomid': self.roomid,
            'message': message,
        })

    def close(self):
        request('rooms/destroy', 'DELETE', {
            'apikey': self.vidi.apikey,
            'roomid': self.roomid,
        })

    def __repr__(self):
        return '<Vidi Room Object (roomid: %s)>' % self.roomid

class Client(object):
    def __init__(self, vidi, room, clientid):
        self.vidi = vidi
        self.room = room
        self.clientid = clientid
        self.id = clientid

    def get_inputs(self):
        inputids = request('inputs', 'GET', {
            'apikey': self.vidi.apikey,
            'clientid': self.clientid,
        })
        return [Input(self.vidi, self.room, self, inputid) for inputid in inputids]

    def get_input(self, inputid):
        return Input(self.vidi, self.room, self, inputid)

    def get_outputs(self):
        outputids = request('outputs', 'GET', {
            'apikey': self.vidi.apikey,
            'clientid': self.clientid,
        })
        return [Output(self.vidi, self.room, self, outputid) for outputid in outputids]

    def get_output(self, outputid):
        return Output(self.vidi, self.room, self, outputid)

    def create_input(self):
        inputid = request('inputs/create', 'POST', {
            'apikey': self.vidi.apikey,
            'clientid': self.clientid,
        })
        return Input(self.vidi, self.room, self, inputid)

    def create_output(self):
        outputid = request('outputs/create', 'POST', {
            'apikey': self.vidi.apikey,
            'clientid': self.clientid,
        })
        return Output(self.vidi, self.room, self, outputid)

    def send_message(self, message):
        request('clients/send_message', 'POST', {
            'apikey': self.vidi.apikey,
            'clientid': self.clientid,
            'message': message,
        })

    def close(self):
        request('clients/destroy', 'DELETE', {
            'apikey': self.vidi.apikey,
            'clientid': self.clientid,
        })

    def __repr__(self):
        return '<Vidi Client Object (clientid: %s)>' % self.clientid

class Input(object):
    def __init__(self, vidi, room, client, inputid):
        self.vidi = vidi
        self.room = room
        self.client = client
        self.inputid = inputid
        self.id = inputid

    def play_video(self, videofile):
        request('inputs/play_video', 'PUT', {
            'apikey': self.vidi.apikey,
            'inputid': self.inputid,
            'videofile': videofile,
        })

    def close(self):
        request('inputs/destroy', 'DELETE', {
            'apikey': self.vidi.apikey,
            'inputid': self.inputid,
        })

    def __repr__(self):
        return '<Vidi Input Object (inputid: %s)>' % self.inputid

class Output(object):
    def __init__(self, vidi, room, client, outputid):
        self.vidi = vidi
        self.room = room
        self.client = client
        self.outputid = outputid
        self.id = outputid

    def close(self):
        request('outputs/destroy', 'DELETE', {
            'apikey': self.vidi.apikey,
            'outputid': self.outputid,
        })

    def __repr__(self):
        return '<Vidi Output Object (outputid: %s)>' % self.outputid

class Binding(object):
    def __init__(self, vidi, room, input, output, bindingid):
        self.vidi = vidi
        self.room = room
        self.input = input
        self.output = output
        self.bindingid = bindingid
        self.id = bindingid

    def get_input(self):
        if self.input is None:
            self._populate_io()
        return self.input

    def get_output(self):
        if self.output is None:
            self._populate_io()
        return self.output

    def close(self):
        request('bindings/destroy', 'DELETE', {
            'apikey': self.vidi.apikey,
            'bindingid': self.bindingid,
        })

    def _populate_io(self):
        io = request('bindings/io', 'GET', {
            'apikey': self.vidi.apikey,
            'bindingid': self.bindingid,
        })
        self.input = Input(self.vidi, self.room, None, io[0])
        self.output = Output(self.vidi, self.room, None, io[1])

    def __repr__(self):
        return '<Vidi Binding Object (bindingid: %s)>' % self.bindingid

class Desktop(object):
    def __init__(self, vidi, desktopid):
        self.vidi = vidi
        self.desktopid = desktopid
        self.id = desktopid

    def notify(self, message):
        request('desktop/notify', 'POST', {
            'apikey': self.vidi.apikey,
            'desktopid': self.desktopid,
            'message': message,
        })

    def ring(self, message='1'):
        request('desktop/ring', 'POST', {
            'apikey': self.vidi.apikey,
            'desktopid': self.desktopid,
            'message': message,
        })

    def __repr__(self):
        return '<Vidi Desktop Object (desktopid: %s)>' % self.desktopid

def request(url, method, parameters):
    params = urllib.urlencode(parameters)
    conn = httplib.HTTPConnection(REST_HOST)
    if method == 'POST':
        headers = {
            "Content-type": "application/x-www-form-urlencoded",
        }
        conn.request(method, REST_URL + url, params, headers)
    else:
        headers = {}
        conn.request(method, REST_URL + url + '?' + params, '', headers)
    response = conn.getresponse()
    if response.status == 200:
        data = json.loads(response.read())
        if len(data) == 1: data = data[0]
        conn.close()
        print url + ": " + str(data)
        return data
    else:
        VidiError(response.read())

class VidiError(Exception):
    def __init__(self, value):
        self.value = value
    def __str__(self):
        return repr(self.value)

