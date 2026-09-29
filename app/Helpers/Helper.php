<?php
namespace App\Helpers;
use Session;
use DateTime;
class Helper{
 
    public static function pr($data, $action = TRUE){
        print "<pre>";
        print_r($data);
        if($action):
            die;
        endif;
    }
    public static function formatDateTime($object, $format = ''){
        return date_format(date_create($object), $format);
    }
    public static function returnJson($returnData = array(), $mode = TRUE){
        print json_encode($returnData);
        if ($mode) :
            die;
        endif;
    }
    public static function UniqueMachineID($salt = "") {  
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {  
            
          $temp = sys_get_temp_dir().DIRECTORY_SEPARATOR."diskpartscript.txt";  
          if(!file_exists($temp) && !is_file($temp)) file_put_contents($temp, "select disk 0\ndetail disk");  
          $output = shell_exec("diskpart /s ".$temp);  
          $lines = explode("\n",$output);  
          $result = array_filter($lines,function($line) {  
              return stripos($line,"ID:")!==false;  
          });  
          // print_r(array_values($result));die;
            
          if(count($result)>0) {  
              $result = array_shift($result);
              // $result = array_values($result);
              $result = explode(":",$result);  
              $result = trim(end($result));         
          } else $result = $output;         
      } else {  
          $result = shell_exec("blkid -o value -s UUID");    
          if(stripos($result,"blkid")!==false) {  
              $result = $_SERVER['HTTP_HOST'];  
          }  
      }     
      return md5($salt.md5($result));  
    }
    public static function encoded($param){
        return urlencode(base64_encode($param));
    }
    public static function decoded($param){
        return base64_decode(urldecode($param));
    }
    public static function extractJson($string){
        $valid = is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))) ? true : false;
        if (!$valid) :
            responseToJson(FALSE, "Not JSON");
        else:
            return json_decode($string, TRUE);
        endif;
    }
    public static function checkApiKey($postData){
        if (!array_key_exists("key", $postData)) :
            responseToJson(FALSE, "Api key missing");
        elseif ($postData['key'] == "") :
            responseToJson(FALSE, "Key field empty");
        elseif ($postData['key'] != getenv('API_KEY')) :
            responseToJson(FALSE, "Wrong Api Key");
        endif;
    }
    public static function validateArray($keys, $arr){
        return !array_diff_key(array_flip($keys), $arr);
    }
    public static function responseToJson($success = TRUE, $message = "success", $data = NULL, $extraField = array()){
        $response = ["success" => $success, "message" => $message, "data" => $data];
        if (!empty($extraField)) :
            foreach ($extraField as $k => $v) :
                $response[$k] = $v;
            endforeach;
        endif;
        returnJson($response);
    }
    public static function uptoTwoDecimal($number){
        return number_format((float)$number, 2, '.', '');
    }
    public static function clean($string) 
    {
       $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
       $string2 = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
       $string3 = preg_replace('/-+/', '-', $string2);
       return strtolower($string3);
    }
    /////////////////////////////////////new fn for time ago/////////////////////////////////////
    public static function time_ago($timestamp)
    {       
        $time_ago        = strtotime($timestamp);
        $current_time    = time();
        $time_difference = $current_time - $time_ago;
        $seconds         = $time_difference;
        
        $minutes = round($seconds / 60); // value 60 is seconds  
        $hours   = round($seconds / 3600); //value 3600 is 60 minutes * 60 sec  
        $days    = round($seconds / 86400); //86400 = 24 * 60 * 60;  
        $weeks   = round($seconds / 604800); // 7*24*60*60;  
        $months  = round($seconds / 2629440); //((365+365+365+365+366)/5/12)*24*60*60  
        $years   = round($seconds / 31553280); //(365+365+365+365+366)/5 * 24 * 60 * 60
                      
        if ($seconds <= 60){    
          return "Just Now";    
        } else if ($minutes <= 60){    
          if ($minutes == 1){    
            return "1 minute ago";    
          } else {    
            return "$minutes minutes ago";    
          }    
        } else if ($hours <= 24){    
          if ($hours == 1){    
            return "an hour ago";    
          } else {    
            return "$hours hrs ago";    
          }    
        } else if ($days <= 7){    
          if ($days == 1){    
            return "yesterday";    
          } else {    
            return "$days days ago";    
          }    
        } else if ($weeks <= 4.3){    
          if ($weeks == 1){    
            return "a week ago";    
          } else {    
            return "$weeks weeks ago";    
          }    
        } else if ($months <= 12){    
          if ($months == 1){    
            return "a month ago";    
          } else {    
            return "$months months ago";    
          }    
        } else {          
          if ($years == 1){    
            return "one year ago";    
          } else {    
            return "$years years ago";    
          }
        }
    }
    // Create a formatting function
    public static function formatting($phone){
        
        // Pass phone number in preg_match function
        if(preg_match(
            '/^\+[0-9]([0-9]{3})([0-9]{3})([0-9]{4})$/', 
        $phone, $value)) {
          
            // Store value in format variable
            // $format = $value[1] . '-' . $value[2] . '-' . $value[3];
            $format = $value[1] . '-' . $value[2] . '-' . '****';
        }
        else {             
            // If given number is invalid
            $format = "Invalid phone number";
        }
        return $format;
    }
    public static function get_starred($str) {
        $str_array =str_split($str);
        foreach($str_array as $key => $char) {
            if($key == 0 || $key == count($str_array)-1) continue;
            if($char != '-') $str[$key] = '*';
        }
        return $str;
    }
    // number convert to words
    public static function getIndianCurrency($number)
    {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
        $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal) ? "and " . ($words[$decimal - ($decimal % 10)] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return ($Rupees ? 'Rupees '.$Rupees . '' : '') . $paise;
    }
    // specific word search check in a string
    public static function searchWordInString($sentence, $word){
        if (str_contains($sentence, $word)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function weekdays($dayNo) {
      if($dayNo==0) {
          $day_name = 'Sunday';
      }
      if($dayNo==1) {
          $day_name = 'Monday';
      }
      if($dayNo==2) {
          $day_name = 'Tuesday';
      }
      if($dayNo==3) {
          $day_name = 'Wednesday';
      }
      if($dayNo==4) {
          $day_name = 'Thursday';
      }
      if($dayNo==5) {
          $day_name = 'Friday';
      }
      if($dayNo==6) {
          $day_name = 'Saturday';
      }
      return $day_name;
    }
    public static function monthName($monthNo) {
      if($monthNo==1) {
          $month_name = 'January';
      }
      if($monthNo==2) {
          $month_name = 'February';
      }
      if($monthNo==3) {
          $month_name = 'March';
      }
      if($monthNo==4) {
          $month_name = 'April';
      }
      if($monthNo==5) {
          $month_name = 'May';
      }
      if($monthNo==6) {
          $month_name = 'June';
      }
      if($monthNo==7) {
          $month_name = 'July';
      }
      if($monthNo==8) {
          $month_name = 'August';
      }
      if($monthNo==9) {
          $month_name = 'September';
      }
      if($monthNo==10) {
          $month_name = 'October';
      }
      if($monthNo==11) {
          $month_name = 'November';
      }
      if($monthNo==12) {
          $month_name = 'December';
      }
      return $month_name;
    }
    public static function getDateListDescending($month, $year) {
      $startDate = new DateTime("$year-$month-01");
      if($month==1) {
        $last_date = 31;
      }
      if($month==2) {
        $last_date = 28;
      }
      if($month==3) {
        $last_date = 31;
      }
      if($month==4) {
        $last_date = 30;
      }
      if($month==5) {
        $last_date = 31;
      }
      if($month==6) {
        $last_date = 30;
      }
      if($month==7) {
        $last_date = 31;
      }
      if($month==8) {
        $last_date = 31;
      }
      if($month==9) {
        $last_date = 30;
      }
      if($month==10) {
        $last_date = 31;
      }
      if($month==11) {
        $last_date = 30;
      }
      if($month==12) {
        $last_date = 31;
      }
      if(date('Y') == $year && date('m') == $month){
        $endDate = new DateTime();
      } else {
        $endDate = new DateTime("$year-$month-$last_date");
      }
      // $endDate = new DateTime(); // Current date
      $dates = [];

      // Generate the list of dates
      while ($startDate <= $endDate) {
          $dates[] = $startDate->format('Y-m-d');
          $startDate->modify('+1 day');
      }
      // echo '<pre>';print_r($dates);die;
      // Sort the dates in descending order
      rsort($dates);

      return $dates;
  }
  public static function getWeekdayName($date) {
    // Map full weekday names to single letters
    $weekdayMap = [
        'Monday' => 'M',
        'Tuesday' => 'T',
        'Wednesday' => 'W',
        'Thursday' => 'T',
        'Friday' => 'F',
        'Saturday' => 'S',
        'Sunday' => 'S'
    ];

    $dateTime = new DateTime($date);
    $fullWeekdayName = $dateTime->format('l'); // Get the full weekday name
    return $weekdayMap[$fullWeekdayName] ?? ''; // Return the corresponding single letter
  }
  public static function generateLightboxImage($imageUrl, $alt = '', $width = 150, $height = 150, $class = 'img-thumbnail', $style = 'margin-top: 10px;') {
    $lightboxUrl = htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8');
    $imgHtml = "<a href=\"$lightboxUrl\" data-lightbox=\"gallery\" data-title=\"$alt\">";
    $imgHtml .= "<img src=\"$lightboxUrl\" alt=\"$alt\" style=\"width: {$width}px; height: {$height}px; $style\" class=\"$class\">";
    $imgHtml .= "</a>";
    return $imgHtml;
}
}
?>