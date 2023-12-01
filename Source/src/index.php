<!DOCTYPE html>
<html lang="en">

<head>
    <title>CourseCompass</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <?php include("templates/includes.php"); ?>
</head>

<body>
    <!-- include navbar header -->
    <?php include("templates/navbar.php"); ?>

    <!-- hero element -->
    <div class="px-4 py-5 my-5 text-center">
        <img class="d-block mx-auto mb-4" src="assets/compass_logo.svg" alt="The Course Compass Logo, which is a compass with a C around it" width="72" height="72">
        <h1 class="display-3 fw-bold">CourseCompass</h1>
        <div class="col-lg-6 mx-auto">
            <p class="lead mb-4">
                The #1 tool for planning your university degree.
                <br>
                Simply select your courses that are completed or in progress and get high quality, hand crafted suggestions to take your degree to the next level.
                <br>
                Download our Excel based tool or use our convenient online app now!
            </p>
        </div>

        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <?php
            $btn_class = "btn btn-primary btn-lg px-4 gap-3";
            include("templates/download_sheet_button.php");
            ?>
            <a href="planner" class="btn btn-outline-secondary btn-lg px-4" role="button" aria-label="Open Online Planner">
                Open Planner
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" />
                </svg>
            </a>
        </div>

    </div>

    <div class="container">
        <h2 class="text-center mb-5">What Students Say About CourseCompass</h2>
        <div class="row">
            <figure class="text-center mb-3 col-lg-6">
                <blockquote class="blockquote">
                    <p>"CourseCompass made choosing my courses a breeze with the A.I. powered course suggestions. The user-friendly interface made the whole process simple and stress-free."</p>
                </blockquote>
                <figcaption class="blockquote-footer">
                    University of Guelph Student <cite title="Source Title">B.COMP. Computer Science</cite>
                </figcaption>
            </figure>
            <figure class="text-center mb-3 col-lg-6">
                <blockquote class="blockquote">
                    <p>"CourseCompass is a lifesaver for anyone navigating university courses. It's user-friendly, efficient, and a valuable companion for keeping track of your academic progress."</p>
                </blockquote>
                <figcaption class="blockquote-footer">
                    University of Guelph Student <cite title="Source Title">B.A. POLS</cite>
                </figcaption>
            </figure>
        </div>
        <div class="row">
            <figure class="text-center mb-3 col-lg-6">
                <blockquote class="blockquote">
                    <p>"As a busy student, I appreciate the easy to learn and highly capable user interface. CourseCompass has streamlined my course selection process."</p>
                </blockquote>
                <figcaption class="blockquote-footer">
                    University of Guelph Student <cite title="Source Title">B.ENG. Mech Eng.</cite>
                </figcaption>
            </figure>
            <figure class="text-center mb-3 col-lg-6">
                <blockquote class="blockquote">
                    <p>"I love how CourseCompass helps me track my semesters with the semester planner. It's a fantastic tool for planning my courses, way better than my university's planner."</p>
                </blockquote>
                <figcaption class="blockquote-footer">
                    University of Guelph Student <cite title="Source Title">B.SC. Animal Biology</cite>
                </figcaption>
            </figure>
        </div>
    </div>
</body>

</html>