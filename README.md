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
$pdfURL = $ag->generate_attest($nom, $prenom, $date_naissance,$lieu_naissance,$adresse,$code_postal,$ville, array($motif));

// get PDF and PNG URL
// return false if internal url not define
$pdfURL = $ag->getPDFURL();
$pngURL = $ag->getPNGURL();

// delete file from export dir
$success_Delete = $ag->deletePDFFile(); // return true/false if succeed
$success_Delete = $ag->deleteQRFile(); // return true/false if succeed
$success_Delete = $ag->deleteAllFiles(); // return true/false if succeed
```
