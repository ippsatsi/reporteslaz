var browser_agente = navigator.userAgent;
var pos_firefox = browser_agente.indexOf('Firefox');
var navegador_antiguo = false;

if (pos_firefox > 0) {
    var texto_firefox = browser_agente.substr(pos_firefox);
    var version_firefox = texto_firefox.slice(8,texto_firefox.indexOf("."));
    if (version_firefox < 60) {
      navegador_antiguo = true;
    }
}
console.log('navegador antiguo:' + navegador_antiguo);
