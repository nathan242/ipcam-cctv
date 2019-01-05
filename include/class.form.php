<?php
    class form extends gui {
        private $inputs = array();
        private $title;
        private $submit;
        private $submit_colour;
        private $method;
        
        public $result;

        /**
         * Construct a form.
         * 
         * @param string $title Form panel heading
         * @param string $submit Submit button text
         * @param string $submit_colour Submit button colour
         * @param string $method Form method (post/get)
         */
        public function __construct(
                $title = '',
                $submit = 'Submit',
                $submit_colour = 'primary',
                $method = 'post'
        ) {
            
            $this->title = $title;
            $this->submit = $submit;
            $this->submit_colour = $submit_colour;
            $this->method = $method;
        }

        /**
         * Add form input field.
         * 
         * @param string $name Field name
         * @param string $display_name Field display text
         * @param string $type Field type
         * @param boolean $allow_empty Allow empty values
         * @param string|boolean $value Optional default value
         * @param array $options Array of additional parameters
         */
        public function input(
                $name,
                $display_name,
                $type = 'text',
                $allow_empty = false,
                $value = false,
                $options = array()
        ) {
            
            $this->inputs[$name] = 
                    array('display_name' => $display_name,
                        'type' => $type,
                        'allow_empty' => $allow_empty,
                        'value' => $value,
                        'options' => $options
                    );
        }

        /**
         * Check for and handle submitted form.
         * 
         * @param function $function Function to process submitted data
         * @param array $pass Array of additional parameters for function
         * @return bool Returns true if form submit is valid
         */
        public function handle($function, $pass = array()) {
            $params = ($this->method == 'post') ? $_POST : $_GET;

            $input_data = array();
            $input_names = array_keys($this->inputs);
            foreach ($input_names as $i) {
                // Is the option set?
                if (!isset($params[$i])) { return false; }

                // If it is empty, is it allowed to be?
                if ($params[$i] == '' && $this->inputs[$i]['allow_empty'] == false) { return false; }

                // Build data array
                $input_data[$i] = $params[$i];
            }
            $pass[] = $input_data;
            $this->result = call_user_func_array($function, $pass);
            
            return true;
        }

        /**
         * Render form
         * 
         * @param boolean $inline Form fields will be inline
         * @param boolean $panel Render on a bootstrap panel
         * @param boolean $table Form will be in a table
         */
        public function html($inline = false, $panel = true, $table = false) {
            if ($inline) {
                if ($table) {
                    $sep_start = '<td>';
                    $sep_end = '</td>';
                } else {
                    $sep_start = $sep_end = ' ';
                }
            } else {
                if ($table) {
                    $sep_start = '<tr><td>';
                    $sep_end = '</td></tr>';
                } else {
                    $sep_start = '<p>';
                    $sep_end = '</p>';
                }
            }
            
            ob_start();
            echo '<form method="'.$this->method.'">';
            if ($table) {
                echo '<table class="table table-hover" border="1">';
                if ($inline) {
                    echo '<tr>';
                    foreach ($this->inputs as $v) {
                        if ($v['type'] != 'hidden') {
                            echo '<th>'.$v['display_name'].'</th>';
                        }
                    }
                    echo '<th></th>';
                    
                    echo '</tr><tr>';
                }
            }
            
            $style = '';
            if ($table && $inline) {
                $style = ' style="width: 100%;"';
            }

            foreach ($this->inputs as $k => $v) {
                if ($v['type'] == 'select') {
                    echo $sep_start;

                    echo '<strong>'.$v['display_name'].'</strong><select name="'.$k.'"'.$style.'>';
                    foreach ($v['options']['selects'] as $sk => $sv) {
                        echo '<option value="'.$sk.'"';
                        if ($v['value'] === $sk) { echo ' selected'; }
                        echo '>'.$sv.'</option>';
                    }
                    echo '</select>';

                    echo $sep_end;
                } elseif ($v['type'] != 'hidden') {
                    $extra = '';
                    
                    if ($v['value'] !== false) {
                        $extra .= ' value="'.$v['value'].'"';
                    } 
                    
                    if (isset($v['options']['placeholder'])) {
                        $extra .= ' placeholder="'.$v['options']['placeholder'].'"';
                    }
                    
                    if (isset($v['options']['autofocus'])) {
                        $extra .= ' autofocus';
                    }
                    
                    echo $sep_start;
                    
                    if (!($table && $inline)) {
                        echo '<strong>'.$v['display_name'].'</strong>';
                    }
                    
                    if ($table && !$inline) {
                        echo $sep_end;
                        echo $sep_start;
                    }
                    
                    echo '<input type="'.$v['type'].'" name="'.$k.'"'.$extra.$style.'>';
                    echo $sep_end;
                } else {
                    $extra = '';
                    
                    if ($v['value'] !== false) {
                        $extra .= ' value="'.$v['value'].'"';
                    }

                    echo '<input type="'.$v['type'].'" name="'.$k.'"'.$extra.'>';
                }

                if (isset($v['options']['after'])) { echo $v['options']['after']; }
            }

            echo $sep_start;
            echo '<input class="btn btn-'.$this->submit_colour.'" type="submit" value="'.$this->submit.'">';
            echo $sep_end;
            
            if ($table) {
                if ($inline) {
                    echo '</tr>';
                }
                
                echo '</table>';
            }
            
            echo '</form>';

            if ($panel) {
                self::panel($this->title, ob_get_clean());
            } else {
                echo ob_get_clean();
            }
        }
    }
