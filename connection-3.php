<?php
$serverName = "localhost";
$userName = "root";
$password = "";
$conn = mysqli_connect($serverName, $userName, $password);
if ($conn) {
    //echo "Connection Successful <br>";
} else {
    echo "Failed to connect: " . mysqli_connect_error();
}

$createDatabase = "CREATE DATABASE IF NOT EXISTS weather";
if (mysqli_query($conn, $createDatabase)) {
    // echo "Database Created or already Exists <br>";
} else {
    echo "Failed to create database: " . mysqli_error($conn);
}

mysqli_select_db($conn, 'weather');

$createTable = "CREATE TABLE IF NOT EXISTS weather (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(80) NOT NULL,
    temperature FLOAT NOT NULL,
    humidity FLOAT NOT NULL,
    temp_max FLOAT NOT NULL,
    temp_min FLOAT NOT NULL,
    wind FLOAT NOT NULL,
    pressure FLOAT NOT NULL,
    icon VARCHAR(100),
    dsc VARCHAR(150),
    today VARCHAR(250),
    today_date VARCHAR(250),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";

if (mysqli_query($conn, $createTable)) {
    //echo "Table Created or already Exists <br>";
} else {
    echo "Failed to create table: " . mysqli_error($conn);
}

if (isset($_GET['q'])) {
    $cityName = $_GET['q'];
} else {
    $cityName = "Knowsley";
}

$selectData = "SELECT * FROM weather WHERE city = '$cityName'";
$result = mysqli_query($conn, $selectData);

$needsUpdate = true;
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $lastUpdated = strtotime($row['last_updated']);
    if ((time() - $lastUpdated) < 7200) { // 7200 seconds = 2 hours
        $needsUpdate = false;
    }
}

if ($needsUpdate) {
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$cityName&APPID=7a5b6ab54dd2269b8388191a28d610f5";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data) {
        $temp = $data['main']['temp'];
        $humidity = $data['main']['humidity'];
        $temp_max = $data['main']['temp_max'];
        $temp_min = $data['main']['temp_min'];
        $pressure = $data['main']['pressure'];
        $wind = $data['wind']['speed'];
        $icon = $data['weather'][0]['icon'];
        $dsc = $data['weather'][0]['description'];
        $timestamp = time();
        $day = date('l', $timestamp);
        $today_date = date('j M, Y', $timestamp);

        if (mysqli_num_rows($result) == 0) {
            $insertData = "INSERT INTO weather (city, temperature, humidity, temp_max, temp_min, pressure, wind, icon, dsc, today_date, today)
                           VALUES ('$cityName','$temp','$humidity','$temp_max','$temp_min','$pressure','$wind','$icon','$dsc','$today_date','$day')";

            if (!mysqli_query($conn, $insertData)) {
                echo "Failed to insert data: " . mysqli_error($conn);
            }
        } else {
            $updateData = "UPDATE weather SET humidity='$humidity', wind='$wind', pressure='$pressure', temperature='$temp', icon='$icon', temp_max='$temp_max', temp_min='$temp_min', dsc='$dsc', today_date='$today_date', today='$day', last_updated=NOW() WHERE city='$cityName'";

            if (!mysqli_query($conn, $updateData)) {
                echo "Failed to update data: " . mysqli_error($conn);
            }
        }
    } else {
        echo "Failed to fetch weather data from API";
    }
}

$result = mysqli_query($conn, $selectData);
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

$json_data = json_encode($rows);
header('Content-Type: application/json');
echo $json_data;

mysqli_close($conn);
?>

