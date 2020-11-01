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
    const ACHATS = 'achats';
    const SANTE = 'sante';
    const FAMILLE = 'famille';
    const HANDICAP = 'handicap';
    const SPORT_ANIMAUX = 'sport_animaux';
    const CONVOCATION = 'convocation';
    const MISSIONS = 'missions';
    const ENFANTS = 'enfants';

    const certiFName = 'certificate.301020.pdf';

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
            'LIEU_DDN'=>array(105,57),
            'ADRESSE'=>array(48,66),
            'SIG_VILLE'=>array(48,234),
            'SIG_DATE'=>array(33,242),
            'SIG_HEURE'=>array(93,242),
            );
        $this->motPos= array(
            'TRAVAIL' => array(26,91.5),
            'ENFANT' => array(26,221),
            'LOISIR' => array(26,170),
            'ACHAT' => array(26,108),
            'SANTE' => array(26,127.5),
            'FAMILLE' => array(26,141.5),
            'HANDI' => array(26,156),
            'JUDIC' => array(26,192),
            'MIG' => array(26,205.5)
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
    function generate_attest($name,$fname,$ddn,$lieu_ddn,$address,$zip,$ville, $motifs, $secondPage=false) {

        // verification si le motif est bien un array
        if(!is_array($motifs)){
            if(is_string($motifs)){
                $motifs=array($motifs); // si c'est une string on le met dans un array pour le traiter ultérieurement
            }else{
                throw new Exception('Error Motif provided is not an array or a string');
                return false;
            }
        }


        // vérificaiton existance du dossier
        if(!is_dir(dirname(__FILE__) . '/EXPORT')){
            mkdir(dirname(__FILE__) . '/EXPORT');
        }
        // génération du QR code
        $date_time=strftime("%d/%m/%G a %Hh%M");
        $qrcode="Cree le: ".$date_time.";\n Nom: ".$name.";\n Prenom: ".$fname.";\n Naissance: ".$ddn." a ".$lieu_ddn.";\n Adresse: ".$address." ".$zip." ".$ville.";\n Sortie: ".$date_time."\n Motifs: ".implode (",", $motifs);

        $this->url_qrcode = dirname(__FILE__) . '/EXPORT/qrcode_attest'.$fname.'.png';
        $qrcode= stripslashes($qrcode);
        $qrFile = QRcode::png($qrcode,$this->url_qrcode, 'M');





        // génération du PDF
        try {
            $pdf = new FPDI();
            $pdf->addPage();
            $pageCount = $pdf->setSourceFile(dirname(__FILE__).'/Certificate/'.ATTESTGEN::certiFName);
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
        $isOk = true;
        foreach ($motifs as $motif){
            switch ($motif) {
                case ATTESTGEN::TRAVAIL:
                    $pdf->SetXY($this->motPos['TRAVAIL'][0], $this->motPos['TRAVAIL'][1]);
                    break;
                case ATTESTGEN::ENFANTS:
                    $pdf->SetXY($this->motPos['ENFANT'][0], $this->motPos['ENFANT'][1]);
                    break;
                case ATTESTGEN::SPORT_ANIMAUX:
                    $pdf->SetXY($this->motPos['LOISIR'][0], $this->motPos['LOISIR'][1]);
                    break;
                case ATTESTGEN::ACHATS:
                    $pdf->SetXY($this->motPos['ACHAT'][0], $this->motPos['ACHAT'][1]);
                    break;
                case ATTESTGEN::SANTE:
                    $pdf->SetXY($this->motPos['SANTE'][0], $this->motPos['SANTE'][1]);
                    break;
                case ATTESTGEN::FAMILLE:
                    $pdf->SetXY($this->motPos['FAMILLE'][0], $this->motPos['FAMILLE'][1]);
                    break;
                case ATTESTGEN::HANDICAP:
                    $pdf->SetXY($this->motPos['HANDI'][0], $this->motPos['HANDI'][1]);
                    break;
                case ATTESTGEN::CONVOCATION:
                    $pdf->SetXY($this->motPos['JUDIC'][0], $this->motPos['JUDIC'][1]);
                    break;
                case ATTESTGEN::MISSIONS:
                    $pdf->SetXY($this->motPos['MIG'][0], $this->motPos['MIG'][1]);
                    break;
                default:
                    $isOk=false;
                    break;
            }
            if($isOk) $pdf->Write(0, 'X');
        }

        // le png
        $pdf->Image($this->url_qrcode,155,230,32,32,'PNG');

        if($secondPage){
            $pdf->addPage();
		    $pdf->Image(dirname(__FILE__) . '/EXPORT/qrcode_attest'.$fname.'.png', 20, 20, 100, 100);
        }
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