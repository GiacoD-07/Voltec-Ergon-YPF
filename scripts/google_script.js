function doPost(e) {
  try {
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    var data = JSON.parse(e.postData.contents);
    
    sheet.appendRow([
      new Date(),
      data.dispositivo || "EcoSmart_01",
      data.voltaje || 0,
      data.corriente || 0,
      data.potencia || 0,
      data.energia || 0,
      data.consumo_fantasma ? "SÍ" : "NO"
    ]);
    
    return ContentService.createTextOutput(JSON.stringify({"result": "success"}))
           .setMimeType(ContentService.MimeType.JSON);
  } catch(error) {
    return ContentService.createTextOutput(JSON.stringify({"result": "error", "error": error.message}))
           .setMimeType(ContentService.MimeType.JSON);
  }
}