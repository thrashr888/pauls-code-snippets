<?php
/**
 * Utility class.
 *
 * @package    Rescue
 * @author     David Dobbs
 * @version    SVN: $Id: Utils.class.php $
 */

class dUtils
{

  // utils::preThis
  // --------------
  // @author: barce
  // @params: an object
  // @returns: 1 // prints out the object in pre_tags
  // usage: utils::preThis($object);
  public static function preThis($object) {
    echo "<pre>\n";
    echo print_r($object);
    echo "</pre>\n";
    return 1;
  }

  public static function logThis ($file, $info) {
    // the ob_* functions are for php4 but this works for php5, too
    $fp = fopen("$file", 'a');
    fwrite($fp, '[' . date('d.m.Y H:i:s') . '] RECEIVED INFO' . "\n");
    fwrite($fp, '[' . date('d.m.Y H:i:s') . '] ' . $info. "\n");
    fclose($fp);
  }

  public static function exceptionToString($e){
    return $e->getFile()." on line no. ".$e->getLine()." ".$e->getMessage()."\n\n".$e->getTraceAsString();
  }

  public static function logException($e,$error=sfLogger::ERR){
    sfContext::getInstance()->getLogger()->log(dUtils::exceptionToString($e),$error);
  }

  public static function rsync($from_path, $to_path, $config_string='app_images_rsync'){
    $cmd = sfConfig::get($config_string);
    $cmd = "$cmd $from_path $to_path";
    exec($cmd, $output, $return);

    if($return == 0){
      return true;
    }else{
      sfContext::getInstance()->getLogger()->info('rsync attempt failed: '.$cmd);
      sfContext::getInstance()->getLogger()->info('rsync returned error code ('.$return.')');
      sfContext::getInstance()->getLogger()->info(debug($output,0,1));
      return false;
    }
  }

  /**
   * Takes a phone # string and formats it to US/EN format.
   * Needs to be 11, 10, or 7 chars long
   *
   * @param string $phone
   * @param boolean $convert
   * @param boolean $trim
   * @return formatted string
   */
  public static function formatPhone($phone = '', $convert = false, $trim = true){
    // If we have not entered a phone number just return empty
    if (empty($phone)) {
      return '';
    }

    // Strip out any extra characters that we do not need only keep letters and numbers
    $phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);

    // Do we want to convert phone numbers with letters to their number equivalent?
    // Samples are: 1-800-TERMINIX, 1-800-FLOWERS, 1-800-Petmeds
    if ($convert == true) {
      $replace = array(
	    			 '2'=>array('a','b','c'),
					 '3'=>array('d','e','f'),
				     '4'=>array('g','h','i'),
					 '5'=>array('j','k','l'),
	                 '6'=>array('m','n','o'),
					 '7'=>array('p','q','r','s'),
					 '8'=>array('t','u','v'),
	    			 '9'=>array('w','x','y','z'));

      // Replace each letter with a number
      // Notice this is case insensitive with the str_ireplace instead of str_replace
      foreach($replace as $digit=>$letters) {
        $phone = str_ireplace($letters, $digit, $phone);
      }
    }

    // If we have a number longer than 11 digits cut the string down to only 11
    // This is also only ran if we want to limit only to 11 characters
    if ($trim == true && strlen($phone)>11) {
      $phone = substr($phone, 0, 11);
    }

