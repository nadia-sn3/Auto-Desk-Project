var viewer; 
let md_ViewerDocument = null; 
let md_viewables = [];  

var options = {
    env: 'AutodeskProduction2',
    accessToken: accessToken,
};

Autodesk.Viewing.Initializer(options, () => {
    if (!viewer) {
        viewer = new Autodesk.Viewing.GuiViewer3D(document.getElementById("forgeViewer"));
        viewer.start();
    }

    document.querySelectorAll(".file-link").forEach(link => {
        link.addEventListener("click", async (e) => {
            e.preventDefault();
            const fileType = link.getAttribute("data-file-type");
            const urn = link.getAttribute("data-urn");
            const fileName = link.getAttribute("data-file-name");

            if (fileType === 'obj' || fileType === 'dwg' || fileType === 'stl') {
                const imageContainer = document.getElementById('imageContainer');
                imageContainer.style.visibility = 'hidden'; 
                const forgeViewer = document.getElementById('forgeViewer');
                forgeViewer.style.visibility = 'visible'; 

                if (urn) {
                    try {
                        console.log("Starting translation for URN:", urn);
                        const isTranslated = await startTranslation(urn);

                        if (isTranslated) {
                            console.log("Translation started. Checking status...");
                            setTimeout(() => {
                                checkTranslationStatus(urn);
                            }, 5000); 
                        } else {
                            console.log("Translation failed to start.");
                        }
                    } catch (error) {
                        console.error("Error during translation process:", error);
                    }
                }
            } 
            else if (fileType === 'jpg' || fileType === 'jpeg' || fileType === 'png') {
                const forgeViewer = document.getElementById('forgeViewer');
                forgeViewer.style.visibility = 'hidden';  // Hide Forge viewer
                const imageContainer = document.getElementById('imageContainer');
                imageContainer.style.visibility = 'visible';  // Show image container

                const filePath = 'http://localhost/Auto-Desk-Project/backend/Business_Logic/Uploaded_Process/uploads/' + fileName;  // Adjust path accordingly

                const imageElement = document.createElement('img');
                imageElement.src = filePath; 
                imageElement.style.maxWidth = '100%'; 
                imageElement.style.maxHeight = 'calc(100vh - 100px)';
                imageElement.style.objectFit = 'contain'; 

                imageContainer.innerHTML = ''; 
                imageContainer.appendChild(imageElement); 

                console.log('Image saved and accessible at:', filePath);
            } 
            // Check for PDF, DOCX, or DOC files
            else if (fileType === 'pdf' || fileType === 'docx' || fileType === 'doc') {
                const forgeViewer = document.getElementById('forgeViewer');
                forgeViewer.style.visibility = 'hidden';  // Hide Forge viewer

                const imageContainer = document.getElementById('imageContainer');
                imageContainer.style.visibility = 'visible';  // Show image container

                const fileDownloadPath = `http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/download.php?file=${encodeURIComponent(decodeURIComponent(fileName))}`;

                // Create a download button
                imageContainer.innerHTML = '';  // Clear previous content
                const downloadBtn = document.createElement('a');
                downloadBtn.href = fileDownloadPath;
                downloadBtn.innerText = 'Download File';
                downloadBtn.style.display = 'block';
                downloadBtn.style.padding = '10px';
                downloadBtn.style.background = '#007bff';
                downloadBtn.style.color = 'white';
                downloadBtn.style.textAlign = 'center';
                downloadBtn.style.textDecoration = 'none';
                downloadBtn.style.borderRadius = '5px';
                downloadBtn.setAttribute('download', fileName);

                imageContainer.appendChild(downloadBtn);  // Append button

                // PDF rendering logic
                if (fileType === 'pdf') {
                    const pdfPath = `http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/download.php?file=${encodeURIComponent(decodeURIComponent(fileName))}`;
                    const loadingTask = pdfjsLib.getDocument(pdfPath);

                    loadingTask.promise.then(function(pdf) {
                        console.log('PDF loaded');

                        imageContainer.innerHTML = '';  // Clear any previous content

                        // Render all pages of the PDF
                        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                            pdf.getPage(pageNum).then(function(page) {
                                console.log('Rendering page: ' + pageNum);

                                const scale = 1.5;  // Adjust scale as needed
                                const viewport = page.getViewport({ scale: scale });

                                // Create a new canvas for this page
                                const canvas = document.createElement('canvas');
                                imageContainer.appendChild(canvas);  // Append the canvas to the container

                                const context = canvas.getContext('2d');
                                canvas.width = viewport.width;
                                canvas.height = viewport.height;

                                // Render the page on the canvas
                                page.render({
                                    canvasContext: context,
                                    viewport: viewport
                                }).promise.then(function() {
                                    console.log('Page rendered');
                                });
                            });
                        }
                    }).catch(function(error) {
                        console.error('Error loading PDF: ' + error);
                    });

                    console.log('Download link displayed:', fileDownloadPath);
                } 
                // DOCX or DOC file rendering logic
                else if (fileType === 'docx' || fileType === 'doc') {
    const docxPath = `http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/download.php?file=${encodeURIComponent(decodeURIComponent(fileName))}`;
    
    // Fetch the .docx file
    fetch(docxPath)
        .then(response => response.arrayBuffer()) 
        .then(arrayBuffer => {
            console.log('Word document loaded');

            mammoth.convertToHtml({ arrayBuffer: arrayBuffer })
                .then(function(result) {
                    // Clear previous content
                    imageContainer.innerHTML = '';  // Clear any previous content
                    
                    imageContainer.innerHTML = result.value;  // The HTML content from the .docx file
                    
                    console.log('Word document rendered');
                })
                .catch(function(error) {
                    console.error('Error converting .docx to HTML:', error);
                });
            })
                .catch(function(error) {
                    console.error('Error loading .docx file:', error);
                });
        
            console.log('Download link displayed:', fileDownloadPath);
            }
                
            }else if (fileType === 'video' || fileType === 'mp4' || fileType === 'webm' || fileType === 'ogg') {
                const videoPath = `http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/download.php?file=${encodeURIComponent(decodeURIComponent(fileName))}`;
            
                // Clear previous content
                imageContainer.innerHTML = '';
            
                // Create a video element
                const videoElement = document.createElement('video');
                videoElement.width = 600;
                videoElement.controls = true;  // Enable video controls
            
                // Create multiple sources for different video formats
                const videoSourceMP4 = document.createElement('source');
                videoSourceMP4.src = videoPath;
                videoSourceMP4.type = 'video/mp4';
            
                const videoSourceWebM = document.createElement('source');
                videoSourceWebM.src = videoPath.replace('.mp4', '.webm');  
                videoSourceWebM.type = 'video/webm';
            
                const videoSourceOGG = document.createElement('source');
                videoSourceOGG.src = videoPath.replace('.mp4', '.ogg');
                videoSourceOGG.type = 'video/ogg';
            
                // Append the sources to the video element
                videoElement.appendChild(videoSourceMP4);
                videoElement.appendChild(videoSourceWebM);
                videoElement.appendChild(videoSourceOGG);
            
                // Append the video element to the container
                imageContainer.appendChild(videoElement);
            
                console.log('Video rendered');
            }
            else {
                console.log('Unsupported file type:', fileType);
            }
        });
    });
});




