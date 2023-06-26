<?php

// define functions to use later 

function GC_Content($seq){
    $GC = round((substr_count($seq, 'G') + substr_count($seq, 'C')) / strlen($seq) * 100);
    return $GC;
}

function dna_to_rna($dna_sequence) {
    $rna_sequence = str_replace('T', 'U', $dna_sequence);
    return $rna_sequence;
}

function reverse_complement($dna_sequence) {
    $reverse_sequence = strrev($dna_sequence);
    $complement_sequence = strtr($reverse_sequence, 'ATCG', 'TAGC');
    return $complement_sequence;
}

function find_stop_codons($rna_sequence) {
    $stop_codons = array('UAA', 'UAG', 'UGA');
    $indices = array();
    $SIndices ='';
    // Loop through the RNA sequence in increments of three
    for ($i = 0; $i < strlen($rna_sequence); $i += 3) {
        $codon = substr($rna_sequence, $i, 3);

        // Check if the codon is a stop codon
        if (in_array($codon, $stop_codons)) {
            // Add the index of the stop codon to the indices array
            $indices[] = $i;
            $SIndices .= "$i,";

        }
    }

    return $SIndices;
}

function TranslateRnatoProtein($seq){
    $protein ='';
    $codons = array(
        "GCA"=>"A", "GCC"=>"A", "GCG"=>"A", "GCU"=>"A",
        "UGC"=>"C", "UGU"=>"C", "GAC"=>"D", "GAU"=>"D",
        "GAA"=>"E", "GAG"=>"E", "UUC"=>"F", "UUU"=>"F",
        "GGA"=>"G", "GGC"=>"G", "GGG"=>"G", "GGU"=>"G",
        "CAC"=>"H", "CAU"=>"H", "AUA"=>"I", "AUC"=>"I",
        "AUU"=>"I", "AAA"=>"K", "AAG"=>"K", "UUA"=>"L",
        "UUG"=>"L", "CUA"=>"L", "CUC"=>"L", "CUG"=>"L",
        "CUU"=>"L", "AUG"=>"M", "AAC"=>"N", "AAU"=>"N",
        "CCA"=>"P", "CCC"=>"P", "CCG"=>"P", "CCU"=>"P",
        "CAA"=>"Q", "CAG"=>"Q", "AGA"=>"R", "AGG"=>"R",
        "CGA"=>"R", "CGC"=>"R", "CGU"=>"R", "CGG"=>"R",
        "AGC"=>"S", "AGU"=>"S", "UCA"=>"S", "UCC"=>"S",
        "UCG"=>"S", "UCU"=>"S", "ACA"=>"T", "ACC"=>"T",
        "ACG"=>"T", "ACU"=>"T", "GUA"=>"V", "GUC"=>"V",
        "GUG"=>"V", "GUU"=>"V", "UGG"=>"W", "UAC"=>"Y",
        "UAU"=>"Y", "UAG"=>"", "UAA"=>"", "UGA"=>""
    );
    $n = strlen($seq);
    for ($i = 0; $i < $n - 2; $i += 3) {
        $codon = substr($seq, $i, 3);
        if (isset($codons[$codon])) {
            $amino_acid = $codons[$codon];
            if ($amino_acid == '') {
                break;
            }
            $protein .= $amino_acid;
        }
    }
    return $protein;
}


function MostFrequentKmer($sequence, $k) {
    $n = strlen($sequence);
    $frequencies = array();
    for ($i = 0; $i <= $n - $k; $i++) {
        $kmer = substr($sequence, $i, $k);
        if (!isset($frequencies[$kmer])) {
        $frequencies[$kmer] = 0;
        }
        $frequencies[$kmer]++;
    }
    arsort($frequencies);
    $most_frequent = array_keys($frequencies)[0];
    return $most_frequent;
}

// Set up database connection variables
$host = "localhost";
$username = "root";
$password = "usbw";
$dbname = "bioserver";

// Connect to the database
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}else{

}

// Get the form data
$genid = $_POST['GeneId'];
$functionxx = 5 ;//$_POST['bioprocess'];

$query = "SELECT * FROM gene WHERE GeneID = GeneId";

$result = mysqli_query($conn, $query);

// Step 4: Fetch the data from the result set

$row = mysqli_fetch_assoc($result);

$seq = $row['Seq'];

if ($functionxx == 1 ) {
    $outPut = dna_to_rna($seq);
    echo "$outPut";
    $conn = mysqli_connect($host, $username, $password, $dbname);

    $query = "UPDATE gene SET TranscriptionRes = '" . $outPut. "' WHERE GeneID = " .$genid;

    $result = mysqli_query($conn, $query);

    if (!$result) {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
elseif($functionxx == 2){
    $outPut = TranslateRnatoProtein($seq);
    echo "$outPut";
    $conn = mysqli_connect($host, $username, $password, $dbname);

    $query = "UPDATE gene SET AminoAcid = '" . $outPut. "' WHERE GeneID = " .$genid;

    $result = mysqli_query($conn, $query);

    if (!$result) {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
elseif($functionxx == 3){
    $outPut = GC_Content($seq);
    echo "$outPut";

    $conn = mysqli_connect($host, $username, $password, $dbname);

    $query = "UPDATE gene SET GC_Content = '" . $outPut. "' WHERE GeneID = " .$genid;
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
elseif($functionxx == 4){
    $k = $_POST['K'];
    $outPut = MostFrequentKmer($seq, $k);
    echo $outPut;

    $conn = mysqli_connect($host, $username, $password, $dbname);

    $query = "UPDATE gene SET kmer_value = '" . $outPut. "' WHERE GeneID = " .$genid;
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        echo "Error updating record: " . mysqli_error($conn);
    }



    $conn = mysqli_connect($host, $username, $password, $dbname);

    $query = "UPDATE gene SET k = '" . $k. "' WHERE GeneID = " .$genid;
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
elseif($functionxx == 5){

    $outPut = find_stop_codons($seq);
    echo "$outPut";

    $FirstStop= substr($outPut, 0, 1);

    $conn = mysqli_connect($host, $username, $password, $dbname);

    $query = "UPDATE gene SET StopCodon = '" . $outPut. "' WHERE GeneID = " .$genid;
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        echo "Error updating record: " . mysqli_error($conn);
    }


    $conn = mysqli_connect($host, $username, $password, $dbname);

    $query = "UPDATE gene SET FirstStopCodon = '" . $FirstStop . "' WHERE GeneID = " .$genid;
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        echo "Error updating record: " . mysqli_error($conn);
    }


}
elseif($functionxx == 6){
    $outPut = reverse_complement($seq);
    echo "$outPut";

    $conn = mysqli_connect($host, $username, $password, $dbname);

    $query = "UPDATE gene SET ReverseCompelement = '" . $outPut. "' WHERE GeneID = " .$genid;
    
    $result = mysqli_query($conn, $query);
    if (!$result) {
        echo "Error updating record: " . mysqli_error($conn);
    }
}


?>


