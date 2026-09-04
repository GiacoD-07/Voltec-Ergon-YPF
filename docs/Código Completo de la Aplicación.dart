/**import 'package:flutter/material.dart'; 
import 'package:firebase_core/firebase_core.dart'; 
import 'package:firebase_database/firebase_database.dart'; 
void main() async { 
WidgetsFlutterBinding.ensureInitialized(); 
await Firebase.initializeApp(); // Inicializa la conexión con Firebase runApp(const EcoSmartGridApp()); 
} 
class EcoSmartGridApp extends StatelessWidget { 
const EcoSmartGridApp({super.key}); 
@override 
Widget build(BuildContext context) { 
return MaterialApp( 
title: 'Eco Smart Grid', 
debugShowCheckedModeBanner: false, 
theme: ThemeData( 
brightness: Brightness.dark,
primaryColor: Colors.greenAccent, 
scaffoldBackgroundColor: const Color(0xFF121212), 
), 
home: const DashboardScreen(), 
); 
} 
} 
class DashboardScreen extends StatefulWidget { 
const DashboardScreen({super.key}); 
@override 
State<DashboardScreen> createState() => _DashboardScreenState(); } 
class _DashboardScreenState extends State<DashboardScreen> { // Referencia a la base de datos de Firebase 
final DatabaseReference _dbRef = FirebaseDatabase.instance.ref(); 
// Variables locales para almacenar los datos en tiempo real 
double p1 = 0.0, p2 = 0.0, p3 = 0.0; 
double kwh1 = 0.0, kwh2 = 0.0, kwh3 = 0.0; 
int r1 = 1, r2 = 1, r3 = 1; 
bool vampiroT2 = false; 
// Constantes de cálculo energético (Argentina) 
final double factorEmisionCO2 = 0.39; // 0.39 kg CO2 por kWh final double costoKwh = 45.0; // Costo estimado por kWh en Pesos ($) 
@override 
void initState() { 
super.initState(); 
_activarEscuchaFirebase(); 
} 
// Escucha activa de cambios en Firebase sin recargar la pantalla manualmente void _activarEscuchaFirebase() { 
_dbRef.child('toma1').onValue.listen((event) { 
final data = event.snapshot.value as Map<dynamic, dynamic>?; if (data != null) { 
setState(() { 
p1 = (data['potencia'] ?? 0.0).toDouble(); 
kwh1 = (data['energia_kwh'] ?? 0.0).toDouble(); 
r1 = data['estado_rele'] ?? 1; 
}); 
} 
});
_dbRef.child('toma2').onValue.listen((event) { 
final data = event.snapshot.value as Map<dynamic, dynamic>?; if (data != null) { 
setState(() { 
p2 = (data['potencia'] ?? 0.0).toDouble(); 
kwh2 = (data['energia_kwh'] ?? 0.0).toDouble(); 
r2 = data['estado_rele'] ?? 1; 
vampiroT2 = data['vampiro_activo'] ?? false; 
}); 
} 
}); 
_dbRef.child('toma3').onValue.listen((event) { 
final data = event.snapshot.value as Map<dynamic, dynamic>?; if (data != null) { 
setState(() { 
p3 = (data['potencia'] ?? 0.0).toDouble(); 
kwh3 = (data['energia_kwh'] ?? 0.0).toDouble(); 
r3 = data['estado_rele'] ?? 1; 
}); 
} 
}); 
} 
// Función para cambiar el estado del relé desde la App hacia Firebase void _conmutarRele(String toma, int estadoActual) { 
int nuevoEstado = estadoActual == 1 ? 0 : 1; 
_dbRef.child(toma).update({'estado_rele': nuevoEstado}); } 
@override 
Widget build(BuildContext context) { 
double kwhTotal = kwh1 + kwh2 + kwh3; 
double co2Total = kwhTotal * factorEmisionCO2; 
double gastoTotal = kwhTotal * costoKwh; 
return Scaffold( 
appBar: AppBar( 
title: const Text('⚡ ECO SMART GRID'), 
centerTitle: true, 
backgroundColor: const Color(0xFF1E1E1E), 
elevation: 0, 
), 
body: SingleChildScrollView( 
padding: const EdgeInsets.all(16.0), 
child: Column( 
crossAxisAlignment: CrossAxisAlignment.start, 
children: [
// --- BLOQUE 1: BANNER DE SUSTENTABILIDAD & ECONOMÍA --- Container( 
padding: const EdgeInsets.all(16.0), 
decoration: BoxDecoration( 
color: const Color(0xFF1E1E1E), 
borderRadius: BorderRadius.circular(15), 
border: Border.all(color: Colors.greenAccent.withOpacity(0.3)), 
), 
child: Row( 
mainAxisAlignment: MainAxisAlignment.spaceAround, 
children: [ 
Column( 
children: [ 
const Icon(Icons.eco, color: Colors.greenAccent, size: 30), 
const SizedBox(height: 5), 
const Text('Huella CO₂', style: TextStyle(color: Colors.grey)), 
Text('${co2Total.toStringAsFixed(3)} kg', 
style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.greenAccent)), 
], 
), 
Container(width: 1, height: 50, color: Colors.grey[800]), 
Column( 
children: [ 
const Icon(Icons.monetization_on, color: Colors.amber, size: 30), 
const SizedBox(height: 5), 
const Text('Gasto Est.', style: TextStyle(color: Colors.grey)), 
Text('\$${gastoTotal.toStringAsFixed(2)}', 
style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.amber)), 
], 
), 
], 
), 
), 
const SizedBox(height: 25), 
const Text('DISPOSITIVOS EN RED', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.grey)), 
const SizedBox(height: 10), 
// --- BLOQUE 2: LISTA DE DISPOSITIVOS (TARJETAS) --- 
_buildDeviceCard('Toma 1: Licuadora', Icons.blender, p1, kwh1, r1, 'toma1', false), _buildDeviceCard('Toma 2: Cargador Celular', Icons.battery_charging_full, p2, kwh2, r2, 'toma2', vampiroT2), 
_buildDeviceCard('Toma 3: Notebook', Icons.laptop, p3, kwh3, r3, 'toma3', false), ], 
), 
),
); 
} 
// Widget Constructor de Tarjetas de Dispositivos de forma modular 
Widget _buildDeviceCard(String name, IconData icon, double potencia, double energia, int releState, String nodo, bool esVampiro) { 
bool isOn = releState == 1; 
return Card( 
color: esVampiro ? const Color(0xFF3A2A00) : const Color(0xFF1E1E1E), margin: const EdgeInsets.only(bottom: 16), 
shape: RoundedRectangleBorder( 
borderRadius: BorderRadius.circular(12), 
side: BorderSide( 
color: esVampiro ? Colors.amber : (isOn ? Colors.greenAccent.withOpacity(0.5) : Colors.transparent), 
width: 1.5 
) 
), 
child: Padding( 
padding: const EdgeInsets.all(16.0), 
child: Row( 
children: [ 
Icon(icon, size: 40, color: esVampiro ? Colors.amber : (isOn ? Colors.greenAccent : Colors.grey)), 
const SizedBox(width: 16), 
Expanded( 
child: Column( 
crossAxisAlignment: CrossAxisAlignment.start, 
children: [ 
Text(name, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)), const SizedBox(height: 4), 
Text('Potencia: ${potencia.toStringAsFixed(1)} W', style: TextStyle(color: Colors.grey[400])), 
Text('Energía: ${energia.toStringAsFixed(3)} kWh', style: TextStyle(color: Colors.grey[500], fontSize: 12)), 
if (esVampiro) 
const Padding( 
padding: EdgeInsets.only(top: 4.0), 
child: Text('¡CONSUMO VAMPIRO DETECTADO!', style: TextStyle(color: Colors.amber, fontSize: 11, fontWeight: FontWeight.bold)), 
), 
], 
), 
), 
Switch( 
value: isOn, 
activeColor: Colors.greenAccent,
onChanged: (value) { 
_conmutarRele(nodo, releState); 
}, 
), 
], 
), 
), 
); 
} 
} 
*/