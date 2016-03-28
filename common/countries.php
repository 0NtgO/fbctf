<?hh

require_once('db.php');

class Countries {
  private $db;

  public function __construct() {
    $this->db = DB::getInstance();
    if (!$this->db->isConnected()) {
      $this->db->connect();
    }
  }

  // Make sure all the countries used field is good.
  public function used_adjust() {
    $sql1 = 'UPDATE countries SET used = 1 WHERE id IN (SELECT entity_id FROM levels)';
    $sql2 = 'UPDATE countries SET used = 0 WHERE id NOT IN (SELECT entity_id FROM levels)';
    $this->db->query($sql1);
    $this->db->query($sql2);
  }

  // Retrieve how many levels are using one country.
  public function who_uses($country_id) {
    $sql = 'SELECT * FROM levels WHERE entity_id = ? AND active = 1 LIMIT 1';
    $element = array($country_id);
    $who_uses = $this->db->query($sql, $element);
    if ($who_uses) {
      return $who_uses[0];
    }
    return $who_uses;
  }

  // Enable or disable country by passing 1 or 0.
  public function toggle_status($country_id, $status) {
    $sql = 'UPDATE countries SET enabled = ? WHERE id = ? LIMIT 1';
    $elements = array($status, $country_id);
    $this->db->query($sql, $elements);
  }

  // Check if country is enabled.
  public function is_enabled($country_id) {
    $sql = 'SELECT enabled FROM countries WHERE id = ?';
    $element = array($country_id);
    return (bool)($this->db->query($sql, $element) == 1);
  }

  // Mark a country as used by passing 1 or 0.
  public function toggle_used($country_id, $status) {
    $sql = 'UPDATE countries SET used = ? WHERE id = ? LIMIT 1';
    $elements = array($status, $country_id);
    $this->db->query($sql, $elements);
  }

  // Check if country is used.
  public function is_used($country_id) {
    $sql = 'SELECT used FROM countries WHERE id = ?';
    $element = array($country_id);
    return (bool)($this->db->query($sql, $element) == 1);
  }

  // All the countries.
  public function all_countries() {
    $sql = 'SELECT * FROM countries ORDER BY name';
    return $this->db->query($sql);
  }

  // All enabled countries. The weird sorting is because SVG lack of z-index
  // and things looking like shit in the map. See issue #20.
  public function all_enabled_countries($map=false) {
    if ($map) {
      $sql = 'SELECT * FROM countries WHERE enabled = 1 ORDER BY CHAR_LENGTH(d)';
    } else {
      $sql = 'SELECT * FROM countries WHERE enabled = 1 ORDER BY name';
    }
    return $this->db->query($sql);
  }

  // All map countries.
  public function all_map_countries() {
    $sql = 'SELECT * FROM countries ORDER BY CHAR_LENGTH(d)';
    return $this->db->query($sql);
  }

  // All not used and enabled countries.
  public function all_available_countries() {
    $sql = 'SELECT * FROM countries WHERE enabled = 1 AND used = 0 ORDER BY name';
    return $this->db->query($sql);
  }

  // Check if country is in an active level.
  public function is_active_level($country_id) {
    $sql = 'SELECT COUNT(*) FROM levels WHERE entity_id = ? AND active = 1 LIMIT 1';
    $element = array($country_id);
    $is_active = $this->db->query($sql, $element);
    return (bool)$is_active[0]['COUNT(*)'];
  }

  // Retrieve country by id.
  public function get_country($country_id) {
    $sql = 'SELECT * FROM countries WHERE id = ? LIMIT 1';
    $element = array($country_id);
    $country = $this->db->query($sql, $element);
    if ($country) {
      return $country[0];  
    } else {
      return false;
    }
    
  }

  // Retrieve a random country.
  public function random_country() {
    $sql = 'SELECT id FROM countries WHERE enabled = 1 AND used = 0 ORDER BY RAND() LIMIT 1';
    return $this->db->query($sql)[0]['id'];
  }
}
