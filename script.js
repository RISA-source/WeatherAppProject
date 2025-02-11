// Getting the Elements
const elements = {
    searchButton : document.getElementById('search-button'),
    cityInput : document.getElementById('city'),
    cityName : document.getElementById('city-name'),
    mainWeather : document.getElementById('main-weather'),
    weatherCondition : document.getElementById('weather-condition'),
    weatherImage : document.getElementById('weather-image'),
    dateElement : document.getElementById('day-date'),
    pressureElement : document.getElementById('pressure-data'),
    humidityElement : document.getElementById('humidity-data'),
    windspeedElement : document.getElementById('wind-speed-data'),
    degreeElement : document.getElementById('degree-celsius')
}

// Default City
const defaultCity = 'Opelika';
let firstRun = true;
// Running the code initially. We see the data of defaultCity.
if(firstRun){
    fetchWeatherData(defaultCity);
    firstRun = false;
}

// Date Day Function
function date_day(){
    // Current Date & Date
    const todayDate = new Date()

    const weekDays = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
    const day = weekDays[todayDate.getDay()]; // Day

    const date = todayDate.getDate(); // Date
    const month = months[todayDate.getMonth()]; // Month 
    const year = todayDate.getFullYear(); // Year
    
    // Display in Day, date / month / year format.
    elements.dateElement.innerText = `${day}, ${month} ${date} / ${year}`;
}

// Calling the function for Date and Day.
date_day();

// Checking for Click on Search for City
elements.searchButton.addEventListener('click',function(){
    console.log('Search Button Clicked');
    const city = elements.cityInput.value; // Taking the value from searchbar
    if (city) {
        // Call the function to fetch the weather data
        console.log('Fetching Data...');
        fetchWeatherData(city);
    }else{
        alert('ERROR: Please Enter a City Name')
        throw new Error('Empty Search | No City Name Found!')
    }
})

async function fetchWeatherData(city) {
    // URL for API
    const geo_url = `https://weatherappproject-production.up.railway.app/connection.php?t=${city}`;
    let data = null;
    // Check if the data is available in localStorage
    const storedData = localStorage.getItem(city.toLowerCase());
    
    // If localStorage has data for this city
    if (storedData) {
        console.log('Stored data found....')
        data = JSON.parse(storedData);
        const lastUpdated = new Date(data[0].Get_Time); // Get the time the data was last updated

        const timeDiff = currentTime - lastUpdated;
        console.log(`Found data was last updated at ${lastUpdated}.`)
        console.log('So.... Using cached data!')
    }

    // If no data in localStorage or data is too old, fetch new data
    if (navigator.onLine) {
        const response = await fetch(geo_url);

        if (!response.ok) {
            alert('ERROR: Please give the correct city name or check your internet connection.');
            throw new Error(`${response.statusText}`);
        }

        data = await response.json();
        console.log('Fetched and converted data from API.');

        // Save new data to localStorage
        localStorage.setItem(city.toLowerCase(), JSON.stringify(data));
        console.log('Stored new data to local storage!')
        displayWeatherData(data); // Display the new data

    } else {
        alert("ERROR: No internet connection, and no cached data available.");
    }
}

function displayWeatherData(data){
    // // Storing necessary data
    const country = data[0].Country;
    const place = data[0].City;
    const main = data[0].Main_Weather;
    let description = data[0].Weather_Description;
    const iconCode = data[0].Icon_Code;
    const icon = `https://openweathermap.org/img/wn/${iconCode}@4x.png`
    const pressure = data[0].Pressure;
    const windSpeed = data[0].Wind_Speed;
    const humidity = data[0].Humidity;
    const degree = data[0].Temp_Degree;
    const direction = data[0].Direction;

    // Converting first letter of description to Capital
    description = description.charAt(0).toUpperCase() + description.slice(1);

    // Populating the DOM
    elements.cityName.innerText = `${place}, ${country}`;
    elements.mainWeather.innerText = `${main}`;
    elements.weatherCondition.innerText = `${description}`;
    elements.weatherImage.src = icon
    elements.pressureElement.innerText = `${pressure} hPa`;
    elements.humidityElement.innerText = `${humidity} %`;
    elements.windspeedElement.innerText = `${windSpeed} m/s  ${direction}°`;
    elements.degreeElement.innerText = `${degree}°C`;
}
