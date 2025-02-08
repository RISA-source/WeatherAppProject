<?php
date_default_timezone_set('Asia/Kathmandu');

# Connection Establishing
$host = 'roundhouse.proxy.rlwy.net';
$username = 'root';
$password = 'YjbFHGRKyLQfsfjOrMIxesEILWMHjTfP';
$db = 'railway';
$port = '49776';

$conn = mysqli_connect($host,$username,$password,$db,$port);
if ($conn){
}else{
    echo 'Connection Unsuccessful '.mysqli_connect_error();
    die('Error');
}
# Connection Established

#Creating the database
$sql_db = "CREATE DATABASE IF NOT EXISTS railway;";
$result = mysqli_query($conn,$sql_db);
if ($result){
}else{
    echo 'Failed to create database '.mysqli_connect_error();
}

# Selecting the created database
mysqli_select_db($conn, 'railway');
 
# Creating the table
$city_table = 'CREATE TABLE IF NOT EXISTS Cities(
City varchar(255) primary key,
Country varchar(255),
Main_Weather varchar(255),
Weather_Description varchar(255),
Temp_Degree decimal(10,2),
Icon_Code varchar(100),
Pressure decimal(10,2),
Wind_Speed decimal(10,2),
Humidity decimal(10,2),
Direction decimal(10,2),
Get_Time DATETIME DEFAULT CURRENT_TIMESTAMP 
);';

# Putting the changes to the MySQL
$result1 = mysqli_query($conn,$city_table);
# Checking for errors in SQL
if($result1){
}else{
    echo 'table not created '.mysqli_connect_error();
}

# Getting the movie name from the Client Side.
if(isset($_GET['t'])){
    $city = $_GET['t'];
}else{
    $city = 'Opelika';
}

function fetchdata($conn,$city){
    # OMDB URL
    $api_key = '5f4671bf9bcb211342029ffcd3ef261c';
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$api_key&units=metric";

    # Fetching the data from the API
    $citydata = file_get_contents($url);
    $data = json_decode($citydata, True);

    # Now taking the necessary data
    $city_name = $data['name'];
    $country = $data['sys']['country'];
    $mainWeather = $data['weather'][0]['main'];
    $weather_desc = $data['weather'][0]['description'];
    $temp_degree = $data['main']['temp'];
    $iconCode = $data['weather'][0]['icon'];
    $pressure = $data['main']['pressure'];
    $windSpeed = $data['wind']['speed'];
    $humidity = $data['main']['humidity'];
    $direction = $data['wind']['deg'];

    # Storing the data in the SQL database
    $sql_insert = "INSERT INTO Cities(City, Country,Main_Weather,Weather_Description,Temp_Degree,Icon_Code,Pressure,Wind_Speed,Humidity,Direction) Values('$city_name','$country','$mainWeather','$weather_desc','$temp_degree','$iconCode','$pressure','$windSpeed','$humidity','$direction')";
    # Checking for any errors while entring the data in the database   
    if(mysqli_query($conn,$sql_insert)){
    }else{
        echo 'Record not Inserted '.mysqli_connect_error();
    }
    $sql_select1 = "SELECT * FROM Cities WHERE City = '$city_name';";
    # Getting the data from MySQL
    $result4 = mysqli_query($conn,$sql_select1);
    # Checking for errors in SQL
    if($result4){
    }else{
        echo 'Data not fetched from database '.mysqli_connect_error();
    }
    $reqData[] = $result4;
    return $reqData;
}

# Selecting all the data of the SQL database
$sql_select = 'SELECT * FROM Cities;';
$sql_delete = "DELETE FROM Cities WHERE City = '$city';";
# Getting the data from MySQL
$result2 = mysqli_query($conn,$sql_select);
# Checking for errors in SQL
if($result2){
}else{
    echo 'Data not fetched from database '.mysqli_connect_error();
}

# Checking if data already exists in the table
if (mysqli_num_rows($result2)==0){
    $finaldata[] = fetchdata($conn, $city);
}else{
    while($row = mysqli_fetch_assoc($result2)){
        if(strtolower($row['City'])==strtolower($city)){
            $lastFetchTime = new DateTime($row['Get_Time']);
            $currentTime = new DateTime();
            $timeDiff = $currentTime->getTimestamp() - $lastFetchTime->getTimestamp();

            if ($timeDiff > 2 * 60 * 60) { // 2 hours in seconds
                $result8 = mysqli_query($conn,$sql_delete);
                # Checking for errors in SQL
                if($result8){
                }else{
                    echo 'Data not able to delete!'.mysqli_connect_error();
                // Fetch new data and update fetch_time
                $finaldata = fetchdata($conn, $city);
                }
            } else {
                // Use existing data
                $finaldata[] = $row ;
            }
        }
    }
    if ($finaldata == []){
        $finaldata = fetchdata($conn, $city);
    }
}

$tobesent = json_encode($finaldata);
header('content-type: application/json');
echo $tobesent

?>
