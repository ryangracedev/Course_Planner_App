var graph;

function buildTree(subjectArea) {
    const graphContainer = $("#graph");

    $.ajax({
        type: "POST",
        url: "/rest/subject.php",
        contentType: "json",
        data: JSON.stringify({
            subject: subjectArea,
        }),
        dataType: "json",
        success: (result) => {
            graph = cytoscape({
                container: document.getElementById("graph"),
                elements: [
                    // nodes
                    { data: { id: "a", label: "CIS*1300" } },
                    { data: { id: "b", label: "CIS*2500" } },
                    { data: { id: "c", label: "CIS*2750" } },
                    { data: { id: "d", label: "CIS*3750" } },
                    { data: { id: "e", label: "CIS*2520" } },
                    { data: { id: "f", label: "CIS*3190" } },
                    { data: { id: "g", label: "CIS*1050" } },
                    // edges
                    {
                        data: {
                            id: "ab",
                            source: "a",
                            target: "b",
                        },
                    },
                    {
                        data: {
                            id: "cd",
                            source: "c",
                            target: "d",
                        },
                    },
                    {
                        data: {
                            id: "ef",
                            source: "e",
                            target: "f",
                        },
                    },
                    {
                        data: {
                            id: "ac",
                            source: "a",
                            target: "c",
                        },
                    },
                    {
                        data: {
                            id: "be",
                            source: "b",
                            target: "e",
                        },
                    },
                ],
                style: [
                    {
                        selector: "node",
                        style: {
                            shape: "ellipse",
                            width: "100px",
                            height: "50px",
                            // "background-color": "white",
                            "background-opacity": 0,
                            "border-style": "solid",
                            "border-color": "black",
                            "border-width": 2,
                            // "target-arrow-color": "black",
                            "target-arrow-shape": "triangle"
                        },
                    },
                    {
                        selector: "node[label]",
                        style: {
                            label: "data(label)",
                        },
                    },
                ],
                layout: {
                    name: "breadthfirst",
                    roots: ["a", "g"]
                }
            });
            graph.autolock(true);
        },
        fail: (error) => {
            alert(error);
        },
    });
}

$(document).ready(() => { buildTree("CIS") });
