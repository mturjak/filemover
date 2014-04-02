<?php
/* 
 * filemover jQuery plugin - PHP server script
 * https://github.com/mturjak/filemover
 *
 * Copyright 2014, Martin Turjak
 * http://newtpond.com
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

class FileMover {
  protected $options;

  public function __construct($options = null){
    $this->options = array(
      // basedirectory for files operations:
      'dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/',
      // name of request directory variable:
      'req-dir' => 'dir',
      // name of request file variable:
      'req-file' => 'file',
      // name of request method:
      'req-method' => 'POST',
      // allow directory creation:
      'new-dir' => false
    );
    if ($options) {
      $this->options = $options + $this->options;
    }
    $this->init();
  }

  private function init() {
    // no echoing should be done before header definition
    header('Content-type: text/plain; charset=utf-8', true); 
    // extract data from the request
    $data = $this->request_handler($this->options['req-file'], $this->options['req-dir'], $this->options['req-method']);
    if(empty($data)) {
      $response = array('done'=>0,'message'=>'No values passed or values not set correctly.');
    } else {
      if(!isset($data['file'])) {
        // if no files passed return list of files in directory
        $response = $this->list_files($data['dir']);
      } else if(gettype($data['file']) == 'array') {
        // move multiple files (passed as an array of filenames)
        $response = array();
        foreach($data['file'] as $f) {
          $response[] = $this->move($f, $data['dir']);
        }
      } else {
        // move single file
        $response = $this->move($data['file'], $data['dir']);
      }
    }
    echo json_encode($response);
  }

  private function request_handler($f, $d, $method) {
    if($_SERVER['REQUEST_METHOD'] === $method) {
      switch($method) {
        case 'POST':
          return $this->post($f, $d);
        case 'GET':
          return $this->get($f, $d);
      }
    }
    return array();
  }

  private function post($f, $d){
    $out = array();
    if(isset($_POST[$f]))
      $out['file'] = $_POST[$f];
    if(isset($_POST[$d]))
      $out['dir'] = $_POST[$d];
    return $out;
  }

  private function get($f, $d){
    $out = array();
    if(isset($_GET[$f]))
      $out['file'] = $_GET[$f];
    if(isset($_GET[$d]))
      $out['dir'] = $_GET[$d];
    return $out;
  }

  private function list_files($dir) {
    $baseDir = $this->options['dir'];
    $dir = realpath($baseDir.$dir);
    if(strpos($dir."/", $baseDir) === 0){
      $files = array();
      foreach (scandir($dir) as $item) {
        if(!is_dir($dir."/".$item))
          $files[] = $item;
      }
      return array('done'=>0,'files' => $files);
    }
    return array('done'=>0,'message'=>'Invalide directory!');
  }

  private function move($fname, $target_dir) {
    $baseDir = $this->options['dir'];
    $file = realpath($baseDir.$fname);
    $dir = realpath($baseDir.$target_dir);
    if(strpos($dir."/", $baseDir) === 0 && strpos($file, $baseDir) === 0){
      if(!is_dir($dir) && $this->options['new-dir'])
        mkdir($dir, 0755);
      if(is_dir($dir))
        return $this->move_handler($file, $dir);
    }
    return array('done'=>0,'message'=>'File or directory name error!');
  }

  private function move_handler($file, $target_dir) {
    if(rename($file, $target_dir.'/'.basename($file)))
      return array('done'=>1,'message'=>'The file '.basename($file).' has been moved to: '.basename($target_dir));
    return array('done'=>0,'message'=>'There was an error uploading the file, please try again!');
  }
}
