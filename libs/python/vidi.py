import httplib, urllib
import simplejson as json
import random


class Vidi(object):
    def __init__(self, apikey, server="api.vidi.zeitin.com"):
        self.apikey = apikey
        self.id = apikey
        self.rest_host = server

    def get_rooms(self):
        roomids = self.request('rooms', 'GET')
        return [Room(self, roomid) for roomid in roomids]

    def get_room(self, roomid):
        return Room(self, roomid)

    def create_room(self):
        roomid = self.request('rooms/create', 'POST')[0]
        return Room(self, roomid)

    def get_property(self, key, access='private'):
        value = self.request('properties/get', 'GET', {
            'where': 'apikey',
            'id': self.id,
            'key': key,
            'access': access,
        })
        return value and value[0]

    def set_property(self, key, value, access='private'):
        result = self.request('properties/set', 'POST', {
            'where': 'apikey',
            'id': self.id,
            'key': key,
            'value': value,
            'access': access,
        })[0]
        return result

    def get_desktop(self, desktopid):
        return Desktop(self, desktopid)

    def close(self):
        self.request('session/destroy', 'DELETE')

    def get_init_js(self, room, client, callback=False, debug=False):
        if isinstance(client, Client) == False:
            raise VidiError('Wrong Client object')
        if isinstance(room, Room) == False:
            raise VidiError('Wrong Room object')

        params = {
            'clientid': client.id,
            'roomid': room.id,
            'debug': debug,
        }

        if callback:
            params.update({'callback': callback})

        return """
<script type="text/javascript" src="%(vidi_js)s"></script>
<script type="text/javascript">
vidi.initialize(%(params)s);
</script>
        """ % {
            'vidi_js': 'http://' + self.rest_host + '/vidi/static/vidi.js',
            'params': json.dumps(params),
        }

    def create_screen(self, **kwargs):
        #TODO: validate params
        divid = 'vidi_screen_' + str(random.randint(1, 10000))
        self.oo = kwargs
        if self.oo.has_key('input'):
            if isinstance(self.oo['input'], Input) == False:
                raise VidiError('Wrong Input object')
            self.oo['inputid'] = self.oo['input'].id
            del self.oo['input']
        if self.oo.has_key('output'):
            if isinstance(self.oo['output'], Output) == False:
                raise VidiError('Wrong Output object')
            self.oo['outputid'] = self.oo['output'].id
            del self.oo['output']
        if self.oo.has_key('divid'):
            html = """
<script type="text/javascript">
vidi.createScreen(%(params)s);
</script>
            """ % {
                'params': json.dumps(self.oo),
            }
        else:
            self.oo['divid'] = divid
            html = """
<div id="%(divid)s"></div>
<script type="text/javascript">
    vidi.createScreen(%(params)s);
</script>
            """ % {
                'params': json.dumps(self.oo),
                'divid': divid,
            }
        self.__dict__.update(self.oo)
        return html

    def request(self, url, method, parameters={}):
        path = '/vidi/rest/' + url
        parameters['apikey'] = self.apikey
        params = urllib.urlencode(parameters)
        conn = httplib.HTTPConnection(self.rest_host)
        try:
            if method == 'POST':
                headers = {
                    "Content-type": "application/x-www-form-urlencoded",
                }
                conn.request(method, path, params, headers)
            else:
                headers = {}
                conn.request(method, path + '?' + params, '', headers)
        except Exception, e:
            raise VidiError(e)
        response = conn.getresponse()
        if response.status == 200:
            data = json.loads(response.read())
            try:
                if data.has_key('error') and data['error']:
                    raise VidiError(data['error_desc'])
            except AttributeError:
                pass
            conn.close()
            return data
        else:
            raise VidiError(response.read())

    # GET style rest api
    def __getattr__(self, name):
        def wrapper(**kwargs):
            return self.request(name, 'GET', kwargs)
        return wrapper

    def __repr__(self):
        return '<Vidi Session Object (apikey: %s, server: %s)>' % (self.apikey, self.rest_host)

class Room(object):
    def __init__(self, vidi, roomid):
        self.vidi = vidi
        self.roomid = roomid
        self.id = roomid

    def get_clients(self):
        clientids = self.vidi.request('clients', 'GET', {
            'roomid': self.roomid,
        })
        return [Client(self.vidi, self, clientid) for clientid in clientids]

    def get_client(self, clientid):
        return Client(self.vidi, self, clientid)

    def get_bindings(self):
        bindingids = self.vidi.request('bindings', 'GET', {
            'roomid': self.roomid,
        })
        return [Binding(self.vidi, self, None, None, bindingid) for bindingid in bindingids]

    def get_binding(self, bindingid):
        return Binding(self.vidi, self, None, None, bindingid)

    def create_client(self):
        clientid = self.vidi.request('clients/create', 'POST', {
            'roomid': self.roomid,
        })[0]
        return Client(self.vidi, self, clientid)

    def bind(self, input, output):
        bindingid = self.vidi.request('bindings/create', 'POST', {
            'inputid': input.inputid,
            'outputid': output.outputid,
        })[0]
        return Binding(self.vidi, self, input, output, bindingid)

    def send_message(self, message):
        self.vidi.request('rooms/send_message', 'POST', {
            'roomid': self.roomid,
            'message': message,
        })

    def close(self):
        self.vidi.request('rooms/destroy', 'DELETE', {
            'roomid': self.roomid,
        })

    def get_property(self, key, access='private'):
        value = self.vidi.request('properties/get', 'GET', {
            'where': 'roomid',
            'id': self.roomid,
            'key': key,
            'access': access,
        })
        return value and value[0]

    def set_property(self, key, value, access='private'):
        result = self.vidi.request('properties/set', 'POST', {
            'where': 'roomid',
            'id': self.roomid,
            'key': key,
            'value': value,
            'access': access,
        })[0]
        return result

    def __repr__(self):
        return '<Vidi Room Object (roomid: %s)>' % self.roomid

