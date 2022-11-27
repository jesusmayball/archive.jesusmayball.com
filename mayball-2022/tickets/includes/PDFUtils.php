<?php
require("fpdf/fpdf.php");
require("fpdi/fpdi.php");

global $config;

class PDF extends Fpdi {

    // static function getLogo($dir) {
    //     //idk what the extension is, so try a few
    //     try {
    //         $logo = self::asset_name($dir, "logo", "png");
    //     } catch (Exception $e) {
    //         try {
    //             $logo = self::asset_name($dir, "logo", "jpg");
    //         } catch (Exception $e2) {
    //             try {
    //                 $logo = self::asset_name($dir, "logo", "jpeg");
    //             } catch (Exception $e3) {
    //                 $logo = null;
    //             }
    //         }
    //     }
    //     return $logo;
    // }
    //
    // static function asset_name($dir, $start, $ext) {
    //     $files = glob($dir . "/*." . $ext);
    //
    //     $suffixLength = -9 - strlen($ext) - 1;
    //     foreach ($files as $file) {
    //         $name = substr($file, strlen($dir) + 1, $suffixLength);
    //         if (substr($name, 0, strlen($start)) === $start) {
    //             return $file;
    //         }
    //     }
    //     throw new Exception("file not found");
    // }
    //
    // function Header() {
    //     global $config;
    //     //we dunno what the exact filename is so try by prefix and suffix
    //
    //     $logo = self::getLogo(realpath('../res'));
    //     if (!$logo) {
    //         $logo = self::getLogo(realpath('../../res'));
    //     }
    //
    //     if ($logo != null) {
    //         //put at coordinates x = 20, y = 20 (in millimeters)
    //         // width = 170; height is automatic to keep ratio
    //         $this->Image($logo, 20, 25, 170);
    //     }
    //
    //     $this->SetFont("Arial", "B", 16);
    //     // Move to the right
    //     $this->Cell(80);
    //     $this->Cell(30, 10, $config["complete_event_date_name"], 0, 0, "C");
    //     $this->Ln(20);
    // }
    //
    // function Footer() {
    //     // Position at 1.5 cm from bottom
    //     $this->SetY(-15);
    //     // Arial italic 8
    //     $this->SetFont("Arial", "I", 10);
    //     // Page number
    //     $this->Cell(0, 10, "Powered by the Jesus Ticketing System", 0, 0, "C");
    // }

    function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
        $op='f';
        elseif($style=='FD' || $style=='DF')
        $op='B';
        else
        $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
        $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }

}

class PDFUtils {

    public static function generateFromTicket($ticket, $file) {

        $db = Database::getInstance();
        $ticketTypes = $db->getAllTicketTypes(false, false, true);
        $ticketType = $ticketTypes[$ticket->ticket_type_id];

        $pdf = new PDF();
        $pgWidth = 210;
        $pgHeight = 297;
        $pdf->AddFont("free3of9", "", "free3of9.php");
        // $pdf->AddPage("P", array(105, 148));
        $pdf->AddPage();
        if (realpath('../res/eticket.pdf')) {
            $pdf->setSourceFile(realpath('../res/eticket.pdf'));
        } else {
            $pdf->setSourceFile(realpath('../../res/eticket.pdf'));
        }
        $tplIdx = $pdf->importPage(1);
        //use the imported page and place it at point 0,0; calculate width and height
        //automaticallay and ajust the page size to the size of the imported page
        // $pdf->useTemplate($tplIdx, 0, 0, 0, 0, true);
        $pdf->useTemplate($tplIdx, 0, 0, $pgWidth, $pgHeight);

        //bar code
        $pdf->SetFont("free3of9", "", 32);

        $width = 56;
        $height = 28;
        $ypos = 232;
        $xpos = ($pgWidth - $width) / 2;

        $pdf->SetFillColor(255, 255, 255);
        $pdf->RoundedRect($xpos, $ypos, $width, $height, 3, 'F');

        $code = "*" . $ticket->hash_code . "*";
        $pdf->SetXY($xpos, $ypos);
        $pdf->Cell($width, $height, $code, 0, 0, 'C');

        $pdf->SetFont("Times", "", 11);

        $pdf->SetXY($xpos, $ypos);
        $name = $ticket->title . " " . $ticket->first_name . " " . $ticket->last_name;
        $pdf->Cell($width, 8, $name, 0, 2, 'C');

        $pdf->SetXY($xpos, $ypos + 16);
        $pdf->SetFontSize(8);
        $pdf->Cell($width, 8, $ticket->hash_code, 0, 2, 'C');

        $pdf->SetFont("Times", "I", 11);
        $pdf->SetXY($xpos, $ypos + 20);
        $pdf->SetFontSize(11);
        $pdf->Cell($width, 8, $ticketType->ticket_type, 0, 2, 'C');

        $pdf->Output($file, "F");
    }

    public static function getFilename($ticket) {
        global $config;
        $secret = $config['eticket_secret'];
        $alg = "sha256";
        $ciphertext = hash_hmac($alg, $ticket->hash_code, $secret);
        $filename = $ciphertext . ".pdf";
        return $filename;
    }
}
?>
