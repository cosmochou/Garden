<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

function __autoload($ClassName) {
   if (class_exists('HTMLPurifier_Bootstrap', FALSE) && HTMLPurifier_Bootstrap::autoload($ClassName))
      return true;

   if(!class_exists('Gdn_FileSystem', FALSE))
      return false;
      
   if(!class_exists('Gdn_LibraryMap', FALSE))
      return false;

   if(!class_exists('Gdn', FALSE))
      return false;
   
   if(substr($ClassName, 0, 4) === 'Gdn_')
      $LibraryFileName = 'class.' . strtolower(substr($ClassName, 4)) . '.php';
   else
      $LibraryFileName = 'class.' . strtolower($ClassName) . '.php';
   
   if(!is_null($ApplicationManager = Gdn::Factory('ApplicationManager')))
      $ApplicationWhiteList = Gdn::Factory('ApplicationManager')->EnabledApplicationFolders();
   else
      $ApplicationWhiteList = NULL;
   
   $LibraryPath = FALSE;

   // If this is a model, look in the models folder(s)
   if (strtolower(substr($ClassName, -5)) == 'model')
      $LibraryPath = Gdn_FileSystem::FindByMapping('library', PATH_APPLICATIONS, $ApplicationWhiteList, 'models' . DS . $LibraryFileName);

   if ($LibraryPath === FALSE)
      $LibraryPath = Gdn_FileSystem::FindByMapping(
         'library',
         PATH_LIBRARY,
         array(
            'core',
            'database',
            'vendors'. DS . 'phpmailer',
            'vendors' . DS . 'htmlpurifier'
         ),
         $LibraryFileName
      );

   // If it still hasn't been found, check for modules
   if ($LibraryPath === FALSE)
      $LibraryPath = Gdn_FileSystem::FindByMapping('library', PATH_APPLICATIONS, $ApplicationWhiteList, 'modules' . DS . $LibraryFileName);

   if ($LibraryPath !== FALSE)
      include_once($LibraryPath);
}

if (!function_exists('AddActivity')) {
   /**
    * A convenience function that allows adding to the activity table with a single line.
    */
   function AddActivity($ActivityUserID, $ActivityType, $Story = '', $RegardingUserID = '', $Route = '', $SendEmail = '') {
      $ActivityModel = new ActivityModel();
      return $ActivityModel->Add($ActivityUserID, $ActivityType, $Story, $RegardingUserID, '', $Route, $SendEmail);
   }
}

if (!function_exists('ArrayCombine')) {
   /**
    * PHP's array_combine has a limitation that doesn't allow array_combine to
    * work if either of the arrays are empty.
    */
   function ArrayCombine($Array1, $Array2) {
      if (!is_array($Array1))
         $Array1 = array();
         
      if (!is_array($Array2))
         $Array2 = array();
         
      if (count($Array1) > 0 && count($Array2) > 0)
         return array_combine($Array1, $Array2);
      elseif (count($Array1) == 0)
         return $Array2;
      else
         return $Array1;
   }
}
/*
 We now support PHP 5.2.0 - Which should make this declaration unnecessary.
if (!function_exists('array_fill_keys')) {
   function array_fill_keys($Keys, $Val) {
      return array_combine($Keys,array_fill(0,count($Keys),$Val));
   }
}
*/
if (!function_exists('ArrayHasValue')) {
   /**
    * Searches $Array (and all arrays it contains) for $Value.
    */ 
   function ArrayHasValue($Array, $Value) {
      if (in_array($Value, $Array)) {
         return TRUE;
      } else {
         foreach ($Array as $k => $v) {
            if (is_array($v))
               return ArrayHasValue($v, $Value);
         }
         return FALSE;
      }
   }
}

if (!function_exists('ArrayKeyExistsI')) {
   /**
    * Case-insensitive ArrayKeyExists search.
    */
   function ArrayKeyExistsI($Key, $Search) {
      if (is_array($Search)) {
         foreach ($Search as $k => $v) {
            if (strtolower($Key) == strtolower($k))
               return TRUE;
         }
      }
      return FALSE;
   }
}

if (!function_exists('ArrayInArray')) {
   /**
    * Searches Haystack array for items in Needle array. If FullMatch is TRUE,
    * all items in Needle must also be in Haystack. If FullMatch is FALSE, only
    * one-or-more items in Needle must be in Haystack.
    *
    * @param array $Needle The array containing items to match to Haystack.
    * @param array $Haystack The array to search in for Needle items.
    * @param bool $FullMatch Should all items in Needle be found in Haystack to return TRUE?
    */
   function ArrayInArray($Needle, $Haystack, $FullMatch = TRUE) {
      $Count = count($Needle);
      $Return = $FullMatch ? TRUE : FALSE;
      for ($i = 0; $i < $Count; ++$i) {
         if ($FullMatch === TRUE) {
            if (in_array($Needle[$i], $Haystack) === FALSE)
               $Return = FALSE;
         } else {
            if (in_array($Needle[$i], $Haystack) === TRUE) {
               $Return = TRUE;
               break;
            }
         }
      }
      return $Return;
   }
}

