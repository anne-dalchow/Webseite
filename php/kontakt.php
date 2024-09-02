<?php
session_start();
error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Europe/Berlin');
require_once("captcha/AntiSpam.php");
$q = AntiSpam::getRandomQuestion();
header('Content-type: text/html; charset=utf-8');


#########################################################################
#	Kontaktformular.com         					                                #
#	http://www.kontaktformular.com        						                    #
#	All rights by KnotheMedia.de                                    			#
#-----------------------------------------------------------------------#
#	I-Net: http://www.knothemedia.de                            					#
#########################################################################
// Der Copyrighthinweis darf NICHT entfernt werden!


  $script_root = substr(__FILE__, 0,
                        strrpos(__FILE__,
                                DIRECTORY_SEPARATOR)
                       ).DIRECTORY_SEPARATOR;

$remote = getenv("REMOTE_ADDR");

function encrypt($string, $key) {
	$result = '';
	for($i=0; $i<strlen($string); $i++) {
	   $char = substr($string, $i, 1);
	   $keychar = substr($key, ($i % strlen($key))-1, 1);
	   $char = chr(ord($char)+ord($keychar));
	   $result.=$char;
	}
	return base64_encode($result);
}

@require('config.php');
require_once("captcha/AntiSpam.php");
include("PHPMailer/Secureimage.php");



// form-data should be deleted
if (isset($_POST['delete']) && $_POST['delete']){
	unset($_POST);
}

$success = false;

$formMessage = '';
$buttonClass = '';



