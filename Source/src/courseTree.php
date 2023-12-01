<!DOCTYPE html>
<html lang="en">

<head>
    <title>Course Planner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php include("templates/includes.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/cytoscape@3.27.0/dist/cytoscape.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dagre/0.8.2/dagre.min.js"></script>
    <script src="https://unpkg.com/cytoscape-dagre@2.5.0/cytoscape-dagre.js"></script>
    <script type="module" src="courseTree.js"></script>
</head>

<body>
    <?php include("templates/navbar.php"); ?>
    <div class="container">
        <div class="col-md-6 mx-auto my-3">
            <form method="POST" class="form form-horizontal" id="tree-select">
                <div class="row g-3 align-items-center">
                    <div class="col-md">
                        <label for="subject-select">Subject Area Tree</label>
                    </div>
                    <div class="col-md">
                        <select id="subject-select" class="form-control form-select" name="subject_area">
                            <?php

                            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                $options = json_decode($_POST['data'], true);

                                if (isset($options['subject_area'])) {
                                    $subject_selected = $options['subject_area'];
                                }
                            }
                            include("./templates/subject_options.php");
                            ?>
                        </select>
                    </div>
                    <!-- hidden div for showing a course path from planner -->
                    <div class="visually-hidden">
                        <input type="hidden" id="course-code" name="course_code" value=<?php if(isset($options['course_code'])) { echo $options['course_code']; } ?>>
                    </div>
                    <div class="col-md">
                        <button type="submit" class="btn btn-primary w-100">View Tree</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="cy" class="vh-100 border border-dark border-2"></div>
    </div>
</body>

</html>