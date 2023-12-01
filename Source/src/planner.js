function addToPlanBtn(course) {
    const addBtn = $("<button>").text("Add To My Plan").attr({
        class: "btn btn-primary m-1 h-100 w-100",
    });
    addBtn.on("click", () => {
        addToPlan(course);
        addBtn.replaceWith(removeFromPlanBtn(course));
    });

    return addBtn;
}

function removeFromPlanBtn(course) {
    const removeBtn = $("<button>").text("Remove").attr({
        class: "btn btn-primary m-1 h-100 w-100",
    });
    removeBtn.on("click", () => {
        addToCatalog(course);
        removeBtn.replaceWith(addToPlanBtn(course));
    });

    return removeBtn;
}

function viewTreeBtn(course) {
    const treeBtn = $("<button>").text("View Prerequisite Courses").attr({
        class: "btn btn-outline-secondary m-1 h-100 w-100"
    });
    treeBtn.on("click", () => {
        $("#tree-data").val(
            JSON.stringify({
                subject_area: course.subject_area,
                course_code: course.subject_area + "\\*" + course.number
            })
        );
        $("#myform").submit();
    });

    return treeBtn;
}

function isPlanned(courseID) {
    const courseDiv = $("#plan").find(`#course-${courseID}`);
    if (courseDiv.length == 0) {
        return false;
    }
    return true;
}

function newCourseDiv(course, parent_id, buttonFunction) {
    const courseDiv = $(`
    <div id="course-${course.id}" class="accordion-item course">
        <div class="accordion-header" id="heading-${course.id}">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${
                course.id
            }" aria-expanded="false" aria-controls="collapse-${course.id}" aria-label="Show more course information">
                <div class="d-flex justify-content-between">
                    <div class="m-2">
                        <strong id='course-${course.id}-code'>${course.subject_area}*${course.number}</strong>
                    </div>
                    <div class="m-2">
                        <p>${course.name}</p>
                    </div>
                </div>
            </button>
        </div>
        <div id="collapse-${
            course.id
        }" class="accordion-collapse collapse" data-bs-parent="#${parent_id}" aria-labelledby="heading-${
        course.id
    }">
            <div class="accordion-body d-grid">
            ${$(addCourseInfo(course)).html()}
            <div class="row g-3 action-buttons">
            </div>
            </div>
        </div>
    </div>
    `).data("info", course);

    // $(courseDiv).find(".accordion-body").append(addCourseInfo(course));
    const viewPrereqBtn = viewTreeBtn(course);
    $(courseDiv)
        .find(".accordion-body .action-buttons")
        .append($(viewPrereqBtn));
    $(viewPrereqBtn).wrap('<div class="col-md-6"></div>');

    const addBtn = buttonFunction(course);
    $(courseDiv).find(".accordion-body .action-buttons").append($(addBtn));
    $(addBtn).wrap('<div class="col-md-6"></div>');

    return courseDiv;
}
function addCourseInfo(course) {
    const infoDiv = $("<div>").attr({});

    if (course.description) {
        $(infoDiv).append(`<p>${course.description}</p>`);
    }
    if (course.prerequisites) {
        $(infoDiv).append(
            `<p><strong>Prerequisites:</strong> ${course.prerequisites}</p>`
        );
    }

    return infoDiv;
}
async function loadCatalog() {
    // clear catalog
    $("#catalog").empty();
    // show loading
    $("#catalog-loading").show();
    // Get all courses before current semester

    //register save plan button
    $("#savePlanBtn").on("click", savePlan);

    // apply filters
    var apiURL = "";
    const eligibleOnly = $("#show-eligible-only").is(":checked");
    if (eligibleOnly) {
        apiURL = "/rest/eligible.php";
    } else {
        apiURL = "/rest/catalog.php";
    }
    console.log(apiURL);
    var requestBody = {
        data: [
            "id",
            "subject_area",
            "number",
            "name",
            "weight",
            "description",
            "prerequisites",
        ],
    };

    if (eligibleOnly) {
        const plannedCourses = [];

        // Get all courses
        $("#plan .course").each((idx, courseDiv) => {
            plannedCourses.push(
                $(courseDiv).data("info").subject_area +
                    "*" +
                    $(courseDiv).data("info").number
            );
        });

        requestBody.completed = plannedCourses;
    }

    // apply semester filters
    const filters = {};
    const semesters = [];
    $("#semester-filter")
        .find("input")
        .each((idx, input) => {
            if ($(input).is(":checked")) {
                semesters.push($(input).val());
            }
        });
    if (semesters.length == 0) {
        alert("At least 1 semester must be chosen");
        $("#catalog-loading").hide();
        return false;
    }
    filters.semester = semesters;

    const subjectArea = $("#subject-select").find(":selected").val();
    if (subjectArea != "All") {
        filters.subject_area = subjectArea;
    }

    if (eligibleOnly) {
        requestBody.course = filters;
    } else {
        requestBody.filter = filters;
    }

    console.log(requestBody);
    requestBody = JSON.stringify(requestBody);

    const catalogDiv = $("#catalog");

    $.ajax({
        type: "POST",
        url: apiURL,
        contentType: "json",
        data: requestBody,
        dataType: "json",
        success: (result) => {
            // catalogDiv.html(JSON.stringify(data));
            $("#catalog-loading").hide();
            if (result.status) {
                result.courses.forEach((course) => {
                    if (!isPlanned(course.id)) {
                        const courseDiv = newCourseDiv(course, "catalog",addToPlanBtn);
                        $("#catalog").append(courseDiv);
                    }
                });
            }
        },
        fail: (error) => {
            catalogDiv.html(JSON.stringify(error));
        },
    });
}
function addToCatalog(course) {
    var lastElement = true;
    $("#catalog")
        .find(".course")
        .each((idx, otherCourse) => {
            const otherData = $(otherCourse).data("info");
            if (
                otherData.subject_area.localeCompare(course.subject_area) >=
                    0 &&
                otherData.number > course.number
            ) {
                const courseDiv = $(`#course-${course.id}`);

                $(courseDiv).find(`#collapse-${course.id}`).attr({
                    "data-bs-parent": "#catalog",
                });
                $(courseDiv).insertBefore(otherCourse);
                if ($("#show-eligible-only").is(":checked")) {
                    loadCatalog();
                }
                return (lastElement = false);
            }
        });
    if (lastElement) {
        const courseDiv = $(`#course-${course.id}`);
        $(courseDiv).find(`#collapse-${course.id}`).attr({
            "data-bs-parent": "#catalog",
        });
        $("#catalog").append(courseDiv);
        if ($("#show-eligible-only").is(":checked")) {
            loadCatalog();
        }
    }
}
function addToPlan(course) {
    const courseDiv = $(`#course-${course.id}`);
    $(courseDiv).find(`#collapse-${course.id}`).attr({
        "data-bs-parent": "#plan",
    });
    $("#plan").append(courseDiv);

    if ($("#show-eligible-only").is(":checked")) {
        loadCatalog();
    }
}