// form has been sent
$isFormSubmit = mb_strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
if ($isFormSubmit) {



	// clean data
	$vorname   	= stripslashes($_POST["vorname"]);
	$name      	= stripslashes($_POST["name"]);
	$firma		= stripslashes($_POST["firma"]);
	$telefon		= stripslashes($_POST["telefon"]);
	$email      = stripslashes($_POST["email"]);
	$betreff   	= stripslashes($_POST["betreff"]);
	$nachricht  = stripslashes($_POST["nachricht"]);
	if($cfg['Datenschutz_Erklaerung']) { $datenschutz = stripslashes($_POST["datenschutz"]); }
	if($cfg['Sicherheitscode']){
		$sicherheits_eingabe = encrypt($_POST["sicherheitscode"], "8h384ls94");
		$sicherheits_eingabe = str_replace("=", "", $sicherheits_eingabe);
	}

	$date = date("d.m.Y | H:i");
	$ip = $_SERVER['REMOTE_ADDR'];
	$UserAgent = $_SERVER["HTTP_USER_AGENT"];
	$host = getHostByAddr($remote);


	// formcheck
	
	if(!$vorname) {
		$fehler['vorname'] = "<span class='errormsg'>Geben Sie bitte Ihren <strong>Vornamen</strong> ein.</span>";
	}
	
	if(!$name) {
		$fehler['name'] = "<span class='errormsg'>Geben Sie bitte Ihren <strong>Nachnamen</strong> ein.</span>";
	}
	
	if (!preg_match("/^[0-9a-zA-ZÄÜÖ_.-]+@[0-9a-z.-]+\.[a-z]{2,6}$/", $email)) {
		$fehler['email'] = "<span class='errormsg'>Geben Sie bitte Ihre <strong>E-Mail-Adresse</strong> ein.</span>";
	}
	
	if(!$betreff) {
		$fehler['betreff'] = "<span class='errormsg'>Geben Sie bitte einen <strong>Betreff</strong> ein.</span>";
	}
	
	if(!$nachricht) {
		$fehler['nachricht'] = "<span class='errormsg'>Geben Sie bitte eine <strong>Nachricht</strong> ein.</span>";
	}
	
	
	
	// -------------------- SPAMPROTECTION ERROR MESSAGES START ----------------------
	if($cfg['Sicherheitscode'] && $sicherheits_eingabe != $_SESSION['captcha_spam']){
		unset($_SESSION['captcha_spam']);
		$fehler['captcha'] = "<span class='errormsg'>Der <strong>Sicherheitscode</strong> wurde falsch eingegeben.</span>";
	} 
		
  if($cfg["Sicherheitsfrage"]){
	$answer = AntiSpam::getAnswerById(intval($_POST["q_id"]));
	if(isset($_POST["q"]) && $_POST["q"] != $answer){
		$fehler['q_id12'] = "<span class='errormsg'>Bitte die <strong>Sicherheitsfrage</strong> richtig beantworten.</span>";
	}
  }

	if($cfg['Honeypot'] && (!isset($_POST["mail"]) || ''!=$_POST["mail"])){
		$fehler['Honeypot'] = "<span class='errormsg-spamprotection' style='display: block;'>Es besteht Spamverdacht. Bitte überprüfen Sie Ihre Angaben.</span>";
	}
	
	if($cfg['Zeitsperre'] && (!isset($_POST["chkspmtm"]) || ''==$_POST["chkspmtm"] || '0'==$_POST["chkspmtm"] || (time() - (int) $_POST["chkspmtm"]) < (int) $cfg['Zeitsperre'])){
		$fehler['Zeitsperre'] = "<span class='errormsg-spamprotection' style='display: block;'>Bitte warten Sie einige Sekunden, bevor Sie das Formular erneut absenden.</span>";
	}
	
	if($cfg['Klick-Check'] && (!isset($_POST["chkspmkc"]) || 'chkspmhm'!=$_POST["chkspmkc"])){
		$fehler['Klick-Check'] = "<span class='errormsg-spamprotection' style='display: block;'>Sie müssen den Senden-Button mit der Maus anklicken, um das Formular senden zu können.</span>";
	}
	
	if($cfg['Links'] < preg_match_all('#http(s?)\:\/\/#is', $nachricht, $irrelevantMatches)){
		$fehler['Links'] = "<span class='errormsg-spamprotection' style='display: block;'>Ihre Nachricht darf ".(0==$cfg['Links'] ? 
																																'keine Links' : 
																																(1==$cfg['Links'] ? 
																																	'nur einen Link' : 
																																	'maximal '.$cfg['Links'].' Links'
																																)
																															)." enthalten.</span>";
	}
	
	if(''!=$cfg['Badwordfilter'] && 0!==$cfg['Badwordfilter'] && '0'!=$cfg['Badwordfilter']){
		$badwords = explode(',', $cfg['Badwordfilter']);			// the configured badwords
		$badwordFields = explode(',', $cfg['Badwordfields']);		// the configured fields to check for badwords
		$badwordMatches = array();									// the badwords that have been found in the fields
		
		if(0<count($badwordFields)){
			foreach($badwords as $badword){
				$badword = trim($badword);												// remove whitespaces from badword
				$badwordMatch = str_replace('%', '', $badword);							// take human readable badword for error-message
				$badword = addcslashes($badword, '.:/');								// make ., : and / preg_match-valid
				if('%'!=substr($badword, 0, 1)){ $badword = '\\b'.$badword; }			// if word mustn't have chars before > add word boundary at the beginning of the word
				if('%'!=substr($badword, -1, 1)){ $badword = $badword.'\\b'; }			// if word mustn't have chars after > add word boundary at the end of the word
				$badword = str_replace('%', '', $badword);								// if word is allowed in the middle > remove all % so it is also allowed in the middle in preg_match 
				foreach($badwordFields as $badwordField){
					if(preg_match('#'.$badword.'#is', $_POST[trim($badwordField)]) && !in_array($badwordMatch, $badwordMatches)){
						$badwordMatches[] = $badwordMatch;
					}
				}
			}		
			
			if(0<count($badwordMatches)){
				$fehler['Badwordfilter'] = "<span class='errormsg-spamprotection' style='display: block;'>Folgende Begriffe sind nicht erlaubt: ".implode(', ', $badwordMatches)."</span>";
			}
		}		
	}
	
  // -------------------- SPAMPROTECTION ERROR MESSAGES ENDE ----------------------
  
  
	if($cfg['Datenschutz_Erklaerung'] && isset($datenschutz) && $datenschutz == ""){ 
		$fehler['datenschutz'] = "<span class='errormsg'>Sie müssen die <strong>Datenschutz&shy;erklärung</strong> akzeptieren.</span>";
	}

    $buttonClass = 'failed';
    $formMessage = '<img src="img/failed.png" style="width:25px;height:25px;vertical-align: middle;"> <span class="error_in_email_sending">Bitte überprüfen und korrigieren Sie Ihre Eingaben.</span>';



	// there are NO errors > upload-check
    if (!isset($fehler) || count($fehler) == 0) {
      $error             = false;
      $errorMessage      = '';
      $uploadErrors      = array();
      $uploadedFiles     = array();
      $totalUploadSize   = 0;
	  $j = 0;       
        
	  if (2==$cfg['UPLOAD_ACTIVE'] && in_array($_SERVER['REMOTE_ADDR'], $cfg['BLACKLIST_IP']) === true) {
          $error = true;
		  $uploadErrors[$j]['name'] = '';
          $uploadErrors[$j]['error'] = "Sie haben keine Erlaubnis Dateien hochzuladen.";
          $j++;
      }

      

      if (!$error) {
          for ($i=0; $i < $cfg['NUM_ATTACHMENT_FIELDS']; $i++) {
              if ($_FILES['f']['error'][$i] == UPLOAD_ERR_NO_FILE) {
                  continue;
              }

              $extension = explode('.', $_FILES['f']['name'][$i]);
              $extension = strtolower($extension[count($extension)-1]);
              $totalUploadSize += $_FILES['f']['size'][$i];

              if ($_FILES['f']['error'][$i] != UPLOAD_ERR_OK) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  switch ($_FILES['f']['error'][$i]) {
                      case UPLOAD_ERR_INI_SIZE :
                          $uploadErrors[$j]['error'] = 'Die Datei ist zu groß (PHP-Ini Direktive).';
                      break;
                      case UPLOAD_ERR_FORM_SIZE :
                          $uploadErrors[$j]['error'] = 'Die Datei ist zu groß (MAX_FILE_SIZE in HTML-Formular).';
                      break;
                      case UPLOAD_ERR_PARTIAL :
						  if (2==$cfg['UPLOAD_ACTIVE']) {
                          	  $uploadErrors[$j]['error'] = 'Die Datei wurde nur teilweise hochgeladen.';
						  } else {
							  $uploadErrors[$j]['error'] = 'Die Datei wurde nur teilweise versendet.';
					  	  }
                      break;
                      case UPLOAD_ERR_NO_TMP_DIR :
                          $uploadErrors[$j]['error'] = 'Es wurde kein temporärer Ordner gefunden.';
                      break;
                      case UPLOAD_ERR_CANT_WRITE :
                          $uploadErrors[$j]['error'] = 'Fehler beim Speichern der Datei.';
                      break;
                      case UPLOAD_ERR_EXTENSION  :
                          $uploadErrors[$j]['error'] = 'Unbekannter Fehler durch eine Erweiterung.';
                      break;
                      default :
						  if (2==$cfg['UPLOAD_ACTIVE']) {
                          	  $uploadErrors[$j]['error'] = 'Unbekannter Fehler beim Hochladen.';
						  } else {
							  $uploadErrors[$j]['error'] = 'Unbekannter Fehler beim Versenden des Email-Attachments.';
						  }
                  }

                  $j++;
                  $error = true;
              }
              if ($totalUploadSize > $cfg['MAX_ATTACHMENT_SIZE']*1024) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  $uploadErrors[$j]['error'] = 'Maximaler Upload erreicht ('.$cfg['MAX_ATTACHMENT_SIZE'].' KB).';
                  $j++;
                  $error = true;
              }
              if ($_FILES['f']['size'][$i] > $cfg['MAX_FILE_SIZE']*1024) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  $uploadErrors[$j]['error'] = 'Die Datei ist zu groß (max. '.$cfg['MAX_FILE_SIZE'].' KB).';
                  $j++;
                  $error = true;
              }
              if (!empty($cfg['WHITELIST_EXT']) && strpos($cfg['WHITELIST_EXT'], $extension) === false) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  $uploadErrors[$j]['error'] = 'Die Dateiendung ist nicht erlaubt.';
                  $j++;
                  $error = true;
              }
              if (preg_match("=^[\\:*?<>|/]+$=", $_FILES['f']['name'][$i])) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  $uploadErrors[$j]['error'] = 'Ungültige Zeichen im Dateinamen (\/:*?<>|).';
                  $j++;
                  $error = true;
              }
              if (2==$cfg['UPLOAD_ACTIVE'] && file_exists($cfg['UPLOAD_FOLDER'].'/'.$_FILES['f']['name'][$i])) {
                  $uploadErrors[$j]['name'] = $_FILES['f']['name'][$i];
                  $uploadErrors[$j]['error'] = 'Die Datei existiert bereits. Bitte benennen Sie die Datei um.';
                  $j++;
                  $error = true;
              }
              if(!$error) {
				  if (2==$cfg['UPLOAD_ACTIVE']) {
                     move_uploaded_file($_FILES['f']['tmp_name'][$i], $cfg['UPLOAD_FOLDER'].'/'.$_FILES['f']['name'][$i]);
				  }
                  $uploadedFiles[$_FILES['f']['tmp_name'][$i]] = $_FILES['f']['name'][$i];
              }
          }
      }
      
      
      if ($error) {
          $errorMessage = ''."";
          if (count($uploadErrors) > 0) {
              $tmp = '';
			  foreach ($uploadErrors as $err) {
                  $tmp .= '<strong>'.$err['name']."</strong><br/>\n- ".$err['error']."<br/><br/>\n";
              }
              $tmp = "<br/>".$tmp;
          }
          $errorMessage .= $tmp.'';
          $fehler['upload'] = "<span class='errormsg-upload' style='display: block;'>".$errorMessage."</span>";
      }
	}


