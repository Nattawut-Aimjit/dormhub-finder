let map;
let markers = [];
let dormData = [];
let searchMarker = null;
let dormDataHash = '';
let autoRefreshInterval = null;
const AUTO_REFRESH_SECONDS = 20; // ปรับได้ตามต้องการ

// ✅ เก็บพิกัดจุดค้นหาล่าสุด (ใช้แสดงแมพเล็กในหน้ารายละเอียด)
// จะเป็น null ถ้าผู้ใช้ยังไม่เคยค้นหา (หรือปิด modal ผลค้นหาไปแล้ว)
let currentSearchLat = null;
let currentSearchLon = null;
let currentSearchLocationName = '';

// instance ของแมพเล็กในหน้ารายละเอียด (แยกจากแมพใหญ่หน้าแรก)
let detailMiniMap = null;

// คำนวณระยะทางแบบเดียวกับฝั่ง PHP (Haversine) ไว้ใช้ fallback
// เผื่อหอพักที่กดดูไม่ได้อยู่ในผลการค้นหาล่าสุด (เช่น กดจาก marker บนแมพใหญ่)
function haversineKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

// Load indicator for debugging
console.log('search-dorm.js loaded');

// ไอคอนหมุดสีน้ำเงินสำหรับหอพัก
const dormIcon = L.icon({
    iconUrl: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
    iconSize: [32, 32],
    iconAnchor: [16, 32]
});

// ไอคอนหมุดสีแดงสำหรับตำแหน่งการค้นหา
const searchIcon = L.icon({
    iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
    iconSize: [32, 32],
    iconAnchor: [16, 32]
});

// คืนค่าหน่วยราคาที่แสดงผล ("เดือน" หรือ "คืน") ตามข้อมูล price_unit ของหอพัก
function priceUnitLabel(dorm) {
    return (dorm && dorm.price_unit === 'วัน') ? 'คืน' : 'เดือน';
}

// สร้าง hash คีย์สำหรับตรวจการอัปเดตข้อมูล
function createDormDataHash(dorms) {
    if (!Array.isArray(dorms)) return '';
    const keys = dorms.map(d => `${d.id}|${d.name}|${d.price}|${d.latitude}|${d.longitude}|${d.status || ''}`).sort();
    return btoa(unescape(encodeURIComponent(keys.join('||'))));
}

// เริ่มระบบ auto refresh
function startDormAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(checkDormDataUpdated, AUTO_REFRESH_SECONDS * 1000);
    console.log(`Auto refresh every ${AUTO_REFRESH_SECONDS} seconds started`);
}

function stopDormAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

function checkDormDataUpdated() {
    fetch('connect.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=getAllDormitories'
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success || !Array.isArray(data.dorms)) return;
            const newHash = createDormDataHash(data.dorms);
            if (dormDataHash && dormDataHash !== newHash) {
                console.info('พบข้อมูลหอพักเปลี่ยนแปลง -> reload เพื่อให้ทุกเครื่องอัปเดตทันที');
                window.location.reload(true);
            }
            dormDataHash = newHash;
        })
        .catch(err => {
            console.warn('checkDormDataUpdated failed', err);
        });
}

// Debug helper: open modal manually from console
function debugShowResultsModal() {
    console.log('debugShowResultsModal called');
    const resultsModal = document.getElementById('resultsModal');
    const cards = document.getElementById('dormCards');
    if (resultsModal) {
        resultsModal.style.display = 'block';
        try { document.body.style.overflow = 'hidden'; } catch (e) { }
        if (cards) cards.innerHTML = '<div class="no-results">ทดสอบ: เปิด modal โดยคำสั่ง debugShowResultsModal()</div>';
    } else {
        console.warn('resultsModal element not found');
        alert('ไม่ได้พบ modal (#resultsModal) ใน DOM');
    }
}
window.debugShowResultsModal = debugShowResultsModal;

// เริ่มต้นแมพ
function initMap() {
    map = L.map('map').setView([13.1171, 99.9440], 13); // Phetchaburi center

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // พยายาม force render map ให้ถูกต้องตอน container ขนาดเปลี่ยน
    setTimeout(function () {
        if (map && map.invalidateSize) {
            map.invalidateSize();
        }
    }, 100);

    // โหลดหอพักทั้งหมดตอนเปิดหน้า
    loadAllDormitories();
}

// โหลดหอพักทั้งหมดและแสดง markers บนแมพ
function loadAllDormitories() {
    fetch("connect.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "action=getAllDormitories"
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.dorms.length > 0) {
                dormData = data.dorms;
                dormDataHash = createDormDataHash(data.dorms);
                displayAllMarkers(data.dorms);
                startDormAutoRefresh();
            }
        })
        .catch(error => {
            console.error("โหลดหอพักไม่สำเร็จ: " + error);
        });
}

