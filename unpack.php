#!/usr/bin/env php
<?php
/**
 * All-in-One WP Migration Extractor CLI Wrapper
 *
 * This script provides a command-line interface to the Ai1wm_Extractor class.
 *
 * Source Class:
 * [Ai1wm_Extractor](https://github.com/dev071/all-in-one-wp-migration-unlimited/blob/main/lib/vendor/servmask/archiver/class-ai1wm-extractor.php)
 */

ini_set('memory_limit','4096M');
error_reporting(E_ERROR | E_PARSE);
$default_extract_path = "./wp-migration";

require_once __DIR__ . '/wordpress/wp-includes/plugin.php';
require_once __DIR__ . '/all-in-one-wp-migration-unlimited/lib/vendor/servmask/archiver/class-ai1wm-archiver.php';
require_once __DIR__ . '/all-in-one-wp-migration-unlimited/lib/vendor/servmask/archiver/class-ai1wm-extractor.php';

class CLI_Extractor extends Ai1wm_Extractor {

  protected $get_data_from_block = null;

  public function __construct( $file_name ) {
    parent::__construct( $file_name );
    $this->get_data_from_block = new ReflectionMethod("Ai1wm_Extractor", "get_data_from_block");
    $this->get_data_from_block->setAccessible(true);
  }

  public function reset () {
    if ( @fseek( $this->file_handle, 0, SEEK_SET ) === -1 ) {
      throw new Ai1wm_Not_Seekable_Exception( sprintf( 'Unable to seek to beginning of file. File: %s', $this->file_name ) );
    }
  }

  public function list_files() {
    $files = array();
    echo "Contents of " . $this->file_name . "\n";
    if ( @fseek( $this->file_handle, 0, SEEK_SET ) === -1 ) {
      throw new Ai1wm_Not_Seekable_Exception( sprintf( 'Unable to seek to beginning of file. File: %s', $this->file_name ) );
    }
    while ( $block = @fread( $this->file_handle, 4377 ) ) {
      if ( ( $data = $this->get_data_from_block->invoke($this, $block ) ) ) {
        //array_push($files, $data['path']);
        array_push($files, $data['path'] . DIRECTORY_SEPARATOR . $data['filename']);
        if ( @fseek( $this->file_handle, (int) $data['size'], SEEK_CUR ) === -1 ) {
          throw new Ai1wm_Not_Seekable_Exception( sprintf( 'Unable to seek to offset of file. File: %s Offset: %d', $this->file_name, $data['size'] ) );
        }
      }
    }
    //return array_unique($files);
    return $files;
  }

}

if ($argc < 2 || in_array($argv[1], ['-h', '--help'])) {
    echo "Usage: php " . basename(__FILE__) . " <archive_path> [destination_path]\n\n";
    echo "  <archive_path>    Path to the .wpress archive file to extract.\n";
    echo "  [destination_path] Optional path to the directory where files should be extracted.\n";
    echo "                    Defaults to './extracted_ai1wm' in the current directory.\n";
    exit(1);
}

$archive_path = $argv[1];
$destination_path = isset($argv[2]) ? $argv[2] : $default_extract_path;
if ($destination_path !== null) {
  @mkdir($destination_path);
} else {
  echo "No directory given!";
  exit(1);

}


try {
    $extractor = new CLI_Extractor($archive_path);
    $num = $extractor->get_total_files_count();
    echo "Number of files: " . $num . "\n";

    $files = $extractor->list_files();
    $extractor->reset();
    echo "Archive read, " . count($files) . " entries\n";
    echo "The following files will be extracted";
    foreach ($files as $file) {
      echo $file . "\n";
    }

    //$success = $extractor->extract_by_files_array($destination_path, $files);
    echo "Starting extraction...\n";
    while ( $extractor->has_not_reached_eof() ) {
      $success = $extractor->extract_one_file_to($destination_path);
    }
    echo "Done\n";
    if ($success) {
      exit(0);
    } else {
      echo "Extraction failed!";
      exit(1);
    }

} catch (Exception $e) {
    echo "An unexpected error occurred: " . $e->getMessage() . "\n";
    exit(1);
}

?>
