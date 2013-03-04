{% extends 'layout.tpl' %}

{% block content %}
  {{ about|raw }}
{% endblock %}

{% block footer %}
<p><small class="muted">See: above.</small></p>
{% endblock %}
