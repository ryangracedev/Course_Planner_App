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

    <div class="container my-5 col-lg-6">
        <h1 class="text-center">API Documentation</h1>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="#list-all-courses">List All Courses</a></li>
            <li class="nav-item"><a class="nav-link" href="#search-course">Search Course</a></li>
            <li class="nav-item"><a class="nav-link" href="#search-course-sbjarea">Search Course By Subject Area</a></li>
            <li class="nav-item"><a class="nav-link" href="#list-sbjarea">List Subject Areas</a></li>
            <li class="nav-item"><a class="nav-link" href="#search-course-sbjarea">Search Course By Subject Area</a></li>
            <li class="nav-item"><a class="nav-link" href="#course-requirements">Get Prerequisites For Course</a></li>
            <li class="nav-item"><a class="nav-link" href="#eligible-courses">Get Eligible Courses</a></li>
        </ul>
        <div id="list-all-courses" class="doc my-5">
            <h2>List All Courses</h2>
            <div class="endpoint p-2">
                <code>POST https://cis3760f23-10.socs.uoguelph.ca/rest/course</code>
            </div>
            <h3>Description</h3>
            <p>Get a list of all courses offered at the University of Guelph.</p>
            <h3>Request Body</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
{
    course="all"
}
                </pre>
                </code>
            </div>
            <h3>Data</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
[
    {
        id, subject_area, number, name, weight, description, department, location, prerequisites, requirements_id, credits_required
    },
    {
        id, subject_area, number, name, weight, description, department, location, prerequisites, requirements_id, credits_required
    },
    {
        id, subject_area, number, name, weight, description, department, location, prerequisites, requirements_id, credits_required
    },
    ...
]
                </pre>
                </code>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:25%">Field</th>
                        <th>Value</th>
                        <th style="width:25%">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>subject_area</td>
                        <td>The abbriviated name for the subject area the course belongs to</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>number</td>
                        <td>The unique id for the course in it's subject area. Larger numbers indicate more advanced courses</td>
                        <td>Integer</td>
                    </tr>
                    <tr>
                        <td>name</td>
                        <td>The display name of the course</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>weight</td>
                        <td>The number of credits granted for completing the course</td>
                        <td>Float</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>The text details of the course including what material is taught</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>department</td>
                        <td>The name of the academic department that administrates the course</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>location</td>
                        <td>Where the course is taught</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>prerequisites</td>
                        <td>The prerequisites of the course in a textual representation</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>credits_required</td>
                        <td>The number of credits needed to take the course</td>
                        <td>Float</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="search-course" class="doc my-5">
            <h2>Search Course</h2>
            <div class="endpoint p-2">
                <code>POST https://cis3760f23-10.socs.uoguelph.ca/rest/course</code>
            </div>
            <h3>Description</h3>
            <p>Get course information on a specific course code.</p>
            <h3>Request Body</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
{
    course=&lt;course_code&gt;
}
                </pre>
                </code>
            </div>
            <h3>Data</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
[
    {
        id, subject_area, number, name, weight, description, department, location, prerequisites, requirements_id, credits_required
    },
    {
        id, subject_area, number, name, weight, description, department, location, prerequisites, requirements_id, credits_required
    },
    {
        id, subject_area, number, name, weight, description, department, location, prerequisites, requirements_id, credits_required
    },
    ...
]
                </pre>
                </code>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:25%">Field</th>
                        <th>Value</th>
                        <th style="width:25%">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>subject_area</td>
                        <td>The abbriviated name for the subject area the course belongs to</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>number</td>
                        <td>The unique id for the course in it's subject area. Larger numbers indicate more advanced courses</td>
                        <td>Integer</td>
                    </tr>
                    <tr>
                        <td>name</td>
                        <td>The display name of the course</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>weight</td>
                        <td>The number of credits granted for completing the course</td>
                        <td>Float</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>The text details of the course including what material is taught</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>department</td>
                        <td>The name of the academic department that administrates the course</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>location</td>
                        <td>Where the course is taught</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>prerequisites</td>
                        <td>The prerequisites of the course in a textual representation</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>credits_required</td>
                        <td>The number of credits needed to take the course</td>
                        <td>Float</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="search-course-sbjarea" class="doc my-5">
            <h2>Search Course By Subject Area</h2>
            <div class="endpoint p-2">
                <code>POST https://cis3760f23-10.socs.uoguelph.ca/rest/subject</code>
            </div>
            <h3>Description</h3>
            <p>View a list of all courses at the University of Guelph filtered by a subject area.</p>
            <h3>Request Body</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
{
    subject=&lt;subject_area&gt;
}
                </pre>
                </code>
            </div>
            <h3>Data</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
