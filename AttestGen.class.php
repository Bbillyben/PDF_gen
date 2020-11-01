<?php

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;
//use QRcode;

require_once(dirname(__FILE__).'/FPDF/fpdf.php');
require_once(dirname(__FILE__).'/FPDI/autoload.php');
require_once(dirname(__FILE__) . '/phpqrcode/phpqrcode.php');

class ATTESTGEN {

    // constante pour les motifs
    const TRAVAIL = 'travail';
    const ENFANT = 'enfant';
    const LOISIR = 'loisir';
    const ACHAT = 'Achats';
    const SANTE = 'Soins';
    const FAMILLE = 'Famille';
    const HANDI = 'handicap';
    const JUDIC = 'judiciaire';
    const MIG = 'mission';

    //public $aMemberVar = 'aMemberVar Member Variable';
    public $generate_attest = 'generate_attest';

    protected $idPos; // array avec les positions des identifiants
    protected $motPos; // array avec les position des cases à cocher motif

    protected $url_qrcode; // addresse du Ppng du qrcode au besoin
    protected $url_pdf; // addresse du Ppng du qrcode au besoin

    function __construct()
    {
        $this->idPos = array(
            'NOM'=>array(42,50),
            'DDN'=>array(42,58),
            'LIEU_DDN'=>array(107,58),
            'ADRESSE'=>array(47,66),
            'SIG_VILLE'=>array(37,234),
            'SIG_DATE'=>array(33,242),
            'SIG_HEURE'=>array(90,242),
            );
        $this->motPos= array(
            'TRAVAIL' => array(28,91.5),
            'ENFANT' => array(28,221),
            'LOISIR' => array(28,169),
            'ACHAT' => array(28,107),
            'SANTE' => array(28,127),
            'FAMILLE' => array(28,141),
            'HANDI' => array(28,156),
            'JUDIC' => array(28,191.5),
            'MIG' => array(28,205)
            );

    }

    // retourne l'url du png du QR code une fois le fichier créé
    public function getPNGURL(){
        if (!isset($this->url_qrcode)){
            return false;
        }
        return $this->url_qrcode;
    }
    //retourne l'URL du pdf une fois le fichier créé
    public function getPDFURL(){
        if (!isset($this->url_pdf)){
            return false;
        }
        return $this->url_pdf;
    }

