const legendLabelGenerator=t=>{const e=t.config._config,a=e.type,o=e.data.datasets;return void 0!==e.options.scales.x&&"time"===e.options.scales.x.type||o.length>1||-1===["pie","bar","polarArea"].indexOf(a)?e.data.datasets.map(((e,a)=>{const l=a%o[0].backgroundColor.length;return{datasetIndex:a,text:e.label,fillStyle:o[0].backgroundColor[l],strokeStyle:o[0].borderColor[l],hidden:!!t.getDataVisibility&&!t.getDataVisibility(a)}})):e.data.labels.map(((e,a)=>{const l=a%o[0].backgroundColor.length;return{datasetIndex:a,text:e,fillStyle:o[0].backgroundColor[l],strokeStyle:o[0].borderColor[l],hidden:!!t.getDataVisibility&&!t.getDataVisibility(a)}}))};export default legendLabelGenerator;