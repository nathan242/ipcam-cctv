<?php

class camera {

    private $id = false;
    private $database;
    private $config;

    public $device_data;

    //public static $valid_properties = array('id', 'name', 'ip_address', 'protocol', 'url', 'username', 'password');
    public static function get_valid_properties() { return  array('id', 'name', 'ip_address', 'protocol', 'url', 'username', 'password'); }

    public static function create_camera(&$database, $data, $inst = false, $config = array()) {
        $fields = self::get_valid_properties();
        foreach ($fields as $f) {
            if ($f != 'id' && !array_key_exists($f, $data)) {
                return false;
            }
        }
        if (!$database->prepared_query("insert into `devices` (`name`, `ip_address`, `protocol`, `url`, `username`, `password`) values (?, ? ,?, ?, ?, ?)", array('s', 's', 's', 's', 's', 's'), array($data['name'], $data['ip_address'], $data['protocol'], $data['url'], $data['username'], $data['password']))) { return false; }
        if ($inst) {
            if (!$database->query("select last_insert_id()")) { return false; }
            if (isset($database->result[0]['last_insert_id()'])) {
                return new camera($database, $config, $database->result[0]['last_insert_id()']);
            } else {
                return false;
            }
        }
        return true;
    }

    public static function delete_camera(&$database, $config, $id = false, $name = false) {
        $device = new camera($database, $config, $id, $name);
        if (!$device->device_data) { return false; }
        if ($device->is_active()) { return false; }
        if (!$database->query("delete from `devices` where id=".$device->device_data['id'])) { return false; }
        if (!$database->query("delete from `camera_log` where device=".$device->device_data['id'])) { return false; }
        if (!$database->query("delete from `config` where device=".$device->device_data['id'])) { return false; }
        return true;
    }

    function __construct(&$database, $config, $id = false, $name = false) {
        $this->database = $database;
        $this->config = $config;
        if ($id !== false) {
            $this->database->prepared_query("select `id` from `devices` where `id`=?",array("i"), array($id));
        } elseif ($name !== false) {
            $this->database->prepared_query("select `id` from `devices` where `name`=?",array("s"), array($name));
        }
        if (isset($this->database->result[0])) {
            $this->id = $this->database->result[0]['id'];
        }

        $this->refresh();
    }

    public function refresh() {
        if ($this->id !== false) {
            $this->database->prepared_query("select `id`, `name`, `ip_address`, `protocol`, `url`, `username`, `password` from `devices` where `id`=?", array("i"), array($this->id));
            if (isset($this->database->result[0])) {
                $this->device_data = $this->database->result[0];
            } else {
                $this->device_data = false;
            }
        } else {
            $this->device_data = false;
        }
    }

    public function update() {
        if ($this->id !== false) {
            if ($this->database->prepared_query("update `devices` set `id`=?, `name`=?, `ip_address`=?, `protocol`=?, `url`=?, `username`=?, `password`=? where `id`=?", array('i','s','s','s','s','s','s','i'), array($this->device_data['id'], $this->device_data['name'], $this->device_data['ip_address'], $this->device_data['protocol'], $this->device_data['url'], $this->device_data['username'], $this->device_data['password'], $this->id))) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function has_pid() {
        if (isset($this->device_data['name']) && file_exists($this->config['pid_directory'].'/'.$this->device_data['name'].".pid")) {
            return true;
        } else {
            return false;
        }
    }

    public function has_sleep_pid() {
        if (isset($this->device_data['name']) && file_exists($this->config['pid_directory'].'/'.$this->device_data['name'].".sleep")) {
            return true;
        } else {
            return false;
        }
    }

    public function get_pid() {
        if (isset($this->device_data['name']) && $pidfile = @fopen($this->config['pid_directory'].'/'.$this->device_data['name'].".pid","r")) {
            $pid = fgets($pidfile);
            fclose($pidfile);
            return $pid;
        } else {
            return false;
        }
    }

    public function get_sleep_pid() {
        if (isset($this->device_data['name']) && $pidfile = @fopen($this->config['pid_directory'].'/'.$this->device_data['name'].".sleep","r")) {
            $pid = fgets($pidfile);
            fclose($pidfile);
            return $pid;
        } else {
            return false;
        }
    }

    public function is_running() {
        $pid = $this->get_pid();
        if ($pid === false) {
            return false;
        }
        exec('ps -p '.$pid,$output);
        if (count($output) > 1) {
            return true;
        } else {
            return false;
        }
    }

    public function is_active() {
        if ($this->has_pid() || $this->has_sleep_pid()) {
            return true;
        } else {
            return false;
        }
    }

    public function full_url() {
        if ($this->device_data['username'] != "" || $this->device_data['password'] != "") {
            $auth = $this->device_data['username'].":".$this->device_data['password']."@";
        } else {
            $auth = "";
        }
        return $this->device_data['protocol'].'://'.$auth.$this->device_data['ip_address'].$this->device_data['url'];
    }

    public function log_add($event, $status) {
        if (!$this->database->prepared_query('insert into camera_log (`device`, `event`, `status`) values (?, ?, ?)', array('i', 's', 'i'), array($this->id, $event, $status))) {
            echo "ERROR: Failed to insert into camera_log. (".$this->id.", ".$event.", ".$status.")\nMYSQL_ERROR: ".$this->database->last_error()."\n";
        }
        $this->database->query('select count(*) as `count` from camera_log');
        $row = $this->database->result[0]['count'];
        $logdelete = $row - $this->config['camera_log_limit'];
        if ($logdelete > 0) {
            $this->database->query('delete from camera_log order by timestamp asc limit '.$logdelete);
        }
    }

    public function log_get($after = false, $before = false, $status = false, $limit = false) {
        $where = "";
        $wheretype = array();
        $wheredata = array();
        if ($after !== false) {
            $where .= " and `timestamp` >= ?";
            $wheretype[] = "s";
            $wheredata[] = $after;
        }
        if ($before !== false) {
            $where .= " and `timestamp` <= ?";
            $wheretype[] = "s";
            $wheredata[] = $before;
        }
        if ($status !== false) {
            $where .= " and `status` = ?";
            $wheretype[] = "i";
            $wheredata[] = $status;
        }

        if ($limit !== false && preg_match('/^[0-9]+$/', $limit)) {
            $limit = " limit ".$limit;
        } else {
            $limit = "";
        }

        if (count($wheretype) > 0) {
            $this->database->prepared_query("select `id`, `timestamp`, `event`, `status` from camera_log where device=".$this->id.$where." order by `timestamp` desc".$limit, $wheretype, $wheredata);
        } else {
            $this->database->query("select `id`, `timestamp`, `event`, `status` from camera_log where device=".$this->id." order by `timestamp` desc".$limit);
        }
        return $this->database->result;
    }

}
?>
