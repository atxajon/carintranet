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

  series: [
    {
      "name": "Tematica/Servicios",
      "colorByPoint": true,
      "data": drupalSettings.data
    }
  ]
});






// var procedencia_data = drupalSettings.procedencia_data;
// (function ($, Drupal, drupalSettings) {
//   Drupal.behaviors.procedencia_chart = {
//     attach: function (context, settings) {
//       if (context == document) {
//         console.log(procedencia_data);
//         var mod, bodyP;
//         $('#procedencia-chart').highcharts({
//           chart: {
//             type: 'pie'
//           },
//           title: {
//             text: 'Total clientes por procedencia'
//           },
//           plotOptions: {
//             series: {
//               dataLabels: {
//                 enabled: true,
//                 format: '{point.name}: {point.y}'
//               }
//             }
//           },
//           series: [{
//             name: 'CT ARM',
//             mod: 'CT',
//             data: [7.0, 6.9, 9.5],
//             bodyPart: 'arm'
//           }, {
//             name: 'MRI BRAIN',
//             mod: 'MRI',
//             data: [-0.2, 0.8, 5.7],
//             bodyPart: 'brain'
//           }, {
//             name: 'MRI WRIST',
//             mod: 'MRI',
//             data: [-0.9, 0.6, 3.5],
//             bodyPart: 'wrist'
//           }, {
//             name: 'PET THYROID',
//             mod: 'PET',
//             data: [3.9, 4.2, 5.7],
//             bodyPart: 'thyroid'
//           }]
//         }, function(chart) {
//           $('.mod').change(function() {
//             mod = this.value;
//             if (mod) {
//               Highcharts.each(chart.series, function(ob, j) {
//                 if (ob.userOptions.mod == mod && (bodyP ? ob.userOptions.bodyPart == bodyP : true)) {
//                   ob.show()
//                 } else {
//                   ob.hide()
//                 }
//               });
//             }
//           });
//
//           $('.body').change(function() {
//             bodyP = this.value;
//             if (bodyP) {
//               Highcharts.each(chart.series, function(ob, j) {
//                 if (ob.userOptions.bodyPart == bodyP && (mod ? ob.userOptions.mod == mod : true)) {
//                   ob.show()
//                 } else {
//                   ob.hide()
//                 }
//               });
//             }
//           })
//         });
//         function get_totals(procedencia_data) {
//           var procedenciaSeries = [];
//           // @todo: loop through array, accumulate values matching, calculate percent, return back an array with the right keys and values.
//           if (procedencia_data != null && procedencia_data.length > 0) {
//             // Loop through rows obtained with php.
//             for (var i = 0; i < procedencia_data.length; i++) {
//               date.push(procedencia_data[i][0]);
//               var currentDate = procedencia_data[i][0];
//               var dateInvested = parseInt(procedencia_data[i][1]);
//               var dateBalance = parseInt(procedencia_data[i][4]);
//               var datePercent = parseInt(procedencia_data[i][3]);
//               var procedenciaSerie = {
//                 name: currentDate,
//                 y: dateInvested,
//                 percent: datePercent
//               };
//               procedenciaSeries.push(procedenciaSerie);
//             }
//           }
//           return procedenciaSeries;
//         }
//       }
//     }
//   }
// })(jQuery, Drupal);