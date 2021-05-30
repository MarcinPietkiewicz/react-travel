<?php 
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
function read_directory_to_option_list($catalog) {
  $files = scandir('protected/'.$catalog);
  // ommit . and .. from catalog items and display only 13 most recent files
  $arr_length = count($files);
  if ($arr_length>15){
  $files = array_slice($files, -14, $arr_length);
  }
  else {
    $files = array_slice($files, 2, $arr_length);
  }
  $files = array_reverse($files);
  $file_name_filter = "/\d{14}\.txt/";
  foreach($files as $value)
  {
  if (preg_match($file_name_filter, $value))
  {
    $value_shown = change_file_datename_to_date($value);
    echo "<option value='$value'>", $value_shown, "</option>", PHP_EOL; 
  }
  } 
}
// read file contents
function read_file($catalog) {
$selected_value = $_POST['filenames'];
if ($selected_value == ''){
  $contents = get_newest_file_content($catalog);
  echo $contents;
  }
  else {
  $path = 'protected/'.$catalog.'/'.$selected_value;
  $contents = file_get_contents($path);
  echo $contents;
  }
}
function read_file_name($catalog) {
  $selected_value = $_POST['filenames'];
  if ($selected_value == ''){
    // read newest file per timestamp name
    $files = scandir('protected/'.$catalog, 1);
    $file_date_time = change_file_datename_to_date($files[0]).' <b>(najnowsza wersja)</b>';
    echo $file_date_time;
    }
    else {
    $file_date_time = change_file_datename_to_date($selected_value);
    echo $file_date_time;
    }
  }
// helper function to convert datehour txt file name to date and hour for display
function change_file_datename_to_date($file_name) {
    $value_shown = substr($file_name, 0, 4)."-".substr($file_name, 4, 2)."-".substr($file_name, 6, 2)." ".substr($file_name, 8, 2).":".substr($file_name, 10, 2).":".substr($file_name, 12, 2);
    return $value_shown;
}
// write form contents in a txt file with date/hour as a filename
function write_file($catalog, $input) {
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $current_date_hour_filename = date("YmdHis").".txt";
  $new_file = fopen('protected/'.$catalog.'/'.$current_date_hour_filename, "w");
  $data = $_POST[$input];
  fwrite($new_file, $data);
  fclose($new_file);
  echo "Nowa wersja została zapisana do pliku - ".($current_date_hour_filename)." i dodana do strony głównej!<br>";
  if ($_POST['email']){
  echo "Dodatkowy mejl wysłany na adres: ".$_POST['email'];
  }
  else {
    echo "Nie wysłano dodatkowego mejla";
  }
}}
// read file contents from selected option menu
function read_selected_file(){
  $selected_value = $_POST['filenames'];
  if (empty($selected_value))
  {
    echo 'Error... file not found, loaded newest version to the text area...<br>Please choose a file from the list.';
  }
  else {
    echo 'Znaleziono i załadowano plik: '.$selected_value.' - wszystko ok!<br>';
  }
}
//secure login to edit website
function move_to_edit() {
  if (array_key_exists('move_me', $_POST)) {
    header('Location: http://www.example.com/');
  }
}
// read most current file contents to modal
function read_file_to_concert_modal($catalog) {
    $contents = get_newest_file_content($catalog);
    print_concert_html($contents);
  }
// for now concert modal html uses the same formatting as concert, but it's easy to change it here
function read_file_to_contact_modal($catalog){
  $contents = get_newest_file_content($catalog);
  $contents = protect_email_mobile_against_bots($contents);  
  print_concert_html($contents);
}
// insert <span>something</span> inside email addresses and telephone numbers to protect them against bots
function protect_email_mobile_against_bots($contents){
  $regex = "/[@]/i";
    $contents = preg_replace($regex, "@<span class='zamazuj'>yahoo-</span>", $contents);
  $regexTwo = "/(\d{3})(\d{3})(\d{3})/";
  $contents = preg_replace($regexTwo, "$1<span class='zamazuj'>243</span>$2<span class='zamazuj'>658</span>$3", $contents);
    return $contents;
}

