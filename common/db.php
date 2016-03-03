<?hh // strict

require_once('utils.php');

class DB {
  private string $settings_file = 'settings.ini';
  private ?array<string, string> $config = null;
  private static ?DB $instance = null;
  private ?PDO $dbh = null;
  public bool $connected = false;

  public static function getInstance(): DB {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct() {
    $this->config = parse_ini_file($this->settings_file);
  }

  private function __clone(): void { }

  public function connect(): void {
    invariant($this->config !== null, "Config can not be null");
    try {
      // TODO: Use must_have_idx
      $host = idx($this->config, 'DB_HOST');
      $port = idx($this->config, 'DB_PORT');
      $db_name = idx($this->config, 'DB_NAME');
      $conn_str = sprintf(
        'mysql:host=%s;port=%s;dbname=%s',
        $host,
        $port,
        $db_name,
      );

      $username = idx($this->config, 'DB_USERNAME');
      $password = idx($this->config, 'DB_PASSWORD');
      $this->dbh = new PDO(
        $conn_str,
        $username,
        $password,
      );
      $this->connected = true;

    } catch (PDOException $e) {
      error_log("[ db.php ] - Connection error: ".$e->getMessage());
      error_page();
    }
  }

  public function disconnect(): void {
    $this->dbh = null;
    $this->connected = false;
  }

  public function query(
    string $query,
    ?array<mixed> $elements = null
  ): array<array<string, string>> {
    if (!$this->connected) {
      $this->connect();
    }

    invariant($this->dbh !== null, '$dbh should not be null');
    $stmt = $this->dbh->prepare($query);
    if ($elements !== null) {
      $i = 1;
      foreach ($elements as &$element) {
        $stmt->bindparam($i, $element);
        $i++;
      }
    }

    try {
      $stmt->execute();
    } catch (PDOException $e) {
      error_log("[ db.php ] - Statement error: " . $stmt->errorInfo());
      error_page();
    }

    $results = array();
    while ($row = $stmt->fetch()) {
      $results[] = $row;
    }
    return $results;
  }
}
