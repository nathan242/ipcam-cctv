<?php
    class editortable extends gui {
        public $title;
        public $key;
        public $original_data;
        public $data;
        public $field_split = '+';
        
        public function __construct($title = '', $key = 'id') {
            $this->title = $title;
            $this->key = $key;
        }
        
        public function set_data($data) {
            $this->original_data = $data;
            $key_value = 0;
            
            foreach ($data as &$row) {
                foreach ($row as $key => &$field) {
                    if ($key == $this->key) {
                        $key_value = $field;
                        continue;
                    }
                    
                    $field = '<input type="text" name="'.$key_value.$this->field_split.$key.'" onchange="'.$this->title.'_update(this);" value="'.$field.'">';
                }
            }
            
            $this->data = $data;
        }
        
        public function handle($function, $pass = array()) {
            if (!isset($_POST['edit']) || $_POST['edit'] != 1) { return false; }
            if (!isset($_POST[$this->key]) || $_POST[$this->key] == '') { return false; }
            if (!isset($_POST['field']) || !isset($_POST['value'])) { return false; }
            
            $pass[] = $_POST[$this->key];
            $pass[] = $_POST['field'];
            $pass[] = $_POST['value'];

            if (call_user_func_array($function, $pass)) {
                exit('0');
            } else {
                exit('1');
            }
        }
        
        public function html($panel = false) {
            $this->output_update_js();
            gui::table($this->data);
        }
        
        private function output_update_js() {
            echo '
<script>
    function '.$this->title.'_update(field) {
        field.style.backgroundColor = "#FFFF00";
        var data = field.name.split("'.$this->field_split.'");
        xhr = new XMLHttpRequest();
        xhr.open("POST", "");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                if (xhr.responseText == 0) {
                    field.style.backgroundColor = "#FFFFFF";
                } else {
                    field.style.backgroundColor = "#FF0000";
                }
            }
        };
        formData = new FormData();
        formData.append("edit", 1);
        formData.append("'.$this->key.'", data[0]);
        formData.append("field", data[1]);
        formData.append("value", field.value);
        xhr.send(formData);
    }
</script>
';
        }
    }
    