if (!function_exists('ArrayValue')) {
   /**
    * Returns the value associated with the $Needle key in the $Haystack
    * associative array or FALSE if not found. This is a CASE-SENSITIVE search.
    *
    * @param string The key to look for in the $Haystack associative array.
    * @param array The associative array in which to search for the $Needle key.
    * @param string The default value to return if the requested value is not found. Default is FALSE.
    */
   function ArrayValue($Needle, $Haystack, $Default = FALSE) {
      $Result = GetValue($Needle, $Haystack, $Default);
		return $Result;
   }
}

if (!function_exists('ArrayValueI')) {
   /**
    * Returns the value associated with the $Needle key in the $Haystack
    * associative array or FALSE if not found. This is a CASE-INSENSITIVE
    * search.
    *
    * @param string The key to look for in the $Haystack associative array.
    * @param array The associative array in which to search for the $Needle key.
    * @param string The default value to return if the requested value is not found. Default is FALSE.
    */
   function ArrayValueI($Needle, $Haystack, $Default = FALSE) {
      $Return = $Default;
      if (is_array($Haystack)) {
         foreach ($Haystack as $Key => $Value) {
            if (strtolower($Needle) == strtolower($Key)) {
               $Return = $Value;
               break;
            }
         }
      }
      return $Return;
   }
}

if (!function_exists('ArrayValuesToKeys')) {
   /** Takes an array's values and applies them to a new array as both the keys
    * and values.
    */
   function ArrayValuesToKeys($Array) {
      return array_combine(array_values($Array), $Array);
   }
}

if (!function_exists('Asset')) {
   /**
    * Takes the path to an asset (image, js file, css file, etc) and prepends the webroot.
    */
   function Asset($Destination = '', $WithDomain = FALSE) {
      $Destination = str_replace('\\', '/', $Destination);
      if (substr($Destination, 0, 7) == 'http://') {
         return $Destination;
      } else {
         $Parts = array(Gdn_Url::WebRoot($WithDomain), $Destination);
         if (!$WithDomain)
            array_unshift($Parts, '/');
            
         return CombinePaths($Parts, '/');
      }
   }
}

if (!function_exists('Attribute')) {
   /**
    * Takes an attribute (or array of attributes) and formats them in
    * attribute="value" format.
    */
   function Attribute($Name, $Value = '') {
      $Return = '';
      if (!is_array($Name)) {
         $Name = array($Name => $Value);
      }
      foreach ($Name as $Attribute => $Val) {
         if ($Val != '') {
            $Return .= ' '.$Attribute.'="'.$Val.'"';
         }
      }
      return $Return;
   }
}

if (!function_exists('C')) {
   /**
    * Retrieves a configuration setting.
    * @param string $Name The name of the configuration setting. Settings in different sections are seperated by a dot ('.')
    * @param mixed $Default The result to return if the configuration setting is not found.
    * @return mixed The configuration setting.
    * @see Gdn::Config()
    */
   function C($Name = FALSE, $Default = FALSE) {
      return Gdn::Config($Name, $Default);
   }
}

if (!function_exists('CalculateNumberOfPages')) {
   /**
    * Based on the total number of items and the number of items per page,
    * this function will calculate how many pages there are.
    * Returns the number of pages available
    */
   function CalculateNumberOfPages($ItemCount, $ItemsPerPage) {
      $TmpCount = ($ItemCount/$ItemsPerPage);
      $RoundedCount = intval($TmpCount);
      $PageCount = 0;
      if ($TmpCount > 1) {
         if ($TmpCount > $RoundedCount) {
            $PageCount = $RoundedCount + 1;
         } else {
            $PageCount = $RoundedCount;
         }
      } else {
         $PageCount = 1;
      }
      return $PageCount;
   }
}

if (!function_exists('CheckPermission')) {
   function CheckPermission($PermissionName) {
      $Result = Gdn::Session()->CheckPermission($PermissionName);
      return $Result;
   }
}

if (!function_exists('CheckRequirements')) {
   function CheckRequirements($ItemName, $RequiredItems, $EnabledItems, $RequiredItemTypeCode) {
      // 1. Make sure that $RequiredItems are present
      if (is_array($RequiredItems)) {
         foreach ($RequiredItems as $RequiredItemName => $RequiredVersion) {
            if (array_key_exists($RequiredItemName, $EnabledItems) === FALSE) {
               throw new Exception(
                  sprintf(
                     T('%1$s requires the %2$s %3$s version %4$s.'),
                     $ItemName,
                     $RequiredItemName,
                     $RequiredItemTypeCode,
                     $RequiredVersion
                  )
               );
            } else if (StringIsNullOrEmpty($RequiredVersion) === FALSE) {
                // If the item exists and is enabled, check the version
               $EnabledVersion = ArrayValue('Version', ArrayValue($RequiredItemName, $EnabledItems, array()), '');
               if ($EnabledVersion !== $RequiredVersion) {
                  // Check for version ranges (<, <=, >, >=)
                  $Matches = FALSE;
                  preg_match_all('/(>|>=|<|<=){1}([\d\.]+)/', $RequiredVersion, $Matches);
                  if (is_array($Matches) && count($Matches) == 3 && count($Matches[1]) > 0) {
                     // The matches array should contain a three parts:
                     /*
                      eg. The following $RequiredVersion string:
                        >1.33<=4.1
                     would result in:
                        Array (
                              [0] => Array
                                  (
                                      [0] => >1.33
                                      [1] => <=4.1
                                  )
                              [1] => Array
                                  (
                                      [0] => >
                                      [1] => <=
                                  )
                              [2] => Array
                                  (
                                      [0] => 1.33
                                      [1] => 4.1
                                  )
                          )
                     */

                     $Operators = $Matches[1];
                     $Versions = $Matches[2];
                     $Count = count($Operators);
                     for ($i = 0; $i < $Count; ++$i) {
                        $Operator = $Operators[$i];
                        $MatchVersion = $Versions[$i];
                        if (!version_compare($EnabledVersion, $MatchVersion, $Operator)) {
                           throw new Exception(
                              sprintf(
                                 T('%1$s requires the %2$s %3$s version %4$s %5$s'),
                                 $ItemName,
                                 $RequiredItemName,
                                 $RequiredItemTypeCode,
                                 $Operator,
                                 $MatchVersion
                              )
                           );
                        }
                     }
                  } else if ($RequiredVersion != '*' && $RequiredVersion != '') {
                     throw new Exception(
                        sprintf(
                           T('%1$s requires the %2$s %3$s version %4$s'),
                           $ItemName,
                           $RequiredItemName,
                           $RequiredItemTypeCode,
                           $RequiredVersion
                        )
                     );
                  }
               }
            }
         }
      }
   }
}

