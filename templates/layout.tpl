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
        <h1>{{ block('title') }}</h1>
      </header>
      <div id="body" class="container">
        {% block content %}{% endblock %}
      </div>
      <footer class="container">
        <p>&copy; 2012 The University of Texas at Austin</p>
      </footer>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    {% block scripts %}{% endblock %}
  </body>
</html>