// convert events file text to html
function convert_events_text_to_html($post_obj) {
  $html = '';
  $text = preg_split('/\R{2}/', $post_obj);
  foreach ($text as $event)
  {
    $lines = preg_split('/\R/', $event); 
    $counter = 0;
    foreach ($lines as $line)
    {
      if ($counter == 0)
      {
        $html .= '<p><b>'.$line.'</b></p>';
        $counter++;
        continue;
      }
      $html .= $line.'<br>';
    }
  }
  return $html;
}
// send confirmation email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
function send_confirmation_email($post_obj = '') {
  require 'password.php';
  $additional_email = $_POST['email'] ? $_POST['email'] : false;
  $utf8_text = mb_convert_encoding($_POST[$post_obj], 'HTML-ENTITIES', "UTF-8");
  $html = convert_events_text_to_html($utf8_text);
  try {
      date_default_timezone_set("Europe/Warsaw");
      $mail = new PHPMailer;
      $mail->Host = $email_host;
      $mail->SMTPAuth = "true";
      $mail->SMTPSecure = "tls";
      $mail->Port = "587";
      $mail->CharSet = 'UTF-8';
      $mail->Username = $email_username;
      $mail->Password = $email_password;
      $mail->isHTML(true);
      $mail->setFrom($email_username);
      $mail->addAddress($email_to);
      if ($additional_email){
      $mail->addCC($additional_email);
      }
      $mail->addReplyTo($email_username);
      $mail->Subject = 'Strona radoslawsobczak.com pomyślnie zaktualizowana!';
      $mail->Body = '<p>Potwierdzenie dokonanych zmian na stronie:</p>'.$html.'<br><br><p>---------------------<br>Zmiany pomyślnie zapisane na serwerze.</p>';
      $mail->send();
      sleep(1);
    }
        catch (Exception $e) {
        header('Location: http://www.radoslawsobczak.com/test2/error.php');
    }
  }
function read_file_to_bio_modal($language = '', $catalog){
  $contents = get_newest_file_content($catalog);
  print_bio_html($contents, $language);
}

function read_file_to_blog_thumb($catalog, $post = 0){
  $contents = get_newest_file_content($catalog);
  print_blog_html_thumb($contents, $post);
}

// update protected file with new email if changed
function change_secondary_email_in_pass_file($new_email) {
  $data = explode("\n", rtrim(file_get_contents('protected/password.php')));
  $data[9] = "\$second_email = '".$_POST[$new_email]."';";
  $data = implode("\n", $data);
  file_put_contents('protected/password.php', $data);
}
  // read newest file per timestamp name from given directory and output contents
function get_newest_file_content($dir){
  $files = scandir('protected/'.$dir, 1);
  $contents = file_get_contents('protected/'.$dir.'/'.$files[0]);
  $contents = mb_convert_encoding($contents, 'HTML-ENTITIES', "UTF-8");
  return $contents;
}
function print_bio_html($contents, $language){
  $sections = preg_split('/\R{2}/', $contents);
  foreach ($sections as $section)
  {
    $lines = preg_split('/\R/', $section); 
    $languagetxt = '#'.$language."#";
    if ($lines[0] == $languagetxt){
      $counter = 0;
      foreach ($lines as $line)
      {
        if ($counter == 0){
        $counter++;
        continue;
        }
        if ($counter == 1)
        {
          echo '<h1>'.$line.'</h1>';
          $counter++;
          continue;
        }
        echo '<p>'.$line.'</p>';
      }
    }
  }
}
function print_concert_html($contents){
  $sections = preg_split('/\R{2}/', $contents);
  foreach ($sections as $section)
  {
    $lines = preg_split('/\R/', $section); 
    $counter = 0;
    foreach ($lines as $line)
    {
      if ($counter == 0)
      {
        echo '<p><b>'.$line.'</b></p>';
        $counter++;
        continue;
      }
      echo $line.'<br>';
    }
    echo '<br>';

  }
}


function print_blog_html_thumb($contents){
  $sections = preg_split('/\R{2}/', $contents);
  foreach ($sections as $section)
  {
    $lines = preg_split('/\R/', $section); 
    $splitter = '###';
    if ($lines[0] == $splitter){
      $counter = 0;
      foreach ($lines as $line)
      {
        if ($counter == 0){
        $counter++;
        continue;
        }
        if ($counter == 1){
          echo '<div class="tile-title">'.$line.'</div>';
          $counter++;
          continue;
          
        }
        if ($counter == 2){
        echo '<div class="text-preview">'.$line.' ...[read more]</div>';
        $counter++;
        
      }
      }
    }
  }
}

?>