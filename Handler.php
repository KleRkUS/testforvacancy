<?php

require 'Connection.php';

class Handler 
{
  private $pattern;
  private $explodeDelimiterPattern;
  private $explodeWhiteSpacePattern;
  private $string;
  private $db;


  public function __construct()
  {

    $this->pattern = "/\{([^\{\}]*(?R)*[^\{\}]*)*\}/";
    $this->explodeDelimiterPattern = "/\|?([^\{\}\|]*(\{[^\{\}]*(?R)*[^\{\}]*\})*[^\{\}\|]*)\|?/";
    $this->explodeWhiteSpacePattern = "/\s?(\{[^\{\}]*(?R)*[^\{\}]*\})|([^\{\}]+)\s?/";
    $this->db = new Connection;

  }

  /**
   * Start
   * 
   * Handle sctipt start
   * 
   * @param string Origin string
   * 
   * @return string result of script work
  **/

  public function start($string)
  {

    $amount = $this->getAmountOfVariants($string);
    $this->setString($string);

    return $this->collectRandomStrings($amount);
    
  }

  /**
   * Set string
   * 
   * String property setter
   * 
   * @param string Origin string
   * 
   * @return 0 
  **/

  private function setString($string)
  {

    $this->string = $string;
    return 0;

  }

  /**
   * Collect Random String
   * 
   * Gets origin string and initializates all manipulations 
   * then collects it to database
   * 
   * @param amount int Amount of all variants of replaces in origin string
   * 
   * @return string Result of execution
  **/

  private function collectRandomStrings($amount)
  {

    if ($this->db->checkExistence($this->string)) {
      return "This string already exists";
    }

    $counter = 0;
    $resultArray = [];

    while ($counter < $amount) {
      $outArr = $this->getRandomValue($this->string);
      $patterns = [];

      foreach ($outArr as $out) {
        array_push($patterns, $this->pattern);
      }

      $newString = preg_replace($patterns, $outArr, $this->string, 1);

      $checker = array_search($newString, $resultArray);

      if (!$checker && $checker !== 0) {
        $counter += 1;
        array_push($resultArray, $newString);
      }
    }

    $res = $this->db->addArray($this->string, $resultArray);
    return "String added";

  }

  /**
   * Get Random Value
   * 
   * Get one random variation of string replaces
   * 
   * @param string Origin string
   * 
   * @return array Array of variants of replaces
  **/

  private function getRandomValue($string)
  {

    $pattern = $this->pattern;
    $outString = '';
    $result = [];

    $parts = $this->explodeByMainPattern($string);
    $parts = array_map(array($this, 'removeBrackets'), $parts);


    foreach ($parts as $part) {

      $subString = $this->getRandomSubstring($part);

      if (preg_match($pattern, $subString) && preg_match($pattern, $subString) == 1) {

        $subs = $this->explodeByWhiteSpacePattern($subString);
        foreach ($subs as $sub) {
          
          if (preg_match($pattern, $sub) && preg_match($pattern, $sub) == 1) {

            $recursiveArray = $this->getRandomValue($sub);

            foreach ($recursiveArray as $recursiveString) {
              $outString .= $recursiveString." ";
            }

          } else {
            $outString .= $sub." ";
          }
        }

        array_push($result, $outString);

      } else {
        array_push($result, $subString);
      }

    }

    return $result;

  }

  /**
   * Explode By Main Pattern
   * 
   * Explodes string by pattern to get substring that
   * contains variants in brackets {}
   * 
   * @param string String to explode
   * 
   * @return array Result array
  **/

  private function explodeByMainPattern($string) 
  {

    $out = [];
    preg_match_all($this->pattern, $string, $out);
    $out = $out[0];
    return $out;

  }

  /**
   * Remove Brackets
   * 
   * Removes brackets in the beggining and end of string
   * 
   * @param string
   * 
   * @return string
  **/

  private function removeBrackets($string)
  {
    return substr($string, 1, -1);
  }

  /**
   * Get Random Substring
   * 
   * Explode string of variants by delimiter and 
   * returns one of possible variants randomly
   * 
   * @param string String of variants
   * 
   * @return string
  **/

  private function getRandomSubstring($string)
  {

    $parts = $this->explodeByDelimiterPattern($string);

    $num = rand(0, count($parts) - 1);
    $subString = $parts[$num];

    return $subString;

  }

  /**
   * Explode By Delimiter Pattern
   * 
   * Explodes string by delimiter pattern where
   * delimiter is |
   * 
   * @param string
   * 
   * @return array Array of pure variants for replace
  **/

  private function explodeByDelimiterPattern($string)
  {

    $parts = [];

    preg_match_all($this->explodeDelimiterPattern, $string, $parts);
    array_pop($parts[1]);

    return $parts[1];

  }

  /**
   * Explode By White Space Pattern
   * 
   * Explodes string by white space pattern to
   * get "bracketed" and "other" parts of variant to get 
   * only one variant from "bracketed" part and concatenate
   * if with "other" where instance is deep
   * 
   * @param subString string 
   * 
   * @return array Array of 
  **/

  private function explodeByWhiteSpacePattern($subString)
  {

    $subs = [];
    preg_match_all($this->explodeWhiteSpacePattern, $subString, $subs);

    return $subs[0];

  }

  /**
   * Get amount of variants
   * 
   * Get amount of variants of all replaces in string
   * This function is little bit about probability theory
   * 
   * @param string Origin string
   * 
   * @return integer Amount of variants
  **/

  private function getAmountOfVariants($string)
  {

    $pattern = $this->pattern;
    $parts = $this->explodeByMainPattern($string);
    $PrimaryAmount = count($parts);
    $secondaryAmount = [];

    $parts = array_map(array($this, 'removeBrackets'), $parts);

    foreach ($parts as $part) {
      
      array_push($secondaryAmount, $this->getAmountOfSecondaryParts($part, 0));

    }

    return array_product($secondaryAmount);
  }

  /**
   * Get amount of secondary parts
   * 
   * In string we have primary parts which is first
   * instances and secondary which is "instances in instanceds"
   * To get amount of all instances we need to multiply
   * amount of secondary parts by amount of primary parts
   * 
   * @param string String which has secondary instances
   * 
   * @return integer
  **/

  private function getAmountOfSecondaryParts($string)
  {

    $pattern = $this->pattern;
    $counter;

    $parts = $this->explodeByDelimiterPattern($string);

    foreach ($parts as $subString) {

      if (preg_match($pattern, $subString) && preg_match($pattern, $subString) == 1) {


        $subParts = $this->explodeByWhiteSpacePattern($subString);
        foreach ($subParts as $subPart) {

          if (preg_match($pattern, $subPart) && preg_match($pattern, $subPart) == 1) {

            $subPart = $this->removeBrackets($subPart);
            $counter += $this->getAmountOfSecondaryParts($subPart);

          }

        }

      } else {

        $counter++;

      }

    }

    return $counter;

  }

}