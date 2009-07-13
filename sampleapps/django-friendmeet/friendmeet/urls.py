from django.conf.urls.defaults import *

urlpatterns = patterns('',
    (r'^$', 'friendmeet.views.index'),
    (r'^create_room/$', 'friendmeet.views.create_room'),
    (r'^join_room/$', 'friendmeet.views.join_room'),
)
