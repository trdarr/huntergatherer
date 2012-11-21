{% extends 'layout.tpl' %}

{% block title %}Hunter-Gatherer Language Database{% endblock %}

{% block flash %}
{% if errors is empty %}{% else %}
<div class="alert alert-block alert-error">
  <h3><i class="icon-warning-sign icon-large"></i> <strong>Error!</strong></h3>
  <p>These things went wrong:</p>
  <ul>
    {% for error in errors %}
      <li>{{ error }}</li>
    {% endfor %}
  </ul>
</div>
{% endif %}
{% endblock %}

{% block content %}
<p><a class="btn" href="/huntergatherer/languages"><i class="icon-arrow-left"></i> Languages</a></p>

<h2>{{ language.language_name }} <small>({{ language.family_name }})</small></h2>

<h3>Grammatical features</h3>
{% if grammatical_features is empty %}
  <p>No grammatical features.</p>
{% else %}
  <table id="grammatical-features" class="table">
    <thead>
      <tr>
        <th>Category</th>
        <th>Feature</th>
        <th>Note</th>
        <th>Answer</th>
        <th>Source</th>
      </tr>
    </thead>
    <tbody>
    {% for feature in grammatical_features %}
      <tr>
        <td>{{ feature.category }}</td>
        <td>{{ feature.name }}</td>
        <td>{{ feature.note }}</td>
        <td>{{ feature.answer }}</td>
        <td>{{ feature.source }}</td>
      </tr>
    {% endfor %}
    </tbody>
  </table>
{% endif %}

<h3>Vocabulary features</h3>
{% if vocabulary_features is empty %}
  <p>No vocabulary features.</p>
{% else %}
  <table id="vocabulary-features" class="table table-condensed table-hover">
    <thead>
      <tr>
        <th>English</th>
        <th>Semantic field</th>
        <th>Part of speech</th>
        <th>Original form</th>
        <th>IPA form</th></tr>
    </tr>
    </thead>
    <tbody>
    {% for feature in vocabulary_features %}
      <tr>
        <td>{{ feature.english }}</td>
        <td>{{ feature.field_name }}</td>
        <td>{{ feature.pos_name }}</td>
        <td>{{ feature.original_form }}</td>
        <td>{{ feature.ipa_form }}</td>
      </tr>
    {% endfor %}
    </tbody>
  </table>
{% endif %}
{% endblock %}

