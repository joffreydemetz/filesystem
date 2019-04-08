<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Filesystem;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Exception;

/**
 * File Helper
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
abstract class File
{
  /**
   * Copies a file
   *
   * If the target file is older than the origin file, it's always overwritten
   * If the target file is newer, it is overwritten only when the
   * $overwriteNewerFiles option is set to true.
   *
   * @param   string  $originFile   The original filename
   * @param   string  $targetFile   The target filename
   * @param   bool    $force        If true, target files newer than origin files are overwritten
   * @throws  Exception  When src doesn't exist or When copy fails
   */
  public static function copy($src, $dest, $force=true)
  {
    $src  = Path::clean($src);
    $dest = Path::clean($dest);
    
    if ( !Folder::exists(dirname($dest)) ){
      Folder::create(dirname($dest));
    }
    
    $fs = new Filesystem();
    
    try {
      $fs->copy($src, $dest, $force);
    } catch(IOExceptionInterface $e){
      throw new Exception(Helper::getTranslation('CANNOT_FIND_SOURCE').' '.$e->getPath());
    }
    
    return true;
  }

  /**
   * Checks the existence of files or directories
   *
   * @param   string    $path     Folder name relative to installation dir
   * @return  bool      true if the file exists, false otherwise
   */
  public static function exists($path)
  {
    $path = Path::clean($path);
    
    $fs = new Filesystem();
    return $fs->exists($path);
  }
  
  /**
   * Write contents to a file
   *
   * @param   string    $filename    The full file path
   * @param   string    $buffer      The buffer to write
   * @return   boolean   True on success
   * @throws   Exception
   */
  public static function write($filename, $buffer)
  {
    $fs = new Filesystem();
    
    try {
      $fs->dumpFile($filename, $buffer);
    }
    catch(IOExceptionInterface $e){
      throw new Exception($e->getMessage());
    }
    
    return true;
  }

  /**
   * Delete a file or array of files
   *
   * @param   string    $path  The file name
   * @return   boolean   True on success
   */
  public static function delete($path)
  {
    $path = Path::clean($path);
    
    $fs = new Filesystem();
    
    try {
      $fs->remove($path);
    }
    catch(IOExceptionInterface $e){
      throw new Exception(Helper::getTranslation('FAILED_DELETING').' - ('.$e->getMessage().')');
    }
    
    return true;
  }

  /**
   * Moves a file
   *
   * @param   string    $src          The path to the source file
   * @param   string    $dest         The path to the destination file
   * @param   bool      $overwrite    Overwrite destination file
   * @return   boolean   True on success
   * @throws   Exception
   */
  public static function move($src, $dest, $overwrite=false)
  {
    $src  = Path::clean($src);
    $dest = Path::clean($dest);
    
    if ( !File::exists($src) ){
      throw new Exception(Helper::getTranslation('CANNOT_FIND_SOURCE').' : '.$src);
    }
    
    if ( File::exists($dest) && !$overwrite ){
      throw new Exception(Helper::getTranslation('ALREADY_EXISTS').' : '.$src);
    }
    
    try {
      $fs = new Filesystem();
      $fs->rename($src, $dest, $overwrite);
    }
    catch(IOExceptionInterface $e){
      throw new Exception(Helper::getTranslation('FAILED_RENAMING').' - ('.$e->getMessage().')');
    }
    
    return true;
  }

  /**
   * Read the contents of a file
   *
   * @param   string    $path       The full file path
   * @return   string    File contents
   * @throws   Exception
   */
  public static function read($path)
  {
    $path = Path::clean($path);
    if ( !File::exists($path) ){
      return '';
    }
    return file_get_contents($path);
  }
  
  public static function same(string $path1, string $path2){
    if ( false === ($fp1=fopen($path1, 'rb')) ){
      return false;
    }
    
    if ( false === ($fp2=fopen($path2, 'rb')) ){
        fclose($fp1);
        return false;
    }
    
    if ( mime_content_type($path1) !== mime_content_type($path2) 
      || filetype($path1) !== filetype($path2)
      || filesize($path1) <> filesize($path2)
    ){
      fclose($fp1);
      fclose($fp2);
      return false;
    }
    
    if ( fread($fp1, filesize($path1)) !== fread($fp2, filesize($path2)) ){
      fclose($fp1);
      fclose($fp2);
      return false;
    }
    
    fclose($fp1);
    fclose($fp2);

    return true;
  }

  /**
   * Moves an uploaded file to a destination folder
   *
   * @param   string    $src          The name of the php (temporary) uploaded file
   * @param   string    $dest         The path (including filename) to move the uploaded file to
   * @return   boolean   True on success
   * @throws   Exception
   */
  public static function upload($src, $dest)
  {
    $dest = Path::clean($dest);
    $baseDir = dirname($dest);

    try {
      if ( !Folder::exists($baseDir) ){
        Folder::create($baseDir);
      }
    }
    catch(Exception $e){
      throw $e;
    }
    
    if ( is_writeable($baseDir) && move_uploaded_file($src, $dest) ){
      if ( Path::setPermissions($dest) ){
        return true;
      }
      
      throw new Exception(Helper::getTranslation('FAILED_CHMOD'));
    }
    
    throw new Exception(Helper::getTranslation('FAILED_MOVE_UPLOAD'));
  }

  /**
   * Gets the extension of a file name
   *
   * @param   string  $file  The file name
   * @return   string  The file extension
   */
  public static function getExt($file)
  {
    $dot = strrpos($file, '.') + 1;
    return substr($file, $dot);
  }

  /**
   * Strips the last extension off of a file name
   *
   * @param  string  $file  The file name
   * @return string  The file name without the extension
   */
  public static function stripExt($file)
  {
    return preg_replace('#\.[^.]*$#', '', $file);
  }

  /**
   * Returns the name, without any path.
   *
   * @param   string  $file  File path
   * @return   string  filename
   */
  public static function getName($file)
  {
    $file = str_replace('\\', '/', $file);
    $slash = strrpos($file, '/');
    if ( $slash !== false ){
      return substr($file, $slash + 1);
    }

    return $file;
  }
  
  /**
   * Returns the file path
   *
   * @param  string  $file 
   * @return string
   */
  public static function getPath(string $file)
  {
    $parts = explode('/', $file);
    array_pop($parts);
    return implode('/', $parts);
  }
  
  /**
   * Returns the name, without any path nor extension
   *
   * @param  string  $file  File path
   * @return string  filename with no extension
   */
  public static function getStrippedName($file)
  {
    $name = self::getName($file);
    return self::stripExt($name);
  }
  
  /**
   * Makes file name safe to use
   *
   * @param  string  $file  The name of the file [not full path]
   * @return string  The sanitised string
   */
  public static function makeSafe($file)
  {
    $regex = [ 
      '#(\.){2,}#', 
      '#[^A-Za-z0-9\.\_\- ]#', '#^\.#',
    ];
    
    $file = rtrim($file, '.');
    $clean = preg_replace($regex, ' ', $file);
    $clean = preg_replace("/[ \_]/", '-', $clean);
    $clean = preg_replace("/[\-]+/", '-', $clean);
    return $clean;
  }
}
