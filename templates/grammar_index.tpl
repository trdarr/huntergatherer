{# vim: ft=jinja: #}
{% extends 'layout.tpl' %}

{% block title %}Hunter-Gatherer Language Database{% endblock %}

{% block content %}
<h2>Grammatical Features
  <small>
    {{ features_count|default(0) }} features in
    {{ features|length|default(0) }} categories
  </small>
</h2>
{% if features is empty %}
  <p>No features.</p>
{% else %}
  <div id="accordion-parent" class="accordion">
  {% for category, feature_set in features %}
    <div class="accordion-group">
      <div class="accordion-heading">
        <a href="#category-{{ feature_set[0].category_id }}"
           class="accordion-toggle"
           data-toggle="collapse"
           data-parent="#accordion-parent">
             {{ category }}
             <small>({{ feature_set|length }})</small>
        </a>
      </div>
      <div id="category-{{ feature_set[0].category_id }}"
           class="accordion-body collapse">
        <div class="accordion-inner">
          <ul class="icons">
          {% for feature in feature_set %}
            <li>
              <i class="icon-asterisk"></i>
              <a href="/huntergatherer/grammar/{{ feature.id }}">
                {{ feature.name }}
              </a>
            </li>
          {% endfor %}
          </ul>
        </div>
      </div>
    </div>
  {% endfor %}
  </div>
{% endif %}
{% endblock %}

{% block scripts %}
<script src="/huntergatherer/www/js/bootstrap/bootstrap-transition.js"></script>
<script src="/huntergatherer/www/js/bootstrap/bootstrap-collapse.js"></script>
{% endblock %}

