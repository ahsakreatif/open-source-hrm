<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            map: null,
            marker: null,
            latitude: {{ $getDefaultLatitude() }},
            longitude: {{ $getDefaultLongitude() }},
            init() {
                // Try to get current values from the form
                const currentLat = $wire.get('data.latitude');
                const currentLng = $wire.get('data.longitude');

                if (currentLat && currentLng) {
                    this.latitude = parseFloat(currentLat);
                    this.longitude = parseFloat(currentLng);
                }

                this.$nextTick(() => {
                    this.initMap();
                });
            },
            initMap() {
                // Initialize the map
                this.map = L.map('{{ $getId() }}-map').setView([this.latitude, this.longitude], {{ $getZoom() }});

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(this.map);

                // Add initial marker
                this.marker = L.marker([this.latitude, this.longitude]).addTo(this.map);

                // Handle map clicks
                this.map.on('click', (e) => {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;

                    // Update marker position
                    this.marker.setLatLng([lat, lng]);

                    // Update form fields
                    this.updateCoordinates(lat, lng);
                });
            },
            updateCoordinates(lat, lng) {
                this.latitude = lat;
                this.longitude = lng;

                // Update Filament form state for latitude and longitude fields
                $wire.set('data.latitude', lat.toFixed(6));
                $wire.set('data.longitude', lng.toFixed(6));

                // Also update the map marker position
                if (this.marker) {
                    this.marker.setLatLng([lat, lng]);
                }
            }
        }"
    >
        <div id="{{ $getId() }}-map" style="height: {{ $getHeight() }}; width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db;"></div>

        <div class="mt-2 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                <input
                    type="text"
                    x-model="latitude"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Latitude"
                    readonly
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                <input
                    type="text"
                    x-model="longitude"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Longitude"
                    readonly
                >
            </div>
        </div>

        <p class="mt-2 text-sm text-gray-500">
            Click on the map to set the location coordinates, or manually enter the latitude and longitude values.
        </p>
    </div>
</x-dynamic-component>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush
