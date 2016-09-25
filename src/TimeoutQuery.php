<?php
/**
 * mysqli-query-timeout
 * wangyang
 */
namespace TimeoutQuery;
use Exception;
use mysqli;

class TimeoutQuery
{
    public $db;
    public $error;
    public $errno;
    public $affected_rows;
    public $timeout = 3;
    function __construct($config){
        $this->db = new mysqli($config['host'], $config['user'], $config['password'], $config['dbname'], $config['port']);
        $this->timeout = $config['timeout'] ?: 3;
        $this->db->set_charset($config['charset']);
    }

    /**
     * @param mixed $sql 
     * @param mixed $timeout 
     * @return mixed 
     */
    public function query($sql,$timeout = null){
        $timeout == null && $timeout = 3;
        $this->db->query($sql, MYSQLI_ASYNC);
        $all_links = array($this->db);
        $processed = 0;
        $begin = microtime(true);
        $ret = null;
        do {
            $links = $errors = $reject = array();
            foreach ($all_links as $link) {
                $links[] = $errors[] = $reject[] = $link;
            }
            if (!mysqli_poll($links, $errors, $reject, 0, 50000)) {
                if(microtime(true)-$begin > $timeout){
                    break;
                }
                continue;
            }
            foreach ($links as $link) {
                if ($result = $link->reap_async_query()) {
                    while($row = $result->fetch_assoc()){
                        $ret[] = $row;
                    }
                    if (is_object($result)){
                        mysqli_free_result($result);
                    }
                } else {
                    $this->error = sprintf("MySQLi Error: %s", mysqli_error($link));
                    throw new Exception($this->error);
                }
                $processed++;
            }
        } while ($processed < count($all_links));
        return $ret;
    }

    public function execute($sql,$timeout = null){
        $timeout == null && $timeout = 3;
        $this->db->query($sql, MYSQLI_ASYNC);
        $all_links = array($this->db);
        $processed = 0;
        $begin = microtime(true);
        $ret = null;
        do {
            $links = $errors = $reject = array();
            foreach ($all_links as $link) {
                $links[] = $errors[] = $reject[] = $link;
            }
            if (!mysqli_poll($links, $errors, $reject, 0, 50000)) {
                if(microtime(true)-$begin > $timeout){
                    break;
                }
                continue;
            }
            foreach ($links as $link) {
                if ($result = $link->reap_async_query()) {
                    $ret = $result;
                    if ($result === TRUE){
                        $this->affected_rows = $link->affected_rows;
                    }else{
                        $this->error = $link->error;
                        $this->errno = $link->errno;
                    }
                } else {
                    $this->error = sprintf("MySQLi Error: %s", mysqli_error($link));
                    throw new Exception($this->error);
                };
                $processed++;
            }
        } while ($processed < count($all_links));
        return $ret;
    }

    public function insert_id(){
        return $this->db->insert_id;
    }

    public function close(){
        $this->db->close();
        unset($this->db);
    }

    function __destruct(){
        $this->db && $this->db->close();
        if(isset($this->db))
            unset($this->db);
    }

}
