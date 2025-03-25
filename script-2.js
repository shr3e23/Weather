const searchBox = document.querySelector(".search input");
const searchBtn = document.querySelector(".search button");
const icon = `https://openweathermap.org/img/wn`;

async function getAndDisplayWeather(city) {
    let data;
    const storedData = localStorage.getItem(city);
    if (storedData) {
        data = JSON.parse(storedData);
    } else if (navigator.onLine) {
        const response = await fetch(`http://localhost/prototype3/connection.php?q=${city}`);
        data = await response.json();
        // Save data to localStorage
        localStorage.setItem(city, JSON.stringify(data));
    }  else {
        // Offline: Retrieve data from localStorage
        data = JSON.parse(localStorage.getItem(city));
    }

    // Update HTML elements with weather data
    if (data && data.length > 0) {
        let iconCheck = `${icon}/${data[0].icon}@2x.png`;
        document.querySelector(".city").innerHTML = data[0].city;
        document.querySelector(".temp").innerHTML = Math.round(data[0].temperature) - 273 + "°C";
        document.querySelector(".humidity").innerHTML = data[0].humidity + "%";
        document.querySelector(".wind").innerHTML = data[0].wind + " km/h";
        document.querySelector(".max").innerHTML = Math.round(data[0].temp_max) - 273 + "°C";
        document.querySelector(".min").innerHTML = Math.round(data[0].temp_min) - 273 + "°C";
        document.querySelector(".pressure").innerHTML = data[0].pressure + " hPa";
        document.querySelector(".weather-icon").innerHTML = `<img src=${iconCheck}>`;
        document.querySelector(".discription").innerHTML = data[0].dsc;
        document.querySelector(".time").textContent = data[0].today_date;
        document.querySelector(".day").textContent = data[0].today;
    } else {
        console.error("No data available for the city");
    }
}

// Event listener for search button
document.querySelector('button[type="submit"]').addEventListener("click", async (e) => {
    e.preventDefault();
    const input = document.querySelector('input[name="search"]').value;
    await getAndDisplayWeather(input);
});

// Initial fetch for default city
getAndDisplayWeather("Knowsley");