$buttonClass = 'failed';


	// there are NO errors > send mail
   if (!isset($fehler))   {
   
   
		// ------------------------------------------------------------
		// -------------------- send mail to admin --------------------
		// ------------------------------------------------------------

		// ---- create mail-message for admin
	  $mailcontent  = "Folgendes wurde am ". $date ." Uhr per Formular geschickt:\n" . "-------------------------------------------------------------------------\n\n";
		$mailcontent .= "Name: " . $vorname . " " . $name . "\n";
		$mailcontent .= "Firma: " . $firma . "\n\n";
		$mailcontent .= "E-Mail: " . $email . "\n";
		$mailcontent .= "Telefon: " . $telefon . "\n";
		$mailcontent .= "\nBetreff: " . $betreff . "\n";
		$mailcontent .= "Nachricht:\n" . $nachricht = preg_replace("/\r\r|\r\n|\n\r|\n\n/","\n",$nachricht) . "\n\n";
		if(count($uploadedFiles) > 0){
			if(2==$cfg['UPLOAD_ACTIVE']){
				$mailcontent .= "\n\n";
				$mailcontent .= 'Es wurden folgende Dateien hochgeladen:'."\n";
				foreach ($uploadedFiles as $filename) {
					$mailcontent .= ' - '.$cfg['DOWNLOAD_URL'].'/'.$cfg['UPLOAD_FOLDER'].'/'.$filename."\n";
				}
			} else {
				$mailcontent .= "\n\n";
				$mailcontent .= 'Es wurden folgende Dateien übertragen:'."\n";
				foreach ($uploadedFiles as $filename) {
					$mailcontent .= ' - '.$filename."\n";
				}
			}
		}
		if($cfg['Datenschutz_Erklaerung']) { $mailcontent .= "\n\nDatenschutz: " . $datenschutz . " \n"; }
    $mailcontent .= "\n\nIP Adresse: " . $ip . "\n";
		$mailcontent = strip_tags ($mailcontent);

		// ---- get attachments for admin
		$attachments = array();
		if(1==$cfg['UPLOAD_ACTIVE'] && count($uploadedFiles) > 0){
			foreach($uploadedFiles as $tempFilename => $filename) {
				$attachments[$filename] = file_get_contents($tempFilename);
			}
		}

		$success = false;

        // ---- send mail to admin
        if($smtp['enabled'] !== 0) {
            require_once __DIR__ . '/smtp.php';
            $success = SMTP::send(
                $smtp['host'],
                $smtp['user'],
                $smtp['password'],
                $smtp['encryption'],
                $smtp['port'],
                $email,
                $ihrname,
                $empfaenger,
                $betreff,
                $mailcontent,
                (2==$cfg['UPLOAD_ACTIVE'] ? array() : $uploadedFiles),
                $cfg['UPLOAD_FOLDER'],
                $smtp['debug']
            );
        } else {
            $success = sendMyMail($email, $vorname." ".$name, $empfaenger, $betreff, $mailcontent, $attachments);
        }

    	// ------------------------------------------------------------
    	// ------------------- send mail to customer ------------------
    	// ------------------------------------------------------------
    	if(
			$success && 
			(
				2==$cfg['Kopie_senden'] || 																// send copy always
				(1==$cfg['Kopie_senden'] && isset($_POST['mail-copy']) && 1==$_POST['mail-copy'])		// send copy only if customer want to
			)
		){

    		// ---- create mail-message for customer
			$mailcontent  = "Vielen Dank für Ihre E-Mail. Wir werden schnellstmöglich darauf antworten.\n\n";
    		$mailcontent .= "Zusammenfassung: \n" .  "-------------------------------------------------------------------------\n\n";
    		$mailcontent .= "Name: " . $vorname . " " . $name . "\n";
    		$mailcontent .= "Firma: " . $firma . "\n\n";
    		$mailcontent .= "E-Mail: " . $email . "\n";
    		$mailcontent .= "Telefon: " . $telefon . "\n";
    		$mailcontent .= "\nBetreff: " . $betreff . "\n";
    		$mailcontent .= "Nachricht:\n" . str_replace("\r", "", $nachricht) . "\n\n";
    		if(count($uploadedFiles) > 0){
    			$mailcontent .= 'Sie haben folgende Dateien übertragen:'."\n";
    			foreach($uploadedFiles as $file){
    				$mailcontent .= ' - '.$file."\n";
    			}
    		}
    		$mailcontent = strip_tags ($mailcontent);

    		// ---- send mail to customer
            if($smtp['enabled'] !== 0) {
                SMTP::send(
                    $smtp['host'],
                    $smtp['user'],
                    $smtp['password'],
                    $smtp['encryption'],
                    $smtp['port'],
                    $empfaenger,
                    $ihrname,
                    $email,
                    "Ihre Anfrage",
                    $mailcontent,
                    array(),
                    $cfg['UPLOAD_FOLDER'],
                    $smtp['debug']
                );
            } else {
                $success = sendMyMail($empfaenger, $ihrname, $email, "Ihre Anfrage", $mailcontent);
            }
		}
		
		// redirect to success-page	   
        
		if ($success) {
            if ($cfg['Erfolgsmeldung'] === 0) {
                if ($smtp['enabled'] === 0 || $smtp['debug'] === 0) {
                    	echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=" . $danke . "\">";
                }
                exit;
            }
            
        } else {
            
        $fehler['Sendmail'] = "<span class='errormsg-emailerror' style='display: block;line-height:25px;font-size:16px;'><span style='text-decoration:underline;font-size:18px;line-height:28px;color:red;'>Die Nachricht konnte aus einem der folgenden Gründe nicht versandt werden:</span><br /><ol style='text-align: left;line-height:25px;color:black;font-weight:normal;'>
  
  <br />
  <li><b>IONOS (1&amp;1) Kunden:</b> <a  style=\"color: blue; text-decoration: none;\" target=\"blank\" href=\"https://www.kontaktformular.com/webhoster-hosteurope.html#IONOS-E-Mail-Versand\">Beachten Sie den Hinweis #1.</a><br/><br/>
  
  <b>Hosteurope Kunden:</b> <a  style=\"color: blue; text-decoration: none;\" target=\"blank\" href=\"https://www.kontaktformular.com/webhoster-hosteurope.html#domain-eigene-email\">Beachten Sie den Hinweis #2, #3 und #4.</a> (<span>&#8592; Eventuell sind die Infos auch für weitere Hosting Provider relevant!</span>)<br/><br/>
  
 
  
    <b>webgo Kunden:</b> <a  style=\"color: blue; text-decoration: none;\" target=\"blank\" href=\"https://www.kontaktformular.com/webhoster-hosteurope.html#domain-eigene-email\">Beachten Sie den Hinweis #2.</a> (<span>&#8592; Eventuell sind die Infos auch für weitere Hosting Provider relevant!</span>)<br/><br/>
    
    
   <b><a  style=\"color: blue; text-decoration: none;\" target=\"blank\" href=\"https://www.kontaktformular.com/hosting-provider-check.html\">Nutzen Sie gerne unsere Seite Hoster-Check. (Das Kontaktformular Script wurde bei verschiedenen Hosting-Anbietern getestet.)</a> </b><br/><br/>
  
  <b>Wichtig:</b> Löschen Sie den Cache und die Cookies (bzw. Websitedaten) in Ihrem Browser, nachdem Sie Änderungen vorgenommen haben. Ansonsten kann es sein, dass diese Meldung weiterhin erscheint. Nutzen Sie ggf. einen alternativen Browser, um die Funktionsfähigkeit des Kontaktformulars festzustellen.</li><br />
  
  
  <li>Die verwendete E-Mail Adresse (siehe config.php) muss ggf. bei Ihrem Hosting Provider verifiziert werden. Bitte wenden Sie sich an Ihren Hosting Provider.<br /><br /><b>Hinweis:</b> Leider verbieten einige Hosting Provider (z.B. Hosteurope, IONOS) die Nutzung einer Domain-fremden E-Mail Adresse. (z.B. von Freemail Anbietern) Wir empfehlen Ihnen daher eine Domain-eigene E-Mail Adresse in der Form info@ihre-domain.com zu nutzen.<br/><br/><b>Wichtig:</b> Löschen Sie den Cache und die Cookies (bzw. Websitedaten) in Ihrem Browser, nachdem Sie die verifizierte E-Mail Adresse in die Datei config.php eingetragen haben. Ansonsten kann es sein, dass diese Meldung weiterhin erscheint. Nutzen Sie ggf. einen alternativen Browser, um die Funktionsfähigkeit des Kontaktformulars festzustellen.</li><br />
    
  
<li>Sie betreiben einen lokalen Webserver (http://localhost): Testen Sie das Kontaktformular grundsätzlich auf dem Webserver eines renommierten Hosting Anbieters. Die PHP Funktion mail() kann auf einem lokalen Webserver in der Regel nur eingeschränkt genutzt werden.</li><br />
  
  
  <li>Sie haben SMTP in der Datei config.php aktiviert und falsche SMTP Daten eingegeben. Bitte wenden Sie sich an Ihren Hosting Provider.<br /><br /><span style=\"text-decoration:underline;\">Hinweis:</span> Das Aktivieren der SMTP Funktion ist für den E-Mail Versand nicht zwingend erforderlich, da grundsätzlich die PHP Funktion mail() verwendet wird.</li><br />
  
  
  <li>Sie haben SMTP in der Datei config.php aktiviert und möchten eine Verbindung zu einem externen Mailserver (z.B. Gmail, GMX, WEB.DE, Yahoo, T-Online) herstellen. Hierfür muss ggf. eine Portfreischaltung bei Ihrem Hosting Provider beantragt werden. Bitte wenden Sie sich an Ihren Hosting Provider.<br /><br /><span style=\"text-decoration:underline;\">Hinweis:</span> Das Aktivieren der SMTP Funktion ist für den E-Mail Versand nicht zwingend erforderlich, da grundsätzlich die PHP Funktion mail() verwendet wird.</li><br />
   
   
   <li><a  style=\"color: blue; text-decoration: none;\" target=\"blank\" href=\"https://www.kontaktformular.com/faq-script-php-kontakt-formular.html#keine-mail-erhalten\">Weitere Informationen erhalten Sie auf unserer FAQ Seite. </a></li>
</ol>";
		}
		
		if (!empty($fehler['Sendmail'])) {
    $buttonClass = '<span style=display:none;>failed</span>';
    $formMessage = '<span style=display:none;>Ihre Nachricht wurde NICHT gesendet.</span>';
}

else {

    $buttonClass = 'finished';
    $formMessage = '<img src="img/finished.png" style="width:29px;height:29px;vertical-align: middle;"> <span class="successfully_sent">Ihre Nachricht wurde gesendet.</span>';
                             
		}
	}
}


