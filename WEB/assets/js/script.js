document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('live-search');
    const typeSelect = document.getElementById('filter-type');
    const container = document.getElementById('property-grid');
    if (!searchInput || !container) return;

    const esc = s => String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

    async function loadProperties() {
        const q = encodeURIComponent(searchInput.value);
        const t = encodeURIComponent(typeSelect ? typeSelect.value : '');
        try {
            const res = await fetch(`/api/properties.php?search=${q}&type=${t}`);
            const data = await res.json();
            if (!data.length) {
                container.innerHTML = '<p style="color:#64748b; text-align:center; grid-column:1/-1;">No properties found.</p>';
                return;
            }
            container.innerHTML = data.map(p => `
                <div class="card">
                    <img src="${esc(p.image || '/assets/images/placeholder.svg')}" alt="${esc(p.title)}" class="prop-img">
                    <div class="card-body">
                        <span class="badge" style="margin-bottom:8px;">For ${esc(p.price_type).toUpperCase()}</span>
                        <h3 style="font-size:1.1rem; margin-bottom:5px;"><a href="/property-details.php?id=${Number(p.id)}" style="color:#0f172a; text-decoration:none;">${esc(p.title)}</a></h3>
                        <p style="color:#64748b; font-size:0.85rem;">📍 ${esc(p.city)} &bull; Agent: ${esc(p.agent_name)}</p>
                        <div style="font-size:1.15rem; font-weight:700; color:#2563eb; margin:8px 0;">BDT ${Number(p.price).toLocaleString()}${p.price_type === 'rent' ? '/mo' : ''}</div>
                        <a href="/property-details.php?id=${Number(p.id)}" class="btn btn-primary btn-sm" style="width:100%;">View Details</a>
                    </div>
                </div>
            `).join('');
        } catch (e) { console.error('API Error:', e); }
    }
    searchInput.addEventListener('input', loadProperties);
    if (typeSelect) typeSelect.addEventListener('change', loadProperties);
});
