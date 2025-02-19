var viewer;//Global variable for the Forge Viewer
let md_ViewerDocument = null;// Store the loaded document
let md_viewables = [];//Store available viewables



var options = {
    env: 'AutodeskProduction2',
    api: 'streamingV2',  // for models uploaded to EMEA change this option to 'streamingV2_EU'
    getAccessToken: function(onTokenReady) {
        var token = accessToken;
        var timeInSeconds = 3600; // Use value provided by APS Authentication (OAuth) API
        onTokenReady(token, timeInSeconds);
    }
};


function initializeViewer() {
    const htmlDiv = document.getElementById('forgeViewer');

    if (!htmlDiv) {
        console.error("Error: Viewer container 'forgeViewer' not found.");
        return;
    }

    viewer = new Autodesk.Viewing.GuiViewer3D(htmlDiv);

    Autodesk.Viewing.Initializer(options, function() {
        const startedCode = viewer.start();
        if (startedCode > 0) {
            console.error('Failed to create a Viewer: WebGL not supported.');
            return;
        }

        console.log('Initialization complete, loading a model next...');
        loadModel(); // Proceed to load the model
    });
}

// Load the model into the viewer
function loadModel() {
    if (typeof translatedUrn !== 'undefined' && translatedUrn) {
        const documentId = `urn:${translatedUrn}`;
        Autodesk.Viewing.Document.load(documentId, onDocumentLoadSuccess, onDocumentLoadFailure);
        console.log('Translated URN:', translatedUrn);
    } else {
        console.error('Error: Translation is undefined or invalid.');
    }
}

// Handle successful document loading
function onDocumentLoadSuccess(viewerDocument) {
    console.log('Document loaded successfully');

    if (!viewerDocument || !viewerDocument.getRoot()) {
        console.error('Error: Document root is missing.');
        return;
    }

    const viewerRoot = viewerDocument.getRoot();
    md_ViewerDocument = viewerDocument; // Store for later use
    md_viewables = viewerRoot.search({ type: 'geometry' }); // Find all viewables

    if (md_viewables.length === 0) {
        console.error('Error: Document contains no viewables.');
        return;
    }

    // Populate the dropdown with viewables
    populateViewablesDropdown();

    // Load the first available model
    viewer.loadDocumentNode(viewerDocument, md_viewables[0]);
}

// Populate the dropdown with available viewables
function populateViewablesDropdown() {
    const dropdown = document.getElementById('viewables');
    dropdown.innerHTML = ''; // Clear previous options

    md_viewables.forEach((viewable, index) => {
        const option = document.createElement('option');
        option.innerHTML = viewable.data.name;
        option.value = index;
        dropdown.appendChild(option);
    });

    // Show the dropdown if multiple viewables exist
    if (md_viewables.length > 1) {
        document.getElementById("viewables_dropdown").style.display = "block";
    }
}

// Function to switch between different viewables
function selectViewable() {
    const selectedIndex = document.getElementById("viewables").selectedIndex;
    if (md_ViewerDocument && md_viewables[selectedIndex]) {
        viewer.loadDocumentNode(md_ViewerDocument, md_viewables[selectedIndex]);
    }
}

// Handle failed document loading
function onDocumentLoadFailure(errorCode) {
    console.error('Failed fetching Forge manifest:', errorCode);
}

// Start the viewer
initializeViewer();

