<?php
/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Set filesystem i18n
 * 
 * @param   array   $strings   Key/value pairs of translations
 * @param   bool    $default   Default translation if not set
 * @return  void
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
function FilesystemTranslate($strings, $default=false)
{
  \JDZ\Filesystem\Helper::setTranslations($strings);
  \JDZ\Filesystem\Helper::setTranslationDefaultValue($default);
}
