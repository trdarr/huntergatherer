{# vim: ft=jinja: #}
{% extends 'layout.tpl' %}

{% block content %}
<h2>{{ feature.name }}</h2>
<div class="row">
  <div class="span-6">
    <dl class="dl-horizontal">
      <dt>Category</dt>
        <dd>{{ feature.category }}</dd>
      <dt>Note</dt>
        <dd>{{ feature.note }}</dd>
    </dl>
  </div>
</div>

<!-- Probably should group these by family. -->
<h3>Languages</h3>
<table id="languages" class="table">
  <thead>
    <tr>
      <th>Family</th>
      <th>Language</th>
      <th>Answer</th>
      <th>Source</th>
    </tr>
  </thead>
  <tbody>
  {% for language in feature.languages %}
    <tr>
      <td>{{ language.family_name }}</td>
      <td><a href="/huntergatherer/languages/{{ language.id }}">{{ language.language_name }}</a></li>
      <td>{{ language.answer }}</td>
      <td>{{ language.source }}</td>
    </tr>
  {% endfor %}
  </tbody>
</table>
{% endblock %}

