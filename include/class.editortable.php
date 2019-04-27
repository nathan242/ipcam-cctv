<?php
    class editortable extends gui {
        public $title;
        public $key;
        public $original_data;
        public $data;
        public $headings = false;
        public $field_split = '+';
        public $buttons = false;
        public $allow_delete = false;
        
        /**
         * Constructor
         * 
         * @param string $title Editor title
         * @param string $key Data key
         */
        public function __construct($title = '', $key = 'id') {
            $this->title = $title;
            $this->key = $key;
        }
        
        /**
         * Set data for editor to show
         * 
         * @param array $data Editor data
         * @param boolean $allow_delete Set true to enable delete function
         */
        public function set_data($data, $allow_delete = false) {
            $this->allow_delete = $allow_delete;
            
            $this->original_data = $data;
            $key_value = 0;
            
            foreach ($data as &$row) {
                foreach ($row as $key => &$field) {
                    if ($key === $this->key) {
                        $key_value = $field;
                        continue;
                    }
                    
                    $field = '<input type="text" name="'.$key_value.$this->field_split.$key.'" onchange="'.$this->title.'_update(this);" value="'.$field.'">';
                }
            }
            
            $this->data = $data;
        }
        
        /**
         * Set headings
         * 
         * @param array $headings Array of headings
         */
        public function set_headings($headings) {
            $this->headings = $headings;
        }
        
        /**
         * Set table buttons
         * 
         * @param array $config Button configuration
         */
        public function set_buttons($config) {
            $this->buttons = $config;
        }
        
        /**
         * Handle post data for edit
         * 
         * @param function $function Function to edit data
         * @param array $pass Array of additional parameters for function
         * @return bool Returns false if post data is not valid
         */
        public function handle_edit($function, $pass = []) {
            if (!isset($_POST[$this->title.'_edit']) || $_POST[$this->title.'_edit'] !== '1') { return false; }
            if (!isset($_POST[$this->key]) || $_POST[$this->key] === '') { return false; }
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
        
        /**
         * Handle post data for delete
         * 
         * @param function $function Function to delete data
         * @param array $pass Array of additional parameters for function
         * @return boolean Returns false if post data is not valid
         */
        public function handle_delete($function, $pass = []) {
            if (!isset($_POST[$this->title.'_delete']) || $_POST[$this->title.'_delete'] !== '1') { return false; }
            if (!isset($_POST[$this->key]) || $_POST[$this->key] === '') { return false; }
            
            $pass[] = $_POST[$this->key];
            
            if (call_user_func_array($function, $pass));
            header('Location: '.$_SERVER['HTTP_REFERER']);
            exit();
        }

        /**
         * Render editor table
         * 
         * @param bool $panel Set true to show table in a panel
         */
        public function html($panel = false) {
            $this->output_update_js();
            
            if ($this->allow_delete) {
                $this->output_delete_js();
                $delete = [
                    'text'    => 'DELETE',
                    'vkey'    => $this->key,
                    'colour'  => 'danger',
                    'options' => [
                        'onclick' => $this->title.'_delete({vkey});'
                    ]
                ];
                
                if (is_array($this->buttons)) {
                    array_unshift($this->buttons, $delete);
                } else {
                    $this->buttons[] = $delete;
                }
            }
            
            ob_start();
            self::table($this->data, $this->headings, false, $this->buttons);
            if ($panel) {
                self::panel($this->title, ob_get_clean());
            } else {
                echo ob_get_clean();
            }
        }
        
        /**
         * Output table update JS
         */
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
        formData.append("'.$this->title.'_edit", 1);
        formData.append("'.$this->key.'", data[0]);
        formData.append("field", data[1]);
        formData.append("value", field.value);
        xhr.send(formData);
    }
</script>
';
        }
        
        /**
         * Output table delete JS
         */
        private function output_delete_js() {
            echo '
<script>
    function '.$this->title.'_delete(id) {
        if (confirm("Delete device "+id+"?")) {
            var form = document.createElement("form");
            var e1 = document.createElement("input");
            var e2 = document.createElement("input");

            form.method = "POST";

            e1.name = "'.$this->title.'_delete";
            e1.value = 1;
            e1.type = "hidden";
            e2.name = "id";
            e2.value = id;
            e2.type = "hidden";

            form.appendChild(e1);
            form.appendChild(e2);
            document.body.appendChild(form);

            form.submit();
        }
    }
</script>
';
        }
    }
    