if (!function_exists('CombinePaths')) {
   // filesystem input/output functions that deal with loading libraries, application paths, etc.
   function CombinePaths($Paths, $Delimiter = DS) {
      if (is_array($Paths)) {
         $MungedPath = implode($Delimiter, $Paths);
         $MungedPath = str_replace(array($Delimiter.$Delimiter.$Delimiter, $Delimiter.$Delimiter), array($Delimiter, $Delimiter), $MungedPath);
         return str_replace(array('http:/', 'https:/'), array('http://', 'https://'), $MungedPath);
      } else {
         return $Paths;
      }
   }
}

if (!function_exists('ConcatSep')) {
   /** Concatenate a string to another string with a seperator.
    *
    * @param string $Sep The seperator string to use between the concatenated strings.
    * @param string $Str1 The first string in the concatenation chain.
    * @param mixed $Str2 The second string in the concatenation chain.
    *  - This parameter can be an array in which case all of its elements will be concatenated.
    *  - If this parameter is a string then the function will look for more arguments to concatenate.
    * @return string
    */
   function ConcatSep($Sep, $Str1, $Str2) {
      if(is_array($Str2)) {
         $Strings = array_merge((array)$Str1, $Str2);
      } else {
         $Strings = func_get_args();
         array_shift($Strings);
      }

      $Result = '';
      foreach($Strings as $String) {
         if(!$String)
            continue;

         if($Result)
            $Result .= $Sep;
         $Result .= $String;
      }
      return $Result;
   }
}

if (!function_exists('ConsolidateArrayValuesByKey')) {
   /**
    * Takes an array of associative arrays (ie. a dataset array), a $Key, and
    * merges all of the values for that key into a single array, returning it.
    */
   function ConsolidateArrayValuesByKey($Array, $Key, $ValueKey = '', $DefaultValue = NULL) {
      $Return = array();
      foreach ($Array as $Index => $AssociativeArray) {
			if(is_object($AssociativeArray)) {
				if($ValueKey === '') {
					$Return[] = $AssociativeArray->$Key;
				} elseif(property_exists($AssociativeArray, $ValueKey)) {
					$Return[$AssociativeArray[$Key]] = $AssociativeArray->$ValueKey;
				} else {
					$Return[$AssociativeArray->$Key] = $DefaultValue;
				}
			} elseif (array_key_exists($Key, $AssociativeArray)) {
            if($ValueKey === '') {
               $Return[] = $AssociativeArray[$Key];
            } elseif (array_key_exists($ValueKey, $AssociativeArray)) {
               $Return[$AssociativeArray[$Key]] = $AssociativeArray[$ValueKey];
            } else {
               $Return[$AssociativeArray[$Key]] = $DefaultValue;
            }
         }
      }
      return $Return;
   }
}

/*
 We now support PHP 5.2.0 - Which should make this declaration unnecessary.
if (!function_exists('filter_input')) {
   if (!defined('INPUT_GET')) define('INPUT_GET', 'INPUT_GET');
   if (!defined('INPUT_POST')) define('INPUT_POST', 'INPUT_POST');
   if (!defined('FILTER_SANITIZE_STRING')) define('FILTER_SANITIZE_STRING', 'FILTER_SANITIZE_STRING');
   if (!defined('FILTER_REQUIRE_ARRAY')) define('FILTER_REQUIRE_ARRAY', 'FILTER_REQUIRE_ARRAY');
   function filter_input($InputType, $FieldName, $Filter = '', $Options = '') {
      $Collection = $InputType == INPUT_GET ? $_GET : $_POST;
      $Value = ArrayValue($FieldName, $Collection, '');
      if (get_magic_quotes_gpc()) {
         if (is_array($Value)) {
            $Count = count($Value);
            for ($i = 0; $i < $Count; ++$i) {
               $Value[$i] = stripslashes($Value[$i]);
            }
         } else {
            $Value = stripslashes($Value);
         }
      }
      return $Value;     
   }
}
*/