// clean post
foreach($_POST as $key => $value){
$_POST[$key] = htmlentities($value, ENT_QUOTES, "UTF-8");
}
?><?php
function sendMyMail($fromMail, $fromName, $toMail, $subject, $content, $attachments=array()){

	$boundary = md5(uniqid(time()));
	$eol = PHP_EOL;

	// header
	$header = "From: =?UTF-8?B?".base64_encode(stripslashes($fromName))."?= <".$fromMail.">".$eol;
	$header .= "Reply-To: <".$fromMail.">".$eol;
	$header .= "MIME-Version: 1.0".$eol;
	if(is_array($attachments) && 0<count($attachments)){
		$header .= "Content-Type: multipart/mixed; boundary=\"".$boundary."\"";
	}
	else{
		$header .= "Content-type: text/plain; charset=utf-8";
	}


	// content with attachments
	if(is_array($attachments) && 0<count($attachments)){

		// content
		$message = "--".$boundary.$eol;
		$message .= "Content-type: text/plain; charset=utf-8".$eol;
		$message .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
		$message .= $content.$eol;

		// attachments
		foreach($attachments as $filename=>$filecontent){
			$filecontent = chunk_split(base64_encode($filecontent));
			$message .= "--".$boundary.$eol;
			$message .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol;
			$message .= "Content-Transfer-Encoding: base64".$eol;
			$message .= "Content-Disposition: attachment; filename=\"".$filename."\"".$eol.$eol;
			$message .= $filecontent.$eol;
		}
		$message .= "--".$boundary."--";
	}
	
	// content without attachments
	else{
		$message = $content;
	}
	
	// subject
	$subject = "=?UTF-8?B?".base64_encode($subject)."?=";
	
	// send mail
	return mail($toMail, $subject, $message, $header);
}

