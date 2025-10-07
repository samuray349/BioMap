let map;
let sidebar;
let infoBox;

function initMap() {
  const center = { lat: 39.09903420850493, lng: -9.283192320989297 };

  map = new google.maps.Map(document.getElementById("map"), {
    zoom: 12,
    center,
    mapId: "DEMO_MAP_ID",
  });

  sidebar = document.getElementById("sidebar");
  infoBox = document.getElementById("location-info");

  const locations = [
    { position: { lat: 39.098569723610105,  lng: -9.21834924308909 }, title: "Fundação dos animais" },
    { position: { lat: 39.13471130131973, lng: -9.299138410129158 }, title: "Lince Ibérico" },
    { position: { lat: 39.16084345764295, lng: -9.237634072626696 }, title: "Javali" }
  ];

  // Create labeled markers
  locations.forEach(loc => {
    const marker = new google.maps.Marker({
      position: loc.position,
      map,
      title: loc.title,
      label: {
        text: loc.title,
        className: "marker-label"
      },
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 8,
        fillColor: "#198754",
        fillOpacity: 1,
        strokeColor: "white",
        strokeWeight: 2
      }
    });

    marker.addListener("click", () => {
      infoBox.innerHTML = `
        <strong class="text-success">${loc.title}</strong><br>
        Lat: ${loc.position.lat.toFixed(4)}, Lng: ${loc.position.lng.toFixed(4)}
      `;
      openSidebar();
    });
  });

  // Sidebar buttons
  document.getElementById("toggle-menu").addEventListener("click", toggleSidebar);
  document.getElementById("btn-saved").addEventListener("click", () => alert("Saved locations coming soon!"));
  document.getElementById("btn-recent").addEventListener("click", () => alert("Recent locations coming soon!"));
}

function toggleSidebar() {
  sidebar.classList.toggle("open");
  // Force map resize after animation
  setTimeout(() => google.maps.event.trigger(map, "resize"), 310);
}

function openSidebar() {
  sidebar.classList.add("open");
  setTimeout(() => google.maps.event.trigger(map, "resize"), 310);
}
