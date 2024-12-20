// Função para filtrar os serviços com base na pesquisa
function filterServices() {
  const searchInput = document.getElementById("search-service").value.toLowerCase();
  const services = document.querySelectorAll(".service-item");

  services.forEach((service) => {
    const title = service.getAttribute("data-title").toLowerCase();
    service.style.display = title.includes(searchInput) ? "flex" : "none";
  });
}

// Coordenadas do salão (substitua com os valores reais do banco de dados)
const salonLatitude = -29.754; // Latitude do salão
const salonLongitude = -50.028; // Longitude do salão

// Função para calcular a distância usando a fórmula de Haversine
function calculateDistance(lat1, lon1, lat2, lon2) {
  const earthRadius = 6371; // Raio da Terra em km
  const dLat = (lat2 - lat1) * (Math.PI / 180);
  const dLon = (lon2 - lon1) * (Math.PI / 180);

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * (Math.PI / 180)) *
      Math.cos(lat2 * (Math.PI / 180)) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2);

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return earthRadius * c;
}

// Verifica se o navegador suporta a Geolocation API
if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(
    (position) => {
      const userLatitude = position.coords.latitude;
      const userLongitude = position.coords.longitude;

      // Calcula a distância
      const distance = calculateDistance(
        salonLatitude,
        salonLongitude,
        userLatitude,
        userLongitude
      );

      // Exibe a distância no elemento com o ID "distance"
      document.getElementById("distance").textContent = `${distance.toFixed(
        2
      )} km`;
    },
    (error) => {
      console.error("Erro ao obter localização:", error);
    }
  );
} else {
  console.warn("Geolocalização não é suportada pelo navegador.");
}