?>
<!DOCTYPE html>
<html lang="de-DE">
	<head>
		<meta charset="utf-8">
		<meta name="language" content="de"/>
		<meta name="description" content="kontaktformular.com"/>
		<meta name="revisit" content="After 7 days"/>
		<meta name="robots" content="INDEX,FOLLOW"/>
		<title>kontaktformular.com</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<!-- Stylesheet -->
<link href="css/style-kontaktformular.css" rel="stylesheet">
<link rel='stylesheet' href='css/inter.min.css'>


<script src="js/jquery.min.js"></script>





</head>





<body>
	

	<div>
		<form id="kontaktformular" class="kontaktformular" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data">


<script>
if (navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0) 
{
   document.getElementsByTagName("BODY")[0].className += " safari";
}
		</script>




			<p id="submitMessage" class="<?= $buttonClass ?>"><?= $formMessage ?><?php 
				if(
					(isset($fehler["Honeypot"]) && $fehler["Honeypot"] != "") || 
					(isset($fehler["Zeitsperre"]) && $fehler["Zeitsperre"] != "") ||
					(isset($fehler["Klick-Check"]) && $fehler['Klick-Check'] != "") ||
					(isset($fehler["Links"]) && $fehler['Links'] != "") ||
					(isset($fehler["Badwordfilter"]) && $fehler['Badwordfilter'] != "") || 
					(isset($fehler["Sendmail"]) && $fehler['Sendmail'] != "") ||
					(isset($fehler["upload"]) && $fehler['upload'] != "") 
				){
					?>
					<div class="row">
						<div class="col-sm-8">
							<?php if (isset($fehler["Honeypot"]) && $fehler["Honeypot"] != "") { echo $fehler["Honeypot"]; } ?>
							<?php if (isset($fehler["Zeitsperre"]) && $fehler["Zeitsperre"] != "") { echo $fehler["Zeitsperre"]; } ?>
							<?php if (isset($fehler["Klick-Check"]) && $fehler["Klick-Check"] != "") { echo $fehler["Klick-Check"]; } ?>
							<?php if (isset($fehler["Links"]) && $fehler["Links"] != "") { echo $fehler["Links"]; } ?>
							<?php if (isset($fehler["Badwordfilter"]) && $fehler["Badwordfilter"] != "") { echo $fehler["Badwordfilter"]; } ?>
							<?php if (isset($fehler["Sendmail"]) && $fehler["Sendmail"] != "") { echo $fehler["Sendmail"]; } ?>
							<?php if (isset($fehler["upload"]) && $fehler["upload"] != "") { echo $fehler["upload"]; } ?>
						</div>
					</div>
					<?php
				}
			
			
			?></p>

			<div class="row">
