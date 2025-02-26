<?php
/*
    Name:           eMail
    Description:    Simple sending eMail in text and HTML with CC, BCC and attachment
    Version:        1.0
    last modified:  2004-05-14

    Autor:          Daniel K�fer
    Homepage:       http://www.danielkaefer.de

    Leave this header in this file!
------------------------------------------------------------------ 
 Updated: 2010-10-25
 Updated by: Michael Hahn (MPH)
 
 Problem: The Suhosin patch for PHP was blocking the email before it ever reached
 the email server due to double line feeds (\n) in the header of the email.
 Also, the body of the message was included in the header. This would also
 trip the security measure everytime it spotted a double line feed.
 Fix: Remove any double line feed from the header info and seperate the body of 
 the message from the header.
 Other updates: I'm not sure about RFC compliance but, every other email I've look at had
 certain information included in double quotes. More than likely to avoid
 erroneous file naming. I tried to emulate this mindset.
 Added line length and EOL char for file chunking. For some reason without
 it there were extra line feeds in the chunked file.

 * Lots of fixes for FA
*/

class email
{
    var $to = array();
    var $cc = array();
    var $bcc = array();
    var $attachment = array();
    var $boundary = "";
    var $header = "";
    var $subject = "";
    var $body = "";
    var $charset = 'ISO-8859-1';
    var $add_params;
    var $to_emails;

    function __construct($name, $mail)
    {
        $this->boundary = md5(uniqid(time()));
        $this->header = "From: $name <$mail>\n";
        $bcc = get_company_pref('bcc_email');
        if ($bcc)
            $this->bcc[] = $bcc;
    }

    function to($mail)
    {
        $this->to[] = $mail;
    }

    function cc($mail)
    {
        $this->cc[] = $mail;
    }

    function bcc($mail)
    {
        $this->bcc[] = $mail;
    }

    function attachment($file, $filename = null)
    {
        if (!isset($filename))
            $filename = basename($file);
        $this->attachment[$filename] = $file;
    }

    function subject($subject)
    {
        $this->subject = $subject;
    }

    function text($text)
    {
        $this->body = "--$this->boundary\n";
        $this->body .= "Content-Type: text/plain; charset=\"{$this->charset}\"\n";
        $this->body .= "Content-Transfer-Encoding: 8bit\n\n";
        $this->body .= $text . "\n";
    }

    function html($html)
    {
        $this->body = "--$this->boundary\n";
        $this->body .= "Content-Type: text/html; charset=\"{$this->charset}\"\n";
        $this->body .= "Content-Transfer-Encoding: quoted-printable\n\n";
        $this->body .= "<html><body>\n" . $html . "\n</body></html>\n";
    }

    function mime_type($filename)
    {
        $file = basename($filename, '.zip');
        if ($filename == $file . '.zip') return 'application/x-zip-compressed';
        $file = basename($filename, '.pdf');
        if ($filename == $file . '.pdf') return 'application/pdf';
        $file = basename($filename, '.xls');
        if ($filename == $file . '.xls') return 'application/vnd.ms-excel';
        $file = basename($filename, '.csv');
        if ($filename == $file . '.csv') return 'application/vnd.ms-excel';
        $file = basename($filename, '.tar');
        if ($filename == $file . '.tar') return 'application/x-tar';
        $file = basename($filename, '.tar.gz');
        if ($filename == $file . '.tar.gz') return 'application/x-tar-gz';
        $file = basename($filename, '.tgz');
        if ($filename == $file . '.tgz') return 'application/x-tar-gz';
        $file = basename($filename, '.gz');
        if ($filename == $file . '.gz') return 'application/x-gzip';
        $file = basename($filename, '.html');
        if ($filename == $file . '.html') return 'text/html';
        return 'application/unknown';
    }

    function send()
    {
        // Add CC Recipients 
        if (!empty($this->cc))
            $this->header .= "Cc: " . implode(", ", $this->cc) . "\n";

        // Add BCC Recipients
        if (!empty($this->bcc))
            $this->header .= "Bcc: " . implode(", ", $this->bcc) . "\n";
        $this->header .= "Content-Type: multipart/mixed;\n boundary=\"$this->boundary\"\n";

        // Add Attachments
        if (!empty($this->attachment)) {
            foreach ($this->attachment as $filename => $file) {
                $file = fread(fopen($file, "r"), filesize($file));
                $this->body .= "--" . $this->boundary . "\n";
                $this->body .= "Content-Type: " . $this->mime_type($file) . "; name=\"" . $filename . "\"\n";
                $this->body .= "Content-Transfer-Encoding: base64\n";
                $this->body .= "Content-Disposition: attachment; filename=\"" . $filename . "\"\n\n";
                $this->body .= chunk_split(base64_encode($file), "72", "\n");
                $file = "";
            }
        }
        $this->body .= "--" . $this->boundary . "--\n";

        $ret = 0;
//
//        display_error(print_r($this->to_emails['email'],true));
//        die;

//        foreach($this->to_emails as $mail)
//        {

        $mail = $this->to_emails['email'];
        $content = "<p>
            Greetings from AxisPro <br><br><br>Please find the attachment.<br><br><br>Thanks & Regards,<br>AxisPro-ERP
            </p>";
        if ($this->send_mail($mail, $this->subject, $content))
            $ret++;

//        }
        return $ret;
    }


    public function send_mail($to, $subject, $content)
    {
        try {
            require('../includes/class.phpmailer.php');
            $mail = new PHPMailer();
            $ret = '';
            $mail->IsSMTP();
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = TRUE;
            $mail->SMTPSecure = "tls";
            $mail->Port = 587;
            $mail->Username = "daxishrms@gmail.com";
            $mail->Password = "Daxis@217";
            $mail->Host = "smtp.gmail.com";
            $mail->Mailer = "smtp";
            $mail->SetFrom("daxishrms@gmail.com", "AxisPro-ERP");
            $mail->AddReplyTo($to, "AxisPro-ERP");
            $mail->AddAddress($to);
            $mail->Subject = $subject;
            $mail->WordWrap = 80;
            $mail->MsgHTML($content);
            $mail->IsHTML(true);

//        display_error(print_r($this->attachment,true));

            if (!empty($this->attachment)) {
                foreach ($this->attachment as $filename => $file) {
                    $mail->addAttachment($file, $filename);
                }
            }


            if (!$mail->Send()) {

                dd($mail->ErrorInfo);
                dd(1111);
                return false;
                $result = ['status' => 'FAIL', 'msg' => 'Mail sending failed'];
            } else {
                //dd($mail->ErrorInfo);


                return true;
                $result = ['status' => 'OK', 'msg' => 'Mail sending successfull'];
            }
            return true;

        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }

//        return $result;
    }


}
