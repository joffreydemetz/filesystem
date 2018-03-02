<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace JDZ\Filesystem;

/**
 * Filesystem Helper
 * 
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
abstract class Helper
{
  /**
   * Holds an array of translations
   * 
   * Defaults are in french
   * 
   * @var   array
   */
  protected static $translations = [
    'UNKNOWN_ERROR' => 'Erreur de type inconnu',
    
    'FAILED_COPYING_FILE' => 'Échec de la copie',
    'FAILED_DELETING_FILE' => 'Échec de la suppression',
    'FILE_CANNOT_FIND_SOURCE' => 'Impossible de trouver le fichier source',
    'UNABLE_TO_FIND_SOURCE_FILE' => 'Impossible de trouver ou de lire le fichier',
    'FAILED_RENAMING_FILE' => 'Échec du renommage',
    'UNABLE_TO_READ_FILE' => 'Impossible d\'ouvrir le fichier',
    'UNABLE_TO_MODIFY_FILE_PERMISSIONS' => 'Impossible de modifier les permissions du fichier.',
    'UNABLE_TO_MODIFY_MOVE_UPLOADED_FILE' => 'Impossible de déplacer le fichier uploadé.',
    'FAILED_FINDING_SOURCE_FOLDER' => 'Impossible de trouver le répertoire source',
    'FOLDER_ALREADY_EXISTS' => 'Le répertoire existe déjà',
    'FAILED_CREATING_FOLDER' => 'Impossible de créer le répertoire cible',
    'FAILED_READING_SOURCE_FOLDER' => 'Impossible d\'ouvrir le répertoire source',
    'FOLDER_LOOP' => 'Boucle infinie détectée',
    'FOLDER_PATH_IS_NOT_IN_OPEN_BASEDIR' => 'Le chemin n\'est pas dans les chemins open_basedir',
    'FAILED_DELETING_FOLDER' => 'Impossible de supprimer le répertoire.',
    'FOLDER_CANNOT_DELETE_ROOT' => 'Vous ne pouvez pas supprimer un répertoire de base.',
    'FAILED_RENAMING_FOLDER' => 'Échec du renommage',
    'FOLDER_PATH_IS_NOT_A_FOLDER' => 'Le chemin n\'est pas un répertoire.',
    'INVALID_ZIP_DATA' => 'Données ZIP invalides',

    'ARCHIVE_UNABLE_TO_LOAD' => 'Impossible de charger l\'archive',
    'ARCHIVE_UNABLE_TO_READ' => 'Impossible de lire l\'archive (%s)',
    'ARCHIVE_UNABLE_TO_WRITE' => 'Impossible d\'écrire l\'archive (%s)',
    'ARCHIVE_UNABLE_TO_WRITE_FILE' => 'Impossible d\'écrire le fichier (%s)',
    'ARCHIVE_UNABLE_TO_WRITE_ENTRY' => 'Impossible d\'écrire l\'entrée (%s)',
    'ARCHIVE_UNABLE_TO_DECOMPRESS' => 'Impossible de décompresser les données',
    'ARCHIVE_UNABLE_TO_CREATE_DESTINATION' => 'Impossible de créer la destination',
    'ARCHIVE_UNABLE_TO_READ_ENTRY' => 'Impossible de lire l\'entrée',
    'ARCHIVE_UNABLE_TO_OPEN_ARCHIVE' => 'Impossible d\'ouvrir l\'archive',
    'ARCHIVE_ZIP_INFO_FAILED' => 'Échec de l\'obtention de l\'information ZIP',
  ];
  
  /**
   * Set the translations
   *
   * @param   array   $translations     Key/value pairs of translations
   * @return  void
   */
  public static function setTranslations(array $translations=[])
  {
    self::$translations = array_merge(self::$translations, $translations);
  }
  
  /**
   * Translation
   * 
   * @param   string  $key  The translation key
   * @return  string  Translated string or "Unknown Error" if not found
   */
  public static function getTranslation($key)
  {
    $key = strtoupper($key);
    if ( isset(self::$translations[$key]) ){
      return self::$translations[$key];
    }
    
    return self::$translations['UNKNOWN_ERROR'];
  }  
}
