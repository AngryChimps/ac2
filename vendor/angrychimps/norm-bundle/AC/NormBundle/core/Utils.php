<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 6/26/14
 * Time: 6:28 PM
 */

namespace AC\NormBundle\core;


class Utils {
    public static function field2property($fieldName) {
        $propertyName = '';
        $words = explode('_', $fieldName);
        foreach($words as $word) {
            if($propertyName === '') {
                $propertyName .= lcfirst($word);
            }
            else {
                $propertyName .= ucfirst($word);
            }
        }

        return $propertyName;
    }

    public static function table2class($tableName) {
        return ucfirst(self::field2property($tableName));
    }

    public static function class2table($classNameWithNamespace) {
        $parts = explode("\\", $classNameWithNamespace);
        $className = $parts[count($parts) - 1];
        if(strpos($className, 'Collection') === strlen($className) - 10) {
            $className = substr($className, 0, strlen($className) - 10);
        }
        return self::camel2TrainCase($className);
    }

    public static function array2quotedString($arr) {
        if(empty($arr)) {
            return null;
        }
        if(!is_array($arr)) {
            return $arr;
        }
        return "'" . implode("', '", $arr) . "'";
    }

    public static function camel2TrainCase($string) {
        $letters = str_split($string);
        $words = array();

        for($i=0; $i<count($letters); $i++) {
            //The first letter always starts a word even if not capitalized
            if($i===0) {
                $words[0] = ucfirst($letters[$i]);
            }
            elseif($letters[$i] === ucfirst($letters[$i])) {
                array_push($words, $letters[$i]);
            }
            else {
                $words[count($words) - 1] .= ucfirst($letters[$i]);
            }
        }

        return implode('_', $words);
    }

}