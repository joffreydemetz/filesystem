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
 * Folder Helper
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
abstract class Folder
{
  /**
   * Mirrors a directory to another.
   *
   * @param   string    $src    The path to the source folder
   * @param   string    $dest   The path to the destination folder
   * @param   bool      $force  Force copy
   * @throws   Exception
   */
  public static function copy($src, $dest, $force=false, $delete=false)
  {
    $src  = Path::clean($src);
    $dest = Path::clean($dest);
    
    $fs = new Filesystem();
    
    try {
      $fs->mirror($src, $dest, null, [ 'override' => $force, 'delete' => $delete, 'copy_on_windows' => true ]);
    } catch(IOExceptionInterface $e){
      throw new Exception(Helper::getTranslation('FAILED_FINDING_SOURCE_FOLDER').' '.$e->getPath());
    }
    
    return true;
  }

  /**
   * Creates a directory recursively.
   *
   * @param string|array|\Traversable $dirs The directory path
   * @param int                       $mode The directory mode
   * @throws Exception On any directory creation failure
   */
  public static function create($path='', $mode=0777)
  {
    $path = Path::clean($path);
    
    $fs = new Filesystem();
    
    try {
      $fs->mkdir($path);
    } catch(IOExceptionInterface $e){
      throw new Exception(Helper::getTranslation('FAILED_CREATING_FOLDER').' '.$e->getPath());
    }
    
    return true;
  }
  
  /**
   * Checks the existence of files or directories.
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
   * Delete a folder.
   *
   * @param   string    $path     The path to the folder to delete.
   * @return   boolean   True on success
   * @throws   Exception
   */
  public static function delete($path)
  {
    if ( !Folder::exists($path) ){
      return true;
    }
    
    $path = Path::clean($path);
    
    if ( trim($path) === '' ){
      throw new Exception(Helper::getTranslation('FOLDER_CANNOT_DELETE_ROOT'));
    }
    
    $fs = new Filesystem();
    
    try {
      $fs->remove($path);
    }
    catch(IOExceptionInterface $e){
      throw new Exception(Helper::getTranslation('FAILED_DELETING_FOLDER').' - ('.$e->getMessage().')');
    }
    
    return true;
  }
  
  /**
   * Moves a folder.
   *
   * @param   string    $src          The path to the source folder
   * @param   string    $dest         The path to the destination folder
   * @param   bool      $overwrite    Overwrite destination file
   * @return   boolean   True on success
   * @throws   Exception
   */
  public static function move($src, $dest, $overwrite=false)
  {
    $src  = Path::clean($src);
    $dest = Path::clean($dest);
    
    if ( !Folder::exists($src) ){
      throw new Exception(Helper::getTranslation('FAILED_FINDING_SOURCE_FOLDER'));
    }
    
    if ( Folder::exists($dest) ){
      throw new Exception(Helper::getTranslation('FOLDER_ALREADY_EXISTS'));
    }
    
    $fs = new Filesystem();
    
    try {
      $fs->rename($src, $dest, $overwrite);
    }
    catch(IOExceptionInterface $e){
      throw new Exception(Helper::getTranslation('FAILED_RENAMING_FOLDER').' - ('.$e->getMessage().')');
    }
    
    return true;
  }

  
  /**
   * Utility function to read the files in a folder.
   *
   * @param   string    $path           The path of the folder to read.
   * @param   string    $filter         A filter for file names.
   * @param   mixed     $recurse        True to recursively search into sub-folders, or an integer to specify the maximum depth.
   * @param   boolean   $full           True to return the full path to the file.
   * @param   array     $exclude        Array with names of files which should not be shown in the result.
   * @param   array     $excludefilter  Array of filter to exclude
   * @param   boolean   $naturalSort    False for asort, true for natsort
   * @return   array     Array of files
   * @throws   Exception
   */
  public static function files($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'Thumbs.db'), $excludefilter = array('^\..*', '.*~'), $naturalSort = true)
  {
    $path = Path::clean($path);

    if ( !is_dir($path) ){
      throw new Exception(Helper::getTranslation('FOLDER_PATH_IS_NOT_A_FOLDER'));
    }

    if ( count($excludefilter) ){
      $excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
    }
    else {
      $excludefilter_string = '';
    }

    $arr = Folder::_items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, true);

    if ( $naturalSort ){
      natsort($arr);
    }
    else {
      asort($arr);
    }
    return array_values($arr);
  }

  /**
   * Utility function to read the folders in a folder.
   *
   * @param   string    $path           The path of the folder to read.
   * @param   string    $filter         A filter for folder names.
   * @param   mixed     $recurse        True to recursively search into sub-folders, or an integer to specify the maximum depth.
   * @param   boolean   $full           True to return the full path to the folders.
   * @param   array     $exclude        Array with names of folders which should not be shown in the result.
   * @param   array     $excludefilter  Array with regular expressions matching folders which should not be shown in the result.
   * @return   array     Array of folders
   * @throws   Exception
   */
  public static function folders($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'Thumbs.db'), $excludefilter = array('^\..*'))
  {
    $path = Path::clean($path);

    if ( !is_dir($path) ){
      throw new Exception(Helper::getTranslation('FOLDER_PATH_IS_NOT_A_FOLDER'));
    }

    if ( count($excludefilter) ){
      $excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
    }
    else {
      $excludefilter_string = '';
    }
    
    $arr = Folder::_items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, false);
    
    asort($arr);
    return array_values($arr);
  }

  /**
   * Function to read the files/folders in a folder.
   *
   * @param   string    $path                  The path of the folder to read.
   * @param   string    $filter                A filter for file names.
   * @param   mixed     $recurse               True to recursively search into sub-folders, or an integer to specify the maximum depth.
   * @param   boolean   $full                  True to return the full path to the file.
   * @param   array     $exclude               Array with names of files which should not be shown in the result.
   * @param   string    $excludefilter_string  Regexp of files to exclude
   * @param   boolean   $findfiles             True to read the files, false to read the folders
   * @return   array     Files
   */
  protected static function _items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles)
  {
    @set_time_limit(ini_get('max_execution_time'));

    $arr = [];

    if ( !($handle = @opendir($path)) ){
      return $arr;
    }

    while(($file = readdir($handle)) !== false){
      if ( $file != '.' && $file != '..' && !in_array($file, $exclude) && (empty($excludefilter_string) || !preg_match($excludefilter_string, $file)) ){
        $fullpath = $path . DIRECTORY_SEPARATOR . $file;

        $isDir = is_dir($fullpath);

        if ( ($isDir xor $findfiles) && preg_match("/$filter/", $file) ){
          if ( $full ){
            $arr[] = $fullpath;
          }
          else {
            $arr[] = $file;
          }
        }
        
        if ( $isDir && $recurse === true ){
          $arr = array_merge($arr, Folder::_items($fullpath, $filter, true, $full, $exclude, $excludefilter_string, $findfiles));
        }
      }
    }
    
    closedir($handle);
    return $arr;
  }
}
