{% extends 'layout.tpl' %}

{% block title %}Hunter-Gatherer Language Database{% endblock %}

{% block content %}
<h2>Languages</h2>
{% if languages is empty %}
  <p>No languages.</p>
{% else %}
  <p>Found <strong>{{ languages | length }}</strong> language(s).</p>
  <ul class="unstyled">
  {% for language in languages %}
    <li><a href="/huntergatherer/languages/{{ language.id }}">{{ language.name }}</a></li>
  {% endfor %}
  </ul>
{% endif %}
{% endblock %}

