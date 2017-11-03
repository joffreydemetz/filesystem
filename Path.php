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
 * Path Helper
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
abstract class Path
{
  protected static $ROOT_PATH = '';
  
  public static function setRootPath($path)
  {
    self::$ROOT_PATH = $path;
  }
  
	/**
	 * Checks if a path's permissions can be changed.
	 *
	 * @param 	string  $path  Path to check.
	 * @return 	boolean  True if path can have mode changed.
	 */
	public static function canChmod($path)
	{
		$perms = fileperms($path);
		if ($perms !== false)
		{
			if (@chmod($path, $perms ^ 0001))
			{
				@chmod($path, $perms);
				return true;
			}
		}

		return false;
	}

	/**
	 * Chmods files and directories recursively to given permissions.
	 *
	 * @param 	string  $path   Root path to begin changing mode [without trailing slash].
	 * @param 	string  $mode   Octal representation of the value to change mode.
	 * @return 	boolean  True if successful
	 */
	public static function setPermissions($path, $mode=0777, $recursive=true)
	{
    $path = Path::clean($path);
    
    $fs = new Filesystem();
    
    try {
      $fs->chmod($path, $mode, 0000, $recursive);
    } catch(IOExceptionInterface $e){
      return false;
    }
    
    return true;
	}

	/**
	 * Function to strip additional / or \ in a path name.
	 *
	 * @param 	string  $path  The path to clean.
	 * @param 	string  $ds    Directory separator (optional).
	 * @return 	string  The cleaned path
	 * @throws 	Exception
	 */
	public static function clean($path, $ds=DIRECTORY_SEPARATOR)
	{
		if ( !is_string($path) && !empty($path) ){
      return '';
			throw new Exception('Path::clean: $path is not a string or is empty.');
		}
    
		$path = trim($path);
    
		if ( empty($path) ){
			$path = self::$ROOT_PATH;
		}
    
		// Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR
		// If dealing with a UNC path don't forget to prepend the path with a backslash.
		elseif ( $ds == '\\' && $path[0] == '\\' && $path[1] == '\\' ){
			$path = "\\" . preg_replace('#[/\\\\]+#', $ds, $path);
		}
		else {
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}
}
