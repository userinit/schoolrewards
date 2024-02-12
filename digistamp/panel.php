<?php
// Assigning 
$yearInfo = [
    "Year 10" => [
        "Tutors" => ["Newton", "Einstein", "Bohr", "Darwin", "Pasteur"],
        "Classes" => ["Freud", "Galilei", "Lavoisier", "Kepler", "Copernicus"]
    ],
    "Year 11" => [
        "Tutors" => ["Faraday", "Maxwell", "Bernard", "Boas", "Heisenberg"],
        "Classes" => ["Pauling", "Virchow", "Schrodinger", "Rutherford", "Dirac"]
    ],
    "Year 12" => [
        "Tutors" => ["Vesalius", "Brahe", "Buffon", "Boltzmann", "Planck"],
        "Classes" => ["Curie", "Herschel", "Lyell", "Lapalace", "Hubble"]
    ],
    "Year 13" => [
        "Tutors" => ["Thomson", "Born", "Crick", "Fermi", "Liebig"],
        "Classes" => ["Eddington", "Harvey", "Malpighi", "Huygens", "Gauss"]
    ]];

if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
    if ($_SERVER['REQUEST_METHOD'] === "GET" && isset($_GET['year']) && isset($_GET['type'])) {
        $year = $_GET['year'];
        $type = $_GET['type'];
        $validYears = [10, 11, 12, 13];

        if (in_array($year, $validYears) && ($type === 'tutor' || $type === 'class')) {
            // After this, we can be sure nobody has manipulated the year and type
            $textYear = "Year " . $year;
            $type = ucfirst(strtolower($type)) . "s";
            $classList = $yearInfo[$textYear][$type];
            $associativeArray = array('classList', $classList);
            $encodedErray = json_encode($associativeArray);
            echo $encodedErray;

        }
    }
    else {
        http_response_code(405);
    }
}
else {
    http_response_code(403);
    header("Location: 403.html");
}
?>