const labelDisplay=a=>{if(["line","radar"].indexOf(a.chart.config.type)>-1)return!1;let e=a.dataset.data[a.dataIndex];return"object"==typeof e&&(e=e.value||0),0!==e&&"auto"};export default labelDisplay;