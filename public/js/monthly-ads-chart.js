document.addEventListener("DOMContentLoaded", function () {

    const departmentColors = [
    '#56D2DB',
    '#FF7675',
    '#06D6A0',
    '#FFD166',
    '#1B9AAA',
    '#4D5B9E',
    '#9B5DE5',
    '#F15BB5',
    '#FDCB6E',
    '#00B894'
    ];

    const categoryColors = [
    '#A29BFE',
    '#55EFC4',
    '#74B9FF',
    '#FAB1A0',
    '#118AB2',
    '#6C5CE7',
    '#E17055',
    '#FFB347',
    '#9E54F9',
    '#2D3436'
    ];

    const statusData = window.chartData;
    const categories = window.chartCategories;

    // ---- Status Chart ----
    const statusOptions = {
        series: statusData,
        chart: {
        type: 'bar',
        height: 350,
        stacked: true,
        toolbar: {
        show: true
        },
        zoom: {
        enabled: true
        }
    },
    responsive: [{
        breakpoint: 480,
        options: {
        legend: {
            position: 'bottom',
            offsetX: -10,
            offsetY: 0
            }
        }
    }],
    colors: ['#417CDC', '#FFA003', '#0FA577', '#9039F6', '#E84747'],
    plotOptions: {
        bar: {
        horizontal: false,
        borderRadius: 10,
        borderRadiusApplication: 'end', // 'around', 'end'
        borderRadiusWhenStacked: 'last', // 'all', 'last'
        dataLabels: {
            total: {
            enabled: true,
            style: {
                fontSize: '13px',
                fontWeight: 900
            }
            }
        }
        },
    },
    xaxis: {
        categories: categories,
        labels: {
        formatter: function (value) {
            return String(value).split(" ")[0];
            }
        }
    },
    yaxis: {
        labels: {
            formatter: function (value) {
            return parseInt(value);
            }
        }
    },
    tooltip: {
        x: {
            formatter: function (value) {
                return value;
            }
        }
    },
    legend: {
        position: 'bottom',
        horizontalAlign: 'center',
        offsetY: 5,
        labels: {
            colors: '#174a38',
            useSeriesColors: false
        }
    },
    fill: {
        opacity: 1
    }
    };

    new ApexCharts(document.querySelector("#monthlyAdChart"), statusOptions).render();

    // ---- Top Departments Chart ----
    const officeData = window.officeData;
    const officeNames = window.officeNames;

    const depOptions = {
        series: [{
        name: 'Ads Submitted',
        data: officeData
        }],
        chart: {
        type: 'bar',
        height: 350,
        stacked: true,
        toolbar: {
        show: true
        },
        zoom: {
        enabled: true
        }
    },
    responsive: [{
        breakpoint: 480,
        options: {
        legend: {
            position: 'bottom',
            offsetX: -10,
            offsetY: 0
        }
        }
    }],
    colors: departmentColors,
    plotOptions: {
        bar: {
        horizontal: false,
        borderRadius: 10,
        distributed: true,
        borderRadiusApplication: 'end', // 'around', 'end'
        borderRadiusWhenStacked: 'last', // 'all', 'last'
        dataLabels: {
            total: {
            enabled: true,
            style: {
                fontSize: '13px',
                fontWeight: 900
            }
            }
        }
        },
    },
    xaxis: {
        categories: officeNames
    },
    yaxis: {
        labels: {
            formatter: function (value) {
            return parseInt(value);
            }
        }
    },
    legend: {
        position: 'bottom',
        horizontalAlign: 'center',
        offsetY: 5,
        labels: {
            colors: '#174a38', // optional custom label color
            useSeriesColors: false
        }
    },
    fill: {
        opacity: 1
    }
    };

    new ApexCharts(document.querySelector("#officeAdChart"), depOptions).render();

    // ---- Category Chart ----
    const categoryLabels = window.categoryLabels;
    const categoryCounts = window.categoryCounts;

    const categoryOptions = {
        series: [{
        name: 'Total Ads',
        data: categoryCounts
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        colors: categoryColors,
        plotOptions: {
            bar: {
                borderRadius: 8,
                distributed: true
            }
        },
        dataLabels: {
            enabled: true
        },
        xaxis: {
            categories: categoryLabels
        },
        yaxis: {
            labels: {
                formatter: (value) => parseInt(value)
            }
        },
        legend: {
            show: false
        }
    };

    new ApexCharts(document.querySelector("#categoryAdChart"), categoryOptions).render();
});
