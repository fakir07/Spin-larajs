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
            height: 800px; /* Increased height */
            max-width: 800px; /* Increased max width */
            margin: auto;
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
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes bounce {
            0%,
            20%,
            50%,
            80%,
            100% {
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
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen">
    <div class="text-center bg-white rounded-lg shadow-md p-6" style="width: 1000px;">
        <div id="chart" style="width: 1000px ;"></div>
        <div id="loading" class="loading-spinner"></div>
        <div id="question" class="mt-6">
            <h1></h1>
        </div>
        <button id="spinButton"
            class="spin-button mt-6 px-8 py-4 bg-blue-500 text-white rounded-lg shadow-lg hover:bg-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-300">
            Spin
        </button>
        <button id="themeToggle" class="mt-4 px-4 py-2 bg-gray-300 rounded-lg">Toggle Theme</button>
        <button id="showModalButton" class="mt-4 px-4 py-2 bg-yellow-500 text-white rounded-lg">Upload Excel</button>
    </div>
    
    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Enter Your Name</h2>
            <h2 class="mt-4">Upload Excel File</h2>
            <form id="uploadForm" method="POST" action="{{ route('import') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" id="fichier" name="fichier" accept=".xls,.xlsx"
                    class="border border-gray-300 rounded-md p-2 mb-4 w-full" required>
                <button type="submit" id="uploadButton"
                    class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg">Upload</button>
            </form>
        </div>
    </div>
    <audio id="spinSound" src="spin.mp3" preload="auto"></audio>
    <audio id="winnerSound" src="winner.mp3" preload="auto"></audio>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const data = @json($data);
        const modal = document.getElementById("myModal");
        const showModalButton = document.getElementById("showModalButton");
        const closeButton = document.getElementsByClassName("close")[0];
    
        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
    
        // Theme toggling
        themeToggle.addEventListener('click', debounce(() => {
            body.classList.toggle('dark');
            body.style.background = body.classList.contains('dark') ?
                'linear-gradient(135deg, #1a202c 30%, #2d3748 100%)' :
                'linear-gradient(135deg, #e3f2fd 30%, #bbdefb 100%)';
        }, 200));
    
        // Modal functionality
        showModalButton.onclick = () => modal.style.display = "block";
        closeButton.onclick = () => modal.style.display = "none";
    
        window.onclick = (event) => {
            if (event.target === modal) modal.style.display = "none";
        };
    
        // Size customization for D3 chart
        const padding = { top: 20, right: 40, bottom: 20, left: 40 },
              w = 800 - padding.left - padding.right,
              h = 800 - padding.top - padding.bottom,
              r = Math.min(w, h) / 2;
        
        let rotation = 0,
            oldrotation = 0,
            picked = 100000,
            oldpick = [],
            color = d3.scale.category20();
    
        const svg = d3.select('#chart')
            .append("svg")
            .data([data])
            .attr("width", w + padding.left + padding.right)
            .attr("height", h + padding.top + padding.bottom);
    
        const container = svg.append("g")
            .attr("class", "chartholder")
            .attr("transform", `translate(${w / 2 + padding.left}, ${h / 2 + padding.top})`);
    
        const vis = container.append("g");
        const pie = d3.layout.pie().sort(null).value(() => 1);
        const arc = d3.svg.arc().outerRadius(r);
    
        const slices = vis.selectAll("g.slice")
            .data(pie(data))
            .enter()
            .append("g")
            .attr("class", "slice");
    
        slices.append("path")
            .attr("fill", (d, i) => color(i))
            .attr("d", arc);
    
        slices.append("text")
            .attr("transform", d => {
                d.innerRadius = 0;
                d.outerRadius = r;
                d.angle = (d.startAngle + d.endAngle) / 2;
                return `rotate(${d.angle * 180 / Math.PI - 90})translate(${d.outerRadius - 40})`;
            })
            .attr("text-anchor", "end")
            .style("font-size", "18px")
            .text((d, i) => data[i].label);
    
        document.getElementById('spinButton').addEventListener('click', spin);
    
        function spin() {
            if (oldpick.length === data.length) {
                alert("All options have been picked!");
                return;
            }
    
            document.getElementById('loading').style.display = 'block'; // Show loading spinner
            const spinSound = document.getElementById('spinSound');
            spinSound.play();
    
            const ps = 360 / data.length,
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
                .duration(9000) // Set duration to 9000 milliseconds (9 seconds)
                .attrTween("transform", rotTween)
                .each("end", function() {
                    d3.select(`.slice:nth-child(${picked + 1}) path`).attr("fill", "#111");
                    const questionElement = d3.select("#question h1");
                    questionElement.text(data[picked].question).classed("winner-animation", true);
                    document.getElementById('loading').style.display = 'none'; // Hide loading spinner
                    document.getElementById('winnerSound').play(); // Play winner sound
                    oldrotation = rotation;
                });
        }
    
        function rotTween(to) {
            const i = d3.interpolate(oldrotation % 360, rotation);
            return function(t) {
                return `rotate(${i(t)})`;
            };
        }
    </script>

    


</body>

</html>
