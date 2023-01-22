<?php
require_once(ROOTDIR.'/vendor/autoload.php');
use \MonologLogger as MonologLogger;
use \MonologHandlerStreamHandler as MonologHandlerStreamHandler;
use Monolog\Logger ;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;


class Log_master 
{
    private $logger;
    private $dir;
    private $full_dirpath;
    private $log_dir;
    private $log_sub_dir;
    private $logfile_name;
    private $file_url;
    private $file_basename;

    public function __construct($channel = '',$log_sub_dir = ""){
        $this->channel_name = $channel;
        $this->enabled_error_types = [""];
        $this->logger = new Logger($channel);
        $this->log_sub_dir = $log_sub_dir;
        $this->log_dir = "logs";
        $this->full_dirpath = ROOTDIR . "{$this->log_dir}/{$this->log_sub_dir}";
        $this->show_logs = false;
        $this->set_filename(debug_backtrace());
        $this->enabled_log_types = $this->get_enabled_log_types();
    }

    public function get_log_url(){
        return $this->download_logfile_url( $this->logfile_name);
    }

    public function get_log_file(){
        return $this->full_dirpath;
    }

    public function display_logs($value = true){
        $this->show_logs = $value;
    }

    public function set_filename($debug_backtrace){
        if(isset($debug_backtrace[0])){
            $filepath = $debug_backtrace[0]['file'];
            $filepath = str_replace('\\', '/', $filepath); // handle widnows dir path
            $filename = str_replace(ROOTDIR , '' , $filepath); // Remove rootdir from url
            $filename = str_replace('.php' , '' , $filename); // remove extension
            $filename = str_replace('\\', '.', $filename); // replace slashes with dots
            $filename = str_replace('/' , "." , $filename); // replace slashes with dots
            
            if($filename !=''){
                $this->file_basename = $filename;
                $this->logfile_name =  $filename ."_". date('Y-m-d') . ".log";
                $fullpath = $this->full_dirpath.$this->logfile_name;
                $base_url = sprintf(
                    "%s://%s/",
                    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                    $_SERVER['SERVER_NAME']
                );
                $this->file_url = $base_url . "{$this->log_dir}/{$this->log_sub_dir}" . $this->logfile_name; 
                $handler = new StreamHandler($fullpath);
                $channel_format = !empty($this->channel_name) ? " [%channel%]" : "";
                $logFormat = "[%datetime%]".$channel_format." [%level_name%]: %message% %context% %extra%\n";
                $formatter = new LineFormatter($logFormat,null,true,true);
                $handler->setFormatter($formatter); // What I really want is to make this change ...
                $this->logger->pushHandler($handler);
            }
        }
    }

    public function auto_delete_log($before_days){
        if($this->file_basename){
            $files = glob($this->full_dirpath."$this->file_basename*.log");
            $now   = time();

            foreach ($files as $file) {
                if (is_file($file)) {
                    if ($now - filemtime($file) >= 60 * 60 * 24 * $before_days) { // x days before 
                        unlink($file);
                    }
                }
            }
        }
    }

    private function log($msg ,$log_type = "info" ,$data = []){
       
        if(in_array($log_type,$this->enabled_log_types) ){
            $traceback_details = $this->traceback_details(debug_backtrace());
            $msg = $traceback_details . "| " .$msg;
            if($this->show_logs){
                $json = !empty($data) ? json_encode($data) : "";
                echo PHP_EOL.$msg . "|". $json;; 
            }
            $this->logger->{$log_type}($msg,$data);
        }
    }

    public function info($msg,$data=[]){
        $this->log($msg , "info" , $data);
    }
    public function warning($msg,$data=[]){
        $this->log($msg , "warning" , $data);
    }
    public function error($msg,$data=[]){
        $this->log($msg , "error" , $data);
    }
    public function critical($msg,$data=[]){
        $this->log($msg , "critical" , $data);
    }
    public function debug($msg,$data=[]){
        $this->log($msg , "debug" , $data);
    }
    public function notice($msg,$data=[]){
        $this->log($msg , "notice" , $data);
    }
    public function alert($msg,$data=[]){
        $this->log($msg , "alert" , $data);
    }
    public function emergency($msg,$data=[]){
        $this->log($msg , "emergency" , $data);
    }

    public function traceback_details($debug_backtrace){
        $backtrace = isset($debug_backtrace[2]) ? $debug_backtrace[2] : [];
        if(!empty($backtrace)){
            $class = isset($backtrace['class']) ? $backtrace['class'] . "->"  : "";
            $method = $class . $backtrace['function'];
            $line_no =  $debug_backtrace[1]['line'];
            return $method . " | " . $line_no ; 
        }

        $backtrace = isset($debug_backtrace[1]) ? $debug_backtrace[1] : [];
        if(!empty($backtrace)){
            $method = "--";
            $line_no =  $debug_backtrace[1]['line'];
            return $method . " | " . $line_no ; 
        }
        return '';
    }
    function get_enabled_log_types(){
        return  ["info","warning","error","critical","debug","notice","alert","emergency"];
    }
    function get_server_logpath($filename  , $date= false , $auto_detect = true){
        if($auto_detect){
            $filename = str_replace('.php' , '' , $filename); // remove extension
            $filename = str_replace('.log' , '' , $filename); // remove extension
            $filename = str_replace('\\', '.', $filename); // replace slashes with dots
            $filename = str_replace('/' , "." , $filename); // replace slashes with dots
            if(!$date){
                $date =  date('Y-m-d');
            }
            $filename =  $filename ."_" . $date . ".log";
        }
        $file_uri = $_SERVER['DOCUMENT_ROOT'] . "{$this->log_dir}/{$this->log_sub_dir}" . $filename; 
        return $file_uri;
    }

    function download_logfile($file_uri){
        if(!file_exists($file_uri)){ // file does not exist
            die("file not found : " . $file_uri);
        } else {
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . basename($file_uri));
            header("Content-Type: text/plain");
            header("Content-Transfer-Encoding: binary");
            // read the file from disk
            readfile($file_uri);
        }
    }

    function download_logfile_url($filename){
        $base_url = sprintf(
            "%s://%s/",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME']
        );
        return $download_logfile_url = $base_url . "Log_master.php?action=download&file=$filename&auto_detect=0";
    }
}
// For testing purpose only
// Refer this for documentation : https://stackify.com/php-monolog-tutorial/
if (isset($_GET['action'])) {
    // ?action=test
    if ($_GET['action'] == 'test') {
        $Log_master = new Log_master(); 
        $Log_master->info("This is info log!" );
        $Log_master->error("This is error log!" );
        $Log_master->debug("This is debug log!" ,['test'=>'test']);
        $delete_before_days = 7;
        $Log_master->auto_delete_log(7);
        echo PHP_EOL . $Log_master->get_log_url(); // print log file url
    }
    if ($_GET['action'] == 'download') {
        $filename = $_GET['file'];
        $filename = str_replace("../" , "" , $filename);
        $date = !isset($_GET['date']) ? false : $_GET['date'];
        $auto_detect = !isset($_GET['auto_detect']) ? 1 : $_GET['auto_detect'];
        $Log_master = new Log_master(); 
        $server_log_path = $Log_master->get_server_logpath($filename,$date,$auto_detect);
        $Log_master->download_logfile($server_log_path);
        exit;
    }
}