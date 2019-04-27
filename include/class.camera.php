<?php

class camera {

    private $id = false;
    private $db;
    private $config;

    public $device_data;

    /**
     * Returns an array of valid camera properties
     * 
     * @return array
     */
    public static function get_valid_properties() {
        return array(
            'id',
            'name',
            'ip_address',
            'protocol',
            'url',
            'username',
            'password'
        );
    }

    /**
     * Return an array of all cameras
     * 
     * @param object $db Database object
     * @return boolean|array
     */
    public static function get_all(&$db) {
        if (!$db->query('SELECT * FROM `devices`')
                || !isset($db->result[0])
                ) {
            return false;
        }
        
        return $db->result;
    }
    
    /**
     * Create a new camera
     * 
     * @param object $db Database object
     * @param array $data Array of new camera data
     * @param bool $inst Set true to return an object for the new camera
     * @param array $config Configuration data array for new camera if returning an object
     * @return \camera|boolean
     */
    public static function create_camera(&$db, $data, $inst = false, $config = array()) {
        $fields = self::get_valid_properties();
        foreach ($fields as $f) {
            if ('id' === $f) { continue; }
            if (!array_key_exists($f, $data)) {
                return false;
            }
        }
        
        if (!$db->prepared_query(
'INSERT INTO
    `devices` (`name`, `ip_address`, `protocol`, `url`, `username`, `password`)
VALUES (?, ? ,?, ?, ?, ?)',
                array('s', 's', 's', 's', 's', 's'),
                array(
                    $data['name'],
                    $data['ip_address'],
                    $data['protocol'],
                    $data['url'],
                    $data['username'],
                    $data['password']
                    )
                )
           ) {
            return false;
        }
        
        if ($inst) {
            if (!$db->query('SELECT LAST_INSERT_ID()')) { return false; }
            if (isset($db->result[0]['LAST_INSERT_ID()'])) {
                return new camera($db, $config, $db->result[0]['LAST_INSERT_ID()']);
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Delete a camera
     * 
     * @param object $db Database object
     * @param array $config Camera configuration array
     * @param int $id Camera ID
     * @param string $name Camera name
     * @return boolean
     */
    public static function delete_camera(&$db, $config, $id = false, $name = false) {
        $device = new camera($db, $config, $id, $name);
        if (!$device->device_data) { return false; }
        if ($device->is_active()) { return false; }
        
        if (!$db->query(
'DELETE FROM
    `devices`
WHERE
    `id`='.$device->device_data['id']
        )) { return false; }
        
        if (!$db->query(
'DELETE FROM
    `camera_log`
WHERE
    `device`='.$device->device_data['id']
        )) { return false; }
        
        if (!$db->query(
'DELETE FROM
    `config`
WHERE
    `device`='.$device->device_data['id']
        )) { return false; }
        
        return true;
    }

    /**
     * Constructor
     * 
     * @param object $db Database object
     * @param array $config Camera configuration array
     * @param int $id Camera ID
     * @param string $name Camera name
     */
    function __construct(&$db, $config, $id = false, $name = false) {
        $this->db = $db;
        $this->config = $config;
        
        if ($id !== false) {
            $this->db->prepared_query(
'SELECT
    `id`
FROM
    `devices`
WHERE
    `id`=?',
                array('i'),
                array($id));
        } elseif ($name !== false) {
            $this->db->prepared_query(
'SELECT
    `id`
FROM
    `devices`
WHERE
    `name`=?',
                array('s'),
                array($name));
        }
        
        if (isset($this->db->result[0])) {
            $this->id = $this->db->result[0]['id'];
        }

        $this->refresh();
    }

    /**
     * Reload camera data
     */
    public function refresh() {
        if ($this->id !== false) {
            $this->db->prepared_query(
'SELECT
    `id`,
    `name`,
    `ip_address`,
    `protocol`,
    `url`,
    `username`,
    `password`
FROM
    `devices`
WHERE
    `id`=?',
                array('i'),
                array($this->id));
            
            if (isset($this->db->result[0])) {
                $this->device_data = $this->db->result[0];
            } else {
                $this->device_data = false;
            }
        } else {
            $this->device_data = false;
        }
    }

    /**
     * Update camera data
     * 
     * @return boolean
     */
    public function update() {
        if ($this->id !== false) {
            if ($this->db->prepared_query(
'UPDATE
    `devices`
SET
    `id`=?,
    `name`=?,
    `ip_address`=?,
    `protocol`=?,
    `url`=?,
    `username`=?,
    `password`=?
WHERE
    `id`=?',
                    array('i','s','s','s','s','s','s','i'),
                    array($this->device_data['id'],
                          $this->device_data['name'],
                          $this->device_data['ip_address'],
                          $this->device_data['protocol'],
                          $this->device_data['url'],
                          $this->device_data['username'],
                          $this->device_data['password'],
                          $this->id))) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Check if camera has a PID file
     * 
     * @return boolean
     */
    public function has_pid() {
        if (isset($this->device_data['name'])
                && file_exists($this->config['pid_directory'].'/'.$this->device_data['name'].'.pid')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if camera has a sleep PID (is sleeping)
     * 
     * @return boolean
     */
    public function has_sleep_pid() {
        if (isset($this->device_data['name'])
                && file_exists($this->config['pid_directory'].'/'.$this->device_data['name'].'.sleep')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return the camera PID
     * 
     * @return int|boolean
     */
    public function get_pid() {
        if (isset($this->device_data['name'])
                && $pidfile = @fopen($this->config['pid_directory'].'/'.$this->device_data['name'].'.pid','r')) {
            $pid = fgets($pidfile);
            fclose($pidfile);
            return $pid;
        } else {
            return false;
        }
    }

    /**
     * Return the camera sleep PID
     * 
     * @return int|boolean
     */
    public function get_sleep_pid() {
        if (isset($this->device_data['name'])
                && $pidfile = @fopen($this->config['pid_directory'].'/'.$this->device_data['name'].'.sleep','r')) {
            $pid = fgets($pidfile);
            fclose($pidfile);
            return $pid;
        } else {
            return false;
        }
    }

    /**
     * Return true if the camera is running (not sleeping)
     * 
     * @return boolean
     */
    public function is_running() {
        $pid = $this->get_pid();
        if ($pid === false) {
            return false;
        }
        exec('ps -p '.$pid, $output);
        if (count($output) > 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return true if the camera is active (running or sleeping)
     * 
     * @return boolean
     */
    public function is_active() {
        if ($this->has_pid() || $this->has_sleep_pid()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the media URL for the camera
     * 
     * @return string
     */
    public function full_url() {
        if ($this->device_data['username'] != ''
                || $this->device_data['password'] != '') {
            $auth = $this->device_data['username'].':'.$this->device_data['password'].'@';
        } else {
            $auth = '';
        }
        return $this->device_data['protocol']
                .'://'.$auth.$this->device_data['ip_address']
                .$this->device_data['url'];
    }

    /**
     * Log event for camera
     * 
     * @param string $event Event string
     * @param int $status Event status ID
     */
    public function log_add($event, $status) {
        if (!$this->db->prepared_query(
'INSERT INTO
    `camera_log` (`device`, `event`, `status`)
VALUES (?, ?, ?)',
                array('i', 's', 'i'),
                array($this->id, $event, $status))) {
            echo 'ERROR: Failed to insert into camera_log. ('
                .$this->id.', '.$event.', '.$status.")\nMYSQL_ERROR: "
                .$this->db->last_error()."\n";
        }
        
        $this->db->query('SELECT COUNT(*) AS `count` FROM `camera_log`');
        $row = $this->db->result[0]['count'];
        $logdelete = $row - $this->config['camera_log_limit'];
        
        if ($logdelete > 0) {
            $this->db->query(
'DELETE FROM
    `camera_log`
ORDER BY
    `timestamp` ASC
LIMIT '.$logdelete);
        }
    }

    /**
     * Get camera log entries
     * 
     * @param string $after Date after
     * @param string $before Date before
     * @param int $status Event status ID
     * @param int $limit Maximum number of entries to get
     * @return array
     */
    public function log_get(
            $after = false,
            $before = false,
            $status = false,
            $limit = false
    ) { 
        $where = '';
        $wheretype = array();
        $wheredata = array();
        if ($after !== false) {
            $where .= ' AND `timestamp` >= ?';
            $wheretype[] = 's';
            $wheredata[] = $after;
        }
        if ($before !== false) {
            $where .= ' AND `timestamp` <= ?';
            $wheretype[] = 's';
            $wheredata[] = $before;
        }
        if ($status !== false) {
            $where .= ' AND `status` = ?';
            $wheretype[] = 'i';
            $wheredata[] = $status;
        }

        if ($limit !== false && preg_match('/^[0-9]+$/', $limit)) {
            $limit = ' LIMIT '.$limit;
        } else {
            $limit = '';
        }

        if (count($wheretype) > 0) {
            $this->db->prepared_query(
'SELECT
    `id`,
    `timestamp`,
    `event`,
    `status`
FROM
    `camera_log`
WHERE
    `device`='.$this->id
.$where.'
ORDER BY
    `timestamp` DESC'
.$limit,
                $wheretype,
                $wheredata);
        } else {
            $this->db->query(
'SELECT
    `id`,
    `timestamp`,
    `event`,
    `status`
FROM
    `camera_log`
WHERE
    `device`='.$this->id.'
ORDER BY
    `timestamp` DESC'
.$limit);
        }
        return $this->db->result;
    }

}