[
    {
        id, subject_area, number, name, weight, description, department, location, prerequisites, requirements_id, credits_required
    },
    {
        id, subject_area, number, name, weight, description, department, location, prerequisites, requirements_id, credits_required
    },
    {
        id, subject_area, number, name, weight, description, department, location, prerequisites, requirements_id, credits_required
    },
    ...
]
                </pre>
                </code>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:25%">Field</th>
                        <th>Value</th>
                        <th style="width:25%">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>subject_area</td>
                        <td>The abbriviated name for the subject area the course belongs to</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>number</td>
                        <td>The unique id for the course in it's subject area. Larger numbers indicate more advanced courses</td>
                        <td>Integer</td>
                    </tr>
                    <tr>
                        <td>name</td>
                        <td>The display name of the course</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>weight</td>
                        <td>The number of credits granted for completing the course</td>
                        <td>Float</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>The text details of the course including what material is taught</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>department</td>
                        <td>The name of the academic department that administrates the course</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>location</td>
                        <td>Where the course is taught</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>prerequisites</td>
                        <td>The prerequisites of the course in a textual representation</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>credits_required</td>
                        <td>The number of credits needed to take the course</td>
                        <td>Float</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="list-sbjarea" class="doc my-5">
            <h2>List Subject Areas</h2>
            <div class="endpoint p-2">
                <code>POST https://cis3760f23-10.socs.uoguelph.ca/rest/subject</code>
            </div>
            <h3>Description</h3>
            <p>View a list of all available subject areas.</p>
            <h3>Request Body</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
{
    subject="all"
}
                </pre>
                </code>
            </div>
            <h3>Data</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
[
    {
        subject_area
    },
    {
        subject_area
    },
    {
        subject_area
    },
    ...
]
                </pre>
                </code>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:25%">Field</th>
                        <th>Value</th>
                        <th style="width:25%">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>subject_area</td>
                        <td>The abbriviated name for the subject area the course belongs to</td>
                        <td>String</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="course-requirements" class="doc my-5">
            <h2>Get Prerequisites For Course</h2>
            <div class="endpoint p-2">
                <code>POST https://cis3760f23-10.socs.uoguelph.ca/rest/prereq</code>
            </div>
            <h3>Description</h3>
            <p>Get the tree of requirements for a specific course.</p>
            <h3>Request Body</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
{
    prereq=&lt;course_code&gt;
}
                </pre>
                </code>
            </div>
            <h3>Data</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
[
    {
        id, parent_id, num_required, subject_area, number, name
    },
    {
        id, parent_id, num_required, subject_area, number, name
    },
    {
        id, parent_id, num_required, subject_area, number, name
    },
    ...
]
                </pre>
                </code>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:25%">Field</th>
                        <th>Value</th>
                        <th style="width:25%">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>id</td>
                        <td>The id of the group the requirement belongs to</td>
                        <td>Integer</td>
                    </tr>
                    <tr>
                        <td>parent_id</td>
                        <td>The id of the parent group the requirement is a child of. If null this is the root node of the requirements tree</td>
                        <td>Integer | Null</td>
                    </tr>
                    <tr>
                        <td>num_required</td>
                        <td>The number of requirements that must be satisfied in this group plus the number of children groups that must be satisfied</td>
                        <td>Integer</td>
                    </tr>
                    <tr>
                        <td>subject_area</td>
                        <td>The subject area the requirement belongs to</td>
                        <td>String</td>
                    </tr>
                    <tr>
                        <td>number</td>
                        <td>The course number of the requirement</td>
                        <td>Integer</td>
                    </tr>
                    <tr>
                        <td>name</td>
                        <td>The course name of the requirement</td>
                        <td>String</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="eligible-courses" class="doc my-5">
            <h2>Get Eligible Courses</h2>
            <div class="endpoint p-2">
                <code>POST https://cis3760f23-10.socs.uoguelph.ca/rest/api</code>
            </div>
            <h3>Description</h3>
            <p>Get a list of eligible courses based on a series of completed courses.</p>
            <h3>Request Body</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
{
    course1=&lt;course_code1&gt;,
    course2=&lt;course_code2&gt;,
    course3=&lt;course_code3&gt;,
    ...
}
                </pre>
                </code>
            </div>
            <h3>Data</h3>
            <div class="endpoint p-2">
                <code>
                <pre>
[
    course_code-01,
    course_code-02,
    course_code-03
    ...
]
                </pre>
                </code>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:25%">Field</th>
                        <th>Value</th>
                        <th style="width:25%">Type</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>course_code</td>
                        <td>The course code of the course you can take. The course code is a concatenation of the subject area and number of the course with a "*"</td>
                        <td>String</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>