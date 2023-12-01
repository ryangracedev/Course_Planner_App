<!DOCTYPE html>
<html lang="en">
<head>
    <title>Course Planner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php include("templates/includes.php"); ?>
    <script type="module" src="planner.js"></script>
</head>

<body>
    <?php include("templates/navbar.php"); ?>
    <form action="courseTree" method="POST" target="_blank" id="myform">
        <input type="hidden" name="data" id="tree-data">
        <input class="visually-hidden" type="submit" value="Submit">
    </form>

    <div class="container">
        <h1>Online Course Planner</h1>
        <!-- <p>View your eligible for the semester in the Course Catalog. Click "Add Semester" to plan your next semester</p> -->
        <div class="row">

            <div class="col-md-6">
                <!-- div for all plans -->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-3 d-flex justify-content-between">
                            <h2>My Plan</h2>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-primary m-1 h-100 w-100" id="savePlanBtn" aria-label="Save Plan Button">Save Plan</button>
                        </div>
                    </div>
                    <div id="plan" class="container overflow-auto accordion" style="padding-top: 20px">

                    </div>
                </div>
            </div>
            <div class="col-md-6 my-4 vh-100">
                <h2>Course Catalog</h2>
                <div class="my-auto" id="catalog-filter">
                    <form id="catalog-filter" class="d-grid container mb-4" method="POST">
                        <div id="semester-filter" class="my-2">
                            <strong>Semesters</strong>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="F" checked aria-label="Fall Checkbox">
                                <label class="form-check-label" for="inlineCheckbox1">Fall</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="W" checked aria-label="Winter Checkbox">
                                <label class="form-check-label" for="inlineCheckbox2">Winter</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="inlineCheckbox3" value="S" checked aria-label="Summer Checkbox">
                                <label class="form-check-label" for="inlineCheckbox3">Summer</label>
                            </div>
                        </div>
                        <div class="my-2">
                            
                            <label><strong>Subject Area</strong> <select id="subject-select" class="form-select" name="subject_area" aria-label="Subject Areas Dropdown"> 
                                <option selected>All</option>
                            </select></label>
                        </div>
                        <div class="form-check form-switch my-2">
                            
                            <label class="form-check-label" for="showEligibleOnly"> <input class="form-check-input" type="checkbox" id="show-eligible-only" aria-label="Show eligible courses only"> Show eligible courses only</label>
                        </div>
                        <button type="submit" class="btn btn-primary my-2" aria-label="Apply Filters Button">Apply Filters</button>
                    </form>
                </div>
                <h3>Select courses you would like to add to plan</h3>
                <div class="container overflow-auto h-100">
                    <div id="catalog-loading" class="text-center">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border m-2" role="status">
                                <span class="visually-hidden">Loading Courses...</span>
                            </div>
                        </div>
                        <h4>Loading Courses</h4>
                    </div>
                    <div id="catalog" class="accordion">
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>