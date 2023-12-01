function getPrereqsForCourses(response, currentSubjectArea){
    var res = [];
    var crossSubjectPrereqs = [];

    for (var i = 0; i < response.length; i++){
        (function(index){
            var course_code = response[index].subject_area + "*" + response[index].number;
            $.ajax({
                type: "POST",
                url: "/rest/prereq.php",
                contentType: "json",
                data: JSON.stringify({
                    prereq: course_code,
                }),
                async: false,
                dataType: "json",
                success: function(result){
                    var json = {
                        course_code: course_code,
                        requiredCourses: [],
                        rawPrereq: response[index].prerequisites,
                        prereqTable: result
                    };

                    for (let j = 0; j < result.length; j++) {
                        let prereqSubject = result[j].subject_area;
                        let prereqNumber = result[j].number;
                        let prereqCode = prereqSubject + "*" + prereqNumber;
                        
                        if (result[j].subject_area!=null && result[j].number!=null) {
                            // Add the prerequisite
                            json.requiredCourses.push(prereqCode);

                            // If it's a cross-subject prereq and not already included, add it to the list
                            if (prereqSubject !== currentSubjectArea && !crossSubjectPrereqs.includes(prereqCode)){
                                crossSubjectPrereqs.push(prereqCode);
                            }
                        }
                    }
                    res.push(json);
                },
                fail: (error) => {
                    catalogDiv.html(JSON.stringify(error));
                },
            });
        })(i);
    }

    // Add cross-subject prerequisites as individual entries
    crossSubjectPrereqs.forEach(prereqCode => {
        res.push({
            course_code: prereqCode,
            requiredCourses: [],
            rawPrereq: '',
            prereqTable: []
        });
    });

    return res;
}

function getCoursesFromSubject(subject_area) {
    var res;

    $.ajax({
        type: "POST",
        url: "/rest/subject.php",
        contentType: "json",
        data: JSON.stringify({
            subject: subject_area,
        }),
        async: false,
        dataType: "json",
        success: function (data){
            res = getPrereqsForCourses(data, subject_area); // Pass subject_area as an argument
        },
        fail: (error) => {
            catalogDiv.html(JSON.stringify(error));
        },
    });
    return res;
}


var cy;

