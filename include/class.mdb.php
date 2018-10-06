<?php

class mdb {

    public $address;
    public $user;
    public $pass;
    public $db;
    public $port;
    public $socket;

    public $debug_print = 0;
    public $debug_log = 0;

    public $keep_connected = 1;
    public $result;

    private $dbobj;
    private $is_connected = 0;
    private $transaction_open = 0;
    private $qresult;
    private $stmt;

    /**
     * Construct DB object.
     * 
     * @param string $address Address of server
     * @param string $user Username
     * @param string $pass Password
     * @param string $db Default database
     * @param int $port Port number
     * @param string|boolean $socket Optional socket path
     */
    function __construct($address, $user, $pass, $db ,$port = 3306, $socket = false) {

        $this->address = $address;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
        $this->port = $port;
        $this->socket = $socket;

    }

    /**
     * Connect to the DB server.
     * 
     * @return boolean
     */
    public function connect() {
        if ($this->is_connected == 0) {
            if ($this->dbobj = new mysqli($this->address, $this->user, $this->pass, $this->db, $this->port, $this->socket)) {
                $this->is_connected = 1;
                $this->transaction_open = 0;
                //TODO: Else, throw error
            } else {
                return false;
            }
        }
        return true; 
    }

    /**
     * Disconnect from the database server.
     * 
     * @return boolean
     */
    public function disconnect() {
        if ($this->is_connected == 1) {
            $this->dbobj->close();
            $this->is_connected = 0;
            $this->transaction_open = 0;
        }
        return true;
    }

    /**
     * Disconnect from the DB server if set to not remain connected or a transaction is not open
     */
    private function disconnect_if_allowed() {
        if ($this->keep_connected == 0 and $this->transaction_open == 0) {
            $this->disconnect(); 
        }
    }

    /**
     * Execute a SQL query and store result in $this->result.
     * 
     * @param string $query SQL query
     * @return boolean
     */
    public function query($query) {
        $this->connect();
        $this->debug_print("QUERY = ".$query);
        if ($this->qresult = $this->dbobj->query($query)) {
            $this->result = $this->query_fetch();
            $this->disconnect_if_allowed();
            return true;
        } else {
            $this->disconnect_if_allowed();
            return false; //mysqli_error()
        }
    }
    
    /**
     * Fetch query results.
     * 
     * @return array|boolean
     */
    private function query_fetch() {
        $output = array();
        if (isset($this->qresult->num_rows) && $this->qresult->num_rows > 0) {
            $fields = $this->qresult->fetch_fields();
            $field_names = array();
            foreach ($fields as $field){
                $field_names[] = $field->name;
            }
            while ($row = $this->qresult->fetch_row()) {
                $table_row = array_combine($field_names, $row);
                $output[] = $table_row;
            }
            return $output;
        } else {
            return false;
        }
    }

    /**
     * Execute a prepared SQL query and store result in $this->result.
     * 
     * @param string $query SQL query
     * @param array $types Array of data types for prepared parameters
     * @param array $data Array of data for prepared parameters
     * @return boolean
     */
    public function prepared_query($query, $types, $data) {
        $this->connect();
        $this->debug_print("PREPARED_QUERY = ".$query." | TYPES = ".print_r($types,1)." | DATA = ".print_r($data,1));
        if (!$this->stmt = $this->dbobj->prepare($query)) {
            $this->disconnect_if_allowed();
            return false;
        }

        //bind_param
        $bind_params = array();
        $param_type = '';
        $n = count($types);
        for ($i = 0; $i < $n; $i++) {
            $param_type .= $types[$i];
        }
        $bind_params[] = & $param_type;
        for ($i = 0; $i < $n; $i++) {
            $bind_params[] = & $data[$i];
        }
        if (!call_user_func_array(array($this->stmt, 'bind_param'), $bind_params)) {
            $this->disconnect_if_allowed();
            return false;
        }

        if (!$this->stmt->execute()) {
            $this->disconnect_if_allowed();
            return false;
        }

        $this->result = $this->prepared_query_fetch();
        $this->disconnect_if_allowed();
        return true;
    }

    /**
     * Fetch prepared query results.
     * 
     * @return array|boolean
     */
    private function prepared_query_fetch() {
        if ($meta = $this->stmt->result_metadata()) {
            $names = array();
            while ($field = $meta->fetch_field()) {
                $name = $field->name;
                //$$name = null;
                $names[$name] = null;
                //$result[$field->name] = &$$name;
                $result[$field->name] = &$names[$name];
            }
            call_user_func_array(array($this->stmt, 'bind_result'), $result);
            $output = array();
            while ($this->stmt->fetch()) {
                foreach ($result as $key=>$value) {
                    $result_temp[$key] = $value;
                }
                $output[] = $result_temp;
            }
            return $output;
        } else {
            return false;
        }
    }

    /**
     * Start a transaction.
     */
    public function start_transaction() {
        $this->connect();
        $this->dbobj->begin_transaction();
        $this->transaction_open = 1;
    }

    /**
     * Commit a transaction.
     */
    public function commit() {
        $this->dbobj->commit();
        $this->transaction_open = 0;
        $this->disconnect_if_allowed();
    }

    /**
     * Rollback a transaction.
     */
    public function rollback() {
        $this->dbobj->rollback();
        $this->transaction_open = 0;
        $this->disconnect_if_allowed();
    }

    /**
     * Escape a string on the server.
     * 
     * @param string $value String to escape
     * @return string
     */
    public function escape($value) {
        return $this->dbobj->real_escape_string($value);
    }

    /**
     * Returns last error.
     * 
     * @return string
     */
    public function last_error() {
        return $this->dbobj->error;
    }

    /**
     * Output debugging information.
     * 
     * @param string $data Data to output
     */
    private function debug_print($data) {
        if ($this->debug_print > 0) {
            if (php_sapi_name() === 'cli') {
                echo "DEBUG: [ ".$data." ]\n";
            } else {
                echo "<pre>DEBUG: [ ".$data." ]</pre>";
            }
        }
        if ($this->debug_log > 0) {
            error_log("DEBUG: [ ".$data." ]\n");
        }
    }

    /**
     * Object destructor.
     */
    function __destruct() {
        $this->disconnect();
    }

}