<div class="col-sm-8">
					
					<input class="input-field" type="text" placeholder=" " name="firma" value="<?php echo $_POST['firma']; ?>" maxlength="<?php echo $zeichenlaenge_firma; ?>">
  

				<div>	</div><label class="label-field">Firma</label>
				</div>
			</div>





			




<div class="row">


<div class="col-sm-4 <?php if ($fehler["vorname"] != "") { echo 'error'; } ?>">
				
				
				
					
						<input  class="input-field" type="text" placeholder=" "<?php if($cfg['HTML5_FEHLERMELDUNGEN']) { ?> required <?php }else{ ?> onchange="checkField(this)" <?php } ?> name="vorname" value="<?php echo $_POST['vorname']; ?>" maxlength="<?php echo $zeichenlaenge_vorname; ?>"> <div> <?php if ($fehler["vorname"] != "") { echo $fehler["vorname"]; } ?></div>
					<label class="label-field">Vorname *</label>
					
				</div>
				
				
				
				
				
				<div class="col-sm-4 <?php if ($fehler["name"] != "") { echo 'error'; } ?>">
				
				
				
					
						<input  class="input-field" type="text" placeholder=" "<?php if($cfg['HTML5_FEHLERMELDUNGEN']) { ?> required <?php }else{ ?> onchange="checkField(this)" <?php } ?> name="name" value="<?php echo $_POST['name']; ?>" maxlength="<?php echo $zeichenlaenge_name; ?>"> <div> <?php if ($fehler["name"] != "") { echo $fehler["name"]; } ?></div>
					<label class="label-field">Nachname *</label>
					
				</div>
				
				
				
				
				
				
				
			</div>






<div class="row">
				<div class="col-sm-4 <?php if ($fehler["email"] != "") { echo 'error'; } ?>">
					
  <input class="input-field" type="email" placeholder=" "<?php if($cfg['HTML5_FEHLERMELDUNGEN']) { ?> required <?php }else{ ?> onchange="checkField(this)" <?php } ?> name="email" value="<?php echo $_POST['email']; ?>" maxlength="<?php echo $zeichenlaenge_email; ?>">
 

				<div>	<?php if ($fehler["email"] != "") { echo $fehler["email"]; } ?></div> <label class="label-field">E-Mail *</label>
				</div>
				
				
				<div class="col-sm-4">
					
					<input class="input-field" type="text" placeholder=" " name="telefon" value="<?php echo $_POST['telefon']; ?>" maxlength="<?php echo $zeichenlaenge_telefon; ?>">
  <div></div><label class="label-field">Telefon</label>

					
				</div>
			</div>



<div class="row">
<div class="col-sm-8 <?php if ($fehler["betreff"] != "") { echo 'error'; } ?>">
					
					<input class="input-field" type="text" placeholder=" "<?php if($cfg['HTML5_FEHLERMELDUNGEN']) { ?> required <?php }else{ ?> onchange="checkField(this)" <?php } ?> name="betreff" value="<?php echo $_POST['betreff']; ?>" maxlength="<?php echo $zeichenlaenge_betreff; ?>">
  

				<div>	<?php if ($fehler["betreff"] != "") { echo $fehler["betreff"]; } ?></div><label class="label-field">Betreff *</label>
				</div>
			</div>