function createGraph(res) {
    var elems = [];

    if(cy) {
        cy.destroy();
    }

    res.forEach((node) => {
        var jsonNode = {};
        jsonNode['data'] = { id: node['course_code'], label: node['course_code'], prereqs: node['rawPrereq'] };
        elems.push(jsonNode);
        node['requiredCourses'].forEach((prereq) => {
            // Include all prerequisites, regardless of subject
            var jsonEdge = {};
            jsonEdge['data'] = { id: prereq + "-" + node['course_code'], source: prereq, target: node['course_code'] };
            elems.push(jsonEdge);
        });
    });
    

    console.log(elems);
    
    cy = cytoscape({
        container: document.getElementById("cy"),

        wheelSensitivity: 0.2,

        elements: elems,

        style: [
            {
                selector: 'node',
                style: {
                    'label': 'data(id)',
                    'background-color': 'gray',
                    'width': 100,
                    'height': 100,
                    "text-valign": "center",
                    "text-halign": "center",
                    "color": "white",
                    "font-weight": "bold",
                    'font-size': 16
                }
            },

            {
                selector: 'edge',
                style: {
                  'width': 6,
                  'line-color': '#ccc',
                  'target-arrow-color': '#ccc',
                  'arrow-scale': 2,
                  'target-arrow-shape': 'triangle',
                  'curve-style': 'bezier'
                }
            }
        ]
    });

    highlightPlannedCourses(cy);

    cy.layout({ name: 'dagre', rankSep: 150, nodeSep: 100}).run();
    cy.autolock(true);

    const courseInfoDiv = $(`
        <div id="courseInfo" class="position-absolute bottom-0 start-0 rounded" style="margin-left: 30px; margin-bottom: 30px; z-index: 100; background-color: rgba(255, 255, 255, 0.7);">
            <h5>Tap on a course to view prerequisite details</h5>
            <h2 id="selectedCourse">Selected Course: None</h2>
            <h3 id="coursePrereqs">Course Prerequisites: None</h3>
        </div>
    `);
    
    $("#cy").append(courseInfoDiv);

    cy.bind('tap', 'edge', function(event) {
        if (event.target.hasClass('highlight')) {
            event.target.removeClass('highlight');
            event.target.css('lineColor', '#ccc');
            event.target.css('target-arrow-color', '#ccc');
        }
        else {
            event.target.addClass('highlight');
            event.target.css('lineColor', '#dc3545');
            event.target.css('target-arrow-color', '#dc3545');
        }
        var connected = event.target.connectedNodes();
        for (var i = 0; i < connected.length; i++) {
            if (connected[i].connectedEdges('.highlight').length < 1) {
                connected[i].css('background-color', 'gray');
            }else{
                connected[i].css('background-color', '#dc3545');
            }
        }
    });

    cy.on('tap', 'node', function(evt){
        var node = evt.target;
        console.log( 'tapped ' + node.id() );
        console.log(evt);

        var hasHighlightClass = node.hasClass('highlight');
        var hasPlannedClass = node.hasClass('planned');

        var values = cy.$('.highlight').select();

        document.getElementById('selectedCourse').textContent = 'Selected Course: ' + node.id();

        let rawPrereq = node[0]._private.data['prereqs'];
        if (rawPrereq == '') {
            rawPrereq = 'None';
        }
        document.getElementById('coursePrereqs').textContent = 'Course Prerequisites: ' + rawPrereq;

        console.log(node);

        for (var i = 0; i < values.length; i++) {
            values[i].removeClass('highlight');
            values[i].css('lineColor', '#ccc');
            values[i].css('target-arrow-color', '#ccc');

            if (!values[i].hasClass('planned')) {
                values[i].css('background-color', 'gray');
            }
        }
        
        if (!hasHighlightClass) {
            
            console.log(node.classes());
            node.addClass('highlight');
            console.log(node.classes());

            if (!hasPlannedClass) {
                node.css('background-color', '#dc3545');
            }

            var values = node.predecessors();

            //console.log(values[0].id());

            for (var i = 0; i < values.length; i++) {

                console.log(values[i]);
                console.log(values[i].classes());
                console.log('\n');

                if (values[i].hasClass('highlight')) {
                    values[i].removeClass('highlight');
                    values[i].css('lineColor', '#ccc');
                    values[i].css('target-arrow-color', '#ccc');

                    if (!values[i].hasClass('planned')) {
                        values[i].css('background-color', 'gray');
                    }
                }else {
                    values[i].addClass('highlight');
                    values[i].css('lineColor', '#dc3545');
                    values[i].css('target-arrow-color', '#dc3545');

                    if (!values[i].hasClass('planned')) {
                        values[i].css('background-color', '#dc3545');
                    }
                }
            }

            if (values.length >0){
                var allNodes = cy.$('');
                for (var i = 0; i < allNodes.length; i++) {
                    if(!allNodes[i].hasClass('highlight')){
                        allNodes[i].css('display','none');
                    }
                }
                cy.fit();
                cy.center();
            }
        }
        else {
            console.log('has highlight class');
            document.getElementById('selectedCourse').textContent = 'Selected Course: None';
            document.getElementById('coursePrereqs').textContent = 'Course Prerequisites: None';
            var allNodes = cy.$('');
            for (var i = 0; i < allNodes.length; i++) {
                if(!allNodes[i].hasClass('highlight')){
                    allNodes[i].css('display','element');

                    if (allNodes[i].hasClass('planned')) {
                        allNodes[i].css('background-color', 'green');
                    }
                }
            }
            cy.fit();
            cy.center();
        }
    });
}

function highlightPlannedCourses(cy) {
    $.ajax({
        type: "POST",
        url: '/rest/getplan',
        contentType: "json",
        dataType: "json",
        success: (result) => {
            result.forEach((plannedCourse) => {
                let courseNode = cy.getElementById(plannedCourse.subject_area + '*' + plannedCourse.number);
                courseNode.addClass('planned');
                courseNode.css('background-color', 'green');
            });
        },
        fail: (error) => {
            catalogDiv.html(JSON.stringify(error));
        },
    });
}

function getSelectedSubject() {
    return $("#subject-select").find(":selected").val();
}


$(document).ready(() => {

    $("#tree-select option").prop("selected", function () {
        // return defaultSelected property of the option
        return this.defaultSelected;
    });

    var res = getCoursesFromSubject(getSelectedSubject());
    createGraph(res);

    // check if course_code in form is set. if so select node by default
    // console.log($("#course-code").val());
    const course_code = $("#course-code").val();
    if(course_code) {
        const node = cy.$("#" + course_code);
        node.emit('tap');
    }

    $("#tree-select").submit((event) => {
        event.preventDefault();
        var res = getCoursesFromSubject(getSelectedSubject());
        createGraph(res);
    });
});
