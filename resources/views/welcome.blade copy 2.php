<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spin the Wheel - Winner Announcement</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet">
    <script src="https://d3js.org/d3.v3.min.js" charset="utf-8"></script>
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd 30%, #bbdefb 100%);
            font-family: 'Roboto', sans-serif;
            transition: background 0.5s;
        }
        #chart {
            width: 100%;
            height: 500px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        #question h1 {
            color: #4A5568;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            transition: opacity 0.5s ease;
            opacity: 0;
        }
        .spin-button {
            transition: transform 0.2s, background-color 0.2s;
        }
        .spin-button:hover {
            transform: scale(1.1);
            background-color: #2563eb;
        }
        .winner-animation {
            animation: fadeIn 1s forwards, bounce 0.5s forwards;
            opacity: 1;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
        .loading-spinner {
            display: none;
            margin: auto;
            border: 8px solid #f3f3f3;
            border-top: 8px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen">
    <div class="text-center bg-white rounded-lg shadow-md p-6">
        <h1 class="text-4xl font-extrabold mb-8 text-blue-600">Spin the Wheel 🎉</h1>
        <div id="chart"></div>
        <div id="loading" class="loading-spinner"></div>
        <div id="question" class="mt-6">
            <h1></h1>
        </div>
        <button id="spinButton" class="spin-button mt-6 px-8 py-4 bg-blue-500 text-white rounded-lg shadow-lg hover:bg-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
            Spin
        </button>
        <button id="themeToggle" class="mt-4 px-4 py-2 bg-gray-300 rounded-lg">Toggle Theme</button>
    </div>

    <audio id="spinSound" src="spin.mp3" preload="auto"></audio>
    <audio id="winnerSound" src="winner.mp3" preload="auto"></audio>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark');
            body.style.background = body.classList.contains('dark') 
                ? 'linear-gradient(135deg, #1a202c 30%, #2d3748 100%)'
                : 'linear-gradient(135deg, #e3f2fd 30%, #bbdefb 100%)';
        });

        var padding = { top: 20, right: 40, bottom: 0, left: 0 },
            w = 500 - padding.left - padding.right,
            h = 500 - padding.top - padding.bottom,
            r = Math.min(w, h) / 2,
            rotation = 0,
            oldrotation = 0,
            picked = 100000,
            oldpick = [],
            color = d3.scale.category20();

        var data = [
            { "label": "Dan", "value": 1, "question": "MERRY CHRISTMAS DAN!!!" },
            { "label": "Sherwin", "value": 2, "question": "MERRY CHRISTMAS SHERWIN!!!" },
            { "label": "Carlo", "value": 3, "question": "MERRY CHRISTMAS CARLO!!!" },
            { "label": "Hermel", "value": 4, "question": "MERRY CHRISTMAS HERMEL!!!" },
            { "label": "Antonio", "value": 5, "question": "MERRY CHRISTMAS ANTONIO!!!" },
            { "label": "Jason", "value": 6, "question": "MERRY CHRISTMAS JASON!!!" },
            { "label": "Gene", "value": 7, "question": "MERRY CHRISTMAS GENE!!!" },
            { "label": "Rachael", "value": 8, "question": "MERRY CHRISTMAS RACHAEL!!!" },
            { "label": "Peter", "value": 9, "question": "MERRY CHRISTMAS PETER!!!" },
            { "label": "Cedrick", "value": 10, "question": "MERRY CHRISTMAS CEDRICK!!!" }
        ];

        var svg = d3.select('#chart')
            .append("svg")
            .data([data])
            .attr("width", w + padding.left + padding.right)
            .attr("height", h + padding.top + padding.bottom);

        var container = svg.append("g")
            .attr("class", "chartholder")
            .attr("transform", "translate(" + (w / 2 + padding.left) + "," + (h / 2 + padding.top) + ")");

        var vis = container.append("g");
        var pie = d3.layout.pie().sort(null).value(function(d) { return 1; });
        var arc = d3.svg.arc().outerRadius(r);

        var arcs = vis.selectAll("g.slice")
            .data(pie)
            .enter()
            .append("g")
            .attr("class", "slice");

        arcs.append("path")
            .attr("fill", function(d, i) { return color(i); })
            .attr("d", function(d) { return arc(d); });

        arcs.append("text").attr("transform", function(d) {
            d.innerRadius = 0;
            d.outerRadius = r;
            d.angle = (d.startAngle + d.endAngle) / 2;
            return "rotate(" + (d.angle * 180 / Math.PI - 90) + ")translate(" + (d.outerRadius - 30) + ")";
        }).attr("text-anchor", "end")
        .text(function(d, i) { return data[i].label; });

        document.getElementById('spinButton').addEventListener('click', spin);

        function spin() {
            if (oldpick.length == data.length) {
                alert("All options have been picked!");
                return;
            }

            document.getElementById('loading').style.display = 'block'; // Show loading spinner
            var spinSound = document.getElementById('spinSound');
            spinSound.play();

            var ps = 360 / data.length,
                rng = Math.floor((Math.random() * 1440) + 360);

            rotation = Math.round(rng / ps) * ps;
            picked = Math.round(data.length - (rotation % 360) / ps);
            picked = picked >= data.length ? (picked % data.length) : picked;

            if (oldpick.indexOf(picked) !== -1) {
                spin();
                return;
            } else {
                oldpick.push(picked);
            }

            rotation += 90 - Math.round(ps / 2);
            vis.transition()
                .duration(3000)
                .attrTween("transform", rotTween)
                .each("end", function() {
                    d3.select(".slice:nth-child(" + (picked + 1) + ") path").attr("fill", "#111");
                    var questionElement = d3.select("#question h1");
                    questionElement.text(data[picked].question).classed("winner-animation", true);
                    document.getElementById('loading').style.display = 'none'; // Hide loading spinner
                    document.getElementById('winnerSound').play(); // Play winner sound
                    oldrotation = rotation;
                });
        }

        function rotTween(to) {
            var i = d3.interpolate(oldrotation % 360, rotation);
            return function(t) {
                return "rotate(" + i(t) + ")";
            };
        }
    </script>
</body>

</html>
