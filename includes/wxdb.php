<?php
/**
 * Weixin DB Class
 *
 * Original code from {@link http://php.justinvincent.com Justin Vincent (justin@visunet.ie)}
 *
 * @see https://github.com/renfeisong/buaasoft-wechat/wiki/WXDB-Class-Reference
 *
 * @author Renfei Song
 * @since 2.0.0
 */

/**
 * Class wxdb
 *
 * Weixin Database Access Abstraction Object
 */
class wxdb {
    var $num_queries = 0;
    var $num_rows = 0;
    var $rows_affected = 0;
    var $insert_id = 0;
    var $last_query;
    var $last_result;

    public $last_error = '';
    public $charset;
    public $debug = false;

    protected $result;
    protected $reconnect_retries = 5;
    protected $dbuser;
    protected $dbpassword;
    protected $dbname;
    protected $dbhost;
    protected $dbh;

    public function __construct($dbuser, $dbpassword, $dbname, $dbhost) {
        register_shutdown_function(array($this, '__destruct'));

        $this->init_charset();

        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;

        $this->db_connect();
    }

    public function __destruct() {
        return true;
    }

    public function init_charset() {
        $this->charset = 'utf8';

        if (defined('DB_CHARSET'))
            $this->charset = DB_CHARSET;
    }

    public function set_charset($dbh, $charset = null) {
        if (!isset($charset))
            $charset = $this->charset;

        mysqli_set_charset($dbh, $charset);
    }

    public function select($db, $dbh = null) {
        if (is_null($dbh))
            $dbh = $this->dbh;
        $success = @mysqli_select_db($dbh, $db);
        if (!$success) {
            $this->print_error('Cannot select database `' . $db . '`');
        }
    }

    public function db_connect() {
        $this->dbh = mysqli_init();

        $port = null;
        $socket = null;
        $host = $this->dbhost;
        $port_or_socket = strstr($host, ':');
        if (!empty($port_or_socket)) {
            $host = substr($host, 0, strpos($host, ':'));
            $port_or_socket = substr($port_or_socket, 1);
            if (0 !== strpos($port_or_socket, '/')) {
                $port = intval($port_or_socket);
                $maybe_socket = strstr($port_or_socket, ':');
                if (!empty($maybe_socket)) {
                    $socket = substr($maybe_socket, 1);
                }
            } else {
                $socket = $port_or_socket;
            }
        }

        mysqli_real_connect($this->dbh, $host, $this->dbuser, $this->dbpassword, null, $port, $socket);

        if ($this->dbh->connect_errno) {
            $this->dbh = null;
            $this->print_error('Cannot connect to database.');
        } else if ($this->dbh) {
            $this->set_charset($this->dbh);
            $this->select($this->dbname, $this->dbh);
            return true;
        }

        return false;
    }

    public function flush() {
        $this->last_result = array();
        $this->last_query = null;
        $this->rows_affected = 0;
        $this->num_rows = 0;
        $this->last_error = '';
        $this->insert_id = 0;

        if (is_resource($this->result)) {
            mysqli_free_result($this->result);
        }
    }

    private function _do_query($query) {
        $this->result = @mysqli_query($this->dbh, $query);
        $this->num_queries++;
    }

    public function check_connection() {
        if (@mysqli_ping($this->dbh)) {
            return true;
        }
        for ($tries = 1; $tries <= $this->reconnect_retries; $tries++) {
            if ($this->db_connect()) {
                return true;
            }
            sleep(1);
        }
        return false;
    }

    function _real_escape($string) {
        if ($this->dbh) {
            return mysqli_real_escape_string($this->dbh, $string);
        }
    }

    public function query($query) {
        $this->flush();
        $this->last_query = $query;
        $this->_do_query($query);
        $mysql_errno = 0;

        // Log errno
        if (!empty($this->dbh)) {
            $mysql_errno = mysqli_errno($this->dbh);
        }

        // If it's a connection error, try re-connect.
        if (empty($this->dbh) || 2006 == $mysql_errno) {
            if ($this->check_connection()) {
                $this->_do_query($query);
            } else {
                $this->print_error('Failed to execute query: ' . $query);
                return false;
            }
        }

        // Log errno.
        $this->last_error = mysqli_errno($this->dbh);

        // If execution failed, return FALSE.
        if ($this->last_error != 0) {
            $this->print_error('Failed to execute query: ' . $query);
            return false;
        }

        if (preg_match('/^\s*(create|alter|truncate|drop)\s/i', $query)) {
            $return_val = $this->result;
        } elseif (preg_match('/^\s*(insert|delete|update|replace)\s/i', $query)) {
            $this->rows_affected = mysqli_affected_rows($this->dbh);
            if (preg_match('/^\s*(insert|replace)\s/i', $query)) {
                $this->insert_id = mysqli_insert_id($this->dbh);
            }
            $return_val = $this->rows_affected;
        } else {
            $num_rows = 0;
            while ($row = @mysqli_fetch_object($this->result)) {
                $this->last_result[$num_rows] = $row;
                $num_rows++;
            }
            $this->num_rows = $num_rows;
            $return_val = $num_rows;
        }

        return $return_val;
    }

    public function insert($table, $data, $format = null) {
        return $this->_insert_replace_helper($table, $data, $format, 'INSERT');
    }

    public function replace($table, $data, $format = null) {
        return $this->_insert_replace_helper($table, $data, $format, 'REPLACE');
    }

    public function escape_by_ref(&$string) {
        if (!is_float($string))
            $string = $this->_real_escape($string);
    }