async function startTranslation(urn) {
    try {
        const response = await fetch(`http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/start_translation.php?urn=${urn}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'urn': urn
            })
        });

        // Get the raw response text
        const responseText = await response.text();
        console.log('Raw server response:', responseText);  // Log raw response for debugging

        // Remove the comment and any other non-JSON content
        const cleanResponseText = responseText.replace(/\/\/.*$/g, '').trim();  // Regex to remove comments

        // Attempt to parse the cleaned response text as JSON
        const data = JSON.parse(cleanResponseText);

        // Check if the response is successful
        if (data.result === 'created') {
            console.log('Translation started:', data.result);
            return data.result;
        } else {
            console.error('Error starting translation:', data.message || data.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Failed to call start_translation.php:', error);
    }
}






async function checkTranslationStatus(urn) {
    try {
        const response = await fetch(`http://localhost/Auto-Desk-Project/backend/Business_Logic/Function/check_job_status.php?urn=${urn}`, {
            method: 'GET',
        });

        const responseText = await response.text();
        console.log('Translation Status Response:', responseText);

        const cleanResponseText = responseText.replace(/\/\/.*$/g, '').trim();  // Clean response text

        const statusData = JSON.parse(cleanResponseText);

        if (statusData.status === 'success') {
            const result = JSON.parse(statusData.result);  // The result is a stringified JSON
            console.log('Translation is complete:', result);

            // Check if the progress is complete
            if (result.progress === 'complete') {
                console.log('Translation completed successfully.');
                // Now that translation is complete, load the model
                loadModel(urn); // Call loadModel with the urn
            } else {
                console.log('Translation is still in progress...');
            }
        } else {
            console.error('Error checking translation status:', statusData.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Failed to check translation status:', error);
    }
}

async function loadModel(urn) {
    if (urn && urn.length > 0) {
        const documentId = `urn:${urn}`;
        console.log('Loading model with URN:', urn);
        Autodesk.Viewing.Document.load(documentId, onDocumentLoadSuccess, onDocumentLoadFailure);
    } else {
        console.error('Error: URN is undefined or invalid.');
    }
}

function onDocumentLoadSuccess(doc) {
    console.log('Document loaded successfully:', doc);

    const viewables = doc.getRoot().search({ type: 'geometry' });

    if (viewables.length === 0) {
        console.error('No viewables found.');
        return;
    }

    viewer.loadDocumentNode(doc, viewables[0]).then(() => {
        console.log('Model loaded successfully and displayed.');
    }).catch((error) => {
        console.error('Error loading model:', error);
    });
}



function onDocumentLoadFailure(errorCode) {
    console.error('Failed fetching Forge manifest. Error code:', errorCode);
    switch (errorCode) {
        case 5:
            console.error("Error: Model access issue, possibly permissions-related.");
            break;
        default:
            console.error("Unknown error occurred.");
            break;
    }
}