// แสดง markers ของหอพักทั้งหมดบนแมพ (โดยไม่สร้าง Cards)
function displayAllMarkers(dorms) {
    dorms.forEach((dorm) => {
        if (dorm.latitude && dorm.longitude) {
            const img = dorm.image && dorm.image.length ? dorm.image : 'Dormhub-no.jpg';
            const popupContent = `
                <div style="width:220px;font-family:Arial,sans-serif;">
                    <div style="width:100%;height:110px;overflow:hidden;border-radius:6px;">
                        <img src="${img}" style="width:100%;height:110px;object-fit:cover;display:block;"/>
                    </div>
                    <div style="padding:6px;">
                        <div style="font-weight:700;margin-bottom:4px;">${dorm.name}</div>
                        <div style="color:#333;">฿${parseInt(dorm.price).toLocaleString()} /${priceUnitLabel(dorm)}</div>
                        <div style="font-size:12px;color:#666;margin-top:4px;">${dorm.area}</div>
                        <button class="dorm-card-button" onclick="viewDetails(${dorm.id})">ดูรายละเอียด</button>
                    </div>
                </div>`;

            const marker = L.marker([dorm.latitude, dorm.longitude], { icon: dormIcon }).addTo(map);
            marker.bindPopup(popupContent);
            marker.on('click', function () { this.openPopup(); });
            markers.push(marker);
        }
    });
}

// ===== ระบบค้นหาอัจฉริยะ =====

// ค่าเริ่มต้น: กรอบพื้นที่จังหวัดเพชรบุรี (ใช้ตอนยังตรวจตำแหน่งผู้ใช้ไม่ได้/ไม่อนุญาต)
// รูปแบบ Nominatim viewbox: left(lon_min),top(lat_max),right(lon_max),bottom(lat_min)
const PHETCHABURI_DEFAULT_VIEWBOX = '99.2,13.55,100.25,12.35';

// ✅ กรอบพื้นที่ที่ใช้ไบแอสการค้นหาจริง — จะถูกอัปเดตเป็น "จังหวัดที่ผู้ใช้อยู่จริง" อัตโนมัติ
// ถ้าตรวจไม่ได้ (ไม่อนุญาต GPS/เบราว์เซอร์ไม่รองรับ) จะใช้ค่าเริ่มต้น (เพชรบุรี) แทน
let currentViewbox = PHETCHABURI_DEFAULT_VIEWBOX;
let currentUserProvince = ''; // ชื่อจังหวัดที่ตรวจพบ (โชว์ให้ผู้ใช้เห็นเฉยๆ)

