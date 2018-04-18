Highcharts.chart('chart', {
  chart: {
    type: 'pie'
  },
  title: {
    text: 'Total expedientes por tematicas/servicios'
  },
  // subtitle: {
  //   text: 'Click the slices to view versions. Source: <a href="http://statcounter.com" target="_blank">statcounter.com</a>'
  // },
  plotOptions: {
    series: {
      dataLabels: {
        enabled: true,
        format: '{point.name}: {point.y}'
      }
    }
  },

  tooltip: {
    headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
    pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.percent}%</b> del total<br/>'
  },

  "series": [
    {
      "name": "Tematica/Servicios",
      "colorByPoint": true,
      "data": drupalSettings.tematicas_data
    }
  ],
  "drilldown": {
    "series": drupalSettings.servicios_data,
  }
});