class Client(object):
    def __init__(self, vidi, room, clientid):
        self.vidi = vidi
        self.room = room
        self.clientid = clientid
        self.id = clientid

    def get_inputs(self):
        inputids = self.vidi.request('inputs', 'GET', {
            'clientid': self.clientid,
        })
        return [Input(self.vidi, self.room, self, inputid) for inputid in inputids]

    def get_input(self, inputid):
        return Input(self.vidi, self.room, self, inputid)

    def get_outputs(self):
        outputids = self.vidi.request('outputs', 'GET', {
            'clientid': self.clientid,
        })
        return [Output(self.vidi, self.room, self, outputid) for outputid in outputids]

    def get_output(self, outputid):
        return Output(self.vidi, self.room, self, outputid)

    def create_input(self):
        inputid = self.vidi.request('inputs/create', 'POST', {
            'clientid': self.clientid,
        })[0]
        return Input(self.vidi, self.room, self, inputid)

    def create_output(self):
        outputid = self.vidi.request('outputs/create', 'POST', {
            'clientid': self.clientid,
        })[0]
        return Output(self.vidi, self.room, self, outputid)

    def send_message(self, message):
        self.vidi.request('clients/send_message', 'POST', {
            'clientid': self.clientid,
            'message': message,
        })

    def close(self):
        self.vidi.request('clients/destroy', 'DELETE', {
            'clientid': self.clientid,
        })

    def get_property(self, key, access='private'):
        value = self.vidi.request('properties/get', 'GET', {
            'where': 'clientid',
            'id': self.clientid,
            'key': key,
            'access': access,
        })
        return value and value[0]

    def set_property(self, key, value, access='private'):
        result = self.vidi.request('properties/set', 'POST', {
            'where': 'clientid',
            'id': self.clientid,
            'key': key,
            'value': value,
            'access': access,
        })[0]
        return result

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
        self.vidi.request('inputs/play_video', 'PUT', {
            'inputid': self.inputid,
            'videofile': videofile,
        })

    def close(self):
        self.vidi.request('inputs/destroy', 'DELETE', {
            'inputid': self.inputid,
        })

    def play_video(self, videofile):
        self.vidi.request('inputs/play_video', 'GET', {
            'inputid': self.inputid,
            'videofile': videofile,
        })

    def is_active(self):
        value = self.vidi.request('inputs/isactive', 'GET', {
            'inputid': self.inputid,
        })
        return value and value[0]

    def get_property(self, key, access='private'):
        value = self.vidi.request('properties/get', 'GET', {
            'where': 'inputid',
            'id': self.inputid,
            'key': key,
            'access': access,
        })
        return value and value[0]

    def set_property(self, key, value, access='private'):
        result = self.vidi.request('properties/set', 'POST', {
            'where': 'inputid',
            'id': self.inputid,
            'key': key,
            'value': value,
            'access': access,
        })[0]
        return result

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
        self.vidi.request('outputs/destroy', 'DELETE', {
            'outputid': self.outputid,
        })

    def is_active(self):
        value = self.vidi.request('outputs/isactive', 'GET', {
            'outputid': self.outputid,
        })
        return value and value[0]

    def get_property(self, key, access='private'):
        value = self.vidi.request('properties/get', 'GET', {
            'where': 'outputid',
            'id': self.outputid,
            'key': key,
            'access': access,
        })
        return value and value[0]

    def set_property(self, key, value, access='private'):
        result = self.vidi.request('properties/set', 'POST', {
            'where': 'outputid',
            'id': self.outputid,
            'key': key,
            'value': value,
            'access': access,
        })[0]
        return result

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
        self.vidi.request('bindings/destroy', 'DELETE', {
            'bindingid': self.bindingid,
        })

    def _populate_io(self):
        io = self.vidi.request('bindings/io', 'GET', {
            'bindingid': self.bindingid,
        })
        self.input = Input(self.vidi, self.room, None, io[0])
        self.output = Output(self.vidi, self.room, None, io[1])

    def is_active(self):
        value = self.vidi.request('bindings/isactive', 'GET', {
            'inputid': self.bindingid,
        })
        return value and value[0]

    def get_property(self, key, access='private'):
        value = self.vidi.request('properties/get', 'GET', {
            'where': 'bindingid',
            'id': self.bindingid,
            'key': key,
            'access': access,
        })
        return value and value[0]

    def set_property(self, key, value, access='private'):
        result = self.vidi.request('properties/set', 'POST', {
            'where': 'bindingid',
            'id': self.bindingid,
            'key': key,
            'value': value,
            'access': access,
        })[0]
        return result

    def __repr__(self):
        return '<Vidi Binding Object (bindingid: %s)>' % self.bindingid

class Desktop(object):
    def __init__(self, vidi, desktopid):
        self.vidi = vidi
        self.desktopid = desktopid
        self.id = desktopid

    def notify(self, message):
        self.vidi.request('desktop/notify', 'POST', {
            'desktopid': self.desktopid,
            'message': message,
        })

    def ring(self, count='1'):
        self.vidi.request('desktop/ring', 'POST', {
            'desktopid': self.desktopid,
            'count': count,
        })

    def __repr__(self):
        return '<Vidi Desktop Object (desktopid: %s)>' % self.desktopid

class VidiError(Exception):
    def __init__(self, value):
        self.value = value
    def __str__(self):
        return repr(self.value)