    public function prepare($query, $args) {
        if (is_null($query))
            return "";

        $args = func_get_args();
        array_shift($args);
        if (isset($args[0]) && is_array($args[0]))
            $args = $args[0];
        $query = str_replace("'%s'", '%s', $query);
        $query = str_replace('"%s"', '%s', $query);
        $query = preg_replace('|(?<!%)%f|' , '%F', $query);
        $query = preg_replace('|(?<!%)%s|', "'%s'", $query);
        array_walk($args, array($this, 'escape_by_ref'));
        return @vsprintf($query, $args);
    }

    function _insert_replace_helper($table, $data, $format = null, $type = 'INSERT') {
        if (!in_array(strtoupper($type), array('REPLACE', 'INSERT')))
            return false;
        $this->insert_id = 0;
        $formats = $format = (array) $format;
        $fields = array_keys($data);
        $formatted_fields = array();
        foreach ($fields as $field) {
            if (!empty($format))
                $form = ($form = array_shift($formats)) ? $form : $format[0];
            else
                $form = '%s';
            $formatted_fields[] = $form;
        }
        $sql = "{$type} INTO `$table` (`" . implode('`,`', $fields) . "`) VALUES (" . implode(",", $formatted_fields) . ")";
        return $this->query($this->prepare($sql, $data));
    }

    public function update($table, $data, $where, $format = null, $where_format = null) {
        if (!is_array($data) || !is_array($where))
            return false;

        $formats = $format = (array)$format;
        $bits = $wheres = array();
        foreach ((array)array_keys($data) as $field) {
            if (!empty($format))
                $form = ($form = array_shift($formats)) ? $form : $format[0];
            else
                $form = '%s';
            $bits[] = "`$field` = {$form}";
        }

        $where_formats = $where_format = (array)$where_format;
        foreach ((array) array_keys($where) as $field) {
            if (!empty($where_format))
                $form = ($form = array_shift($where_formats)) ? $form : $where_format[0];
            else
                $form = '%s';
            $wheres[] = "`$field` = {$form}";
        }

        $sql = "UPDATE `$table` SET " . implode(', ', $bits) . ' WHERE ' . implode(' AND ', $wheres);
        return $this->query($this->prepare($sql, array_merge(array_values($data), array_values($where))));
    }

    public function delete($table, $where, $where_format = null) {
        if (!is_array($where))
            return false;

        $wheres = array();

        $where_formats = $where_format = (array)$where_format;

        foreach (array_keys($where) as $field) {
            if (!empty($where_format)) {
                $form = ($form = array_shift($where_formats)) ? $form : $where_format[0];
            } else {
                $form = '%s';
            }

            $wheres[] = "$field = $form";
        }

        $sql = "DELETE FROM `$table` WHERE " . implode(' AND ', $wheres);

        return $this->query($this->prepare($sql, $where));
    }

    public function get_results($query = null, $output = OBJECT) {
        if ($query)
            $this->query($query);
        else
            return null;

        $new_array = array();
        if ($output == OBJECT) {
            // Return an integer-keyed array of row objects
            return $this->last_result;
        } elseif ($output == OBJECT_K) {
            // Return an array of row objects with keys from column 1
            // (Duplicates are discarded)
            foreach ($this->last_result as $row) {
                $var_by_ref = get_object_vars($row);
                $key = array_shift($var_by_ref);
                if (!isset($new_array[$key]))
                    $new_array[$key] = $row;
            }
            return $new_array;
        } elseif ($output == ARRAY_A || $output == ARRAY_N) {
            // Return an integer-keyed array of...
            if ($this->last_result) {
                foreach((array)$this->last_result as $row) {
                    if ($output == ARRAY_N) {
                        // ...integer-keyed row arrays
                        $new_array[] = array_values(get_object_vars($row));
                    } else {
                        // ...column name-keyed row arrays
                        $new_array[] = get_object_vars($row);
                    }
                }
            }
            return $new_array;
        } else {
            $this->print_error("\$db->get_results(string query, output type) -- Output type must be one of: OBJECT, OBJECT_K, ARRAY_A, ARRAY_N");
            return null;
        }
    }

    public function get_var($query = null, $x = 0, $y = 0) {
        if ($query)
            $this->query( $query );

        // Extract var out of cached results based x,y vals
        if (!empty($this->last_result[$y])) {
            $values = array_values(get_object_vars($this->last_result[$y]));
        }

        // If there is a value return it else return null
        return (isset($values[$x]) && $values[$x] !== '') ? $values[$x] : null;
    }

    public function get_row($query = null, $output = OBJECT, $y = 0) {
        if ($query)
            $this->query($query);
        else
            return null;

        if (!isset($this->last_result[$y]))
            return null;

        if ($output == OBJECT) {
            return $this->last_result[$y] ? $this->last_result[$y] : null;
        } elseif ($output == ARRAY_A) {
            return $this->last_result[$y] ? get_object_vars($this->last_result[$y]) : null;
        } elseif ($output == ARRAY_N) {
            return $this->last_result[$y] ? array_values(get_object_vars($this->last_result[$y])) : null;
        } else {
            $this->print_error("\$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N");
            return null;
        }
    }

    public function get_col($query = null , $x = 0) {
        if ($query)
            $this->query( $query );

        $new_array = array();
        // Extract the column values
        for ($i = 0, $j = count($this->last_result); $i < $j; $i++) {
            $new_array[$i] = $this->get_var(null, $x, $i);
        }
        return $new_array;
    }

    public function print_error($error) {
        if ($this->debug) {
            echo $error . "\n";
        }
    }
}