    // detruit le fichier PDF si créé
    public function deletePDFFile(){
        if (!isset($this->url_pdf)){
            return false;
        }
        if(!file_exists($this->url_pdf)){
            return false;
        }

        return unlink($this->url_pdf);

    }
    // detruit le fichier QR code png créé
    public function deleteQRFile(){
        if (!isset($this->url_qrcode)){
            return false;
        }
        if(!file_exists($this->url_qrcode)){
            return false;
        }

        return unlink($this->url_qrcode);

    }
    // détruit les 2 fichiers
    public function deleteAllFiles(){
        return $this->deletePDFFile() && $this->deleteQRFile();
    }
    function generate_attest($name,$fname,$ddn,$lieu_ddn,$address,$zip,$ville, $motifs) {


        // vérificaiton existance du dossier
        if(!is_dir(dirname(__FILE__) . '/EXPORT')){
            mkdir(dirname(__FILE__) . '/EXPORT');
        }
        // génération du QR code
        $date_time=strftime("%d/%m/%G a %Hh%M");
        $qrcode="Cree le: ".$date_time.";\nNom: ".$name.";\nPrenom: ".$fname.";\nNaissance: ".$ddn." a ".$lieu_ddn.";\nAdresse:".$address." ".$zip." ".$ville.";\nSortie: ".$date_time."\nMotifs: ".implode (",", $motifs);
      
        $this->url_qrcode = dirname(__FILE__) . '/EXPORT/qrcode_attest'.$fname.'.png';
        $qrcode= stripslashes($qrcode);
        $qrFile = QRcode::png($qrcode,$this->url_qrcode, 'M');





        // génération du PDF
        try {
            $pdf = new FPDI();
            $pdf->addPage();
            $pageCount = $pdf->setSourceFile(dirname(__FILE__) . '/Certificate/certificate.d1673940.pdf');
            $pageId = $pdf->importPage(1);
            $pdf->useTemplate($pageId);


        }
        catch (Exception $e) {
            throw new Exception('Error creating PDF file ('.$e->getMessage().')');
        }
        // ecriture
        $pdf->SetFont('Arial', '', '13');
        $pdf->SetTextColor(0,0,0);

        // NOM
        $pdf->SetXY($this->idPos['NOM'][0], $this->idPos['NOM'][1]);
        $pdf->Write(0, $fname.' '.$name);

        //DDN
        $pdf->SetXY($this->idPos['DDN'][0], $this->idPos['DDN'][1]);
        $pdf->Write(0, $ddn);

        //LIEU_DDN
        $pdf->SetXY($this->idPos['LIEU_DDN'][0], $this->idPos['LIEU_DDN'][1]);
        $pdf->Write(0, $lieu_ddn);

        //adresse
        // en plus petit
        $pdf->SetFont('Arial', '', '10');
        $pdf->SetXY($this->idPos['ADRESSE'][0], $this->idPos['ADRESSE'][1]);
        $pdf->Write(0, $address.' '.$zip.' '.$ville);

        // pour la signature
        //ville
        $pdf->SetFont('Arial', '', '13');
        $pdf->SetXY($this->idPos['SIG_VILLE'][0], $this->idPos['SIG_VILLE'][1]);
        $pdf->Write(0, $ville);

        // date
        $cDate = strftime("%d/%m/%G");
        $pdf->SetXY($this->idPos['SIG_DATE'][0], $this->idPos['SIG_DATE'][1]);
        $pdf->Write(0, $cDate);
        //heure
        $cDate = strftime("%Hh%M");
        $pdf->SetXY($this->idPos['SIG_HEURE'][0], $this->idPos['SIG_HEURE'][1]);
        $pdf->Write(0, $cDate);


        ///// pour les motif

        $pdf->SetFont('Arial', '', '16');
        foreach ($motifs as $motif){
            switch ($motif) {
                case ATTESTGEN::TRAVAIL:
                    $pdf->SetXY($this->motPos['TRAVAIL'][0], $this->motPos['TRAVAIL'][1]);
                    break;
                case ATTESTGEN::ENFANT:
                    $pdf->SetXY($this->motPos['ENFANT'][0], $this->motPos['ENFANT'][1]);
                    break;
                case ATTESTGEN::LOISIR:
                    $pdf->SetXY($this->motPos['LOISIR'][0], $this->motPos['LOISIR'][1]);
                    break;
                case ATTESTGEN::ACHAT:
                    $pdf->SetXY($this->motPos['ACHAT'][0], $this->motPos['ACHAT'][1]);
                    break;
                case ATTESTGEN::SANTE:
                    $pdf->SetXY($this->motPos['SANTE'][0], $this->motPos['SANTE'][1]);
                    break;
                case ATTESTGEN::FAMILLE:
                    $pdf->SetXY($this->motPos['FAMILLE'][0], $this->motPos['FAMILLE'][1]);
                    break;
                case ATTESTGEN::HANDI:
                    $pdf->SetXY($this->motPos['HANDI'][0], $this->motPos['HANDI'][1]);
                    break;
                case ATTESTGEN::JUDIC:
                    $pdf->SetXY($this->motPos['JUDIC'][0], $this->motPos['JUDIC'][1]);
                    break;
                case ATTESTGEN::MIG:
                    $pdf->SetXY($this->motPos['MIG'][0], $this->motPos['MIG'][1]);
                    break;
            }
             $pdf->Write(0, 'X');
        }

        // le png
        $pdf->Image($this->url_qrcode,155,230,32,32,'PNG');

        // enregistrement

        try {
            //attestation-2020-10-30_07-28_Benjamin Legendre
            $date_time=strftime("-%G-%m-%d_%H-%M");
            $this->url_pdf=dirname(__FILE__)."/EXPORT/attestation".urlencode($date_time."_".$fname.' '.$name.".pdf");
            $pdf->Output($this->url_pdf,'F');
        }
        catch (Exception $e) {
            throw new Exception('Error saving PDF file ('.$e->getMessage().')');
        }
        return $this->url_pdf;
    }
}
?>