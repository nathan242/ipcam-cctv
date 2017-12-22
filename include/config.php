<?php
    class config {

        private $database;

        private $default_keys = array(
            'vlc_exec'=>'cvlc',
            'recording_directory'=>'',
            'pid_directory'=>'',
            'log_directory'=>'',
            'retry_time_limit'=>'100',
            'retry_count'=>'3',
            'sleep_time'=>'10',
            'camera_log_limit'=>'10000',
            'recording_file_extension'=>'.mp4',
            'recording_file_mux'=>'mp4',
            'transcode_enable'=>'0',
            'transcode_video_codec'=>'',
            'transcode_audio_codec'=>'',
            'transcode_fps'=>'',
            'transcode_scale'=>'',
            'transcode_bitrate'=>'',
            'recording_device_limit'=>'',
            'recording_mime'=>'video/mp4'
        );

        public $config_data = array();

        function __construct(&$database, $device = false, $load_global = true) {
            $this->database = $database;

            // Check global settings
            $this->check_keys();

            if ($load_global) {
                // Load global config
                $this->load_config();
            }

            // Load device specific config and override global config values
            if ($device !== false) {
                $this->load_config($device);
            }
        }

        private function load_config($device = false) {
            if ($device !== false) {
                $this->database->prepared_query('SELECT `key`, `value` FROM `config` WHERE `device`=?', array('i'), array($device));
            } else {
                $this->database->query('SELECT `key`, `value` FROM `config` WHERE `device` IS NULL');
            }

            if (isset($this->database->result[0])) {
                foreach ($this->database->result as $cnf) {
                    $this->config_data[$cnf['key']] = $cnf['value'];
                }
                return true;
            }

            return false;
        }

        private function check_keys() {
            $this->database->query('SELECT `key` FROM `config` WHERE `device` IS NULL AND `key` IN ("'.implode('","', array_keys($this->default_keys)).'")');

            $keys = array();
            if (isset($this->database->result[0])) {
                foreach ($this->database->result as $r) {
                    $keys[] = $r['key'];
                }
            }

            foreach ($this->default_keys as $k => $v) {
                if (!in_array($k, $keys)) {
                    $this->database->query('INSERT INTO `config` (`key`, `value`) VALUES ("'.$k.'", "'.$v.'")');
                }
            }
        }

        public static function get_key_instances($database, $key) {
            if (!$database->prepared_query('SELECT GROUP_CONCAT(`device`) AS `devices`, `key`, `value` FROM `config` WHERE `key`=? GROUP BY `key`, `value`', array('s'), array($key))) { return false; }
            if (isset($database->result[0])) {
                return $database->result;
            } else {
                return false;
            }
        }

        public static function config_dump($database) {
            if (!$database->query('SELECT DISTINCT `device` FROM `config`')) { return false; }
            if (!isset($database->result[0])) { return false; }
            $devices = array();
            foreach ($database->result as $r) {
                if ($r['device'] == '') { $r['device'] = 'GLOBAL'; }
                $devices[] = $r['device'];
            }
            $ret = array();
            foreach ($devices as $d) {
                if ($d == 'GLOBAL') {
                    if (!$database->query('SELECT `key`, `value` FROM `config` WHERE `device` IS NULL')) { return false; }
                } else {
                    if (!$database->query('SELECT `key`, `value` FROM `config` WHERE `device`='.(int)$d)) { return false; }
                }
                if (isset($database->result[0])) {
                    $c = array();
                    foreach ($database->result as $r) {
                        $c[$r['key']] = $r['value'];
                    }
                    $ret[$d] = $c;
                }
            }
            return $ret;
        }

        public static function set($database, $device, $key, $value) {
            if ($device === false) {
                if (!$database->prepared_query('SELECT `id` FROM `config` WHERE `device` IS NULL AND `key`=?', array('s'), array($key))) { return false; }
            } else {
                if (!$database->prepared_query('SELECT `id` FROM `config` WHERE `device`=? AND `key`=?', array('i', 's'), array($device, $key))) { return false; }
            }
            if (isset($database->result[0])) {
                $id = $database->result[0]['id'];
                if (!$database->prepared_query('UPDATE `config` SET `value`=? WHERE `id`=?', array('s', 'i'), array($value, $id))) { return false; }
            } else {
                if ($device === false) {
                    if (!$database->prepared_query('INSERT INTO `config` (`key`, `value`) VALUES (?, ?)', array('s', 's'), array($key, $value))) { return false; }
                } else {
                    if (!$database->prepared_query('INSERT INTO `config` (`device`, `key`, `value`) VALUES (?, ?, ?)', array('i', 's', 's'), array($device, $key, $value))) { return false; }
                }
            }
            return true;
        }

        public static function del($database, $device, $key) {
            if ($device === false) {
                if (!$database->prepared_query('DELETE FROM `config` WHERE `device` IS NULL AND `key`=?', array('s'), array($key))) { return false; }
            } else {
                if (!$database->prepared_query('DELETE FROM `config` WHERE `device`=? AND `key`=?', array('i', 's'), array($device, $key))) { return false; }
            }
            return true;
        }
    }
?>
