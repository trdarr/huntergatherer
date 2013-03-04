<?php

abstract class Importer {
  // The number of database queries by type, for debugging.
  public static $queries = array();

  public $methods;   // An array of the names of this object's public methods.
  protected $cache;  // An array for caching the results of SQL queries.
  protected $pdo;    // The PDO object, required by the constructor.

  public function __construct($pdo) {
    $class = get_called_class();
    $methods = new ReflectionClass($class);
    $methods = array_filter($methods->getMethods(ReflectionMethod::IS_PUBLIC),
      function ($e) use ($class) { return substr($e->name, 0, 2) !== '__'
        && $e->getDeclaringClass()->name === $class; });
    $methods = array_map(function ($e) { return $e->name; }, $methods);

    $this->cache = array_fill_keys($methods, array());
    $this->methods = $methods;
    $this->pdo = $pdo;
  }

  // Generate cache functions to save some typing.
  // TODO, maybe: move this into an object if you don't like functional PHP.
  // TODO, maybe: if $cache_key is null, make functions require keys.
  protected function cache_functions($function, $cache_key) {
    // Dynamically select which cache to pull from.
    $cache = &$this->cache[$function];

    // Generate functions for working with that specific cache.
    // All functions handle an arbitrary key or the provided cache_key.
    return array(
      // cache_get: retrieve the value for a key.
      function ($key=null) use (&$cache, $cache_key) {
        return $cache[$key !== null ? $key : $cache_key];
      },

      // cache_set: cache a key/value pair. $value defaults to true.
      function ($value=true, $key=null) use (&$cache, $cache_key) {
        $cache[$key !== null ? $key : $cache_key] = $value;
      },

      // is_cached: does this key exist in the cache?
      function ($key=null) use (&$cache, $cache_key) {
        return isset($cache[$key !== null ? $key : $cache_key]);
      },
    );
  }

  // Query the database given some SQL and the parameters.
  // Returns a PDOStatement::fetchAll result set, if applicable.
  protected function query($sql, $params=null) {
    $type = explode(' ', $sql); $type = $type[0];
    if (isset(self::$queries[$type]))
      self::$queries[$type] += 1;
    else self::$queries[$type] = 1;

    try {
      $statement = $this->pdo->prepare($sql);
      $success = $params !== null
        ? $statement->execute($params)
        : $statement->execute();
    } catch (PDOException $e) {
      list($_, $caller) = $e->getTrace();
      extract($caller);  // Who actually threw the exception?

      die(sprintf(nl2br("<b>%s</b> from <b>%s:%d</b>\n%s\n
        <b>SQL:</b> %s\n<b>Params:</b>")."<pre>%s</pre>\n",
        get_class($e), basename($file), $line,
        $e->getMessage(), $sql, print_r($params, true)));
    }

    // PDOStatement::fetchAll throws an exception for some statements.
    if (!in_array($type, array('DELETE', 'INSERT', 'UPDATE')))
      return $statement->fetchAll(PDO::FETCH_ASSOC);
  }
}