// ตรวจตำแหน่งปัจจุบันของผู้ใช้ (ขอสิทธิ์ผ่านเบราว์เซอร์) แล้วแปลงเป็น "กรอบจังหวัด" สำหรับไบแอสการค้นหา
// ทำครั้งเดียวตอนโหลดหน้า เพื่อป้องกันการค้นหาสถานที่ชื่อซ้ำข้ามจังหวัดแบบมั่วๆ
function detectUserProvinceAndBias() {
    if (!navigator.geolocation) {
        console.log('เบราว์เซอร์นี้ไม่รองรับ Geolocation — ใช้ค่าเริ่มต้น (เพชรบุรี)');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            try {
                const { latitude, longitude } = pos.coords;

                // 1) reverse geocode หาว่าอยู่จังหวัดอะไร
                const revUrl = `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json&addressdetails=1`;
                const revData = await fetch(revUrl, { headers: { 'Accept-Language': 'th' } }).then(r => r.json());
                const state = revData && revData.address ? (revData.address.state || revData.address.province) : null;
                if (!state) {
                    console.warn('หาชื่อจังหวัดจากตำแหน่งไม่เจอ — ใช้ค่าเริ่มต้น (เพชรบุรี)');
                    return;
                }

                // 2) หากรอบพื้นที่ (boundingbox) ของทั้งจังหวัดนั้น
                const provUrl = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(state)}&format=json&limit=1&countrycodes=th`;
                const provData = await fetch(provUrl, { headers: { 'Accept-Language': 'th' } }).then(r => r.json());

                if (Array.isArray(provData) && provData.length > 0 && Array.isArray(provData[0].boundingbox)) {
                    const bb = provData[0].boundingbox.map(parseFloat); // Nominatim: [south, north, west, east]
                    const [south, north, west, east] = bb;
                    currentViewbox = `${west},${north},${east},${south}`; // แปลงเป็นฟอร์แมต viewbox: left,top,right,bottom
                    currentUserProvince = state;
                    console.log('✅ ตรวจพบตำแหน่งผู้ใช้ -> ไบแอสการค้นหาไปที่จังหวัด:', state, currentViewbox);
                    updateSearchLocationHint();
                }
            } catch (e) {
                console.warn('ตรวจจับจังหวัดจากตำแหน่งล้มเหลว ใช้ค่าเริ่มต้น (เพชรบุรี):', e);
            }
        },
        (err) => {
            // ผู้ใช้ไม่อนุญาต หรือดึงตำแหน่งไม่สำเร็จ -> ไม่เป็นไร ใช้ค่าเริ่มต้น (เพชรบุรี) ต่อไปเงียบๆ
            console.warn('ไม่ได้รับอนุญาตตำแหน่ง หรือดึงตำแหน่งไม่สำเร็จ ใช้ค่าเริ่มต้น (เพชรบุรี):', err.message);
        },
        { enableHighAccuracy: false, timeout: 8000, maximumAge: 10 * 60 * 1000 }
    );
}

// แสดง hint เล็กๆ ใต้ช่องค้นหา บอกว่ากำลังอิงพื้นที่จังหวัดไหนอยู่
function updateSearchLocationHint() {
    const hint = document.getElementById('searchLocationHint');
    if (hint && currentUserProvince) {
        hint.textContent = `📍 กำลังอิงพื้นที่ค้นหาแถบ: ${currentUserProvince}`;
        hint.style.display = 'inline-flex';
    }
}

// สถานที่สำคัญที่ล็อกพิกัดไว้ล่วงหน้า (ตอบเร็ว แม่นยำ ไม่ต้องพึ่ง geocode ภายนอก)
const lockedPlaces = {
    'ราชภัฏเพชรบุรี': { lat: 13.1171, lon: 99.9440, label: 'มหาวิทยาลัยราชภัฏเพชรบุรี' }
    // เพิ่มสถานที่ล็อกอื่นๆ ที่นี่ได้ เช่น
    // 'บิ๊กซีเพชรบุรี': { lat: 13.1089, lon: 99.9421, label: 'บิ๊กซี เพชรบุรี' }
};

function normalizePlaceText(s) {
    return (s || '').toString().replace(/\s+/g, '').toLowerCase();
}

// Levenshtein distance (แบบง่าย) — ใช้เทียบความคล้ายคำ รองรับพิมพ์ผิด/พิมพ์ตกหล่นนิดหน่อย
function levenshtein(a, b) {
    a = a || ''; b = b || '';
    const m = a.length, n = b.length;
    if (m === 0) return n;
    if (n === 0) return m;
    const dp = new Array(n + 1);
    for (let j = 0; j <= n; j++) dp[j] = j;
    for (let i = 1; i <= m; i++) {
        let prev = dp[0];
        dp[0] = i;
        for (let j = 1; j <= n; j++) {
            const temp = dp[j];
            dp[j] = a[i - 1] === b[j - 1] ? prev : 1 + Math.min(prev, dp[j], dp[j - 1]);
            prev = temp;
        }
    }
    return dp[n];
}

// หาสถานที่ล็อกที่ใกล้เคียงที่สุด — ทนพิมพ์ผิดได้ในระดับหนึ่ง
function matchLockedPlace(searchText) {
    const n = normalizePlaceText(searchText);
    if (!n) return null;
    let best = null, bestDist = Infinity;
    for (const [placeName, coords] of Object.entries(lockedPlaces)) {
        const pn = normalizePlaceText(placeName);
        if (n === pn) return coords;
        const dist = levenshtein(n, pn);
        const threshold = Math.max(2, Math.floor(pn.length * 0.25));
        if (dist <= threshold && dist < bestDist) {
            bestDist = dist;
            best = coords;
        }
    }
    return best;
}

function shortenPlaceName(displayName) {
    if (!displayName) return '';
    const parts = displayName.split(',').map(s => s.trim());
    return parts.slice(0, 2).join(', ');
}

// ===== Autocomplete: ดึงคำแนะนำ (ชื่อหอพัก + สถานที่) ขณะพิมพ์ =====
let suggestDebounceTimer = null;
let suggestRequestSeq = 0; // กันปัญหาผลลัพธ์เก่ามาถึงทีหลังผลลัพธ์ใหม่ (race condition)
let currentSuggestionItems = [];

function scheduleSuggestFetch(text) {
    clearTimeout(suggestDebounceTimer);
    if (!text || text.trim().length < 2) {
        closeSuggestionBox();
        currentSuggestionItems = [];
        return;
    }
    suggestDebounceTimer = setTimeout(() => fetchSuggestions(text.trim()), 300);
}

async function fetchSuggestions(text) {
    const mySeq = ++suggestRequestSeq;
    showSuggestLoading();

    const dormPromise = fetch('connect.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=searchSuggestions&q=${encodeURIComponent(text)}`
    }).then(r => r.json()).catch(() => ({ success: false, dorms: [] }));

    const placeUrl = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(text)}&format=json&limit=5&countrycodes=th&viewbox=${currentViewbox}&bounded=1&addressdetails=1`;
    const placePromise = fetch(placeUrl, { headers: { 'Accept-Language': 'th' } })
        .then(r => r.json()).catch(() => []);

    const [dormRes, placeRes] = await Promise.all([dormPromise, placePromise]);

    if (mySeq !== suggestRequestSeq) return; // มีคำค้นใหม่กว่าเข้ามาแล้ว ทิ้งผลนี้ไป

    const dormSuggestions = (dormRes && dormRes.success && Array.isArray(dormRes.dorms)) ? dormRes.dorms : [];
    const placeSuggestions = Array.isArray(placeRes) ? placeRes : [];

    currentSuggestionItems = [
        ...dormSuggestions.map(d => ({ type: 'dorm', data: d })),
        ...placeSuggestions.map(p => ({ type: 'place', data: p }))
    ];

    renderSuggestions();
}

function showSuggestLoading() {
    const box = document.getElementById('searchSuggestions');
    if (!box) return;
    box.innerHTML = '<div class="suggest-loading">กำลังค้นหา...</div>';
    box.classList.add('show');
}

function renderSuggestions() {
    const box = document.getElementById('searchSuggestions');
    if (!box) return;

    if (currentSuggestionItems.length === 0) {
        box.innerHTML = '<div class="suggest-empty">ไม่พบคำแนะนำ ลองพิมพ์คำอื่น หรือกดค้นหาต่อได้เลย</div>';
        box.classList.add('show');
        return;
    }

    box.innerHTML = currentSuggestionItems.map((item, idx) => {
        if (item.type === 'dorm') {
            const d = item.data;
            return `<div class="suggest-item" onclick="selectSuggestionByIndex(${idx})">
                <span class="suggest-tag dorm">หอพัก</span>
                <div class="suggest-main">
                    <div class="suggest-title">${escapeHtml(d.name)}</div>
                    <div class="suggest-sub">${escapeHtml(d.area || d.address || '')}</div>
                </div>
            </div>`;
        } else {
            const p = item.data;
            const shortLabel = shortenPlaceName(p.display_name);
            return `<div class="suggest-item" onclick="selectSuggestionByIndex(${idx})">
                <span class="suggest-tag place">สถานที่</span>
                <div class="suggest-main">
                    <div class="suggest-title">${escapeHtml(shortLabel)}</div>
                    <div class="suggest-sub">${escapeHtml(p.display_name)}</div>
                </div>
            </div>`;
        }
    }).join('');

    box.classList.add('show');
}

function selectSuggestionByIndex(idx) {
    const item = currentSuggestionItems[idx];
    if (!item) return;
    if (item.type === 'dorm') {
        selectDormSuggestion(item.data);
    } else {
        selectPlaceSuggestion(item.data);
    }
}

// ผู้ใช้เลือกหอพักจากคำแนะนำโดยตรง -> เปิดหน้ารายละเอียดหอพักนั้นเลย
function selectDormSuggestion(dorm) {
    closeSuggestionBox();
    const input = document.getElementById('searchLocation');
    if (input) input.value = dorm.name;
    viewDetails(dorm.id);
}

// ผู้ใช้เลือกสถานที่จากคำแนะนำ -> ค้นหาหอพักใกล้จุดนั้นทันที (ไม่ต้อง geocode ซ้ำ)
function selectPlaceSuggestion(place) {
    closeSuggestionBox();
    const label = shortenPlaceName(place.display_name);
    const input = document.getElementById('searchLocation');
    if (input) input.value = label;
    runLocationSearch(parseFloat(place.lat), parseFloat(place.lon), label);
}

function closeSuggestionBox() {
    const box = document.getElementById('searchSuggestions');
    if (box) { box.classList.remove('show'); box.innerHTML = ''; }
}

// ===== ค้นหาหลัก: กดปุ่ม/กด Enter =====
function searchDormitory() {
    const input = document.getElementById("searchLocation");
    const searchText = input.value.trim();

    console.log('searchDormitory()', { searchText });

    if (!searchText) {
        alert("กรุณากรอกสถานที่ที่ต้องการค้นหา");
        return;
    }

    closeSuggestionBox();

    // 1) เช็คสถานที่ที่ล็อกไว้ล่วงหน้าก่อน (แม่นยำสุด ทนพิมพ์ผิดเล็กน้อยได้)
    const locked = matchLockedPlace(searchText);
    if (locked) {
        console.log('Using locked place coords:', locked);
        runLocationSearch(locked.lat, locked.lon, locked.label || searchText);
        return;
    }

    // 2) ถ้ามีคำแนะนำจาก autocomplete อยู่แล้วสำหรับข้อความปัจจุบัน ใช้ตัวที่ดีที่สุด (อันดับแรก) เลย
    //    ครอบคลุมกรณีพิมพ์ไม่ครบ/พิมพ์เพี้ยนนิดหน่อยแล้วกด Enter โดยไม่ได้เลือกจาก dropdown
    if (currentSuggestionItems.length > 0) {
        selectSuggestionByIndex(0);
        return;
    }

    // 3) ไม่มีคำแนะนำเลย (เช่น debounce ยังไม่ทันโหลด) -> ลอง geocode ตรงๆ เป็นทางเลือกสุดท้าย
    const resultsModalInit = document.getElementById("resultsModal");
    const cardsContainerInit = document.getElementById("dormCards");
    if (resultsModalInit) {
        resultsModalInit.style.display = "block";
        try { document.body.style.overflow = 'hidden'; } catch (e) { }
    }
    if (cardsContainerInit) {
        cardsContainerInit.innerHTML = '<div class="no-results">กำลังค้นหา...</div>';
    }

    attemptClientGeocode(searchText).then(coords => {
        if (coords) {
            console.log('Client geocode found:', coords);
            runLocationSearch(coords.lat, coords.lon, searchText);
        } else {
            console.log('Client geocode failed, fallback to server');
            runLocationSearch(null, null, searchText);
        }
    }).catch(err => {
        console.warn('Geocode error:', err);
        runLocationSearch(null, null, searchText);
    });
}

// ✅ ทำการค้นหาจริง เมื่อรู้พิกัดปลายทางแล้ว (ใช้ร่วมกันทั้งจากปุ่มค้นหา และการเลือกคำแนะนำ)
function runLocationSearch(lat, lon, label) {
    const priceRange = document.getElementById("priceFilter").value || 'all';

    const resultsModalInit = document.getElementById("resultsModal");
    const cardsContainerInit = document.getElementById("dormCards");
    if (resultsModalInit) {
        resultsModalInit.style.display = "block";
        try { document.body.style.overflow = 'hidden'; } catch (e) { }
    }
    if (cardsContainerInit) {
        cardsContainerInit.innerHTML = '<div class="no-results">กำลังค้นหา...</div>';
    }

    if (lat != null && lon != null && map) {
        map.setView([lat, lon], 14);
        if (searchMarker) map.removeLayer(searchMarker);
        searchMarker = L.marker([lat, lon], { icon: searchIcon }).addTo(map);
    }

    let body = `action=searchDormitory&location=${encodeURIComponent(label || '')}&priceRange=${encodeURIComponent(priceRange)}&bias=${encodeURIComponent(currentViewbox)}`;
    if (lat != null && lon != null) body += `&searchLat=${encodeURIComponent(lat)}&searchLon=${encodeURIComponent(lon)}`;

    fetch("connect.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body
    })
        .then(response => response.json())
        .then(data => {
            console.log('search response:', data);
            if (data.success) {
                dormData = data.dorms;
                // ✅ จำจุดค้นหาไว้ ใช้ตอนเปิดดูรายละเอียดหอพัก (แมพเล็ก + ระยะทาง)
                currentSearchLat = data.searchLat;
                currentSearchLon = data.searchLon;
                currentSearchLocationName = data.searchLocation || label || '';
                console.log('displayResults will be called, results count=', data.dorms.length);
                displayResults(data.dorms, data.searchLocation, data.searchLat, data.searchLon);
            } else {
                document.getElementById("dormCards").innerHTML = '<div class="no-results">' + (data.message || "ไม่พบผลลัพธ์") + '</div>';
                const resultsModal = document.getElementById("resultsModal");
                if (resultsModal) resultsModal.style.display = "block";
                if (data.searchLat && data.searchLon && map) {
                    map.setView([data.searchLat, data.searchLon], 14);
                }
            }
        })
        .catch(error => {
            alert("เกิดข้อผิดพลาด: " + error);
        });
}

// ✅ ฟังก์ชัน Geocode ฝั่งลูกค้า — พยายามหลายรูปแบบ (ลองแบบจำกัดในจังหวัดก่อน แล้วค่อยขยายวงกว้างขึ้น)
async function attemptClientGeocode(searchText) {
    const geocodeWithOptions = (url) => fetch(url, { headers: { 'Accept-Language': 'th' } }).then(r => r.json());

    function normalizeText(s) {
        return (s || '').toString().replace(/\s+/g, '').toLowerCase();
    }

    function isGoodType(r) {
        const goodTypes = ['city', 'town', 'village', 'county', 'state', 'university', 'college', 'administrative', 'borough', 'suburb'];
        const cls = (r.class || '').toLowerCase();
        const typ = (r.type || '').toLowerCase();
        if (goodTypes.includes(typ)) return true;
        if (goodTypes.includes(cls)) return true;
        return false;
    }

    function pickBestResult(results, searchText) {
        if (!Array.isArray(results) || results.length === 0) return null;
        const n = normalizeText(searchText);
        // 1) exact name contains
        for (const r of results) {
            if (normalizeText(r.display_name).includes(n)) return r;
        }
        // 2) preferred types
        for (const r of results) {
            if (isGoodType(r)) return r;
        }
        // 3) fall back to first
        return results[0];
    }

    const urls = [
        // ✅ ลองจำกัดในจังหวัดเพชรบุรีก่อน ป้องกันไปเจอสถานที่ชื่อซ้ำกันคนละจังหวัด
        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(searchText)}&format=json&limit=5&countrycodes=th&viewbox=${currentViewbox}&bounded=1`,
        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(searchText)}&format=json&limit=5&countrycodes=th`,
        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(searchText)}&format=json&limit=5`,
        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(searchText + ', Thailand')}&format=json&limit=5`
    ];

    for (const url of urls) {
        try {
            const geo = await geocodeWithOptions(url);
            if (Array.isArray(geo) && geo.length > 0) {
                const best = pickBestResult(geo, searchText);
                if (best) {
                    return { lat: parseFloat(best.lat), lon: parseFloat(best.lon) };
                }
            }
        } catch (e) {
            console.warn('geocode attempt failed for', url, e);
        }
    }
    return null;
}

// ✅ แสดงผลลัพธ์ — เรียงจากใกล้สุด
function displayResults(dorms, searchLocation, searchLat, searchLon) {
    const cardsContainer = document.getElementById("dormCards");
    const resultsModal = document.getElementById("resultsModal");
    console.log('displayResults invoked, dorms length =', dorms && dorms.length);
    if (resultsModal) {
        console.log('showing results modal');
        resultsModal.style.display = "block";
        // Prevent background scroll while modal open
        try { document.body.style.overflow = 'hidden'; } catch (e) { }
    } else {
        console.warn('resultsModal element not found');
        // Fallback: simple alert so user knows there are results
        if (dorms && dorms.length > 0) {
            alert('พบ ' + dorms.length + ' ผลลัพธ์ (modal ไม่ถูกพบ)');
        } else {
            alert('ไม่พบผลลัพธ์');
        }
    }

    cardsContainer.innerHTML = "";

    if (dorms.length === 0) {
        cardsContainer.innerHTML = '<div class="no-results">ไม่พบหอพักใกล้เคียง</div>';
        if (searchLat && searchLon) {
            map.setView([searchLat, searchLon], 14);
        }
        return;
    }

    // ล้าง markers ของผลลัพธ์เดิม
    let resultMarkers = markers.filter(m => m._popup && m._popup._content.includes('กม'));
    resultMarkers.forEach(marker => map.removeLayer(marker));
    markers = markers.filter(m => !m._popup || !m._popup._content.includes('กม'));

    // ✅ เรียงลำดับตามระยะทาง (ใกล้สุดเป็นอันดับแรก)
    dorms.sort((a, b) => a.distance - b.distance);

    // สร้าง Card สำหรับแต่ละหอพัก
    const resultMarkersLocal = [];
    dorms.forEach((dorm, index) => {
        // สร้าง Card
        const card = document.createElement("div");
        card.className = "dorm-card";
        card.innerHTML = `
            <img src="${dorm.image}" alt="${dorm.name}" class="dorm-card-image">
            <div class="dorm-card-content">
                <div class="dorm-card-name">${dorm.name}</div>
                <div class="dorm-card-price">฿${parseInt(dorm.price).toLocaleString()} /${priceUnitLabel(dorm)}</div>
                <div class="dorm-card-info">
                    <div><strong>ที่ตั้ง:</strong> ${dorm.address}</div>
                    <div class="dorm-card-distance"><strong>ระยะทาง:</strong> ${dorm.distance.toFixed(2)} กม.</div>
                    <div><strong>ประเภท:</strong> ${dorm.type}</div>
                    <div><strong>สิ่งอำนวยความสะดวก:</strong> ${dorm.facilities}</div>
                </div>
                <button class="dorm-card-button" onclick="viewDetails(${dorm.id})">ดูรายละเอียด</button>
            </div>
        `;
        cardsContainer.appendChild(card);

        // เพิ่ม Marker บนแมพ (มี popup แสดงรูป+ชื่อ+ราคา) และเปิด popup เมื่อ click
        const img = dorm.image && dorm.image.length ? dorm.image : 'รูป/Dormhub-no.png';
        const popupContent = `
            <div style="width:220px;font-family:Arial,sans-serif;">
                <div style="width:100%;height:110px;overflow:hidden;border-radius:6px;">
                    <img src="${img}" style="width:100%;height:110px;object-fit:cover;display:block;"/>
                </div>
                <div style="padding:6px;">
                    <div style="font-weight:700;margin-bottom:4px;">${dorm.name}</div>
                    <div style="color:#333;">฿${parseInt(dorm.price).toLocaleString()} /${priceUnitLabel(dorm)}</div>
                    <div style="font-size:12px;color:#666;margin-top:4px;">${dorm.distance.toFixed(2)} กม.</div>
                </div>
            </div>`;

        const marker = L.marker([dorm.latitude, dorm.longitude], { icon: dormIcon }).addTo(map);
        marker.bindPopup(popupContent);
        marker.on('click', function () { this.openPopup(); });
        markers.push(marker);
        resultMarkersLocal.push(marker);
    });

    // Pan แมพไปที่พิกัดของการค้นหา แล้ว zoom ให้เห็นผลลัพธ์
    if (searchLat && searchLon) {
        map.setView([searchLat, searchLon], 14);

        if (resultMarkersLocal.length > 0) {
            setTimeout(() => {
                const group = new L.featureGroup(resultMarkersLocal);
                const bounds = group.getBounds();
                bounds.extend([searchLat, searchLon]);
                map.fitBounds(bounds.pad(0.1));
            }, 100);
        }
    } else if (resultMarkersLocal.length > 0) {
        const group = new L.featureGroup(resultMarkersLocal);
        map.fitBounds(group.getBounds().pad(0.1));
    }
}

// ปิด Modal ผลการค้นหา
function closeResultsModal() {
    const resultsModal = document.getElementById("resultsModal");
    if (resultsModal) resultsModal.style.display = "none";
    try { document.body.style.overflow = ''; } catch (e) { }

    // เอา marker ของผลลัพธ์การค้นหาออก (popup ที่มีคำว่า 'กม')
    try {
        const resultMarkers = markers.filter(m => m._popup && m._popup._content && m._popup._content.includes('กม'));
        resultMarkers.forEach(marker => {
            try { if (map && map.hasLayer && map.hasLayer(marker)) map.removeLayer(marker); } catch (e) { }
            markers = markers.filter(m => m !== marker);
        });
    } catch (e) {
        console.warn('Error removing result markers:', e);
    }

    // เอา searchMarker (ตำแหน่งการค้นหา) ออก
    try {
        if (searchMarker) {
            if (map && map.hasLayer && map.hasLayer(searchMarker)) map.removeLayer(searchMarker);
            searchMarker = null;
        }
    } catch (e) { searchMarker = null; }

    // ล้างเนื้อหา results และคืนค่า input/filters
    try {
        const cards = document.getElementById('dormCards');
        if (cards) cards.innerHTML = '';
        const input = document.getElementById('searchLocation');
        const price = document.getElementById('priceFilter');
        if (input) input.value = '';
        if (price) price.value = 'all';
    } catch (e) { }

    // รีเซ็ตมุมมองแผนที่กลับไปยังค่าเริ่มต้น
    try {
        if (map && typeof map.setView === 'function') map.setView([13.1171, 99.9440], 13);
    } catch (e) { }

    // ✅ ล้างจุดค้นหาที่จำไว้ — ถ้าปิดผลค้นหาแล้ว การเปิดดูรายละเอียดต่อไปจะไม่มีแมพเล็ก
    currentSearchLat = null;
    currentSearchLon = null;
    currentSearchLocationName = '';
}


// ดูรายละเอียด
function viewDetails(dormId) {
    // Fetch dorm details from server and show in modal
    fetch('connect.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=getDormById&id=${encodeURIComponent(dormId)}`
    })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'ไม่พบข้อมูล');
                return;
            }
            const d = data.dorm;
            const modal = document.getElementById('detailModal');
            const container = document.getElementById('detailContent');
            // Build gallery HTML
            let images = [];
            if (d.images) {
                try { images = JSON.parse(d.images); } catch (e) { images = []; }
            }
            if (!images || images.length === 0) {
                if (d.image) images = [d.image];
            }
            // ถ้าอาร์เรย์ถูกเก็บในลำดับย้อนกลับ (พบในบางกรณี) ให้กลับลำดับ
            // ผู้ใช้คาดหวังว่าไฟล์ที่อัพโหลดแรกจะอยู่ก่อนสุด
            if (Array.isArray(images) && images.length > 1) {
                // check if first element seems to be newest by simple heuristic?
                // สำหรับตอนนี้ให้เรากลับลำดับเสมอ (จะไม่เสียหายถ้าลำดับถูกต้องอยู่แล้ว)
                images.reverse();
            }

            let galleryHtml = '';
            if (images && images.length > 0) {
                galleryHtml += `<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                <div style="flex:1 1 60%;">
                    <img id="detailMainImage" src="${images[0]}" style="width:100%;height:420px;object-fit:cover;border-radius:8px;" />
                </div>
                <div style="flex:1 1 35%;display:flex;flex-direction:column;gap:8px;max-height:420px;overflow:auto;">
            `;
                images.forEach((img, idx) => {
                    galleryHtml += `<img src="${img}" data-idx="${idx}" style="width:100%;height:80px;object-fit:cover;border-radius:6px;cursor:pointer;" onclick="document.getElementById('detailMainImage').src='${img}'" />`;
                });
                galleryHtml += '</div></div>';
            } else {
                galleryHtml = '<div style="margin-bottom:12px;color:#666;">ไม่มีรูปภาพ</div>';
            }

            // ✅ เตรียมข้อมูลแมพเล็ก — แสดงเฉพาะกรณีมีจุดค้นหาอยู่ (ผู้ใช้ค้นหามาก่อนหน้านี้)
            const hasSearchContext = (currentSearchLat != null && currentSearchLon != null);
            const dormLat = d.latitude !== null && d.latitude !== undefined ? parseFloat(d.latitude) : null;
            const dormLon = d.longitude !== null && d.longitude !== undefined ? parseFloat(d.longitude) : null;
            const showMiniMap = hasSearchContext && dormLat !== null && dormLon !== null && !isNaN(dormLat) && !isNaN(dormLon);

            let distanceKm = null;
            if (showMiniMap) {
                // ใช้ค่าที่คำนวณไว้แล้วตอนค้นหา (ให้ตัวเลขตรงกับใน card ผลการค้นหา) ถ้าหาไม่เจอค่อย fallback คำนวณเอง
                const matched = Array.isArray(dormData) ? dormData.find(x => String(x.id) === String(d.id)) : null;
                distanceKm = (matched && matched.distance != null && matched.distance < 99999)
                    ? matched.distance
                    : haversineKm(currentSearchLat, currentSearchLon, dormLat, dormLon);
            }

            const mapHtml = showMiniMap ? `
            <div style="margin-bottom:16px;">
                <div style="color:#333;margin-bottom:6px;"><strong>ระยะทางจากจุดที่ค้นหา${currentSearchLocationName ? ' (' + escapeHtml(currentSearchLocationName) + ')' : ''}:</strong> ${Number(distanceKm).toFixed(2)} กม.</div>
                <div id="detailMiniMap" style="width:100%;height:260px;border-radius:8px;overflow:hidden;"></div>
            </div>` : '';

            const infoHtml = `
            <h2 style="margin-top:0;">${escapeHtml(d.name)}</h2>
            <div style="margin-bottom:12px;">
                <div style="color:#333;margin-bottom:6px;"><strong>ราคาเริ่มต้น:</strong> ฿${Number(d.price).toLocaleString()} /${priceUnitLabel(d)}</div>
                <div style="color:#333;margin-bottom:6px;"><strong>ประเภท:</strong> ${escapeHtml(d.type)}</div>
                <div style="color:#333;margin-bottom:6px;"><strong>สถานที่ใกล้เคียง:</strong> ${escapeHtml(d.area)}</div>
            </div>
            <div style="color:#333;margin-bottom:12px;"><strong>ที่อยู่:</strong> ${escapeHtml(d.address)}</div>
            ${mapHtml}
            ${d.price_unit === 'วัน' ? '' : `
            <div style="color:#333;margin-bottom:12px;"><strong>นำ้:</strong> ฿${Number(d.water_rate).toLocaleString()} /หน่วย</div>
            <div style="color:#333;margin-bottom:12px;"><strong>ไฟฟ้า:</strong> ฿${Number(d.electricity_rate).toLocaleString()} /หน่วย</div>`}
            <div style="color:#333;margin-bottom:12px;"><strong>สิ่งอำนวยความสะดวก:</strong> ${escapeHtml(d.facilities)}</div>
            <div style="margin-top:20px; padding-top:15px; border-top:1px solid #ddd;">
                ${window.isViewOnlyMode ?
                    '<p style="color:#666; font-size:14px;">📧 กรุณา <a href="javascript:void(0)" onclick="closeDetailModal(); openLoginModal();" style="color:#4FB8C4;font-weight:600;">เข้าสู่ระบบ</a> เพื่อติดต่อหอพัก</p>' :
                    `<a href="${d.contact_link || '#'}" target="_blank" class="contact-btn" style="display:inline-block;background:#00b900;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;transition:0.3s;cursor:pointer;">📞 ติดต่อหอพัก</a>`
                }
            </div>
        `;

            container.innerHTML = galleryHtml + infoHtml;
            modal.style.display = 'block';
            try { document.body.style.overflow = 'hidden'; } catch (e) { }

            // ✅ สร้างแมพเล็ก หลังจาก DOM ถูกแทรกแล้วเท่านั้น (ต้องมี div#detailMiniMap อยู่จริงก่อน)
            if (showMiniMap) {
                initDetailMiniMap(dormLat, dormLon, currentSearchLat, currentSearchLon, distanceKm);
            }
        })
        .catch(err => { console.error(err); alert('เกิดข้อผิดพลาด'); });
}

