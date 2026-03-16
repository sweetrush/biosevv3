<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biosecurity System - Samoa</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        .tabs {
            display: flex;
            background: #2c3e50;
            padding: 0;
            margin: 0;
            list-style: none;
            border-radius: 8px 8px 0 0;
            overflow: hidden;
        }

        .tabs li {
            flex: 1;
        }

        .tabs button {
            width: 100%;
            padding: 15px 20px;
            background: #34495e;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            border-right: 1px solid #2c3e50;
        }

        .tabs button:hover {
            background: #4a6278;
        }

        .tabs button.active {
            background: #3498db;
            color: white;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .tab-icon {
            font-size: 20px;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🚢 Biosecurity Information System - Samoa</h1>
            <p>Vessel Voyage and Passenger Inspection Management</p>
        </header>

        <ul class="tabs">
            <li>
                <button class="tab-link active" onclick="openTab(event, 'voyageTab')">
                    <span class="tab-icon">🚢</span>
                    Voyage Details
                </button>
            </li>
            <li>
                <button class="tab-link" onclick="openTab(event, 'inspectionTab')">
                    <span class="tab-icon">🔍</span>
                    Passenger Inspection
                </button>
            </li>
            <li>
                <button class="tab-link" onclick="openTab(event, 'seizureTab')">
                    <span class="tab-icon">⚠️</span>
                    Passenger Seizure
                </button>
            </li>
            <li>
                <button class="tab-link" onclick="openTab(event, 'cargoReleaseTab')">
                    <span class="tab-icon">📦</span>
                    Cargo Release
                </button>
            </li>
        </ul>

        <div id="voyageTab" class="tab-content active">
            <?php include 'voyage_form.php'; ?>
        </div>

        <div id="inspectionTab" class="tab-content">
            <?php include 'inspection_form.php'; ?>
        </div>

        <div id="seizureTab" class="tab-content">
            <?php include 'seizure_form.php'; ?>
        </div>

        <div id="cargoReleaseTab" class="tab-content">
            <?php include 'cargo_release_form.php'; ?>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            // Hide all tab content
            var tabContent = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabContent.length; i++) {
                tabContent[i].classList.remove("active");
            }

            // Remove active class from all tab links
            var tabLinks = document.getElementsByClassName("tab-link");
            for (var i = 0; i < tabLinks.length; i++) {
                tabLinks[i].classList.remove("active");
            }

            // Show the current tab and mark button as active
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");

            // If switching to inspection tab, check for active voyage
            if (tabName === 'inspectionTab' && typeof checkActiveVoyage === 'function') {
                checkActiveVoyage();
            }

            // If switching to seizure tab, check for active voyage
            if (tabName === 'seizureTab' && typeof checkActiveVoyageSeizure === 'function') {
                checkActiveVoyageSeizure();
            }

            // If switching to cargo release tab, check for active voyage
            if (tabName === 'cargoReleaseTab' && typeof checkActiveVoyageRelease === 'function') {
                checkActiveVoyageRelease();
            }
        }
    </script>

    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script src="inspection_script.js?v=<?php echo time(); ?>"></script>
    <script src="seizure_script.js?v=<?php echo time(); ?>"></script>
    <script src="cargo_release_script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
