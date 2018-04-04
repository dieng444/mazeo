<?php

/**
 * Class Asset
 * @package Mazeo\Util\Util
 * @author Macky Dieng
 */
namespace Mazeo\Util\Util;


class Asset
{

    /**
     * Stylesheets file type static variable
     */
    const CSS_FILE = 'css';

    /**
     * Javascript file type static variable
     */
    const JS_FILE = 'js';

    private $host;
    /**
     * Asset constructor.
     */
    public function __construct()
    {
        $server = $_SERVER['SERVER_NAME'];
        $protocol = strtolower(explode('/',$_SERVER['SERVER_PROTOCOL'])[0]);
        $this->host = $protocol.'://'.$server.'/';
    }

    /**
     * Generates path for a given file
     * @param string $package - the package of the current file
     * @param string $file - the current file
     * @param boolean $isGlobal - Specify whether is a global file or not
     * @param string $subPath - optional sub directory fo unknown folder file type (pdf, img...)
     * @return null|string
     * @throws MazeoException
     */
    public function assignAssetFile($package, $path, $isGlobal=false)
    {
        $packageParts = null;
        if (is_null($package) && !$isGlobal) {
          throw new MazeoException(ErrorManager::MissedParameterMsg(__FUNCTION__,'package'));
        }
        if (!$isGlobal) {
          $packageParts = explode(':',$package);
        }
        $pathParts = explode('/',$path);
        $fileParts = explode('.',$pathParts[sizeof($pathParts) - 1]);
        $link = null;
        $assetFile = null;
        if ($isGlobal) {
          $assetFile = 'app/Resources/public/' . $path;
        } else {
          $assetFile = 'src/'.$packageParts[0].'/'.$packageParts[1].'/Resources/public/'.$path;
        }
        if (is_file($assetFile))  {
            $assetFile = $this->host.$assetFile;
            $ext = $fileParts[sizeof($fileParts) - 1];
            if ($ext===self::CSS_FILE) {
              $link = '<link href="'.$assetFile.'" rel="stylesheet" type="text/css" />';
            } elseif ($ext===self::JS_FILE) {
              $link = '<script src="'.$assetFile.'"></script>';
            } else {
              $link = $assetFile;
            }
            return $link;
        } else {
          throw new MazeoException("Unable to load the asset file \"{$assetFile}\"  from package \"{$package}\"");
        }
    }
    /**
     * [assignVendorFile return a give file complete path]
     * @param  [type] $path [the partial path of the file]
     * @return [type]       [description]
     */
    public function assignVendorFile($path)
    {
        return $this->host.$path;
    }
    /**
     * [slugify description]
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public static function slugify($text)
    {
      // replace non letter or digits by -
      $text = preg_replace('~[^\pL\d]+~u', '-', $text);

      // transliterate
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

      // remove unwanted characters
      $text = preg_replace('~[^-\w]+~', '', $text);

      // trim
      $text = trim($text, '-');

      // remove duplicate -
      $text = preg_replace('~-+~', '-', $text);

      // lowercase
      $text = strtolower($text);

      if (empty($text)) {
        return 'n-a';
      }

      return $text;
    }
}