// ✅ สร้าง/รีเซ็ตแมพเล็กในหน้ารายละเอียด: หมุดหอพัก + หมุดจุดค้นหา + เส้นเชื่อมระยะทาง
function initDetailMiniMap(dormLat, dormLon, searchLat, searchLon, distanceKm) {
    // ล้างแมพเก่าก่อนเสมอ ป้องกัน error "Map container is already initialized"
    if (detailMiniMap) {
        try { detailMiniMap.remove(); } catch (e) { }
        detailMiniMap = null;
    }

    const el = document.getElementById('detailMiniMap');
    if (!el) return;

    detailMiniMap = L.map('detailMiniMap', { zoomControl: true, attributionControl: false });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(detailMiniMap);

    const dormPos = [dormLat, dormLon];
    const searchPos = [searchLat, searchLon];

    const dormMarker = L.marker(dormPos, { icon: dormIcon }).addTo(detailMiniMap)
        .bindPopup('ตำแหน่งหอพักนี้');
    const searchMarkerMini = L.marker(searchPos, { icon: searchIcon }).addTo(detailMiniMap)
        .bindPopup(currentSearchLocationName ? `จุดที่ค้นหา: ${escapeHtml(currentSearchLocationName)}` : 'จุดที่ค้นหา');

    const line = L.polyline([dormPos, searchPos], { color: '#4FB8C4', weight: 3, dashArray: '6,6' }).addTo(detailMiniMap);

    // แสดงระยะทางกลางเส้น (tooltip ค้างอยู่ตลอด)
    line.bindTooltip(`${Number(distanceKm).toFixed(2)} กม.`, { permanent: true, direction: 'center', className: 'distance-tooltip' });

    detailMiniMap.fitBounds(L.latLngBounds([dormPos, searchPos]).pad(0.25));

    // แก้ปัญหาแมพเบี้ยว เพราะ modal เพิ่งถูกแสดงตอน initialize (container อาจยังไม่มีขนาดจริง)
    setTimeout(() => { if (detailMiniMap) detailMiniMap.invalidateSize(); }, 150);
}

