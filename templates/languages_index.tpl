{% extends 'layout.tpl' %}

{% block title %}Hunter-Gatherer Language Database{% endblock %}

{% block content %}
<h2>Languages
  <small>
    {{ languages_count|default(0) }} languages in
    {{ languages|length|default(0) }} families
  </small>
</h2>
{% if languages is empty %}
  <p>No languages.</p>
{% else %}
  <div id="accordion-parent" class="accordion">
  {% for family, language_set in languages %}
    <div class="accordion-group">
      <div class="accordion-heading">
        <a href="#family-{{ language_set[0].family_id }}"
           class="accordion-toggle"
           data-toggle="collapse"
           data-parent="#accordion-parent">
             {{ family }}
             <small>({{ language_set|length }})</small>
        </a>
      </div>
      <div id="family-{{ language_set[0].family_id }}"
           class="accordion-body collapse">
        <div class="accordion-inner">
          <ul class="icons">
          {% for language in language_set %}
            <li>
              <i class="icon-asterisk"></i>
              <a href="/huntergatherer/languages/{{ language.id }}">
                {{ language.name }}
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

