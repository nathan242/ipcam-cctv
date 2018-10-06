<?php
    class user {
        
        /**
         * Check if a valid user is logged in. If not, redirect to login page.
         * 
         * @param object $db Database object
         */
        public static function check_logged_in(&$db) {
            if (
            !isset($_SESSION['user_id']) 
            || !$db->prepared_query('SELECT `id` FROM `users` WHERE `id`=? AND `enabled`=1', array('i'), array($_SESSION['user_id']))
            || !isset($db->result[0])
            ) {
                session_destroy();
                header('Location: index.php');
                exit;
            }
        }

        /**
         * Attempt to log in. If successful the user data will be set in the session.
         * 
         * @param object $db Database object
         * @param string $username Username
         * @param string $password Password
         * @return boolean
         */
        public static function login(&$db, $username, $password) {
            $hash = hash('sha256', $password);
            if (!$db->prepared_query('SELECT `id` FROM `users` WHERE `username`=? AND `password`=? AND `enabled`=1', array('s', 's'), array($username, $hash)) || !isset($db->result[0])) {
                return false;
            } else {
                $_SESSION['user_id'] = $db->result[0]['id'];
                $_SESSION['loginuser'] = $username;
                return true;
            }
        }
    }