<div class="row">
<div class="col-sm-8 <?php if ($fehler["nachricht"] != "") { echo 'error'; } ?>">
					
				
  
  <textarea <?php if($cfg['HTML5_FEHLERMELDUNGEN']) { ?> required <?php }else{ ?> onchange="checkField(this)" <?php } ?> aria-label="Nachricht"  class="textarea-field" rows="5" placeholder=" "  style="height:100%;width:100%;" name="nachricht"><?php echo $_POST['nachricht']; ?></textarea>
  
  
  
  <div><?php if ($fehler["nachricht"] != "") { echo $fehler["nachricht"]; } ?></div> <label class="label-field">Nachricht *</label>

					
				</div>
			</div>
			
			
			
			



		



		<?php
		// -------------------- DATEIUPLOAD START ----------------------
			if(0<$cfg['NUM_ATTACHMENT_FIELDS']){
				echo '<div class="row">
						<div class="col-sm-8">';
				for ($i=0; $i < $cfg['NUM_ATTACHMENT_FIELDS']; $i++) {
							echo '<input class="input-field" type="file" size=12 name="f[]" placeholder="">';
				}
				echo '<label><i class="fa fa-upload"></i>&nbsp;&nbsp;</label></div>
					</div>';
			}
		// -------------------- DATEIUPLOAD ENDE ----------------------
		?>






		<?php
		// -------------------- SPAMPROTECTION START ----------------------

		if($cfg['Honeypot']){ ?>
			<div style="height: 2px; overflow: hidden;">
				<label style="margin-top: 10px;">Das nachfolgende Feld muss leer bleiben, damit die Nachricht gesendet wird!</label>
				<div style="margin-top: 10px;"><input type="email" name="mail" value="" /></div>
			</div>
		<?php }

		if($cfg['Zeitsperre']){ ?>
			<input type="hidden" name="chkspmtm" value="<?php echo time(); ?>" />
		<?php }

		if($cfg['Klick-Check']){ ?>
			<input type="hidden" name="chkspmkc" value="chkspmbt" />
		<?php }


		if($cfg['Sicherheitscode']) { ?>
			<div style="margin-bottom:10px;"><img aria-label="Captcha" src="captcha/captcha.php" alt="Sicherheitscode" title="kontaktformular.com-sicherheitscode" id="captcha" />
						<a href="javascript:void(0);" onclick="javascript:document.getElementById('captcha').src='captcha/captcha.php?'+Math.random();cursor:pointer;">
							<span class="captchareload"><i style="color:grey;" class="fas fa-sync-alt"></i></span>
						</a></div>
						
						
						
						<div class="row">
			
		
			
						
					
			
			
				<div class="col-sm-8 <?php if ($fehler["captcha"] != "") { echo 'error'; } ?>">
					
					
					<input <?php if($cfg['HTML5_FEHLERMELDUNGEN']) { ?> required <?php }else{ ?> onchange="checkField(this)" <?php } ?> aria-label="Eingabe" id="answer" placeholder=" "  type="text" name="sicherheitscode" maxlength="150"  class="captcha-field<?php if ($fehler["captcha"] != "") { echo ' errordesignfields'; } ?>"/>
					 <div><?php if ($fehler["captcha"] != "") { echo $fehler["captcha"]; } ?></div><label class="label-field">Sicherheitscode *</label>
				</div>
			</div>
		  

		<?php }

		if($cfg['Sicherheitsfrage']) { ?>
		  <div style="margin-bottom:10px;line-height:20px;"><?php echo $q[1]; ?>
						<input type="hidden" name="q_id" value="<?php echo $q[0]; ?>"/>
					</div>	
					
					
			<div class="row">
			
			
				
				<div class="col-sm-8 <?php if ($fehler["q_id12"] != "") { echo 'error'; } ?>">
					
									
					<input <?php if($cfg['HTML5_FEHLERMELDUNGEN']) { ?> required <?php }else{ ?> onchange="checkField(this)" <?php } ?> aria-label="Antwort" id="answer" placeholder=" "  type="text" class="question-field<?php if ($fehler["q_id12"] != "") { echo ' errordesignfields'; } ?>" name="q"/>
					<div><?php if ($fehler["q_id12"] != "") { echo $fehler["q_id12"]; } ?></div><label class="label-field">Antwort *</label>
				</div>
			</div>
		  
		  

		<?php } 

		// -------------------- SPAMPROTECTION ENDE ----------------------
		{ ?>






		<?php }
		
	// -------------------- MAIL-COPY START ----------------------

		if(1==$cfg['Kopie_senden']) { ?>
			<div class="row checkbox-row">
				<div class="col-sm-8">
					
					<label></label>
					<label class="checkbox-inline">
						<input aria-label="E-Mail-Kopie senden" type="checkbox" id="inlineCheckbox11" name="mail-copy" value="1" <?php if (isset($_POST['mail-copy']) && $_POST['mail-copy']=='1') echo(' checked="checked" '); ?>> <div style="padding-top:4px;padding-bottom:2px;"><span>Kopie der Nachricht per E-Mail senden</span></div>
					</label>
				</div>
			</div>
				
<?php  }

if($cfg['Datenschutz_Erklaerung']) { ?>
			<div style="margin-bottom:8px;">
				
			</div>

			
		<?php } 

		// -------------------- MAIL-COPY ENDE ----------------------
		
		
		// -------------------- DATAPROTECTION START ----------------------

		if($cfg['Datenschutz_Erklaerung']) { ?>
			<div class="row checkbox-row <?php if ($fehler["datenschutz"] != "") { echo 'error_container'; } ?>">
				<div class="col-sm-8 <?php if ($fehler["datenschutz"] != "") { echo 'error'; } ?>">
					
					<label></label>
					<label class="checkbox-inline">
						<input <?php if($cfg['HTML5_FEHLERMELDUNGEN']) { ?> required <?php }else{ ?> onchange="checkField(this)" <?php } ?> aria-label="Datenschutz" type="checkbox" id="inlineCheckbox11" name="datenschutz" value="akzeptiert" <?php if ($_POST['datenschutz']=='akzeptiert') echo(' checked="checked" '); ?>> <div style="padding-top:4px;padding-bottom:2px;"> <a href="<?php echo "$datenschutzerklaerung"; ?>" target="_blank">Ich stimme der Datenschutz&shy;erklärung zu.</a> *</div>
					</label>
					<?php if ($fehler["datenschutz"] != "") { echo $fehler["datenschutz"]; } ?>
				</div>
			</div>
		<?php } 

		// -------------------- DATAPROTECTION ENDE ----------------------
		 ?>
		 
		 
