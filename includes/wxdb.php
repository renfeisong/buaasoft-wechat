<?php
/**
 * Weixin DB Class
 *
 * Original code from {@link http://php.justinvincent.com Justin Vincent (justin@visunet.ie)}
 *
 * @see https://github.com/renfeisong/buaasoft-wechat/wiki/WXDB-Class-Reference
 *
 * @author Renfei Song
 * @since 1.0.0
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

    protected $result;
    protected $reconnect_retries = 5;
    protected $dbuser;
    protected $dbpassword;
    protected $dbname;
    protected $dbhost;
    protected $dbh;

    public function __construct($dbuser, $dbpassword, $dbname, $dbhost) {
        register_shutdown_function(array($this, '__destruct'));

        $this->init_charset('utf8');

        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;

        $this->db_connect();
    }

    public function __destruct() {
        return true;
    }

    public function init_charset($charset) {
        $this->charset = $charset;
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
            echo 'Cannot select database '.$db;
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
            echo 'Cannot connect to database.';
        } else if ($this->dbh) {
            $this->set_charset($this->dbh);
            $this->select($this->dbname, $this->dbh);
            // echo 'Database connected.';
            return true;
        }

        return false;
    }

    public function flush() {
        $this->last_result = array();
        $this->last_query = null;
        $this->rows_affected = $this->num_rows = 0;
        $this->last_error = '';

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
        if (!empty($this->dbh)) {
            $mysql_errno = mysqli_errno($this->dbh);
        }
        if (empty($this->dbh) || 2006 == $mysql_errno) {
            if ($this->check_connection()) {
                $this->_do_query($query);
            } else {
                $this->insert_id = 0;
                return false;
            }
        }

        $this->last_error = mysqli_errno($this->dbh);

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
        $sql = "{$type} INTO `$table` (`" . implode( '`,`', $fields ) . "`) VALUES (" . implode( ",", $formatted_fields ) . ")";
        return $this->query($this->prepare($sql, $data));
    }

    public function update($table, $data, $where, $format = null, $where_format = null) {
        if (!is_array($data) || !is_array($where))
            return false;

        $formats = $format = (array)$format;
        $bits = $wheres = array();
        foreach ((array)array_keys($data) as $field) {
            if (!empty($format))
                $form = ($form = array_shift( $formats)) ? $form : $format[0];
            else
                $form = '%s';
            $bits[] = "`$field` = {$form}";
        }

        $where_formats = $where_format = (array) $where_format;
        foreach ((array) array_keys($where) as $field) {
            if (!empty($where_format))
                $form = ($form = array_shift($where_formats)) ? $form : $where_format[0];
            else
                $form = '%s';
            $wheres[] = "`$field` = {$form}";
        }

        $sql = "UPDATE `$table` SET " . implode( ', ', $bits ) . ' WHERE ' . implode( ' AND ', $wheres);
        return $this->query($this->prepare($sql, array_merge(array_values( $data ), array_values($where))));
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

        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $wheres);
        return $this->query($this->prepare( $sql, $where));
    }
}