from django import template

register = template.Library()

@register.inclusion_tag('menu.html', takes_context=True)
def menu(context):
	return {
        'request': context['request'],
		'menuitems': MenuItem.objects.all().order_by('order'),
	}