<hr style="height:0.10rem; border:none; color:#DADADA; background-color:#DADADA; margin-top:43px; " />

			<div class="row" id="send">
			<div class="col-sm-8">
						
			<div class="required_notice">
					<span style="line-height:23px;font-size:16px;color:white"><b>Hinweis:</b> Felder mit <span class="pflichtfeld">*</span> müssen ausgefüllt werden.</span>
					</div>
					
					<button type="submit" class="senden <?= $buttonClass ?>" name="kf-km" id="submitButton">
                  

                    <span class="label">Nachricht senden</span>  <svg class="loading-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>

	
            </div>

              

        </div>
  
<?php if ($cfg['Loading_Spinner']): ?>
<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", () => {
                const element = document.getElementById("submitButton");
                
            });          
            document.querySelector('.senden').addEventListener('click', function() {
            var form = document.getElementById('kontaktformular'); 
            if (form.checkValidity()) {
            this.classList.add('loading');
            this.style.backgroundColor = '#A6A6A6';  
            } else {
            console.log('');
    }
});
        </script>
        
        
        
    
        
<script type="text/javascript">document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('kontaktformular');

    form.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        const submitButton = document.getElementById('submitButton');
        const submitButtonLabel = submitButton.querySelector('.label');
        const loadingSpinner = submitButton.querySelector('.loading-spinner');

        // Blende den Text aus und zeige den Spinner an
        submitButtonLabel.style.display = 'none';
        loadingSpinner.style.display = 'block';

        submitButton.disabled = true;

        // Simuliere den Ladevorgang und reiche das Formular nach einer Sekunde ein
        setTimeout(() => {
            form.submit(); // Führe den echten Form-Submit nach 1 Sekunde durch
        }, 1000);
    });
});

</script>
    
    
        
        
    
<?php endif; ?>
		 
<?php if ($cfg['Erfolgsmeldung']): ?>

<script>
 

    
    // Überprüfe, ob die Klasse "finished" aktiv ist
  var isFinished = document.querySelector('.senden').classList.contains('finished');

  // Ändere die Beschriftung entsprechend
  if (isFinished) {
  var submitButton = document.getElementById('submitButton');
  submitButton.innerHTML = '<span class="label_finished">Nachricht gesendet</span>';
  
}

  
</script>
        <?php endif; ?>



        <?php if ($cfg['Klick-Check']) { ?>
			<script type="text/javascript">
				function chkspmkcfnk(){
					document.getElementsByName('chkspmkc')[0].value = 'chkspmhm';
				}
				document.getElementsByName('kf-km')[0].addEventListener('mouseenter', chkspmkcfnk);
				document.getElementsByName('kf-km')[0].addEventListener('touchstart', chkspmkcfnk);
			</script>
		<?php } ?>
			<script type="text/javascript">
				// set class kontaktformular-validate for form if user wants to send the form > so the invalid-styles only appears after validation
				function setValidationStyles(){
					document.getElementById('kontaktformular').setAttribute('class', 'kontaktformular kontaktformular-validate');
				}
				document.getElementsByName('kf-km')[0].addEventListener('click', setValidationStyles);
				document.getElementById('kontaktformular').addEventListener('submit', setValidationStyles);

			</script>
		<?php if(!$cfg['HTML5_FEHLERMELDUNGEN']) { ?>
			
			
			<script type="text/javascript">

				// set class kontaktformular-validate for form if user wants to send the form > so the invalid-styles only appears after validation
				function checkField(field){
					if(''!=field.value){
						
						// if field is checkbox: go to parentNode and do things because checkbox is in label-element
						if('checkbox'==field.getAttribute('type')){
							field.parentNode.parentNode.classList.remove("error");						
							field.parentNode.nextElementSibling.style.display = 'none';
						}
						// field is no checkbox: do things with field
						else{
							field.parentNode.classList.remove("error");
						  field.nextElementSibling.style.display = 'none';
						}
						
						// remove class error_container from parent-elements
						field.parentNode.parentNode.parentNode.classList.remove("error_container");
						field.parentNode.parentNode.classList.remove("error_container");
						field.parentNode.classList.remove("error_container");	
					}
				}
				
			</script>
		
		<?php } ?>
		
		</form>
	</div>
<!-- Dieser Copyrighthinweis darf NICHT entfernt werden. -->
<br />
<div style="display: flex;justify-content: center;align-items: center;padding-right:5%;padding-left:5%;margin-top:2px;">

<div style="border: 1px solid #ccc; margin: 0;box-sizing: border-box;display: inline-block;line-height: 0.3;vertical-align: top;padding-bottom:7.5px;padding-right:5px;padding-left:5px;">
						<br /><br /><a href="https://www.kontaktformular.com" title="kontaktformular.com" style="text-decoration: none;color:rgba(208, 208, 208, 1);;font-size:13px;line-height: 20px;" target="_blank">&copy; by kontaktformular.com - Alle Rechte vorbehalten.</a>
</div>
</div>
<!-- Dieser Copyrighthinweis darf NICHT entfernt werden. -->
	
</body>
</html>