if (!function_exists('ForceBool')) {
   function ForceBool($Value, $DefaultValue = FALSE, $True = TRUE, $False = FALSE) {
      if (is_bool($Value)) {
         return $Value ? $True : $False;
      } else if (is_numeric($Value)) {
         return $Value == 0 ? $False : $True;
      } else if (is_string($Value)) {
         return strtolower($Value) == 'true' ? $True : $False;
      } else {
         return $DefaultValue;
      }
   }
}

if (!function_exists('getallheaders')) {
   /**
    * If PHP isn't running as an apache module, getallheaders doesn't exist in
    * some systems.
    * Ref: http://github.com/lussumo/Garden/issues/closed#issue/3/comment/19938
    */
   function getallheaders() {
      foreach($_SERVER as $name => $value)
          if(substr($name, 0, 5) == 'HTTP_')
              $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
      return $headers;
   }
}

if (!function_exists('ForceSSL')) {
   /**
    * Checks the current url for SSL and redirects to SSL version if not
    * currently on it. Call at the beginning of any method you want forced to
    * be in SSL. Garden.AllowSSL must be TRUE in order for this function to
    * work.
    */
   function ForceSSL() {
      if (C('Garden.AllowSSL')) {
         if (Gdn::Request()->Scheme() != 'https')
            Redirect(Gdn::Request()->Url('', TRUE, TRUE));
      }
   }
}

if (!function_exists('GetConnectionString')) {
   function GetConnectionString($DatabaseName, $HostName = 'localhost', $ServerType = 'mysql') {
      $HostName = explode(':', $HostName);
      $Port = count($HostName) == 2 ? $HostName[1] : '';
      $HostName = $HostName[0];
      $String = $ServerType.':host='.$HostName;
      if ($Port != '')
         $String .= ';port='.$Port;
      return $String .= ';dbname='.$DatabaseName;
   }
}

if (!function_exists('GetIncomingValue')) {
   /**
    * Grabs $FieldName from either the GET or POST collections (whichever one it
    * is present in. Checks $_POST first).
    */
   function GetIncomingValue($FieldName, $Default = FALSE) {
      if (array_key_exists($FieldName, $_POST) === TRUE) {
         $Result = filter_input(INPUT_POST, $FieldName, FILTER_SANITIZE_STRING); //FILTER_REQUIRE_ARRAY);
      } else if (array_key_exists($FieldName, $_GET) === TRUE) {
         $Result = filter_input(INPUT_GET, $FieldName, FILTER_SANITIZE_STRING); //, FILTER_REQUIRE_ARRAY);
      } else {
         $Result = $Default;
      }
      return $Result;
   }
}

if (!function_exists('GetMentions')) {
   function GetMentions($String) {
      $Mentions = array();
      
      // This one grabs mentions that start at the beginning of $String
      preg_match(
         '/^(@([\d\w_-]{1,20}))/si',
         $String,
         $Matches
      );
      if (count($Matches) == 3)
         $Mentions[] = $Matches[2];
      
      // This one handles all other mentions
      preg_match_all(
         '/([\s]+)(@([\d\w_-]{1,20}))/si',
         $String,
         $Matches
      );
      if (count($Matches) == 4) {
         for ($i = 0; $i < count($Matches[3]); ++$i) {
            $Mentions[] = $Matches[3][$i];
         }
      }
      return array_unique($Mentions);
   }
}

if (!function_exists('GetPostValue')) {
   /**
    * Return the value for $FieldName from the $_POST collection.
    */
   function GetPostValue($FieldName, $Default = FALSE) {
      return array_key_exists($FieldName, $_POST) ? $_POST[$FieldName] : $Default;
   }
}

if (!function_exists('GetValue')) {
	/**
	 * Return the value from an associative array or an object.
	 *
	 * @param string $Key The key or property name of the value.
	 * @param mixed $Collection The array or object to search.
	 * @param mixed $Default The value to return if the key does not exist.
    * @param bool $Remove Whether or not to remove the item from the collection.
	 * @return mixed The value from the array or object.
	 */
	function GetValue($Key, &$Collection, $Default = FALSE, $Remove = FALSE) {
		$Result = $Default;
		if(is_array($Collection) && array_key_exists($Key, $Collection)) {
			$Result = $Collection[$Key];
         if($Remove)
            unset($Collection[$Key]);
		} elseif(is_object($Collection) && property_exists($Collection, $Key)) {
			$Result = $Collection->$Key;
         if($Remove)
            unset($Collection->$Key);
      }
			
      return $Result;
	}
}

if (!function_exists('GetValueR')) {
   /**
	 * Return the value from an associative array or an object.
    * This function differs from GetValue() in that $Key can be a string consisting of dot notation that will be used to recursivly traverse the collection.
	 *
	 * @param string $Key The key or property name of the value.
	 * @param mixed $Collection The array or object to search.
	 * @param mixed $Default The value to return if the key does not exist.
	 * @return mixed The value from the array or object.
	 */
   function GetValueR($Key, &$Collection, $Default = FALSE) {
      $Path = explode('.', $Key);

      $Value = $Collection;
      for($i = 0; $i < count($Path); ++$i) {
         $SubKey = $Path[$i];

         if(is_array($Value) && isset($Value[$SubKey])) {
            $Value = $Value[$SubKey];
         } elseif(is_object($Value) && isset($Value->$SubKey)) {
            $Value = $Value->$SubKey;
         } else {
            return $Default;
         }
      }
      return $Value;
   }
}

