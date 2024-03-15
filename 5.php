<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and process form data
    $taskCount = count($_POST['task_name']);
    $tasks = [];
    for ($i = 0; $i < $taskCount; $i++) {
        $tasks[] = [
            'name' => $_POST['task_name'][$i],
            'start' => $_POST['start_date'][$i],
            'end' => $_POST['end_date'][$i],
            'dependency' => $_POST['dependency'][$i]
        ];
    }
    // Generate SVG elements based on tasks
    $svgElements = '';
    $y = 50; // Initial Y position for the first task
    $taskPositions = []; // Store task positions for dependency lines

    // Define colors for bars
    $colors = ['#7B68EE', '#FFA07A', '#3CB371', '#FFD700', '#20B2AA', '#00BFFF', '#DC143C', '#FF69B4'];

    foreach ($tasks as $index => $task) {
        // Calculate bar width and days difference
        $start = strtotime($task['start']);
        $end = strtotime($task['end']);
        $width = ($end - $start) / (60 * 60 * 24) * 10; // 10 pixels per day for example
        $daysDiff = ($end - $start) / (60 * 60 * 24); // Difference in days

        // Store the position of the task
        $taskPositions[$index] = ['x' => 10, 'y' => $y, 'width' => $width];

        // Draw the task name
        $svgElements .= "<text x='10' y='". ($y - 20) ."' font-family='Verdana' font-size='14' fill='black'>". htmlspecialchars($task['name']) ."</text>";
        // Draw the task bar
        $colorIndex = $index % count($colors); // Cycle through colors
        $svgElements .= "<rect x='10' y='". $y ."' width='". $width ."' height='20' style='fill:".$colors[$colorIndex].";' />";
        // Draw the days difference
        $svgElements .= "<text x='". ($width + 15) ."' y='". ($y + 15) ."' font-family='Verdana' font-size='14' fill='black'>". $daysDiff ." days</text>";

        $y += 40; // Increment Y position for the next task
    }

    // Draw dependency lines
    foreach ($tasks as $index => $task) {
        if (!empty($task['dependency'])) {
            $depIndex = $task['dependency'] - 1; // Adjust for array index
            if (isset($taskPositions[$depIndex])) {
                // Calculate positions for dependency line
                $startX = $taskPositions[$depIndex]['x'] + $taskPositions[$depIndex]['width'];
                $startY = $taskPositions[$depIndex]['y'] + 10;
                $endX = $taskPositions[$index]['x'];
                $endY = $taskPositions[$index]['y'] + 10;

                // Draw the line
                $svgElements .= "<line x1='". $startX ."' y1='". $startY ."' x2='". $endX ."' y2='". $endY ."' style='stroke:black;stroke-width:2' />";
                // Draw an arrow at the end of the line
                $svgElements .= "<polygon points='". ($endX-5) .",". ($endY-5) ." ". ($endX-5) .",". ($endY+5) ." ". $endX .",". $endY ."' style='fill:black;' />";
            }
        }
    }

    // Output the SVG graph
    header('Content-Type: image/svg+xml');
    echo "<svg width='100%' height='". $y ."' xmlns='http://www.w3.org/2000/svg'>";
    echo $svgElements;
    echo "</svg>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Horizontal Graph Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .task {
            margin-bottom: 10px;
        }
        label {
            margin-right: 10px;
        }
        input[type="text"],
        input[type="date"],
        input[type="number"],
        button,
        input[type="submit"] {
            margin-top: 5px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            font-size: 14px;
        }
        button {
            background-color: #5cb85c;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #4cae4c;
        }
        input[type="submit"] {
            background-color: #337ab7;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #286090;
        }
    </style>
</head>
<body>
    <form method="post">
        <div id="taskInputs">
            <div class="task">
                <label for="task_name[]">Task Name:</label>
                <input type="text" name="task_name[]" required>
                <label for="start_date[]">Start Date:</label>
                <input type="date" name="start_date[]" required>
                <label for="end_date[]">End Date:</label>
                <input type="date" name="end_date[]" required>
                <label for="dependency[]">Dependency (Task Number):</label>
                <input type="number" name="dependency[]" min="0">
            </div>
        </div>
        <button type="button" onclick="addTask()">Add Another Task</button>
        <input type="submit" value="Generate Graph">
    </form>

    <script>
        function addTask() {
            var container = document.getElementById("taskInputs");
            var taskDiv = document.createElement("div");
            taskDiv.className = "task";
            taskDiv.innerHTML = document.querySelector(".task").innerHTML;
            container.appendChild(taskDiv);
        }
    </script>
</body>
</html>