// helper
function escapeHtml(s) { return String(s || '').replace(/[&<>"']/g, function (m) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": "&#39;" }[m]; }); }

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    if (modal) modal.style.display = 'none';
    try { document.body.style.overflow = ''; } catch (e) { }

    // ✅ ล้างแมพเล็กทิ้ง (ถ้ามี) ป้องกัน memory leak / error ตอนเปิดใหม่
    if (detailMiniMap) {
        try { detailMiniMap.remove(); } catch (e) { }
        detailMiniMap = null;
    }
}

// เริ่มต้นแมพเมื่อโหลดหน้า
document.addEventListener("DOMContentLoaded", function () {
    // initialize map only if element exists (viewhomepage.php has no map)
    if (document.getElementById('map')) {
        initMap();
    }

    // ให้ Enter key ทำการค้นหา (search input may not be present)
    const searchInput = document.getElementById("searchLocation");
    if (searchInput) {
        // ✅ ตรวจตำแหน่งผู้ใช้ครั้งเดียวตอนโหลดหน้า เพื่อไบแอสการค้นหาไปที่จังหวัดของผู้ใช้จริง
        detectUserProvinceAndBias();

        searchInput.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                searchDormitory();
            }
        });

        // ✅ ดึงคำแนะนำ (autocomplete) ขณะพิมพ์
        searchInput.addEventListener("input", function () {
            scheduleSuggestFetch(this.value);
        });

        // ✅ โฟกัสกลับมาที่ช่องค้นหา ถ้ามีคำแนะนำค้างอยู่ให้เปิดกล่องขึ้นมาอีกครั้ง
        searchInput.addEventListener("focus", function () {
            if (currentSuggestionItems.length > 0) {
                const box = document.getElementById('searchSuggestions');
                if (box) box.classList.add('show');
            }
        });
    }

    // ✅ ปิดกล่องคำแนะนำเมื่อคลิกนอกกล่องค้นหา
    document.addEventListener('click', function (e) {
        const wrap = document.querySelector('.search-input-wrap');
        if (wrap && !wrap.contains(e.target)) {
            closeSuggestionBox();
        }
    });

    // ปิด Modal เมื่อคลิกนอก Modal — ให้เรียกฟังก์ชันปิดเดียวกับปุ่ม X
    const resultsModal = document.getElementById("resultsModal");
    if (resultsModal) {
        window.addEventListener("click", function (e) {
            if (e.target == resultsModal) {
                closeResultsModal();
            }
        });
    }

    // ปิด Modal ด้วยปุ่ม Escape
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const rm = document.getElementById('resultsModal');
            if (rm && rm.style.display === 'block') closeResultsModal();
        }
    });

    // Repaint map เมื่อเปลี่ยนขนาดหน้าจอ / orientation
    window.addEventListener('resize', function () {
        if (map && map.invalidateSize) map.invalidateSize();
    });
    window.addEventListener('orientationchange', function () {
        if (map && map.invalidateSize) map.invalidateSize();
    });
});