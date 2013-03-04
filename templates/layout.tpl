<!html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>{{ block('title') }}</title>
    <link rel="stylesheet" href="/huntergatherer/www/css/bootstrap+font-awesome.min.css" />
    <link rel="stylesheet" href="/huntergatherer/www/css/huntergatherer.css" />
    {% block styles %}{% endblock %}
  </head>
  <body class="container">
    <header>
      <h1 class="row">
        <a href="/huntergatherer">Hunter-Gatherer Language Database</a>
      </h1>
      <nav class="row">
        <ul class="nav nav-pills">
          <li{% if handler == 'languages' %} class="active"{% endif %}><a href="/huntergatherer/languages">Languages</a></li>
          <li{% if handler == 'grammar' %} class="active"{% endif %}><a href="/huntergatherer/grammar">Grammar</a></li>
          <li class="disabled"><a href="/huntergatherer/vocabulary">Vocabulary</a></li>
          <li class="disabled"><a href="/huntergatherer/syntax">Syntax</a></li>
          <li class="disabled"><a href="/huntergatherer/sources">Citations</a></li>
          <li class="disabled"><a href="/huntergatherer/contributors">Contributors</a></li>
          <li{% if handler == 'about' %} class="active"{% endif %}><a href="/huntergatherer/about">About</a></li>
          <li class="disabled"><a href="/huntergatherer/login">Log in</a></li>
        </ul>
      </nav>
    </header>
    <div id="body" class="row">
      {% block subtitle %}{% endblock %}
      {% block flash %}{% endblock %}
      {% block content %}{% endblock %}
    </div>
    <footer class="row">
      {% block footer %}
        <p>
          <small class="muted">
            This software was developed by employees of
            <a href="http://laits.utexas.edu/">Liberal Arts Instructional Technology Services (LAITS)</a> at the
            <a href="http://utexas.edu/">University of Texas</a> at Austin,
            and is based upon work supported by
            <a href="http://nsf.gov/awardsearch/showAward?AWD_ID=0902114"><b>BCS-0902114</b> "Dynamics of Hunter-Gatherer Language Change"</a>,
            a grant awarded to
            <a href="http://yale.edu/">Yale University</a> by the
            <a href="http://nsf.gov/div/index.jsp?div=bcs">Division of Behavioral and Cognitive Sciences (BCS)</a> of the
            <a href="http://nsf.gov/">National Science Foundation (<b>NSF</b>)</a>.
            Any opinions, findings, and conclusions or recommendations expressed
            in this material are those of the author(s) and do not necessarily
            reflect the views of the NSF. For more information, see
            "<a href="/huntergatherer/about">About</a>" or
            <a href="http://webspace.yale.edu/huntergatherer/index.html">Dynamics of Hunter-Gatherer Language Change</a> at Yale.
          </small>
        </p>
      {% endblock %}
    </footer>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="/huntergatherer/www/js/huntergatherer.js"></script>
    {% block scripts %}{% endblock %}
  </body>
</html>

