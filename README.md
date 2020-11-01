# PDF_gen
A full php solution to generate French COVID attestation

Use of 
 * phpQRcode library : http://phpqrcode.sourceforge.net/docs/html/index.html
 *  Setasign / FPDF : https://github.com/Setasign/FPDF
 *  Setasign / FPDI : https://github.com/Setasign/FPDI

Class to be used : ATTESTGEN in PDF_gen/AttestGen.class.php

It create an attestation at the date and time the script is called.


Use : 

```instanciate :
$ag = new ATTESTGEN()

// create qrcode and png in /EXPORT sub dir
// return pdfURL
$pdfURL = $ag->generate_attest($nom, $prenom, $date_naissance,$lieu_naissance,$adresse,$code_postal,$ville, array($motif), false);

// get PDF and PNG URL
// return false if internal url not define
$pdfURL = $ag->getPDFURL();
$pngURL = $ag->getPNGURL();

// delete file from export dir
$success_Delete = $ag->deletePDFFile(); // return true/false if succeed
$success_Delete = $ag->deleteQRFile(); // return true/false if succeed
$success_Delete = $ag->deleteAllFiles(); // return true/false if succeed
```


Description of generate pdf
```function generate_attest($name,$fname,$ddn,$lieu_ddn,$address,$zip,$ville, $motifs, $secondPage=false)
$name // your Name
$fname // your firstName
$ddn // your date of birth
$lieu_ddn // the place you where born
$address // your current adress
$zip // your zip code
$ville // the town you live in
 $motifs // an array of motif from class constant motif
 $secondPage=false // wether ypou want the second page of the attestation with the big qrcode
```

Motif availables : corresponding to the attestation
```
    const TRAVAIL = 'travail'; 
    const ACHATS = 'achats';
    const SANTE = 'sante';
    const FAMILLE = 'famille';
    const HANDICAP = 'handicap';
    const SPORT_ANIMAUX = 'sport_animaux';
    const CONVOCATION = 'convocation';
    const MISSIONS = 'missions';
    const ENFANTS = 'enfants';
```
