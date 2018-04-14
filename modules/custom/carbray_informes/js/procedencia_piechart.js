Highcharts.chart('procedencia-chart', {
  chart: {
    type: 'pie'
  },
  title: {
    text: 'Total clientes por procedencia'
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

  series: [
    {
      "name": "Procedencia",
      "colorByPoint": true,
      "data": drupalSettings.procedencia_data
    }
  ]
});