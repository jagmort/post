<?php
require('./param.php');
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';
header($origin);

function file_force_contents($dir, $contents) {
    $parts = explode('/', $dir);
    $file = array_pop($parts);
    $dir = 'temp';
    foreach($parts as $part)
        if(!is_dir($dir .= "/$part")) mkdir($dir);
    file_put_contents("$dir/$file", $contents);
}

if (in_array($_SERVER['REMOTE_ADDR'], $access) && (sizeof($_POST) > 0)) {
    $datetime = new DateTime(null, new DateTimeZone('Europe/Moscow'));
    $year = $datetime->format('Y');
    $mon = $datetime->format('m');
    $day = $datetime->format('d');
    $dir = $year . "/" . $mon . "/" . $day;
    $html = $dir . "/con" . $datetime->format('-Ymd-His') . ".html";
    $long = $dir . "/lng" . $datetime->format('-Ymd-His') . ".html";
    $raw = $dir . "/raw" . $datetime->format('-Ymd-His') . ".txt";
    file_force_contents($raw, print_r($_POST, true) . print_r($_SERVER, true));
    if(isset($_POST['content']) && strlen($_POST['content']) > 0) {
        $line = [];
        $text = preg_replace("/\R/u", "", $_POST['content']);
        $text = preg_replace("/ГП СПД/u", "ГП&nbsp;СПД", $text);
        $text = preg_replace("/Российская Федерация ГОС-ВО, /u", "", $text);
        $text = preg_replace("/(Interaction-\d+) (\d{2})/u", "\\1\n\\2", $text);
        $line = explode("\n", $text);
        $message = "<html>\n<head>\n<meta charset=\"utf-8\"/>\n</head>\n<body>\n<table style=\"font-family:Calibri,Arial,sans-serif;font-size:11pt;border-width:0;border-collapse:collapse\">\n";
        $message2 = '<tr><td colspan="3" style="border-top:1px solid #eee">&nbsp;</td></tr>' . "\n";
        $i = 0; $j = 0;
        foreach ($line as $value) {
            if(strlen($value) > 0) {
                if(preg_match("/(\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}) (.+) (\S+) (\S+)/u", $value, $matches) == 1) { // разбивка полей
                    if(preg_match("/ГПМК-|ГС-|ГОМК-|ИЗМ-/u", $matches[3]) == FALSE) {
                        if(++$i & 1) { $message .= '<tr style="background-color:#eee">'; }
                        else $message .= "<tr>";
                        $message .= '<td style="white-space:nowrap;padding:1px 2px">' . $matches[1] . '</td><td style="padding:1px 8px">' . $matches[2] . '</td><td style="text-align:right;white-space:nowrap;padding:1px 2px"><a target="_blank" href="' . $matches[4] . '">' . $matches[3] . "</a></td></tr>\n";
                    }
                    else {
                        if(++$j & 1) { $message2 .= '<tr style="background-color:#eee;color:#999">'; }
                        else $message2 .= '<tr style="color:#999">';
                        $message2 .= '<td style="white-space:nowrap;padding:1px 2px">' . $matches[1] . '</td><td style="padding:1px 8px">' . $matches[2] . '</td><td style="text-align:right;white-space:nowrap;padding:1px 2px"><a style="color:#999" target="_blank" href="' . $matches[4] . '">' . $matches[3] . "</a></td></tr>\n";
                    }
                }
            }
        }
        $message .= $message2 . "</table>\n</body>\n</html>";
        if(($i + $j) > 0) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isMail();
            $mail->CharSet = 'utf-8'; 
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->setFrom($from);
            //$mail->addCC($cc);
            foreach($to as $r)
                $mail->addAddress($r);
            $mail->Body = $message;
            $mail->send();

            file_force_contents($html, $message);
            echo "Content OK";
        }
        else {
            echo "No content problem";
        }
    } // content

    if(isset($_POST['long']) && strlen($_POST['long']) > 0) {
        $line = [];
        $text = preg_replace("/\R/u", "", $_POST['long']);
        $text = preg_replace("/ГП СПД/u", "ГП&nbsp;СПД", $text);
        $text = preg_replace("/Российская Федерация ГОС-ВО, /u", "", $text);
        $text = preg_replace('/(\${3}) (\d{2})/u', "\\1\n\\2", $text);
        $line = explode("\n", $text);
        $message = "<html>\n<head>\n<meta charset=\"utf-8\"/>\n</head>\n<body>\n<table style=\"font-family:Calibri,Arial,sans-serif;font-size:11pt;border-width:0;border-collapse:collapse\">\n";
        $message2 = '<tr><td colspan="3" style="border-top:1px solid #eee">&nbsp;</td></tr>' . "\n";
        $i = 0; $j = 0;
        foreach ($line as $value) {
            if(strlen($value) > 0) {
                if(preg_match('/(\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}) (.+) (\S+) (\S+) \${3}([^$]*)\${3}/u', $value, $matches) == 1) { // разбивка полей
                    if(preg_match("/ГПМК-|ГС-|ГОМК-|ИЗМ-/u", $matches[3]) == FALSE) {
                        if(++$i & 1) { $message .= '<tr style="background-color:#eee">'; }
                        else $message .= "<tr>";
                        $message .= '<td style="white-space:nowrap;padding:1px 2px">' . $matches[1] . '</td><td style="padding:1px 4px">' . $matches[2] . '</td><td style="text-align:right;white-space:nowrap;padding:1px 4px"><a target="_blank" href="' . $matches[4] . '">' . $matches[3] . '</a></td><td style="padding:1px 2px">' . $matches[5] . "</td></tr>\n";
                    }
                    else {
                        if(++$j & 1) { $message2 .= '<tr style="background-color:#eee;color:#999">'; }
                        else $message2 .= '<tr style="color:#999">';
                        $message2 .= '<td style="white-space:nowrap;padding:1px 2px">' . $matches[1] . '</td><td style="padding:1px 4px">' . $matches[2] . '</td><td style="text-align:right;white-space:nowrap;padding:1px 4px"><a style="color:#999" target="_blank" href="' . $matches[4] . '">' . $matches[3] . '</a></td><td style="padding:1px 2px">' . $matches[5] . "</td></tr>\n";
                    }
                }
            }
        }
        $message .= $message2 . "</table>\n</body>\n</html>";
        if(($i + $j) > 0) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isMail();
            $mail->CharSet = 'utf-8'; 
            $mail->isHTML(true);
            $mail->Subject = $subject2;
            $mail->setFrom($from);
            //$mail->addCC($cc);
            foreach($to as $r)
                $mail->addAddress($r);
            $mail->Body = $message;
            $mail->send();

            file_force_contents($long, $message);
            echo "Long OK";
        }
        else {
            echo "No long problem";
        }
    } // long
}
else {
    echo "Access denied or empty data";
}
?>