<!DOCTYPE html>
<html>

<head>
    <title>Course Planner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php include("templates/includes.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/cytoscape@3.27.0/dist/cytoscape.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/raphael@2.3.0/raphael.min.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/treant-js@1.0.1/Treant.min.js"></script> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/treant-js@1.0.1/Treant.min.css">
    <script src="tree.js"></script>

    <!-- <style>
        #graph {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0px;
            left: 0px;
        }
    </style> -->
</head>

<body>
    <?php include("templates/navbar.php"); ?>

    <div class="container">
        <div class="row">
            <div class="col-md-3">

            </div>
            <div class="col-md 9 vh-100">
                <div id="graph" class="border border-2 h-100">

                </div>
            </div>
        </div>
    </div>

</body>

</html>