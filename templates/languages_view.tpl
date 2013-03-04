{% extends 'layout.tpl' %}

{% block scripts %}
  <script src="/huntergatherer/www/js/bootstrap/bootstrap-transition.js"></script>
  <script src="/huntergatherer/www/js/bootstrap/bootstrap-collapse.js"></script>
  <script src="/huntergatherer/www/js/editablegrid/editablegrid.js"></script>
  <script src="/huntergatherer/www/js/editablegrid/editablegrid_editors.js"></script>
  <script src="/huntergatherer/www/js/editablegrid/editablegrid_renderers.js"></script>
  <script src="/huntergatherer/www/js/editablegrid/editablegrid_utils.js"></script>
  <script>
    var language_id = {{ language.language_id }};
    $(function() {
      var features = {{ grammatical_features|json_encode|raw }};
      for (category in features) {
        // Replace commas and spaces with hyphens.
        // (Matches the Twig filter in the accordion.)
        var id = 'grammar-' + category.toLowerCase().replace(/[ ,]+/g, '-');

        var grammar_grid = new EditableGrid(id);

        /* Passing this as the second argument sends a request to the
         * server when an edit is made. Handle it there for permanence.
        {
          modelChanged: function(row_index, column_index, old_value, new_value, row) {
            $.ajax('/huntergatherer/languages/edit', {
              data: {
                language: language_id,
                row_index: row_index,
                column_index: column_index,
                old_value: old_value,
                new_value: new_value
              },
              failure: function() { console.error('Fission mailed.'); },
              success: function() { console.log('Mission complete.'); },
              type: 'put'
            });
          }
        }
        */

        grammar_grid.load(features[category]);
        grammar_grid.renderGrid(id, 'table table-condensed');
      }
    });
  </script>
{% endblock %}

{% block content %}
<h2>{{ language.language_name }}</h2>
<dl class="dl-horizontal">
  <dt>Family</dt>
    <dd>{{ language.family_name }}</dd>
  <dt>Region</dt>
    <dd>{{ language.region_name }}</dd>
  <dt>ISO 639-2</dt>
    <dd>{{ language.iso }}</dd>
  <dt>Location</dt>
    <dd>
      {{ language.latitude|number_format(2, '.') }}&deg;,
      {{ language.longitude|number_format(2, '.') }}&deg;
    </dd>
  <dt>Notes</dt>
    <dd>{{ language.notes }}</dd>
</dl>

<h3>Features</h3>
<div id="accordion-parent" class="accordion">
  <div class="accordion-group">
    <div class="accordion-heading">
      <a href="#grammar" class="accordion-toggle" data-toggle="collapse"
         data-parent="#accordion-parent">Grammar</a>
    </div>
    <div id="grammar" class="accordion-body collapse">
      <div class="accordion-inner">
        {% for category, feature_set in grammatical_features %}
          <h4>{{ category }}</h4>
          <div id="grammar-{{ category|lower|replace({' ': '-', ', ': '-'}) }}"></div>
        {% else %}
          <p>No grammatical feature data.</p>
        {% endfor %}
      </div>
    </div>
  </div>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a href="#vocabulary" class="accordion-toggle" data-toggle="collapse"
         data-parent="#accordion-parent">Vocabulary</a>
    </div>
    <div id="vocabulary" class="accordion-body collapse">
      <div id="vocabulary-features" class="accordion-inner">
        <p>No vocabulary feature data.</p>
      </div>
    </div>
  </div>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a href="#syntax" class="accordion-toggle" data-toggle="collapse"
         data-parent="#accordion-parent">Syntax</a>
    </div>
    <div id="syntax" class="accordion-body collapse">
      <div id="syntactical-features" class="accordion-inner">
        <p>No syntactical feature data.</p>
      </div>
    </div>
  </div>
</div>
{% endblock %}

