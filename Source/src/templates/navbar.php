<header>
    <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
        <div class="container-fluid py-3">

            <a class="navbar-brand" href="/">CourseCompass</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="planner">Online Planner</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courseTree">Prerequisite Visualiser</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="docs">API Docs</a>
                    </li>
                </ul>

                <div class="d-flx">
                    <?php
                    $btn_class = "btn btn-outline-light me-2";
                    include("download_sheet_button.php");
                    ?>
                </div>
            </div>

        </div>
    </nav>
</header>