if (!function_exists('InArrayI')) {
   /**
    * Case-insensitive version of php's native in_array function.
    */
   function InArrayI($Needle, $Haystack) {
      $Needle = strtolower($Needle);
      foreach ($Haystack as $Item) {
         if (strtolower($Item) == $Needle)
            return TRUE;
      }
      return FALSE;
   }
}

if (!function_exists('IsTimestamp')) {
   function IsTimestamp($Stamp) {
      return checkdate(
         @date("m", $Stamp),
         @date("d", $Stamp),
         @date("Y", $Stamp)
      );
   }
}

if (!function_exists('IsWritable')) {
   /**
    * PHP's native is_writable() function fails to correctly determine write
    * capabilities on some systems (Windows), and in our tests it returned TRUE
    * despite not being able to create subfolders within the folder being
    * checked. Our version truly verifies permissions by performing file-write
    * tests.
    */
   function IsWritable($Path) {
      if ($Path{strlen($Path) - 1} == DS) {
         // Recursively return a temporary file path
         return IsWritable($Path . uniqid(mt_rand()) . '.tmp');
      } elseif (is_dir($Path)) {
         return IsWritable($Path . '/' . uniqid(mt_rand()) . '.tmp');
      }
      // Check tmp file for read/write capabilities
      $KeepPath = file_exists($Path);
      $File = @fopen($Path, 'a');
      if ($File === FALSE)
         return FALSE;
      
      fclose($File);
      
      if (!$KeepPath)
         unlink($Path);
      
      return TRUE;
   }
}

if (!function_exists('MergeArrays')) {
   /**
    * Merge two associative arrays into a single array.
    *
    * @param array The "dominant" array, who's values will be chosen over those of the subservient.
    * @param array The "subservient" array, who's values will be disregarded over those of the dominant.
    */
   function MergeArrays(&$Dominant, $Subservient) {
      foreach ($Subservient as $Key => $Value) {
         if (!array_key_exists($Key, $Dominant)) {
            // Add the key from the subservient array if it doesn't exist in the
            // dominant array.
            $Dominant[$Key] = $Value;
         } else {
            // If the key already exists in the dominant array, only continue if
            // both values are also arrays - because we don't want to overwrite
            // values in the dominant array with ones from the subservient array.
            if (is_array($Dominant[$Key]) && is_array($Value)) {
               $Dominant[$Key] = MergeArrays($Dominant[$Key], $Value);
            }
         }
      }
      return $Dominant;
   }
}

if (!function_exists('Now')) {
   function Now() {
      list($usec, $sec) = explode(" ", microtime());
      return ((float)$usec + (float)$sec);
   }
}

if (!function_exists('parse_ini_string')) {
   /**
    * parse_ini_string not supported until PHP 5.3.0, and we currently support
    * PHP 5.2.0.
    */
   function parse_ini_string ($Ini) {
      $Lines = split("\n", $Ini);
      $Result = array();
      foreach($Lines as $Line) {
         $Parts = split('=', $Line, 2);
         if(count($Parts) == 1) {
            $Result[trim($Parts[0])] = '';
         } elseif(count($Parts) >= 2) {
            $Result[trim($Parts[0])] = trim($Parts[1]);
         }
      }
      return $Result;
   }
}

if (!function_exists('PrefixString')) {
   /**
    * Takes a string, and prefixes it with $Prefix unless it is already prefixed that way.
    *
    * @param string $Prefix The prefix to use.
    * @param string $String The string to be prefixed.
    */
   function PrefixString($Prefix, $String) {
      if (substr($String, 0, strlen($Prefix)) != $Prefix) {
         $String = $Prefix . $String;
      }
      return $String;
   }
}

