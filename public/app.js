// Responsabilidad: actualizar la interfaz, consultar la API y enviar órdenes de relés.
const API_BASE = new URL('api/', document.baseURI).toString();
let csrfToken = null;

// Inicializa datos y comienza las actualizaciones periódicas del panel.
document.addEventListener('DOMContentLoaded', () => {
  cargarTokenWeb();
  actualizarMetricas();
  actualizarReles();

  // Refrescar datos cada 2 segundos
  setInterval(actualizarMetricas, 2000);
  setInterval(actualizarReles, 3000);
});

async function actualizarMetricas() {
  try {
    // Obtiene la última medición y actualiza los indicadores principales.
    const res = await fetch(`${API_BASE}lectura`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    document.getElementById('val-potencia').textContent = parseFloat(data.potencia_activa || 0).toFixed(1);
    document.getElementById('val-voltaje').textContent = Math.round(data.voltaje || 220);
    document.getElementById('val-corriente').textContent = parseFloat(data.corriente || 0).toFixed(2);
    document.getElementById('val-energia').textContent = parseFloat(data.energia_total_kwh || 0).toFixed(2);
    document.getElementById('val-co2').textContent = parseFloat(data.huella_carbono_kg || 0).toFixed(3);
    document.getElementById('co2-factor').textContent = `Factor: ${parseFloat(data.factor_emision_co2_kg_kwh || 0.39).toFixed(2)} kg/kWh`;

    const banner = document.getElementById('banner-fantasma');
    if (data.consumo_fantasma == 1) {
      banner.classList.remove('d-none');
      banner.classList.add('d-flex');
    } else {
      banner.classList.add('d-none');
      banner.classList.remove('d-flex');
    }

    document.getElementById('txt-status').textContent = 'EN LÍNEA';
    document.getElementById('txt-status').className = 'status-text text-success';
  } catch (err) {
    document.getElementById('txt-status').textContent = 'OFFLINE';
    document.getElementById('txt-status').className = 'status-text text-danger';
  }
}

async function actualizarReles() {
  try {
    // Sincroniza la posición de los interruptores con el estado guardado en la API.
    const res = await fetch(`${API_BASE}control-reles`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
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

async function cargarTokenWeb() {
  // Solicita un token temporal que se enviará en las operaciones de escritura.
  const res = await fetch(`${API_BASE}web-token`, { credentials: 'same-origin' });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);

  const data = await res.json();
  csrfToken = data.token;
}

function setReleUI(num, estado) {
  // Refleja un estado recibido del servidor en el interruptor correspondiente.
  const switchEl = document.getElementById(`switch-rele-${num}`);
  const labelEl = document.getElementById(`label-rele-${num}`);
  const isChecked = estado == 1;

  switchEl.checked = isChecked;
  labelEl.textContent = isChecked ? 'Encendido' : 'Desconectado';
  labelEl.className = isChecked ? 'relay-status is-on' : 'relay-status';
}

async function toggleRele(num, estado) {
  // Envía el cambio y revierte la interfaz si el servidor lo rechaza.
  const bodyData = {};
  bodyData[`rele_${num}`] = estado ? 1 : 0;

  try {
    if (csrfToken === null) await cargarTokenWeb();

    const res = await fetch(`${API_BASE}control-reles`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify(bodyData)
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    setReleUI(num, estado ? 1 : 0);
  } catch (err) {
    setReleUI(num, estado ? 0 : 1);
    console.error("Error al cambiar relé", err);
  }
}
