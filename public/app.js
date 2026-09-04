document.addEventListener('DOMContentLoaded', () => {
  actualizarMetricas();
  actualizarReles();

  // Refrescar datos cada 2 segundos
  setInterval(actualizarMetricas, 2000);
  setInterval(actualizarReles, 3000);
});

async function actualizarMetricas() {
  try {
    const res = await fetch('/api/lectura');
    const data = await res.json();

    document.getElementById('val-potencia').textContent = parseFloat(data.potencia_activa || 0).toFixed(1);
    document.getElementById('val-voltaje').textContent = Math.round(data.voltaje || 220);
    document.getElementById('val-corriente').textContent = parseFloat(data.corriente || 0).toFixed(2);
    document.getElementById('val-energia').textContent = parseFloat(data.energia_total_kwh || 0).toFixed(2);

    const banner = document.getElementById('banner-fantasma');
    if (data.consumo_fantasma == 1) {
      banner.classList.remove('d-none');
      banner.classList.add('d-flex');
    } else {
      banner.classList.add('d-none');
      banner.classList.remove('d-flex');
    }
  } catch (err) {
    document.getElementById('txt-status').textContent = 'OFFLINE';
    document.getElementById('txt-status').className = 'small fw-semibold text-danger';
  }
}

async function actualizarReles() {
  try {
    const res = await fetch('/api/control-reles');
    const data = await res.json();

    if (data.reles) {
      setReleUI(1, data.reles.rele_1);
      setReleUI(2, data.reles.rele_2);
      setReleUI(3, data.reles.rele_3);
    }
  } catch (err) {
    console.error("Error consultando relés", err);
  }
}

function setReleUI(num, estado) {
  const switchEl = document.getElementById(`switch-rele-${num}`);
  const labelEl = document.getElementById(`label-rele-${num}`);
  const isChecked = estado == 1;

  switchEl.checked = isChecked;
  labelEl.textContent = isChecked ? 'Encendido' : 'Desconectado';
  labelEl.className = isChecked ? 'small text-info fw-semibold' : 'small text-muted-custom';
}

async function toggleRele(num, estado) {
  const bodyData = {};
  bodyData[`rele_${num}`] = estado ? 1 : 0;

  try {
    await fetch('/api/control-reles', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(bodyData)
    });
    setReleUI(num, estado ? 1 : 0);
  } catch (err) {
    console.error("Error al cambiar relé", err);
  }
}