if (!function_exists('ProxyHead')) {
   
   function ProxyHead($Url, $Headers=array(), $Timeout = FALSE) {
		if(!$Timeout)
			$Timeout = C('Garden.SocketTimeout', 1.0);

      $UrlParts = parse_url($Url);
      $Scheme = GetValue('scheme', $UrlParts, 'http');
      $Host = GetValue('host', $UrlParts, '');
      $Port = GetValue('port', $UrlParts, '80');
      $Path = GetValue('path', $UrlParts, '');
      $Query = GetValue('query', $UrlParts, '');
      
      // Get the cookie.
      $Cookie = array('Cookie'      => '');
      foreach($_COOKIE as $Key => $Value) {
         if(strncasecmp($Key, 'XDEBUG', 6) == 0)
            continue;
         
         if(strlen($Cookie['Cookie']) > 0)
            $Cookie['Cookie'] .= '; ';
            
         $Cookie['Cookie'] .= $Key.'='.urlencode($Value);
      }
      
      $Response = '';
      if (function_exists('curl_init')) {
         $Url = $Scheme.'://'.$Host.$Path;
         $Handler = curl_init();
			curl_setopt($Handler, CURLOPT_TIMEOUT, $Timeout);
         curl_setopt($Handler, CURLOPT_URL, $Url);
         curl_setopt($Handler, CURLOPT_PORT, $Port);
         curl_setopt($Handler, CURLOPT_HEADER, 1);
         curl_setopt($Handler, CURLOPT_NOBODY, 1);
         curl_setopt($Handler, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($Handler, CURLOPT_HTTPHEADER, $Headers);
         
         if (strlen($Cookie['Cookie']))
            curl_setopt($Handler, CURLOPT_COOKIE, $Cookie['Cookie']);
            
         if ($Query != '') {
            curl_setopt($Handler, CURLOPT_POST, 1);
            curl_setopt($Handler, CURLOPT_POSTFIELDS, $Query);
         }
         $Response = curl_exec($Handler);
         if ($Response == FALSE)
            $Response = curl_error($Handler);
            
         curl_close($Handler);
      } else if (function_exists('fsockopen')) {
         $Referer = Gdn::Request()->WebRoot();
      
         // Make the request
         $Pointer = @fsockopen($Host, $Port, $ErrorNumber, $Error, $Timeout);
         if (!$Pointer)
            throw new Exception(sprintf(T('Encountered an error while making a request to the remote server (%1$s): [%2$s] %3$s'), $Url, $ErrorNumber, $Error));
         
         $Request = "HEAD $Path?$Query HTTP/1.1\r\n";
         
         $Header = array(
            'Host'            => $Host,
            'User-Agent'      => 'Vanilla/2.0',
            'Accept'          => '*/*',
            'Accept-Charset'  => 'utf-8',
            'Referer'         => $Referer,
            'Connection'      => 'close'
         );
         
         if (strlen($Cookie['Cookie']))
            $Header = array_merge($Header, $Cookie);
            
         $Header = array_merge($Header, $Headers);
         
         $HeaderString = "";
         foreach ($Header as $HeaderName => $HeaderValue) {
            $HeaderString .= "{$HeaderName}: {$HeaderValue}\r\n";
         }
         $HeaderString .= "\r\n";
                  
         // Send the headers and get the response
         fputs($Pointer, $Request);
         fputs($Pointer, $HeaderString);
         while ($Line = fread($Pointer, 4096)) {
            $Response .= $Line;
         }
         @fclose($Pointer);
         $Response = trim($Response);

      } else {
         throw new Exception(T('Encountered an error while making a request to the remote server: Your PHP configuration does not allow curl or fsock requests.'));
      }
      
      $ResponseLines = explode("\n",trim($Response));
      $Status = array_shift($ResponseLines);
      $Response = array();
      $Response['HTTP'] = trim($Status);
      
      /* get the numeric statuc code. 
       * - trim off excess edge whitespace, 
       * - split on spaces, 
       * - get the 2nd element (as a single element array), 
       * - pop the first (only) element off it... 
       * - return that.
       */
      $Response['StatusCode'] = array_pop(array_slice(explode(' ',trim($Status)),1,1));
      foreach ($ResponseLines as $Line) {
         $Line = explode(':',trim($Line));
         $Response[array_shift($Line)] = implode(':',$Line);
      }
      
      return $Response;
   }

}

if (!function_exists('ProxyRequest')) {
   /**
    * Uses curl or fsock to make a request to a remote server. Returns the
    * response.
    *
    * @param string $Url The full url to the page being requested (including http://)
    */
   function ProxyRequest($Url, $Timeout = FALSE) {
		if(!$Timeout)
			$Timeout = C('Garden.SocketTimeout', 1.0);

      $UrlParts = parse_url($Url);
      $Scheme = GetValue('scheme', $UrlParts, 'http');
      $Host = GetValue('host', $UrlParts, '');
      $Port = GetValue('port', $UrlParts, '80');
      $Path = GetValue('path', $UrlParts, '');
      $Query = GetValue('query', $UrlParts, '');
      // Get the cookie.
      $Cookie = '';
      foreach($_COOKIE as $Key => $Value) {
         if(strncasecmp($Key, 'XDEBUG', 6) == 0)
            continue;
         
         if(strlen($Cookie) > 0)
            $Cookie .= '; ';
            
         $Cookie .= $Key.'='.urlencode($Value);
      }

      $Response = '';
      if (function_exists('curl_init')) {
         
         $Url = $Scheme.'://'.$Host.$Path;
         $Handler = curl_init();
         curl_setopt($Handler, CURLOPT_URL, $Url);
         curl_setopt($Handler, CURLOPT_PORT, $Port);
         curl_setopt($Handler, CURLOPT_HEADER, 0);
         curl_setopt($Handler, CURLOPT_RETURNTRANSFER, 1);
         if ($Cookie != '')
            curl_setopt($Handler, CURLOPT_COOKIE, $Cookie);
            
         if ($Query != '') {
            curl_setopt($Handler, CURLOPT_POST, 1);
            curl_setopt($Handler, CURLOPT_POSTFIELDS, $Query);
         }
         $Response = curl_exec($Handler);
         if ($Response == FALSE)
            $Response = curl_error($Handler);
            
         curl_close($Handler);
      } else if (function_exists('fsockopen')) {
         $Referer = Gdn_Url::WebRoot(TRUE);
      
         // Make the request
         $Pointer = @fsockopen($Host, $Port, $ErrorNumber, $Error);
         if (!$Pointer)
            throw new Exception(sprintf(T('Encountered an error while making a request to the remote server (%1$s): [%2$s] %3$s'), $Url, $ErrorNumber, $Error));
   
         if(strlen($Cookie) > 0)
            $Cookie = "Cookie: $Cookie\r\n";
         
         $Header = "GET $Path?$Query HTTP/1.1\r\n"
            ."Host: $Host\r\n"
            // If you've got basic authentication enabled for the app, you're going to need to explicitly define the user/pass for this fsock call
            // "Authorization: Basic ". base64_encode ("username:password")."\r\n" . 
            ."User-Agent: Vanilla/2.0\r\n"
            ."Accept: */*\r\n"
            ."Accept-Charset: utf-8;\r\n"
            ."Referer: $Referer\r\n"
            ."Connection: close\r\n";
            
         if ($Cookie != '')
            $Header .= $Cookie;
         
         $Header .= "\r\n";
         
         // Send the headers and get the response
         fputs($Pointer, $Header);
         while ($Line = fread($Pointer, 4096)) {
            $Response .= $Line;
         }
         @fclose($Pointer);
         $Response = trim(substr($Response, strpos($Response, "\r\n\r\n") + 4));
         return $Response;
      } else {
         throw new Exception(T('Encountered an error while making a request to the remote server: Your PHP configuration does not allow curl or fsock requests.'));
      }
      return $Response;
   }
}

if (!function_exists('RandomString')) {
   function RandomString($Length, $Characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
      $CharLen = strlen($Characters) - 1;
      $String = '' ;
      for ($i = 0; $i < $Length; ++$i) {
        $Offset = rand() % $CharLen;
        $String .= substr($Characters, $Offset, 1);
      }
      return $String;
   }
}

if (!function_exists('Redirect')) {
   function Redirect($Destination = FALSE, $StatusCode = NULL) {
      if (!$Destination)
         $Destination = Url('');
      // Close any db connections before exit
      $Database = Gdn::Database();
      $Database->CloseConnection();
      // Clear out any previously sent content
      @ob_end_clean();
      
      // assign status code
      $SendCode = (is_null($StatusCode)) ? 302 : $StatusCode;
      // re-assign the location header
      header("location: ".Url($Destination), TRUE, $SendCode);
      // Exit
      exit();
   }
}

if (!function_exists('RemoteIP')) {
   function RemoteIP() {
      return GetValue('REMOTE_ADDR', $_SERVER, 'undefined');
   }
}

if (!function_exists('RemoveFromConfig')) {
   function RemoveFromConfig($Name) {
      $Config = Gdn::Factory(Gdn::AliasConfig);
      $Path = PATH_CONF . DS . 'config.php';
      $Config->Load($Path, 'Save');
      if (!is_array($Name))
         $Name = array($Name);
      
      foreach ($Name as $k) {
         $Config->Remove($k);
      }
      $Result = $Config->Save($Path);
      if ($Result)
         $Config->Load($Path, 'Use');
      return $Result;
   }
}

// Functions relating to data/variable types and type casting
if (!function_exists('RemoveKeyFromArray')) {
   function RemoveKeyFromArray($Array, $Key) {
      if (!is_array($Key))
         $Key = array($Key);

      $Count = count($Key);
      for ($i = 0; $i < $Count; $i++) {
         $KeyIndex = array_keys(array_keys($Array), $Key[$i]);
         if (count($KeyIndex) > 0) array_splice($Array, $KeyIndex[0], 1);
      }
      return $Array;
   }
}

if (!function_exists('RemoveQuoteSlashes')) {
 	function RemoveQuoteSlashes($String) {
		return str_replace("\\\"", '"', $String);
	}
}

if (!function_exists('SafeGlob')) {
   function SafeGlob($Pattern, $Flags = 0) {
      $Return = glob($Pattern, $Flags);
      if (!is_array($Return))
         $Return = array();
         
      return $Return;
   }
}

if (!function_exists('SafeParseStr')) {
   function SafeParseStr($Str, &$Output, $Original = NULL) {
      $Exploded = explode('&',$Str);
      $Output = array();
      if (is_array($Original)) {
         $FirstValue = reset($Original);
         $FirstKey = key($Original);
         unset($Original[$FirstKey]);
      }
      foreach ($Exploded as $Parameter) {
         $Parts = explode('=', $Parameter);
         $Key = $Parts[0];
         $Value = count($Parts) > 1 ? $Parts[1] : '';
         
         if (!is_null($Original)) {
            $Output[$Key] = $FirstValue;
            $Output = array_merge($Output, $Original);
            break;
         }
         
         $Output[$Key] = $Value;
      }
   }
}

if (!function_exists('SaveToConfig')) {
   function SaveToConfig($Name, $Value = '') {
      $Config = Gdn::Factory(Gdn::AliasConfig);
      $Path = PATH_CONF . DS . 'config.php';
      $Config->Load($Path, 'Save');
      if (!is_array($Name))
         $Name = array($Name => $Value);
      
      foreach ($Name as $k => $v) {
         $Config->Set($k, $v);
      }
      return $Config->Save($Path);
   }
}

if (!function_exists('SliceString')) {
   function SliceString($String, $Length, $Suffix = '…') {
      if (function_exists('mb_strimwidth')) {
      	static $Charset;
      	if(is_null($Charset)) $Charset = Gdn::Config('Garden.Charset', 'utf-8');
      	return mb_strimwidth($String, 0, $Length, $Suffix, $Charset);
      } else {
         $Trim = trim($String, 0, $Length);
         return $Trim . ((strlen($Trim) != strlen($String)) ? $Suffix: ''); 
      }
   }
}

if (!function_exists('StringIsNullOrEmpty')) {
   function StringIsNullOrEmpty($String) {
      return is_null($String) === TRUE || (is_string($String) && trim($String) == '');
   }
}


if (!function_exists('SetValue')) {
	/**
	 * Set the value on an object/array.
	 *
	 * @param string $Needle The key or property name of the value.
	 * @param mixed $Haystack The array or object to set.
	 * @param mixed $Value The value to set.
	 */
	function SetValue($Key, &$Collection, $Value) {
		if(is_array($Collection))
			$Collection[$Key] = $Value;
		elseif(is_object($Collection))
			$Collection->$Key = $Value;
	}
}


if (!function_exists('T')) {
   /**
	 * Translates a code into the selected locale's definition.
	 *
	 * @param string $Code The code related to the language-specific definition.
    *   Codes thst begin with an '@' symbol are treated as literals and not translated.
	 * @param string $Default The default value to be displayed if the translation code is not found.
	 * @return string The translated string or $Code if there is no value in $Default.
	 * @see Gdn::Translate()
	 */
   function T($Code, $Default = '') {
      return Gdn::Translate($Code, $Default);
   }
}

if (!function_exists('TouchValue')) {
	/**
	 * Set the value on an object/array if it doesn't already exist.
	 *
	 * @param string $Key The key or property name of the value.
	 * @param mixed $Collection The array or object to set.
	 * @param mixed $Default The value to set.
	 */
	function TouchValue($Key, &$Collection, $Default) {
		if(is_array($Collection) && !array_key_exists($Key, $Collection))
			$Collection[$Key] = $Default;
		elseif(is_object($Collection) && !property_exists($Collection, $Key))
			$Collection->$Key = $Default;
	}
}

if (!function_exists('Translate')) {
   /**
	 * Translates a code into the selected locale's definition.
	 *
	 * @param string $Code The code related to the language-specific definition.
    *   Codes thst begin with an '@' symbol are treated as literals and not translated.
	 * @param string $Default The default value to be displayed if the translation code is not found.
	 * @return string The translated string or $Code if there is no value in $Default.
	 * @deprecated
	 * @see Gdn::Translate()
	 */
   function Translate($Code, $Default = '') {
      $ErrorCode = defined('E_USER_DEPRECATED') ? E_USER_DEPRECATED : E_USER_WARNING;
      trigger_error('Translate() is deprecated. Use T() instead.', $ErrorCode);
      return Gdn::Translate($Code, $Default);
   }
}

if (!function_exists('TrueStripSlashes')) {
   if(get_magic_quotes_gpc()) {
      function TrueStripSlashes($String) {
         return stripslashes($String);
      }
   } else {
      function TrueStripSlashes($String) {
         return $String;
      }
   }
}

// Takes a route and prepends the web root (expects "/controller/action/params" as $Destination)
if (!function_exists('Url')) {   
   function Url($Path = '', $WithDomain = FALSE, $RemoveSyndication = FALSE) {
      $Result = Gdn::Request()->Url($Path, $WithDomain);
      return $Result;

      // Cache the rewrite urls config setting in this object.
      static $RewriteUrls = NULL;
      if(is_null($RewriteUrls)) $RewriteUrls = ForceBool(Gdn::Config('Garden.RewriteUrls', FALSE));
      
      $Prefix = substr($Path, 0, 7);
      if (in_array($Prefix, array('http://', 'https:/'))) {
         return $Path;
      }
      if ($Path == '#' || $Path == '') {
         $Path = Gdn_Url::Request(FALSE, FALSE, $RemoveSyndication).$Path;
      }

      $Paths = array();
      if (!$WithDomain)
         $Paths[] = '/';

      $Paths[] = Gdn_Url::WebRoot($WithDomain);
      if (!$RewriteUrls)
         $Paths[] = 'index.php';

      $Paths[] = $Path;
      return CombinePaths($Paths, '/');
   }
}

if( !function_exists('parse_ini_string') ){
   function parse_ini_string( $string ) {
      $array = Array();
      $lines = explode("\n", $string );
   
      foreach( $lines as $line ) {
         $statement = preg_match("/^(?!;)(?P<key>[\w+\.\-]+?)\s*=\s*(?P<value>.+?)\s*$/", $line, $match );
   
         if( $statement ) {
            $key    = $match[ 'key' ];
            $value    = $match[ 'value' ];
   
            # Remove quote
            if( preg_match( "/^\".*\"$/", $value ) || preg_match( "/^'.*'$/", $value ) ) {
               $value = mb_substr( $value, 1, mb_strlen( $value ) - 2 );
            }
   
            $array[ $key ] = $value;
         }
      }
      return $array;
   }
}