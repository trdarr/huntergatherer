<!html>
  <head>
    <meta charset="utf-8" />
    <title>{{ block('title') }}</title>
    <link rel="stylesheet" href="/huntergatherer/www/css/bootstrap+font-awesome.min.css" />
    {% block styles %}{% endblock %}
  </head>
  <body>
    <div class="container">
      <header class="container">
        <h1><a href="/huntergatherer" style="color: inherit">Hunter-Gatherer Language Database</a></h1>
      </header>
      <div id="body" class="container">
        {% block subtitle %}{% endblock %}
        {% block flash %}{% endblock %}
        {% block content %}{% endblock %}
      </div>
      <footer class="container">
        <small class="muted">&copy; 2012 The University of Texas at Austin</small>
      </footer>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    {% block scripts %}{% endblock %}
  </body>
</html>