    // Perform phone number formatting here
    if (strlen($phone) == 7) {
      return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
    } elseif (strlen($phone) == 10) {
      return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "($1) $2-$3", $phone);
    } elseif (strlen($phone) == 11) {
      return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1 ($2) $3-$4", $phone);
    }

    // Return original phone if not 7, 10 or 11 digits long
    return $phone;
  }

  public static function generatePassword($length=9, $strength=0) {
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength & 1) {
      $consonants .= 'BDGHJLMNPQRSTVWXZ';
    }
    if ($strength & 2) {
      $vowels .= "AEUY";
    }
    if ($strength & 4) {
      $consonants .= '23456789';
    }
    if ($strength & 8) {
      $consonants .= '@#$%';
    }

    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++) {
      if ($alt == 1) {
        $password .= $consonants[(rand() % strlen($consonants))];
        $alt = 0;
      } else {
        $password .= $vowels[(rand() % strlen($vowels))];
        $alt = 1;
      }
    }
    return $password;
  }

  /**
   * Function: getAge
   * Arguments: $yearFrom, $monFrom, $dayFrom
   * Comments: Given birth date, getAge outputs the age of an individual if he/she was born on $myDate1
   */
  public static function getAge($yearFrom, $monFrom, $dayFrom){
    // Initial delcarations for clarity.
    //list($monFrom, $dayFrom, $yearFrom) = $myDate1;
    //list($monTo, $dayTo, $yearTo) = $myDate2;
    $monTo = date("n");
    $dayTo = date("j");
    $yearTo = date("Y");

    // Is $dateFrom before $dateTo?
    if($yearFrom > $yearTo || ($yearFrom == $yearTo && ($monFrom > $monTo || ($monFrom == $monTo && $dayFrom > $dayTo))))
    {
      // Swap the dates.
      list($myDate1, $myDate2) = array($myDate2, $myDate1);
    }

    // $myTime holds information about months, days and years respectively.
    $myTime = array(0, 0, 0);

    // Set the number of years to the difference between $yearTo and $yearFrom.
    $myTime[2] = $yearTo - $yearFrom;

    // Subtract a year if necessary.
    if($monTo < $monFrom)
    {
      $myTime[2] -= 1;
    }
    else
    {
      if($monTo == $monFrom && $dayTo < $dayFrom)
      {
        $myTime[2] -= 1;

      }
    }

    // Set the number of months to the difference between $monTo and $monFrom.
    $myTime[0] = $monTo - $monFrom;

    // Subtract a month if necessary.
    if($dayTo < $dayFrom)
    {
      $myTime[0] -= 1;

      // Add the leftover days between $dayFrom and $dayTo.
      $myTime[1] = dUtils::getDaysInMonth(array($monFrom, 1, $yearTo)) - $dayFrom + $dayTo;

    }
    // If the value of dayFrom comes before the value of $dayTo, find their difference (no need for compensation).
    else
    {
      $myTime[1] = $dayTo - $dayFrom;
    }

    // Compensate for 12 month wrap-around.
    if($myTime[0] < 0)
    {
      $myTime[0] = 12 + $myTime[0];
    }

    // Finally, we need to determine if the timespan contains the break where
    // the Gregorian calendar was established.
    if(
    (
    ($yearFrom == 1582 && $monFrom == 10 && $dayFrom <= 4) ||
    ($yearFrom == 1582 && $monFrom < 10) ||
    ($yearFrom < 1582)
    ) &&
    (
    ($yearTo == 1582 && $monTo == 10 && $dayTo >= 15) ||
    ($yearTo == 1582 && $monTo > 10) ||
    ($yearTo > 1582)
    )
    )
    {
      $myTime[1] -= 10;
    }

    // If, because of the Gregorian calendar skip, the 1 drops below zero,
    // borrow a month and add 31 days (the number of days there would have been
    // in October 1582 if there had not been the change to the Gregorian calendar.
    if($myTime[1] < 0)
    {
      $myTime[0] -= 1;
      $myTime[1] += 31;
    }

    // If the number of months drops below zero, borrow a year and add 12 months.
    if($myTime[0] < 0)
    {
      $myTime[2] -= 1;
      $myTime[0] += 12;
    }

    if ($myTime[2] > 0) {
      if ($myTime[2] != 1) {
        $age_string = $myTime[2] . ' Years';
      } else {
        $age_string = $myTime[2] . ' Year';
      }
    } else if ($myTime[0] > 0) {
      if ($myTime[0] != 1) {
        $age_string = $myTime[0] . ' Months';
      } else {
        $age_string = $myTime[0] . ' Month';
      }
    } else if ($myTime[1] > 0) {
      if ($myTime[0] != 1) {
        $age_string = $myTime[1] . ' Days';
      } else {
        $age_string = $myTime[1] . ' Day';
      }
    }else{
      $age_string = '';
    }
    return $age_string;
  }

  /**
   * Function: getDaysInMonth
   * Arguments: $myDate
   * Comments: Given a date, getDaysInMonth determines how many
   *           days were/are/will be in that particular month of
   *           that particular year.
   */

  public static function getDaysInMonth($myDate)
  {
    // Initial declarations for clarity.
    list($month, $day, $year) = $myDate;

    // Look at the $month variable
    switch($month)
    {
      // February (Accounts for leap years)
      case 2 :
        $days = 28 + dUtils::isLeapYear($year);
        break;

        // April, June, September, November
      case 4 :
      case 6 :
      case 9 :
      case 11 :
        $days = 30;
        break;
        // January, March, May, July, August, October, December
      default :
        $days = 31;
    }
    return $days;
  }

  /**
   *  Function: isLeapYear
   * Arguments: $year
   *  Comments: Given a year, isLeapYear determines whether or
   *            not it is a leap year.
   */

  public static function isLeapYear($year)
  {
    $leapYear = 0;

    if($year != 4) // Year 4 was not a leap year!
    {
      if(!($year % 4)) // if $year is divisible by 4
      {
        $leapYear = 1;
        if(!($year % 100) && ($year % 400)) // if $year is divisible by 100 and not divisible by 400
        {
          $leapYear = 0;
        }
      }
    }

    return $leapYear;
  }

  /**
   * This thing makes a descriptive sentence out of an array of strings.
   * If it's empty, it will return an empty string.
   *
   * @param array $attributes the adjectives
   * @param string $start something to start the sentence with
   * @param string $ending goes before the last word
   * @param string $comma a comma (replace with "and"?)
   * @return string
   */
  public static function describer($attributes=false,$start='is',$ending='and is',$comma=','){
    $cnt=0;
    if(join('',$attributes)==''){
      return false;
    }
    $sentence = count($attributes)==0 ? '' : ' '.$start.' ';
    foreach($attributes as $word){
      if(trim($word)==''){
        continue;
      }
      $cnt+=1;
      if($cnt==count($attributes)){
        $sentence .= $word; // the last word
      }elseif($cnt==count($attributes)-1){
        $sentence .= $word.' '.$ending.' '; // right before the last word
      }else{
        $sentence .= $word.$comma.' '; // all the other words
      }
    }
    return $sentence.'.';
  }

  public static function titleCase($str) {

    // Edit this list to change what words should be lowercase
    $small_words = "a an and as at but by en for if in of on or the to v[.]? via vs[.]?";
    $small_re = str_replace(" ", "|", $small_words);

    // Replace HTML entities for spaces and record their old positions
    $htmlspaces = "/&nbsp;|&#160;|&#32;/";
    $oldspaces = array();
    preg_match_all($htmlspaces, $str, $oldspaces, PREG_OFFSET_CAPTURE);

    // Remove HTML space entities
    $words = preg_replace($htmlspaces, " ", $str);

    // Split around sentance divider-ish stuff
    $words = preg_split('/( [:.;?!][ ] | (?:[ ]|^)["Ò])/x', $words, -1, PREG_SPLIT_DELIM_CAPTURE);

    for ($i = 0; $i < count($words); $i++) {

      // Skip words with dots in them like del.icio.us
      $words[$i] = preg_replace_callback('/\b([[:alpha:]][[:lower:].\'Õ(&\#8217;)]*)\b/x', 'titleSkipDotted', $words[$i]);

      // Lowercase our list of small words
      $words[$i] = preg_replace("/\b($small_re)\b/ei", "strtolower(\"$1\")", $words[$i]);

      // If the first word in the title is a small word, capitalize it
      $words[$i] = preg_replace("/\A([[:punct:]]*)($small_re)\b/e", "\"$1\" . ucfirst(\"$2\")", $words[$i]);

      // If the last word in the title is a small word, capitalize it
      $words[$i] = preg_replace("/\b($small_re)([[:punct:]]*)\Z/e", "ucfirst(\"$1\") . \"$2\"", $words[$i]);
    }

    $words = join($words);

    // Oddities
    $words = preg_replace("/ V(s?)\. /i", " v$1. ", $words);                    // v, vs, v., and vs.
    $words = preg_replace("/(['Õ]|&#8217;)S\b/i", "$1s", $words);               // 's
    $words = preg_replace("/\b(AT&T|Q&A)\b/ie", "strtoupper(\"$1\")", $words);  // AT&T and Q&A
    $words = preg_replace("/-ing\b/i", "-ing", $words);                         // -ing
    $words = preg_replace("/(&[[:alpha:]]+;)/Ue", "strtolower(\"$1\")", $words);          // html entities

    // Put HTML space entities back
    $offset = 0;
    for ($i = 0; $i < count($oldspaces[0]); $i++) {
      $offset = $oldspaces[0][$i][1];
      $words = substr($words, 0, $offset) . $oldspaces[0][$i][0] . substr($words, $offset + 1);
      $offset += strlen($oldspaces[0][$i][0]);
    }

    return $words;
  }

  public static function titleSkipDotted($matches) {
    return preg_match('/[[:alpha:]] [.] [[:alpha:]]/x', $matches[0]) ? $matches[0] : ucfirst($matches[0]);
  }

  /**
   * This method lets us send an email, abstracting all the instance methods above.
   *
   * @param string $to_email
   * @param string $to_name
   * @param string $email_name The name of the partial file the email used.
   * @param array $email_data The data passed to the email.
   * @param mixed $email_subject_data The replacement strings for the email subject line.
   * @return boolean
   */
  public static function setupAndSend($to_email, $to_name, $email_name, $email_data=false, $email_subject_data=false){
    sfLoader::loadHelpers('Partial');
    
    $dEmail = new dEmail();
    $dEmail->setTo($to_email);
    $dEmail->setToName($to_name);
    $dEmail->setHtmlMessage(get_partial("global/email/$email_name", $email_data));
    $dEmail->setTextMessage(get_partial("global/email/$email_name.txt", $email_data));
    $dEmail->setSubject(sprintf(sfConfig::get('email_subject_'.$email_name), $email_subject_data));

    //send the email
    if ($dEmail->send() != 1) {
      return false;
    }else{
      return true;
    }
  }

}