function populateSubjectFilterSelect() {
    $.ajax({
        type: "POST",
        url: "/rest/subject.php",
        contentType: "json",
        data: JSON.stringify({
            subject: "all",
        }),
        dataType: "json",
        success: (result) => {
            // catalogDiv.html(JSON.stringify(data));
            // const subjectSelect = $("#subject-select")
            result.forEach((course) => {
                $("#subject-select").append(
                    $("<option>", {
                        value: course.subject_area,
                        text: course.subject_area,
                    })
                );
            });
        },
        fail: (error) => {
            catalogDiv.html(JSON.stringify(error));
        },
    });
}

function savePlan() {
    let courseCodes = [];

    console.log($("#plan").children());

    $("#plan").children().each(function() {
        const divId = $(this).attr('id');
        const courseCode = $(this).find(`#${divId}-code`).text();
        courseCodes.push(courseCode);
    })

    if (courseCodes.length > 0) {
        $.ajax({
            type: "POST",
            url: "/rest/saveplan",
            contentType: "json",
            data: JSON.stringify({courses: courseCodes}),
            dataType: "json",
            success: (result) => {
                alert("Successfully saved planned courses!");
            },
            fail: (error) => {
                alert("Unexpected error ocurred while trying to save planned courses. Please try again");
            },
        });
    }
    else {
        alert("Plan cannot be empty!");
    }
}

$(document).ready(() => {
    $.ajax({
        type: "POST",
        url: "/rest/getplan",
        contentType: "json",
        dataType: "json",
        success: (result) => {
            result.forEach((course)=>{
                let newCourseData = newCourseDiv(course,"plan",removeFromPlanBtn);
                $("#plan").append(newCourseData);
            });

        },
        fail: (error) => {
            catalogDiv.html(JSON.stringify(error));
        },
    });

    populateSubjectFilterSelect();
    $("#catalog-filter").submit((event) => {
        event.preventDefault();
        loadCatalog();
    });
    loadCatalog();
    
});
